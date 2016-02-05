<?php
namespace lib\router;
class route
{
	public $status = true;
	public $match;

	public function __construct($run = true)
	{
		if($run){
			call_user_func_array(array($this,'check_route'), func_get_args());
		}
	}


	public function check_route()
	{
		$this->match = object();
		$args = func_get_args();
		if(count($args) == 0) return;
		$route = $args[0];
		$fn = isset($args[1])? $args[1] : false;
		if(is_string($route))
		{
			$this->url($route);
		}
		else
		{
			if(!isset($route['max']) && isset($route['url']) && is_array($route['url']))
			{
				$route['max'] = count($route['url']);
			}
			elseif(!isset($route['max']) && !(isset($route['url']) && is_string($route['url']) && preg_match("/^(\/.*\/|#.*#|[.*])[gui]{0,3}$/i", $route['url'])))
			{
				$route['max'] = 0;
			}

			foreach ($route as $key => $value)
			{
				if(method_exists($this, $key))
				{
					$this->$key($value);
				}
			}
		}
		if($this->status === true && is_object($fn))
		{
			$arg = array_splice($args, 2);
			array_push($arg, $this->match);
			call_user_func_array($fn, $arg);
		}
		return $this->status;
	}


	function max($max)
	{
		if(count(\lib\router::$url_array) > $max){
			$this->status = false;
		}
	}


	function min($min)
	{
		if(count(\lib\router::$url_array) < $min){
			$this->status = false;
		}
	}


	function fn($function)
	{
		if(is_object($function)){
			$status = call_user_func($function);
		}elseif(is_array($function)){
			$status = call_user_func_array($function[0], array_splice($function, 1));
		}else{
			$status = false;
		}
		$this->status = (!$status)? false : $this->status;
	}

	function real_url($url_Parameters){
		$this->parametersCaller('real_url', $url_Parameters, \lib\router::$real_url_array, '/');
	}

	function property($url_Parameters){
		$this->parametersCaller('property', $url_Parameters, \lib\router::$url_index_property);
	}

	function url($url_Parameters){
		$this->parametersCaller('url', $url_Parameters, \lib\router::$url_array);
	}

	function sub_domain($sub_domain_Parameters){
		$this->parametersCaller('sub_domain', $sub_domain_Parameters, \lib\router::$sub_domain, '.');
	}

	function domain($domain_Parameters){
		$this->parametersCaller('domain', $domain_Parameters, \lib\router::$domain, '.');
	}

	function get($get){
		$this->parametersCaller('get', $get, $_GET, '&');
	}

	function post($post){
		$this->parametersCaller('post', $post, $_POST, '&');
	}

	function parametersCaller($name, $parameters, $array, $join = "/")
	{
		if(!is_array($parameters)){
			$match = $this->check_parameters($parameters, join($array, $join));
			if($match){
				if(!isset($this->match->$name)) $this->match->$name = array();
				array_push($this->match->$name, $match);
			}
			return;
		}
		foreach ($parameters as $key => $value) {
			if(!isset($array[$key])){
				$this->status = false;
				break;
			}
			$match = $this->check_parameters($value, $array[$key]);
			if($match){
				if(!isset($this->match->$name)) $this->match->$name = array();
				array_push($this->match->$name, $match);
			}
		}
	}

	function check_parameters($reg, $value)
	{
		if(preg_match("/^(\/.*\/|#.*#|[.*])[gui]{0,3}$/i", $reg)){
			if(!preg_match($reg, $value, $array)){
				$this->status = false;
			}else{
				return $array;
			}
		}elseif($reg != $value){
			$this->status = false;
		}else{
			return $value;
		}
		return false;
	}
}
?>