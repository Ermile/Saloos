<?php
namespace lib\db;

trait log
{
	/**
	 * save log of sql request into file for debug
	 * @param  [type] $_text [description]
	 * @return [type]        [description]
	 */
	public static function log($_text)
	{
		$classes  = (array_column(debug_backtrace(), 'file'));
		if(DEBUG)
		{
			$fileAddr = root.'public_html/files/';
			\lib\utility\file::makeDir($fileAddr, null, true);
			// set file address
			$fileAddr .= 'db.log';
			$_text = str_repeat("-", 70). urldecode($_SERVER['REQUEST_URI']). "\n". $_text. "\r\n";
			file_put_contents($fileAddr, $_text, FILE_APPEND);
		}
	}
}
?>