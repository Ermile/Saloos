<?php
namespace lib\utility;

/** Access: handle permissions **/
class perm
{
	use \lib\mvc\controllers\login;

	public $perm_list       = [];
	public $user_id         = null;
	public $caller          = null;
	public $user_permission = null;

	/**
	 * load permission
	 */
	public function __construct()
	{
		if(method_exists('\lib\permission', 'list'))
		{
			$this->perm_list = \lib\permission::list();
		}
		$this->user_id = $this->login('id');
		$this->user_permission = $this->login('permission');
	}



	/**
	 * Loads an user data.
	 */
	public function load_user_data()
	{
		$user_data = \lib\db\users::get($this->user_id);
		if(isset($user_data['user_permission']))
		{
			$this->user_permission = $user_data['user_permission'];
			$_SESSION['user']['permission'] = $this->user_permission;
		}
	}

	/**
	 * check access users
	 *
	 * @param      <type>  $_caller  The caller
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function access($_caller)
	{
		if(!$this->user_id)
		{
			return false;
		}

		if(empty($this->perm_list))
		{
			return true;
		}

		$this->caller($_caller);

		if(isset($this->caller['need_check']))
		{
			$this->load_user_data();
		}

		if(isset($this->caller['need_verify']))
		{
			$this->load_user_data();
			// and verify users !
		}

		if($this->user_permission === 'admin')
		{
			return true;
		}

		$explode = explode(',', $this->user_permission);

		if(isset($this->caller['key']))
		{
			if(in_array($this->caller['key'], $explode))
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
	private function caller($_caller)
	{
		$caller              = array_column($this->perm_list, 'caller');
		$caller              = array_combine(array_keys($this->perm_list), $caller);
		$key                 = array_search($_caller, $caller);
		$this->caller        = isset($this->perm_list[$key]) ? $this->perm_list[$key] : null;
		$this->caller['key'] = $key;
	}


	/**
	 * return the perm list
	 */
	public function list($_group = null)
	{
		return $this->perm_list;
	}
}
?>