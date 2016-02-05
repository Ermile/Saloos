<?php
namespace lib\utility;

/** ShortURL: Bijective conversion between natural numbers (IDs) and short strings **/
class ShortURL
{
    /**
     * ShortURL::encode() takes an ID and turns it into a short string
     * ShortURL::decode() takes a short string and turns it into an ID
     *
     * Features:
     * + large alphabet (49 chars) and thus very short resulting strings
     * + proof against offensive words (removed 'a', 'e', 'i', 'o' and 'u')
     * + unambiguous (removed 'I', 'l', '1', 'O' and '0')
     *
     * Example output:
     * 123456789 <=> pgK8p
     *
     * Source: https://github.com/delight-im/ShortURL (Apache License 2.0)
     */

    const ALPHABET = '23456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';

    public static function encode($_num = null, $_alphabet = null)
    {
        if($_alphabet == null)
            $_alphabet = self::ALPHABET;
        $lenght = strlen($_alphabet);

        $str = '';
        while ($_num > 0)
        {
            $str = substr($_alphabet, ($_num % $lenght), 1) . $str;
            $_num = floor($_num / $lenght);
        }
        return $str;
    }
    public static function decode($_str = null, $_alphabet = null)
    {
        if($_alphabet == null)
            $_alphabet = self::ALPHABET;
        $lenght = strlen($_alphabet);

        $num = 0;
        $len = strlen($_str);
        for ($i = 0; $i < $len; $i++)
        {
            $num = $num * $lenght + strpos($_alphabet, $_str[$i]);
        }
        return $num;
    }

}