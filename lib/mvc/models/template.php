<?php
namespace lib\mvc\models;

trait template
{
	/**
	 * this fuction check the url entered from user in database
	 * first search in posts and if not exist search in terms table
	 * @return [array] datarow of result if exist else return false
	 */
	function s_template_finder()
	{
		// first of all search in url field if exist return row data
		$tmp_result = $this->get_posts(true);
		if($tmp_result)
			return $tmp_result;

		// if url not exist in posts then search in terms table and if exist return row data
		$tmp_result = $this->get_terms(true);
		if($tmp_result)
			return $tmp_result;


		// else retun false
		return false;
	}


	/**
	 * [get_posts description]
	 * @param  boolean $_forcheck [description]
	 * @return [type]             [description]
	 */
	public function get_posts($_forcheck = false)
	{
		$url     = $this->url('path');
		$preview = \lib\utility::get('preview');
		// search in url field if exist return row data
		$qry = $this->sql()->table('posts')->where('post_url', $url);
		if(!$preview)
			$qry = $qry->andPost_status('publish');

		$qry = $qry->groupOpen('g_language');
		$qry = $qry->and('post_language', substr(\lib\router::get_storage('language'), 0, 2));
		$qry = $qry->or('post_language', 'IS', 'NULL');
		$qry = $qry->groupClose('g_language');

		$qry = $qry->select();
		if($qry->num() == 1)
		{
			$datarow = $qry->assoc();

			if($_forcheck)
				return array( 'table' => 'posts',
							  'type' => $datarow['post_type'],
							  'slug' => $datarow['post_slug']
							);
			else
			{
				foreach ($datarow as $key => $value)
				{
					// if field contain json, decode it
					if(substr($value, 0, 1) == '{')
						$datarow[$key] = json_decode($value, true);
				}
				return $datarow;
			}
		}

		return false;
	}
}
?>
