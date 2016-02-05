<?php
namespace lib\form;
class maker
{
	public $attr = array();
	public $child = array();
	public $label = '';
	public $validate = null;
	public $elname = null;
	/**
	**
	**/
	function __construct($type = 'text'){
		$this->elname = $type;
		$this->attr('type', $type);
	}

	function child($index = false){
		if($index !== false){
			return $this->child[$index];
		}else{
			$child = array_push($this->child, new $this);
			return $this->child[$child-1];
		}
	}

	function validate(){
		if(!$this->validate){
			$validate = new \lib\validator\maker();
			$this->validate = $validate;
			return $validate;
		}else{
			return $this->validate;
		}
	}

	function filter(){
		if(!$this->filter){
			$filter = new filterMaker(func_get_args());
			$this->filter = $filter;
			return $filter;
		}else{
			if(count(func_get_args())> 0){
				call_user_func_array(array($this->filter, 'extend'), func_get_args());
			}
			return $this->filter;
		}
	}

	function compile($autoSet = true)
	{
		$array = array();
		$array['attr'] = $this->attr;
		// var_dump($array['attr']);
		$array['label'] = !empty($this->label) ? T_($this->label) : $this->label;
		$array['child'] = array();
		$array['validate'] = $this->validate;
		$child = $this->child;
		foreach ($child as $key => $value)
		{
			// if(isset($value->attr['type']))
			// {
			// 	unset($value->attr['type']);
			// 	$is_child = true;
			// }

			array_push($array['child'], $value->compile());
		}
		// $blackChild = array('type');
		foreach ($array['child'] as $key => $value) {
			unset($array['child'][$key]['child']);
			unset($array['child'][$key]['validate']);
			// foreach ($blackChild as $K => $V) {
			// 	if(isset($array['child'][$key]['attr'][$V])){
			// 		unset($array['child'][$key]['attr'][$V]);
			// 	}
			// }
		}
		if($autoSet === true){
			// if user don't set name of element give it from *->make('name')* and set it as name
			if(!isset($array['attr']['name']) && $this->elname){
				$array['attr']['name'] = $this->elname;
			}
			// if user don't set pl of element give it from *->label('Hi')* and set it as placeholder
			if(!isset($array['attr']['placeholder']) && $array['label'] && isset($array['attr']['type']) && $array['attr']['type']!='submit'){
				$array['attr']['placeholder'] = $array['label'];
				if(!empty($array['attr']['placeholder'])){
					$array['attr']['placeholder'] = gettext($array['attr']['placeholder']);
				}
			}
			// if user don't set id of element give it from *->name('username')* and set it as id
			if(!isset($array['attr']['id']) && isset($array['attr']['name'])){
				$array['attr']['id'] = $array['attr']['name'];
			}
		}
		if(count($array['child']) < 1){
			unset($array['child']);
		}
		return $array;
	}
	/***/
	function attr($name, $value = ''){
		$this->attr[$name] = $value;
		return $this;
	}

	function classname($class){
		return $this->attr('class',$class);
	}
	function pl($placeholder=null){
		return $this->attr('placeholder',$placeholder);
	}
	function label($label=null){
		$this->label = $label;
		return $this;
	}

	function addClass($class){
		if(!isset($this->attr['class'])){
			return $this->classname($class);
		}
		$aClass = preg_split("/ /", $this->attr['class']);
		array_push($aClass, $class);
		return $this->classname(join(" ", $aClass));
	}

	function removeClass($class){
		if(!isset($this->attr['class'])){
			return $this;
		}
		$aClass = preg_split("/ /", $this->attr['class'],-1, PREG_SPLIT_NO_EMPTY);
		$index = array_search($class, $aClass);
		if($index !== false){
			unset($aClass[$index]);
		}
		return $this->classname(join(" ", $aClass));
	}

	function __call($name, $value){
		$name = preg_replace("/^([a-zA-Z0-9]+)_([a-zA-Z0-9]+)$/", "$1-$2", $name);
		$name = preg_replace("/^([a-zA-Z0-9]+)__([a-zA-Z0-9]+)$/", "$1_$2", $name);
		$this->attr[$name] = (isset($value[0]))? $value[0] : "";
		return $this;
	}

}
?>