<?php
namespace lib\utility\telegram;

/** telegram generate needle library**/
class generate extends tg
{
	/**
	 * this library generate telegram tools
	 * v1.0
	 */


	/**
	 * default action to handle message texts
	 * @param  [type] [description]
	 * @return [type]       [description]
	 */
	public static function answer($forceSample = null)
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
}
?>