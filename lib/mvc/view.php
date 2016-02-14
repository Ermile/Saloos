<?php
namespace lib\mvc;

class view extends \lib\view
{
	public function mvc_construct()
	{
		// define default value for url
		$this->url->full             = $this->url('full');       // full url except get parameter with http[s]
		$this->url->path             = $this->url('path');       // full path except parameter and domain name
		$this->url->breadcrumb       = $this->url('breadcrumb'); // full path in array for using in breadcrumb
		$this->url->domain           = $this->url('domain');     // domain name like 'ermile'
		$this->url->base             = $this->url('base');

		$this->url->tld              = $this->url('tld');        // domain ltd like 'com'
		$this->url->raw              = Service;                  // domain name except subdomain like 'ermile.com'
		$this->url->root             = $this->url('root');
		$this->url->static           = $this->url->root. 'static/';
		$this->url->protocol         = Protocol;

		$this->url->account          = $this->url('account');
		$this->url->MainStatic       = $this->url('MainService'). '/'.'static/';
		$this->url->MainSite         = $this->url('MainSite');
		$this->url->MainProtocol     = $this->url('MainProtocol');
		$this->url->SubDomain        = SubDomain? SubDomain.'.': null;


		// return all parameters and clean it
		$this->url->param            = \lib\utility::get(null, true);
		$this->url->all              = $this->url->full.$this->url->param;


		$this->data->site['title']   = T_("Saloos");
		$this->data->site['desc']    = T_("Another Project with Saloos");
		$this->data->site['slogan']  = T_("Saloos is an artichokes for PHP programming!!");
		if(defined('LangList') && constant('LangList'))
			$this->data->site['langlist'] = unserialize(constant('LangList'));
		else
			$this->data->site['langlist'] = ['fa_IR' => 'فارسی', 'en_US' => 'English'];

		$this->data->site['currentlang'] = substr(\lib\router::get_storage('language'), 0, 2);

		$this->data->page['title']   = null;
		$this->data->page['desc']    = null;
		$this->data->page['special'] = null;

		$this->data->bodyclass       = null;
		$this->data->module          = $this->module();
		$this->data->child           = $this->child();
		$this->data->login           = $this->login('all');
		$this->data->perm            = $this->access(null, 'all');
		$this->data->permContent     = $this->access('all');

		// define default value for global
		$this->global->title         = null;
		$this->global->login         = $this->login();


		$this->global->lang          = \lib\router::get_storage('language');
		$this->global->direction     = \lib\router::get_storage('direction');
		$this->global->id            = $this->url('path','_');


		// define default value for include
		$this->include->newline      = PHP_EOL;
		$this->include->css_main     = false;
		$this->include->css_ermile   = true;
		$this->include->js_main      = true;
		$this->include->css          = true;
		$this->include->js           = true;
		$this->include->fontawesome  = null;
		$this->include->datatable    = null;
		$this->include->telinput     = null;
		$this->include->lightbox     = null;
		$this->include->editor       = null;



		if(method_exists($this, '_construct'))
		{
			$this->_construct();
		}

		if(isset($this->url->MainStatic) && $this->url->MainStatic)
			$this->url->myStatic = $this->url->MainStatic;
		elseif(isset($this->url->MainStatic))
			$this->url->myStatic = $this->url->static;

		if(method_exists($this, 'config')){
			$this->config();
		}
		if(method_exists($this, 'options')){
			$this->options();
		}

		$this->set_title();

		if(defined('SaveAsCookie') && SaveAsCookie)
		{
			$mygetlist = \lib\utility::get(null, 'raw');
			if($mygetlist)
			{
				// var_dump(7); exit();
				foreach ($mygetlist as $name => $value)
				{
					if($name === 'ssid')
						$_SESSION['ssid'] = $value;

					elseif( !($name === 'dev' || $name === 'lang') )
						\lib\utility\Cookie::write($name, $value);
				}

				// remove get parameter from url
				header('Location: '. $this->url('full'));
			}
		}

		// check main  ********************************************* CHECK FOR ONLY IN FIRST PAGE IN RIGHT PLACE
		// in all page like ajax request must be run
		if(AccountService === MainService)
		{
			$this->model()->checkMainAccount();
			$this->controller()->checkSession();
		}
		// if logvisitor on set visitors
		if(defined('LogVisitors') && constant('LogVisitors'))
		{
			$this->model()->addVisitor();
		}
	}


