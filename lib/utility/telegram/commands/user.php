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

		return $response;
	}


	/**
	 * start conversation
	 * @return [type] [description]
	 */
	public static function start()
	{
		// $result =
		// [
		// 	[
		// 		'method'       => 'sendMessage',
		// 		'text'         =>'به *_fullName_* خوش آمدید.',
		// 		'reply_markup' =>
		// 		[
		// 			'keyboard' =>
		// 			[
		// 				["آشنایی با _type_"],
		// 				["درباره _type_", "تماس با ما"],
		// 				[],
		// 			],
		// 		],
		// 	]
		// ];

		$result['text'] = "_start_";
		$result['text'] .= "\r\n\n\n". 'Made by @Ermile';

		// $result =
		// [
		// 	[
		// 		'text' => "_start_"."\r\n\n\n". 'Made by @Ermile',
		// 	],
		// 	[
		// 		'text' => "salam",
		// 	],
		// ];


		return $result;
	}


	/**
	 * show about message
	 * @return [type] [description]
	 */
	public static function about()
	{
		$result['text'] = "_about_";
		$result['text'] .= "\r\n\n\n". 'Made by @Ermile';

		return $result;
	}


	/**
	 * show contact message
	 * @return [type] [description]
	 */
	public static function contact()
	{
		$result['text'] = "_contact_";
		$result['text'] .= "\r\n\n\n". 'Made by @Ermile';

		return $result;
	}
}
?>