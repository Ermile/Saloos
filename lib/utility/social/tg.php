<?php
namespace lib\utility\social;

/** telegram **/
class tg
{
	/**
	 * this library get and send telegram messages
	 * v2.7
	 */
	public static $saveLog  = true;
	public static $response = null;
	public static $cmd      = null;

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
		self::$response = $message;
		return $message;
	}


	/**
	 * handle response and return needed key if exist
	 * @param  [type] $_needle [description]
	 * @return [type]          [description]
	 */
	static function response($_needle = null, $_arg = 'id')
	{
		$data = null;

		switch ($_needle)
		{
			case 'update_id':
				if(isset(self::$response['update_id']))
				{
					$data = self::$response['update_id'];
				}
				break;

			case 'message_id':
				if(isset(self::$response['message']['message_id']))
				{
					$data = self::$response['message']['message_id'];
				}
				elseif(isset(self::$response['callback_query']['message']['message_id']))
				{
					$data = self::$response['callback_query']['message']['message_id'];
				}
				break;

			case 'from':
				if(isset(self::$response['message']['from']))
				{
					$data = self::$response['message']['from'];
				}
				elseif(isset(self::$response['callback_query']['from']))
				{
					$data = self::$response['callback_query']['from'];
				}
				if($_arg)
				{
					$data = $data[$_arg];
				}
				break;

			case 'chat':
				if(isset(self::$response['message']['chat']))
				{
					$data = self::$response['message']['chat'];
				}
				elseif(isset(self::$response['callback_query']['message']['chat']))
				{
					$data = self::$response['callback_query']['message']['chat'];
				}
				if($_arg)
				{
					$data = $data[$_arg];
				}
				break;

			case 'text':
				if(isset(self::$response['message']['text']))
				{
					$data = self::$response['message']['text'];
				}
				elseif(isset(self::$response['callback_query']['data']))
				{
					$data = 'cb_'.self::$response['callback_query']['data'];
				}
				break;

			default:
				break;
		}

		return $data;
	}


	/**
	 * seperate input text to command
	 * @param  [type] $_input [description]
	 * @return [type]         [description]
	 */
	public static function cmd($_input = null)
	{
		// define variable
		$cmd =
		[
			'text'  => null,
			'command'  => null,
			'optional' => null,
			'argument' => null,
		];
		// if user dont pass input string use response text
		if(!$_input)
		{
			$_input = self::response('text');
		}
		$cmd['text'] = $_input;
		$text = explode(' ', $_input);
		if(isset($text[0]))
		{
			$cmd['command'] = $text[0];
			if(isset($text[1]))
			{
				$cmd['optional'] = $text[1];
				if(isset($text[2]))
				{
					$cmd['argument'] = $text[2];
				}
			}
		}
		// return analysed text given from user
		return $cmd;
	}


	/**
	 * save log of process into file
	 * @param  [type] $_data [description]
	 * @return [type]        [description]
	 */
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
			$_url = \lib\utility\option::get('telegram', 'meta', 'hook');
		}
		$data = ['url' => $_url];
		// if (!is_null($_file))
		// {
		// 	$data['certificate'] = \CURLFile($_file);
		// }
		return self::executeCurl('setWebhook', $data, 'description') .': '. $_url;
	}


	/**
	 * execute telegram method
	 * @param  [type] $_name [description]
	 * @param  [type] $_args [description]
	 * @return [type]        [description]
	 */
	static function __callStatic($_name, $_args)
	{
		if(isset($_args[0]))
		{
			$_args = $_args[0];
		}
		return self::executeCurl($_name, $_args);
	}


	/**
	 * Execute cURL call
	 *
	 * @param string     $_method Action to execute
	 * @param array|null $_data   Data to attach to the execution
	 *
	 * @return mixed Result of the cURL call
	 */
	public static function executeCurl($_method, array $_data = null, $_output = null)
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
			return 'telegram is off!';
		// get custom api key in custom conditon
		if(isset($_data['api_key']))
		{
			$mykey = $_data['api_key'];
			unset($_data['api_key']);
		}
		else
		{
			$mykey = \lib\utility\option::get('telegram', 'meta', 'key');
			// get key and botname
			// $mybot = \lib\utility\option::get('telegram', 'meta', 'bot');

		}
		// if key is not correct return
		if(strlen($mykey) < 20)
			return 'api key is not correct!';

		$ch = curl_init();
		if ($ch === false)
		{
			return 'Curl failed to initialize';
		}
		$_url   = "https://api.telegram.org/bot$mykey/$_method";

		$curlConfig =
		[
			CURLOPT_URL            => "https://api.telegram.org/bot$mykey/$_method",
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			// CURLOPT_HEADER         => true, // get header
			CURLOPT_SAFE_UPLOAD    => true,
			CURLOPT_SSL_VERIFYPEER => false,
		];
		curl_setopt_array($ch, $curlConfig);

		if (!empty($_data))
		{
			curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($_data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		}
		if(Tld === 'dev')
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$result = curl_exec($ch);
		if ($result === false)
		{
			return curl_error($ch). ':'. curl_errno($ch);
		}
		if (empty($result) | is_null($result))
		{
			return 'Empty server response';
		}
		curl_close($ch);
		//Logging curl requests
		if(substr($result, 0,1) === "{")
		{
			$result = json_decode($result, true);
			if($_output && isset($result[$_output]))
			{
				$result = $result[$_output];
			}
		}
		self::saveLog($result);
		// return result
		return $result;
	}
}
?>