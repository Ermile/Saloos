<?php
namespace lib\utility;

/** PHP Upload Management **/
class upload
{
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
	public static $upload_from_path = null;
	public static $extentionsDisallow = ['php', 'php5', 'htaccess', 'exe', 'bat', 'bin'];
	public static $extentions         =
	[
		'png', 'jpeg', 	'jpg', 	'zip', 	'rar', 'mp3',
		'mp4', 'pdf', 	'doc', 	'docx', 'apk', 'chm',
		'jar', 'txt', 	'css', 	'js', 	'htm', 'html',
		'swf', 'xml', 	'xlsx', 'pptx'
	];


	/**
	 * get $_FILES
	 *
	 * @param      <type>  $_name  The name
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function _FILES($_name)
	{
		if(self::$upload_from_path)
		{
			$path = self::$upload_from_path;
			if(\lib\utility\file::exists($path))
			{
				$tmp_FILES =
				[
					'name'     => \lib\utility\file::getName($path),
					'type'     => \lib\utility\file::content_type($path),
					'tmp_name' => $path,
					'error'    => 0,
					'size'     => \lib\utility\file::getSize($path),
				];
				return $tmp_FILES;
			}
		}
		else
		{
			if(isset($_FILES[$_name]))
			{
				return $_FILES[$_name];
			}
		}
		return [];
	}


	/**
	 * Check for invalid upload process
	 * @param  string self::$fieldName [description]
	 * @return [type]        [description]
	 */
	public static function invalid($_name = 'upfile', $_maxSize = null)
	{
		self::$fieldName = $_name;
		try
		{
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if ( !isset(self::_FILES(self::$fieldName)['error']) || is_array(self::_FILES(self::$fieldName)['error']))
			{
				throw new \RuntimeException(T_('Invalid parameters'));
			}

			// Check self::_FILES(self::$fieldName)['error'] value.
			switch (self::_FILES(self::$fieldName)['error'])
			{
				case UPLOAD_ERR_OK:
					break;

				case UPLOAD_ERR_NO_FILE:
					throw new \RuntimeException(T_('No file sent'));

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new \RuntimeException(T_('Exceeded filesize limit'));

				default:
					throw new \RuntimeException(T_('Unknown errors'));
			}


			$fileInfo           = pathinfo(self::_FILES(self::$fieldName)['name']);
			self::$fileName     = $fileInfo['filename'];

			self::$fileExt      = strtolower($fileInfo['extension']);
			$extCheck           = self::extCheck(self::$fileExt);
			self::$fileType     = $extCheck['type'];
			self::$fileMime     = $extCheck['mime'];
			self::$fileDisallow = $extCheck['disallow'];

			if(!$_maxSize)
			{
				$_maxSize = self::max_file_upload_in_bytes(true);
			}

			// Check filesize here.
			self::$fileSize = self::_FILES(self::$fieldName)['size'];
			if ( self::$fileSize > $_maxSize)
			{
				throw new \RuntimeException(T_('Exceeded filesize limit'));
			}

			//check file extention with allowed extention list
			// set file data like name, ext, mime
			// file with long name does not allowed in our system
			if(strlen(self::$fileName) > 200 || strpos(self::$fileName, 'htaccess') !== false)
			// if(strlen(self::$fileName) > 200)
			{
				throw new \RuntimeException(T_('Exceeded file name limit'));
			}
			// file with long extension does not allowed in our system
			if(strlen(self::$fileExt) > 10 || self::$fileDisallow )
			{
				throw new \RuntimeException(T_('Exceeded file extension limit'));
			}

			self::$fileFullName = \lib\utility\filter::slug(self::$fileName). '.'. self::$fileExt;
			self::$fileMd5      = md5_file(self::_FILES(self::$fieldName)['tmp_name']);

			if(is_array(self::$extentions) && !in_array(self::$fileExt, self::$extentions))
			{
				throw new \RuntimeException(T_("We don't support this type of file"));
			}

			// DO NOT TRUST self::_FILES(self::$fieldName)['mime'] VALUE !!
			// Check MIME Type by yourself.
			// Alternative check
			if(function_exists('finfo'))
			{
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				// var_dump($finfo);
				// if (false === $ext = array_search( $finfo->file(self::_FILES(self::$fieldName)['tmp_name']), self::$extentions ), true ))
				// {
				// 	throw new \RuntimeException(T_('Invalid file format.'));
				// }
				self::$fileMime = mime_content_type($fileInfo['basename']);
			}

			// it is not invalid, that's mean it's a valid upload
			return false;
		}
		catch (\RuntimeException $e)
		{
			return $e->getMessage();
		}
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
	 * Get the MIME and type of file extension.
	 * @param string $_ext File extension
	 * @access public
	 * @return string MIME type of file.
	 * @static
	 */
	public static function extCheck($_ext = '')
	{
		// if pass filepath
        if(file_exists($_ext))
        {
        	$fileInfo = pathinfo($_ext);
        	$_ext     = strtolower($fileInfo['extension']);
        }

		$mimes =
		[
			// archive
			'gtar'     => [ 'type' => 'archive',    'mime' => 'application/x-gtar'],
			'tar'      => [ 'type' => 'archive',    'mime' => 'application/x-tar'],
			'tgz'      => [ 'type' => 'archive',    'mime' => 'application/x-tar'],
			'zip'      => [ 'type' => 'archive',    'mime' => 'application/zip'],
			'7z'       => [ 'type' => 'archive',    'mime' => 'application/x-7z-compressed'],
			'rar'      => [ 'type' => 'archive',    'mime' => 'application/x-rar-compressed'],
			// audio
			'mp3'      => [ 'type' => 'audio',      'mime' => 'audio/mpeg'],
			'wav'      => [ 'type' => 'audio',      'mime' => 'audio/x-wav'],
			// image
			'bmp'      => [ 'type' => 'image',      'mime' => 'image/bmp'],
			'gif'      => [ 'type' => 'image',      'mime' => 'image/gif'],
			'jpeg'     => [ 'type' => 'image',      'mime' => 'image/jpeg'],
			'jpg'      => [ 'type' => 'image',      'mime' => 'image/jpeg'],
			'png'      => [ 'type' => 'image',      'mime' => 'image/png'],
			'tif'      => [ 'type' => 'image',      'mime' => 'image/tiff'],
			'svg'      => [ 'type' => 'image',      'mime' => 'image/svg+xml'],
			// pdf
			'pdf'      => [ 'type' => 'pdf',        'mime' => 'application/pdf'],
			// video
			'mpeg'     => [ 'type' => 'video',      'mime' => 'video/mpeg'],
			'mpg'      => [ 'type' => 'video',      'mime' => 'video/mpeg'],
			'mp4'      => [ 'type' => 'video',      'mime' => 'video/mp4'],
			'mov'      => [ 'type' => 'video',      'mime' => 'video/quicktime'],
			'avi'      => [ 'type' => 'video',      'mime' => 'video/x-msvideo'],
			'dvi'      => [ 'type' => 'video',      'mime' => 'application/x-dvi'],
			// word
			'doc'      => [ 'type' => 'word',       'mime' => 'application/msword'],
			'docx'     => [ 'type' => 'word',       'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
			// excel
			'xls'      => [ 'type' => 'excel',      'mime' => 'application/vnd.ms-excel'],
			'xlsx'     => [ 'type' => 'excel',      'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
			// powerpoint
			'ppt'      => [ 'type' => 'powerpoint', 'mime' => 'application/vnd.ms-powerpoint'],
			'pptx'     => [ 'type' => 'powerpoint', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
			'ppsx'     => [ 'type' => 'powerpoint', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow'],
			// code
			'js'       => [ 'type' => 'code',       'mime' => 'application/x-javascript'],
			'dll'      => [ 'type' => 'code',       'mime' => 'application/octet-stream'],
			// diallow file list
			'php'      => [ 'type' => 'code',       'mime' => 'application/x-httpd-php'],
			'php5'     => [ 'type' => 'code',       'mime' => 'application/x-httpd-php'],
			'exe'      => [ 'type' => 'code',       'mime' => 'application/octet-stream'],
			'bat'      => [ 'type' => 'code',       'mime' => 'application/x-bat'],
			'bin'      => [ 'type' => 'code',       'mime' => 'application/macbinary'],
			'htaccess' => [ 'type' => 'code',       'mime' => 'application/x-jar'],
			// text
			'rtx'      => [ 'type' => 'text',       'mime' => 'text/richtext'],
			'rtf'      => [ 'type' => 'text',       'mime' => 'text/rtf'],
			'log'      => [ 'type' => 'text',       'mime' => 'text/plain'],
			'text'     => [ 'type' => 'text',       'mime' => 'text/plain'],
			'txt'      => [ 'type' => 'text',       'mime' => 'text/plain'],
			'xml'      => [ 'type' => 'text',       'mime' => 'text/xml'],
			'xsl'      => [ 'type' => 'text',       'mime' => 'text/xml'],
			'css'      => [ 'type' => 'text',       'mime' => 'text/css'],
			'htm'      => [ 'type' => 'text',       'mime' => 'text/html'],
			'html'     => [ 'type' => 'text',       'mime' => 'text/html'],
			'shtml'    => [ 'type' => 'text',       'mime' => 'text/html'],
			'xht'      => [ 'type' => 'text',       'mime' => 'application/xhtml+xml'],
			'xhtml'    => [ 'type' => 'text',       'mime' => 'application/xhtml+xml'],
			// file
			'psd'      => [ 'type' => 'file',       'mime' => 'application/octet-stream'],
			'eps'      => [ 'type' => 'file',       'mime' => 'application/postscript'],
			'apk'      => [ 'type' => 'file',       'mime' => 'application/vnd.android.package-archive'],
			'chm'      => [ 'type' => 'file',       'mime' => 'application/vnd.ms-htmlhelp'],
			'jar'      => [ 'type' => 'file',       'mime' => 'application/x-jar'],
		];

		// if exist in list return it
		if(array_key_exists(strtolower($_ext), $mimes))
		{
			$myResult = $mimes[strtolower($_ext)];
		}
		else
		{
			$myResult = ['type' => 'file', 'mime' => 'application/octet-stream'];
		}

		$myResult['disallow'] = null;

		if(in_array($_ext, self::$extentionsDisallow))
		{
			$myResult['disallow'] = true;
		}
		// else return the
		return $myResult;
	}


	/**
	 * upload and insert post in database
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function complete_upload($_options = [])
	{
		$default_options =
		[

			'file_path'     => null,
			'user_id'       => false,
			'folder_size'   => 1000,
			'upload_name'   => 'upfile',
			'folder_prefix' => 'files/',
			'crop'          => true,
			'resize'        => false,
			'copy_file'     => false,
			'ftp'			=> false,
			'ftp_user'		=> null,
			'ftp_password'	=> null,
		];

		$_options = array_merge($default_options, $_options);

		// check upload name
		if(!$_options['upload_name'])
		{
			return \lib\debug::error(T_("upload name not found"), false, 'upload');
		}

		// check foler prefix
		if(!$_options['folder_prefix'])
		{
			return \lib\debug::error(T_("folder prefix not found"), false, 'upload');
		}

		// check user id
		if(!$_options['user_id'] || !is_numeric($_options['user_id']))
		{
			return \lib\debug::error(T_("user id not set"), false, 'sql');
		}

		// default upload file from upload in server
		// you can move from read path in new path
		// by set 'file_path' = [real file path]
		$upload_from_path = false;

		// check file path
		if($_options['file_path'] !== null)
		{
			self::$upload_from_path = $_options['file_path'];
			$upload_from_path = true;
		}

		// 1. check upload process and validate it
		$invalid = self::invalid($_options['upload_name']);
		if($invalid)
		{
			var_dump(self::_FILES('upfile'));
			exit();
			return \lib\debug::error($invalid, false, 'upload');
		}

		// 2. Generate file_id, folder_id and url
		$query         = "SELECT COUNT(posts.id) AS 'count' FROM posts WHERE post_type = 'attachment' ";
		$qry_count     = \lib\db::get($query,'count', true);

		$folder_prefix = $_options['folder_prefix'];
		$folder_id     = ceil(($qry_count+1) / $_options['folder_size']);

		$folder_loc    = $folder_prefix . $folder_id;
		$file_id       = $qry_count % $_options['folder_size'] + 1;
		$url_full      = "$folder_loc/$file_id-" . self::$fileFullName;

		// 3. Check for record exist in db or not
		$file_md5  = self::$fileMd5;
		$qry_count = "SELECT posts.id AS 'id' FROM posts WHERE post_slug = '$file_md5' LIMIT 1";
		$qry_count = \lib\db::get($qry_count, 'id', true);
		if($qry_count || !empty($qry_count))
		{
			$id = (int) $qry_count;
			// $link = '<a target="_blank" href=/cp/attachments/edit='. $id. '>'.
			// T_('Duplicate - File exist').'</a>';
			// \lib\debug::msg("link", $link);
			return \lib\debug::error(T_('Duplicate - File exist'), false, 'upload');
		}

		// 4. transfer file to project folder with new name
		if($upload_from_path)
		{
			if(!\lib\utility\file::copy($_options['file_path'], $folder_loc))
			{
				return \lib\debug::fatal(T_('Fail on tranfering file, upload from path'));
			}

			if($_options['copy_file'] === false)
			{
				\lib\utility\file::delete($_options['file_path']);
			}
		}
		else
		{
			if(!self::transfer($url_full, $folder_loc))
			{
				return \lib\debug::msg('mag', false); //->set_message(T_('Fail on tranfering file'));
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
				$extlen     = strlen(self::$fileExt);
				$url_file   = substr($url_full, 0, -$extlen-1);
				$url_thumb  = $url_file.'-thumb.'.self::$fileExt;
				$url_normal = $url_file.'-normal.'.self::$fileExt;

				\lib\utility\image::load($url_full);
				\lib\utility\image::thumb(600, 400);
				\lib\utility\image::save($url_normal);

				\lib\utility\image::thumb(150, 150);
				\lib\utility\image::save($url_thumb);
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

		$url_slug = self::$fileMd5;
		$url_body = $folder_id. "_". $file_id;
		$page_url = self::sp_generateUrl($url_slug, $url_body, $file_meta['type']. "/");

		if( strpos($file_meta['mime'], 'image') !== false)
			list($file_meta['width'], $file_meta['height'])= getimagesize($url_full);
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
			'post_status'      => 'draft',
			'post_publishdate' => date('Y-m-d H:i:s')
		];
		$post_new_id = \lib\db\posts::insert($insert_attachment);
		return \lib\debug::msg('mag', true); //->set_result($post_new_id)->set_file_id(\lib\db::insert_id());
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
			$newURL .= '/';
		}
		$newURL .= $_slug. '/';
		$newURL = trim($newURL, '/');
		// $newURL .= '/';

		return $newURL;
	}
}
?>