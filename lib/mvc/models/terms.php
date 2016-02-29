<?php
namespace lib\mvc\models;

trait terms
{
	/**
	 * return the detail of term
	 * @param  boolean $_forcheck [description]
	 * @return [type]             [description]
	 */
	public function get_terms($_forcheck = false)
	{
		$url = $this->url('path');
		$qry = $this->sql()->tableTerms()->whereTerm_url($url)->select();

		if($qry->num() == 1)
		{
			$datarow = $qry->assoc();

			if($_forcheck)
				return array( 'table' => 'terms',
							  'type' => $datarow['term_type'],
							  'slug' => $datarow['term_slug']
							);
			else
				return $datarow;
		}
		return false;
	}


	/**
	 * return list of cats in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_catsInTerm()
	{
		$url = $this->url('path');

		$qry_id = $this->sql()->table('terms')->where('term_url', $url)->select()->assoc('id');
		$datatable = $this->sql()->table('terms')->where('term_parent', $qry_id)->select()->allassoc();
		// var_dump($datatable);
		return $datatable;
	}


	/**
	 * return list of posts in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_postsInTerm()
	{
		$url = $this->url('path');
		if(substr($url, 0, 4) === 'tag/')
			$url = substr($url, 4, $url);


		if(substr($url, 0, 11) === 'book-index/')
		{
			preg_match("#^book-index/([^\/]*)(.*)$#", $url, $m);
			$url_raw = "book/$m[1]";


			if($m[2] !== '')
			{
				$qry = $this->sql()->table('posts')->where('post_status', 'publish')->order('id', 'ASC');
				$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
				$qry->join('terms')->on('id', '#termusages.term_id')->and('term_url', $url)->groupby('#posts.id');
			}
			else
			{
				$parent_id = $this->sql()->table('posts')->where('post_url', $url_raw)
					->and('post_status', 'publish')->select()->assoc('id');
				$qry = $this->sql()->table('posts')->where('post_parent', $parent_id)
					->and('post_status', 'publish')->order('id', 'ASC');
			}


			return $qry->select()->allassoc();
		}

		$qry = $this->sql()->table('posts')->where('post_status', 'publish')->order('id', 'DESC');
		$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
		$qry->join('terms')->on('id', '#termusages.term_id')->and('term_url', $url)->groupby('#posts.id');

		return $qry->select()->allassoc();
	}
}
?>
