<?php
namespace lib;

/** Create simple and clean connection to db **/
class db
{
	/**
	 * this library doing useful db actions
	 * v4.4
	 */

	// save link to database
	public static $link;
	public static $link_open = array();
	public static $path_project = database. 'install/';
	public static $path_addons  = addons. 'includes/cls/database/install/';

	// declare connection variables
	public static $db_name      = null;
	public static $db_user      = null;
	public static $db_pass      = null;
	public static $db_host      = 'localhost';
	public static $db_charset   = 'utf8';
	public static $db_lang      = 'fa_IR';


	/**
	 * class constructor
	 * @param boolean $_autoCreate [description]
	 */
	public function __construct($_db_name = null)
	{
		self::$db_name = $_db_name? $_db_name: self::$db_name ? self::$db_name : db_name;

	}


	/**
	 * connect to related database
	 * if not exist create it
	 * @return [type] [description]
	 */
	public static function connect($_db_name = null, $_autoCreate = false)
	{
		if($_db_name === true)
		{
			// connect to default db
			self::$db_name = db_name;
		}
		elseif($_db_name === '[tools]')
		{
			// connect to core db
			self::$db_name = core_name.'_tools';
		}
		elseif($_db_name)
		{
			// connect to db passed from user
			// else connect to last db saved
			self::$db_name = $_db_name;
		}

		// fill variable if empty variable
		self::$db_name = self::$db_name ? self::$db_name : db_name;
		self::$db_user = self::$db_user ? self::$db_user : db_user;
		self::$db_pass = self::$db_pass ? self::$db_pass : db_pass;
		if(array_key_exists(self::$db_name, self::$link_open))
		{
			self::$link = self::$link_open[self::$db_name];
			return true;
		}
		var_dump(self::$db_name);
		// if mysqli class does not exist or have some problem show related error
		if(!class_exists('mysqli'))
		{
			echo( "<p>"."we can't find database service!"." "
							."please contact administrator!")."</p>";
			exit();
		}

		$link = @mysqli_connect(self::$db_host, self::$db_user, self::$db_pass, self::$db_name);

		// if we have error on connection to this database
		if(!$link)
		{
			switch (@mysqli_connect_errno())
			{
				case 1045:
					echo "<p>"."We can't connect to database service!"." "
								  ."Please contact administrator!"."</p>";
					exit();
					break;

				case 1049:
					// if allow to create then start create database
					if($_autoCreate)
					{
						// connect to mysql database for creating new one
						$link = @mysqli_connect(self::$db_host, self::$db_user, self::$db_pass, 'mysql');
						// if can connect to mysql database
						if($link)
						{
							$qry = "CREATE DATABASE if not exists ". self::$db_name . " DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";
							// try to create database
							if(!@mysqli_query($link, $qry))
							{
								// if cant create db
								return false;
							}
							// else if can create new database then reset link to dbname
							$link = @mysqli_connect(self::$db_host, self::$db_user, self::$db_pass, self::$db_name);
						}
						else
						{
							return false;
						}
					}
					elseif($_autoCreate === false)
					{
						return false;
					}
					// else only show related message
					else
					{
						echo( "<p>".T_("We can't connect to correct database!")." "
									  .T_("Please contact administrator!")."</p>" );
						\lib\main::$controller->_processor(array('force_stop' => true));
					}
					break;

				default:
					// another errors occure
					// on development create connection error handling system
					break;
			}
		}
		// link is created and exist,
		// check if link is exist set it as global variable
		if($link)
		{
			// set charset for link
			@mysqli_set_charset($link, self::$db_charset);
			// save link as global variable
			self::$link = $link;
			self::$link_open[self::$db_name] = $link;
			return true;
		}
		// if link is not created return false
		return false;
	}


	/**
	 * fetch all row from database
	 * @param  [type] $_result    [description]
	 * @param  [type] $resulttype [description]
	 * @return [type]             [description]
	 */
	public static function fetch_all($_result, $_field = null, $resulttype = MYSQLI_ASSOC)
	{
		$result = [];
		// if mysqli fetch all is exist use it
		if(function_exists('mysqli_fetch_all'))
		{
			$result = @mysqli_fetch_all($_result, $resulttype);
		}
		else
		{
			for (; $tmp = $_result->fetch_array($resulttype);)
			{
				$result[] = $tmp;
			}
		}
		// give only one column of result
		if($result && $_field !== null)
		{
			if(is_array($_field))
			{
				// if pass 2 field use one as key and another as value of result
				if(count($_field) === 2 && isset($_field[0]) && isset($_field[1]))
				{
					$result_key   = array_column($result, $_field[0]);
					$result_value = array_column($result, $_field[1]);
					if($result_key && $result_value)
					{
						// for two field use array_combine
						$result = array_combine($result_key, $result_value);
					}
				}
				else
				{
					// need more than 2 field
				}

			}
			else
			{
				$result = array_column($result, $_field);
			}
		}
		// return result
		return $result;
	}


