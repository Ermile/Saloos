<?php
namespace lib\saloos;
/**
 * autoload saloos core lib
 */
class lib
{
	public function __call($name, $args){
		$class_name = "lib\\{$name}";
		if(!class_exists($class_name)){
			\lib\error::core("lib\\{$name}");
		}else{
			$class_name_valid = $class_name;
			return new $class_name_valid($args);
		}
	}
}