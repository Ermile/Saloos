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

			case '/contanct':
			case 'contanct':
			case 'تماس':
				$response = self::contact();
				break;

			default:
				break;
		}

		if(isset($response['text']))
		{
			$response['text'] = $response['text']. "\r\n\n\n". 'Made by @Ermile';
		}

		return $response;
	}


	/**
	 * start conversation
	 * @return [type] [description]
	 */
	public static function start()
	{
		$result['text'] = 'به *_fullName_* خوش آمدید.';
		$result['reply_markup'] =
		[
			'keyboard' =>
			[
				["آشنایی با _type_"],
				["درباره _type_"],
				["تماس با ما"],
			],
		];
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
		$result['text'] = "_contact_";
		return $result;
	}

}
?>