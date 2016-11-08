<?php
namespace lib\utility\location;

class tools
{
	public static $array = [];


	/**
	 * set the repository
	 *
	 * @param      <type>  $_array  The array
	 */
	public static function set($_array)
	{
		self::$array = $_array;
	}


	/**
	 * Gets the by name.
	 * get the name of country and return all data for this country
	 *
	 * @param      <type>  $name   The name of country
	 *
	 * @return     <type>  country details.
	 */
	public static function get_by_name($_name)
	{
		if(isset(self::$array[$_name]))
		{
			return self::$array[$_name];
		}
		else
		{
			return null;
		}
	}


	/**
	 * get the cost detail of country
	 *
	 * @example self::get('id', 107, 'name')
	 * @return string "Iran"
	 *
	 * @example self::get('name', 'Iran', 'id')
	 * @return string "107"
	 *
	 * @example self::get('name', 'Iran', ['id','language'])
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

		foreach (self::$array as $key => $value)
		{
			if(isset(self::$array[$key][$_type]) && self::$array[$key][$_type] == $_cost)
			{
				if($_request && !is_array($_request))
				{
					if(isset(self::$array[$key][$_request]))
					{
						if($_request == "localname" && self::$array[$key][$_request] == '')
						{
							return self::$array[$key]['name'];
						}
						return self::$array[$key][$_request];
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
						if(isset(self::$array[$key][$v]))
						{
							if($v == "localname" && self::$array[$key][$v] == '')
							{
								$result[$v] = self::$array[$key]['name'];
							}
							else
							{
								$result[$v] = self::$array[$key][$v];
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
					return self::$array[$key];
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

		foreach (self::$array as $key => $value)
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
		if(array_key_exists($_name, self::$array))
		{
			return true;
		}
		return false;
	}
}
?>