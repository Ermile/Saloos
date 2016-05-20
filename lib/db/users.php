<?php
namespace lib\db;

/** users account managing **/
class users
{
	/**
	 * this library work with acoount
	 * v1.1
	 */

	public static $user_id;


	/**
	 * check signup and if can add new user
	 * @return [type] [description]
	 */
	public static function signup($_mobile, $_pass, $_perm = null, $_name = null)
	{
		// first if perm is true get default permission from db
		if($_perm === true)
		{
			// if use true fill it with default value
			$_perm     = \lib\utility\option::get('account');
		}
		// if permission is not set then set it null
		if(!$_perm)
		{
			$_perm = 'NULL';
		}
		if(!$_name)
		{
			$_name = 'NULL';
		}

		$qry = "SELECT * FROM `users` WHERE `user_mobile` = $_mobile";
		// connect to project database
		\lib\db::connect();
		$result     = @mysqli_query(\lib\db::$link, $qry);
		$user_exist = @mysqli_affected_rows(\lib\db::$link);

		if($user_exist !== 0)
		{
			if(!is_a($result, 'mysqli_result') || $user_exist !== 1)
			{
				// no record exist
				return false;
			}

			// if has result return id
			if($result && $row = @mysqli_fetch_assoc($result))
			{
				if(isset($row['id']))
				{
					self::$user_id = $row['id'];
					return $row['id'];
				}
			}
			// mobile number exist in database
			return false;
		}

		$qry = "INSERT INTO `users`
		(
			`user_mobile`,
			`user_pass`,
			`user_displayname`,
			`user_permission`,
			`user_createdate`
		)
		VALUES
		(
			$_mobile,
			'$_pass',
			'$_name',
			$_perm,
			'".date('Y-m-d H:i:s')."'
		)";

		// execute query
		$result     = @mysqli_query(\lib\db::$link, $qry);
		$user_exist = @mysqli_affected_rows(\lib\db::$link);

		// give last insert id
		$last_id    = @mysqli_insert_id(\lib\db::$link);
		// if have last insert it return it
		if($last_id)
		{
			self::$user_id = $last_id;
			return $last_id;
		}
		return null;
	}
}
?>