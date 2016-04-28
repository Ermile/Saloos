<?php
namespace lib\utility\telegram\commands;
// use telegram class as bot
use \lib\utility\telegram\tg as bot;

class menu
{
	public static $return = false;

	public static function exec($_cmd)
	{
		$response = null;
		switch ($_cmd['command'])
		{
			case 'menu':
			case 'منو':
				$response = self::menu0();
				break;

			case 'loc':
			case 'موقعیت':
				$response = self::menu_loc();
				break;


			case 'inline':
			case 'اینلاین':
				$response = self::menu_inline();
				break;

			case 'main':
			case 'mainmenu':
			case 'menu0':
			case 'منو۰':
				$response = self::menu_main();
				break;

			case 'return':
			case 'بازگشت':
				switch ($_cmd['text'])
				{
					case 'بازگشت به منوی اصلی':

						break;

					default:

						break;
				}
				$response = self::returnBtn();
				break;

			default:
				break;
		}

		// automatically add return to end of keyboard
		if(self::$return)
		{
			// if has keyboard
			if(isset($response['replyMarkup']['keyboard']))
			{
				$response['replyMarkup']['keyboard'][] = ['بازگشت'];
			}
		}

		return $response;
	}


	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function menu_main()
	{
		// disable return from main menu
		$result['text']        = 'منوی اصلی'."\r\n";
		$result['replyMarkup'] =
		[
			'keyboard' =>
			[
				[""],
				[""],
				[""],
				[""],
			],
		];
		return $result;
	}

	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function returnBtn()
	{
		$result['text'] = 'بازگشت به منوی قبلی'."\r\n";
		$result['text'] .= 'مثلا برگشتیم'."\r\n";
		$result['replyMarkup'] =
		[
			'keyboard' =>
			[
					["خوب"],
					["بد"],
			],
		];
		return $result;
	}


	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function menu0()
	{
		$result['text'] = 'منوی آزمایشی'."\r\n";
		$result['replyMarkup'] =
		[
			'keyboard' =>
			[
					["سلام"],
					["خوبی"],
					["خوب", "بد"],
			],
		];
		return $result;
	}




	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function menu_loc()
	{
		$result['text']        = 'منوی موقعیت'."\r\n";
		$result['replyMarkup'] =
		[
			'keyboard' =>
			[
				[
					[
						'text'            => 'تقاضای شماره تلفن',
						'request_contact' => true
					],
					[
						'text'             => 'تقاضای آدرس',
						'request_location' => true
					]
				]
			]
		];
		return $result;
	}


	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function menu_inline()
	{
		$result['text']        = 'منوی اینلاین آزمایشی'."\r\n";
		$result['replyMarkup'] =
		[
			'inline_keyboard' =>
			[
				[
					[
						'text'          => '<',
						'callback_data' => 'go_left'
					],
					[
						'text'          => '^',
						'callback_data' => 'go_up'
					],
					[
						'text'          => '>',
						'callback_data' => 'go_right'
					],
				],
				[
					[
						'text' => 'open google.com',
						'url'  => 'google.com'
					],
					[
						'text'                => 'search \'test\' inline',
						'switch_inline_query' => 'test'
					],
				]
			],
		];
		return $result;
	}
}
?>