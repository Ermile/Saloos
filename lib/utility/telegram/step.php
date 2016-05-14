<?php
namespace lib\utility\telegram;

/** telegram step by step library**/
class step extends tg
{
	/**
	 * this library help create step by step messages
	 * v2.0
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
				$_SESSION['tg']['step'][$_key][]   = $_value;
				$_SESSION['tg']['step']['last']    = $_value;
				// $_SESSION['tg']['step']['counter'] += $_value;
				self::plus('counter');
				break;

			case 'pointer':
				self::plus('counter');
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
	public static function plus($_key = 'pointer', $_num = 1, $_relative = true)
	{
		if($_relative)
		{
			$_num = self::get($_key) + $_num;
		}

		return self::set($_key, $_num);
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
			// save text
			self::set('text', $_text);
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

			// generate func name
			if(is_callable($funcName))
			{
				// get and return response
				return call_user_func($funcName, $_text);
			}
		}
	}
}
?>