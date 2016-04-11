<?php
namespace lib\utility;

/** Access: handle permissions **/
class permission
{
	/**
	 * this library get permissions and check it!
	 * v1.0
	 */

	/**
	 * return
	 * @param  string $_loc  location
	 * @param  string $_type type of permission needed
	 * @return [type]        [description]
	 */
	public static function access($_content = null, $_loc = null, $_type = null, $_block = null)
	{
		$myStatus = null;
		$su       = null;
		// if user is superviser then set su to true
		// permission id 1 is supervisior of system
		if(isset($_SESSION['user']['permission']) && $_SESSION['user']['permission'] === "1")
		{
			$su       = true;
			$suStatus = \lib\utility\option::permListFill('su');
		}

		// if programmer not set content, give it automatically from address
		if($_content === 'all')
		{
			$myStatus = [];
			if($su)
			{
				foreach ($suStatus as $key => $value)
				{
					if(isset($value['enable']))
					{
						$myStatus[$key] = $value['enable'];
					}
				}
			}
			elseif(isset($_SESSION['permission']))
			{
				foreach ($_SESSION['permission'] as $key => $value)
				{
					if(isset($value['enable']))
					{
						$myStatus[$key] = $value['enable'];
					}
				}
			}
			return $myStatus;
		}
		elseif(!$_content)
		{
			$_content = \lib\router::get_repository_name();
			if($_content !== "content")
			{
				$_content = substr($_content, strpos($_content, '_') + 1);
			}
		}
		if(!isset($suStatus[$_content]) || !isset($suStatus[$_content]['modules']))
		{
			$su = false;
		}

		// if user want specefic location
		if($_loc == 'all')
		{
			if($su)
			{
				$myStatus = $suStatus[$_content]['modules'];
			}
			elseif(isset($_SESSION['permission'][$_content]['modules']))
			{
				$myStatus = $_SESSION['permission'][$_content]['modules'];
			}
		}
		elseif($_loc)
		{
			if($_type)
			{
				if($su)
				{
					if(isset($suStatus[$_content]['modules'][$_loc][$_type]))
					{
						$myStatus = $suStatus[$_content]['modules'][$_loc][$_type];
					}
				}
				elseif(isset($_SESSION['permission'][$_content]['modules'][$_loc][$_type]))
				{
					$myStatus = $_SESSION['permission'][$_content]['modules'][$_loc][$_type];
				}
			}
			else
			{
				if($su)
				{
					$myStatus = $suStatus[$_content]['modules'][$_loc];
				}
				elseif(isset($_SESSION['permission'][$_content]['modules'][$_loc]))
				{
					$myStatus = $_SESSION['permission'][$_content]['modules'][$_loc];
				}
			}
		}
		// else if not set location and only want enable status
		else
		{
			if($su)
			{
				$myStatus = $suStatus[$_content]['enable'];
			}
			elseif(isset($_SESSION['permission'][$_content]['enable']))
			{
				$myStatus = $_SESSION['permission'][$_content]['enable'];
			}
		}


		if(!$myStatus)
		{
			if($_block === "notify" && $_type && $_loc)
			{
				$msg = null;
				switch ($_type)
				{
					case 'view':
						$msg = "You can't view this part of system";
						break;

					case 'add':
						$msg = T_("You can't add new") .' '. T_($_loc);
						break;

					case 'edit':
						$msg = T_("You can't edit") .' '. T_($_loc);
						break;

					case 'delete':
						$msg = T_("You can't delete") .' '. T_($_loc);
						break;

					default:
						$msg = "You can't access to this part of system";
						break;
				}
				$msg = $msg. "<br/> ". T_("Because of your permission");

				\lib\debug::error(T_($msg));
				// exit();
			}
			elseif($_block)
			{
				\lib\error::access(T_("You can't access to this page!"));
			}
			else
			{
				// do nothing!
			}
		}
		return $myStatus;
	}
}
?>