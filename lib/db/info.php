<?php
namespace lib\db;

trait info
{
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
		// $num = @mysqli_num_rows(self::$link);
		$num = self::$link->affected_rows;
		return $num;
	}


	/**
	 * get the database version from options table
	 *
	 * @param      boolean  $_db_name  The database name
	 */
	public static function db_version($_db_name = true)
	{
		$query =
		"
			SELECT
				option_value AS 'version'
			FROM
				options
			WHERE
				post_id IS NULL AND
				user_id IS NULL AND
				option_cat = 'database_version' AND
				option_key = 'database_version'
			LIMIT 1
		";

		$db_version = \lib\db::get($query, 'version', true, $_db_name);
		if(empty($db_version) || !$db_version)
		{
			// the first time the version is 0
			return false;
		}
		return $db_version;
	}


	/**
	 * Sets the database version.
	 *
	 * @param      <type>   $_version  The version
	 * @param      boolean  $_db_name  The database name
	 */
	public static function set_db_version($_version, $_db_name = true)
	{
		$result          = null;
		$current_version = self::db_version($_db_name);

		if(!$current_version)
		{
			$insert =
			"
				INSERT INTO
					options
				SET
					options.post_id      = NULL,
					options.user_id      = NULL,
					options.option_cat   = 'database_version',
					options.option_key   = 'database_version',
					options.option_value = '$_version'
			";
			$result = \lib\db::query($insert, $_db_name);
		}
		else
		{
			$update =
			"
				UPDATE
					options
				SET
					options.option_value  = '$_version'
				WHERE
					options.user_id IS NULL AND
					options.post_id IS NULL AND
					options.option_cat   = 'database_version' AND
					options.option_key   = 'database_version'
			";
			$result = \lib\db::query($update, $_db_name);
		}
		return $result;
	}
}
?>