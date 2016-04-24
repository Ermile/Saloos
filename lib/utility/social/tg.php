<?php
namespace lib\utility\social;

/** telegram **/
class tg
{
	/**
	 * this library get and send telegram messages
	 * v1.0
	 */

	/**
	 * hook telegram messages
	 * @param  boolean $_save [description]
	 * @return [type]         [description]
	 */
	public static function hook($_save = false)
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
			return 'telegram is off!';

		$message = json_decode(file_get_contents('php://input'), true);
		if($_save)
		{
			file_put_contents('tg.json', json_encode($message). "\r\n", FILE_APPEND);
		}
		return $message;
	}


	/**
	 * execute telegram method
	 * @param  [type] $_name [description]
	 * @param  [type] $_args [description]
	 * @return [type]        [description]
	 */
	static function __callStatic($_name, $_args)
	{
		return self::execute($_name, $_args);
	}


	/**
	 * execute command to telegram server
	 * @param  [type] $_url     [description]
	 * @param  [type] $_content [description]
	 * @return [type]           [description]
	 */
	private static function execute($_method, $_content)
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
			return 'telegram is off!';
		// get key and botname
		$mykey = \lib\utility\option::get('telegram', 'meta', 'key');
		$mybot = \lib\utility\option::get('telegram', 'meta', 'bot');
		// if key is not correct return
		if(strlen(!$mykey) < 20)
			return 'api key is not correct!';

		$url   = "https://api.telegram.org/bot$mykey/$_method";
		$ch    = curl_init();
		curl_setopt($ch, CURLOPT_URL, $_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_content));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		// return result
		return $server_output;
	}
}
?>