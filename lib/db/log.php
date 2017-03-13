<?php
namespace lib\db;

trait log
{
	/**
	 * save log of sql request into file for debug
	 * @param  [type] $_text [description]
	 * @return [type]        [description]
	 */
	public static function log($_text, $_time = null, $_name = 'log.sql')
	{
		$classes  = (array_column(debug_backtrace(), 'file'));

		// start saving
		$fileAddr = database.'log/';
		$time_ms  = round($_time*1000);
		$date_now = new \DateTime("now", new \DateTimeZone('Asia/Tehran') );
		\lib\utility\file::makeDir($fileAddr, null, true);
		// set file address
		$fileAddr .= $_name;
		$my_text  = "\n#". str_repeat("-", 70). ' '. urldecode($_SERVER['REQUEST_URI']);
		$my_text .= "\n#". $_time. "s";
		$my_text .= "\t---". $date_now->format("Y-m-d H:i:s");
		$my_text .= "\n". $time_ms . "ms";
		if($time_ms > 50)
		{
			$my_text .= "\n"."--- CRITICAL!";
		}
		elseif($time_ms > 10)
		{
			$my_text .= "\n"."--- WARN!";
		}
		elseif($time_ms > 3)
		{
			$my_text .= "\n"."--- CHECK!";
		}
		$my_text .= "\n";
		$my_text .= $_text. "\r\n";

		file_put_contents($fileAddr, $my_text, FILE_APPEND);
	}
}
?>