<?php
namespace lib\utility;
require(lib."utility/KavenegarApi.php");

/** Sms management class **/
class Sms
{
	// Create a text message and send it to user mobile number
	public static function send($_mobile, $_status = null, $_arg = null, $_service= MainService)
	{
		$_status	   = is_null($_status)? \lib\router::get_url(): $_status;
		$mymessage	= T_(ucfirst($_service))."\n";

		switch ($_status)
		{
			case 'signup':
				$mymessage .= T_('your verification code is').' '.$_arg;
				break;

			case 'recovery':
				$mymessage .= T_('your recovery code is').' '.$_arg;
				break;

			case 'verification':
				$mymessage .= T_('you account is verified successfully');
				break;

			case 'changepass':
				$mymessage .= T_('your password is changed successfully');
				break;

			default:
				$mymessage .= T_('thanks for using our service')."\n".T_('made in iran');
				break;
		}
		$mymessage .= "\n\n".ucfirst($_service).'.com';

		if(substr($_mobile,0,2)=='98')
			$iran = true;
		else
			$iran = null;

		if($iran)
		{
			$api    = new \KavenegarApi();
			$result = $api->send($_mobile, $mymessage, 0);
			// $result = $api->select(27657835);
			// $result = $api->cancel(27657835);
			// $result = $api->selectoutbox(1410570000);
			// $result = $api->account_info();

			// var_dump($result);exit();
		}
		else
		{
			\lib\debug::warn(T_('now we only support Iran!'));
			if(DEBUG)
			{
				\lib\debug::warn("Think sms is send to $_mobile!");
				\lib\debug::true($mymessage);
			}
		}
	}

}
