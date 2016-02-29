<?php
/**
 * default of all define
 */
// Define Global variables ***************************************************************************************
// Core name
define('core_name'	,'saloos');

// Define main service
if(!defined('MainService'))
	define('MainService', 'ermile');

// Define Saloos variables ***************************************************************************************
if(!defined("core"))
	define("core", preg_replace("[\\\\]", "/", __DIR__).'/' );

// Saloos library
if(!defined("lib"))
	define("lib", "lib/");

 	set_include_path(get_include_path() . PATH_SEPARATOR . core.'saloos-addons/');
	set_include_path(get_include_path() . PATH_SEPARATOR . core);
// Saloos plugin
if(!defined("addons"))
	define("addons", core."saloos-addons/");

// Saloos helper
if(!defined("helper"))
	define("helper", core."helper/");

// Saloos error page
if(!defined("error_file"))
	define("error_file", core."page/page_error.php");


// Define Project variables **************************************************************************************
if(!defined("root"))
	define("root", dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/' );

// Project include folder
if(!defined("dir_includes"))
	define("dir_includes", root.'includes/');

// Project library
if(!defined("ilib"))
	define("ilib", "ilib/");

// Project helper
if(!defined("ihelper"))
	define("ihelper", dir_includes."helper/");

// Project default repository
if(!defined("repository"))
	define("repository", root.'content/');

// Project cls
if(!defined("cls"))
	define("cls", dir_includes."cls/");

// Project database
if(!defined("database"))
	define("database", dir_includes."cls/database/");

// Project MVC
if(!defined("mvc"))
	define("mvc", dir_includes."mvc/");

// Set default timezone to Asia/Tehran, Please set timezone in your php.ini
if(!defined("timezone"))
	date_default_timezone_set('Asia/Tehran');
else
	date_default_timezone_set(constant('timezone'));


// Define Project Methods *****************************************************************************************
// define object method
function object($val = array())
{
	return (object) $val;
}
?>