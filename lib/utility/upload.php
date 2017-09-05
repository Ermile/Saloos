<?php
namespace lib\utility;

/** PHP Upload Management **/
class upload
{
	use upload\sql;
	use upload\ext;
	use upload\check;

	// default max size is 10MB
	const MAX_SIZE = 10000000;
	public static $fieldName;
	public static $fileName;
	public static $fileExt;
	public static $fileFullName;
	public static $fileMime;
	public static $fileType;
	public static $fileDisallow;
	public static $fileMd5;
	public static $fileSize;
	public static $upload_from_path   = null;
	public static $real_file_path     = null;
	public static $FILES              = [];
	public static $extentionsDisallow = ['php', 'php5', 'htaccess', 'exe', 'bat', 'bin'];
	public static $extentions         =
	[
		'png', 'jpeg', 	'jpg', 	'zip', 	'rar', 'mp3',
		'mp4', 'pdf', 	'doc', 	'docx', 'apk', 'chm',
		'jar', 'txt', 	'css', 	'js', 	'htm', 'html',
		'swf', 'xml', 	'xlsx', 'pptx'
	];


	/**
	 * get $_FILES if exist
	 * and get the tmp_FILES if upload form url
	 *
	 * @param      <type>  $_name  The name
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function _FILES($_name = 'upfile')
	{
		if(self::$upload_from_path)
		{
			$path = self::$upload_from_path;
			if(\lib\utility\file::exists($path))
			{
				$tmp_FILES =
				[
					'name'     => \lib\utility\file::getName(self::$real_file_path),
					'type'     => \lib\utility\file::content_type($path),
					'tmp_name' => $path,
					'error'    => 0,
					'size'     => \lib\utility\file::getSize($path),
				];
				self::$FILES[$_name] = $tmp_FILES;
			}
		}
		else
		{
			self::$FILES = $_FILES;
		}

		if(isset(self::$FILES[$_name]))
		{
			return self::$FILES[$_name];
		}
		return self::$FILES;
	}



	/**
	 * Change to display size
	 * @param  [type] $filesize file size in byte
	 * @return [type]           return human readable size
	 */
	public static function readableSize($_filesize, $_type = 'file', $_emptyTxt = null)
	{
		if(is_numeric($_filesize) && $_type == 'file')
		{
			$decr   = 1024;
			$step   = 0;
			$prefix = array(T_('Byte'), T_('KB'), T_('MB'), T_('GB'), T_('TB'), T_('PB'));

			while(($_filesize / $decr) > 0.9)
			{
				$_filesize = $_filesize / $decr;
				$step++;
			}
			return round($_filesize, 2).' '.$prefix[$step];
		}
		elseif($_type == 'folder')
		{
			if($_filesize == 0)
			{
				if(!$_emptyTxt)
				{
					$_emptyTxt = $_emptyTxt = 'empty';
				}
				return T_($_emptyTxt);
			}
			else
			{
				return $_filesize .' '. T_('item');
			}
		}
		else
		{
			return T_('NaN');
		}
	}


	/**
	 * return system size to byte
	 * @param  [type] $_size get value of size
	 * @return [type]      return size in byte
	 */
	public static function sp_fileSizeByte($_size)
	{
		$_size = trim($_size);
		$last = strtolower($_size[strlen($_size)-1]);
		switch($last)
		{
			case 'g':
				$_size *= 1024;
			case 'm':
				$_size *= 1024;
			case 'k':
				$_size *= 1024;
		}
		return $_size;
	}

	/**
	 * Get max file upload size
	 * @return [type] return in byte
	 */
	public static function max_file_upload_in_bytes($_raw = false)
	{
		//select maximum upload size
		$max_upload   = self::sp_fileSizeByte(ini_get('upload_max_filesize'));
		//select post limit
		$max_post     = self::sp_fileSizeByte(ini_get('post_max_size'));
		//select memory limit
		$memory_limit = self::sp_fileSizeByte(ini_get('memory_limit'));
		// find the smallest of them, this defines the real limit
		$min = min($max_upload, $max_post, $memory_limit);
		// if user want can get raw value for use in another func
		if($_raw)
		{
			return $min;
		}

		// return the smallest of them in human readable size
		return self::readableSize($min);
	}


