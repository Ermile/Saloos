<?php
namespace lib;
class form
{
	public static $formExtends;
	private $Element_Sortable_MCLS = array();

	function make($name = false, $args = false)
	{
		if(is_object($name))
		{
			$this->$args = $name;
		}
		elseif(preg_match("/^\.(.*)$/", $name, $fname))
		{
			$class = '\cls\form\\'.$fname[1];
			$args  = func_get_args();
			if(class_exists($class))
				return new $class(...array_splice($args, 1));
			else
			{
				$class = '\addons\includes'. $class;
				if(class_exists($class))
					return new $class(...array_splice($args, 1));
				else
					error::page("form $class not exist");
			}

		}
		elseif(preg_match("/^\#(.*)$/", $name, $fname))
		{
			$class = '\cls\form\symbol';
			if(class_exists($class))
				$form = self::$formExtends ? self::$formExtends : new $class;
			else
			{
				$class = '\addons\includes'. $class;
				if(class_exists($class))
					$form = self::$formExtends ? self::$formExtends : new $class;
				else
					error::page("form $class not exist");
			}
			if(isset($form->{$fname[1]}))
			{
				return $form->{$fname[1]};
				return null;
			}
			else
			{
				error::page("form extend $fname[1]");
			}
		}
		elseif(preg_match("/^\@([^\.]*)\.(.*)$/", $name, $fname))
		{
			$s_function = "get".ucfirst($fname[1]);
			$table = \lib\sql\table::$s_function($fname[2]);
			if($table)
			{
				$form = new $this;
				foreach ($table as $key => $value) {
					if(isset($value->closure) && isset($value->closure->form))
					{
						$form->make($value->closure->form, $key);
					}
				}
				// var_dump($form);
				$form->add('submit', 'submit')->value(T_('submit'))->class('button primary row-clear');
				$args = func_get_args();
				if(isset($args[2]))
					$form->submit->value($args[2]);

				// if(isset($args->submit))
				// 	$form->submit->value($args->submit);

				return $form;
			}
			return null;
		}
		elseif($name)
		{
			$element = new \lib\form\maker($name);
			return $element;
		}
		else
		{
			return new $this;
		}
	}

	private function sortable(){
		if(count($this->Element_Sortable_MCLS) == 0){
			$array = array();
			foreach ($this as $key => $value) {
				if($key == 'Element_Sortable_MCLS') continue;
				array_push($array, $key);
			}
			$this->Element_Sortable_MCLS = $array;
		}
		ksort($this->Element_Sortable_MCLS);
	}

	function compile($autoSet = true){
		$this->sortable();
		$array = array();
		foreach ($this->Element_Sortable_MCLS as $k => $v) {
			$value = $this->$v;
			if(method_exists($value, "compile")){
				array_push($array, $value->compile($autoSet));
			}
		}
		return $array;
	}

	function after($name, $after){
		$this->sortable();
		$index = array_search($name, $this->Element_Sortable_MCLS);
		if($index === false) return $this;
		$peroperty = $this->Element_Sortable_MCLS[$index];

		$aindex = array_search($after, $this->Element_Sortable_MCLS);
		if($aindex === false) return $this;
		$array = array();
		$aValue = null;
		foreach ($this->Element_Sortable_MCLS as $key => $value) {
			if($key == $index) continue;
			array_push($array, $value);
			if($key == $aindex){
				array_push($array, $peroperty);
			}
		}
		$this->Element_Sortable_MCLS = $array;
		return $this;
	}

