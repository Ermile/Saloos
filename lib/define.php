<?php
namespace lib;
class define
{
  public function __construct()
  {
    // check php version to upper than 5.6
    if (version_compare(phpversion(), '5.6', '<'))
      die( "<p>For using Saloos you must update php version to 5.6 or higher!</p>" );


    /**
     * in coming soon period show public_html/pages/coming/ folder
     * developer must set get parameter like site.com/dev=anyvalue
     * for disable this attribute turn off it from config.php in project root
     */
    if(defined('CommingSoon') && CommingSoon && isset($_GET['dev'])){
      setcookie('preview','yes',time() + 30*24*60*60,'/','.'.Service);
    }
    elseif(defined("CommingSoon") && CommingSoon && !isset($_COOKIE["preview"])){
      header('Location: http://'.AccountService.MainTld.'/static/page/coming/', true, 302);
      exit();
    }

    /**
     * set default language to storage for next use
     */
    if(defined('DefaultLanguage'))
    {
      router::set_storage('defaultLanguage', DefaultLanguage );
    }
    else
    {
      router::set_storage('defaultLanguage', 'en_US' );
    }

    // if current tld is ir or referrer from site with ir tld,
    // change language to fa_IR
    if(router::get_storage('language'))
    {
      $myLang = router::get_storage('language');
      switch (Tld)
      {
        case 'ir':
          $myLang = "fa_IR";
          break;

        default:
          break;
      }

      if(defined('MainService') && Tld !== 'dev')
      {
        // for example redirect ermile.ir to ermile.com/fa
        $myLang = substr($myLang, 0, 2);
        $myredirect = new \lib\redirector();
        $myredirect->set_domain()->set_url($myLang)->redirect();
      }
      else
      {
        // else show in that domain with fa langusage
        router::set_storage('language', $myLang );
      }
    }

    // if current tld is ir or referrer from site with ir tld,
    // change language to fa_IR
    if(router::get_storage('language'))
    {
      $myLang = router::get_storage('language');
      switch (Tld)
      {
        case 'ir':
          $myLang = "fa_IR";
          break;

        default:
          break;
      }

      if(defined('MainService') && Tld !== 'dev')
      {
        // for example redirect ermile.ir to ermile.com/fa
        $myLang = substr($myLang, 0, 2);
        $myredirect = new \lib\redirector();
        $myredirect->set_domain()->set_url($myLang)->redirect();
      }
      else
      {
        // else show in that domain with fa langusage
        router::set_storage('language', $myLang );
      }
    }

    /**
     * Localized Language, defaults to English.
     *
     * Change this to localize Saloos. A corresponding MO file for the chosen
     * language must be installed to content/languages. For example, install
     * fa_IR.mo to content/languages and set LANGUAGE to 'fa_IR' to enable Persian
     * language support.
     */
    router::set_storage('language', router::get_storage('defaultLanguage'));
    if(router::get_repository_name() === 'content')
    {
      // $mysub = router::get_sub_domain();
      $mysub = router::get_url(0);

      // if subdomain is set, change current lang to it, except for default language
      if(defined('LangList') && constant('LangList'))
      {
        // check langlist with subdomain and if is equal set current language
        foreach (unserialize(LangList) as $key => $value)
        {
          $myLang = substr($key, 0, 2);
          if($mysub === $myLang)
          {
            if(router::get_storage('defaultLanguage') === $key)
            {
              // redirect to homepage
              $myredirect = new \lib\redirector();
              $myredirect->set_domain()->set_url()->redirect();
            }
            else
            {
              router::set_storage('language', $key);
              // update base url
              router::$base .= router::get_url(0). "/";
              router::remove_url($myLang);
            }
          }
        }
      }
    }
    else
    {
      // change with get all times except on content or root,
      // because in root user must change language with subdomain
      if (isset($_GET["lang"]))
      {
        router::set_storage('language', $_GET["lang"] );
      }

      // cookies work all times and on all condition
      elseif(isset($_COOKIE["lang"]))
        router::set_storage('language', $_COOKIE["lang"] );

    // save language preference for future page requests
      setcookie('lang', router::get_storage('language'), time() + 30*24*60*60,'/', '.'.Service);
    }

    // use saloos php gettext function
    require_once(lib.'utility/gettext/gettext.inc');
    // gettext setup
    T_setlocale(LC_MESSAGES, router::get_storage('language'));
    // Set the text domain as 'messages'
    T_bindtextdomain('messages', root.'includes/languages');
    T_bind_textdomain_codeset('messages', 'UTF-8');
    T_textdomain('messages');

    // check direction of language and set for rtl languages
    switch (router::get_storage('language'))
    {
      case 'fa_IR':
      case 'ar_SU':
        router::set_storage('direction', 'rtl');
        break;

      default:
        router::set_storage('direction', 'ltr');
        break;
    }


    /**
     * If DEBUG is TRUE you can see the full error description, If set to FALSE show userfriendly messages
     * change it from project config.php
     */
    if (!defined('DEBUG'))
    {
      if(Tld === 'dev')
      {
        define('DEBUG', true);
      }
      else
      {
        define('DEBUG', false);
      }
    }
    if (DEBUG)
    {
      ini_set('display_errors'        , 'On');
      ini_set('display_startup_errors', 'On');
      ini_set('error_reporting'       , 'E_ALL | E_STRICT');
      ini_set('track_errors'          , 'On');
      ini_set('display_errors'        , 1);
      error_reporting(E_ALL);

      //Setting for the PHP Error Handler
      // set_error_handler('\lib\error::myErrorHandler');

      //Setting for the PHP Exceptions Error Handler
      // set_exception_handler('\lib\error::myErrorHandler');

      //Setting for the PHP Fatal Error
      // register_shutdown_function('\lib\error::myErrorHandler');
    }
    else
    {
      error_reporting(0);
      ini_set('display_errors', 0);

    }
    // change header and remove php from it
    header("X-Powered-By: Saloos!");



    /**
     * A session is a way to store information (in variables) to be used across multiple pages.
     * Unlike a cookie, the information is not stored on the users computer.
     * access to session with this code: $_SESSION["test"]
     */
    if(is_string(Domain))
      session_name(Domain);
    session_set_cookie_params(0, '/', '.'.Service, false, true);
    session_start();
  }
}
?>