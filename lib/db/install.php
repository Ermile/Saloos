<?php
namespace lib\db;

trait install
{
	public static $path_project = database. 'install/';
	public static $path_addons  = addons. 'includes/cls/database/install/';


	/**
	 * read current project and addons folder to find database folder
	 * then start installing files into databases
	 *** database name must not use - in name!
	 * @param  boolean $_onlyUpgrade run upgrade process if true
	 * @param  boolean $_addonsFirst first run addons query
	 * @return [type]                array contain a result of installation
	 */
	public static function install($_onlyUpgrade = false, $_addonsFirst = true)
	{
		// increase php code execution time
		ini_set('max_execution_time', 300); //300 seconds = 5 minutes

		$result = [];
		$myList = [];
		// find addresses
		$path_project = self::$path_project;
		$path_addons  = self::$path_addons;
		// if want to only upgrade read upgrade folder
		if($_onlyUpgrade)
		{
			$path_project = substr(self::$path_project, 0, -8). 'upgrade/';
			$path_addons  = substr(self::$path_addons,  0, -8). 'upgrade/';
		}
		// read folders
		$project = glob($path_project.'*', GLOB_ONLYDIR);
		$addons  = glob($path_addons.'*',  GLOB_ONLYDIR);
		// merge two location list in one array
		$dbList  = array_merge($project, $addons);
		// flip array to change location to key
		$dbList  = array_flip($dbList);
		// create a array to install each table only one times, remove duplicate
		foreach ($dbList as $key => $myDbLoc)
		{
			$myDbName     = self::find_dbName($key);
			$myList[$key] = $myDbName;
		}


		// reverse because first install addons databases
		if($_addonsFirst)
		{
			$myList = array_reverse($myList);
		}

		var_dump($myList);

		// run query for each folder
		foreach ($myList as $myDbLoc => $myDbName)
		{
			$myDbCon = $myDbName;

			// get the current version of database
			$db_version = self::db_version($myDbCon);

			$result[$myDbName]['version'] = $db_version;

			if(substr($myDbName, -1) === '+')
			{
				$myDbCon = substr($myDbName, 0, -1);
			}
			// if only want to upgrade run connection in specefic condition

			if($_onlyUpgrade)
			{
				$result[$myDbName]['connect'] = self::connect($myDbCon, false);
				$result[$myDbName]['exec']    =
					self::execFolder($myDbLoc.'/', 'v.', false, $myDbCon, $db_version);
			}
			// run normal installation
			else
			{
				$result[$myDbName]['connect'] = self::connect($myDbCon, true);
				$result[$myDbName]['exec']    =
					self::execFolder($myDbLoc.'/', null, false, $myDbCon, $db_version);
			}
		}
		// on normal installation call upgrade process to complete installation
		if(!$_onlyUpgrade)
		{
			$result['upgrade'] = self::install(true, true);
		}

		// decrease php code execution time to default value
		// reset to default
		$max_time = ini_get("max_execution_time");
		ini_set('max_execution_time', $max_time); //300 seconds = 5 minutes
		// return final result
		return $result;
	}


	/**
	 * execute files in one folder
	 * @param  [type]  $_path   [description]
	 * @param  [type]  $_group  [description]
	 * @param  boolean $_addons [description]
	 * @return [type]           [description]
	 */
	public static function execFolder($_path = null, $_group = null, $_addons = false, $_db_name = true , $_db_version = 0)
	{
		$result = [];
		// if want to read from addons update location
		$myDbName = null;
		if($_addons)
		{
			$_path    = self::$path_addons. $_path;
			$_path    = $_path.'/';
			$myDbName = self::find_dbName($_path);
			self::connect($myDbName, true);
		}

		if($myDbName === null && $_db_name !== true)
		{
			$myDbName = $_db_name;
		}

		// if want custom group of files, select this group
		if($_group)
		{
			$_path = $_path. $_group. "*.sql";
		}
		else
		{
			$_path = $_path. "*.sql";
		}
		// var_dump(glob($_path));
		// for each item with this situation create
		foreach(glob($_path) as $key => $filename)
		{
			$result[$filename] = self::execFile($filename, false, $myDbName, $_db_version);
		}

		return $result;
	}


	/**
	 * execute sql file directly to add some database
	 * @param  [type]  $_path  [description]
	 * @param  boolean $_tools [description]
	 * @return [type]          [description]
	 */
	public static function execFile($_path, $_addons = false, $_db_name = true, $_db_version = 0)
	{
		// if want to read from addons update location
		if($_addons)
		{
			$_path = self::$path_addons. $_path. '.sql';
		}

		$file_version = 0;
		if(preg_match("/v\.([\d\.]+)\_(.*)$/", $_path, $split))
		{
			if(isset($split[1]))
			{
				$file_version = $split[1];
			}
		}
		// var_dump($_path);
		// var_dump($file_version);

		if(version_compare($_db_version, $file_version, "<"))
		{
			// if this path exist, read file and run
			if(file_exists($_path))
			{
				// read file and save in variable
				$qry_list = file_get_contents($_path);
				// seperate with semicolon
				if(substr($qry_list, 0,9) == 'DELIMITER')
				{
					$qry_list = [$qry_list];
				}
				else
				{
					$qry_list = explode(';', $qry_list);
				}

				$has_error = null;
				foreach ($qry_list as $key => $qry)
				{
					$qry = trim($qry);
					if($qry && self::query($qry, $_db_name))
					{
						$has_error = true;
					}
				}

				// set the new version in database
				self::set_db_version($file_version, $_db_name);

				// if command execute successfully
				if(!$has_error)
				{
					return T_('Successfully');
				}
				else
				{
					return T_('Error!');
				}
			}
		}
		else
		{
			return T_('Needless of update');
		}
		// file not exist or error on creating table, return false
		return false;
	}



	/**
	 * find db name by giving folder location
	 * @param  [type] $_loc [description]
	 * @return [type]       [description]
	 */
	public static function find_dbName($_loc)
	{
		$myDbName = preg_replace("[\\\\]", "/", $_loc);
		$myDbName = substr( $myDbName, (strrpos($myDbName, "/" )+ 1));
		// change db_name and core_name to defined value
		$myDbName = str_replace('(db_name)', db_name, $myDbName);
		$myDbName = str_replace('(core_name)', core_name, $myDbName);
		// return result
		return $myDbName;
	}


	/**
	 * check db exist or not
	 * @return [type] no of tables in database
	 */
	public static function count_table($_create = false)
	{
		$result  = false;
		$connect = self::connect(true, $_create);
		if($connect)
		{
			$result = mysqli_query(self::$link, 'SHOW TABLES');
			$result = $result->num_rows;
		}
		// return result
		return $result;
	}
}
?>