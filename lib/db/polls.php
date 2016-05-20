<?php
namespace lib\db;

/** work with polls **/
class polls
{
	/**
	 * this library work with acoount
	 * v1.0
	 */


	/**
	 * get list of polls
	 * @param  [type] $_user_id set userid
	 * @param  [type] $_return  set return field value
	 * @param  string $_type    set type of poll
	 * @return [type]           an array or number
	 */
	public static function get($_user_id = null, $_return = null, $_type = 'sarshomar')
	{
		// calc type if needed
		if($_type === null)
		{
			$_type = "post_type LIKE 'poll_%'";
		}
		else
		{
			$_type = "post_type = 'poll_". $_type. "'";
		}
		// calc user id if exist
		if($_user_id)
		{
			$_user_id = "AND user_id = $_user_id";
		}
		else
		{
			$_user_id = null;
		}
		// generate query string
		$qry = "SELECT * FROM posts WHERE $_type $_user_id";
		// run query
		if($_return && $_return !== 'count')
		{
			$result = \lib\db::get($qry, $_return);
		}
		else
		{
			$result = \lib\db::get($qry);
		}
		// if user want count of result return count of it
		if($_return === 'count')
		{
			return count($result);
		}
		// return last insert id
		return $result;
	}


	/**
	 * save poll into database
	 * @return [type] [description]
	 */
	private static function save($_input, $_user_id)
	{
		// return false if count of input value less than 3
		// 1 question
		// 2 answer or more
		if(count($_input) < 3 || count($_input) > 10 || !isset($_input['question']))
		{
			return false;
		}
		// extract question
		$question = $_input['question'];
		unset($_input['question']);
		// save question into post table
		$saveQusStatus = self::saveQuestion($question, $_input, $_user_id);
		// save answers into options table
		$saveAnsStatus = self::saveAnswers($_input, $saveQusStatus);
		// return final result
		return $saveAnsStatus;
	}


	/**
	 * save question into post table
	 * @param  [type] $_question    [description]
	 * @param  [type] $_answersList [description]
	 * @return [type]               [description]
	 */
	public static function saveQuestion($_question, $_answersList, $_user_id)
	{
		$slug         = \lib\utility\filter::slug($_question);
		$url          = 'civility/'.$_user_id.'/'.$slug;
		$_answersList = json_encode($_answersList, JSON_UNESCAPED_UNICODE);
		$pubDate      = date('Y-m-d H:i:s');
		// create query string
		$qry = "INSERT INTO posts
		(
			`post_language`,
			`post_title`,
			`post_slug`,
			`post_url`,
			`post_meta`,
			`post_type`,
			`post_status`,
			`post_publishdate`,
			`user_id`
		)
		VALUES
		(
			'fa',
			'$_question',
			'$slug',
			'$url',
			'$_answersList',
			'poll',
			'draft',
			'$pubDate',
			$_user_id
		)";
		// run query
		$result  = \lib\db::query($qry);
		// return last insert id
		return \lib\db::insert_id();
	}


	/**
	 * save answers into options table
	 * @param  [type] $_answersList raw answer list
	 * @return [type]               [description]
	 */
	public static function saveAnswers($_answersList, $_post_id)
	{
		$answers = [];
		$max_ans = 10;
		// foreach answers exist fill the array
		foreach ($_answersList as $key => $value)
		{
			$answers[$key]['txt'] = $value;
		}
		// decode for saving into db
		$answers     = json_encode($answers, JSON_UNESCAPED_UNICODE);
		$option_data =
		[
			'post'   => $_post_id,
			'cat'    => 'meta_polls',
			'key'    => 'answers_'.$_post_id,
			'value'  => "",
			'meta'   => $answers,
			'status' => 'enable',
		];
		// save in options table and if successful return session_id
		return \lib\utility\option::set($option_data, true);
	}



}
?>