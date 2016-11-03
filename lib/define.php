<?php
namespace lib;
class define
{
  public function __construct()
  {
    // check php version to upper than 5.6
    if(version_compare(phpversion(), '5.6', '<'))
      die("<p>For using Saloos you must update php version to 5.6 or higher!</p>");

    /**
     * If DEBUG is TRUE you can see the full error description, If set to FALSE show userfriendly messages
     * change it from project config.php
     */
    if (!defined('DEBUG'))
    {
      if(\lib\utility\option::get('config', 'meta', 'debug'))
      {
        define('DEBUG', true);
      }
      elseif(Tld === 'dev')
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

    /**
     * A session is a way to store information (in variables) to be used across multiple pages.
     * Unlike a cookie, the information is not stored on the users computer.
     * access to session with this code: $_SESSION["test"]
     */
    if(is_string(Domain))
      session_name(Domain);
    // set session cookie params
    session_set_cookie_params(0, '/', '.'.Service, false, true);
    // if user enable saving sessions in db
    // temporary disable because not work properly
    if(false)
    {
      $handler = new \lib\utility\sessionHandler();
      session_set_save_handler($handler, true);
    }
    // start sessions
    session_start();

    /**
     * in coming soon period show public_html/pages/coming/ folder
     * developer must set get parameter like site.com/dev=anyvalue
     * for disable this attribute turn off it from config.php in project root
     */
    if(\lib\utility\option::get('config', 'meta', 'coming') || defined('CommingSoon'))
    {
      // if user set dev in get, show the site
      if(isset($_GET['dev']))
      {
        setcookie('preview','yes',time() + 30*24*60*60,'/','.'.Service);
      }
      elseif(router::get_url(0) === 'saloos_tg')
      {
        // allow telegram to commiunate on coming soon
      }
      elseif(!isset($_COOKIE["preview"]))
      {
        header('Location: http://'.AccountService.MainTld.'/static/page/coming/', true, 302);
        exit();
      }
    }

    // change header and remove php from it
    header("X-Powered-By: Saloos!");
  }
}
?>