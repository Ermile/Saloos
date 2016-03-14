<?php
namespace lib;

trait mvm
{
	public $Methods = array();
	public function __call($name, $args)
	{
		$black = array("_construct", "corridor", "config");

		if(method_exists($this, '_call_corridor') && method_exists($this, '_call') && $value = $this->_call_corridor($name, $args))
		{
			return $this->_call($name, $args, $value);
		}

		elseif(isset($this->Methods[$name]))
		{
			return call_user_func_array($this->Methods[$name], $args);
		}

		elseif(method_exists($this->controller, $name) && !preg_grep("/^{$name}$/", $black))
		{
			return call_user_func_array(array($this->controller, $name), $args);
		}

		\lib\error::internal(get_called_class()."()->$name()");
	}
}
?>