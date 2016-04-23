<?php
namespace lib\utility\social;

/** telegram **/
class tg
{
	public static function do($_type = null, $_save_in_db = true)
	{
		// if telegram is off then do not run
		if(!\lib\utility\option::get('telegram', 'status'))
			return 'telegram is off!';
		// get key and botname
		$mykey = \lib\utility\option::get('telegram', 'meta', 'key');
		$mybot = \lib\utility\option::get('telegram', 'meta', 'bot');

		//This configuration file is intended to run the bot with the webhook method.
		//Uncommented parameters must be filled
		//Please notice that if you open this file with your browser you'll get the "Input is empty!" Exception.
		//This is a normal behaviour because this address has to be reached only by Telegram server

		// Load composer
		$mycomposer = addons. lib. 'SocialNetwork/php-telegram-bot/vendor/autoload.php';
		// check file exist
		if(!file_exists($mycomposer))
		{
			return 'autoload is not exist!';
		}

		require_once $mycomposer;



		$API_KEY  = $mykey;
		$BOT_NAME = $mybot;

		//$commands_path = __DIR__ . '/Commands/';

		$tg_folder = root.'public_html/files/tg/';

		try {
			// Create Telegram API object
			$telegram = new \Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);

			// if is not set then
			if($_type === null)
			{
				$_type = \lib\utility::get('do');
			}

			switch ($_type)
			{
				// Set webhook
				case 'set':
					$hook_url = 'https://'.Domain.'.'.Tld.'/cp/tg/$*Ermile*$/';
					$result = $telegram->setWebHook($hook_url);
					// Uncomment to use certificate
					//$result = $telegram->setWebHook($hook_url, $path_certificate);

					if ($result->isOk())
					{
						\lib\utility\file::makeDir($tg_folder.'download/', 0775, true);
						\lib\utility\file::makeDir($tg_folder.'upload/',   0775, true);
						return $result->getDescription();
					}
					break;

				// Unset webhook
				case 'unset':
					$result = $telegram->unsetWebHook();

					if ($result->isOk())
					{
						return $result->getDescription();
					}
					break;

				case 'hook':
				default:

					//// Enable MySQL
					// $telegram->enableMySQL($mysql_credentials);

					//// Enable MySQL with table prefix
					// $telegram->enableMySQL($mysql_credentials, $BOT_NAME . '_');
					if($_save_in_db)
					{
						$mysql_credentials =
						[
							'host'     => 'localhost',
							'user'     => db_user,
							'password' => db_pass,
							'database' => core_name.'_tools',
						];
						$telegram->enableMySQL($mysql_credentials);
					}

					//// Add an additional commands path
					//$telegram->addCommandsPath($commands_path);

					//// Here you can enable admin interface for the channel you want to manage
					//$telegram->enableAdmins(['your_telegram_id']);
					//$telegram->setCommandConfig('sendtochannel', ['your_channel' => '@type_here_your_channel']);

					//// Here you can set some command specific parameters,
					//// for example, google geocode/timezone api key for date command:
					//$telegram->setCommandConfig('date', ['google_api_key' => 'your_google_api_key_here']);

					//// Logging
					$telegram->setLogRequests(true);
					$telegram->setLogPath($BOT_NAME . '.log');
					$telegram->setLogVerbosity(3);

					//// Set custom Upload and Download path
					$telegram->setDownloadPath($tg_folder.'download/');
					$telegram->setUploadPath($tg_folder.'upload/');

					// Handle telegram webhook request
					$telegram->handle();
					break;
			}
		}
		catch (\Longman\TelegramBot\Exception\TelegramException $e)
		{
			// Silence is golden!
			// log telegram errors
			return $e->getMessage();
		}
	}
}