	/**
	 * read query info and analyse it and return array contain result
	 * @return [type] [description]
	 */
	public static function qry_info($_needle = null)
	{
		preg_match_all ('/(\S[^:]+): (\d+)/', mysqli_info(self::$link), $matches);
		$info = array_combine ($matches[1], $matches[2]);
		if($_needle && isset($info[$_needle]))
		{
			$info = $info[$_needle];
		}

		return $info;
	}


	/**
	 * execute sql file directly to add some database
	 * @param  [type]  $_path  [description]
	 * @param  boolean $_tools [description]
	 * @return [type]          [description]
	 */
	public static function execFile($_path, $_addons = false)
	{
		// if want to read from addons update location
		if($_addons)
		{
			$_path = self::$path_addons. $_path. '.sql';
		}

		// if this path exist, read file and run
		if(file_exists($_path))
		{
			// read file and save in variable
			$qry_list = file_get_contents($_path);
			// seperate with semicolon
			$qry_list = explode(';', $qry_list);
			$has_error = null;
			foreach ($qry_list as $key => $qry)
			{
				$qry = trim($qry);
				if($qry && !@mysqli_query(self::$link, $qry))
				{
					$has_error = true;
				}
			}
			// if command execute successfully
			if(!$has_error)
			{
				return true;
			}
		}
		// file not exist or error on creating table, return false
		return false;
	}


