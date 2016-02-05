<?php
namespace lib;
class utility
{
	// filter post and safe it
	public static function post($_name = null, $_type = null, $_arg = null)
	{
		if(!$_name)
			return $_POST;
		elseif(isset($_POST[$_name]))
		{
			if(is_array($_POST[$_name]))
				$myvalue = $_POST[$_name];
			else
				$myvalue = htmlspecialchars($_POST[$_name], ENT_QUOTES | ENT_HTML5 , 'UTF-8');


			// if set filter use filter class to clear input value
			if($_type === 'filter')
			{
				if(method_exists('\lib\utility\Filter', $_name))
					$myvalue = \lib\utility\Filter::$_name($myvalue, $_arg);
			}
			// for password user hasher parameter for hash post value
			elseif($_type === 'hash')
				$myvalue = self::hasher($myvalue);

			return $myvalue;
		}
		return null;
	}

	// filter get and safe it
	public static function get($_name = null, $_arg = null)
	{
		$myget = array();
		foreach ($_GET as $key => &$value)
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
		$_GET = $myget;
		unset($myget);

		if($_name)
			return isset($_GET[$_name])? $_GET[$_name] : null;

		elseif(!empty($_GET))
		{
			if($_arg === 'raw')
				return $_GET;
			else
				return ($_arg? '?': null).http_build_query($_GET);
		}

		return null;
	}

	// Call this funtion for encode or decode your password.
	// If you pass hashed password func verify that, else create a new pass to save in db
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

	// create a random code for use in verification
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

	// convert datetime to human timing for better reading
	public static function humanTiming($_time, $_max = 'ultimate', $_format = "Y/m/d", $_lang = 'en')
	{
		// auto convert with strtotime function
		$_time = strtotime($_time);
		$time_diff  = time() - $_time; // to get the time since that moment
		$tokens = array (
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

		$_max = array_search(T_($_max), $tokens);

		foreach ($tokens as $unit => $text)
		{
			if ($time_diff < $unit)
				continue;
			// if time diff less than user request change it to humansizing
			if($time_diff < $_max || $_max == T_('ultimate'))
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
}
?>