<?php
namespace lib\mvc;

class view extends \lib\view
{
	use \lib\mvc\viewes\constructor;
	use \lib\mvc\viewes\posts;
	use \lib\mvc\viewes\terms;

	/**
	 * set title for pages depending on condition
	 */
	public function set_title()
	{
		if($this->data->page['title'])
		{
			// for child page set the
			if($this->data->child && SubDomain === 'cp')
			{
				if(substr($this->module(), -3) === 'ies')
				{
					$moduleName = substr($this->module(), 0, -3).'y';
				}
				elseif(substr($this->module(), -1) === 's')
				{
					$moduleName = substr($this->module(), 0, -1);
				}
				else
				{
					$moduleName = $this->module();
				}

				$childName = $this->child(true);
				if($childName)
				{
					$this->data->page['title'] = T_($childName).' '.T_($moduleName);
				}
			}

			// set user-friendly title for books
			if($this->module() === 'book')
			{
				$breadcrumb = $this->model()->breadcrumb();
				$this->data->page['title'] = $breadcrumb[0] . ' ';
				array_shift($breadcrumb);

				foreach ($breadcrumb as $value)
				{
					$this->data->page['title'] .= $value . ' - ';
				}
				$this->data->page['title'] = substr($this->data->page['title'], 0, -3);

				$this->data->parentList = $this->model()->sp_books_nav();
			}

			if($this->data->page['special'])
				$this->global->title = $this->data->page['title'];
			else
				$this->global->title = $this->data->page['title'].' | '.$this->data->site['title'];
		}
		else
			$this->global->title = $this->data->site['title'];

		$this->global->short_title = substr($this->global->title, 0, strrpos(substr($this->global->title, 0, 120), ' ')) . '...';
	}
}
?>