	function before($name, $before){
		$this->sortable();
		$index = array_search($name, $this->Element_Sortable_MCLS);
		if($index === false) return $this;
		$peroperty = $this->Element_Sortable_MCLS[$index];

		$bindex = array_search($before, $this->Element_Sortable_MCLS);
		if($bindex === false) return $this;
		$array = array();
		$aValue = null;
		foreach ($this->Element_Sortable_MCLS as $key => $value) {
			if($key == $index) continue;
			if($key == $bindex){
				array_push($array, $peroperty);
			}
			array_push($array, $value);
		}
		$this->Element_Sortable_MCLS = $array;
		return $this;
	}
	function atEnd($name){
		$this->sortable();
		$index = array_search($name, $this->Element_Sortable_MCLS);
		if($index === false) return $this;
		$peroperty = $this->Element_Sortable_MCLS[$index];
		$array = array();
		foreach ($this->Element_Sortable_MCLS as $key => $value) {
			if($key == $index) continue;
			array_push($array, $value);
		}
		array_push($array, $peroperty);
		$this->Element_Sortable_MCLS = $array;
		return $this;

	}

	function atFirst($name){
		$this->sortable();
		$index = array_search($name, $this->Element_Sortable_MCLS);
		if($index === false) return $this;
		$peroperty = $this->Element_Sortable_MCLS[$index];
		$array = array();
		array_push($array, $peroperty);

		foreach ($this->Element_Sortable_MCLS as $key => $value) {
			if($key == $index) continue;
			array_push($array, $value);
		}
		$this->Element_Sortable_MCLS = $array;
		return $this;
	}

	function add($name, $type = false, $replace = false){
		$this->sortable();
		$form = new $this;
		$frm = $type == false ? $name : $type;
		if(!$type){

			foreach ($frm as $key => $value) {
				if(!isset($this->$key) || $replace){
					$this->$key = $value;
					$k = array_search($key, $this->Element_Sortable_MCLS);
					if($k == false){
						array_push($this->Element_Sortable_MCLS, $key);
					}
				}
			}
			return $this;
		}else{
			$k = array_search($name, $this->Element_Sortable_MCLS);
			if(is_object($frm)){
				$this->$name = $frm;
				if($k === false){
					array_push($this->Element_Sortable_MCLS, $name);
				}
			}else{
				if(!isset($this->$name) || $replace){
					$this->$name = $form->make($type);
					if($k == false){
						array_push($this->Element_Sortable_MCLS, $name);

					}
				}
			}
			return $this->$name;
		}
	}

	function remove(){
		$args = func_get_args();
		if(is_array($args[0])){
			$black = $args[0];
		}elseif(count($args) > 1){
			$black = $args;
		}else{
			$black = preg_split("/([\.,\s\-])/", $args[0],-1, PREG_SPLIT_NO_EMPTY);
		}
		foreach ($black as $key => $value) {
			$k = array_search($value, $this->Element_Sortable_MCLS);
			if($k !== false){
				unset($this->Element_Sortable_MCLS[$k]);
				unset($this->$value);
			}
		}
		$this->sortable();
		return $this;

	}

	function white(){
		$args = func_get_args();
		if(is_array($args[0])){
			$white = $args[0];
		}elseif(count($args) > 1){
			$white = $args;
		}else{
			$white = preg_split("/([\.,\s\-])/", $args[0],-1, PREG_SPLIT_NO_EMPTY);
		}

		foreach ($this->Element_Sortable_MCLS as $key => $value) {
			if(!preg_grep("/^".$value."$/", $white)){
				unset($this->Element_Sortable_MCLS[$key]);
				unset($this->$value);
			}
		}
		// $this->sort($white);
		return $this;
	}
	/**
	** sort
	**/
	public function sort(){
		$this->sortable();
		$args = func_get_args();
		if(is_array($args[0])){
			$sort = $args[0];
		}elseif(count($args) > 1){
			$sort = $args;
		}else{
			$sort = preg_split("/([\.,\s\-])/", $args[0],-1, PREG_SPLIT_NO_EMPTY);
		}
		$element = array();
		foreach ($sort as $key => $value) {
			array_push($element, $value);
		}
		foreach ($this->Element_Sortable_MCLS as $key => $value) {
			$other_element = $this->Element_Sortable_MCLS[$key];
			if(array_search($other_element, $element) == -1){
				array_push($element, $value);
			}
		}
		$this->Element_Sortable_MCLS = $element;
		return $this;
	}
}
?>