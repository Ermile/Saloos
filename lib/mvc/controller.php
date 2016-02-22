<?php
namespace lib\mvc;
use \lib\router;
class controller extends \lib\controller
{
	public function __construct()
	{
		parent::__construct();
		$myrep = router::get_repository_name();

		// running template base module for homepage
		if(\lib\router::get_storage('CMS') && $myrep === 'content' && method_exists($this, 's_template_finder') && get_class($this) === 'content\home\controller')
		{
			$this->s_template_finder();
		}
	}

	// Return login status without parameter
	// If you pass the name as all return all of user session
	// If you pass specefic user data field name return it
	public function login($_name = null)
	{
		if(isset($_name))
		{
			if($_name=="all")
				return isset($_SESSION['user'])? $_SESSION['user']: null;

			else
				return isset($_SESSION['user'][$_name])? $_SESSION['user'][$_name]: null;
		}

		if(isset($_SESSION['user']['id']))
			return true;
		else
			return false;
	}

	/**
	 * return
	 * @param  string $_loc  location
	 * @param  string $_type type of permission needed
	 * @return [type]        [description]
	 */
	public function access($_content = null, $_loc = null, $_type = null, $_block = null)
	{
		$myStatus = null;
		$su       = null;
		// if user is superviser then set su to true
		// permission id 1 is supervisior of system
		if(isset($_SESSION['user']['permission']) && $_SESSION['user']['permission'] === "1")
		{
			$su       = true;
			$suStatus = new \content_cp\permissions\controller;
			$suStatus = $suStatus->permListFill("su");
		}

		// if programmer not set content, give it automatically from address
		if($_content === 'all')
		{
			$myStatus = [];
			if($su)
			{
				foreach ($suStatus as $key => $value)
				{
					if(isset($value['enable']))
					{
						$myStatus[$key] = $value['enable'];
					}
				}
			}
			elseif(isset($_SESSION['permission']))
			{
				foreach ($_SESSION['permission'] as $key => $value)
				{
					if(isset($value['enable']))
					{
						$myStatus[$key] = $value['enable'];
					}
				}
			}
			return $myStatus;
		}
		elseif(!$_content)
		{
			$_content = router::get_repository_name();
			if($_content !== "content")
			{
				$_content = substr($_content, strpos($_content, '_') + 1);
			}
		}
		if(!isset($suStatus[$_content]) || !isset($suStatus[$_content]['modules']))
		{
			$su = false;
		}

		// if user want specefic location
		if($_loc == 'all')
		{
			if($su)
			{
				$myStatus = $suStatus[$_content]['modules'];
			}
			elseif(isset($_SESSION['permission'][$_content]['modules']))
			{
				$myStatus = $_SESSION['permission'][$_content]['modules'];
			}
		}
		elseif($_loc)
		{
			if($_type)
			{
				if($su)
				{
					if(isset($suStatus[$_content]['modules'][$_loc][$_type]))
					{
						$myStatus = $suStatus[$_content]['modules'][$_loc][$_type];
					}
				}
				elseif(isset($_SESSION['permission'][$_content]['modules'][$_loc][$_type]))
				{
					$myStatus = $_SESSION['permission'][$_content]['modules'][$_loc][$_type];
				}
			}
			else
			{
				if($su)
				{
					$myStatus = $suStatus[$_content]['modules'][$_loc];
				}
				elseif(isset($_SESSION['permission'][$_content]['modules'][$_loc]))
				{
					$myStatus = $_SESSION['permission'][$_content]['modules'][$_loc];
				}
			}
		}
		// else if not set location and only want enable status
		else
		{
			if($su)
			{
				$myStatus = $suStatus[$_content]['enable'];
			}
			elseif(isset($_SESSION['permission'][$_content]['enable']))
			{
				$myStatus = $_SESSION['permission'][$_content]['enable'];
			}
		}


		if(!$myStatus)
		{
			if($_block === "notify" && $_type && $_loc)
			{
				$msg = null;
				switch ($_type)
				{
					case 'view':
						$msg = "You can't view this part of system";
						break;

					case 'add':
						$msg = T_("You can't add new") .' '. T_($_loc);
						break;

					case 'edit':
						$msg = T_("You can't edit") .' '. T_($_loc);
						break;

					case 'delete':
						$msg = T_("You can't delete") .' '. T_($_loc);
						break;

					default:
						$msg = "You can't access to this part of system";
						break;
				}
				$msg = $msg. "<br/> ". T_("Because of your permission");

				\lib\debug::error(T_($msg));
				$this->model()->_processor(object(array("force_json" => true, "force_stop" => true)));
			}
			elseif($_block)
			{
				\lib\error::access(T_("You can't access to this page!"));
			}
			else
			{
				// do nothing!
			}
		}

		return $myStatus;
	}


	// return module name for use in view or other place
	public function module($_type = null, $_fix = true)
	{
		if($_type == 'prefix')
			$mymodule	= substr(router::get_url(0), 0, -1);
		elseif($_type == 'array')
			$mymodule	= router::get_url(-1);
		else
			$mymodule	= router::get_url(0);

		if($_fix)
			$mymodule	= $mymodule? $mymodule: 'home';

		return $mymodule;
	}

	// return module name for use in view or other place
	public function child($_title = null)
	{
		$mychild = router::get_url(1);
		if(strrpos($mychild,'=') !== false)
		{
			$mychild = substr($mychild,0,strrpos($mychild,'='));
		}

		if(!$_title)
			return $mychild;

		if($mychild=='add')
			return T_('add new');

		if($mychild == 'edit')
			return T_('edit');

		if($mychild == 'delete')
			return T_('delete');

	}

