<?php
namespace lib\mvc;

use \lib\debug;
use \lib\utility;
use \lib\utility\Cookie;


class model extends \lib\model
{
	// this function get table name and id then related record of it. table name and id can set
	// but if user don't pass table name or id,
	// function use current real method name get from url for table name and current parameter for id
	public function datarow($_table = null, $_id = null, $_metatable = false)
	{
		if (!$_table)
			$_table = $this->module();

		// if myid parameter set use it else use url parameter for myid
		if (!$_id)
			$_id    = $this->childparam();

		$tmp_result = $this->sql()->table($_table)->where('id', $_id)->select();

		if ($tmp_result->num() == 1)
		{
			$tmp_result = $tmp_result->assoc();
			// add meta table rows as filed to datarow, can access via meta in datarow
			if($_metatable)
			{
				$prefix = substr($_table, 0, -1) .'meta';

				// $metas  = $this->sql()->table($prefix.'s')->where('post_id', $_id)
				$metas  = $this->sql()->table('options')->where('post_id', $_id)
					->field($prefix.'_key', $prefix.'_value')->select()->allassoc();

				foreach ($metas as $key => $value)
				{
					$myval = $value[$prefix.'_value'];
					if(substr($myval, 0,1) === '{')
						$myval = json_decode($myval, true);

					$tmp_result['meta'][$value[$prefix.'_key']] = $myval;
				}
			}

			return $tmp_result;
		}

		elseif($tmp_result->num() > 1)
			\lib\error::access(T_("id is found 2 or more times. it's imposible!"));

		else
		{
			\lib\error::access(T_("Url incorrect: id not found"));
			return false;
		}

		return null;
	}



	/**
		Common Operate like insert, update, delete in control panel and other place ---------------------- Start
	**/
	// *************************************************************************************** Query Creator
	// create query string automatically form getTable class field data
	// the add and edit function use this function for create query
	public function create_query($_type = null, $_id = null)
	{
		$qry_module = $this->module(SubDomain);
		$qry_table  = 'table'.ucfirst($qry_module);
		$qry        = $this->sql()->$qry_table();


		// in update type save record data and check if change set this else don't need to set
		if($_type == 'update')
		{
			$mydatarow = $this->datarow($qry_module, $_id);
			$not_change = true;
		}

		// get all fields of table and filter fields name for show in datatable
		// access from columns variable
		// check if datatable exist then get this data
		$incomplete_fields = array();
		$fields            = \lib\sql\getTable::get($qry_module);
		// var_dump(utility::post());
		// var_dump($qry_module);

		foreach ($fields as $key => $value)
		{
			// if this field can exist in query string
			if($value['query'])
			{
				$tmp_setfield = 'set'.ucfirst($key);
				$tmp_value    = utility::post($value['value']);
				$tmp_value    = trim($tmp_value);

				// if user fill box and post data for this field add to query string
				if($tmp_value || $tmp_value === '0')
				{
					// in update type check for change or not
					if($_type == 'update')
					{
						// if change add to query string and set it
						if($mydatarow[$key] != $tmp_value)
						{
							$qry = $qry->$tmp_setfield($tmp_value);
							$not_change = false;
						}
					}
					else
						$qry = $qry->$tmp_setfield($tmp_value);
				}

				// else if this table contain user_id then use logined user id
				elseif($key=='user_id')
					$qry = $qry->$tmp_setfield($this->login('id'));

				// else if user must fill this field, save the name and send it as incomplete
				elseif(!$value['null'])
				{
					// $incomplete_fields[$key] = $value['value'];
					array_push($incomplete_fields, $value['value']);
				}
			}
		}

		// on cp depending on module add some variable to query
		if(SubDomain === 'cp')
		{
			switch ($this->module())
			{
				case 'tags':
					if(count($incomplete_fields) === 3)
					{
						$qry_module        = 'terms';
						$incomplete_fields = null;
						$term_url          = utility::post('slug');
						$qry = $qry->setTerm_type('tag')->setTerm_url($term_url);
					}
					break;

				case 'categories':
					if(count($incomplete_fields) === 3)
					{
						$qry_module        = 'terms';
						$incomplete_fields = null;
						$term_url          = utility::post('slug');
						$qry = $qry->setTerm_type('cat')->setTerm_url($term_url);
					}
					break;

				case 'pages':
					$qry = $qry->setPost_type('page');
					$qry_module = 'posts';
					break;
			}
		}

		if($incomplete_fields)
		{
			debug::error(T_("all require fields must fill"), json_encode($incomplete_fields));
			// return false;
		}

		if($_type == 'update' && $not_change)
		{
			debug::error(T_("some fields must be change for update!"));
			// return false;
		}
		// var_dump($qry);exit();
		return $qry;
	}


