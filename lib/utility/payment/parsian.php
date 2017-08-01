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
		// https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx

		// method => SalePaymentRequest
		// parameter:

		// 	LoginAccount
		// 	Amount
		// 	OrderId
		// 	CallbackUrl

		// ClientSalePaymentResponseData
		//  SalePaymentRequest(ClientSalePaymentRequestData data)

		//  response:
		//  ClientSalePaymentResponseData
		//  Token
		//  Status

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
                \lib\db\logs::set('payment:parsian:merchantid:not:set', self::$user_id, $log_meta);
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

		$request                 = [];
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
            'MerchantID' => null,
            'Authority'  => null,
            'Amount'     => null,
            'Status'     => null,
        ];

        $_args = array_merge($default_args, $_args);

        if(!$_args['MerchantID'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:merchantid:not:set', self::$user_id, $log_meta);
            }
            return debug::error(T_("The MerchantID is required"), 'MerchantID', 'arguments');
        }

        if(!$_args['Amount'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:amount:not:set', self::$user_id, $log_meta);
            }
            return debug::error(T_("The Amount is required"), 'Amount', 'arguments');
        }

        if(!$_args['Authority'])
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:authority:not:set', self::$user_id, $log_meta);
            }
            return debug::error(T_("The Authority is required"), 'Authority', 'arguments');
        }

        if($_args['Status'] == 'NOK')
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:user:cancel:operation', self::$user_id, $log_meta);
            }
            return debug::error(T_("The user cancel the transaction or transaction is faild"), 'Status', 'arguments');
        }

        $request               = [];
        $request['MerchantID'] = $_args['MerchantID'];
        $request['Amount']     = $_args['Amount'];
        $request['Authority']  = $_args['Authority'];

        try
        {
            $client = @new \soapclient('https://de.parsian.com/pg/services/WebGate/wsdl');

            $result                         = $client->PaymentVerification($request);
            $msg                            = self::msg($result->Status);
            $log_meta['meta']['soapclient'] = $result;

            if($result->Status == 100)
            {
                return true;
            }
            elseif($result->Status == 101)
            {
                return true;
            }
            else
            {
                if(self::$save_log)
                {
                    \lib\db\logs::set('payment:parsian:verify:error', self::$user_id, $log_meta);
                }
                debug::error($msg);
                return false;
            }
        }
        catch (SoapFault $e)
        {
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:verify:error:load:web:services', self::$user_id, $log_meta);
            }
            return debug::error(T_("Error in load web services"));
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
			case '-32768':
			case -32768:
				$msg = 'خطای ناشناخته رخ داده است';
				break;

			case '-1552':
			case -1552:
				$msg = 'برگشت تراکنش مجاز نمی باشد';
				break;

			case '-1551':
			case -1551:
				$msg = 'برگشت تراکنش قبلاً انجام شده است';
				break;

			case '-1550':
			case -1550:
				$msg = 'برگشت تراکنش در وضعیت جاری امکان پذیر نمی باشد';
				break;

			case '-1549':
			case -1549:
				$msg = 'زمان مجاز برای درخواست برگشت تراکنش به اتمام رسیده است';
				break;

			case '-1548':
			case -1548:
				$msg = 'فراخوانی سرویس درخواست پرداخت قبض ناموفق بود';
				break;

			case '-1540':
			case -1540:
				$msg = 'تاييد تراکنش ناموفق مي باشد';
				break;

			case '-1536':
			case -1536:
				$msg = 'فراخوانی سرویس درخواست شارژ تاپ آپ ناموفق بود';
				break;

			case '-1533':
			case -1533:
				$msg = 'تراکنش قبلاً تایید شده است';
				break;

			case '1532':
			case 1532:
				$msg = 'تراکنش از سوی پذیرنده تایید شد';
				break;

			case '-1531':
			case -1531:
				$msg = 'تراکنش به دلیل انصراف شما در بانک ناموفق بود';
				break;

			case '-1530':
			case -1530:
				$msg = 'پذیرنده مجاز به تایید این تراکنش نمی باشد';
				break;

			case '-1528':
			case -1528:
				$msg = 'اطلاعات پرداخت یافت نشد';
				break;

			case '-1527':
			case -1527:
				$msg = 'انجام عملیات درخواست پرداخت تراکنش خرید ناموفق بود';
				break;

			case '-1507':
			case -1507:
				$msg = 'تراکنش برگشت به سوئیچ ارسال شد';
				break;

			case '-1505':
			case -1505:
				$msg = 'تایید تراکنش توسط پذیرنده انجام شد';
				break;

			case '-132':
			case -132:
				$msg = 'مبلغ تراکنش کمتر از حداقل مجاز می باشد';
				break;

			case '-131':
			case -131:
				$msg = 'Token نامعتبر می باشد';
				break;

			case '-130':
			case -130:
				$msg = 'Token زمان منقضی شده است';
				break;

			case '-128':
			case -128:
				$msg = 'قالب آدرس IP معتبر نمی باشد';
				break;

			case '-127':
			case -127:
				$msg = 'آدرس اینترنتی معتبر نمی باشد';
				break;

			case '-126':
			case -126:
				$msg = 'کد شناسایی پذیرنده معتبر نمی باشد';
				break;

			case '-121':
			case -121:
				$msg = 'رشته داده شده بطور کامل عددی نمی باشد';
				break;

			case '-120':
			case -120:
				$msg = 'طول داده ورودی معتبر نمی باشد';
				break;

			case '-119':
			case -119:
				$msg = 'سازمان نامعتبر می باشد';
				break;

			case '-118':
			case -118:
				$msg = 'مقدار ارسال شده عدد نمی باشد';
				break;

			case '-117':
			case -117:
				$msg = 'طول رشته کم تر از حد مجاز می باشد';
				break;

			case '-116':
			case -116:
				$msg = 'طول رشته بیش از حد مجاز می باشد';
				break;

			case '-115':
			case -115:
				$msg = 'شناسه پرداخت نامعتبر می باشد';
				break;

			case '-114':
			case -114:
				$msg = 'شناسه قبض نامعتبر می باشد';
				break;

			case '-113':
			case -113:
				$msg = 'پارامتر ورودی خالی می باشد';
				break;

			case '-112':
			case -112:
				$msg = 'شماره سفارش تکراری است';
				break;

			case '-111':
			case -111:
				$msg = 'مبلغ تراکنش بیش از حد مجاز پذیرنده می باشد';
				break;

			case '-108':
			case -108:
				$msg = 'قابلیت برگشت تراکنش برای پذیرنده غیر فعال می باشد';
				break;

			case '-107':
			case -107:
				$msg = 'قابلیت ارسال تاییده تراکنش برای پذیرنده غیر فعال می باشد';
				break;

			case '-106':
			case -106:
				$msg = 'قابلیت شارژ برای پذیرنده غیر فعال می باشد';
				break;

			case '-105':
			case -105:
				$msg = 'قابلیت تاپ آپ برای پذیرنده غیر فعال می باشد';
				break;

			case '-104':
			case -104:
				$msg = 'قابلیت پرداخت قبض برای پذیرنده غیر فعال می باشد';
				break;

			case '-103':
			case -103:
				$msg = 'قابلیت خرید برای پذیرنده غیر فعال می باشد';
				break;

			case '-102':
			case -102:
				$msg = 'تراکنش با موفقیت برگشت داده شد';
				break;

			case '-101':
			case -101:
				$msg = 'پذیرنده اهراز هویت نشد';
				break;

			case '-100':
			case -100:
				$msg = 'پذیرنده غیرفعال می باشد';
				break;

			case '-1':
			case -1:
				$msg = 'خطای سرور';
				break;

			case '0':
			case 0:
				$msg = 'عملیات موفق می باشد';
				break;

			case '1':
			case 1:
				$msg = 'صادرکننده ی کارت از انجام تراکنش صرف نظر کرد';
				break;

			case '2':
			case 2:
				$msg = 'عملیات تاییدیه این تراکنش قبلا باموفقیت صورت پذیرفته است';
				break;

			case '3':
			case 3:
				$msg = 'پذیرنده ی فروشگاهی نامعتبر می باشد';
				break;

			case '5':
			case 5:
				$msg = 'از انجام تراکنش صرف نظر شد';
				break;

			case '6':
			case 6:
				$msg = 'بروز خطايي ناشناخته';
				break;

			case '8':
			case 8:
				$msg = 'باتشخیص هویت دارنده ی کارت، تراکنش موفق می باشد';
				break;

			case '9':
			case 9:
				$msg = 'درخواست رسيده در حال پي گيري و انجام است ';
				break;

			case '10':
			case 10:
				$msg = 'تراکنش با مبلغي پايين تر از مبلغ درخواستي ( کمبود حساب مشتري ) پذيرفته شده است ';
				break;

			case '12':
			case 12:
				$msg = 'تراکنش نامعتبر است';
				break;

			case '13':
			case 13:
				$msg = 'مبلغ تراکنش نادرست است';
				break;

			case '14':
			case 14:
				$msg = 'شماره کارت ارسالی نامعتبر است (وجود ندارد)';
				break;

			case '15':
			case 15:
				$msg = 'صادرکننده ی کارت نامعتبراست (وجود ندارد)';
				break;

			case '17':
			case 17:
				$msg = 'مشتري درخواست کننده حذف شده است ';
				break;

			case '20':
			case 20:
				$msg = 'در موقعيتي که سوئيچ جهت پذيرش تراکنش نيازمند پرس و جو از کارت است ممکن است درخواست از کارت ( ترمينال) بنمايد اين پيام مبين نامعتبر بودن جواب است';
				break;

			case '21':
			case 21:
				$msg = 'در صورتي که پاسخ به در خواست ترمينا ل نيازمند هيچ پاسخ خاص يا عملکردي نباشيم اين پيام را خواهيم داشت ';
				break;

			case '22':
			case 22:
				$msg = 'تراکنش مشکوک به بد عمل کردن ( کارت ، ترمينال ، دارنده کارت ) بوده است لذا پذيرفته نشده است';
				break;

			case '30':
			case 30:
				$msg = 'قالب پیام دارای اشکال است';
				break;

			case '31':
			case 31:
				$msg = 'پذیرنده توسط سوئی پشتیبانی نمی شود';
				break;

			case '32':
			case 32:
				$msg = 'تراکنش به صورت غير قطعي کامل شده است ( به عنوان مثال تراکنش سپرده گزاري که از ديد مشتري کامل شده است ولي مي بايست تکميل گردد';
				break;

			case '33':
			case 33:
				$msg = 'تاریخ انقضای کارت سپری شده است';
				break;

			case '38':
			case 38:
				$msg = 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است. کارت توسط دستگاه ضبط شود';
				break;

			case '39':
			case 39:
				$msg = 'کارت حساب اعتباری ندارد';
				break;

			case '40':
			case 40:
				$msg = 'عملیات درخواستی پشتیبانی نمی گردد';
				break;

			case '41':
			case 41:
				$msg = 'کارت مفقودی می باشد';
				break;

			case '43':
			case 43:
				$msg = 'کارت مسروقه می باشد';
				break;

			case '45':
			case 45:
				$msg = 'قبض قابل پرداخت نمی باشد';
				break;

			case '51':
			case 51:
				$msg = 'موجودی کافی نمی باشد';
				break;

			case '54':
			case 54:
				$msg = 'تاریخ انقضای کارت سپری شده است';
				break;

			case '55':
			case 55:
				$msg = 'رمز کارت نا معتبر است';
				break;

			case '56':
			case 56:
				$msg = 'کارت نا معتبر است';
				break;

			case '57':
			case 57:
				$msg = 'انجام تراکنش مربوطه توسط دارنده ی کارت مجاز نمی باشد';
				break;

			case '58':
			case 58:
				$msg = 'انجام تراکنش مربوطه توسط پایانه ی انجام دهنده مجاز نمی باشد';
				break;

			case '59':
			case 59:
				$msg = 'کارت مظنون به تقلب است';
				break;

			case '61':
			case 61:
				$msg = 'مبلغ تراکنش بیش از حد مجاز می باشد';
				break;

			case '62':
			case 62:
				$msg = 'کارت محدود شده است';
				break;

			case '63':
			case 63:
				$msg = 'تمهیدات امنیتی نقض گردیده است';
				break;

			case '65':
			case 65:
				$msg = 'تعداد درخواست تراکنش بیش از حد مجاز می باشد';
				break;

			case '68':
			case 68:
				$msg = 'پاسخ لازم براي تکميل يا انجام تراکنش خيلي دير رسيده است';
				break;

			case '69':
			case 69:
				$msg = 'تعداد دفعات تکرار رمز از حد مجاز گذشته است ';
				break;

			case '75':
			case 75:
				$msg = 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است';
				break;

			case '78':
			case 78:
				$msg = 'کارت فعال نیست';
				break;

			case '79':
			case 79:
				$msg = 'حساب متصل به کارت نا معتبر است یا دارای اشکال است';
				break;

			case '80':
			case 80:
				$msg = 'درخواست تراکنش رد شده است';
				break;

			case '81':
			case 81:
				$msg = 'کارت پذيرفته نشد';
				break;

			case '83':
			case 83:
				$msg = 'سرويس دهنده سوئيچ کارت تراکنش را نپذيرفته است';
				break;

			case '84':
			case 84:
				$msg = 'در تراکنشهايي که انجام آن مستلزم ارتباط با صادر کننده است در صورت فعال نبودن صادر کننده اين پيام در پاسخ ارسال خواهد شد ';
				break;

			case '91':
			case 91:
				$msg = 'سيستم صدور مجوز انجام تراکنش موقتا غير فعال است و يا  زمان تعيين شده براي صدور مجوز به پايان رسيده است';
				break;

			case '92':
			case 92:
				$msg = 'مقصد تراکنش پيدا نشد';
				break;

			case '93':
			case 93:
				$msg = 'امکان تکميل تراکنش وجود ندارد';
				break;

			default:
				$msg = 'پرداخت تراکنش به دلیل انصراف در صفحه بانک ناموفق بود';
				break;
		}

		return $msg;
	}
}