	// if pass parameter return the property of it, else return value of child
	public function childparam($_name = null)
	{
		if($_name)
			return router::get_url_property($_name);
		else
			return router::get_url_property($this->child());
	}

	function regenerateSession($reload = false)
	{
		return;
	    // This token is used by forms to prevent cross site forgery attempts
	    if(!isset($_SESSION['nonce']) || $reload)
	        $_SESSION['nonce'] = md5(microtime(true));

	    if(!isset($_SESSION['IPaddress']) || $reload)
	        $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];

	    if(!isset($_SESSION['userAgent']) || $reload)
	        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];

	    //$_SESSION['user_id'] = $this->user->getId();

	    // Set current session to expire in 1 minute
	    $_SESSION['OBSOLETE'] = true;
	    $_SESSION['EXPIRES'] = time() + 60;

	    // Create new session without destroying the old one
	    session_regenerate_id(false);

	    // Grab current session ID and close both sessions to allow other scripts to use them
	    $newSession = session_id();
	    session_write_close();

	    // Set session ID to the new one, and start it back up again
	    session_id($newSession);
	    session_start();

	    // Don't want this one to expire
	    unset($_SESSION['OBSOLETE']);
	    unset($_SESSION['EXPIRES']);
	}

	public function checkSession()
	{
		return;
	    try{
	        if($_SESSION['OBSOLETE'] && ($_SESSION['EXPIRES'] < time()))
	            throw new Exception('Attempt to use expired session.');

	        if(!is_numeric($_SESSION['user_id']))
	            throw new Exception('No session started.');

	        if($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR'])
	            throw new Exception('IP Address mixmatch (possible session hijacking attempt).');

	        if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
	            throw new Exception('Useragent mixmatch (possible session hijacking attempt).');

	        if(!$this->loadUser($_SESSION['user_id']))
	            throw new Exception('Attempted to log in user that does not exist with ID: ' . $_SESSION['user_id']);

	        if(!$_SESSION['OBSOLETE'] && mt_rand(1, 100) == 1)
	        {
	            $this->regenerateSession();
	        }

	        return true;

	    }catch(Exception $e){
	        return false;
	    }
	}

	function convert_Num2En($string)
	{
		$persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
		$num = range(0, 9);
		return str_replace($persian, $num, $string);
	}

	function s_template_finder()
	{
		// if lang exist in module or subdomain remove it and continue
		$currentLang = substr(router::get_storage('language'), 0, 2);
		$defaultLang = substr(router::get_storage('defaultLanguage'), 0, 2);

		if($currentLang === SubDomain && $currentLang !== $defaultLang)
		{
			\lib\router::set_sub_domain(null);
		}
		// elseif($currentLang === $this->module() && $currentLang !== $defaultLang)
		// 	\lib\router::remove_url($currentLang);



		// continue find best template for this condition
		$mymodule    = $this->module();
		if($mymodule == 'home')
		{
			// if home template exist show it
			if( is_file(root.'content/template/home.html') )
				$this->display_name	= 'content\template\home.html';
			$this->get()->ALL();
			return 0;
		}


		elseif($mymodule == 'search')
		{
			if( is_file(root.'content/template/search.html') )
				$this->display_name	= 'content\template\search.html';

			$this->get()->ALL();
			return;
		}


		elseif($mymodule == 'feed')
		{
			$site_title    = $this->view()->data->site['title'];
			$site_desc     = $this->view()->data->site['desc'];
			$site_protocol = $this->url('MainProtocol'). '://';
			$site_url      = $this->url('MainSite');

			$rss = new \lib\utility\RSS($site_protocol, $site_url, $site_title, $site_desc);
			// add posts
			foreach ($this->model()->get_feeds() as $row)
				$rss->addItem($row['link'], $row['title'], $row['desc'], $row['date']);

			$rss->create();

			// \lib\utility\RSS::create();
			exit();
			return;
		}

		$myurl = null;
		if(!empty(db_name))
		{
			$myurl = $this->model()->s_template_finder();
		}
		else
			$myurl = null;

		// if url does not exist show 404 error
		if(!$myurl)
		{
			// var_dump($mymodule);
			// var_dump(\lib\router::get_storage('language'));
			// if user entered url contain one of our site language

			$currentPath = $this->url('path', '_');
			// if custom template exist show this template
			if( is_file(root.'content/template/static_'. $currentPath. '.html') )
			{
				$this->display_name	= 'content\template\static_'. $currentPath. '.html';
			}
			// elseif 404 template exist show it
			elseif( is_file(root.'content/template/404.html') )
			{
				header("HTTP/1.1 404 NOT FOUND");
				$this->display_name	= 'content\template\404.html';
			}
			// else show saloos default error page
			else
			{
				\lib\error::page(T_("Does not exist!"));
				return;
			}
		}

		// elseif template type exist show it
		elseif( is_file(root.'content/template/'.$myurl['type'].'-'.$myurl['slug'].'.html') )
			$this->display_name	= 'content\template\\'.$myurl['type'].'-'.$myurl['slug'].'.html';

		// elseif template type exist show it
		elseif( is_file(root.'content/template/'.$myurl['type'].'.html') )
			$this->display_name	= 'content\template\\'.$myurl['type'].'.html';

		// elseif template type exist show it
		elseif( is_file(root.'content/template/'.$myurl['table'].'.html') )
			$this->display_name	= 'content\template\\'.$myurl['table'].'.html';

		// elseif default template exist show it else use homepage!
		elseif( is_file(root.'content/template/dafault.html') )
			$this->display_name	= 'content\template\dafault.html';

		$this->route_check_true = true;

		$this->get(null, $myurl['table'])->ALL();
		// $this->get()->ALL();
	}
}
?>