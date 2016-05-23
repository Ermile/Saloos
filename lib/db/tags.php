<?php
namespace lib\db;

/** tags managing **/
class tags
{
	/**
	 * this library work with tags
	 * v1.1
	 */


	/**
	 * get tags list used in specefic post in specefic table
	 * @param  [type]  $_post_id   [description]
	 * @param  [type]  $_return    [description]
	 * @param  [type]  $_foreign   [description]
	 * @param  boolean $_seperator [description]
	 * @return [type]              [description]
	 */
	public static function usage($_post_id, $_return = null, $_foreign = null, $_seperator = false)
	{
		$result = terms::usage($_post_id, $_return, $_foreign, 'tag');
		if($_seperator)
		{
			if(is_array($result) && $result)
			{
				if($_seperator === true)
				{
					$_seperator = ', ';
				}
				$result = implode($result, $_seperator);
			}
			else
			{
				$result = "";
			}
		}
		return $result;
	}
}
?>