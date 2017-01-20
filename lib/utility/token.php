<?php
namespace lib\utility;
use \lib\utility;
use \lib\debug;

class token
{
	/**
	 * the api key
	 *
	 * @var        <type>
	 */
	private static $API_KEY = null;


	/**
	 * create
	 *
	 * @param      <type>   $_authorization  The authorization
	 * @param      boolean  $_type          The guest
	 *
	 * @return     string   ( description_of_the_return_value )
	 */
	private static function create_token($_parent, $_type, $_guest_token = false)
	{
		self::$API_KEY = null;
		$user_id       = null;
		$key           = null;

		if($_type == 'guest')
		{
			$user_id = \lib\db\users::signup_inspection();
			$key     = 'guest';
		}
		elseif(is_int($_type))
		{
			$user_id = $_type;
			$key     = 'user_token';
		}
		elseif($_type == 'tmp_login')
		{
			$user_id = null;
			$key     = 'tmp_login';
		}

		$date  = date("Y-m-d H:i:s");
		$token = "~Saloos~_!_". $user_id . $key. time(). rand(1,1000). $date;
		$token = utility::hasher($token);
		$meta  = [];

		$meta['time'] = $date;

		$guest_id = null;


		if($_guest_token)
		{
			$guest_token_type = self::get_type($_guest_token);
			if($guest_token_type == 'guest')
			{
				$guest_id = self::get_id($_guest_token);
			}
		}

		$meta['guest'] = $guest_id;

		$args  =
		[
			'user_id'      => $user_id,
			'parent_id'    => $_parent,
			'option_cat'   => 'token',
			'option_key'   => $key,
			'option_value' => $token,
			'option_meta'  => json_encode($meta, JSON_UNESCAPED_UNICODE),
		];
		\lib\db\options::insert($args);

		return $token;
	}


	/**
	 * check authorization
	 *
	 * @param      <type>   $_authorization  The authorization
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	private static function check($_authorization)
	{
		$api_key_parent = null;

		$where = ['option_value' => $_authorization, 'option_status' => 'enable'];

		$get   = \lib\db\options::get($where);

		if(!$get || empty($get) || ( isset($get[0]) && !array_key_exists('parent_id', $get[0])))
		{
			debug::error(T_("authorization faild (parent not found)"), 'authorization', 'access');
			return false;
		}

		$parent_id = $get[0]['parent_id'];

		if(!is_null($parent_id))
		{
			debug::error(T_("authorization faild (this authorization is not a api key)"), 'authorization', 'access');
			return false;
		}

		if(isset($get[0]['id']))
		{
			$api_key_parent = $get[0]['id'];
		}

		return $api_key_parent;
	}


	/**
	 * Creates a guest.
	 */
	public static function create_guest($_authorization)
	{
		$parent = self::check($_authorization);
		return self::create_token($parent, 'guest');
	}


	/**
	 * Creates a temporary login.
	 */
	public static function create_tmp_login($_authorization, $_guest_token = false)
	{
		$parent = self::check($_authorization);
		return self::create_token($parent, 'tmp_login', $_guest_token);
	}


	/**
	 * verify $_tmp_login
	 *
	 * @param      <type>  $_token  The temporary login token
	 * @param      <type>  $_guest_token      The guest token
	 */
	public static function verify($_token, $_user_id)
	{
		self::$API_KEY = null;
		$type = self::get_type($_token);
		if($type == 'tmp_login')
		{
			$max_life_time = 60 * 7; // 7 min
			$token_time = self::get_time($_token);

			if(!$token_time ||  \DateTime::createFromFormat('Y-m-d H:i:s', $token_time) === false)
			{
				debug::error(T_("Invalid token"), 'authorization', 'system');
				return false;
			}

			$now         = time();
			$token_time  = strtotime($token_time);
			$diff_seconds = $now - $token_time;

			if($diff_seconds > $max_life_time)
			{
				debug::error(T_("Invalid token"), 'authorization', 'time');
				return false;
			}

			$guest_token = self::get_meta_guest($_token);

			$user_token  = null;

			if($guest_token)
			{
				$where = ['id' => $guest_token, 'option_status' => 'enable'];
				$arg =
				[
					'user_id'    => $_user_id,
					'option_key' => 'user_token'
				];

				$update = \lib\db\options::update_on_error($arg, $where);
				if((int) \lib\db::affected_rows() == 1)
				{
					$user_token = \lib\db\options::get($where);
					if(isset($user_token[0]['value']))
					{
						$user_token = $user_token[0]['value'];
						return $user_token;
					}
					else
					{
						debug::error(T_("Invalid token"), 'authorization', 'access');
						return false;
					}
				}
				else
				{
					debug::error(T_("Invalid token"), 'authorization', 'access');
					return false;
				}
			}
			$parent     = null;
			$parent_id  = self::get_parent_id($_token);
			$get_parent = ['id' => $parent_id, 'option_status' => 'enable'];
			$get_parent = \lib\db\options::get($get_parent);

			if(isset($get_parent[0]['value']))
			{
				$parent = self::check($get_parent[0]['value']);
			}
			else
			{
				debug::error(T_("Invalid token (parent)"), 'authorization', 'access');
				return false;
			}

			if($parent)
			{
				$user_token = self::create_token($parent, $_user_id);
			}

			return $user_token;
		}
		else
		{
			debug::error(T_("Invalid token (tmp_login)"), 'authorization', 'access');
			return false;
		}

	}


