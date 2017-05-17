<?php
namespace lib;

/** Access: handle permissions **/
class permission
{

	public static $perm_list       = [];
	public static $user_id         = null;
	public static $caller          = null;
	public static $user_permission = null;

	/**
	 * load permission
	 */
	public static function _construct()
	{
		if(empty(self::$perm_list))
		{
			if(file_exists('../permission.php'))
			{
				require_once('../permission.php');
			}
		}

		if(!self::$user_id && isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id']))
		{
			self::$user_id = $_SESSION['user']['id'];
		}

		if(isset($_SESSION['user']['permission']))
		{
			self::$user_permission = $_SESSION['user']['permission'];
		}

		if(!self::$user_permission)
		{
			self::load_user_data();
		}
	}


	/**
	 * Loads an user data.
	 */
	public static function load_user_data()
	{
		if(self::$user_id && is_numeric(self::$user_id))
		{
			$user_data = \lib\db\users::get(self::$user_id);
			if(isset($user_data['user_permission']))
			{
				self::$user_permission = $user_data['user_permission'];
				$_SESSION['user']['permission'] = self::$user_permission;
			}
		}
	}

	/**
	 * check access users
	 *
	 * @param      <type>  $_caller  The caller
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function access($_caller, $_action = null)
	{
		self::_construct();
		// var_dump(debug_backtrace());exit();
		$permission_check = self::check($_caller);

		if($_action === 'notify')
		{
			if($permission_check)
			{
				return true;
			}
			else
			{
				\lib\debug::error(T_("Can not access to it"));
				return false;
			}
		}
		elseif($_action === 'block')
		{
			if($permission_check)
			{
				return true;
			}
			else
			{
				\lib\error::access();
				return false;
			}
		}
		else
		{
			return $permission_check;
		}
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_caller  The caller
	 */
	private static function check($_caller)
	{
		if(!self::$user_id)
		{
			return false;
		}

		if(empty(self::$perm_list))
		{
			return true;
		}

		self::caller($_caller);

		$user_data_loaded = false;
		if(isset(self::$caller['need_check']))
		{
			self::load_user_data();
			$user_data_loaded = true;
		}

		if(isset(self::$caller['need_verify']))
		{
			if(!$user_data_loaded)
			{
				self::load_user_data();
			}
			// and verify users !
		}

		if(self::$user_permission === 'admin')
		{
			return true;
		}

		$explode = explode(',', self::$user_permission);

		if(isset(self::$caller['key']))
		{
			if(in_array(self::$caller['key'], $explode))
			{
				return true;
			}
		}
		return false;
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_caller  The caller
	 */
	private static function caller($_caller)
	{
		$caller              = array_column(self::$perm_list, 'caller');
		$caller              = array_combine(array_keys(self::$perm_list), $caller);
		$key                 = array_search($_caller, $caller);
		self::$caller        = isset(self::$perm_list[$key]) ? self::$perm_list[$key] : null;
		self::$caller['key'] = $key;
	}


	/**
	 * return the perm list
	 */
	public function list($_group = null)
	{
		return self::$perm_list;
	}
}
?>