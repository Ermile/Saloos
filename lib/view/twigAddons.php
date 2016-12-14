<?php
namespace lib\view;

trait twigAddons
{
	/**
	 * add twig filter
	 * @param string $method [description]
	 */
	public function add_twig_filter($method)
	{
		if(!isset($this->twig['filter'])) $this->twig['filter'] = array();
		array_push($this->twig['filter'], $method);
	}


	/**
	 * add twig function
	 * @param string $method [description]
	 */
	public function add_twig_function($method)
	{
		if(!isset($this->twig['function'])) $this->twig['function'] = array();
		array_push($this->twig['function'], $method);
	}


	/**
	 * attach twig extentions
	 * @param  object $twig
	 */
	public function twig_Extentions($twig)
	{
		foreach ($this->twig as $key => $value)
		{
			$ext="add".ucfirst($key);
			foreach ($value as $k => $v)
			{
				$method_name = "twig_{$key}_$v";
				$twig->$ext($this->$method_name());
			}
		}
	}


	/**
	 * [twig_macro description]
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function twig_macro($name)
	{
		if(!isset($this->data->twig_macro)) $this->data->twig_macro = array();
		if(array_search($name, $this->data->twig_macro) === false) array_push($this->data->twig_macro, $name);
	}


	/**
	 * twig custom filter for static file cache
	 */
	public function twig_filter_fcache()
	{
		return new \Twig_SimpleFilter('fcache', function ($string)
		{
			if(file_exists($string))
			{
				return $string.'?'.filemtime($string);
			}
		});
	}


	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_jdate()
	{
		return new \Twig_SimpleFilter('jdate', function ($_string, $_format ="Y/m/d", $_convert = true)
		{
			return \lib\utility\jdate::date($_format, $_string, $_convert);
		});
	}


