<?php
/**
 * Module base of Eloab package
 * @package Eloab_Base
 * @author Bao Le
 * @date 2022
 * @description Model Logger of Eloab base
 */
namespace Eloab\Base\Model;

use Monolog\Logger as MonoLogger;

class Logger extends MonoLogger
{
    protected $inShell;

    public function setInShell($inShell = false)
    {
        $this->inShell = $inShell;
    }

    /**
     * @param $message
     * @param $level
     * @param array $extra
     * @return void
     */
    public function log($message, $level = \Zend_Log::INFO, array $extra = []) : void
    {
        // Print out command line if isShell is true
        if ($this->inShell) {
            $printMessage = is_array($message) || is_object($message) ?
                print_r($message, true) : $message;
            printf($printMessage . "\n");
        }
        parent::log($level, $message, $extra);
    }
}
