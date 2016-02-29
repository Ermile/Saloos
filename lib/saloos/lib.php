<?php
namespace lib\saloos;
/**
 * autoload saloos core lib
 */
class lib
{
	public $prefix;
	public function __construct($args = null){
		$this->prefix = $args ? "\\". trim($args[0], "\\"). "\\" : "\\";
	}
	public function __call($name, $args){
		$path = array("ilib", "lib");
		foreach ($path as $key => $value) {
			$class_name = "{$value}{$this->prefix}{$name}";
			if(class_exists($class_name)){
				return new $class_name(...$args);
			}
		}
		\lib\error::core("lib\\{$name}");
	}
}