<?php
namespace lib\utility;

/** Visitor: handle visitor details **/
class visitor
{
	/**
	 * this library get visitor detail and do some work on it
	 * v1.0
	 */

	// declare private static variable to save options
	private static $visitor;
	private static $link;
	private static $result;



	/**
	 * save a visitor in database
	 * @return [type] [description]
	 */
	public static function save()
	{
		// create link to database
		$connect = self::createLink();
		if($connect)
		{
			// create a query string
			$qry     = self::create_query();
			// execute query and save result
			$result  = @mysqli_query(self::$link, $qry);
			// return resul
			return $result;
		}
		return $connect;
	}


	/**
	 * create link to database if not exist
	 * @param  boolean $_force [description]
	 * @return [type]          [description]
	 */
	private static function createLink($_force = false)
	{
		if(!self::$link || $_force)
		{
			// open database connection and create link
			if(!\lib\db::connect('[tools]'))
			{
				// cant connect to database
				return false;
			}
			// save link as global variable
			self::$link = \lib\db::$link;
			return true;
		}
		return true;
	}


	/**
	 * create final query string to add new record to visitors table
	 * @return [string] contain insert query string
	 */
	public static function create_query()
	{
		// declare variables
		self::$visitor['`visitor_ip`']         = ClientIP;
		self::$visitor['`url_id`']             = self::checkDetailExist('url',     self::url());
		self::$visitor['`agent_id`']           = self::checkDetailExist('agent',   self::agent());
		self::$visitor['`url_idreferer`']      = self::checkDetailExist('url',     self::referer());
		self::$visitor['`user_id`']            = self::user_id();
		self::$visitor['`visitor_createdate`'] = "'".date('Y-m-d H:i:s')."'";

		// create query string
		$qry_fields = implode(', ', array_keys(self::$visitor));
		$qry_values = implode(', ', self::$visitor);
		$qry = "INSERT INTO visitors ( $qry_fields ) VALUES ( $qry_values );";
		// return query
		return $qry;
	}


	/**
	 * check value exist in table if not add new one
	 * @param  [type] $_table name of table
	 * @param  [type] $_value value to check
	 * @return [type]         final id
	 */
	public static function checkDetailExist($_table, $_value)
	{
		// create link to database
		self::createLink();
		$default = 0;
		$qry     = "SELECT * FROM $_table"."s WHERE $_table".'_'."$_table = '$_value';";
		// run qry and save result
		$result  = @mysqli_query(self::$link, $qry);
		// if result is not mysqli result return false
		if(!is_a($result, 'mysqli_result'))
		{
			// no record exist
			return 'NULL';
		}
		// if has result return id
		if($result && $row = @mysqli_fetch_assoc($result))
		{
			if(isset($row['id']))
			{
				return $row['id'];
			}
			return $default;
		}

		// create insert query to add new record
		$qry     = "INSERT INTO $_table"."s ( $_table".'_'."$_table ) VALUES ( '$_value' );";
		if($_table === 'agent')
		{
			$is_bot  = self::isBot();
			$qry     = "INSERT INTO $_table"."s ( $_table".'_'."$_table, `agent_robot` ) VALUES ( '$_value', $is_bot );";
		}
		elseif($_table === 'url')
		{
			$is_external  = self::isExternal($_value);
			$qry     = "INSERT INTO $_table"."s ( $_table".'_'."$_table, `url_external` ) VALUES ( '$_value', $is_external );";
		}
		// execute query
		$result  = @mysqli_query(self::$link, $qry);
		// give last insert id
		$last_id = @mysqli_insert_id(self::$link);
		// if have last insert it return it
		if($last_id)
		{
			return $last_id;
		}
		// return default value
		return $default;
	}


	/**
	 * return current url
	 * @return [type] [description]
	 */
	public static function url($_encode = true)
	{
		$url = null;
		// get protocol
		$url = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://';
		// get name
		$url .= $_SERVER['SERVER_NAME'];
		// get port
		$url .= $_SERVER["SERVER_PORT"] != "80"? ":".$_SERVER["SERVER_PORT"]: '';
		// get request url
		$url .= $_SERVER['REQUEST_URI'];
		// if user want encode referer
		if($_encode)
		{
			$url = urlencode($url);
		}
		// return result
		return $url;
	}


