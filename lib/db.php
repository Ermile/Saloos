<?php
namespace lib;

/** Create simple and clean connection to db **/
class db
{
	/**
	 * this library doing useful db actions
	 * v4.4
	 */
	use db\connect;
	use db\backup;
	use db\install;
	use db\get;
	use db\info;
	use db\pagination;
	use db\log;


	/**
	 * run query string and return result
	 * now you don't need to check result
	 * @param  [type] $_qry [description]
	 * @return [type]       [description]
	 */
	public static function query($_qry, $_db_name = true, $_multi_query = false)
	{
		// on default system connect to default db
		$different_db = false;

		// check debug status
		if(!\lib\debug::$status)
		{
			return false;
		}

		// check connect to default db or no
		if($_db_name === true)
		{
			// connect to main database
			self::connect(true);
		}
		elseif(is_string($_db_name))
		{
			// connect to different db
			self::connect($_db_name);
			// different db used.
			$different_db = true;
		}
		else
		{
			return false;
		}

		// check the mysql link
		if(!self::$link)
		{
			return null;
		}
		// get time before execute query
		$qry_exec_time = microtime(true);
		/**
		 * send the query to mysql engine
		 */
		if($_multi_query === true)
		{
			$result = mysqli_multi_query(self::$link, $_qry);
		}
		else
		{
			$result = mysqli_query(self::$link, $_qry);
		}
		// get diff of time after exec
		$qry_exec_time = microtime(true) - $qry_exec_time;

		// if debug mod is true save all string query
		if(DEBUG)
		{
			self::log($_qry, $qry_exec_time);
		}
		// check the mysql result
		if(!is_a($result, 'mysqli_result') && !$result)
		{
			// no result exist
			// save mysql error
			self::log("MYSQL ERROR ". mysqli_error(self::$link));
			if(self::$debug_error)
			{
				\lib\debug::error(mysqli_error(self::$link),false, 'sql');
			}
			return false;
		}

		// set the default link
		if($different_db)
		{
			self::$link = self::$link_default;
		}

		// return the mysql result
		return $result;
	}


	/**
	 * transaction
	 */
	public static function transaction()
	{
		self::query("START TRANSACTION");
	}


	/**
	 * commit
	 */
	public static function commit()
	{
		self::query("COMMIT");
	}


	/**
	 * rollback
	 */
	public static function rollback()
	{
		self::query("ROLLBACK");
	}
}
?>