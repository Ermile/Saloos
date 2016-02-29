<?php
namespace lib\mvc\viewes;

trait terms
{
	/**
	 * [view_terms description]
	 * @return [type] [description]
	 */
	function view_terms()
	{
		$this->data->post = array();
		$tmp_result       = $this->model()->get_terms();
		$tmp_fields       = array(	'id'            =>'id',
									'term_language' =>'language',
									'term_type'     =>'type',
									'term_title'    =>'title',
									'term_slug'     =>'slug',
									'term_url'      =>'url',
									'term_desc'     =>'desc',
									'term_parent'   =>'parent',
									'date_modified' =>'modified'
								);
		foreach ($tmp_fields as $key => $value)
			$this->data->post[$value] = html_entity_decode($tmp_result[$key]);

		$this->data->page['title'] = $this->data->post['title'];

		// generate datatable
		$this->data->datatable = $this->model()->sp_postsInTerm();

		$this->data->datatable_cats = $this->model()->sp_catsInTerm();
		// switch ($this->data->module)
		// {
		// 	case 'book-index':
		// 		$this->data->datatable_cats = $this->model()->sp_catsInTerm();
		// 		break;
		// }

		// set title of page after add title
		$this->set_title();
	}
}
?>