	// *************************************************************************************** Insert
	// call this function and add a new record!
	// this function use create_query func to create a query string then add
	public function insert($_qry = null)
	{
		// if user pass the qry use it else use our automatic creator
		// $myqry = $_qry? $_qry: null;

		if(!$_qry)
		{
			$_qry = $this->create_query(__FUNCTION__);
			// if all require fields not filled then show error and pass invalid fileds name
			if(!$_qry)
				return false;
		}

		return $this->post_commit($_qry);
	}


	// *************************************************************************************** Update
	// call this func and edit current record automatically!
	// this function use create_query func to create a query string then edit
	public function update($_qry = null, $_id = null)
	{
		// if user pass the qry use it else use our automatic creator
		// $myqry = $_qry? $_qry: null;

		if(!$_qry)
		{
			$tmp_id = $_id? $_id: $this->childparam('edit');
			// debug::true(T_("id: ").$tmp_id);

			$_qry   = $this->create_query(__FUNCTION__, $tmp_id);
			// if all require fields not filled then show error and pass invalid fileds name
			if(!$_qry)
				return false;

			$_qry   = $_qry->whereId($tmp_id);
		}

		return $this->put_commit($_qry);
	}


	// *************************************************************************************** Delete
	// call this func and delete specefic record easily!
	// if you want to delete specefic query you must pass all query except ->delete() at end
	public function delete($_qry = null, $_id = null, $_table = null)
	{
		// if user pass the qry use it else use our automatic creator
		// $myqry = $_qry? $_qry: null;

		if(!$_qry)
		{
			$tmp_table  = $_table? $_table: 'table'.ucfirst($this->module());
			$tmp_id     = $_id?    $_id:    $this->childparam('delete');
			$tmp_id     = $tmp_id? $tmp_id: \lib\utility::post('id');
			$_qry       = $this->sql()->$tmp_table()->whereId($tmp_id);
			// var_dump($_qry);
		}
		if(!$_qry->select()->num())
		{
			debug::error(T_("id does not exist!"));
			return false;
		}

		return $this->delete_commit($_qry);
	}

