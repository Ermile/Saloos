<?php
namespace lib\view;

trait optimize
{
	public $form;
	public $forms;


	/**
	 * [createform description]
	 * @param  [type] $_name [description]
	 * @param  [type] $_type [description]
	 * @return [type]        [description]
	 */
	function createform($_name, $_type = null)
	{
		$this->data->extendForm = true;
		if(!$this->form)
		{
			$this->twig_macro('form');
			$this->form = new  \lib\form;
			$this->data->form = object();
		}

		$args = func_get_args();
		if(count($args) === 2)
		{
			$submit_value = T_('submit');

			if($_type == 'add')          $submit_value = T_('submit');
			elseif($_type == 'edit')     $submit_value = T_('save');
			elseif($_type == 'login')    $submit_value = T_('sing in');
			elseif($_type == 'register') $submit_value = T_('create an account');
			elseif(!empty($_type))       $submit_value = $_type;

			array_push($args, $submit_value);
		}

		$form = call_user_func_array(array($this->form, 'make'), $args);
		if(get_class($form) == 'lib\form' || preg_match("/cls\\\\form/", get_class($form)))
		{
			preg_match("/^(@[^\.]+)*\.(.+)$/", $_name, $sName);
			$this->data->form->{$sName[2]} = $form;
		}

		// if type of form is edit then fill it with related data
		if($_type == 'edit')
		{
			$this->form_fill($form, $sName[2]);
		}


		return $form;
	}


	/**
	 * This function fill forms for edit and work automatically
	 * @param  [type] $_form  [description]
	 * @param  [type] $_table [description]
	 * @return [type]         [description]
	 */
	public function form_fill($_form, $_table = null)
	{
		$_table   = $_table? $_table: $this->data->module;
		$_datarow = $this->model()->datarow($_table);

		foreach ($_form as $key => $value)
		{
			if(isset($_datarow[$key]))
			{
				$oForm = $_form->$key;
				// var_dump($_form);
				if($oForm->attr['type'] == "radio" || $oForm->attr['type'] == "select" || $oForm->attr['type'] == "checkbox")
				{
					foreach ($oForm->child as $k => $v)
					{
						if($v->attr["value"] == $_datarow[$key])
						{
							if ($oForm->attr['type'] == "select")
							{
								$_form->$key->child($k)->selected("selected");
							}
							else
							{
								$v->checked("checked");
							}
						}
						else
						{
							$v->attr('checked', null);
							$v->attr('selected', null);
						}
					}
				}
				else
				{
					$oForm->value($_datarow[$key]);
				}
			}
		}
	}
}
?>