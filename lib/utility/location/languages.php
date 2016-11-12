<?php
namespace lib\utility\location;
/** country managing **/
class languages
{

	public static $data =
	[
		'fa_IR' => ['lang' => 'fa', 'name' => 'fa_IR', 'localname' => 'Persian - فارسی', 'country' => ['Iran']],
		'en_US' => ['lang' => 'en', 'name' => 'en_US', 'localname' => 'English', 'country' => ['United Kingdom', 'United States']],
		'ar_SU' => ['lang' => 'ar', 'name' => 'ar_SU', 'localname' => 'Arabic - العربية', 'country' => ['Saudi Arabia']],
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
	 * check language exist
	 *
	 * @param      <type>  $_lang  The language
	 */
	public static function check($_lang, $_column = 'lang')
	{
		$lang_list = array_column(self::$data, $_column);
		if(in_array($_lang, $lang_list))
		{
			return true;
		}
		return false;
	}
}
?>