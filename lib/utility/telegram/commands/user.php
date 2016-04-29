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

			case '/help':
			case 'help':
			case 'کمک':
				$response = self::help();
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
				'text'         => "به *_fullName_* خوش آمدید."." /help",
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
		$result['method']  = "sendPhoto";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/about.jpg"));
		$result['photo']   = 'AgADBAADtqcxG-eq1QnHfgOD-d-edTTxQhkABMMyWG58No_62ncAAgI';
		$result['caption'] = "_about_";


		return $result;
	}


	/**
	 * show contact message
	 * @return [type] [description]
	 */
	public static function contact()
	{
		// get location address from http://www.gps-coordinates.net/
		$result =
		[
			[
				'method'    => "sendVenue",
				'latitude'  => '34.6349668',
				'longitude' => '50.87914999999998',
				'title'     => 'Ermile | ارمایل',
				'address'   => '#83, Moallem 10, Moallem, Qom, Iran',
			],
		];

		$result[] =
		[
			'text' => "_contact_",
		];

		return $result;
	}


	/**
	 * show help message
	 * @return [type] [description]
	 */
	public static function help()
	{
		$text = "*_fullName_*\r\n\n";
		$text .= "You can control me by sending these commands:\r\n\n";
		$text .= "/start start conversation\n";
		$text .= "/about about\n";
		$text .= "/contact contact us\n";
		$text .= "/menu show main menu\n";
		$text .= "/intro show intro menu\n";
		$text .= "/feature know more about favorite feature\n";
		$text .= "/global read about out global features\n";
		$text .= "/list show list of rooms menu\n";
		$text .= "/standard readmore about standard room\n";
		$text .= "/modern readmore about modern room\n";
		$text .= "/family readmore about family room\n";
		$text .= "/lux readmore about lux room\n";
		// $text .= "/contact contact us\n";
		$result =
		[
			[
				'text'         => $text,
			],
		];

		return $result;
	}

}
?>