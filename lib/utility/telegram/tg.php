<?php
namespace lib\utility\telegram;

/** telegram **/
class tg
{
	/**
	 * this library get and send telegram messages
	 * v8.0
	 */
	public static $api_key     = null;
	public static $name        = null;
	public static $botan       = null;
	public static $cmd         = null;
	public static $cmdFolder   = null;
	public static $saveLog     = true;
	public static $hook        = null;
	public static $fill        = null;
	public static $defaultText = 'Undefined';
	public static $priority    =
	[
		'callback',
		'user',
		'menu',
		'simple',
		'conversation',
	];



	/**
	 * handle tg requests
	 * @return [type] [description]
	 */
	public static function run($_allowSample = false)
	{
		// run hook and save it on $hook value
		self::hook();

		// generate response from defined commands
		$ans    = self::generateResponse();
		$result = [];
		if(!$ans && $_allowSample)
		{
			$ans = self::generateResponse(true);
		}
		// if we have some answer send each answer seperated
		if(isset($ans[0]))
		{
			foreach ($ans as $key => $eachAns)
			{
				$result[] = self::sendResponse($eachAns);
			}
		}
		// else run single answer
		else
		{
			$result[] = self::sendResponse($ans);
		}
		// return result of sending
		return $result;
	}


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
		self::$hook = json_decode(file_get_contents('php://input'), true);
		// save log if allow
		self::saveLog(self::$hook);
		// detect cmd and save it in static value
		self::cmd(self::response('text'));
		// if botan is set then save analytics with botan.io
		self::botan();
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
			// define user detail array
			if(isset($_data['message']['from']) && $user_id = self::response('from'))
			{
				$userDetail =
				[
					'cat'    => 'telegram',
					'key'    => 'user',
					'value'  => $user_id,
					'meta'   => $_data['message']['from'],
					'status' => 'disable'
				];
				// save in options table
				\lib\utility\option::set($userDetail, true);
			}
		}
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
			'text'     => null,
			'command'  => null,
			'optional' => null,
			'argument' => null,
		];
		// if debug mode is enable give text from get parameter
		if(!$_input && \lib\utility\option::get('telegram', 'meta', 'debug'))
		{
			$_input = \lib\utility::get('text');
		}
		// save input value as text
		$cmd['text'] = $_input;
		// seperate text by space
		$text = explode(' ', $_input);
		// if we have parameter 1 save it as command
		if(isset($text[0]))
		{
			$cmd['command'] = $text[0];
			// if we have parameter 2 save it as optional
			if(isset($text[1]))
			{
				$cmd['optional'] = $text[1];
				// if we have parameter 3 save it as argument
				if(isset($text[2]))
				{
					$cmd['argument'] = $text[2];
				}
			}
		}
		// save cmd as global cmd value
		self::$cmd = $cmd;
		// return analysed text given from user
		return $cmd;
	}


	/**
	 * default action to handle message texts
	 * @param  [type] [description]
	 * @return [type]       [description]
	 */
	private static function generateResponse($forceSample = null)
	{
		$answer  = null;
		// read from saloos command template
		$cmdFolder = __NAMESPACE__ .'\commands\\';

		// use user defined command
		if(!$forceSample && self::$cmdFolder)
		{
			$cmdFolder = self::$cmdFolder;
		}
		foreach (self::$priority as $class)
		{
			$funcName = $cmdFolder. $class.'::exec';
			// generate func name
			if(is_callable($funcName))
			{
				// get response
				$answer = call_user_func($funcName, self::$cmd);
				// if has response break loop
				if($answer)
				{
					break;
				}
			}
		}
		// if we dont have answer text then use default text
		if(!$answer)
		{
			if(self::response('chat', 'type') === 'group')
			{
				// if saloos bot joied to group show thanks message
				if(self::response('new_chat_member', 'username') === self::$name)
				{
					$msg = "Thanks for using me!\r\n\nI'm Bot.";
					$msg = "با تشکر از شما عزیزان به خاطر دعوت از من!\r\n\nمن یک ربات هستم.";
					$answer = ['text' => $msg ];
				}
			}
			elseif(\lib\utility\option::get('telegram', 'meta', 'debug'))
			{
				// then if not exist set default text
				$answer = ['text' => self::$defaultText];
			}
		}
		return $answer;
	}


	/**
	 * generate response and sending message
	 * @return [type] result of sending
	 */
	public static function sendResponse($_prop)
	{
		// if method is not set user sendmessage method
		if(!isset($_prop['method']))
		{
			if(isset($_prop['text']))
			{
				$_prop['method'] = 'sendMessage';
			}
			else
			{
				return 'method is not set!';
			}
		}

		switch ($_prop['method'])
		{
			// create send message format
			case 'sendMessage':
				// require chat id
				$_prop['chat_id']    = self::response('chat');
				// add reply message id
				if(self::response('message_id'))
				{
					$_prop['reply_to_message_id'] = self::response('message_id');
				}
				break;


			case 'editMessageText':
			case 'editMessageCaption':
			case 'editMessageReplyMarkup':
				$_prop['chat_id']    = self::response('chat');
				$_prop['message_id'] = self::response('message_id');
				break;

			case 'sendPhoto':
			case 'sendAudio':
			case 'sendDocument':
			case 'sendSticker':
			case 'sendVideo':
			case 'sendVoice':
			case 'sendLocation':
			case 'sendVenue':
			case 'sendContact':
			case 'sendChatAction':
			default:
				// require chat id
				$_prop['chat_id']    = self::response('chat');
				break;
		}
		// if array key exist but is null
		if(array_key_exists('chat_id', $_prop) && is_null($_prop['chat_id']))
		{
			$_prop['chat_id'] = \lib\utility::get('id');
		}


		// if on answer we have callback analyse it and send answer
		if(isset($_prop['callback']) && isset($_prop['callback']['text']))
		{
			// generate callback query
			$data =
			[
				'callback_query_id' => self::response('callback_query_id'),
				'text'              => $_prop['callback']['text'],
			];
			if(isset($_prop['callback']['show_alert']))
			{
				$data['show_alert'] = $_prop['callback']['show_alert'];
			}
			// call callback answer
			self::answerCallbackQuery($data);
			// unset callback
			unset($_prop['callback']);
		}

		// replace values of text and markup
		$_prop = self::replaceFill($_prop);
		// decode markup if exist
		if(isset($_prop['reply_markup']))
		{
			$_prop['reply_markup'] = json_encode($_prop['reply_markup']);
			// self::$answer['force_reply'] = true;
		}
		// markdown is enable by default
		if(isset($_prop['text']) && !isset($_prop['parse_mode']))
		{
			$_prop['parse_mode'] = 'markdown';
		}
		// call bot send message func
		$funcName = 'self::'. $_prop['method'];
		$result   = call_user_func($funcName, $_prop);
		// return result of sending
		return $result;
	}


	/**
	 * replace fill values if exist
	 * @param  [type] $_data [description]
	 * @return [type]        [description]
	 */
	private static function replaceFill($_data)
	{
		if(!self::$fill)
		{
			return $_data;
		}

		// replace all texts
		if(isset($_data['text']))
		{
			foreach (self::$fill as $search => $replace)
			{
				$search	= '_'.$search.'_';
				$_data['text'] = str_replace($search, $replace, $_data['text']);
			}
		}

		// replace all texts
		if(isset($_data['caption']))
		{
			foreach (self::$fill as $search => $replace)
			{
				$search	= '_'.$search.'_';
				$_data['caption'] = str_replace($search, $replace, $_data['caption']);
			}
		}

		if(isset($_data['reply_markup']['keyboard']))
		{
			foreach ($_data['reply_markup']['keyboard'] as $itemRowKey => $itemRow)
			{
				foreach ($itemRow as $key => $itemValue)
				{
					if(!is_array($itemValue))
					{
						foreach (self::$fill as $search => $replace)
						{
							$search	= '_'.$search.'_';
							$newValue = str_replace($search, $replace, $itemValue);

							$_data['reply_markup']['keyboard'][$itemRowKey][$key] = $newValue;
						}
					}
				}
			}
		}
		return $_data;
	}


	/**
	 * handle response and return needed key if exist
	 * @param  [type] $_needle [description]
	 * @return [type]          [description]
	 */
	public static function response($_needle = null, $_arg = 'id')
	{
		$data = null;

		switch ($_needle)
		{
			case 'update_id':
				if(isset(self::$hook['update_id']))
				{
					$data = self::$hook['update_id'];
				}
				break;

			case 'message_id':
				if(isset(self::$hook['message']['message_id']))
				{
					$data = self::$hook['message']['message_id'];
				}
				elseif(isset(self::$hook['callback_query']['message']['message_id']))
				{
					$data = self::$hook['callback_query']['message']['message_id'];
				}
				break;

			case 'message':
				if(isset(self::$hook['message']))
				{
					$data = self::$hook['message'];
				}
				elseif(isset(self::$hook['callback_query']['message']))
				{
					$data = self::$hook['callback_query']['message'];
				}
				break;

			case 'callback_query_id':
				if(isset(self::$hook['callback_query']['id']))
				{
					$data = self::$hook['callback_query']['id'];
				}
				break;

			case 'from':
				if(isset(self::$hook['message']['from']))
				{
					$data = self::$hook['message']['from'];
				}
				elseif(isset(self::$hook['callback_query']['from']))
				{
					$data = self::$hook['callback_query']['from'];
				}
				if($_arg)
				{
					$data = $data[$_arg];
				}
				break;

			case 'chat':
			case 'new_chat_member':
			case 'new_chat_participant':
				if(isset(self::$hook['message'][$_needle]))
				{
					$data = self::$hook['message'][$_needle];
				}
				elseif(isset(self::$hook['callback_query']['message'][$_needle]))
				{
					$data = self::$hook['callback_query']['message'][$_needle];
				}
				if($_arg)
				{
					$data = $data[$_arg];
				}
				break;

			case 'text':
				if(isset(self::$hook['message']['text']))
				{
					$data = self::$hook['message']['text'];
				}
				elseif(isset(self::$hook['callback_query']['data']))
				{
					$data = 'cb_'.self::$hook['callback_query']['data'];
				}
				elseif(isset(self::$hook['message']['contact'])
					&& isset(self::$hook['message']['contact']['phone_number'])
				)
				{
					$data = 'type_phone_number '. self::$hook['message']['contact']['phone_number'];
				}
				elseif(isset(self::$hook['message']['location'])
					&& isset(self::$hook['message']['location']['longitude'])
					&& isset(self::$hook['message']['location']['latitude'])
				)
				{
					$data = 'type_location ';
					$data .= self::$hook['message']['location']['longitude']. ' ';
					$data .= self::$hook['message']['location']['latitude'];
				}


				// remove @bot_name
				$data = str_replace('@'.self::$name, '', $data);
				// trim text
				$data = trim($data);
				break;

			case 'contact':
				if(isset(self::$hook['message']['contact']))
				{
					$data = self::$hook['message']['contact'];
				}
				if($_arg && isset($data[$_arg]))
				{
					$data = $data[$_arg];
				}
				break;

			case 'location':
				if(isset(self::$hook['message']['location']))
				{
					$data = self::$hook['message']['location'];
				}
				if($_arg && isset($data[$_arg]))
				{
					$data = $data[$_arg];
				}
				break;

			default:
				break;
		}

		return $data;
	}



	public static function botan()
	{
		if(!isset(self::$botan))
		{
			return false;
		}
		$botan  = new Botan(self::$botan);
		if(!self::response('message'))
		{
			return 'message is not correct!';
		}
		$result = $botan->track(self::response('message'), self::response('text'));
		return $result;
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
		$answer = ['url' => $_url];
		// if (!is_null($_file))
		// {
		// 	$data['certificate'] = \CURLFile($_file);
		// }
		return self::executeCurl('setWebhook', $answer, 'description') .': '. $_url;
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
	 * @return mixed Result of the cURL call
	 */
	public static function executeCurl($_method = null, array $_data = null, $_output = null)
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
		{
			return 'telegram is off!';
		}
		// if method or data is not set return
		if(!$_method || !$_data)
		{
			return 'method or data is not set!';
		}

		// if api key is not set get it from options
		if(!self::$api_key)
		{
			self::$api_key = \lib\utility\option::get('telegram', 'meta', 'key');
		}
		// if key is not correct return
		if(strlen(self::$api_key) < 20)
		{
			return 'api key is not correct!';
		}

		// initialize curl
		$ch = curl_init();
		if ($ch === false)
		{
			return 'Curl failed to initialize';
		}

		$curlConfig =
		[
			CURLOPT_URL            => "https://api.telegram.org/bot".self::$api_key."/$_method",
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			// CURLOPT_HEADER         => true, // get header
			CURLOPT_SAFE_UPLOAD    => true,
			CURLOPT_SSL_VERIFYPEER => false,
		];
		curl_setopt_array($ch, $curlConfig);
		if (!empty($_data))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
			// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			// curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($_data));
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $_data);
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