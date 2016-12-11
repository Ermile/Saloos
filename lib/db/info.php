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
}
?>