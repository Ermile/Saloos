<?php
namespace lib\db;

/** terms managing **/
class terms
{
	/**
	 * this library work with terms
	 * v1.0
	 */


	/**
	 * get terms list used in specefic table
	 * @param  [type] $_usageid [description]
	 * @param  string $_return  [description]
	 * @param  string $_foreign [description]
	 * @param  string $_type    [description]
	 * @return [type]           [description]
	 */
	public static function usage($_usageid, $_return = 'term_title', $_foreign = 'posts', $_type = 'tag')
	{
		if($_return === null)
		{
			$_return = 'term_title';
		}
		if($_foreign === null)
		{
			$_foreign = 'posts';
		}
		if($_type === null)
		{
			$_type = 'tag';
		}
		$qry ="SELECT * FROM terms
		INNER JOIN termusages
			ON termusages.term_id = terms.id
			WHERE
				termusages.termusage_foreign = '$_foreign' AND
				termusages.termusage_id = $_usageid AND
				terms.term_type = '$_type'
		";
		// run query
		if($_return && $_return !== true)
		{
			$result = \lib\db::get($qry, $_return);
		}
		else
		{
			$result = \lib\db::get($qry);
		}
		// if user want count of result return count of it
		if($_return === true)
		{
			$result = count($result);
		}
		// return final result
		return $result;
	}
}
?>