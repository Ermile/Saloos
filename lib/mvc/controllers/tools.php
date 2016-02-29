<?php
namespace lib\mvc\controllers;

trait tools
{
	/**
	 * call model func and return needed option in all condition
	 * @return [type] return string or array contain option value
	 */
	public function option()
	{
		return $this->model()->sp_get_options(...func_get_args());
	}


	/**
	 * convert numver to en
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	function convert_Num2En($string)
	{
		$persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
		$num = range(0, 9);
		return str_replace($persian, $num, $string);
	}
}
?>