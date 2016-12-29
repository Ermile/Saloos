<?php
namespace lib;
class utility
{
	public static $POST;
	public static $GET;
	public static $COOKIE;
	public static $FILES;


	/**
	 * filter post and safe it
	 * @param  [type] $_name [description]
	 * @param  [type] $_type [description]
	 * @param  [type] $_arg  [description]
	 * @return [type]        [description]
	 */
	public static function post($_name = null, $_type = null, $_arg = null)
	{
		if(!self::$POST)
		{
			self::$POST = utility\safe::safe($_POST);
		}
		$myvalue = null;
		if(!$_name)
		{
			return self::$POST;
		}
		elseif(is_array($_name))
		{
			$_name = current($_name);
			foreach (self::$POST as $key => $value)
			{
				if (strpos($key, $_name) === 0)
				{
					$myvalue[$key] = $value;
				}
			}
			return $myvalue;
		}
		elseif(isset(self::$POST[$_name]))
		{
			if(is_array(self::$POST[$_name]))
				$myvalue = self::$POST[$_name];
			else
				$myvalue = self::$POST[$_name];


			// if set filter use filter class to clear input value
			if($_type === 'filter')
			{
				if(method_exists('\lib\utility\filter', $_name))
					$myvalue = \lib\utility\filter::$_name($myvalue, $_arg);
			}
			// for password user hasher parameter for hash post value
			elseif($_type === 'hash')
			{
				if($_arg)
				{
					$myvalue = self::hasher($myvalue);
				}
				elseif(strlen($myvalue) > 4 && strlen(strlen($myvalue) < 50))
				{
					$myvalue = self::hasher($myvalue);
				}
				else
				{
					$myvalue = null;
				}
			}

			return $myvalue;
		}

		return null;
	}


	/**
	 * get files
	 *
	 * @param      <type>  $_name  The name
	 */
	public static function files($_name = null)
	{
		if(!self::$FILES)
		{
			self::$FILES = $_FILES;
		}

		if($_name)
		{
			if(isset(self::$FILES[$_name]))
			{
				return self::$FILES[$_name];
			}
			else
			{
				return null;
			}
		}
		return self::$FILES;
	}


	/**
	 * filter get and safe it
	 * @param  [type] $_name [description]
	 * @param  [type] $_arg  [description]
	 * @return [type]        [description]
	 */
	public static function get($_name = null, $_arg = null)
	{
		if(!self::$GET)
		{
			self::$GET = utility\safe::safe($_GET);
		}
		$myget = array();
		foreach (self::$GET as $key => &$value)
		{
			$pos = strpos($key, '=');
			if($pos)
			{
				$key_t = substr($key, 0, $pos);
				$value = substr($key, $pos+1);
				$myget[$key_t] = $value;
			}
			else
			{
				$myget[$key] = $value;
			}
		}
		self::$GET = $myget;
		unset($myget);

		if($_name)
			return isset(self::$GET[$_name])? self::$GET[$_name] : null;

		elseif(!empty(self::$GET))
		{
			if($_arg === 'raw')
				return self::$GET;
			else
				return ($_arg? '?': null).http_build_query(self::$GET);
		}

		return null;
	}


	/**
	 * filter cookie and safe it
	 * @param  string $_name unsafe cookie key
	 * @return string        safe cookie
	 */
	public static function cookie($_name = null)
	{
		if(!self::$COOKIE)
		{
			self::$COOKIE = utility\safe::safe($_COOKIE);
		}
		if($_name)
		{
			if(array_key_exists($_name, self::$COOKIE))
			{
				return self::$COOKIE[$_name];
			}
			else
			{
				return null;
			}
		}else
		{
			return self::$COOKIE;
		}
	}


