<?php
namespace lib\utility;

/** account managing **/
class account
{
	/**
	 * this library work with acoount
	 * v1.0
	 */


	/**
	 * check signup and if can add new user
	 * @return [type] [description]
	 */
	public static function signup($_mobile, $_pass, $_perm = 'NULL')
	{
		$qry = "SELECT * FROM `users` WHERE `user_mobile` = $_mobile";
		// connect to project database
		\lib\db::connect();
		$result     = @mysqli_query(\lib\db::$link, $qry);
		$user_exist = @mysqli_affected_rows(\lib\db::$link);
		if($user_exist !== 0)
		{
			// mobile number exist in database
			return false;
		}

		$qry = "INSERT INTO `users`
		(
			`user_mobile`,
			`user_pass`,
			`user_permission`,
			`user_createdate`
		)
		VALUES
		(
			$_mobile,
			'$_pass',
			$_perm,
			'".date('Y-m-d H:i:s')."'
		)";
		// var_dump($qry);

		// execute query
		$result     = @mysqli_query(\lib\db::$link, $qry);
		// var_dump($result);
		// var_dump(\lib\db::$link);
		$user_exist = @mysqli_affected_rows(\lib\db::$link);

		// give last insert id
		$last_id    = @mysqli_insert_id(\lib\db::$link);
		// if have last insert it return it
		if($last_id)
		{
			return $last_id;
		}
		return null;
	}
}
?>