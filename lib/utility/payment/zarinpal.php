<?php
namespace lib\utility\payment;
use \lib\debug;

class zarinpal
{

    /**
     * pay price
     *
     * @param      array  $_args  The arguments
     */
    public static function pay($_args = [])
    {
        $default_args =
        [
            'MerchantID'  => null,
            'Description' => null,
            'CallbackURL' => null,
            'Email'       => null,
            'Mobile'      => null,
            'Amount'      => null,
        ];

        $_args = array_merge($default_args, $_args);


        if(!$_args['MerchantID'])
        {
            return debug::error(T_("The MerchantID is required"), 'MerchantID', 'arguments');
        }

        if(!$_args['Amount'])
        {
            return debug::error(T_("The Amount is required"), 'Amount', 'arguments');
        }

        if(!$_args['Description'])
        {
            return debug::error(T_("The Description is required"), 'Description', 'arguments');
        }

        if(!$_args['CallbackURL'])
        {
            return debug::error(T_("The CallbackURL is required"), 'CallbackURL', 'arguments');
        }

        $price = $_args['Amount'];

        if(is_numeric($price) && $price > 0 && $price == round($price, 0))
        {
            // no thing!
        }
        else
        {
            return debug::error(T_("Amount must be larger than 0"), 'Amount', 'arguments');
        }

        $request                = [];
        $request['MerchantID']  = $_args['MerchantID'];
        $request['Amount']      = $_args['Amount'];
        $request['Description'] = $_args['Description'];
        $request['CallbackURL'] = $_args['CallbackURL'];
        if($_args['Email'])
        {
            $request['Email']       = $_args['Email'];
        }

        if($_args['Mobile'])
        {
            $request['Mobile']      = $_args['Mobile'];
        }

        try
        {
            $client = @new \soapclient('https://de.zarinpal.com/pg/services/WebGate/wsdl');

            $result = $client->PaymentRequest($request);

            switch ($result->Status)
            {
                case -1:
                    $msg = T_("The submited inforamation are incomplete.");
                    break;

                case -2:
                    $msg = T_("IP or merchant code of the host is incorrect");
                    break;

                case -3:
                    $msg = T_("Due to Shaparak's limitations, It's impossible to pay the specified amount");
                    break;

                case -4:
                    $msg = T_("The host level is below silver level");
                    break;

                case -11:
                    $msg = T_("Nothing found for the specified request");
                    break;

                case -12:
                    $msg = T_("It's impossible to edit the request");
                    break;

                case -21:
                    $msg = T_("No financial operation found for this transaction");
                    break;

                case -22:
                    $msg = T_("Transaction faild");
                    break;

                case -33:
                    $msg = T_("The specified transaction amount does not match with the payed amount");
                    break;

                case -34:
                    $msg = T_("Highest amount of transaction is passed as a result of number or amount");
                    break;

                case -40:
                    $msg = T_("Access unavilable to method");
                    break;

                case -41:
                    $msg = T_("Invalid information is sent for AdditionalData");
                    break;

                case -42:
                    $msg = T_("Valid lifespan of ID must be between 30 minutes to 45 days");
                    break;

                case -54:
                    $msg = T_("The request is archived");
                    break;

                case 100:
                    $msg = T_("Operation was successfully done");
                    break;

                case 101:
                    $msg = T_("Payment operation was successfull and PatmentVerification of the transaction has already done");
                    break;

                default:
                    $msg = T("The error code is :code", ['code' => $result->Status]);
                    break;
            }

            if ($result->Status == 100)
            {
                $url = "https://www.zarinpal.com/pg/StartPay/" . $result->Authority;
                $redirect = (new \lib\redirector($url, false))->redirect();
            }
            else
            {
                return debug::error($msg);
            }
        }
        catch (SoapFault $e)
        {
            return debug::error(T_("Error in load web services"));
        }
    }


    /**
     * { function_description }
     *
     * @param      array  $_args  The arguments
     */
    public static function verify($_args = [])
    {

    }

}
?>