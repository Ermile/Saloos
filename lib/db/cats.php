<?php
namespace lib\db;

/** cats managing **/
class cats
{
	/**
	 * this library work with cats
	 * v1.0
	 */


	/**
	 * get cats list used in specefic post in specefic table
	 * @param  [type]  $_post_id   [description]
	 * @param  [type]  $_return    [description]
	 * @param  [type]  $_foreign   [description]
	 * @param  boolean $_seperator [description]
	 * @return [type]              [description]
	 */
	public static function usage($_post_id, $_return = null, $_foreign = null, $_seperator = false)
	{
		$result = terms::usage($_post_id, $_return, $_foreign, 'cat');
		if($_seperator && $result)
		{
			if($_seperator === true)
			{
				$_seperator = ', ';
			}
			$result = implode($result, $_seperator);
		}
		return $result;
	}
}
?>