// https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx

// method => SalePaymentRequest
// parameter:

// 	LoginAccount
// 	Amount
// 	OrderId
// 	CallbackUrl

// ClientSalePaymentResponseData
//  SalePaymentRequest(ClientSalePaymentRequestData data)

//  response:
//  ClientSalePaymentResponseData
//  Token
//  Status


//  hps://pec.shaparak.ir/NewIPG/?Token=175793116


//  Token = Request.Form["Token"]
// status = Request.Form["status"]
// OrderId = Request.Form["OrderIds"]
// TerminalNo = Request.Form["TerminalNo"]
// RRN = Request.Form["RRN"]


// (0 > Token and 0 = Status )


// ConfirmPayment


// https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx

// ClientConfirmResponseData ConfirmPayment(ClientConfirmRequestData data)

// input:

// LoginAccount
// Token

// response
// Status
// RRN

// CardNumberMasked
// Token

// (0 = status (و (0 > RRN







// کد عنوان لاتین عنوان فارسی
//  32768 -UnkownError خطاي ناشناخته رخ داده است
//  1552 -PaymentRequestIsNotEligibleToReversal برگشت تراکنش مجاز نمی باشد
//  1551 -PaymentRequestIsAlreadyReversed برگشت تراکنش قب ًلا انجام شده است
//  1550 -PaymentRequestStatusIsNotReversalable برگشت تراکنش در وضعیت جاري امکان پذیر
// نمی باشد
//  1549 -MaxAllowedTimeToReversalHasExceeded زمان مجاز براي درخواست برگشت تراکنش
// به اتمام رسیده است
//  1548 -BillPaymentRequestServiceFailed فراخوانی سرویس درخواست پرداخت قبض
// ناموفق بود
//  1540 -InvalidConfirmRequestService تایید تراکنش ناموفق می باشد
// TopupChargeServiceTopupChargeRequestFail -1536
// ed
// فراخوانی سرویس درخواست شارژ تاپ آپ
// ناموفق بود
//  1533 -PaymentIsAlreadyConfirmed تراکنش قبلاً تایید شده است
//  1532 -MerchantHasConfirmedPaymentRequest تراکنش از سوي پذیرنده تایید شد
//  1531 -CannotConfirmNonSuccessfulPayment تایید تراکنش ناموفق امکان پذیر نمی باشد
// 17
// MerchantConfirmPaymentRequestAccessVaio -1530
// lated
// پذیرنده مجاز به تایید این تراکنش نمی باشد
//  1528 -ConfirmPaymentRequestInfoNotFound اطلاعات پرداخت یافت نشد
//  1527 -CallSalePaymentRequestServiceFailed انجام عملیات درخواست پرداخت تراکنش
// خرید ناموفق بود
//  1507 -ReversalCompleted تراکنش برگشت به سوئیچ ارسال شد
//  1505 -PaymentConfirmRequested تایید تراکنش توسط پذیرنده انجام شد
//  132 -InvalidMinimumPaymentAmount مبلغ تراکنش کمتر از حداقل مجاز میباشد
//  131 -InvalidToken Tokenنامعتبر می باشد
//  130 -TokenIsExpired Tokenزمان منقضی شده است
//  128 -InvalidIpAddressFormat قالب آدرس IP معتبر نمی باشد
//  127 -InvalidMerchantIp آدرس اینترنتی معتبر نمی باشد
//  126 -InvalidMerchantPin کد شناسایی پذیرنده معتبر نمی باشد
//  121 -InvalidStringIsNumeric رشته داده شده بطور کامل عددي نمی باشد
//  120 -InvalidLength طول داده ورودي معتبر نمی باشد
//  119 -InvalidOrganizationId سازمان نامعتبر می باشد
//  118 -ValueIsNotNumeric مقدار ارسال شده عدد نمی باشد
//  117 -LenghtIsLessOfMinimum طول رشته کم تر از حد مجاز می باشد
//  116 -LenghtIsMoreOfMaximum طول رشته بیش از حد مجاز می باشد
//  115 -InvalidPayId شناسه پرداخت نامعتبر می باشد
// 18
//  114 -InvalidBillId شناسه قبض نامعتبر می باشد
//  113 -ValueIsNull پارامتر ورودي خالی می باشد
//  112 -OrderIdDuplicated شماره سفارش تکراري است
//  111 -InvalidMerchantMaxTransAmount مبلغ تراکنش بیش از حد مجاز پذیرنده می
// باشد
//  108 -ReverseIsNotEnabled قابلیت برگشت تراکنش براي پذیرنده غیر
// فعال می باشد
//  107 -AdviceIsNotEnabled قابلیت ارسال تاییده تراکنش براي پذیرنده
// غیر فعال می باشد
//  106 -ChargeIsNotEnabled قابلیت شارژ براي پذیرنده غیر فعال می باشد
//  105 -TopupIsNotEnabled قابلیت تاپ آپ براي پذیرنده غیر فعال می
// باشد
//  104 -BillIsNotEnabled قابلیت پرداخت قبض براي پذیرنده غیر فعال
// می باشد
//  103 -SaleIsNotEnabled قابلیت خرید براي پذیرنده غیر فعال می باشد
//  102 -ReverseSuccessful تراکنش با موفقیت برگشت داده شد
//  101 -MerchantAuthenticationFailed پذیرنده اهراز هویت نشد
//  100 -MerchantIsNotActive پذیرنده غیرفعال می باشد
// سرور خطاي Server Error -1
//  0 Successful عملیات موفق می باشد
// از انجام ترا 1 Decline Issuer Card To Refer صادرکننده ي کارت کنش صرف
// 19
// نظر کرد
// باموفقیت قبلا تراکنش این تاییدیه عملیات Refer To Card Issuer Special Conditions 2
// صورت پذیرفته است
//  3 Merchant Invalid پذیرنده ي فروشگاهی نامعتبر می باشد
//  5 Honour Not Do از انجام تراکنش صرف نظر شد
//  6 Error بروز خطایی ناشناخته
//  8 Identification With Honour باتشخیص هویت دارنده ي کارت، تراکنش
// موفق می باشد
//  9 Inprogress Request درخواست رسیده در حال پی گیري و انجام
// است
//  10 Amount Partial For Approved تراکنش با مبلغی پایین تر از مبلغ درخواستی
//  )کمبود حساب مشتري ( پذیرفته شده است
//  12 Transaction Invalid تراکنش نامعتبر است
//  13 Amount Invalid مبلغ تراکنش نادرست است
//  14 Number Card Invalid شماره کارت ارسالی نامعتبر است (وجود
// ندارد)
//  15 Issuer Such No صادرکننده ي کارت نامعتبراست (وجود
// ندارد)
//  17 Cancellation Customer مشتري درخواست کننده حذف شده است
//  20 Response Invalid در موقعیتی که سوئیچ جهت پذیرش تراکنش
// نیازمند پرس و جو از کارت است ممکن است
// درخواست از کارت ( ترمینال) بنماید این پیام
// 20
// مبین نامعتبر بودن جواب است
//  21 Taken Action No در صورتی که پاسخ به در خواست ترمینا ل
// نیازمند هیچ پاسخ خاص یا عملکردي نباشیم
// این پیام را خواهیم داشت
//  22 Malfunction Suspected تراکنش مشکوك به بد عمل کردن ( کارت ،
// ترمینال ، دارنده کارت ) بوده است لذا
// پذیرفته نشده است
//  30 Error Format قالب پیام داراي اشکال است
//  31 Switch By Supported Not Bank پذیرنده توسط سوئی پشتیبانی نمی شود
//  32 Partially Completed تراکنش به صورت غیر قطعی کامل شده است
// ( به عنوان مثال تراکنش سپرده گزاري که از
// دید مشتري کامل شده است ولی می بایست
// تکمیل گردد
//  33 Up Pick Card Expired تاریخ انقضاي کارت سپري شده است
//  38 Up Pick Exceeded Tries PIN Allowable تعداد دفعات ورود رمزغلط بیش از حدمجاز
// است. کارت توسط دستگاه ضبط شود
//  39 Acount Credit No کارت حساب اعتباري ندارد
// گردد نمی پشتیبانی درخواستی عملیات Requested Function is not supported 40
//  41 Card Lost کارت مفقودي می باشد
//  43 Card Stolen کارت مسروقه می باشد
// باشد نمی پرداخت قابل قبض Bill Can not Be Payed 45
//  51 Funds Sufficient No موجودي کافی نمی باشد
// 21
//  54 Account Expired تاریخ انقضاي کارت سپري شده است
//  55 PIN Incorrect رمز کارت نا معتبر است
//  56 Record Card No کارت نا معتبر است
// انجام ترا دارنده ي 57 CardHolder To Permitted Not Transaction کنش مربوطه توسط
// کارت مجاز نمی باشد
//  58 Terminal To Permitted Not Transaction انجام تراکنش مربوطه توسط پایانه ي انجام
// دهنده مجاز نمی باشد
//  59 Decline-Fraud Suspected کارت مظنون به تقلب است
//  61 Limit Amount Withdrawal Exceeds مبلغ تراکنش بیش از حد مجاز می باشد
//  62 Decline-Card Restricted کارت محدود شده است
//  63 Violation Security تمهیدات امنیتی نقض گردیده است
//  65 Limit Frequency Withdrawal Exceeds تعداد درخواست تراکنش بیش از حد مجاز
// می باشد
//  68 Late Too Received Response پاسخ لازم براي تکمیل یا انجام تراکنش
// خیلی دیر رسیده است
//  69 Exceeded Tries PIN Of Number Allowabe تعداد دفعات تکرار رمز از حد مجاز گذشته
// است
//  75 Slm-Exceeds Reties PIN تعداد دفعات ورود رمزغلط بیش از حدمجاز
// است
// نیست فعال کارت Deactivated Card-Slm 78
//  79 Slm-Amount Invalid حساب متصل به کارت نا معتبر است یا داراي
// اشکال است
// 22
//  80 Slm-Denied Transaction درخواست تراکنش رد شده است
// نشد پذیرفته کارت Cancelled Card-Slm 81
// تراکنش را 83 Slm-Refuse Host سرویس دهنده سوئیچ کارت
// نپذیرفته است
//  84 Slm-Down Issuer در تراکنشهایی که انجام آن مستلزم ارتباط با
// صادر کننده است در صورت فعال نبودن
// صادر کننده این پیام در پاسخ ارسال خواهد
// شد
//  91 Inoperative Is Switch Or Issuer سیستم صدور مجوز انجام تراکنش موقتا غیر
// فعال است و یا زمان تعیین شده براي صدو
// مجوز به پایان رسیده است
// Financial Inst Or Intermediate Net Facility 92
// Not Found for Routing
// مقصد تراکنش پیدا نشد
//  93 Completed Be Cannot Tranaction امکان تکمیل تراکنش وجود ندارد



?>