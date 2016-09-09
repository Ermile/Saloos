<?php
namespace lib\utility;
class safe
{
	/**
	 * get array and walk for find string variables for safing :)
	 * @param  array $_array unsage array
	 * @return array         safe array
	 */
	public static function array($_array)
	{
		$array = self::array_walk($_array);
		return $array;
	}

	/**
	 * safe string for sql injection and XSS
	 * @param  string $_string unsafe string
	 * @return string          safe string
	 */
	public static function safe($_string)
	{
		if(is_array($_string))
		{
			return self::array($_string);
		}
		$string = htmlspecialchars($_string, ENT_QUOTES | ENT_HTML5);
		$string = addcslashes($string, '\\');
		return $string;
	}

	/**
	 * Nested function for walk array
	 * @param  array $_value unpack array
	 * @return array         safe array
	 */
	private static function array_walk($_value)
	{
		foreach ($_value as $key => $value)
		{
			if(is_array($value))
			{
				$_value[$key] = self::array_walk($value);
			}
			else
			{
				$_value[$key] = self::safe($value);
			}
		}
		return $_value;
	}
}
?>