	/**
	 * get token from db
	 *
	 * @param      <type>  $_authorization  The authorization
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function get($_authorization)
	{
		if(!self::$API_KEY)
		{
			$arg =
			[
				'option_cat'   => 'token',
				'option_value' => $_authorization,
				'limit'        => 1
			];
			$tmp = \lib\db\options::get($arg);
			if(isset($tmp[0]))
			{
				$tmp = $tmp[0];
			}
			self::$API_KEY = $tmp;
		}

		return self::$API_KEY;
	}


	/**
	 * get_type
	 * get_parent
	 * get_value
	 *
	 * @param      <type>  $_name  The name
	 * @param      <type>  $_arg   The argument
	 */
	public static function __callStatic($_name, $_authorization)
	{
		if(preg_match("/^(get)\_(.*)$/", $_name, $field))
		{
			self::get(...$_authorization);
			if(isset($field[2]))
			{
				$field = $field[2];
			}
			else
			{
				$field = null;
			}

			if($field == 'time')
			{
				$field = 'meta_time';
			}

			if(preg_match("/^(meta)\_(.*)$/", $field, $meta))
			{
				if(isset($meta[2]))
				{
					$meta = $meta[2];
					if(isset(self::$API_KEY['meta'][$meta]))
					{
						return self::$API_KEY['meta'][$meta];
					}
					else
					{
						return null;
					}
				}
				else
				{
					return null;
				}
			}

			// type of authorization is key of options table
			if($field == 'type')
			{
				$field = 'key';
			}

			if(isset(self::$API_KEY[$field]))
			{
				return self::$API_KEY[$field];
			}
		}
	}



	/**
	 * get token data to show
	 */
	public static function get_api_key($_user_id)
	{
		$where =
		[
			'user_id'       => $_user_id,
			'option_cat'    => 'token',
			'option_key'    => 'api_key',
			'option_status' => 'enable',
			'limit'         => 1
		];
		$api_key = \lib\db\options::get($where);

		if($api_key && isset($api_key[0]['value']))
		{
			return $api_key[0]['value'];
		}
	}


	/**
	 * Creates an api key.
	 *
	 * @param      string  $_user_id  The user identifier
	 *
	 * @return     string  ( description_of_the_return_value )
	 */
	public static function create_api_key($_user_id)
	{
		self::destroy_api_key($_user_id);

		$api_key = "!~Saloos~!". $_user_id. ':_$_:'. time(). "*Ermile*". rand(2, 200);
		$api_key = utility::hasher($api_key);

		$arg =
		[
			'user_id'      => $_user_id,
			'option_cat'   => 'token',
			'option_key'   => 'api_key',
			'option_value' => $api_key
		];
		$set = \lib\db\options::insert($arg);
		if($set)
		{
			return $api_key;
		}
	}


	/**
	 * destroy api keuy
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function destroy_api_key($_user_id)
	{
		$where =
		[
			'user_id'    => $_user_id,
			'option_cat' => 'token',
			'option_key' => 'api_key'
		];
		$set = ['option_status' => 'disable'];
		\lib\db\options::update_on_error($set, $where);
	}
}
?>