	// set title for pages depending on condition
	public function set_title()
	{
		if($this->data->page['title'])
		{
			// for child page set the
			if($this->data->child && SubDomain === 'cp')
			{
				if(substr($this->module(), -3) === 'ies')
				{
					$moduleName = substr($this->module(), 0, -3).'y';
				}
				elseif(substr($this->module(), -1) === 's')
				{
					$moduleName = substr($this->module(), 0, -1);
				}
				else
				{
					$moduleName = $this->module();
				}

				$childName = $this->child(true);
				if($childName)
				{
					$this->data->page['title'] = T_($childName).' '.T_($moduleName);
				}
			}

			// set user-friendly title for books
			if($this->module() === 'book')
			{
				$breadcrumb = $this->model()->breadcrumb();
				$this->data->page['title'] = $breadcrumb[0] . ' ';
				array_shift($breadcrumb);

				foreach ($breadcrumb as $value)
				{
					$this->data->page['title'] .= $value . ' - ';
				}
				$this->data->page['title'] = substr($this->data->page['title'], 0, -3);

				$this->data->parentList = $this->model()->sp_books_nav();
			}

			if($this->data->page['special'])
				$this->global->title = $this->data->page['title'];
			else
				$this->global->title = $this->data->page['title'].' | '.$this->data->site['title'];
		}
		else
			$this->global->title = $this->data->site['title'];

		$this->global->short_title = substr($this->global->title, 0, strrpos(substr($this->global->title, 0, 120), ' ')) . '...';
	}


	function view_posts()
	{
		$this->data->post = array();
		$tmp_result       = $this->model()->get_posts();
		$tmp_fields       = array(	'id'               =>'id',
									'post_language'    =>'language',
									'post_title'       =>'title',
									'post_slug'        =>'slug',
									'post_content'     =>'content',
									'post_meta'        =>'meta',
									'post_type'        =>'type',
									'post_url'         =>'url',
									'post_comment'     =>'comment',
									'post_count'       =>'count',
									'post_status'      =>'status',
									'post_parent'      =>'parent',
									'user_id'          =>'user',
									'post_publishdate' =>'publishdate',
									'date_modified'    =>'modified'
								);
		foreach ($tmp_fields as $key => $value)
		{
			if(is_array($tmp_result[$key]))
				$this->data->post[$value] = $tmp_result[$key];
			else
				$this->data->post[$value] = html_entity_decode(trim($tmp_result[$key]));
		}

		// set page title
		$this->data->page['title'] = $this->data->post['title'];
		$this->data->page['desc'] = \lib\utility\Excerpt::extractRelevant($this->data->post['content'], $this->data->page['title']);
		// var_dump($this->data->post['title']);
		$this->set_title();

		$this->data->nav = $this->model()->sp_nav();
	}

	function view_terms()
	{
		$this->data->post = array();
		$tmp_result       = $this->model()->get_terms();
		$tmp_fields       = array(	'id'            =>'id',
									'term_language' =>'language',
									'term_type'     =>'type',
									'term_title'    =>'title',
									'term_slug'     =>'slug',
									'term_url'      =>'url',
									'term_desc'     =>'desc',
									'term_parent'   =>'parent',
									'date_modified' =>'modified'
								);
		foreach ($tmp_fields as $key => $value)
			$this->data->post[$value] = html_entity_decode($tmp_result[$key]);

		$this->data->page['title'] = $this->data->post['title'];

		// generate datatable
		$this->data->datatable = $this->model()->sp_postsInTerm();

		$this->data->datatable_cats = $this->model()->sp_catsInTerm();
		// switch ($this->data->module)
		// {
		// 	case 'book-index':
		// 		$this->data->datatable_cats = $this->model()->sp_catsInTerm();
		// 		break;
		// }

		// set title of page after add title
		$this->set_title();
	}

}
?>