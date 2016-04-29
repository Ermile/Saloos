<?php
namespace lib\utility\telegram\commands;
// use telegram class as bot
use \lib\utility\telegram\tg as bot;

class user
{
	/**
	 * execute user request and return best result
	 * @param  [type] $_cmd [description]
	 * @return [type]       [description]
	 */
	public static function exec($_cmd)
	{
		$response = null;
		switch ($_cmd['command'])
		{
			case '/start':
			case 'start':
			case 'شروع':
				$response = self::start();
				break;

			case '/about':
			case 'about':
			case 'درباره':
				$response = self::about();
				break;

			case '/contact':
			case 'contact':
			case 'تماس':
				$response = self::contact();
				break;

			default:
				break;
		}
		return $response;
	}


	/**
	 * start conversation
	 * @return [type] [description]
	 */
	public static function start()
	{
		$result =
		[
			[
				'text'         => "به *_fullName_* خوش آمدید.",
				'reply_markup' => menu::main(true),
			],
		];
		// on debug mode send made by ermile at the end of start msg
		if(\lib\utility\option::get('telegram', 'meta', 'debug'))
		{
			$result[] =
			[
				'text' => "Made by @Ermile",
			];
		}
		return $result;
	}


	/**
	 * show about message
	 * @return [type] [description]
	 */
	public static function about()
	{
		$result['text'] = "_about_";

		return $result;
	}


	/**
	 * show contact message
	 * @return [type] [description]
	 */
	public static function contact()
	{
		$result['method'] = "sendPhoto";
		$result['photo']  = new \CURLFile(realpath("static/images/telegram/contact.jpg"));
		$result['caption'] = "_contact_";

		return $result;
	}
}
?>