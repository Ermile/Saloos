<?php
namespace lib\utility;
class safe
{
	/**
	 * safe string for sql injection and XSS
	 * @param  string $_string unsafe string
	 * @return string          safe string
	 */
	public static function safe($_string)
	{
		if(is_array($_string) || is_object($_string))
		{
			return self::walk($_string);
		}
		if(
			gettype($string) == 'integer' ||
			gettype($string) == 'double' ||
			gettype($string) == 'boolean' ||
			$string === null
			)
		{
			return $string;
		}
		$string = htmlspecialchars($_string, ENT_QUOTES | ENT_HTML5);
		$string = addcslashes($string, '\\');
		return $string;
	}

	/**
	 * Nested function for walk array or object
	 * @param  array or object $_value unpack array or object
	 * @return array or object         safe array or object
	 */
	private static function walk($_value)
	{
		foreach ($_value as $key => $value)
		{
			if(is_array($value) || is_object($value))
			{
				if(is_array($_value))
				{
					$_value[$key] = self::walk($value);
				}
				elseif(is_object($_value))
				{
					$_value->$key = self::walk($value);
				}
			}
			else
			{
				if(is_array($_value))
				{
					$_value[$key] = self::safe($value);
				}
				elseif(is_object($_value))
				{
					$_value->$key = self::safe($value);
				}
			}
		}
		return $_value;
	}
}
?>