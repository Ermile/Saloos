<?php
namespace lib\view;

trait twigAddons{
	/**
	 * add twig filter
	 * @param string $method [description]
	 */
	public function add_twig_filter($method){
		if(!isset($this->twig['filter'])) $this->twig['filter'] = array();
		array_push($this->twig['filter'], $method);
	}
	/**
	 * add twig function
	 * @param string $method [description]
	 */
	public function add_twig_function($method){
		if(!isset($this->twig['function'])) $this->twig['function'] = array();
		array_push($this->twig['function'], $method);
	}

	/**
	 * attach twig extentions
	 * @param  object $twig
	 */
	public function twig_Extentions($twig){
		foreach ($this->twig as $key => $value) {
			$ext="add".ucfirst($key);
			foreach ($value as $k => $v) {
				$method_name = "twig_{$key}_$v";
				$twig->$ext($this->$method_name());
			}
		}
	}

	public function twig_macro($name){
		if(!isset($this->data->twig_macro)) $this->data->twig_macro = array();
		if(array_search($name, $this->data->twig_macro) === false) array_push($this->data->twig_macro, $name);
	}

	/**
	 * twig custom filter for static file cache
	 */
	public function twig_filter_fcache(){
		return new \Twig_SimpleFilter('fcache', function ($string) {
			if(file_exists($string)){
				return $string.'?'.filemtime($string);
			}
		});
	}

	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_jdate()
	{
		return new \Twig_SimpleFilter('jdate', function ($_string, $_format ="Y/m/d"){
			return \lib\utility\jdate::date($_format, $_string);
		});
	}

	/**
	 * twig custom filter for convert date to best type of showing
	 */
	public function twig_filter_sdate()
	{
		return new \Twig_SimpleFilter('sdate', function ($_string, $_max ="day", $_format ="Y/m/d")
		{
			return \lib\utility::humanTiming($_string, $_max, $_format, $this->data->site['currentlang']);
		});
	}

	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_readableSize()
	{
		return new \Twig_SimpleFilter('readableSize', function ($_string, $_type = 'file', $_emptyTxt = null)
		{
			return \lib\utility\upload::readableSize($_string, $_type, $_emptyTxt);
		});
	}

	/**
	 * twig custom filter for dump with php
	 */
	public function twig_function_dump(){
		return new \Twig_SimpleFunction('dump', function () {
		});
	}

	public function twig_function_result(){
		return new \Twig_SimpleFunction('result', function () {
			var_dump($this->model());
		});
	}

	public function twig_function_breadcrumb()
	{
		return new \Twig_SimpleFunction('breadcrumb', function ($_path = null, $_direct = null)
		{
			// if user dont pass a path give it from controller
			if(!$_path)
			{
				$myurl = $this->model()->breadcrumb();
				$_path = $this->url('breadcrumb');
			}
			$direct = null;
			if($_direct === true)
			{
				$direct = "data-direct";
			}
			$currentUrl = null;
			$result = '<a href="/" tabindex="-1" '. $direct.'><i class="fa fa-home"></i> '.T_('Home').'</a>';

			foreach ($myurl as $key => $part)
			{
				if($part != '$')
				{
					$currentUrl .= $_path[$key];
					$location   = T_(ucfirst($part));
					if(end($myurl) === $part)
					{
						$result .= "<a>$location</a>";
					}
					else
					{
						$baseURL = $this->data->url->base;
						$result .= "<a href='$baseURL$currentUrl' tabindex='-1'>$location</a>";
					}
				}
			}

			echo $result;
		});
	}

	public function twig_function_posts()
	{
		return new \Twig_SimpleFunction('posts', function ()
		{
			$posts  = $this->model()->posts(...func_get_args());
			$html   = array_column(func_get_args(), 'html');

			if($html)
			{
				$result = null;
				foreach ($posts as $item)
					$result .= "<a href='/".$item['url']."'>".$item['title']."</a>";

				echo $result;
			}
			else
			{
				return $posts;
			}

		});
	}
}
?>