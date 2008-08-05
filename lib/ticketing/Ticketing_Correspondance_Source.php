<?php

class Ticketing_Correspondance_Source 
{
	protected $arrProperties = array();

	protected function __construct($arrProps)
	{
		$this->arrProperties = $arrProps;
	}

	public function __get($property)
	{
		switch (strtolower($property))
		{
			case 'id':
			case 'value':
				return $this->arrProperties['Id'];
			case 'name':
			case 'description':
				return $this->arrProperties['Description'];
			case 'constant':
				return $this->arrProperties['Constant'];
		}
	}

	public static function listAll()
	{
		static $instances;
		if (!isset($instances))
		{
			$instances = array();
			foreach ($GLOBALS['*arrConstant'][strtolower(__CLASS__)] as $id => $props)
			{
				$props['Id'] = $id;
				$instances[$id] = new self($props);
			}
		}
		return $instances;
	}

	public static function getForId($id)
	{
		$instances = self::listAll();
		return $instances[$id];
	}
}

?>
