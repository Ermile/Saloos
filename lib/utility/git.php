<?php
namespace lib\utility;

/** Git management **/
class git
{

	public static function createPackage($_saloos = true)
	{
		echo "<pre>";
		$res = array();
		//get details from last commit
		if($_saloos)
		{
			chdir(core);
		}
		exec("git log --name-only --max-count=4", $res);
		//keep only files names, not commit details
		foreach($res as $k=>$string)
		{
			if(!is_file($string))
				unset($res[$k]);
		}
		//recount array
		var_dump($res);
		array_values($res);
		echo "<h1>Files found in git log result:</h1>";
		print_r($res);
		echo "<hr />";
		//specify the folder where your packages are stored
		$packagesfolder = core."packages";
		//specify the folder name for this particular package
		$folder = "package.".date("m.d.Y.h.i", time());
		// if the packages folder doesn't exist create one
		if(!is_dir($packagesfolder))
		{
			mkdir($packagesfolder);
			echo "<h1>Packages folder not found - created a new one</h1>";
			echo "<hr />";
		}
		// $result = self::create_zip($res, $packagesfolder.'/'.$folder.'.zip', true);
		// var_dump($result);
		// copy the files to the package folder and keep the entire folder structure
		// (eg: if a file is in ... sub_folder/sub_sub_folder/file.php ... it will be copied to packagefolder/sub_sub_folder/sub_sub_folder/file.php )

		foreach($res as $file)
		{
			exec('cp --parents '.$file.' '.$packagesfolder.'/');
		}

		echo "<h1>Files copied</h1>";
		echo "<hr />";
		//navigate to the packagefolder
		chdir($packagesfolder);
		//archive now

		exec( 'tar -cf '.$folder.'.tar *' );
		echo "<h1>Files archived</h1>";
		echo "<hr />";

		exec( 'mv '.$folder.'.tar ../' );
		echo "<h1>tar file moved to the packages folder</h1>";
		echo "<hr />";
		//return to the packages folder
		chdir( '..' );
		exec('rm -rf '.$folder.'/');
		echo "<h1>package folder removed</h1>";
		echo "<hr />";
		die("done");
	}


	/* creates a compressed zip file */
	protected static function create_zip($files = array(), $destination = '', $overwrite = false) {
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) { return false; }
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files))
		{
			//cycle through each file
			foreach($files as $file)
			{
				//make sure the file exists
				if(file_exists($file))
				{
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if(count($valid_files))
		{
			//create the archive
			$zip = new \ZipArchive();
			if($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true)
			{
				var_dump($destination);
				return false;
			}
			//add the files
			foreach($valid_files as $file)
			{
				$zip->addFile($file,$file);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

			//close the zip -- done!
			$zip->close();

			//check to make sure the file exists
			return file_exists($destination);
		}
		else
		{
			return false;
		}
	}

}
?>