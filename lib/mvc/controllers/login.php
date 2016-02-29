<?php
namespace lib\mvc\controllers;

trait login
{
	/**
	 * Return login status without parameter
	 * If you pass the name as all return all of user session
	 * If you pass specefic user data field name return it
	 * @param  [type] $_name [description]
	 * @return [type]        [description]
	 */
	public function login($_name = null)
	{
		if(isset($_name))
		{
			if($_name=="all")
				return isset($_SESSION['user'])? $_SESSION['user']: null;

			else
				return isset($_SESSION['user'][$_name])? $_SESSION['user'][$_name]: null;
		}

		if(isset($_SESSION['user']['id']))
			return true;
		else
			return false;
	}
}
?>