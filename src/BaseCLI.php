<?php
namespace RbParser;

use JakubOnderka\PhpConsoleColor\ConsoleColor;

/**
 * Class BaseCLI
 * @package SqlDumper
 * Base class for CLI application
 */

class BaseCLI
{
    const TXT_INFO_COLOR_CODE = 'color_10';
    const TXT_WARNING_COLOR_CODE = 'color_208';

    protected $colorOutput; // Object ConsoleColor to output colored text

    public function __construct()
    {
        $this->colorOutput = new ConsoleColor();
    }
    
    /**
     * @param string $text
     */
    protected function message($text)
    {
        echo $text . PHP_EOL;
    }

    /**
     * @param string $text
     */
    protected function info($text)
    {
        echo $this->colorOutput->apply(self::TXT_INFO_COLOR_CODE, $text . PHP_EOL);
    }

    /**
     * @param string $text
     */
    protected function warning($text)
    {
        echo $this->colorOutput->apply(self::TXT_WARNING_COLOR_CODE, $text . PHP_EOL);
    }

    /**
     * @param string $text
     */
    protected function error($text)
    {
        fwrite(STDERR, $text . PHP_EOL);
    }

    protected function endSuccess()
    {
        exit(0);
    }

    protected function endWarning()
    {
        exit(1);
    }

    protected function endCritical()
    {
        exit(2);
    }
}