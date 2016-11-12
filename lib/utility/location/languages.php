<?php
namespace lib\utility\location;
/** country managing **/
class languages
{

	public static $data =
	[
		'en' => ['name' => 'en', 'direction' => 'ltr', 'iso' => 'en_US', 'localname' => 'English', 'country' => ['United Kingdom', 'United States']],
		'fa' => ['name' => 'fa', 'direction' => 'rtl', 'iso' => 'fa_IR', 'localname' => 'Persian - فارسی', 'country' => ['Iran']],
		'ar' => ['name' => 'ar', 'direction' => 'rtl', 'iso' => 'ar_SU', 'localname' => 'Arabic - العربية', 'country' => ['Saudi Arabia']],
	];


	/**
	 * get lost of languages
	 */
	public static function list($_request = null, $_index = null)
	{
		if($_request === null)
		{
			return self::$data;
		}
		else
		{
			if($_index === null)
			{
				return array_column(self::$data, $_request);
			}
			else
			{
				return array_column(self::$data, $_index, $_request);
			}
		}
	}


	/**
	 * check language exist and return true or false
	 * @param  [type] $_lang   [description]
	 * @param  string $_column [description]
	 * @return [type]          [description]
	 */
	public static function check($_lang, $_column = 'name')
	{
		$lang_list = array_column(self::$data, $_column);
		if(in_array($_lang, $lang_list))
		{
			return true;
		}
		return false;
	}


	/**
	 * get lang
	 *
	 * @param      <type>  $_key      The key
	 * @param      string  $_request  The request
	 */
	public static function get($_key, $_request = 'iso')
	{
		if(isset(self::$data[$_key]))
		{
			return self::$data[$_key][$_request];
		}
		return null;
	}
}
?>