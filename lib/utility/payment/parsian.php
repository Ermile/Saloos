<?php
namespace lib\utility\payment;
use \lib\debug;

class parsian
{
	/**
     * auto save logs
     *
     * @var        boolean
     */
    public static $save_log = false;
    // to save log for this user
    public static $user_id  = null;

    /**
     * pay price
     *
     * @param      array  $_args  The arguments
     */
    public static function pay($_args = [])
    {
        $log_meta =
        [
            'data' => null,
            'meta' =>
            [
                'args' => func_get_args()
            ],
        ];

        $default_args =
        [
			'LoginAccount' => null,
			'Amount'       => null,
			'OrderId'      => null,
			'CallbackUrl'  => null,
        ];

        $_args = array_merge($default_args, $_args);
        // if soap is not exist return false
        if(!class_exists("soapclient"))
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:soapclient:not:install', self::$user_id, $log_meta);
            }
            debug::error(T_("Can not connect to parsian gateway. Install it!"));
            return false;
        }

        if(!$_args['LoginAccount'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:LoginAccount:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The LoginAccount is required"), 'LoginAccount', 'arguments');
            return false;
        }

        if(!$_args['Amount'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:amount:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The Amount is required"), 'Amount', 'arguments');
            return false;
        }

        if(!$_args['OrderId'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:description:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The OrderId is required"), 'OrderId', 'arguments');
            return false;
        }

        if(!$_args['CallbackUrl'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:callbackurl:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The CallbackUrl is required"), 'CallbackUrl', 'arguments');
            return false;
        }

        $price = $_args['Amount'];

        if(is_numeric($price) && $price > 0 && $price == round($price, 0))
        {
            // no thing!
        }
        else
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:amount:lessthan:0', self::$user_id, $log_meta);
            }
            debug::error(T_("Amount must be larger than 0"), 'Amount', 'arguments');
            return false;
        }

		$request      = [];
		$LoginAccount = $request['LoginAccount'] = $_args['LoginAccount'];
		$Amount       = $request['Amount']       = $_args['Amount'];
		$OrderId      = $request['OrderId']      = $_args['OrderId'];
		$CallbackUrl  = $request['CallbackUrl']  = $_args['CallbackUrl'];

        try
        {
        	$soap_meta =
        	[
				'soap_version' => 'SOAP_1_1',
				'cache_wsdl'   => WSDL_CACHE_NONE ,
				'encoding'     => 'UTF-8',
        	];

			$client = new \SoapClient('https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL',$soap_meta);

			$result = $client->SalePaymentRequest(["requestData" => $request]);

			$status = $result->SalePaymentRequestResult->Status;
			$token  = $result->SalePaymentRequestResult->Token;
			$msg    = $result->SalePaymentRequestResult->Message;

            if ($status === 0 && $token > 0)
            {
                if(self::$save_log)
                {
                    \lib\db\logs::set('payment:parsian:redirect', self::$user_id, $log_meta);
                }

				$url      = "hps://pec.shaparak.ir/NewIPG/?Token=" . $token;
				$redirect = (new \lib\redirector($url, false))->redirect();
            }
            else
            {
                if(self::$save_log)
                {
                    \lib\db\logs::set('payment:parsian:error', self::$user_id, $log_meta);
                }
                debug::error($msg);
                return false;
            }
        }
        catch (SoapFault $e)
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:error:load:web:services', self::$user_id, $log_meta);
            }
            debug::error(T_("Error in load web services"));
            return false;
        }
    }


    /**
     * check redirect url
     */
    public static function check_url($_args = [])
    {
    	$log_meta =
        [
            'data' => null,
            'meta' =>
            [
				'args'     => func_get_args(),
				'_REQUEST' => $_REQUEST,
            ],
        ];

        $default_args =
        [
			'LoginAccount' => null,
        ];

        $_args = array_merge($default_args, $_args);

        if(!$_args['LoginAccount'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:LoginAccount:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The LoginAccount is required"), 'LoginAccount', 'arguments');
            return false;
        }

    	$Token		= isset($_REQUEST['Token']) 		? (string) $_REQUEST['Token'] 		: null;
		$status		= isset($_REQUEST['status']) 		? (string) $_REQUEST['status'] 		: null;
		$OrderId	= isset($_REQUEST['OrderId']) 		? (string) $_REQUEST['OrderId'] 	: null;
		$TerminalNo	= isset($_REQUEST['TerminalNo']) 	? (string) $_REQUEST['TerminalNo'] 	: null;
		$RRN		= isset($_REQUEST['RRN']) 			? (string) $_REQUEST['RRN'] 		: null;

		if($status === '0' && intval($Token) > 0)
		{
			if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:check:url', self::$user_id, $log_meta);
            }
			return self::verify(['LoginAccount' => $_args['LoginAccount'], 'Token' => $Token]);
		}
		else
		{
			return false;
		}
    }


    /**
     * { function_description }
     *
     * @param      array  $_args  The arguments
     */
    public static function verify($_args = [])
    {

        $log_meta =
        [
            'data' => null,
            'meta' =>
            [
                'args' => func_get_args()
            ],
        ];

        $default_args =
        [
			'LoginAccount' => null,
			'Token'        => null,
        ];

        $_args = array_merge($default_args, $_args);

        if(!$_args['LoginAccount'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:LoginAccount:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The LoginAccount is required"), 'LoginAccount', 'arguments');
            return false;
        }

        if(!$_args['Token'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:Token:not:set', self::$user_id, $log_meta);
            }
            debug::error(T_("The Token is required"), 'Token', 'arguments');
            return false;
        }

		$request                 = [];
		$request['LoginAccount'] = $_args['LoginAccount'];
		$request['Token']        = $_args['Token'];

		try
		{
			$soap_meta =
			[
				'soap_version' => 'SOAP_1_1',
				'cache_wsdl'   => WSDL_CACHE_NONE ,
				'encoding'     => 'UTF-8',
			];

			$client	= new SoapClient('https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL', $soap_meta);

			$result	= $client->ConfirmPayment(["requestData" => $request]);

			$Status = $result->ConfirmPaymentResult->Status;

			$RRN = isset($result->ConfirmPaymentResult->RRN) ? $result->ConfirmPaymentResult->RRN : null;

			$CardNumberMasked = isset($result->ConfirmPaymentResult->CardNumberMasked) ? $result->ConfirmPaymentResult->CardNumberMasked : null;

			$log_meta['meta']['client']           = $client;
			$log_meta['meta']['result']           = $result;
			$log_meta['meta']['Status']           = $Status;
			$log_meta['meta']['RRN']              = $RRN;
			$log_meta['meta']['CardNumberMasked'] = $CardNumberMasked;

			if($Status === 0)
			{
				if(self::$save_log)
	            {
	                \lib\db\logs::set('payment:parsian:transaction:ok', self::$user_id, $log_meta);
	            }
				return true;
			}
			else
			{
				if(self::$save_log)
	            {
	                \lib\db\logs::set('payment:parsian:error:verify', self::$user_id, $log_meta);
	            }
				debug::error(self::msg($Status));
				return false;
			}
		}
		catch(Exception $e)
		{
 			if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:error:load:web:services:verify', self::$user_id, $log_meta);
            }
            debug::error(T_("Error in load web services"));
            return false;
		}
    }


    /**
     * set msg
     *
     * @param      <type>  $_status  The status
     */
   	public static function msg($_status)
	{
		$msg = null;

		switch($_status)
		{
			case -32768:$msg = 'خطای ناشناخته رخ داده است';break;
			case -1552: $msg = 'برگشت تراکنش مجاز نمی باشد';break;
			case -1551: $msg = 'برگشت تراکنش قبلاً انجام شده است';break;
			case -1550: $msg = 'برگشت تراکنش در وضعیت جاری امکان پذیر نمی باشد';break;
			case -1549: $msg = 'زمان مجاز برای درخواست برگشت تراکنش به اتمام رسیده است';break;
			case -1548: $msg = 'فراخوانی سرویس درخواست پرداخت قبض ناموفق بود';break;
			case -1540: $msg = 'تاييد تراکنش ناموفق مي باشد';break;
			case -1536: $msg = 'فراخوانی سرویس درخواست شارژ تاپ آپ ناموفق بود';break;
			case -1533: $msg = 'تراکنش قبلاً تایید شده است';break;
			case 1532:  $msg = 'تراکنش از سوی پذیرنده تایید شد';break;
			case -1531: $msg = 'تراکنش به دلیل انصراف شما در بانک ناموفق بود';break;
			case -1530: $msg = 'پذیرنده مجاز به تایید این تراکنش نمی باشد';break;
			case -1528: $msg = 'اطلاعات پرداخت یافت نشد';break;
			case -1527: $msg = 'انجام عملیات درخواست پرداخت تراکنش خرید ناموفق بود';break;
			case -1507: $msg = 'تراکنش برگشت به سوئیچ ارسال شد';break;
			case -1505: $msg = 'تایید تراکنش توسط پذیرنده انجام شد';break;
			case -132: 	$msg = 'مبلغ تراکنش کمتر از حداقل مجاز می باشد';break;
			case -131: 	$msg = 'Token نامعتبر می باشد';break;
			case -130: 	$msg = 'Token زمان منقضی شده است';break;
			case -128: 	$msg = 'قالب آدرس IP معتبر نمی باشد';break;
			case -127: 	$msg = 'آدرس اینترنتی معتبر نمی باشد';break;
			case -126: 	$msg = 'کد شناسایی پذیرنده معتبر نمی باشد';break;
			case -121: 	$msg = 'رشته داده شده بطور کامل عددی نمی باشد';break;
			case -120: 	$msg = 'طول داده ورودی معتبر نمی باشد';break;
			case -119: 	$msg = 'سازمان نامعتبر می باشد';break;
			case -118: 	$msg = 'مقدار ارسال شده عدد نمی باشد';break;
			case -117: 	$msg = 'طول رشته کم تر از حد مجاز می باشد';break;
			case -116: 	$msg = 'طول رشته بیش از حد مجاز می باشد';break;
			case -115: 	$msg = 'شناسه پرداخت نامعتبر می باشد';break;
			case -114: 	$msg = 'شناسه قبض نامعتبر می باشد';break;
			case -113: 	$msg = 'پارامتر ورودی خالی می باشد';break;
			case -112: 	$msg = 'شماره سفارش تکراری است';break;
			case -111: 	$msg = 'مبلغ تراکنش بیش از حد مجاز پذیرنده می باشد';break;
			case -108: 	$msg = 'قابلیت برگشت تراکنش برای پذیرنده غیر فعال می باشد';break;
			case -107: 	$msg = 'قابلیت ارسال تاییده تراکنش برای پذیرنده غیر فعال می باشد';break;
			case -106: 	$msg = 'قابلیت شارژ برای پذیرنده غیر فعال می باشد';break;
			case -105: 	$msg = 'قابلیت تاپ آپ برای پذیرنده غیر فعال می باشد';break;
			case -104: 	$msg = 'قابلیت پرداخت قبض برای پذیرنده غیر فعال می باشد';break;
			case -103: 	$msg = 'قابلیت خرید برای پذیرنده غیر فعال می باشد';break;
			case -102: 	$msg = 'تراکنش با موفقیت برگشت داده شد';break;
			case -101: 	$msg = 'پذیرنده اهراز هویت نشد';break;
			case -100: 	$msg = 'پذیرنده غیرفعال می باشد';break;
			case -1: 	$msg = 'خطای سرور';break;
			case 0: 	$msg = 'عملیات موفق می باشد';break;
			case 1: 	$msg = 'صادرکننده ی کارت از انجام تراکنش صرف نظر کرد';break;
			case 2: 	$msg = 'عملیات تاییدیه این تراکنش قبلا باموفقیت صورت پذیرفته است';break;
			case 3: 	$msg = 'پذیرنده ی فروشگاهی نامعتبر می باشد';break;
			case 5: 	$msg = 'از انجام تراکنش صرف نظر شد';break;
			case 6: 	$msg = 'بروز خطايي ناشناخته';break;
			case 8: 	$msg = 'باتشخیص هویت دارنده ی کارت، تراکنش موفق می باشد';break;
			case 9: 	$msg = 'درخواست رسيده در حال پي گيري و انجام است ';break;
			case 10: 	$msg = 'تراکنش با مبلغي پايين تر از مبلغ درخواستي ( کمبود حساب مشتري ) پذيرفته شده است ';break;
			case 12: 	$msg = 'تراکنش نامعتبر است';break;
			case 13: 	$msg = 'مبلغ تراکنش نادرست است';break;
			case 14: 	$msg = 'شماره کارت ارسالی نامعتبر است (وجود ندارد)';break;
			case 15: 	$msg = 'صادرکننده ی کارت نامعتبراست (وجود ندارد)';break;
			case 17: 	$msg = 'مشتري درخواست کننده حذف شده است ';break;
			case 20: 	$msg = 'در موقعيتي که سوئيچ جهت پذيرش تراکنش نيازمند پرس و جو از کارت است ممکن است درخواست از کارت ( ترمينال) بنمايد اين پيام مبين نامعتبر بودن جواب است';break;
			case 21: 	$msg = 'در صورتي که پاسخ به در خواست ترمينا ل نيازمند هيچ پاسخ خاص يا عملکردي نباشيم اين پيام را خواهيم داشت ';break;
			case 22: 	$msg = 'تراکنش مشکوک به بد عمل کردن ( کارت ، ترمينال ، دارنده کارت ) بوده است لذا پذيرفته نشده است';break;
			case 30: 	$msg = 'قالب پیام دارای اشکال است';break;
			case 31: 	$msg = 'پذیرنده توسط سوئی پشتیبانی نمی شود';break;
			case 32: 	$msg = 'تراکنش به صورت غير قطعي کامل شده است ( به عنوان مثال تراکنش سپرده گزاري که از ديد مشتري کامل شده است ولي مي بايست تکميل گردد';break;
			case 33: 	$msg = 'تاریخ انقضای کارت سپری شده است';break;
			case 38: 	$msg = 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است. کارت توسط دستگاه ضبط شود';break;
			case 39: 	$msg = 'کارت حساب اعتباری ندارد';break;
			case 40: 	$msg = 'عملیات درخواستی پشتیبانی نمی گردد';break;
			case 41: 	$msg = 'کارت مفقودی می باشد';break;
			case 43: 	$msg = 'کارت مسروقه می باشد';break;
			case 45: 	$msg = 'قبض قابل پرداخت نمی باشد';break;
			case 51: 	$msg = 'موجودی کافی نمی باشد';break;
			case 54: 	$msg = 'تاریخ انقضای کارت سپری شده است';break;
			case 55: 	$msg = 'رمز کارت نا معتبر است';break;
			case 56: 	$msg = 'کارت نا معتبر است';break;
			case 57: 	$msg = 'انجام تراکنش مربوطه توسط دارنده ی کارت مجاز نمی باشد';break;
			case 58: 	$msg = 'انجام تراکنش مربوطه توسط پایانه ی انجام دهنده مجاز نمی باشد';break;
			case 59: 	$msg = 'کارت مظنون به تقلب است';break;
			case 61: 	$msg = 'مبلغ تراکنش بیش از حد مجاز می باشد';break;
			case 62: 	$msg = 'کارت محدود شده است';break;
			case 63: 	$msg = 'تمهیدات امنیتی نقض گردیده است';break;
			case 65: 	$msg = 'تعداد درخواست تراکنش بیش از حد مجاز می باشد';break;
			case 68: 	$msg = 'پاسخ لازم براي تکميل يا انجام تراکنش خيلي دير رسيده است';break;
			case 69: 	$msg = 'تعداد دفعات تکرار رمز از حد مجاز گذشته است ';break;
			case 75: 	$msg = 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است';break;
			case 78: 	$msg = 'کارت فعال نیست';break;
			case 79: 	$msg = 'حساب متصل به کارت نا معتبر است یا دارای اشکال است';break;
			case 80: 	$msg = 'درخواست تراکنش رد شده است';break;
			case 81: 	$msg = 'کارت پذيرفته نشد';break;
			case 83: 	$msg = 'سرويس دهنده سوئيچ کارت تراکنش را نپذيرفته است';break;
			case 84: 	$msg = 'در تراکنشهايي که انجام آن مستلزم ارتباط با صادر کننده است در صورت فعال نبودن صادر کننده اين پيام در پاسخ ارسال خواهد شد ';break;
			case 91: 	$msg = 'سيستم صدور مجوز انجام تراکنش موقتا غير فعال است و يا  زمان تعيين شده براي صدور مجوز به پايان رسيده است';break;
			case 92: 	$msg = 'مقصد تراکنش پيدا نشد';break;
			case 93: 	$msg = 'امکان تکميل تراکنش وجود ندارد';break;
			default:
				$msg = 'پرداخت تراکنش به دلیل انصراف در صفحه بانک ناموفق بود';
				break;
		}

		return $msg;
	}
}
?>