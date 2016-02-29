<?php
namespace lib\mvc;
use \lib\router;
class controller extends \lib\controller
{
	use \lib\mvc\controllers\access;
	use \lib\mvc\controllers\login;
	use \lib\mvc\controllers\sessions;
	use \lib\mvc\controllers\template;
	use \lib\mvc\controllers\tools;
	use \lib\mvc\controllers\url;


	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		parent::__construct();
		$myrep = router::get_repository_name();

		// running template base module for homepage
		if(\lib\router::get_storage('CMS') && $myrep === 'content' && method_exists($this, 's_template_finder') && get_class($this) === 'content\home\controller')
		{
			$this->s_template_finder();
		}
	}
}
?>