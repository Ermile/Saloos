<?php
namespace lib\db;

trait log
{
	/**
	 * save log of sql request into file for debug
	 * @param  [type] $_text [description]
	 * @return [type]        [description]
	 */
	public static function log($_text, $_time = null, $_name = 'db.sql')
	{
		$classes  = (array_column(debug_backtrace(), 'file'));
		if(DEBUG)
		{
			$fileAddr = database.'log/';
			\lib\utility\file::makeDir($fileAddr, null, true);
			// set file address
			$fileAddr .= $_name;
			$my_text  = "\n#". str_repeat("-", 70). ' '. urldecode($_SERVER['REQUEST_URI']);
			$my_text .= "\n#". $_time. "s";
			$my_text .= "\n#". round($_time*1000) . "ms";
			$my_text .= "\n";
			$my_text .= $_text. "\r\n";
			file_put_contents($fileAddr, $my_text, FILE_APPEND);
		}
	}
}
?>