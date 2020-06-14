<?php
namespace RbParser;

use DiDom\Document;

class RbParser extends BaseCLI
{
    private $districts = [];
    private $unknownDistrictsCount = 0;

    public function run()
    {
        $this->info('Start parsing');
        $this->parseDistrictsList();
        $this->parseDistrictsData();
        $this->saveInCsv();
        $this->info('Parsing ended');
        $this->info('Total districts count: ' . count($this->districts));
        $this->info('Not parsed disctricts count: ' . $this->unknownDistrictsCount);
    }

    private function parseDistrictsList()
    {
        $document = new Document('https://www.bashkortostan.ru/thesaurus/governments/local/', true);
        $links = $document->find('.ogv__item--local .ogv__portal-name a');

        foreach($links as $link) {
            $this->message($link->text());

            $this->districts[] = [
                'name' => $link->text(),
                'site' => $link->attr('href'),
            ];
        }

        $this->info('Found ' . count($links) . ' district links');
    }

    private function parseDistrictsData()
    {
        $this->info('Start parsing districts data');

        foreach ($this->districts as &$district) {
            $this->message($district['name']);

            if (!$this->isKnownSite($district['site'])) {
                $this->warning('Unknown! ' . $district['site']);
                $this->unknownDistrictsCount++;

                continue;
            }

            $this->parseDistrict($district);
        }
    }

    private function parseDistrict(&$district)
    {
        $this->parseDistrictCeo($district);
        $this->parseDistrictContacts($district);
    }

    private function parseDistrictCeo(&$district)
    {
        $document = new Document($district['site'] . '/about/structure/', true);

        $ceo = $document->first('.data-table.workers .workers__item');
        $ceoData = $ceo->find('td');

        $district['ceo_name'] = trim($ceoData[0]->text());
        $district['ceo_department'] = trim($ceoData[1]->text());
        $district['ceo_position'] = trim($ceoData[2]->text());
        $district['ceo_contacts'] = trim($ceoData[3]->text());
    }

    private function parseDistrictContacts(&$district)
    {
        $document = new Document($district['site'] . '/about/contacts/', true);
        $postAddress = $document->first('.data-table--striped tr:nth-child(4) td:nth-child(2)');
        $district['post_address'] = trim($postAddress->text());
        $phone = $document->first('.data-table--striped tr:nth-child(5) td:nth-child(2)');
        $district['phone'] = trim($phone->text());
    }

    private function saveInCsv()
    {
        if(!$fp = fopen('data.csv', 'w')) {
            throw new \Exception('Can not open scv file');
        }

        $this->outHeader($fp);

        foreach ($this->districts as $key => $district) {
            fputcsv($fp, $district);
        }

        fclose($fp);
    }

    private function outHeader($fp)
    {
        fputcsv($fp, [
            'Название', 'Сайт', 'Ф. И. О. рукодвителя', 'Подразделение руководителя', 'Должность руководителя',
            'Контактная ифнормация руководителя', 'Почтовый адрес', 'Телефон приемной',
        ]);
    }

    /**
     * Если сайт не на поддомене сайта bashkortostan.ru, то его структура неизвестна
     * и надо обрабатывать в ручную
     * @param $url
     * @return bool
     */
    private function isKnownSite($url)
    {
        return (bool) substr_count($url, '.bashkortostan.ru');
    }
}