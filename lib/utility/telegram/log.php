<?php
namespace lib\utility\telegram;

/** telegram save library**/
class log extends tg
{
	/**
	 * this library help to save something on telegram
	 * v1.5
	 */


	/**
	 * save log of process into file
	 * @param  [type] $_data [description]
	 * @return [type]        [description]
	 */
	public static function save($_data, $_hook = false)
	{
		// if do not allow to save return null
		if(!self::$saveLog)
		{
			return null;
		}
		$fileAddr = root.'public_html/files/telegram/tg_'. self::$name. '.json';
		file_put_contents($fileAddr, json_encode($_data, JSON_UNESCAPED_UNICODE). "\r\n", FILE_APPEND);

		// if not in hook return null
		if($_hook)
		{
			self::saveHistory(self::response('text'));
			return self::saveHook($_data);
		}
		else
		{
			return self::saveResponse($_data);
		}
	}


	/**
	 * save history of messages into session of this user
	 * @param  [type] $_text [description]
	 * @return [type]        [description]
	 */
	private static function saveHistory($_text, $_maxSize = 20)
	{
		if(!isset($_SESSION['tg']['history']))
		{
			$_SESSION['tg']['history'] = [];
		}
		// Prepend text to the beginning of an session array
		array_unshift($_SESSION['tg']['history'], $_text);
		// if count of messages is more than maxSize, remove old one
		if(count($_SESSION['tg']['history']) > $_maxSize)
		{
			// Pop the text off the end of array
			array_pop($_SESSION['tg']['history']);
		}
	}


	/**
	 * save data on hooking
	 * @param  [type] $_data [description]
	 * @return [type]        [description]
	 */
	private static function saveHook($_data)
	{
		// define user detail array
		$from_id = self::response('from');
		// add user_id to save dest of files
		self::$saveDest .= $from_id.'-'. self::response('from', 'username').'/';
		// if we do not have from id return false
		if(!isset($_data['message']['from']) || !$from_id)
		{
			return false;
		}

		$meta = $_data['message']['from'];
		// calc full_name of user
		$meta['full_name'] = trim(self::response('from','first_name'). ' '. self::response('from','last_name'));

		if($contact = self::response('contact', null))
		{
			$meta = array_merge($meta, $contact);
			// if user send contact detail save as normal user
			if(isset($contact['phone_number']))
			{
				\lib\db\users::signup($contact['phone_number'], 'telegram', true, $meta['full_name']);
				self::$user_id = \lib\db\users::$user_id;
			}
			// if user send contact detail then save all of his/her profile photos
			self::sendResponse(['method' => 'getUserProfilePhotos']);
		}
		elseif($location = self::response('location'))
		{
			$meta = array_merge($meta, $location);
		}
		// if user_id is not set try to give user_id from database
		if(!isset(self::$user_id))
		{
			$qry = "SELECT `user_id`
				FROM options
				WHERE
					`option_cat` = 'telegram' AND
					`option_key` LIKE 'user_%' AND
					`option_value` = $from_id
			";
			$my_user_id = \lib\db::get($qry, 'user_id', true);
			if(is_numeric($my_user_id))
			{
				self::$user_id = $my_user_id;
			}
		}

		$userDetail =
		[
			'cat'    => 'telegram',
			'key'    => 'user_'.self::response('from', 'username'),
			'value'  => $from_id,
			'meta'   => $meta,
		];
		if(isset(self::$user_id))
		{
			$userDetail['user']   = self::$user_id;
			$userDetail['status'] = 'enable';
		}
		else
		{
			$userDetail['status'] = 'disable';
		}
		// save in options table
		\lib\utility\option::set($userDetail, true);
		// save session id database only one time
		// if exist use old one
		// else insert new one to database
		\lib\utility\session::save_once(self::$user_id, 'telegram_'.self::response('from', 'id'));

		return true;
	}


	/**
	 * save telegram response
	 * @param  [type] $_data [description]
	 * @return [type]        [description]
	 */
	private static function saveResponse($_data)
	{
		// if this result is not okay return false
		if(!$_data['ok'])
		{
			return false;
		}
		// if result is not good return false
		if(!isset($_data['result']['total_count']) || !isset($_data['result']['photos']))
		{
			return false;
		}

		// now we are giving photos
		$count  = $_data['result']['total_count'];
		$photos = $_data['result']['photos'];
		$result = [];
		// if has more than one image
		if($count === 0)
		{
			self::createUserDetail($img['file_id']);
		}
		elseif($count > 0)
		{
			// get biggest size of first image(last profile photo)
			$img = end($photos[0]);
			// if file_id is exist
			if(isset($img['file_id']))
			{
				self::createUserDetail($img['file_id']);
			}
		}


		// if dir is not created, create it
		if(!is_dir(self::$saveDest))
		{
			\lib\utility\file::makeDir(self::$saveDest, 0775, true);
		}

		// loop on all photos
		foreach ($photos as $photoKey => $photo)
		{
			$photo = end($photo);
			if(isset($photo['file_id']) && $photo['file_id'])
			{
				$myFile = self::getFile(['file_id' => $photo['file_id']]);
				// save file
				$result[$photoKey] = self::saveFile($myFile, $photoKey, '.jpg');
			}
		}
		return $result;
	}


	/**
	 * save telegram file
	 * @param  [type] $_response [description]
	 * @param  [type] $_prefix   [description]
	 * @param  [type] $_ext      [description]
	 * @return [type]            [description]
	 */
	public static function saveFile($_response, $_prefix = null, $_ext = null)
	{
		if(!isset($_response['ok']) || !isset($_response['result']) || !isset($_response['result']['file_path']))
		{
			return false;
		}
		$file_id   = $_response['result']['file_id'];
		$file_path = $_response['result']['file_path'];
		$dest      = self::$saveDest;
		$exist     = glob($dest.'/*'.$file_id.$_ext);
		// if file exist then don't need to get it from server, return
		if(count($exist))
		{
			return null;
		}
		// add prefix if exits
		if($_prefix)
		{
			$dest .= $_prefix .'-';
		}
		// add file_id
		$dest      .= $file_id;
		if($_ext)
		{
			$dest = $dest. $_ext;
		}
		// save file source
		$source    = "https://api.telegram.org/file/bot";
		$source    .= self::$api_key. "/". $file_path;

		return copy($source, $dest);
	}


	/**
	 * generate user details
	 * @return [type] [description]
	 */
	public static function createUserDetail($_photo = null, $_createArray = true, $_sendMsg = true)
	{
		// create detail of caption
		$user_details = "Your Id: ". self::response('from');
		$user_details .= "\nName: ". self::response('from', 'first_name');
		$user_details .= ' '. self::response('from', 'last_name');
		$user_details .= "\nUsername: @". self::response('from', 'username');
		if($_createArray)
		{
			// create array of message
			if($_photo)
			{
				$user_details =
				[
					'caption' => $user_details,
					'method'  => 'sendPhoto',
					'photo'   => $_photo,
				];
			}
			else
			{
				$user_details =
				[
					'text' => $user_details,
				];
			}
			$user_details['reply_to_message_id'] = self::response('message_id');
			if($_sendMsg)
			{
				$user_details = self::sendResponse($user_details);
			}
		}
		return $user_details;
	}
}
?>