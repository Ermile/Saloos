<?php
namespace lib\utility\location;

trait tools
{
	/**
	 * get the cost detail of country
	 *
	 * @example \lib\utility\location\countries::get('id', 107, 'name')
	 * @return string "Iran"
	 *
	 * @example \lib\utility\location\countries::get('name', 'Iran', 'id')
	 * @return string "107"
	 *
	 * @example \lib\utility\location\countries::get('name', 'Iran', ['id','language'])
	 * @return array ['id' => "107", 'language' => "fa-IR"]
	 *
	 * @param      string      		  $_type     The type
	 * @param      string		      $_cost     The cost
	 * @param      array|string       $_request  The request
	 *
	 * @return     array|string  ( description_of_the_return_value )
	 */
	public static function get($_type, $_cost, $_request = null)
	{

		foreach (self::$data as $key => $value)
		{
			if(isset(self::$data[$key][$_type]) && self::$data[$key][$_type] == $_cost)
			{
				if($_request && !is_array($_request))
				{
					if(isset(self::$data[$key][$_request]))
					{
						if($_request == "localname" && self::$data[$key][$_request] == '')
						{
							return self::$data[$key]['name'];
						}
						return self::$data[$key][$_request];
					}
					else
					{
						return null;
					}
				}

				if($_request && is_array($_request))
				{
					$result = [];
					foreach ($_request as $k => $v) {
						if(isset(self::$data[$key][$v]))
						{
							if($v == "localname" && self::$data[$key][$v] == '')
							{
								$result[$v] = self::$data[$key]['name'];
							}
							else
							{
								$result[$v] = self::$data[$key][$v];
							}
						}
						else
						{
							$result[$v] = null;
						}
					}
					return $result;
				}
				else
				{
					return self::$data[$key];
				}
			}
		}
	}


	/**
	 * get list of country
	 */
	public static function list($_field, $_field2 = null)
	{
		$result = [];

		foreach (self::$data as $key => $value)
		{
			if($_field2)
			{
				if($_field2 == "localname" && $value['localname'] == '')
				{
					$result[$value[$_field]] = $value['name'];
				}
				else
				{
					$result[$value[$_field]] = $value[$_field2];
				}
			}
			else
			{
				if($_field == "localname" && $value['localname'] == '')
				{
					$result[] = $value['name'];
				}
				else
				{
					$result[] = $value[$_field];
				}
			}
		}
		return $result;
	}


	/**
	 * check country name exist of no
	 *
	 * @param      <type>   $_name  The name
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function check($_name)
	{
		if(array_key_exists($_name, self::$data))
		{
			return true;
		}
		return false;
	}
}
?>