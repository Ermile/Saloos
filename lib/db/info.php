<?php
namespace lib\db;

trait info
{
	private static $all_db_version = [];

	/**
	 * read query info and analyse it and return array contain result
	 * @return [type] [description]
	 */
	public static function qry_info($_needle = null)
	{
		preg_match_all ('/(\S[^:]+): (\d+)/', mysqli_info(self::$link), $matches);
		$info = array_combine ($matches[1], $matches[2]);
		if($_needle && isset($info[$_needle]))
		{
			$info = $info[$_needle];
		}
		return $info;
	}


	/**
	 * get rows matched
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function rows_matched()
	{
		return self::qry_info("Rows matched");
	}


	/**
	 * get rows changed
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function changed()
	{
		return self::qry_info("Changed");
	}


	/**
	 * get the warnings
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function warnings()
	{
		return self::qry_info("Warnings");
	}


	/**
	 * return the last insert id
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert_id()
	{
		$last_id = @mysqli_insert_id(self::$link);
		return $last_id;
	}


	/**
	 * return version of mysql used on server
	 * @return [type] [description]
	 */
	public static function version()
	{
		// mysqli_get_client_info();
		// mysqli_get_client_version();
		return mysqli_get_server_version(self::$link);
	}


	/**
	 * get num rows of query
	 *
	 * @return     <int>  ( description_of_the_return_value )
	 */
	public static function num()
	{
		$num = @mysqli_num_rows(self::$link);
		// $num = self::$link->affected_rows;
		return $num;
	}


	/**
	 * get the affected rows
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function affected_rows()
	{
		return mysqli_affected_rows(self::$link);
	}


	/**
	 * get the database version from options table
	 *
	 * @param      boolean  $_db_name  The database name
	 */
	public static function db_version($_db_name = true)
	{

		self::connect($_db_name);

		$db_name = self::$db_name;

		$core_name = core_name.'_tools';

		if(empty(self::$all_db_version))
		{
			$query = "SELECT * FROM $core_name.db_version ";
			$db_version = \lib\db::get($query,['db_name', 'version']);
			if(empty($db_version) || !$db_version)
			{
				return false;
			}
			else
			{
				self::$all_db_version = $db_version;
			}
		}

		if(isset(self::$all_db_version[$db_name]))
		{
			return self::$all_db_version[$db_name];
		}
		else
		{
			return false;
		}
	}


	/**
	 * Sets the database version.
	 *
	 * @param      <type>   $_version  The version
	 * @param      boolean  $_db_name  The database name
	 */
	public static function set_db_version($_version, $_db_name = true)
	{
		self::connect($_db_name);

		$db_name = self::$db_name;

		$core_name = core_name.'_tools';

		$query =
		"
			INSERT INTO
				$core_name.db_version
			(db_version.db_name, db_version.version)
			VALUES
			('$db_name', '$_version')
			ON DUPLICATE KEY UPDATE
				db_version.version = '$_version'
		";
		\lib\db::query($query);
	}
}
?>