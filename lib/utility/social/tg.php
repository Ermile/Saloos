<?php
namespace lib\utility\social;

/** telegram **/
class tg
{
	/**
	 * this library get and send telegram messages
	 * v1.3
	 */
	public static $saveLog = true;

	/**
	 * hook telegram messages
	 * @param  boolean $_save [description]
	 * @return [type]         [description]
	 */
	public static function hook()
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
			return 'telegram is off!';
		$message = json_decode(file_get_contents('php://input'), true);
		self::saveLog($message);
		return $message;
	}


	private static function saveLog($_data)
	{
		if(self::$saveLog)
		{
			file_put_contents('tg.json', json_encode($_data). "\r\n", FILE_APPEND);
		}
	}

	/**
	 * setWebhook for telegram
	 * @param string $_url  [description]
	 * @param [type] $_file [description]
	 */
	public static function setWebhook($_url = '', $_file = null)
	{
		if(empty($_url))
		{
			$tld = MainTld;
			if($tld === '.dev')
			{
				$tld = '.com';
			}
			$_url = 'https://'. Domain. $tld. '/saloos_tg/';
			$_url .= \lib\utility\option::get('telegram', 'meta', 'hook') . '/';
		}
		$data = ['url' => $_url];
		// if (!is_null($_file))
		// {
		// 	$data['certificate'] = \CURLFile($_file);
		// }
		return self::executeCurl('setWebhook', $data, 'description');
	}


	/**
	 * execute telegram method
	 * @param  [type] $_name [description]
	 * @param  [type] $_args [description]
	 * @return [type]        [description]
	 */
	static function __callStatic($_name, $_args)
	{
		return self::executeCurl($_name, $_args);
	}


	/**
	 * execute command to telegram server
	 * @param  [type] $_url     [description]
	 * @param  [type] $_content [description]
	 * @return [type]           [description]
	 */
	private static function executeCurl($_method, $_content, $_output = null)
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
			return 'telegram is off!';
		// get key and botname
		$mykey = \lib\utility\option::get('telegram', 'meta', 'key');
		$mybot = \lib\utility\option::get('telegram', 'meta', 'bot');
		// if key is not correct return
		if(strlen($mykey) < 20)
			return 'api key is not correct!';

		$_url   = "https://api.telegram.org/bot$mykey/$_method";
		$ch    = curl_init();
		curl_setopt($ch, CURLOPT_URL, $_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_content));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close ($ch);

		if(substr($server_output, 0,1) === "{")
		{
			$server_output = json_decode($server_output, true);
			if($_output && isset($server_output[$_output]))
			{
				$server_output = $server_output[$_output];
			}
		}
		self::saveLog($server_output);
		// return result
		return $server_output;
	}
}
?>