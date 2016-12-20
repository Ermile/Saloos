<?php
namespace lib;
class debug
{
	#errors
	private static $error    = array();
	private static $warn     = array();
	private static $true     = array();
	private static $msg      = array();
	private static $property = array();
	private static $form     = array();
	private static $title;
	public static  $x        = true;
	#error status: 0 : error , 1 : true, 2 : warning;
	public static  $status   = 1;


	/**
	 * create error message (fatal)
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	public static function error($_error, $_element = false, $_group = 'public')
	{
		self::$x = false;
		self::$status = 0;
		array_push(self::$error,
			array('title' => $_error, "element" => $_element, "group" => $_group));
	}


	/**
	 * create warn message
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	public static function warn($_error, $_element = false, $_group = 'public')
	{
		if(self::$x){
			self::$status = 2;
		}
		array_push(self::$warn,
			array('title' => $_error, "element" => $_element, "group" => $_group));
	}

	/**
	 * create true message (successful)
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	public static function true($_error, $_element = false, $_group = 'public')
	{
		array_push(self::$true,
			array('title' => $_error, "element" => $_element, "group" => $_group));
	}

	public static function title($_title){
		self::$title = $_title;
	}


	/**
	 * set msg for showing data with ajax on pages
	 * @param  [string or array] $_name  if array we seperate it in many msg else it's name of msg
	 * @param  [string or array] $_value if pass
	 * @param  [bool]            $_reset
	 * @return set global value
	 */
	public static function msg($_name, $_value = null, $_reset = null)
	{
		if($_reset)
			self::$msg = array();

		if(is_array($_name))
		{
			foreach($_name as $key => $value)
				self::$msg[$key] = $value;
		}
		else
		{
			if($_value)
				self::$msg[$_name] = $_value;
			else
				array_push(self::$msg, $_name);
		}
	}


/**
 * set property for debug
 * @param  [type]  $_property [description]
 * @param  boolean $_value    [description]
 * @return [type]             [description]
 */
	public static function property($_property, $_value = false)
	{
		if(is_array($_property)){
			foreach ($_property as $key => $value) {
				self::$property[$key] = $value;
			}
		}else{
			if($_value !== false){
				self::$property[$_property] = $_value;
			}else{
				array_push(self::$property, $_property);
			}
		}
	}

	/**
	 * set form of messages
	 * @param  [type] $_form [description]
	 * @return [type]        [description]
	 */
	public static function form($_form)
	{
		if(!array_search($_form, self::$form)){
			self::$form[] = $_form;
		}
	}


	/**
	 * compile message and return it for show in page
	 * @param  boolean $_json convert return value to json or not
	 * @return [string]       depending on condition return json or string
	 */
	public static function compile($_json = false)
	{
		$debug = array();
		$debug['status'] = self::$status;
		$debug['title']  = self::$title;
		$messages = array();
		if(count(self::$error) > 0) $messages['error'] = self::$error;
		if(count(self::$warn) > 0)  $messages['warn']  = self::$warn;
		if(count(self::$msg) > 0)   $debug['msg']      = self::$msg;
		if(count(self::$property) > 0){
			foreach (self::$property as $key => $value) {
				$debug[$key] = $value;
			}
		}
		if(count(self::$form) > 0) $debug['msg']['form'] = self::$form;
		if(count(self::$true) > 0 || count($debug)       == 0) $messages['true'] = self::$true;
		if(count($messages) > 0) $debug['messages']       = $messages;
		return ($_json)? json_encode($debug) : $debug;
	}

	public static function db_return($_status)
	{
		$return = new \lib\db\db_return();
		return $return->set_ok($_status);
	}
}

?>