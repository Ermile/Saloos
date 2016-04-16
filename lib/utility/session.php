<?php
namespace lib\utility;

/** session: handle session of project **/
class session
{
	/**
	 * this library work with session
	 * v1.0
	 */

	public static function save()
	{
		// define session array
		$session =
		[
			'user'  => true,
			'cat'   => 'sessions',
			'key'   => session_name().'__USER_',
			'value' => session_id(),
		];
		// save in options table
		return \lib\utility\option::set($session);
	}
}
?>