	/**
	 * Transfer uploaded file to specefic folder of project
	 * @param  [type] $_url url of file in project like files/1/2-test.png
	 * @return [type]       [description]
	 */
	public static function transfer($_url, $_folder = null)
	{
		if($_folder && !is_dir($_folder))
		{
			\lib\utility\file::makeDir($_folder, 0775, true);
		}

		if($_folder && !is_dir($_folder))
		{
			header("HTTP/1.1 412 Precondition Failed");
			exit();
		}

		if(move_uploaded_file(self::_FILES(self::$fieldName)['tmp_name'], $_url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * save file as tmp
	 *
	 * @param      <type>  $_file_path  The file path
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function temp_donwload($_file_path, $_options = [], $_upload_process = false)
	{
		if($_upload_process === false)
		{
			$_options['file_path']   = $_file_path;
			$_options['save_as_tmp'] = true;
			return self::upload($_options);
		}
		elseif($_upload_process === true)
		{
			$move_to = $_options['move_to']. $_options['tmp_path'];

			$master_name = 'tmp_master_name';
			if(preg_match("/(SALOOS\_[^\/]+)/", self::$upload_from_path, $master_name))
			{
				if(isset($master_name[1]))
				{
					$master_name = $master_name[1];
				}
			}

			$file_name   = \lib\utility\file::getName(self::$real_file_path);
			$master_name = $master_name. '_'. $file_name;
			$new_name    = $move_to. $master_name;

			$link = \lib\router::$base.DIRECTORY_SEPARATOR.$_options['tmp_path']. $master_name;

			if($move_to && !is_dir($move_to))
			{
				\lib\utility\file::makeDir($move_to, 0775, true);
			}

			if(\lib\utility\file::move(self::$upload_from_path, $new_name, true))
			{
				return \lib\debug::db_return(true)
								->set_result($file_name)
								->set_file_name($master_name)
								->set_link($link)
								->set_new_name($new_name);
			}
			else
			{
				return \lib\debug::db_return(false)->set_message(T_('Fail on tranfering file, move in temp'));
			}
		}
	}


	/**
	 * move temp file
	 *
	 * @param      <type>  $_file_path  The file path
	 * @param      array   $_options    The options
	 */
	public static function temp_move($_file_path, $_options = [])
	{
		$_options['file_path'] = $_file_path;
		return self::upload($_options);
	}


	/**
	 * upload and insert post record in database
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function upload($_options = [])
	{
		$default_options =
		[
			// the file path to download and move
			// @example : http://domain.com/file.jpg
			// @example : /var/www/html/file.jpg
			// leave null to get file from $_FILES
			'file_path'     => null,
			// the user inserted the attachment
			'user_id'       => false,
			// folder size of file
			// every folder have 1000 files
			'folder_size'   => 1000,
			// the upload name in <form> in html
			'upload_name'   => 'upfile',
			// file move to this location
			// if use from $_FILE this option is useless
			// the apache move the file to public_html of site
			// when you are set a file_path to download and move
			// we need to move() the file and the move() function need to real location
			'move_to'       => root. 'public_html/',
			// folder prefix
			// this option set after 'move_to' option
			// in upload mode [apache upload the file] this option afte folder public_html of site
			'folder_prefix' => 'files/',
			// crop file [image file]
			// creat the thump image file
			'crop'          => true,
			// resize file
			// no thing ye...
			'resize'        => true,
			// copy file, we not delete the masert file
			// this option is useless because we get the file in tmp folder
			// we must to delete it
			'copy'          => false,
			'move'          => true,
			// the protocol of resive file
			// for example http, https, ftp,	sftp, null: local
			// we get the protocol from firt of 'file_path'
			// this method autmatic was set
			'protocol'      => null,
			// the user name of ftp or sftp protocol
			'username'      => null,
			// the password of ftp or sftp protocol
			'password'      => null,
			// the file meta in post talbe
			// default meta of post is mime, type, size, ext, url, thumb, normal
			// you can set this index to replace the index or inser new index to
			// merge this array and your array
			'meta'          => [],
			// the parent id of post record
			'parent'        => null,
			// the post status
			'post_status'   => 'draft',
			// save file in temp directory
			// whitout save in database
			'save_as_tmp'   => false,
			// the tmp_path
			'tmp_path'      => implode(DIRECTORY_SEPARATOR, ['files','tmp']). DIRECTORY_SEPARATOR,

		];

		$_options = array_merge($default_options, $_options);

		// check upload name
		if(!$_options['upload_name'])
		{
			return \lib\debug::db_return(false)->set_message(T_("upload name not found"));
		}

		// check foler prefix
		if(!$_options['folder_prefix'])
		{
			return \lib\debug::db_return(false)->set_message(T_("folder prefix not found"));
		}

		// check user id
		if((!$_options['user_id'] || !is_numeric($_options['user_id'])) && $_options['save_as_tmp'] === false)
		{
			return \lib\debug::db_return(false)->set_message(T_("user id not set"));
		}

		// get the protocol
		$protocol = null;

		// default upload file from upload in server
		// you can move from read path in new path
		// by set 'file_path' = [real file path]
		$upload_from_path = false;

		// check file path
		if($_options['file_path'] !== null)
		{
			if(preg_match("/^(http|https|ftp|sftp)\:/", $_options['file_path'], $protocol))
			{
				if(isset($protocol[1]))
				{
					$protocol = $protocol[1];
				}
			}

			$file_path = null;

			switch ($protocol)
			{
				case 'http':
				case 'https':
				case 'ftp':
				case 'sftp':
					$file_path = \lib\utility\file::open($_options['file_path']);
					break;

				default:
					$file_path = $_options['file_path'];
					break;
			}

			self::$real_file_path   = $_options['file_path'];
			self::$upload_from_path = $file_path;
			$upload_from_path       = true;
		}

		// 1. check upload process and validate it
		$invalid = self::invalid($_options['upload_name']);
		if($invalid)
		{
			return \lib\debug::db_return(false)->set_message($invalid);
		}

		// save file as tmp in tmp_path
		if($_options['save_as_tmp'] === true)
		{
			return self::temp_donwload(null, $_options, true);
		}

		// 2. Generate file_id, folder_id and url

		$qry_count     = self::attachment_count();

		$folder_prefix = $_options['folder_prefix'];
		$folder_id     = ceil(($qry_count + 1) / $_options['folder_size']);

		$folder_loc    = $folder_prefix . $folder_id;

		if($folder_loc && !is_dir($folder_loc))
		{
			\lib\utility\file::makeDir($folder_loc, 0775, true);
		}

		$file_id       = $qry_count % $_options['folder_size'] + 1;
		$url_full      = "$folder_loc". DIRECTORY_SEPARATOR. "$file_id-" . self::$fileFullName;

		// 3. Check for record exist in db or not
		$duplicate = self::duplicate(self::$fileMd5);

		if($duplicate->is_ok())
		{
			return \lib\debug::db_return(false)
					->set_result($duplicate->get_result())
					->set_message(T_('Duplicate - File exist'));
		}

		// 4. transfer file to project folder with new name
		if($upload_from_path)
		{
			if(!\lib\utility\file::rename(self::$upload_from_path, $_options['move_to']. $url_full, true))
			{
				return \lib\debug::db_return(false)->set_message(T_('Fail on tranfering file, upload from path'));
			}

			if($_options['copy'] === false || $_options['move'] === true)
			{
				\lib\utility\file::delete(self::$upload_from_path);
			}
		}
		else
		{
			if(!self::transfer($url_full, $folder_loc))
			{
				return \lib\debug::db_return(false)->set_message(T_('Fail on tranfering file'));
			}
		}

		$file_ext   = self::$fileExt;
		$url_thumb  = null;
		$url_normal = null;

		switch ($file_ext)
		{
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
				if($_options['crop'] === true)
				{
					$extlen     = strlen(self::$fileExt);
					$url_file   = substr($url_full, 0, -$extlen-1);
					$url_thumb  = $url_file.'-thumb.'.self::$fileExt;
					$url_normal = $url_file.'-normal.'.self::$fileExt;

					\lib\utility\image::load($url_full);
					\lib\utility\image::thumb(600, 400);
					\lib\utility\image::save($url_normal);

					\lib\utility\image::thumb(150, 150);
					\lib\utility\image::save($url_thumb);
				}
				break;
		}

		// 5. get filemeta data
		$file_meta =
		[
			'mime'   => self::$fileMime,
			'type'   => self::$fileType,
			'size'   => self::$fileSize,
			'ext'    => $file_ext,
			'url'    => $url_full,
			'thumb'  => $url_thumb,
			'normal' => $url_normal,
		];
		$file_meta = array_merge($file_meta, $_options['meta']);

		$url_slug = self::$fileMd5;
		$url_body = $folder_id. "_". $file_id;
		$page_url = self::sp_generateUrl($url_slug, $url_body, $file_meta['type']. "/");

		if( strpos($file_meta['mime'], 'image') !== false)
		{
			list($file_meta['width'], $file_meta['height']) = getimagesize($url_full);
		}

		$file_meta = json_encode($file_meta, JSON_UNESCAPED_UNICODE);

		// 6. add uploaded file record to db
		$insert_attachment =
		[
			'post_title'       => self::$fileName,
			'post_slug'        => self::$fileMd5,
			'post_meta'        => $file_meta,
			'post_type'        => 'attachment',
			'post_url'         => $page_url,
			'user_id'          => $_options['user_id'],
			'post_status'      => $_options['post_status'],
			'post_parent'	   => $_options['parent'],
			'post_publishdate' => date('Y-m-d H:i:s')
		];

		$post_new_id = \lib\db\posts::insert($insert_attachment);
		return \lib\debug::db_return(true)->set_result($post_new_id)->set_file_id(\lib\db::insert_id());
	}


	/**
	 * create url automatically from input values
	 * @param  [type] $_slug   slug
	 * @param  [type] $_catUrl body url, cat url or parent url
	 * @param  [type] $_prefix prefix if needed
	 * @return [type]          created url
	 */
	public static function sp_generateUrl($_slug, $_catUrl = null, $_prefix = null)
	{
		$newURL = $_prefix. $_catUrl;
		if($newURL)
		{
			$newURL .= DIRECTORY_SEPARATOR;
		}
		$newURL .= $_slug. DIRECTORY_SEPARATOR;
		$newURL = trim($newURL, DIRECTORY_SEPARATOR);
		return $newURL;
	}
}
?>