	/**
	 * execute files in one folder
	 * @param  [type]  $_path   [description]
	 * @param  [type]  $_group  [description]
	 * @param  boolean $_addons [description]
	 * @return [type]           [description]
	 */
	public static function execFolder($_path = null, $_group = null, $_addons = false)
	{
		$result = [];
		// if want to read from addons update location
		if($_addons)
		{
			$_path    = self::$path_addons. $_path;
			$myDbName = self::find_dbName($_path);
			$_path    = $_path.'/';
			self::connect($myDbName, true);
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
		// for each item with this situation create
		foreach(glob($_path) as $key => $filename)
		{
			$result[$filename] = self::execFile($filename);
		}

		return $result;
	}


	/**
	 * check db exist or not
	 * @return [type] no of tables in database
	 */
	public static function exist($_create = false)
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
		// create a array to install each table only one times, remove duplicate
		foreach ($dbList as $key => $myDbLoc)
		{
			$myDbName = self::find_dbName($myDbLoc);
			if(!in_array($myDbName, $myList))
			{
				$myList[$myDbName] = $myDbLoc;
			}
		}
		// flip array to change location to key
		$myList = array_flip($myList);
		// reverse because first install addons databases
		if($_addonsFirst)
		{
			$myList = array_reverse($myList);
		}

		// run query for each folder
		foreach ($myList as $myDbLoc => $myDbName)
		{
			$myDbCon = $myDbName;
			if(substr($myDbName, -1) === '+')
			{
				$myDbCon = substr($myDbName, 0, -1);
			}
			// if only want to upgrade run connection in specefic condition
			if($_onlyUpgrade)
			{
				$result[$myDbName]['connect'] = db::connect($myDbCon, false);
				$result[$myDbName]['exec']    = self::execFolder($myDbLoc.'/', 'v.');
			}
			// run normal installation
			else
			{
				$result[$myDbName]['connect'] = db::connect($myDbCon, true);
				$result[$myDbName]['exec']    = self::execFolder($myDbLoc.'/');
			}
		}
		// on normal installation call upgrade process to complete installation
		if(!$_onlyUpgrade)
		{
			$result['upgrade'] = self::install(true);
		}

		// decrease php code execution time to default value
		// reset to default
		$max_time = ini_get("max_execution_time");
		ini_set('max_execution_time', $max_time); //300 seconds = 5 minutes
		// return final result
		return $result;
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
	 * this function create a backup from db with exec command
	 * the backup file with bz2 compressing method is created in projectdir/backup/db/
	 * for using this function call it with one of below types
	 * db::backup();
	 * db::backup('Daily');
	 * db::backup('Weekly');
	 * @param  [type] $_period the name of subfolder or type of backup
	 * @return [type]          status of running commad
	 */
	public static function backup_dump($_period = null)
	{
		$_period    = $_period? $_period.'/':null;
		$db_host    = self::$db_host;
		$db_charset = self::$db_charset;
		$dest_file  = self::$db_name.'.'. date('d-m-Y_H-i-s'). '.sql';
		$dest_dir   = database."backup/$_period";
		// create folder if not exist
		if(!is_dir($dest_dir))
			mkdir($dest_dir, 0755, true);

		$cmd  = "mysqldump --single-transaction --add-drop-table";
		$cmd .= " --host='$db_host' --set-charset='$db_charset'";
		$cmd .= " --user='".self::$db_user."'";
		$cmd .= " --password='".self::$db_pass."' '". self::$db_name."'";
		$cmd .= " | bzip2 -c > $dest_dir.$dest_file";

		$return_var = NULL;
		$output     = NULL;
		$result     = exec($cmd, $output, $return_var);
		if($return_var === 0)
			return true;

		return false;
	}


	/**
	 * this function delete older backup file from db backup folder
	 * you can pass type of clean (folder) and days to keep
	 * call function with below syntax
	 * db::clean();
	 * db::clean('Daily');
	 * db::clean('Weekly', 3);
	 * @param  [type] $_period the name of subfolder or type of backup
	 * @param  [type] $_arg    value of the days for keep files
	 * @return [type]          the result of cleaning seperate by type in array
	 */
	public static function clean($_period = null, $_arg = null)
	{
		$days_to_keep = $_arg[0]? $_arg[0]: 3;
		if($_period === false)
		{
			$days_to_keep = 100;
		}
		$_period      = $_period? $_period.'/':null;
		$dest_dir     = database."backup/$_period";
		$result       =
		[
			'folders'   => 0,
			'files'     => 0,
			'deleted'   => 0,
			'duplicate' => 0,
			'skipped'   => 0,
		];

		if(!is_dir($dest_dir))
			return false;

		$handle              = opendir($dest_dir);
		$keep_threshold_time = strtotime("-$days_to_keep days");
		$files_list          = [];
		while (false !== ($file = readdir($handle)))
		{
			if($file === '.' || $file === '..')
			 continue;

			$dest_file_path = "$dest_dir/$file";
			if(!is_dir($dest_file_path))
			{
				$result['files'] += 1;
				$file_time       = filemtime($dest_file_path);
				$file_code = substr($file, strrpos($file, '_')+1, -4);
				if(isset($files_list[$file_code]))
				{
					$result['duplicate'] += 1;
					unlink($dest_file_path);
				}
				else
				{
					$files_list[$file_code] = $file;
				}
				if($file_time < $keep_threshold_time)
				{
					$result['deleted'] += 1;
					unlink($dest_file_path);
				}
				else
				{
					$result['skipped'] += 1;
				}
			}
			else
			{
				$result['folders'] += 1;
			}
		}
		$result['list'] = $files_list;
		return $result;
	}


	/**
	 * create backup from database
	 * @param  [type] $_period [description]
	 * @param  string $_tables [description]
	 * @return [type]          [description]
	 */
	public static function backup($_period = null, $_tables = '*')
	{
		self::connect(true, false);
		mysqli_select_db(self::$link, self::$db_name);

		//get all of the tables
		if($_tables == '*')
		{
			$_tables   = [];
			$result   = mysqli_query(self::$link, 'SHOW TABLES');
			$_tables = self::fetch_all($result, 'Tables_in_'. db_name);
		}
		else
		{
			$_tables = is_array($_tables) ? $_tables : explode(',',$_tables);
		}
		$return = null;

		//cycle through
		foreach($_tables as $table)
		{
			$result     = mysqli_query(self::$link, 'SELECT * FROM '.$table);
			$num_fields = mysqli_num_fields($result);
			$return     .= 'DROP TABLE '.$table.';';
			$row2       = mysqli_fetch_row(mysqli_query(self::$link, 'SHOW CREATE TABLE '.$table));
			$return     .= "\n\n".$row2[1].";\n\n";

			for ($i = 0; $i < $num_fields; $i++)
			{
				while($row = mysqli_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j < $num_fields; $j++)
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);

						if (isset($row[$j]))
						{
							$return.= '"'.$row[$j].'"' ;
						}
						else
						{
							$return.= '""';
						}
						if ($j < ($num_fields-1))
						{
							$return.= ',';
						}
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		// if user pass true in period we call clean func
		if($_period === true)
		{
			$clean_result = self::clean(false);
			print_r($clean_result);
			echo "<hr />";
			$_period = null;
		}
		//save file
		$_period    = $_period? $_period.'/':null;
		$dest_dir   = database."backup/$_period";
		$dest_file  = self::$db_name.'_b'. date('Ymd_His').'_'. md5($return) . '.sql';
		// create folder if not exist
		if(!is_dir($dest_dir))
			mkdir($dest_dir, 0755, true);

		// $dest_file = 'db-backup-'.time().'-'.(md5(implode(',',$_tables))).'.sql';
		$handle = fopen($dest_dir. $dest_file, 'w+');
		if(fwrite($handle, $return) === FALSE)
		{
			echo "Cannot write to file ($filename)";
			return false;
		}
		// write successful close file and return true
		fclose($handle);
		echo "Successfully create database backup<br />";
		echo "Location:  $dest_dir<br />";
		echo "File name: $dest_file<hr />";
		return true;
	}


	/**
	 * run query and get result of this query
	 * @param  [type]  $_qry          [description]
	 * @param  [type]  $_column       [description]
	 * @param  boolean $_onlyOneValue [description]
	 * @return [type]                 [description]
	 */
	public static function get($_qry, $_column = null, $_onlyOneValue = false)
	{
		// generate query and get result
		$result = self::query($_qry);
		// fetch datatable by result
		$result = self::fetch_all($result, $_column);
		// if we have only one row of result only return this row
		if($_onlyOneValue && count($result) === 1 && isset($result[0]))
		{
			$result = $result[0];
		}

		return $result;
	}


	/**
	 * run query string and return result
	 * now you don't need to check result
	 * @param  [type] $_qry [description]
	 * @return [type]       [description]
	 */
	public static function query($_qry)
	{
		// check debug status
		if(!\lib\debug::$status)
		{
			return false;
		}

		// connect to main database
		self::connect(true);
		if(!self::$link)
		{
			return null;
		}
		// if debug mod is true save all string query
		self::log($_qry);
		$result   = mysqli_query(self::$link, $_qry);
		if(!is_a($result, 'mysqli_result') && !$result)
		{
			// no result exist
			// save mysql error
			echo(mysqli_error(self::$link));
			self::log("MYSQL ERROR ". mysqli_error(self::$link));
			return false;
		}
		// return query run result
		return $result;
	}


	public static function insert_id()
	{
		$last_id = @mysqli_insert_id(self::$link);
		return $last_id;
	}

	/**
	 * create select query if you can't create manually!
	 * @param  string $_table [description]
	 * @param  array  $_field [description]
	 * @param  array  $_where [description]
	 * @param  array  $_arg   [description]
	 * @return [type]         [description]
	 */
	public static function select($_table = 'options', $_field = [], $_where = [], $_arg = [])
	{
		// calc fields
		$myfield = "*";
		if(is_array($_field) & $_field)
		{
			$myfield = implode(", ", $_field);
			$myfield = substr($myfield, 0, -2);
		}
		elseif(isset($_field))
		{
			$myfield = "`$_field`";
		}

		// calc where
		$mywhere = "";
		if(is_array($_where) & $_where)
		{
			foreach ($_where as $key => $value)
			{
				// in all condition except first loop
				if($mywhere)
				{
					$opr = 'AND';
					// if opr isset use it
					if(isset($value['opr']))
					{
						$opr = $value['opr'];
						if(isset($value['value']))
						{
							$value = $value['value'];
						}
						else
						{
							// if value is not set use null
							$value = "NULL";
						}
					}
					$mywhere .= " $opr ";
				}

				if(is_array($value))
				{
					$value = implode(", ", $value);
					$value = substr($value, 0, -2);
					$mywhere .= "$key IN ($value)";
				}
				elseif(substr($value, 0, 4) === 'LIKE')
				{
					$mywhere .= "`$key` $value";
				}
				elseif(is_string($value))
				{
					$mywhere .= "`$key` = '$value'";
				}
				else
				{
					$mywhere .= "`$key` = $value";
				}
			}
		}
		else
		{
			$mywhere = "";
		}


		$qry = "SELECT $myfield FROM $_table";
		if($mywhere)
		{
			$qry .= " WHERE $mywhere";
		}

		return $qry;
	}


	/**
	 * save log of sql request into file for debug
	 * @param  [type] $_text [description]
	 * @return [type]        [description]
	 */
	private static function log($_text)
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


	/**
	 * return version of mysql used on server
	 * @return [type] [description]
	 */
	public static function version()
	{
		// mysqli_get_client_info();
		// mysqli_get_client_version();
		return mysqli_get_server_version(self::$link);
	}

	/**
	 * get pagnation
	 *
	 * @param      <type>  $_query   The query
	 * @param      <type>  $_length  The length
	 *
	 * @return     <type>  array [startlimit, endlimit]
	 */
	public static function pagnation($_query, $_length, $_force = true)
	{
		if($_force)
		{
			if(is_int($_query))
			{
				$count = $_query;
			}
			else
			{
				$count = self::query($_query);
				$count = mysqli_num_rows($count);
			}
			\lib\main::$controller->pagnation_make($count, $_length);
			$current = \lib\main::$controller->pagnation_get('current');

			$length = \lib\main::$controller->pagnation_get('length');
			$limit_start = ($current - 1) * $length ;
			if($limit_start < 0)
			{
				$limit_start = 0;
			}
			$limit_end = $length;
			return [$limit_start, $limit_end];
		}
	}


	/**
	 * get num rows of query
	 *
	 * @return     <int>  ( description_of_the_return_value )
	 */
	public static function num()
	{
		$num = @mysqli_num_rows(self::$link);
		return $num;
	}
}
?>