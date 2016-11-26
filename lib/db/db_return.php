<?
namespace lib\db;

class db_return
{
	public $return;

	public function is_ok()
	{
		return get_ok() === true;
	}

	public function __call($_name, $_args)
	{
		if(preg_match("/^set_(.*)$/", $_name, $peroperty))
		{
			if(count($_args) > 1)
			{
				if(!isset($this->return[$peroperty[1]]))
				{
					$this->return[$peroperty[1]] = array();
				}
				elseif(!is_array($this->return[$peroperty[1]]))
				{
					$this->return[$peroperty[1]] = [$this->return[$peroperty[1]]];
				}
				$this->return[$peroperty[1]][$_args[0]] = $_args[1];
			}
			else
			{
				$this->return[$peroperty[1]] = $_args[0];
			}
			return $this;
		}
		elseif(preg_match("/^get_(.*)$/", $_name, $peroperty))
		{
			if(!isset($this->return[$peroperty[1]]))
			{
				return null;
			}
			if(count($_args) > 0)
			{
				if(!is_array($this->return[$peroperty[1]]))
				{
					return null;
				}
				return $this->return[$peroperty[1]][$_args[0]];
			}
			else
			{
				return $this->return[$peroperty[1]];
			}
		}
	}
}
?>