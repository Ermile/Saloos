<?php
namespace lib\utility\telegram;

/** telegram step by step library**/
class step extends tg
{
	/**
	 * this library help create step by step messages
	 * v3.2
	 */

	/**
	 * define variables
	 * @param  [type] $_name name of current step for call specefic file
	 * @return [type]        [description]
	 */
	public static function start($_name)
	{
		// name of step for call specefic file
		self::set('name', $_name);
		// counter of step number, increase automatically
		self::set('counter', 1);
		// pointer of current step, can change by user commands
		self::set('pointer', 1);
		// save text of each step
		self::set('text', []);
		// save last entered text
		self::set('last', null);
		// save title for some text on saving
		self::set('textTitle', null);
	}


	/**
	 * delete session step value
	 * @return [type] [description]
	 */
	public static function stop()
	{
		unset($_SESSION['tg']['step']);
	}


	/**
	 * set specefic key of step
	 * @param  string $_key   name of key
	 * @param  string $_value value of this key
	 * @return [type]         [description]
	 */
	public static function set($_key, $_value)
	{
		// some condition for specefic keys
		switch ($_key)
		{
			case 'text':
				if(!is_string($_value))
				{
					return false;
				}
				// if title of text isset use this title
				if($text_title = self::get('textTitle'))
				{
					$_SESSION['tg']['step'][$_key][$text_title] = $_value;
					// empty textTitle
					$_SESSION['tg']['step']['textTitle'] = null;
				}
				// else only add new text
				else
				{
					$_SESSION['tg']['step'][$_key][] = $_value;
				}
				$_SESSION['tg']['step']['last']    = $_value;
				$_SESSION['tg']['step']['counter'] = $_SESSION['tg']['step']['counter'] + 1;
				break;

			case 'pointer':
				$_SESSION['tg']['step']['counter'] = $_SESSION['tg']['step']['counter'] + $_value;

			default:
				$_SESSION['tg']['step'][$_key] = $_value;
				// return that value was set!
				break;
		}
		// return true because it's okay!
		return true;
	}


	/**
	 * get specefic key of step
	 * @param  string $_key [description]
	 * @return [type]       [description]
	 */
	public static function get($_key = null)
	{
		if($_key === null)
		{
			if(isset($_SESSION['tg']['step']))
			{
				return $_SESSION['tg']['step'];
			}
		}
		elseif($_key === false)
		{
			if(isset($_SESSION['tg']['step']))
			{
				return true;
			}
		}
		elseif(isset($_SESSION['tg']['step'][$_key]))
		{
			return $_SESSION['tg']['step'][$_key];
		}
		elseif(isset($_SESSION['tg']['step']))
		{
			return null;
		}

		return false;
	}


	/**
	 * go to next step
	 * @param  integer  $_num number of jumping
	 * @return function       result of jump
	 */
	public static function plus($_num = 1, $_key = 'pointer', $_relative = true)
	{
		if($_relative)
		{
			$_num = self::get($_key) + $_num;
		}

		return self::set($_key, $_num);
	}


	/**
	 * goto specefic step directly
	 * @param  integer $_step [description]
	 * @param  string  $_key  [description]
	 * @return [type]         result of jump
	 */
	public static function goto($_step = 1, $_key = 'pointer')
	{
		return self::set($_key, $_step);
	}



	/**
	 * [check description]
	 * @param  [type] $_text [description]
	 * @return [type]        [description]
	 */
	public static function check($_text)
	{
		// $tmp_text =
		// "user_id_: ".   tg::$user_id.
		// "\n id: ".      session_id().
		// "\n name: ".    session_name().
		// "\n session: ". json_encode($_SESSION);
		// // for debug
		// $tmp =
		// [
		// 	'text' => $tmp_text
		// ];
		// $a = tg::sendResponse($tmp);


		// if before this message step started
		if(self::get(false))
		{
			// calc current step
			switch ($_text)
			{
				case '/done':
				case '/end':
				case '/stop':
				case '/cancel':
					// if user want to stop current step
					$currentStep = 'stop';
					break;

				default:
					$currentStep = 'step'. self::get('pointer');
					break;
			}
			// create namespace and class name
			$call        = tg::$cmdFolder. 'step_'. self::get('name');
			// create function full name
			$funcName    = $call. '::'. $currentStep;
			// save result of step
			$result      = null;
			// generate func name
			if(is_callable($funcName))
			{
				// get and return response
				$result = call_user_func($funcName, $_text);
			}
			// save text afrer reading current step function
			self::set('text', $_text);
			// after saving text return result
			return $result;
		}
	}
}
?>