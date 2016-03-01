<?php
namespace lib;
use \lib\router\route;
use \lib\api;
class controller
{

	public $api, $model, $view, $method;
	public $model_name, $view_name, $display_name;
	public $debug = true;

	/**
	 * if display true && method get corridor run view display
	 * @var boolean
	 */
	public $display = true;

	public $route_check_true = false;

	public function __construct()
	{
	}

	public function loadModel()
	{
		if(!isset($this->loadModel)) $this->loadModel = new \lib\load($this, "model");
		return call_user_func_array(array($this->loadModel, 'method'), func_get_args());
	}

	public function loadView()
	{
		if(!isset($this->loadModel)) $this->loadModel = new \lib\load($this, "view");
		return call_user_func_array(array($this->loadModel, 'method'), func_get_args());
	}

	public function _corridor()
	{
		if(method_exists($this, 'corridor'))
		{
			$this->corridor();
		}
		if(!$this->method) $this->method = 'get';
		$processor_arg = false;
		if(isset($this->model_api_processor))
		{
			$name = $this->model_api_processor->method;
			$args = $this->model_api_processor->args;
			$api_callback = call_user_func_array(array($this->model(), $name), array($args));
			$this->api_callback = $api_callback;

		}
		if(saloos::is_json_accept())
		{
			$this->display = false;
		}
		if(!\lib\router::get_storage('api') && $this->method == 'get' && $this->display)
		{
			$this->view();

			if(isset($this->view_api_processor))
			{
				$name = $this->view_api_processor->method;
				$args = $this->view_api_processor->args;
				if(isset($this->api_callback)) $args->api_callback = $api_callback;
				call_user_func_array(array($this->view(), $name), array($args));

			}
			if($this->display)
			{
				$this->view()->corridor();
			}
		}
		elseif(router::get_storage('api') || !$this->display)
		{
			$mycallback = isset($this->api_callback)? $this->api_callback: null;
			debug::msg('callback', $mycallback);
			$processor_arg = object(array('force_json'=>true));
		}
		if($this->model)
		{
			$this->model()->_processor($processor_arg);
		}

	}

	public function model()
	{
		if(!$this->model)
		{
			if($this->model_name)
			{
				$class_name = $this->model_name;
			}
			else
			{
				// $class_name = get_called_class();
				// $class_name = preg_replace("/\\\controller$/", '\model', $class_name);
				$class_name = $this->findParentClass(__FUNCTION__);
			}

			$object = object();
			$object->controller = $this;
			$this->model = new $class_name($object);
		}
		return $this->model;
	}

	public function view()
	{
		if(!$this->view)
		{
			if($this->view_name)
			{
				$class_name = $this->view_name;
			}
			else
			{
				// $class_name = get_called_class();
				// $class_name = preg_replace("/\\\controller$/", '\\\view', $class_name);
				$class_name = $this->findParentClass(__FUNCTION__);
			}

			$object = object();
			$object->controller = $this;
			$this->view = new $class_name($object);
		}
		return $this->view;
	}

	/**
	 * this function find parent class, if class exist return the name of parent class
	 * else find a parent folder and if class exist use the parent one
	 * @param  [type] $_className the name of class, view or model
	 * @return [type]             return the address of exist class or show error page
	 */
	protected function findParentClass($_className)
	{
		$MyClassName = get_called_class();
		$MyClassName = str_replace("\controller", '\\'.$_className, $MyClassName);

		// if class not exist remove one slash and check it
		if(!class_exists($MyClassName))
		{
			// have more than one back slash for example content\aa\bb\view
			if(substr_count($MyClassName, "\\") > 2)
			{
				$MyClassName = str_replace("\\".$_className, '', $MyClassName);
				$MyClassName = substr($MyClassName, 0, strrpos( $MyClassName, '\\')) . $_className;
			}

			// if after remove one back slash(if exist), class not exist
			if(!class_exists($MyClassName))
			{
				// have more than one back slash for example content\aa\view
				if(substr_count($MyClassName, "\\") == 2)
				{
					$MyClassName = str_replace("\\".$_className, '', $MyClassName);
					$MyClassName = substr($MyClassName, 0, strrpos( $MyClassName, '\\')) . "\home\\" . $_className;
					// var_dump($MyClassName);
				}
				// have more than one back slash for example content\home
				else
				{
					// i dont know this condtion!
					// do nothing!
				}
			}
		}

		if(!class_exists($MyClassName))
		{
			\lib\error::page($_className . " not found");
		}

		return $MyClassName;
	}

	public function route()
	{
		$route = new route(false);
		$return_route = call_user_func_array(array($route, 'check_route'), func_get_args());
		if($route->status == true)
			$this->route_check_true = true;

		return $route;
	}

	public final function check_api($name, $model_function = null, $view_function = false)
	{
		if(!$this->api)
		{
			$this->api = new api($this);
		}
		return $this->api->$name($model_function, $view_function);
	}

