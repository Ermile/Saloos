<?php
namespace lib\utility\social;

/** telegram **/
class tg
{
	public static function check()
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
		//$mysql_credentials = [
		//    'host'     => 'localhost',
		//    'user'     => 'dbuser',
		//    'password' => 'dbpass',
		//    'database' => 'dbname',
		//];

		try {
			// Create Telegram API object
			$telegram = new \Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);

			//// Enable MySQL
			//$telegram->enableMySQL($mysql_credentials);

			//// Enable MySQL with table prefix
			//$telegram->enableMySQL($mysql_credentials, $BOT_NAME . '_');

			//// Add an additional commands path
			//$telegram->addCommandsPath($commands_path);

			//// Here you can enable admin interface for the channel you want to manage
			//$telegram->enableAdmins(['your_telegram_id']);
			//$telegram->setCommandConfig('sendtochannel', ['your_channel' => '@type_here_your_channel']);

			//// Here you can set some command specific parameters,
			//// for example, google geocode/timezone api key for date command:
			//$telegram->setCommandConfig('date', ['google_api_key' => 'your_google_api_key_here']);

			//// Logging
			//$telegram->setLogRequests(true);
			//$telegram->setLogPath($BOT_NAME . '.log');
			//$telegram->setLogVerbosity(3);

			//// Set custom Upload and Download path
			//$telegram->setDownloadPath('../Download');
			//$telegram->setUploadPath('../Upload');

			// Handle telegram webhook request
			$telegram->handle();
		}
		catch (\Longman\TelegramBot\Exception\TelegramException $e)
		{
			// Silence is golden!
			// log telegram errors
			return $e->getMessage();
		}
	}
}