	/**
	 * twig custom filter for convert date to best type of showing on each language
	 * tdate means translated date
	 */
	public function twig_filter_tdate()
	{
		return new \Twig_SimpleFilter('tdate', function ($_string, $_format ="Y/m/d", $_convert = true)
		{
			$result = $_string;
			if($this->data->site['currentlang'] == 'fa')
			{
				$result = \lib\utility\jdate::date($_format, $_string, $_convert);
			}
			else
			{
				$result = date($_format, strtotime($_string));
			}

			return $result;
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
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_persian()
	{
		return new \Twig_SimpleFilter('persian', function ($_number)
		{
			return \lib\utility\human::number($_number, $this->data->site['currentlang']);
		});
	}


	/**
	 * [twig_filter_exist description]
	 * @return [type] [description]
	 */
	public function twig_filter_exist()
	{
		return new \Twig_SimpleFilter('exist', function ($_file, $_alternative = null)
		{
			$result = \lib\utility\file::alternative($_file, $_alternative);
			return $result;
		});
	}


	public function twig_filter_humantime()
	{
		return new \Twig_SimpleFilter('humantime', function ()
		{
			$result = \lib\utility\human::time(...func_get_args());
			return $result;
		});
	}


	/**
	 * [twig_filter_decode description]
	 * @return [type] [description]
	 */
	public function twig_filter_decode()
	{
		return new \Twig_SimpleFilter('decode', function ($_array, $_key = null)
		{
			$result = json_decode($_array, true);
			if(is_array($result) && isset($result[$_key]))
			{
				$result = $result[$_key];
			}
			else
			{
				$result = $_array;
			}

			return $result;
		});
	}


	/**
	 * twig custom filter for dump with php
	 */
	public function twig_function_dump()
	{
		return new \Twig_SimpleFunction('dump', function()
		{

		});
	}


	/**
	 * [twig_function_result description]
	 * @return [type] [description]
	 */
	public function twig_function_result()
	{
		return new \Twig_SimpleFunction('result', function()
		{
			var_dump($this->model());
		});
	}


	/**
	 * [twig_function_language description]
	 * @return [type] [description]
	 */
	public function twig_function_langList()
	{
		return new \Twig_SimpleFunction('langList', function()
		{
			$result      = null;
			$html        = array_column(func_get_args(), 'html');
			$all         = array_column(func_get_args(), 'all');
			$onlyLink    = array_column(func_get_args(), 'onlyLink');
			$class       = array_column(func_get_args(), 'class');
			$langList    = $this->data->site['langlist'];
			$urlRoot     = $this->data->url->root;
			$urlContent  = $this->data->url->content;
			$urlPath     = $this->data->url->path;

			if(!$all)
			{
				$currentlang = \lib\define::get_language();
				unset($langList[$currentlang]);
			}

			if($html)
			{
				$lang_string = '';
				foreach ($langList as $key => $value)
				{
					$langPrefix  = \lib\define::get_current_language_string($key);
					$href        = $urlRoot. $langPrefix;
					$activeClass = '';

					if(!$langPrefix)
					{
						$activeClass = " class='active'";
					}
					if($urlContent)
					{
						$href .= '/'.$urlContent;
					}
					if($urlPath)
					{
						$href .= '/'.$urlPath;
					}
					$lang_string .= "<a href='". $href . "'$activeClass hreflang='$key' data-direct>";
					$lang_string .= $value;
					$lang_string .= "</a>";
				}

				if(!$onlyLink)
				{
					if(is_array($class) && isset($class[0]))
					{
						$class = $class[0];
					}
					if(!is_string($class) || !$class)
					{
						$class = '';
					}
					else
					{
						$class = ' '. $class;
					}

					$lang_string = "<nav class='langlist$class' data-xhr='langlist'>". $lang_string .'</nav>';
				}

				echo $lang_string;
			}
			// return $langList;
		});
	}


	/**
	 * [twig_function_breadcrumb description]
	 * @return [type] [description]
	 */
	public function twig_function_breadcrumb()
	{
		return new \Twig_SimpleFunction('breadcrumb', function ($_path = null, $_direct = null, $_homepage = true)
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
			$result     = '';
			if($_homepage || count($myurl))
			{
				if(\lib\router::get_repository_name() === 'content')
				{
					$result = '<a href="/" tabindex="-1" '. $direct.'><i class="fa fa-home"></i> '.T_('Homepage').'</a>';
				}
				else
				{
					$result = '<a href="/" tabindex="-1" '. $direct.'><i class="fa fa-home"></i> '.T_('Home').'</a>';
				}

			}

			foreach ($myurl as $key => $part)
			{
				// if($part != '$')
				{
					$currentUrl .= $_path[$key].'/';
					$location   = T_(ucfirst($part));
					if(end($myurl) === $part)
					{
						$result .= "<a>$location</a>";
					}
					else
					{
						$baseURL = $this->data->url->base.'/';
						$anchorUrl = trim($baseURL.$currentUrl, '/');
						$result .= "<a href='$anchorUrl' tabindex='-1'>$location</a>";
					}
				}
			}

			echo $result;
		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_posts()
	{
		return new \Twig_SimpleFunction('posts', function()
		{
			$posts  = $this->model()->posts(...func_get_args());
			$html   = array_column(func_get_args(), 'html');
			$desc   = array_column(func_get_args(), 'desc');
			if($html && count($html) === 1)
			{
				$html = $html[0];
			}

			if($desc && count($desc) === 1)
			{
				$desc = $desc[0];
			}

			if($html)
			{
				$counter = 0;
				$result  = '';
				$content = '';
				foreach ($posts as $item)
				{
					$result .= "\n    ";
					$result .= "<article>";

					if($desc == 'all' || (is_numeric($desc) && $desc > $counter))
					{
						$result .= "<a href='/".$item['url']."'>".$item['title']."</a>";
						if(isset($item['content']))
						{
							$content = \lib\utility\excerpt::get($item['content']);
							if($content)
							{
								$result .= '<p>'. $content .'</p>';
							}
						}
					}
					else
					{
						$result .= "<a href='/".$item['url']."'>".$item['title']."</a>";
					}
					$result .= "</article>";
					// increase counter
					$counter++;
				}

				echo $result;
			}
			else
			{
				return $posts;
			}

		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_tags()
	{
		return new \Twig_SimpleFunction('tags', function()
		{
			$tags = [];
			$args = func_get_args();
			if(isset($args[0]))
			{
				$args = $args[0];
			}

			// get post id
			if(!isset($args['post_id']))
			{
				if(isset($this->data->post['id']))
				{
					$args['post_id'] = $this->data->post['id'];
				}
			}
			// get tags
			if(isset($args['post_id']))
			{
				$tags = \lib\db\tags::usage($args['post_id']);
			}

			// check html mod
			if(isset($args['html']))
			{
				$html = '';
				foreach ($tags as $key => $value) {
					$html .= "<a href=\"$value\">$value</a>";
				}
				echo $html;
			}
			else
			{
				return $tags;
			}
		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_comments()
	{
		return new \Twig_SimpleFunction('comments', function()
		{
			$comments = [];
			$args = func_get_args();
			if(isset($args[0]))
			{
				$args = $args[0];
			}

			// get post id
			if(!isset($args['post_id']))
			{
				if(isset($this->data->post['id']))
				{
					$args['post_id'] = $this->data->post['id'];
				}
			}
			// count of show comments
			$limit = 6;
			if(isset($args['limit']))
			{
				$limit = $args['limit'];
			}

			// get comments
			if(isset($args['post_id']))
			{
				$comments = \lib\db\comments::get_post_comment($args['post_id'], $limit, $this->login('id'));
			}
			return $comments;
		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_similar_post()
	{
		return new \Twig_SimpleFunction('similar_post', function()
		{
			$similar_post = [];
			$args = func_get_args();
			if(isset($args[0]))
			{
				$args = $args[0];
			}
			// get post id
			if(!isset($args['post_id']))
			{
				if(isset($this->data->post['id']))
				{
					$args['post_id'] = $this->data->post['id'];
				}
			}

			// count of show similar
			$limit = 5;
			if(isset($args['limit']))
			{
				$limit = $args['limit'];
			}

			if(isset($args['post_id']))
			{
				$similar_post = \lib\db\tags::get_post_similar($args['post_id'], $limit);
			}

			if(isset($args['html']))
			{
				$html = '';
				foreach ($similar_post as $key => $value) {
					$html .= "<a href=\"$value[url]\">$value[title]</a>";
				}
				echo $html;
			}
			else
			{
				return $similar_post;
			}

		});
	}


	/**
	 * [twig shortURL decode|encode]
	 * @return [type] [description]
	 */
	public function twig_filter_shortURL()
	{
		return new \Twig_SimpleFilter('shortURL', function ($_url, $_type = 'decode', $_alphabet = null)
		{
			$result = null;
			if($_type === 'decode')
			{
				$result = \lib\utility\shortURL::decode($_url, $_alphabet);
			}
			elseif($_type === 'encode')
			{
				$result = \lib\utility\shortURL::encode($_url, $_alphabet);
			}
			return $result;
		});
	}
}
?>