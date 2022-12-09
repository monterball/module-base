<?php
/**
 * Module base of Eloab package
 * @package Eloab_Base
 * @author Bao Le
 * @date 2022
 */
namespace Eloab\Base;

class EloabStaticFunctions {
    public static function log($file, $message)
    {
        $formattedDate = date('d-m-Y H:i:s');
        file_put_contents(BP . "/var/log/$file", $formattedDate . ': ' . $message . "\n", FILE_APPEND);
    }
}
