<?php
namespace lib\utility\upload;

trait check
{

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
}
?>