<?php
namespace lib\utility;
use lib\utility;

class request
{
	public $request = [];
	public $method = 'get';
	public function __construct($_options = array())
	{
		if(!isset($_options['method']))
		{
			if(empty($_GET) && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json')
			{
				$this->method 		= 'input_json';
			}
			else
			{
				$this->method 		= $_SERVER['REQUEST_METHOD'];
			}
		}
		else
		{
			$this->method 		= $_options['method'];
		}

		if(!isset($_options['request']))
		{
			$this->request 		= [];
		}
		else
		{
			$this->request 		= $_options['request'];
		}
		$this->method = strtolower($this->method);

		switch ($this->method) {
			case 'post':
				$this->request = utility::post();
				break;

			case 'array':
				$this->request = utility\safe::safe($this->request);
				break;

			case 'json':
				$json = json_decode($this->request);
				$this->request = utility\safe::safe($json);
				break;

			case 'object':
				$this->request = utility\safe::safe($this->request);
				break;

			case 'input_json':
				$input = json_decode(file_get_contents('php://input'), true);
				$this->request = utility\safe::safe($input);
				break;

			default:
				$this->request = utility::get(null, 'raw');
				break;
		}
	}

	public function get()
	{
		$args = func_get_args();
		$request = $this->request;
		if(empty($args))
		{
			return $request;
		}

		foreach ($args as $key => $value) {
			if(is_object($request))
			{
				if(!isset($request->$value))
				{
					return null;
				}
				$request = $request->$value;
			}
			elseif(is_array($request))
			{
				if(!array_key_exists($value, $request))
				{
					return null;
				}
				$request = $request[$value];
			}
			else
			{
				return null;
			}
		}
		return $request;
	}
}
?>