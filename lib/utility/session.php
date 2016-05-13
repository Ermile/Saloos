<?php
namespace lib\utility;

/** session: handle session of project **/
class session
{
	/**
	 * this library work with session
	 * v2.2
	 */


	/**
	 * save session in options table
	 * @return [type] [description]
	 */
	public static function save($_userid = true, $_meta = false)
	{
		$session_id = session_id();
		// define session array
		$session =
		[
			'user'  => $_userid,
			'cat'   => 'session',
			'key'   => session_name().'__USER_',
			'value' => $session_id,
		];
		if($_meta)
		{
			$session['meta'] = $_meta;
		}
		// save in options table and if successful return session_id
		if(\lib\utility\option::set($session))
		{
			return $session_id;
		}
		// else return false
		return false;
	}


	/**
	 * save session id database only one time
	 * if exist use old one
	 * else insert new one to database
	 * @param  [type]  $_userid [description]
	 * @param  boolean $_meta   [description]
	 * @return [type]           [description]
	 */
	public static function save_once($_userid, $_meta = false)
	{
		// create key value
		$op_key = session_name().'_'. $_userid;
		// create query string
		$qry = "SELECT `option_value`
			FROM options
			WHERE
				`user_id` = $_userid AND
				`option_cat` = 'session' AND
				`option_key` = '$op_key'
		";
		// if we have meta then add it to query
		if($_meta)
		{
			$qry .= "AND `option_meta` = '$_meta'";
		}
		// run query and get result
		$session_id = \lib\db::get($qry, 'option_value', true);
		// if session exist restart session with new id
		if($session_id)
		{
			self::restart($session_id);
		}
		// else if session is not exist for this condition
		else
		{
			$session_id = self::save($_userid, $_meta);
		}
		return $session_id;
	}


	public static function restart($_session_id)
	{
		// if a session is currently opened, close it
		if (session_id() != '')
		{
			session_write_close();
		}
		// use new id
		session_id($_session_id);
		// start new session
		session_start();
	}


	/**
	 * delete session file with id
	 * @param  [type] $_id [description]
	 * @return [type]      [description]
	 */
	public static function delete($_id)
	{
		$path   = session_save_path();
		$result = [];
		if(is_integer($_id))
		{
			$_id = [$_id];
		}
		if(is_array($_id))
		{
			foreach ($_id as $value)
			{
				$filename = $path. '/sess_'.$value;
				$result[$value] = null;
				if(file_exists($filename))
				{
					$result[$value] = @unlink($filename);
				}
			}
		}
		// return result
		return $result;
	}


	/**
	 * delete session file with given perm name
	 * @param  [type]  $_permName [description]
	 * @param  boolean $_exceptMe [description]
	 * @return [type]             [description]
	 */
	public static function deleteByPerm($_permName)
	{
		$permList     = \lib\utility\option::permList(true);
		$deleteResult = [];

		// if permission exist
		if(isset($permList[$_permName]))
		{
			// find user with this permission
			$perm_id = $permList[$_permName];
			// connect to database
			\lib\db::connect(true);
			$qry =
			"SELECT `options`.option_value
				FROM users
				INNER JOIN `options` ON `options`.user_id = `users`.id
				WHERE `options`.option_cat = 'session' AND
					user_permission = $perm_id;";
			// run query and give result
			$result = @mysqli_query(\lib\db::$link, $qry);
			// fetch all records
			$result = \lib\db::fetch_all($result, 'option_value');
			if($result)
			{
				$deleteResult = self::delete($result);
				// for each file in delete
				foreach ($deleteResult as $key => $value)
				{
					// if file is deleted
					if($value === true)
					{
						$qry = "DELETE FROM options WHERE option_cat = 'session' AND option_value = '$key';";
						@mysqli_query(\lib\db::$link, $qry);
					}
				}
				return $deleteResult;
			}
		}
		return null;
	}
}
?>