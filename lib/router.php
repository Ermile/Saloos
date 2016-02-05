<?php
namespace lib;
/**
 * saloos router controller and configuration
 * @author Baravaak <itb.baravak@gmail.com>
 * @version 0.0.1
 */
class router
{
	use router\config;

	public function __construct()
	{
		self::$repository = repository;
		$path = preg_replace("/^\.\//","/",'/');
		$clean_url = $_SERVER['REQUEST_URI'];
		if(preg_match("#0x#", $clean_url)){
			// var_dump("error");
		}
		$clean_url = preg_replace("#0x#Ui", "", $clean_url);
		$clean_url = preg_replace("#^https?://{$_SERVER['HTTP_HOST']}\/#", '', $clean_url);
		$clean_url = preg_replace("#^$path#", '', $clean_url);
		$clean_url = urldecode($clean_url);

		preg_match("/^([^?]*)(\?.*)?$/", $clean_url, $url);

		self::$real_url_string = self::$url_string = $url[1];

		self::$real_url_array  = self::$url_array = preg_split("[\/]", preg_replace("/^\/|\/$/", '', $url[1]), -1 , PREG_SPLIT_NO_EMPTY);

		// HTTP_HOST is not secure and attacker can change it
		$domain           = $_SERVER['HTTP_HOST'];
		self::$domain     = preg_split("[\.]", $domain);
		self::$sub_domain = self::$domain;
		array_pop(self::$sub_domain);
		array_pop(self::$sub_domain);

		if ( (isset(self::$real_url_array[0]) && self::$real_url_array[0] == 'home') || (isset(self::$real_url_array[1]) && self::$real_url_array[1] == 'home') )
		{
			\lib\error::page("home");
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

		/**
		 * user want control panel or CMS
		 */
		if(defined('CMS') && constant('CMS'))
		{
			$myCP = constant('CMS') === true? 'cp': constant('CMS');
			router::set_storage('CMS', $myCP );
		}
		elseif(defined('ControlPanel') && constant('ControlPanel'))
		{
			$myCP = constant('ControlPanel') === true? 'cp': constant('ControlPanel');
			router::set_storage('CMS', $myCP );
		}
		else
		{
			router::set_storage('CMS', false);
		}

		/**
		 * before router
		 */
		if(self::$auto_repository)
		{
			// first get subdomain and if not exist get first url part as mysub
			$mysub = router::get_sub_domain();
			if(!$mysub)
			{
				$mysub   = router::get_url(0);
				router::$sub_is_fake = true;
      			router::set_storage('language', router::get_storage('defaultLanguage') );
			}

			if($mysub)
			{
				// automatically set repository if folder of it exist
				$myaddons    = array();
				$mysub_real  = $mysub;
				$myloc       = null;
				$mysub_valid = null;
				// check for account with specefic name
				if(defined('Account') && constant('Account'))
				{
					$mykey = constant('Account') === true? 'account': constant('Account');
					$myaddons[$mykey] = 'account';
				}
				// check for account with specefic name
				if(\lib\router::get_storage('CMS'))
				{
					$myaddons[\lib\router::get_storage('CMS')] = 'cp';
				}
				// check this sub is exist in our data or not
				if(array_key_exists($mysub, $myaddons))
				{
					$mysub       = $myaddons[$mysub];
					$mysub_valid = true;
				}

				// set repository name
				$myrep    = 'content_'.$mysub;

				// check content_aaa folder is exist in project or saloos addons folder
				if(is_dir(root.$myrep))
					$myloc = false;
				// if exist on addons folder
				elseif($mysub_valid && is_dir(addons.$myrep))
					$myloc = addons;

				// if folder exist
				if(!is_null($myloc))
				{
					// if url is fake, show it like subdomain and remove from url
					if(router::$sub_is_fake)
					{
						router::remove_url($mysub_real);
						router::set_sub_domain($mysub_real);
					}
					// set repository to this folder
					$myparam = array($myrep);
					if($myloc)
						array_push($myparam, $myloc);

					// call function and pass param value to it
					router::set_repository(...$myparam);
				}

				// if subdomain is set, change current lang to it, except for default language
				if(defined('LangList') && constant('LangList'))
				{
					// check langlist with subdomain and if is equal set current language
					foreach (unserialize(LangList) as $key => $value)
					{
						if($mysub === substr($key, 0, 2))
						{
							if(router::get_storage('defaultLanguage') !== $key)
							{
								router::set_storage('language', $key);
							}
							else
							{
								// redirect to homepage
								$myredirect = new \lib\redirector();
								$myredirect->set_domain()->set_url()->redirect();
							}
						}
					}
				}
			}
		}

		if(self::$auto_api)
		{
			// automatically allow api, if you wan't to desable it, only set a value
			$route = new router\route("/^api([^\/]*)/", function($reg)
			{
				router::remove_url($reg->url);
				router::set_storage('api', true);
			});
		}

		if(class_exists('\cls\route'))
		{
			$router = new \cls\route;
			$router->main = $this;
			if(method_exists($router, "_before")){
				$router->_before();
			}
		}
		$this->check_router();
		/**
		 * after router
		 */
		if(class_exists('\cls\route')){
			if(method_exists($router, "_after")){
				$router->_after();
			}
		}

		// Define Project Constants *******************************************************************
		// declate some constant variable for better use in all part of app
		// like dev or com or ir or ...
		if(!defined('Tld'))
			define('Tld', router::get_root_domain('tld'));

		// like .dev or .com
		if(!defined('MainTld'))
			define('MainTld', (Tld === 'dev'? '.dev': '.com'));

		// like ermile
		if(!defined('Domain'))
			define('Domain', router::get_root_domain('domain'));

		// like account
		if(!defined('SubDomain'))
			define('SubDomain', router::get_sub_domain());

		// like  127.0.0.1
		if(!defined('ClientIP'))
			define('ClientIP', router::get_clientIP() );

		// like ermile.com
		if(!defined('Service'))
			define('Service', Domain.'.'.Tld);

		// like test
		if(!defined('Module'))
			define('Module', router::get_url(0));

		// like https://ermile.com
		router::set_storage('url_site', Protocol.'://' . Domain.'.'.Tld.'/');


		if(defined('Account') && constant('Account'))
		{
			// if use ermile set Mainservice for creating all account together
			if(is_string(constant('Account')) && constant('Account') !== constant('MainService'))
				$myAccount = constant('Account');
			else
				$myAccount = 'account';
		}
		else
		  $myAccount = false;

		// set MyAccount for use in all part of services
		if(!defined('AccountService'))
		{
			if(defined('Account') && constant('Account') === constant('MainService'))
				define('AccountService', constant('MainService'));
			else
				define('AccountService', Domain);
		}

		// set MyAccount for use in all part of services
		if(!defined('MyAccount'))
			define('MyAccount', $myAccount);



		router::$base = Protocol.'://';
		if(router::$sub_is_fake)
			router::$base .= Service.'/'.(SubDomain? SubDomain.'/': null);
		else
			router::$base .= SubDomain.'.'.Service.'/';


		if(count(explode('.', SubDomain)) > 1)
			die("<p>Saloos only support one subdomain!</p>" );
		elseif(SubDomain === 'www')
		{
			   header('Location: '.router::get_storage('url_site'), true, 301);
		}
	}

	public function check_router()
	{
		// Check connection protocol and return related value
		if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 )
			$this->set_protocol("https");
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
			$this->set_protocol($_SERVER['HTTP_X_FORWARDED_PROTO']);
		else
			$this->set_protocol('http');

		if(!defined('Protocol'))
			define('Protocol', $this->get_Protocol());


		$this->check_property_router();
		$this->check_method_router();
		$this->check_class_router();
	}

	public static function check_property_router(){
		if(count(self::$url_array) < 2) return;
		self::$url_array_property = $urls = array_slice(self::$url_array, 2);
	}

	public static function check_method_router(){
		if(count(self::$url_array) >= 2 && !empty(self::$url_array[1])){
			if(preg_match("[=]", self::$url_array[1])){
				self::$method = 'home';
				self::add_url_property(self::$url_array[1]);
			}else{
				self::$method = self::$url_array[1];
			}
		}else{
			self::$method = 'home';
		}
	}

	public static function check_class_router(){
		if(count(self::$url_array) >= 1){
			if(preg_match("[=]", self::$url_array[0])){
				self::$class = 'home';
				if(self::$method != 'home'){
					self::add_url_property(self::$method);
					self::$method = 'home';
				}
				self::add_url_property(self::$url_array[0]);
			}else{
				self::$class = self::$url_array[0];

			}
		}else{
			self::$class = 'home';
		}
	}
}
?>