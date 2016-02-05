<?php
namespace lib;

class main
{
	static $controller = null;
	public function __construct()
	{
		$this->loadController();
	}

	public function loadController()
	{
		/**
		 * find controller;
		 */
		$url_property = router::get_url_property(-1);
		$myrep        = router::get_repository_name();

		if(!router::get_controller())
		{
			$controller_name	= '\\'.$myrep.'\\'.router::get_class().'\\'.router::get_method().'\\controller';
			$prv_class			= router::get_class();
			// var_dump($controller_name);

			if(!class_exists($controller_name))
			{
				if((!isset($url_property[1]) || $url_property[1] != router::get_method()) && router::get_method() != 'home')
				{
					router::add_url_property(router::get_method());
				}
				$prv_method = router::get_method();
				router::set_method('home');
				$controller_name = '\\'.$myrep.'\\'.router::get_class().'\\'.router::get_method().'\\controller';
				// var_dump(router::get_url_property(-1));
				// var_dump($controller_name);

				if(!class_exists($controller_name))
				{
					router::set_class($prv_class);
					$controller_name = '\\'.$myrep.'\\'.router::get_class().'\\controller';
					// var_dump(router::get_url_property(-1));
					// var_dump($controller_name);

					if(!class_exists($controller_name))
					{
						if((!isset($url_property[0]) || $url_property[0] != router::get_class()) && router::get_class() != 'home')
						{
							router::add_url_property(router::get_class());
						}
						router::set_class('home');
						$controller_name = '\\'.$myrep.'\\'.router::get_class().'\\'.router::get_method().'\\controller';
						// var_dump(router::get_url_property(-1));
						// var_dump($controller_name);
//
						if(!class_exists($controller_name))
						{
							router::set_class('home');
							$controller_name = '\\'.$myrep.'\\'.router::get_class().'\\controller';
							// var_dump(router::get_url_property(-1));
							// $controller_name='\account\home\controller';
							// var_dump($controller_name);

							if(!class_exists($controller_name))
							{
								\lib\error::page("content not found");
							}
						}
					}
				}
			}
		}
		else
		{
			$controller_name = router::get_controller();
		}

		router::set_controller($controller_name);
		if(!class_exists($controller_name))
		{
			error::page($controller_name);
		}


		$controller = new $controller_name;
		self::$controller = $controller;

		// running template base module for homepage
		if(\lib\router::get_storage('CMS') && $myrep == 'content' && method_exists($controller, 's_template_finder'))
		{
			$controller->s_template_finder();
		}

		if(method_exists($controller, '_route'))
		{
			$controller->_route();
		}

		if(router::get_controller() !== $controller_name)
		{
			$this->loadController();
			return;
		}
		if(method_exists($controller, 'config'))
		{
			$controller->config();
		}
		if(method_exists($controller, 'options'))
		{
			$controller->options();
		}

		if(count(router::get_url_property(-1)) > 0 && $controller->route_check_true === false)
		{
			error::page('Unavailable');
		}

		$controller->_corridor();
	}
}
?>