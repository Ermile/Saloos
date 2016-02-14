<?php
namespace lib;
/**
 * checking and routing http request and server model. change your mind change your mvc
 */
class api
{
	public $controller;
	public function __construct($controller_class){
		$this->controller = $controller_class;
	}

	public function get($model_api_name, $view_api_name){
		// $this->controller->route_check_true = true;
		return call_user_func_array(array($this, 'add_api'), array('get', $model_api_name, $view_api_name));
	}

	public function post($model_api_name, $view_api_name){
		return call_user_func_array(array($this, 'add_api'), array('post', $model_api_name, $view_api_name));
	}

	public function put($model_api_name, $view_api_name){
		return call_user_func_array(array($this, 'add_api'), array('put', $model_api_name, $view_api_name));

	}

	public function delete($model_api_name, $view_api_name){
		return call_user_func_array(array($this, 'add_api'), array('delete', $model_api_name, $view_api_name));
	}

	private function add_api($name, $model_api_name, $view_api_name){
		$api_config = new api\config($this, $name, $model_api_name, $view_api_name);
		return $api_config;
	}
}
?>