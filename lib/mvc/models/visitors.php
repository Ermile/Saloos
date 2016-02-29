<?php
namespace lib\mvc\models;

trait visitors
{
	/**
	 * add visitors to related db
	 */
	public function addVisitor()
	{
		// var_dump(222);
		// return 0;
		// this function add each visitor detail in visitors table
		// var_dump($_SERVER['REMOTE_ADDR']);
		$url = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME']
				.( $_SERVER["SERVER_PORT"] != "80"? ":".$_SERVER["SERVER_PORT"]: '' ).$_SERVER['REQUEST_URI'];

		if (strpos($url,'favicon.ico') !== false)
			return false;

		$referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: null;
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$robot = 'no';
		$botlist = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
			"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
			"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
			"crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
			"msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
			"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
			"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
			"Butterfly","Twitturls","Me.dium","Twiceler", "inoreader");
		foreach($botlist as $bot)
		{
			if(strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
				$robot = 'yes';
		}

		$userid = isset($_SESSION['user']['id'])? $_SESSION['user']['id']: null;

		$qry		= $this->sql()->tableVisitors()
					->setVisitor_ip(ClientIP)
					->setVisitor_url(urlencode($url))
					->setVisitor_agent(urlencode($agent))
					->setVisitor_referer(urlencode($referer))
					->setVisitor_robot($robot)
					->setUser_id($userid)
					->setVisitor_createdate(date('Y-m-d H:i:s'));
		$sql		= $qry->insert();

		$this->commit(function()
		{
			// \lib\debug::true("Register sms successfully");
		});

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			// \lib\debug::fatal("Register sms failed!");
		});

		// $sQl = new dbconnection_lib;
		// $sQl->query("COMMIT");
		// $sQl->query("START TRANSACTION");
	}


}
?>