	/**
	 * return user_id if loginned to system
	 * @return [type] [description]
	 */
	public static function user_id()
	{
		$userid = isset($_SESSION['user']['id'])? $_SESSION['user']['id']: 'NULL';

		return $userid;
	}


	/**
	 * return referer of visitor in current page
	 * @return [type] [description]
	 */
	public static function referer($_encode = true)
	{
		$referer = null;
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$referer = $_SERVER['HTTP_REFERER'];
		}
		// if user want encode referer
		if($_encode)
		{
			$referer = urlencode($referer);
		}

		return $referer;
	}


	/**
	 * return agent of visitor in current page
	 * @return [type] [description]
	 */
	public static function agent($_encode = true)
	{
		$agent = null;
		if(isset($_SERVER['HTTP_USER_AGENT']))
		{
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		// if user want encode referer
		if($_encode)
		{
			$agent = urlencode($agent);
		}
		return $agent;
	}


	/**
	 * compare two url and say hase diferrent host or not
	 * @return boolean [description]
	 */
	public static function isExternal($_url)
	{
		$_url = urldecode($_url);
		$external = parse_url($_url, PHP_URL_HOST);
		if($external !== Service)
		{
			// return true if not same
			return 1;
		}
		// return default value
		return 0;
	}

	/**
	 * check current user is bot or not
	 * @return boolean [description]
	 */
	public static function isBot()
	{
		$robot   = 'NULL';
		$agent   = self::agent();
		$botlist =
		[
			"Teoma",
			"alexa",
			"froogle",
			"Gigabot",
			"inktomi",
			"looksmart",
			"URL_Spider_SQL",
			"Firefly",
			"NationalDirectory",
			"Ask Jeeves",
			"TECNOSEEK",
			"InfoSeek",
			"WebFindBot",
			"girafabot",
			"crawler",
			"www.galaxy.com",
			"Googlebot",
			"Scooter",
			"Slurp",
			"msnbot",
			"appie",
			"FAST",
			"WebBug",
			"Spade",
			"ZyBorg",
			"rabaz",
			"Baiduspider",
			"Feedfetcher-Google",
			"TechnoratiSnoop",
			"Rankivabot",
			"Mediapartners-Google",
			"Sogou web spider",
			"WebAlta Crawler",
			"TweetmemeBot",
			"Butterfly",
			"Twitturls",
			"Me.dium",
			"Twiceler",
			"inoreader",
			"yoozBot",
		];

		foreach($botlist as $bot)
		{
			if(strpos($agent, $bot) !== false)
			{
				$robot = true;
			}
		}
		// return result
		return $robot;
	}


	/**
	 * show visitor result
	 * @return [type] [description]
	 */
	public static function chart()
	{
		self::createLink();
		/**
		 add getting unique visitor in next update
		 */

		$qry =
			"SELECT
				date_format(visitor_createdate,'%Y-%m-%d') as date,
				0 as bots,
				count(*) as humans,
				count(*) as total

				FROM `visitors`

				GROUP BY date
				ORDER BY date ASC
				LIMIT 0, 10";
		$result  = @mysqli_query(self::$link, $qry);
		$result  = mysqli_fetch_all($result, MYSQLI_ASSOC);

		$result_total = array_column($result, 'total');
		self::$result['chart'] = $result;
		self::$result['total'] = array_sum($result_total);
		self::$result['max']   = max($result_total);
		self::$result['min']   = min($result_total);
		// return result
		return $result;
	}


	/**
	 * return top pages visited on this site
	 * @return [type] [description]
	 */
	public static function top_pages($_count = 10)
	{
		self::createLink();
		$qry =
			"SELECT
				urls.url_url as url,
				count(visitors.id) as total
			FROM urls
			INNER JOIN visitors ON urls.id = visitors.url_id
			GROUP BY visitors.url_id
			ORDER BY total DESC
			LIMIT 0, $_count";
		$result  = @mysqli_query(self::$link, $qry);
		$result  = mysqli_fetch_all($result, MYSQLI_ASSOC);

		foreach ($result as $key => $row)
		{
			$result[$key]['url'] = urldecode($row['url']);
			if(strpos($result[$key]['url'], 'http://') !== false)
			{
				$result[$key]['url'] = substr($result[$key]['url'], 7);
			}

		}
		return $result;
	}
}
?>