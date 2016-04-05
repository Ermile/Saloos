<?php
namespace lib\utility;

/** Option: handle options of project from db **/
class Option
{
	/**
	 * this library get options from db only one times!
	 * v1.0
	 */

	// declare private static variable to save options
	private static $options;

	/**
	 * get options from db and return the result
	 * @param  [type]  $_key  [description]
	 * @param  string  $_type [description]
	 * @param  boolean $_meta [description]
	 * @return [type]         [description]
	 */
	public static function get($_key = null, $_type = 'value', $_meta = false)
	{
		// fetch records from database
		if(!self::$options)
		{
			self::$options = self::fetch();
		}

		$result  = [];

		// check condition for show best result
		if($_key && isset(self::$options[$_key]))
		{
			if($_type && !($_type === true))
			{
				if(isset(self::$options[$_key][$_type]))
				{
					if($_meta)
					{
						if(isset(self::$options[$_key][$_type][$_meta]))
						{
							$result = self::$options[$_key][$_type][$_meta];
						}
						else
						{
							$result = null;
						}
					}
					else
					{
						$result = self::$options[$_key][$_type];
					}
				}
				else
					$result = null;
			}
			else
			{
				$result = self::$options[$_key];
			}
		}
		else
		{
			$result = null;
		}
		// var_dump($result);
		return $result;
	}


	/**
	 * fetch options from db then fix and return result
	 * @param  boolean $_pemissionDetails [description]
	 * @return [type]                     [description]
	 */
	public static function fetch($_pemissionDetails = false)
	{
		// connect to default database
		\lib\db::connect(true);

		// set query string
		$qry =
		"SELECT `options`.*
			FROM `options`
			WHERE user_id IS NULL AND
				post_id IS NULL AND
				(
					option_cat like 'option%' OR
					option_cat like 'permissions'
				)";

		// run query and give result
		$result = @mysqli_query(\lib\db::$link, $qry);
		// if result is not mysqli result return false
		if(!is_a($result, 'mysqli_result'))
		{
			// no record exist
			return '#NA';
		}

		// fetch all records
		$result   = mysqli_fetch_all($result, MYSQLI_ASSOC);
		$permList = [];


		foreach ($result as $key => $row)
		{
			// save permissions to query result
			if($row['option_cat'] == 'permissions')
			{
				// if status is enable
				if($row['option_status'] == 'enable')
				{
					// save list of permission with details of it
					if($_pemissionDetails)
					{
						$qry_result['permissions']['meta'][$row['option_value']] = json_decode($row['option_meta'], true);
					}
					else
					{
						$qry_result['permissions']['meta'][$row['option_key']] = $row['option_value'];
					}

					// save current user permission as option permission value
					if($row['option_key'] == $_SESSION['user']['permission'])
					{
						$qry_result['permissions']['value'] = $row['option_key'];
					}
				}
			}
			else
			{
				$myValue  = $row['option_value'];
				$myMeta   = $row['option_meta'];
				$myStatus = $row['option_status'];
				if($myStatus === 'enable' || $myStatus === 'on' || $myStatus === 'active')
				{
					$myStatus = true;
				}
				else
				{
					$myStatus = false;
				}

				if(substr($myValue, 0,1) == '{')
				{
					$myValue = json_decode($myValue, true);
				}

				if(substr($myMeta, 0,1) == '{')
				{
					$myMeta = json_decode($myMeta, true);
				}

				// save result
				$qry_result[$row['option_key']] =
				[
					'value'  => $myValue,
					'meta'   => $myMeta,
					'status' => $myStatus
				];
			}

		}

		return $qry_result;
	}
}
?>