	public function __call($name, $args)
	{
		if(preg_grep("/^$name$/", array('get', 'post', 'put', 'delete')))
		{
			array_unshift($args, $name);
			return call_user_func_array(array($this, 'check_api'), $args);
		}
		elseif(method_exists('\lib\router', $name))
		{
			return call_user_func_array('\lib\router::'.$name, $args);
		}
		\lib\error::page(get_called_class()."->$name()");
	}

	public function controller()
	{
		return $this;
	}

	public function change_model($name)
	{
		$this->model_name = $name;
	}

	public function change_view($name)
	{
		$this->view_name = $name;
	}

	public function property()
	{
		$args = func_get_args();
		if(count($args) == 1)
		{
			$name = $args[0];
			return $this->$name;
		}
		elseif(count($args) == 2)
		{
			$name = $args[0];
			return $this->$name = $args[1];
		}
	}

	public function debug()
	{
		return $this->debug;
	}
	public function redirector()
	{
		if(!isset($this->redirector))	$this->redirector = new \lib\redirector(...func_get_args());
		return $this->redirector;
	}


	/**
	 * clearly return url property for use
	 * @param  [type] $_type type of url you need
	 * @param  [type] $_arg  an argument for pass into some condition
	 * @return [type]        the url value
	 */
	public function url($_type = null, $_arg = null)
	{
		$tmp_result = null;
		$myprefix   = Protocol."://";
		$mypostfix  = '/';

		$mytld      = router::get_root_domain('tld');

		switch ($_type)
		{
			// sub domain like 'account'
			case 'sub':
				return router::get_sub_domain($_arg);
				break;

			case 'path':
				$myUrl = router::get_url($_arg);
				if($_arg == '_')
				{
					// filter url to delete disallow characters
					$myUrl = router::urlfilterer($myUrl);
					// dont use $ in id
					$myUrl = str_replace('$', 'dollar', $myUrl);
				}
				return $myUrl;
				break;

			case 'breadcrumb':
				$myurl      = router::get_url(-1);
				$breadcrumb = array();
				foreach ($myurl as $value)
				{
					$tmp_pos = strpos($value, '=');
					array_push($breadcrumb, $tmp_pos? substr($value, 0, $tmp_pos): $value);
				}
				return $breadcrumb;
				break;

			case 'param':
				return \lib\utility::get(null, $_arg);
				break;

			// domain tld like 'com'
			case 'tld':
				return $mytld;
				break;

			// domain name like 'ermile'
			case 'domain':
				return router::get_root_domain('domain');
				break;

			// domain name except subdomain like 'ermile.com'
			case 'raw':
				return router::get_root_domain('domain').'.'.$mytld;
				break;

			// like raw plus http[s]:// domain name except subdomain like 'http://ermile.com/'
			case 'root':
				return $myprefix. router::get_root_domain(). $mypostfix;
				break;

			// use main protocol and give it from config file if not exist use root url
			// return http or https
			case 'MainProtocol':
				if(defined('MainProtocol') && constant('MainProtocol') && is_string(constant('MainProtocol')))
					return constant('MainProtocol');
				else
					return 'http';
				break;

			// use main site and give it from config file if not exist use root url
			// like raw plus http[s]:// domain name except subdomain like 'http://ermile.com/'
			case 'MainSite':
				if(defined('MainSite') && constant('MainSite') && is_string(constant('MainSite')))
					return constant('MainSite');
				else
					return router::get_root_domain(). $mypostfix;
				break;

			// base url for user in base tag with http[s]
			case 'base':
				return router::$base;
				break;

			// full url except get parameter with http[s]
			case 'full':
				return $myprefix. router::get_domain(). '/'. router::get_url();
				break;

			// return module info
			case 'module':
				if($_arg === 'prefix')
					$mymodule	= substr(router::get_url(0), 0, -1);
				elseif($_arg == 'array')
					$mymodule	= router::get_url(-1);
				elseif($_arg == 'cp')
				{
					$mymodule = router::get_url(0);
					switch ($mymodule)
					{
						case 'tags':
						case 'cats':
							$mymodule = 'terms';
							break;
						case 'pages':
							$mymodule = 'posts';
							break;
					}
				}
				else
					$mymodule = router::get_url(0);

				return $mymodule;
				break;

			case 'child':
				$mychild = router::get_url(1);
				if(strrpos($mychild,'=') !==false)
					$mychild = substr($mychild, 0, strrpos($mychild, '='));

				if(!$_arg)
					return $mychild;

				if($mychild=='add')
					return T_('add new');

				if($mychild == 'edit')
					return T_('edit');

				if($mychild == 'delete')
					return T_('delete');

				break;

			// login service and main service with full address
			case 'LoginService':
			case 'account':
				return $myprefix. AccountService. MainTld. '/'. MyAccount;
				break;

			case 'MainService':
				$_arg      = is_array($_arg)  ? $_arg     : array('com', 'dev');

				if (in_array($mytld, $_arg))
					return $myprefix. constant('MainService').'.'.$mytld;
				else
					return $myprefix. constant('MainService'). MainTld;
				break;

			default:
				return null;
				break;
		}
	}

}
?>