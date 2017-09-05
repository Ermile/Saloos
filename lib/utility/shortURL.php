<?php
namespace lib\utility;

/** ShortURL: Bijective conversion between natural numbers (IDs) and short strings **/
class shortURL
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

	// const ALPHABET = '23456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
	const ALPHABET = SHORTURL_ALPHABET;


	/**
	 * encode input text
	 * @param  [type] $_num      [description]
	 * @param  [type] $_alphabet [description]
	 * @return [type]            [description]
	 */
	public static function encode($_num = null, $_alphabet = null)
	{
		if($_alphabet == null)
		{
			$_alphabet = self::ALPHABET;
		}
		$lenght = mb_strlen($_alphabet);

		$str = '';
		while ($_num > 0)
		{
			$str  = substr($_alphabet, ($_num % $lenght), 1) . $str;
			$_num = floor($_num / $lenght);
		}
		return $str;
	}


	/**
	 * decode input text
	 * @param  [type] $_str      [description]
	 * @param  [type] $_alphabet [description]
	 * @return [type]            [description]
	 */
	public static function decode($_str = null, $_alphabet = null)
	{
		if($_alphabet == null)
		{
			$_alphabet = self::ALPHABET;
		}

		if(!self::is($_str, $_alphabet))
		{
			return false;
		}

		$lenght = mb_strlen($_alphabet);

		$num    = 0;
		$len    = mb_strlen($_str);
		for ($i = 0; $i < $len; $i++)
		{
			$num = $num * $lenght + strpos($_alphabet, $_str[$i]);
		}
		return $num;
	}


	/**
	 * Determines if short url.
	 *
	 * @param      <type>   $_string  The string
	 *
	 * @return     boolean  True if short url, False otherwise.
	 */
	public static function is($_string, $_alphabet = null)
	{
		if($_alphabet == null)
		{
			$_alphabet = self::ALPHABET;
		}
		return preg_match("/^[". $_alphabet. "]+$/", $_string);
	}
}