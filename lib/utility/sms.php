<?php
namespace lib\utility;
require(lib."utility/kavenegar_api.php");

/** Sms management class **/
class sms
{
	/**
	 * Create a text message and send it to user mobile number
	 * @param  [type] $_mobile [description]
	 * @param  [type] $_msg    [description]
	 * @param  [type] $_arg    [description]
	 * @return [type]          [description]
	 */
	public static function send($_mobile, $_msg = null, $_arg = null, $_type = 0)
	{
		// declare variables
		$tmp_obj     = \lib\main::$controller;
		$settings    = $tmp_obj->option('sms', null);
		$sms_apikey  = null;
		$sms_line    = null;

		// if sms service is disable, go out
		if(!$settings['status'] || !isset($settings['meta']))
		{
			return false;
		}

		// set restriction
		if(isset($settings['meta']['iran']) && $settings['meta']['iran'] &&
			substr($_mobile, 0, 2) !== '98')
		{
			self::error(T_("We can't give service to this number"));
			self::error(T_("now we only support Iran!"));
			return false;
		}

		// get sms service name and if not exist show related msg
		$sms_service = $settings['value'];
		if(!method_exists(__CLASS__, $sms_service))
		{
			self::error(T_('This sms service is unavailable'), 'error');
			return false;
		}

		// set message and call related sms service
		$sms_msg = self::message($_msg, $_arg, $settings);
		if(!$sms_msg)
		{
			// message is empty
			return false;
		}

		if(isset($settings['meta']['debug']) && $settings['meta']['debug'])
		{
			$_type = 'debug';
		}

		if(isset($settings['meta']['apikey']) && $settings['meta']['apikey'])
		{
			$sms_apikey = $settings['meta']['apikey'];
		}
		if(isset($settings['meta']['line1']) && $settings['meta']['line1'])
		{
			$sms_line = $settings['meta']['line1'];
		}

		// call related service with special parameters
		$result = self::{$sms_service}
			(
				$sms_apikey,	// apikey
				$sms_line,		// line number for sending message
			 	__FUNCTION__,   // name of method want to call, for example send
			 	$_mobile,       // target mobile number
			 	$sms_msg,       // message to send
			 	$_type          // type of call, 'debug' for simulate sending
			 );
		return $result;
	}


	/**
	 * create special message depending on settings
	 * @param  [type] $_msg      [description]
	 * @param  [type] $_arg      [description]
	 * @param  [type] $_settings [description]
	 * @return [type]            [description]
	 */
	private static function message($_msg = null, $_arg = null, $_settings = null)
	{
		$_arg         = trim($_arg);
		$sms_msg      = null;
		$sms_header   = null;
		$sms_footer   = null;
		$sms_maxOne   = 160;
		$sms_forceOne = null;
		$sms_template = ['signup', 'recovery', 'verification', 'changepass'];
		$template     = null;
		// if user want one of our template create message automatically
		if(in_array($_msg, $sms_template))
		{
			$template = $_msg;
		}
		// else if msg is empty five it automatically
		elseif(!$_msg)
		{
			$template = is_null($_msg)? \lib\router::get_url(): $_msg;
		}


		// set message header
		if(isset($_settings['meta']['header']) && $_settings['meta']['header'])
		{
			$sms_header = T_($_settings['meta']['header']);
		}
		// set message footer
		if(isset($_settings['meta']['footer']) && $_settings['meta']['footer'])
		{
			$sms_footer = T_($_settings['meta']['footer']);
		}
		// set message footer
		if(isset($_settings['meta']['one']) && $_settings['meta']['one'])
		{
			$sms_forceOne = $_settings['meta']['one'];
		}
		// if user want our template
		if($template)
		{
			// if user want to send message for this template
			// then create related message
			if(isset($_settings['meta'][$template]))
			{
				// set related message depending on status passed
				switch ($template)
				{
					case 'signup':
						$_msg = T_('Your verification code is'). ' '. $_arg;
						break;

					case 'recovery':
						$_msg = T_('Your recovery code is'). ' '. $_arg;
						break;

					case 'verification':
						$_msg = T_('You account is verified successfully');
						break;

					case 'changepass':
						$_msg = T_('Your password is changed successfully');
						break;
				}
			}
			// else if send permisson is off
			else
			{
				return false;
			}
		}
		else
		{
			// else if possible translate user message
			$_msg = T_($_msg);
		}
		$_msg = trim($_msg);
		// if message is not set then return false
		if(!$_msg)
		{
			return false;
		}

		// create complete message
		$sms_msg    = $sms_header. "\n". $_msg. "\n\n". $sms_footer;

		if($sms_forceOne && mb_strlen($sms_msg) > self::is_rtl($sms_msg, true))
		{
			// create complete message
			$sms_msg    = $sms_header. "\n". $_msg;
			if($sms_forceOne && mb_strlen($sms_msg) > self::is_rtl($sms_msg, true))
			{
				// create complete message
				$sms_msg    = $_msg;
			}
		}

		// return final message:)
		return $sms_msg;
	}


	/**
	 * call kavenegar sms api
	 * @param  [type]  $_mobile [description]
	 * @param  [type]  $_msg    [description]
	 * @param  integer $_type   [description]
	 * @return [type]           [description]
	 */
	private static function kavenegar_api($_apikey, $_line, $_request, $_mobile = false, $_msg = false, $_type = 0)
	{
		if($_type === 'debug')
		{
			self::error(T_($_request). T_(' to '). $_mobile, 'true');
			self::error(T_($_msg), 'true');
			return 'debug';
		}
		if(!$_apikey || !$_line )
		{
			self::error(T_('Please set apikey and linenumber'), 'error');
			return 'debug';
		}
		// create new instance from kavenegar api and call requested func of it
		$api    = new \kavenegar_api($_apikey, $_line);
		$result = $api->{$_request}($_mobile, $_msg, 0);

		// $result = $api->select(27657835);
		// $result = $api->cancel(27657835);
		// $result = $api->selectoutbox(1410570000);
		// $result = $api->account_info();
		return $result;
	}


	/**
	 * check the input is rtl or not
	 * @param  [type]  $string [description]
	 * @param  [type]  $type   [description]
	 * @return boolean         [description]
	 */
	private static function is_rtl($_str, $_type = false)
	{
		$rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
		$result            = preg_match($rtl_chars_pattern, $_str);
		if($_type)
		{
			$result = $result? 70: 160;
		}
		return $result;
	}


	/**
	 * show special error to user depending on status of debug
	 * @param  [type] $_err  [description]
	 * @param  string $_type [description]
	 * @return [type]        [description]
	 */
	private static function error($_err, $_type = 'warn')
	{
		\lib\debug::{$_type}($_err);

		// if(DEBUG)
		// {
		// 	\lib\debug::{$_type}($_err);
		// }
	}
}
?>