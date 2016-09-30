<?php
namespace lib\utility;

/** Filter of values : mobile ...etc **/
class filter
{
	/**
	 * this function filter mobile value
	 * 1. remove space from mobile number if exist
	 * 2. remove + from first of number if exist
	 * 3. remove 0 from iranian number
	 * 4. start 98 to start of number for iranian number
	 *
	 * @param  [str] $_mobile
	 * @return [str] filtered mobile number
	 */
	public static function mobile($_mobile)
	{
		$mymobile = str_replace(' ', '', $_mobile);

		// if user enter plus in start of number delete it
		if(substr($mymobile, 0, 1) === '+')
			$mymobile = substr($mymobile, 1);

		// if start with zero then remove it
		if(strlen($mymobile) === 11 && substr($mymobile, 0, 2) === '09')
			$mymobile = substr($mymobile, 1);

		// if user type 10 number like 935 726 9759 and number start with 9 append 98 at first
		if(strlen($mymobile) === 10 && substr($mymobile, 0, 1) === '9')
			$mymobile = '98'.$mymobile;

		return $mymobile;
	}

	/**
	 * filter birthdate value
	 * @param  [str] $_date raw date for filtering in function
	 * @param  [str] $_arg  type of input like year or month on day
	 * @return [str]        filtered birthdate
	 */
	public static function birthdate($_date, $_arg)
	{
		if($_arg && method_exists(__CLASS__, $_arg) )
		{
			$mydate = self::$_arg($_date);
		}
		else
		{
			$mydate	= $_date;
		}

		return $mydate;
	}

	/**
	 * change simple string in any language to english
	 * @param  [type] $_string  raw string
	 * @param  [type] $_splitor if needed pass splitor
	 * @return [type]           return the new slug in english
	 */
	public static function slug($_string, $_splitor=null)
	{
		$slugify = new \lib\utility\slugify();
		$slugify->activateRuleset('persian');
		if($_splitor)
			return $slugify->slugify($_string, $_splitor);
		else
			return $slugify->slugify($_string);
	}

	public static function decode_meta($_array, $_field = null)
	{
		$field = $_field? $_field : "/^.+_meta$/";
		array_walk($_array, function(&$_row, $_key, $_field)
		{
			$keys = array_keys($_row);
			$json_fields = preg_grep($_field, $keys);
			foreach ($json_fields as $key => $value) {
				$_row[$value] = json_decode($_row[$value], true);
			}
		}, $field);
		return $_array;
	}
}