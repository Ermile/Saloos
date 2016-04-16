<?php
namespace lib;
/**
 * saloos main configure
 */
class saloos
{
	// @var saloos core current version
	const version = '4.8.3';

	// @var saloos core current commit number
	// now get it automatically from git commands
	// const iversion = 726;

	// @var current version last update date
	// now get it automatically from git last commit date

	/**
	 * constractor
	 */
	public function __construct()
	{
		if(php_sapi_name() == "cli"){
			return;
		}
		self::lib()->router();
		self::lib()->define();

		self::lib()->main();
	}


	public static function route()
	{
		$route = new router\route(false);
		call_user_func_array(array($route, 'check_route'), func_get_args());

		return $route;
	}


	public static function __callstatic($name, $args)
	{
		if(preg_match("/^is_(.*)$/", $name, $aName))
		{
			$class = '\lib\saloos\is';
			return call_user_func_array(array($class, $aName[1]), $args);
		}

		$class = '\\lib\\saloos\\'.$name;
		return new $class($args);
	}


	/**
	 * @return saloos commit count from Git
	 */
	public static function getCommitCount($_saloos = true)
	{
		$commitCount = null;
		try
		{
			if($_saloos)
			{
				chdir(core);
			}
			$commitCount = exec('git rev-list --all --count');
		}
		catch (Exception $e)
		{
			$commitCount = 0;
		}

		return $commitCount;
	}



	/**
	 * @return last version of Saloos
	 */
	public static function getLastVersion()
	{
		// $commitCount = exec('git rev-list --all --count');
		return self::version;
	}


	/**
	 * @return last Update of Saloos
	 */
	public static function getLastUpdate($_saloos = true)
	{
		$commitDate = null;
		try
		{
			if($_saloos)
			{
				chdir(core);
			}
			$commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
			$commitDate = $commitDate->format('Y-m-d');
		}
		catch (Exception $e)
		{
			$commitDate = date();
		}

		return $commitDate;
	}
}
?>