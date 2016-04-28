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
			case '/menu':
			case 'menu':
			case 'main':
			case 'mainmenu':
			case 'منو':
				$response = self::main();
				break;

			case '/intro':
			case 'intro':
			case '/tour':
			case 'tour':
			case 'معرفی':
				$response = self::intro();
				break;


			case 'امکانات':
			case 'امکانات هتل':
				$response = self::features();
				break;

			case 'مشخصات':
			case 'مشخصات عمومی':
				$response = self::global();
				break;

			case 'لیست':
			case 'لیست اتاق‌ها':
				$response = self::global();
				break;


			case 'loc':
			case 'موقعیت':
				$response = self::menu_loc();
				break;


			case 'inline':
			case 'اینلاین':
				$response = self::menu_inline();
				break;

			case 'return':
			case 'بازگشت':
				switch ($_cmd['text'])
				{
					case 'بازگشت به منوی اصلی':
						$response = self::main();
						break;

					case 'بازگشت به منوی معرفی':
						$response = self::intro();
						break;

					default:
						$response = self::main();
						break;
				}
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
	 * create mainmenu
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function main($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				["معرفی _type_"],
				["درباره _type_", "تماس با ما"],
			],
		];

		if($_onlyMenu)
		{
			return $menu;
		}

		$result =
		[
			[
				'text'         => "*_fullName_*\r\n\nمنوی اصلی",
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}


	/**
	 * create mainmenu
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function intro($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				["لیست اتاق‌ها"],
				["مشخصات عمومی", "امکانات هتل"],
				["بازگشت به منوی اصلی"],
			],
		];

		if($_onlyMenu)
		{
			return $menu;
		}

		$result =
		[
			[
				'text'         => "*_fullName_*\r\n\n_intro_",
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}


	/**
	 * show features message
	 * @return [type] [description]
	 */
	public static function features()
	{
		$result['text'] = "*_fullName_*\r\n\n_features_";
		return $result;
	}


	/**
	 * show global message
	 * @return [type] [description]
	 */
	public static function global()
	{
		$result['text'] = "*_fullName_*\r\n\n_globals_";
		return $result;
	}


	/**
	 * create mainmenu
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function list($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				["دو تخته استاندارد"],
				["دو تخته میهمان خارجی"],
				["سوئیت جونیور"],
				["سوئیت جونیور میهمان خارجی"],
				["بازگشت به منوی معرفی"],
			],
		];

		if($_onlyMenu)
		{
			return $menu;
		}

		$result =
		[
			[
				'text'         => "*_fullName_*\r\n\n_list_",
				'reply_markup' => $menu,
			],
		];

		// return menu
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