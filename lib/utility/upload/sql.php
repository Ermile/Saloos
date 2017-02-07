<?php
namespace lib\utility\upload;

trait sql
{

	/**
	 * get count of attachment in post table
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function attachment_count()
	{
		$query = "SELECT COUNT(posts.id) AS 'count' FROM posts WHERE post_type = 'attachment' ";
		$count = \lib\db::get($query,'count', true);
		return $count;
	}

	/**
	 * check duplocate MD5 of file in database
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function duplicate($_md5)
	{

		$qry_count = "SELECT posts.id AS 'id' FROM posts WHERE post_slug = '$_md5' LIMIT 1";
		$qry_count = \lib\db::get($qry_count, 'id', true);
		if($qry_count || !empty($qry_count))
		{
			$id = (int) $qry_count;
			\lib\storage::set_upload(["id" =>  $id]);
			return true;
		}
		return false;
	}
}
?>