	/**
	 * Call this funtion for encode or decode your password.
	 * If you pass hashed password func verify that,
	 * else create a new pass to save in db
	 * @param  [type] $_plainPassword  [description]
	 * @param  [type] $_hashedPassword [description]
	 * @return [type]                  [description]
	 */
	public static function hasher($_plainPassword, $_hashedPassword = null)
	{
		// custom text to add in start and end of password
		$mystart        = '^_^$~*~';
		$myend          = '~_~!^_^';
		$_plainPassword = $mystart. $_plainPassword. $myend;
		$_plainPassword = md5($_plainPassword);

		// if requrest verify pass check with
		if($_hashedPassword)
			$myresult    = password_verify($_plainPassword, $_hashedPassword);

		else
		{
			// create option for creating hash cost
			$myoptions   = array('cost' => 7 );
			$myresult    = password_hash($_plainPassword, PASSWORD_BCRYPT, $myoptions);
		}

		return $myresult;
	}


	/**
	 * create a random code for use in verification
	 * @param  integer $_length [description]
	 * @param  boolean $type    [description]
	 * @return [type]           [description]
	 */
	public static function randomCode($_length = 4, $type = true)
	{
		$mystring	= '';
		if($type === true)
			$mycharacters = "23456789";
		elseif($type == 'all')
			$mycharacters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		elseif($type == 'protected')
			$mycharacters = "123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
		else
			$mycharacters = "23456789ABCDEFHJKLMNPRTVWXYZ";

		for ($p = 0; $p < $_length; $p++)
			$mystring .= $mycharacters[mt_rand(0, strlen($mycharacters)-1)];

		return $mystring;
	}


	/**
	 * convert datetime to human timing for better reading
	 * @param  [type] $_time   [description]
	 * @param  string $_max    [description]
	 * @param  string $_format [description]
	 * @param  string $_lang   [description]
	 * @return [type]          [description]
	 */
	public static function humanTiming($_time, $_max = 'ultimate', $_format = "Y/m/d", $_lang = 'en')
	{
		// auto convert with strtotime function
		$_time     = strtotime($_time);
		$time_diff = time() - $_time; // to get the time since that moment
		$tokens    = array (
			31536000 => T_('year'),
			2592000  => T_('month'),
			604800   => T_('week'),
			86400    => T_('day'),
			3600     => T_('hour'),
			60       => T_('minute'),
			1        => T_('second')
			);
		if($time_diff < 10)
			return T_('A few seconds ago');

		$type = array_search(T_($_max), $tokens);

		foreach ($tokens as $unit => $text)
		{
			if ($time_diff < $unit)
				continue;
			// if time diff less than user request change it to humansizing
			if($time_diff < $type || $_max === 'ultimate')
			{
				$numberOfUnits = floor($time_diff / $unit);
				return $numberOfUnits.' '.$text.(($numberOfUnits>1)? T_('s '):' ').T_('ago');
			}
			// else show it dependig on current language
			else
			{
				if($_lang == 'fa')
				{
					return \lib\utility\jdate::date($_format, $_time);
				}
				else
				{
					return date($_format, $_time);
				}
			}
		}
	}


    /**
     * check saloos language and if needed convert to persian date
     * else show default date
     * @param  [type] $_date [description]
     * @return [type]        [description]
     */
    public static function date($_format, $_stamp = false, $_type = false, $_persianChar = true)
    {
    	$result = null;

    	if(strlen($_stamp) < 2)
    	{
    		$_stamp = false;
    	}

        // get target language
    	if($_type === 'default')
    	{
    		$_type = \lib\define::get_language('default');
    	}
    	elseif($_type === 'current')
    	{
    		$_type = \lib\define::get_language();
    	}

        // if need persian use it else use default date function
    	if($_type === true || $_type === 'fa')
    	{
    		$result = \lib\utility\jdate::date($_format, $_stamp, $_persianChar);
    	}
    	else
    	{
    		if($_stamp)
    		{
    			$result = date($_format, $_stamp);
    		}
    		else
    		{
    			$result = date($_format);
    		}
    	}

    	return $result;
    }
}
?>