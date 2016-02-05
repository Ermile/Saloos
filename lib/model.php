<?php
namespace lib;

class model
{
	use mvm;
	// query lists
	public $querys;

	// sql object
	public $sql;

	public $commit = array();
	public $rollback = array();

	public $transaction = true;
	public function __construct($object = false){
		if(!$object) return;
		$this->querys = object();
		$this->controller = $object->controller;
		if(method_exists($this, '_construct')){
			$this->_construct();
		}
	}

	public function _processor($options = false)
	{
		if(is_array($options)){
			$options = (object) $options;
		}
		$force_json   = gettype($options) == 'object' && isset($options->force_json)   && $options->force_json   ? true : false;
		$force_stop   = gettype($options) == 'object' && isset($options->force_stop)   && $options->force_stop   ? true : false;
		$not_redirect = gettype($options) == 'object' && isset($options->not_redirect) && $options->not_redirect ? true : false;

		if($this->transaction && debug::$status)
		{
			if(isset($this->sql))
				$this->sql->commit();
			if(count($this->commit))
				call_user_func_array($this->commit[0], array_slice($this->commit, 1));
		}
		elseif($this->transaction && !debug::$status)
		{
			if(isset($this->sql))
				$this->sql->rollback();
			if(count($this->rollback))
				call_user_func_array($this->rollback[0], array_slice($this->rollback, 1));
		}
		if($not_redirect)
			$this->controller()->redirector = false;


		if(\saloos::is_json_accept() || $force_json)
		{
			header('Content-Type: application/json');
			if(isset($this->controller()->redirector) && $this->controller()->redirector)
			{
				$_SESSION['debug'][md5( strtok($this->redirector()->redirect(true), '?') )] = debug::compile();
				debug::msg("redirect", $this->redirector()->redirect(true));
			}
			echo debug::compile(true);
		}
		elseif(!\lib\router::get_storage('api') && strtolower($_SERVER['REQUEST_METHOD']) == "post")
		{
			$this->redirector();
		}
		if(isset($this->controller()->redirector) && $this->controller()->redirector && !\saloos::is_json_accept())
		{
			$_SESSION['debug'][md5( strtok($this->redirector()->redirect(true), '?') )] = debug::compile();
			$this->redirector()->redirect();
		}
		if($force_stop) exit();
	}

	public final function commit(){
		$this->commit = func_get_args();
	}

	public final function rollback(){
		$this->rollback = func_get_args();
	}

	public function validate(){
		if(!isset($this->validate)) $this->validate = new \lib\validator\pack;
		return $this->validate;
	}

	public function sql($name = null){
		if(!$this->sql){
			$this->sql = new sql\maker;
			if($this->transaction) $this->sql->transaction();
		}
		$name = $name ? $name : count((array)$this->querys);
		$query = $this->querys->$name = $this->sql;
		return $query;
	}

	public function __get($name){
		if(property_exists($this->controller, $name)){
			return $this->controller->$name;
		}
	}

	public function _call_corridor($name, $args){
		preg_match("/^api_(.+)$/", $name, $spilt_name);
		return count($spilt_name) ? $spilt_name : false;
	}

	public function _call($name, $args, $parm){
		$method = $args[0]->method;
		$api_name = "{$method}_$parm[1]";
		return $this->$api_name($args[0]);
	}
}
?>