	// ************************************************************************************** Start commits
	protected function post_commit($_qry)
	{
		$qry = $_qry->insert();
		// var_dump($_qry);exit();
		// ======================================================
		// you can manage next event with one of these variables,
		// commit for successfull and rollback for failed
		// if query run without error means commit
		$this->commit(function()
		{
			debug::true(T_("Insert Successfully"));
		} );

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::title(T_("Transaction error").': ');
		} );
		return $qry->LAST_INSERT_ID();
	}

	protected function put_commit($_qry)
	{
		$_qry = $_qry->update();
		// var_dump($_qry); exit();
		// ======================================================
		// you can manage next event with one of these variables,
		// commit for successfull and rollback for failed
		//
		// if query run without error means commit
		$this->commit(function()
		{
			debug::true(T_("Update Successfully"));
		} );

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::title(T_("Transaction Error").': ');
		} );
	}

	protected function delete_commit($_qry)
	{
		$_qry = $_qry->delete();
		// var_dump($_qry);exit();
		// ======================================================
		// you can manage next event with one of these variables,
		// commit for successfull and rollback for failed
		//
		// if query run without error means commit
		$this->commit(function()
		{
			debug::true(T_("Delete Successfully"));
		} );

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::error(T_("Delete Failed!"));
		} );
	}

	// ************************************************************************************** End commits
	public function backup()
	{
		$this->sql()->backup();
	}
	/**
		Common Operate like insert, update, delete in control panel and other place ---------------------- End
	**/



	// check ssid in get return and after check set login data for user
	// check user permissions and validate session for disallow unwanted attack
	public function checkMainAccount($_type = null)
	{
		$_type = $_type !== null? $_type: $this->put_ssidStatus();
		// var_dump($_type);

		switch ($_type)
		{
			// user want to attack to our system! logout from system and show message
			case 'attack':
				$this->put_logout();
				\lib\error::bad(T_("you want hijack us!!?"));
				break;


			// only log out user from system
			case 'logout':
				$this->put_logout('redirect');
				break;


			// if user_id set in options table login user to system
			case is_numeric($_type):
				$mydatarow	= $this->sql()->tableUsers()->whereId($_type)->select()->assoc();
				$myfields = array('id',
										'user_mobile',
										'user_email',
										'user_displayname',
										'user_meta',
										'user_status',
										'user_permission',
										);
				$this->setLoginSession($mydatarow, $myfields);
				break;

			// ssid does not available on this sub domain
			case 'notlogin':
				$this->put_logout('redirect');
				break;

			default:
				break;
		}
	}

	// check status of
	public function put_ssidStatus()
	{
		$myreferer         = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: null;
		$mytrusturl        = $this->url('account').'/login';
		$is_trustreferer   = $mytrusturl === substr($myreferer, 0, strlen($mytrusturl))? true: false;

		if($is_trustreferer === false)
		{
			$myfrom = utility::get('from');
			$is_trustreferer = $myfrom === 'login'? true: false;
			// var_dump($is_trustreferer);
		}

		// set ssid from session
		$myssid = isset($_SESSION['ssid'])? $_SESSION['ssid']: null;

		// if ssid does not exist return null
		if($myssid === null)
			return 'notlogin';



		// ***************************************************** CHECK LOGIN TIME UNDER 1 MIN
		// whereId("<", 10)
		// whereTime('<', 2015)->andTime('>', 2014)
		$tmp_result    = $this->sql()->table('options')
									->where ('option_cat',    'cookie_token')
									->and   ('option_key',    ClientIP)
									->and   ('option_value',  $myssid)
									->and   ('option_status', 'enable')
									->select()
									->assoc();


		if(!is_array($tmp_result))
			return 'attack';

		// if user passed ssid is correct then update record and set login sessions
		if($tmp_result['option_status'] === 'enable')
		{
			$qry	= $this->sql()->table('options')
						->set   ('option_status', 'expire')
						->where ('option_cat',    'cookie_token')
						->and   ('option_key',    ClientIP)
						->and   ('option_value',  $myssid)
						->and   ('option_status', 'enable');
			$sql	= $qry->update();

			$this->commit();
			$this->rollback();

			return $tmp_result['user_id'];
		}

		// for second page user check or antoher website after login in first one
		if($tmp_result['usermeta_status'] === 'expire')
			return $tmp_result['user_id'];

		// if code is disable with logout then return logout
		// this condition is occur when user logout form main service
		if($tmp_result['usermeta_status'] === 'disable')
			return 'logout';

		return 'attack';
	}


	// remove sessions and update ssid record in db for logout user from system
	public function put_logout($_status = null)
	{
		$_ssid = isset($_SESSION['ssid'])? $_SESSION['ssid']: null;

		// unset and destroy session then regenerate it
		session_unset();
		if(session_status() === PHP_SESSION_ACTIVE)
		{
			session_destroy();
			session_regenerate_id(true);
		}

		if($_ssid === null)
			return null;

		// login user to system and set status to expire
		$qry	= $this->sql()->table ('options')
							->set     ('option_status', 'disable')
							->where   ('option_cat',    'cookie_token')
							->and     ('option_key',    ClientIP)
							->and     ('option_value',  $_ssid);
		$sql	= $qry->update();


		$this->commit(function() { debug::true(T_("logout successfully")); });
		$this->rollback();
		// debug::true(T_("logout successfully out"));

		// $_SESSION['debug'][md5('http://ermile.dev')] = debug::compile();
		// var_dump($_SESSION['debug']);
		// exit();

		// var_dump('you are logout form system but redirect is not work!');

		if($_status === 'redirect')
		{
			$this->redirector()->set_domain()->set_url(); //->redirect();
			$this->model()->_processor();
		}
		return null;
	}

	// check referrer and redirect to specefic service
	protected function setLogin($_id, $_redirect = true)
	{
		$tmp_domain = null;
		$mycode     = $this->setLoginToken($_id);
		$this->checkMainAccount($_id);
		$myreferer  = utility\Cookie::read('referer');
		utility\Cookie::delete('referer');

		if($_redirect)
		{
			if($myreferer === 'jibres' || $myreferer === 'talambar')
				$tmp_domain = $myreferer .'.'. $this->url('tld');

			$this->redirector()->set_domain($tmp_domain)->set_url('?ssid='.$mycode);
		}
	}


	// Create Token and add to db for cross login
	// if don't pass a fields name use default data for fill user session
	protected function setLoginToken($_id)
	{
		// you can change the code way easily at any time!
		$mycode	= md5('^_^'.$_id.'_*Ermile*_'.date('Y-m-d H:i:s').'^_^');
		$qry		= $this->sql()->table('options')
									->set('user_id',      $_id)
									->set('option_cat',   'cookie_token')
									->set('option_key',   ClientIP)
									->set('option_value', $mycode);
		$sql		= $qry->insert();

		$_SESSION['ssid'] = $mycode;

		$this->commit(function()   { });
		$this->rollback(function() { });

		return $mycode;
	}


	// Pass a datarow of userdata and field for set in user session
	// if don't pass a fields name use default data for fill user session
	protected function setLoginSession($_datarow, $_fields)
	{
		$_SESSION['user']      = [];
		$_SESSION['permission'] = [];
		foreach ($_fields as $value)
		{
			if(substr($value, 0, 5) === 'user_')
			{
				$_SESSION['user'][substr($value, 5)] = $_datarow[$value];
			}
			else
			{
				$_SESSION['user'][$value] = $_datarow[$value];
			}
		}

		if(isset($_datarow['user_permission']) && is_numeric($_datarow['user_permission']))
		{
			$this->setPermissionSession($_datarow['user_permission']);
		}
	}


	/**
	 * [setPermissionSession description]
	 * @param [type] $_permID [description]
	 */
	public function setPermissionSession($_permID = null)
	{
		// if permission is set for this user,
		// get permission detail and set in permission session
		if(!$_permID && isset($_SESSION['user']['permission']))
		{
			$_permID = $_SESSION['user']['permission'];
		}

		if(is_numeric($_permID))
		{
			$_SESSION['user']['permission'] = $_permID;
			$qry = $this->sql()->table('options')
				->where('option_cat',  'permissions')
				->and('option_key',    $_permID)
				// ->and('option_status', 'enable')
				->and('post_id',       '#NULL')
				->and('user_id',       '#NULL')
				->select();

			if($qry->num() == 1)
			{
				$qry    = $qry->assoc();
				$myMeta = $qry['option_meta'];

				if(substr($myMeta, 0,1) == '{')
				{
					$myMeta = json_decode($myMeta, true);
				}
				$_SESSION['permission'] = $myMeta;
			}
			else
			{
				// do nothing!
			}
		}
	}


	/**
	 * add visitors to related db
	 */
	public function addVisitor()
	{
		// var_dump(222);
		// return 0;
		// this function add each visitor detail in visitors table
		// var_dump($_SERVER['REMOTE_ADDR']);
		$url = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME']
				.( $_SERVER["SERVER_PORT"] != "80"? ":".$_SERVER["SERVER_PORT"]: '' ).$_SERVER['REQUEST_URI'];

		if (strpos($url,'favicon.ico') !== false)
			return false;

		$referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: null;
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$robot = 'no';
		$botlist = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
			"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
			"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
			"crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
			"msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
			"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
			"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
			"Butterfly","Twitturls","Me.dium","Twiceler", "inoreader");
		foreach($botlist as $bot)
		{
			if(strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
				$robot = 'yes';
		}

		$userid = isset($_SESSION['user']['id'])? $_SESSION['user']['id']: null;

		$qry		= $this->sql()->tableVisitors()
					->setVisitor_ip(ClientIP)
					->setVisitor_url(urlencode($url))
					->setVisitor_agent(urlencode($agent))
					->setVisitor_referer(urlencode($referer))
					->setVisitor_robot($robot)
					->setUser_id($userid)
					->setVisitor_createdate(date('Y-m-d H:i:s'));
		$sql		= $qry->insert();

		$this->commit(function()
		{
			// debug::true("Register sms successfully");
		});

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			// debug::fatal("Register sms failed!");
		});

		// $sQl = new dbconnection_lib;
		// $sQl->query("COMMIT");
		// $sQl->query("START TRANSACTION");
	}


	/**
	 * this fuction check the url entered from user in database
	 * first search in posts and if not exist search in terms table
	 * @return [array] datarow of result if exist else return false
	 */
	function s_template_finder()
	{
		// first of all search in url field if exist return row data
		$tmp_result = $this->get_posts(true);
		if($tmp_result)
			return $tmp_result;

		// if url not exist in posts then search in terms table and if exist return row data
		$tmp_result = $this->get_terms(true);
		if($tmp_result)
			return $tmp_result;


		// else retun false
		return false;
	}

	public function get_posts($_forcheck = false)
	{
		$url     = $this->url('path');
		$preview = utility::get('preview');
		// search in url field if exist return row data
		$qry = $this->sql()->table('posts')->where('post_url', $url);
		if(!$preview)
			$qry = $qry->andPost_status('publish');

		$qry = $qry->groupOpen('g_language');
		$qry = $qry->and('post_language', substr(\lib\router::get_storage('language'), 0, 2));
		$qry = $qry->or('post_language', 'IS', 'NULL');
		$qry = $qry->groupClose('g_language');

		$qry = $qry->select();
		if($qry->num() == 1)
		{
			$datarow = $qry->assoc();

			if($_forcheck)
				return array( 'table' => 'posts',
							  'type' => $datarow['post_type'],
							  'slug' => $datarow['post_slug']
							);
			else
			{
				foreach ($datarow as $key => $value)
				{
					// if field contain json, decode it
					if(substr($value, 0, 1) == '{')
						$datarow[$key] = json_decode($value, true);
				}
				return $datarow;
			}
		}

		return false;
	}

	public function get_feeds($_forcheck = false)
	{
		$start    = utility::get('start');
		$lenght   = utility::get('lenght');
		// search in url field if exist return row data
		$qry = $this->sql()->table('posts')
				->field(
					'#post_language as `lang`',
					'#post_title as `title`',
					'#post_content as `desc`',
					'#post_url as `link`',
					'#post_publishdate as `date`'
					)
				->where('post_type', 'post')
				->and('post_status', 'publish')
				->limit(0, 10);

		$qry = $qry->groupOpen('g_language');
		$qry = $qry->and('post_language', substr(\lib\router::get_storage('language'), 0, 2));
		$qry = $qry->or('post_language', 'IS', 'NULL');
		$qry = $qry->groupClose('g_language');
		$qry = $qry->select();

		return $qry->allassoc();
	}

	public function get_terms($_forcheck = false)
	{
		$url = $this->url('path');
		$qry = $this->sql()->tableTerms()->whereTerm_url($url)->select();

		if($qry->num() == 1)
		{
			$datarow = $qry->assoc();

			if($_forcheck)
				return array( 'table' => 'terms',
							  'type' => $datarow['term_type'],
							  'slug' => $datarow['term_slug']
							);
			else
				return $datarow;
		}
		return false;
	}


	/**
	 * return navigations of curent page and get it form posts table
	 * @return [type] datarow
	 */
	public function sp_nav()
	{
		$url = $this->url('path');
		preg_match("#^((book/|)[^\/]*).*#", $url, $like_url);
		$like_url = $like_url[1];
		if($like_url == 'book') return null;

		$current_id = $this->sql()->table('posts')->where('post_url', $url)
			->and('post_status', 'publish')->select()->assoc();


		$nav_next = $this->sql()->table('posts')->where('id','>', $current_id['id'])
			->and('post_url', 'LIKE', "'$like_url%'")
			->and('post_status', 'publish')->order('id', 'ASC')->limit(0,1)
			->select()->assoc();

		$nav_prev = $this->sql()->table('posts')->where('id','<', $current_id['id'])
			->and('post_url', 'LIKE', "'$like_url%'")
			->and('post_status', 'publish')->order('id', 'DESC')->limit(0,1)
			->select()->assoc();


		$result_nav = ['current' => $current_id['id'] ];
		$result_nav['next'] = [ 'url' => $nav_next['post_url'], 'title' => $nav_next['post_title'] ];
		$result_nav['prev'] = [ 'url' => $nav_prev['post_url'], 'title' => $nav_prev['post_title'] ];

		if($nav_prev || $nav_next)
			return $result_nav;

		return null;
	}


	/**
	 * return list of cats in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_catsInTerm()
	{
		$url = $this->url('path');

		$qry_id = $this->sql()->table('terms')->where('term_url', $url)->select()->assoc('id');
		$datatable = $this->sql()->table('terms')->where('term_parent', $qry_id)->select()->allassoc();
		// var_dump($datatable);
		return $datatable;
	}


	/**
	 * return list of posts in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_postsInTerm()
	{
		$url = $this->url('path');
		if(substr($url, 0, 4) === 'tag/')
			$url = substr($url, 4, $url);


		if(substr($url, 0, 11) === 'book-index/')
		{
			preg_match("#^book-index/([^\/]*)(.*)$#", $url, $m);
			$url_raw = "book/$m[1]";


			if($m[2] !== '')
			{
				$qry = $this->sql()->table('posts')->where('post_status', 'publish')->order('id', 'ASC');
				$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
				$qry->join('terms')->on('id', '#termusages.term_id')->and('term_url', $url)->groupby('#posts.id');
			}
			else
			{
				$parent_id = $this->sql()->table('posts')->where('post_url', $url_raw)
					->and('post_status', 'publish')->select()->assoc('id');
				$qry = $this->sql()->table('posts')->where('post_parent', $parent_id)
					->and('post_status', 'publish')->order('id', 'ASC');
			}


			return $qry->select()->allassoc();
		}

		$qry = $this->sql()->table('posts')->where('post_status', 'publish')->order('id', 'DESC');
		$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
		$qry->join('terms')->on('id', '#termusages.term_id')->and('term_url', $url)->groupby('#posts.id');

		return $qry->select()->allassoc();
	}


	/**
	 * this function return the number of post needed with special condition
	 * @param  [type] $args argumats can pass from twig
	 * @return [type]       the array contain list of posts
	 */
	public function posts(...$args)
	{
		$qry = $this->sql()->tablePosts();
		$qry = $qry->andPost_type('post');


		// check passed value for exist and use it ----------------------------- number of post and offset
		// if pass number of records needed in first param
		if(isset($args[0]) && is_numeric($args[0]))
		{
			// if pass offset as 2nd param
			if(isset($args[1]) && is_numeric($args[1]))
				$qry = $qry->limit($args[1], $args[0]);
			// else if only pass record needed without offset
			else
				$qry = $qry->limit($args[0]);
		}
		// if dont pass through function use default value
		else
			$qry = $qry->limit(10);

		// check passed value for exist and use it ----------------------------- Language
		$post_language = array_column($args, 'language');
		if( $post_language && count($post_language) === 1)
		{
			$qry = $qry->and('post_language', $post_language);
		}
		// if dont pass through function use default value
		else
		{
			$qry = $qry->groupOpen('g_language');
			$qry = $qry->and('post_language', substr(\lib\router::get_storage('language'), 0, 2));
			$qry = $qry->or('post_language', 'IS', 'NULL');
			$qry = $qry->groupClose('g_language');
		}



		// check passed value for exist and use it ----------------------------- orderby
		$post_orderby = array_column($args, 'orderby');
		if( $post_orderby && count($post_orderby) === 1)
			$post_orderby = "order".ucfirst($post_orderby[0]);
		// if dont pass through function use default value
		else
			$post_orderby = "orderId";



		// check passed value for exist and use it ----------------------------- order
		$post_order = array_column($args, 'order');
		if( $post_order && count($post_order) === 1)
		{
			$post_order = $post_order[0];
			if(!is_array($post_order))
				$qry = $qry->$post_orderby($post_order);
		}
		// if dont pass through function use default value
		else
			$qry = $qry->$post_orderby('desc');



		// check passed value for exist and use it ----------------------------- status
		$post_status = array_column($args, 'status');
		if( $post_status && count($post_status) === 1)
		{
			$post_status = $post_status[0];
			// if pass in array splite it and create specefic query
			if(is_array($post_status))
			{
				foreach ($post_status as $value)
				{
					if ($value === reset($post_status))
					{
						$qry = $qry->groupOpen('g_status');
						$qry = $qry->andPost_status($value);
					}
					else
						$qry = $qry->orPost_status($value);
				}
				$qry = $qry->groupClose('g_status');
			}
			// if not array use the passed value
			else
				$qry = $qry->andPost_status($post_status);
		}
		// if dont pass through function use default value
		else
			$qry = $qry->andPost_status('publish');





		// check passed value for exist and use it ----------------------------- category
		$post_cat = array_column($args, 'cat');
		// INNER JOIN termusages ON posts.id = termusages.object_id
		// INNER JOIN terms ON termusages.term_id = terms.id
		// WHERE
		// termusages.termusage_type = 'posts'
		if( $post_cat && count($post_cat) === 1)
		{
			// $qry = $qry->query("SELECT `posts`.* FROM `posts` ");
			$post_cat = $post_cat[0];

			$qry->joinTermusages()->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
			// $qry->joinTerms()->whereId('#termusages.term_id')->andTerm_slug('#"statements"');

			// $obj = $qry->joinTerms();
			// $obj->whereId('#termusages.term_id')->andTerm_slug('#"statements"');


			$obj = $qry->joinTerms()->on('id', '#termusages.term_id')->groupby('#posts.id');
			// $obj->whereTerm_slug('#"statements"');



			// if pass in array splite it and create specefic query
			if(is_array($post_cat))
			{
				foreach ($post_cat as $value)
				{
					$opr = '=';
					if(substr($value, 0, 1) === '-')
					{
						$opr = '<>';
						$value = substr($value, 1);
					}


					if ($value === reset($post_cat))
					{
						// $qry = $qry->groupOpen('g_cat');
						$obj->andTerm_slug($opr, "$value");
					}
					else
					{
						$obj->orTerm_slug($opr, "$value");
					}
				}
				// $qry = $qry->groupClose('g_cat');
			}
			// if not array use the passed value
			else
			{
				$opr = '=';
				if(substr($post_cat, 0, 1) === '-')
				{
					$opr = '<>';
					$post_cat = substr($post_cat, 1);
				}
				$obj->andTerm_slug($opr, "$post_cat");
			}


			// $qry->joinUsers()->whereId('#kids.user_id')
			// 						->fieldUser_firstname("firstname")
		}



		$qry = $qry->select();
		// echo $qry->string();
		return $qry->allassoc();
	}


	/**
	 * create breadcrumb and location of it
	 * @return [type] [description]
	 */
	public function breadcrumb()
	{
		$_addr = $this->url('breadcrumb');
		$breadcrumb = array();

		foreach ($_addr as $key => $value)
		{
			if($key > 0)
				$breadcrumb[] = strtolower("{$breadcrumb[$key-1]}/$value");
			else
				$breadcrumb[] = strtolower("$value");
		}

		$qry = $this->sql()->table('posts')
			->where('post_url', 'IN' , "('".join("' , '", $breadcrumb)."')");
		$qry = $qry->select();
		$post_titles = $qry->allassoc('post_title');
		$post_urls = $qry->allassoc('post_url');

		if(count($breadcrumb) != $post_titles){
			$terms_qry = $this->sql()->table('terms')
				->where('term_url', 'IN' , "('".join("' , '", $breadcrumb)."')");
			$terms_qry = $terms_qry->select();
			$term_titles = $terms_qry->allassoc('term_title');
			$term_urls = $terms_qry->allassoc('term_url');
		}

		$br = array();
		foreach ($breadcrumb as $key => $value)
		{
			$post_key = array_search($value, $post_urls);
			$term_key = array_search($value, $term_urls);
			if($post_key !== false){
				$br[] = $post_titles[$post_key];
			}elseif($term_key !== false){
				$br[] = $term_titles[$term_key];
			}else{
				$br[] = T_($_addr[$key]);
			}
		}
		return $br;
		$qry = $qry->select()->allassoc();
		if(!$qry){
			return $_addr;
		}
		$br = array();
		foreach ($breadcrumb as $key => $value)
		{
			if ($value != $qry[$key]['post_url']){
				$br[] = T_($_addr[$key]);
				array_unshift($qry, '');
			}else{
				$br[] = $qry[$key]['post_title'];
			}
		}
		return $br;
	}

	/**
	 * get the list of pages
	 * @param  boolean $_select for use in select box
	 * @return [type]           return string or dattable
	 */
	public function sp_books_nav()
	{
		$myUrl  = \lib\router::get_url(-1);
		$result = ['cats' => null, 'pages' => null];
		$parent_search = null;

		switch (count($myUrl))
		{
			// book/book1
			case 2:
				$myUrl  = $this->url('path');
				$parent_search = 'id';
				break;
			// book/book1/jeld1
			case 3:
				$myUrl  = $this->url('path');
				$parent_search = 'parent';
				break;
			// book/book1/jeld1/page1
			case 4:
				$myUrl = $myUrl[0]. '/'. $myUrl[1]. '/'. $myUrl[2];
				$parent_search = 'parent';
				break;
			// on other conditions return false
			default:
				return false;
		}

		// get id of current page
		$qry = $this->sql()->table('posts')
			->where('post_type', 'book')
			->and('post_url', $myUrl)
			->and('post_status', 'publish')
			->field('id', '#post_parent as parent')
			->select();
		if($qry->num() != 1)
			return;

		$datarow = $qry->assoc();

		// get list of category or jeld
		$qry = $this->sql()->table('posts')
			->where('post_type', 'book')
			->and('post_status', 'publish')
			->and('post_parent', $datarow[$parent_search])
			->field('id', '#post_title as title', '#post_parent as parent', '#post_url as url')
			->select();
		if($qry->num() < 1)
			return;

		$result['cats'] = $qry->allassoc();
		$catsid         = $qry->allassoc('id');
		$catsid         = implode($catsid, ', ');

		// check has page on category or only in
		$qry2 = $this->sql()->table('posts')
			->where('post_type', 'book')
			->and('post_status', 'publish')
			->and('post_parent', 'IN', '('. $catsid. ')')
			->field('id');

		$qry2            = $qry2->select();
		$result['pages'] = $qry2->num();

		return $result;
	}

}
?>
