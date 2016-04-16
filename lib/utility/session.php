<?php
namespace lib\utility;

/** session: handle session of project **/
class session
{
	/**
	 * this library work with session
	 * v1.1
	 */


	/**
	 * save session in options table
	 * @return [type] [description]
	 */
	public static function save()
	{
		// define session array
		$session =
		[
			'user'  => true,
			'cat'   => 'sessions',
			'key'   => session_name().'__USER_',
			'value' => session_id(),
		];
		// save in options table
		return \lib\utility\option::set($session);
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
				$result[$filename] = null;
				if(file_exists($filename))
				{
					$result[$filename] = @unlink($filename);
				}
			}
		}
		// return result
		return $result;
	}


	/**
	 * delete sessions file with given perm name
	 * @param  [type]  $_permName [description]
	 * @param  boolean $_exceptMe [description]
	 * @return [type]             [description]
	 */
	public static function deleteByPerm($_permName)
	{
		$permList = \lib\utility\option::permList(true);

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
				WHERE `options`.option_cat = 'sessions' and
					user_permission = $perm_id;";
			// run query and give result
			$result = @mysqli_query(\lib\db::$link, $qry);
			// fetch all records
			$result = \lib\db::fetch_all($result, 'option_value');
			if($result)
			{
				return self::delete($result);
			}
		}
		return null;
	}
}
?>