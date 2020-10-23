<?php

namespace Zver;

use Zver\StringHelper;

class Encoder
{

    protected static $ENCODING_MAP = [
        '0' => '7',
        '1' => 'a',
        '2' => 'f',
        '3' => '5',
        '4' => 'e',
        '5' => '3',
        '6' => '8',
        '7' => '0',
        '8' => '6',
        '9' => 'c',
        'a' => '1',
        'b' => 'd',
        'c' => '9',
        'd' => 'b',
        'e' => '4',
        'f' => '2',
    ];

    /**
     * '0', '$'
     * '7', '0'
     * '$', '7'
     * '1', '$'
     * 'a', '1'
     * '$', 'a'
     * '2', '$'
     * 'f', '2'
     * '$', 'f'
     * '3', '$'
     * '5', '3'
     * '$', '5'
     * '4', '$'
     * 'e', '4'
     * '$', 'e'
     * '6', '$'
     * '8', '6'
     * '$', '8'
     * '9', '$'
     * 'c', '9'
     * '$', 'c'
     * 'b', '$'
     * 'd', 'b'
     * '$', 'd'
     */

    /**
     * String encoding function
     */
    public static function encodeString($string)
    {
        $result = '';
        $string_length = strlen($string);
        for ($i = 0; $i < $string_length; $i++) {
            if (isset(self::$ENCODING_MAP[$string{$i}])) {
                $result .= self::$ENCODING_MAP[$string{$i}];
            } else {
                $result .= $string{$i};
            }
        }

        return $result;
    }

    /**
     * Hardid encoding function
     */
    public static function encodeHardid($hardid)
    {
        $pos = strpos($hardid, 'DEV_');
        if ($pos === false) {
            return $hardid;
        }

        $head = substr($hardid, 0, $pos + 4);
        $tail = substr($hardid, $pos + 4);

        $tail = strtoupper(static::encodeString(strtolower($tail)));

        return $head . $tail;
    }

    /**
     * Filename encoding function
     */
    public static function encodeFilename($filename)
    {

        $filename = StringHelper::load($filename);

        $prefix = '';

        if ($filename->isContainIgnoreCase('_')) {
            $prefix = $filename->getFirstPart('_') . '_';
            $filename = $filename->removeFirstPart('_');
        }

        $ext = $filename->getLastPart('.');
        $filename->setFirstPart('.');

        if (StringHelper::load($ext)
                        ->isEqualsIgnoreCase('7z')) {
            $ext = '$z';
        }

        return $prefix . static::encodeString($filename->get()) . '.' . $ext;
    }

    /**
     * Filename decoding function
     */
    public static function decodeFilename($filename)
    {
        $filename = StringHelper::load($filename);

        $prefix = '';

        if ($filename->isContainIgnoreCase('_')) {
            $prefix = $filename->getFirstPart('_') . '_';
            $filename = $filename->removeFirstPart('_');
        }

        $ext = $filename->getLastPart('.');
        $filename->setFirstPart('.');

        if (StringHelper::load($ext)
                        ->isEqualsIgnoreCase('$z')) {
            $ext = '7z';
        }

        return $prefix . static::decodeString($filename->get()) . '.' . $ext;
    }

    /**
     * String decoding function
     */
    public static function decodeString($string)
    {
        $replaces = array_flip(static::$ENCODING_MAP);
        $result = '';
        $string_length = strlen($string);
        for ($i = 0; $i < $string_length; $i++) {
            if (isset($replaces[$string{$i}])) {
                $result .= $replaces[$string{$i}];
            } else {
                $result .= $string{$i};
            }
        }

        return $result;
    }

    /**
     * Hardid decoding function
     */
    public static function decodeHardid($hardid)
    {
        $pos = strpos($hardid, 'DEV_');
        if ($pos === false) {
            return $hardid;
        }

        $head = substr($hardid, 0, $pos + 4);
        $tail = substr($hardid, $pos + 4);

        $tail = strtoupper(static::decodeString(strtolower($tail)));

        return $head . $tail;
    }
}