<?php

class Ticketing_Category
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
				return $this->arrProperties['Name'];
			case 'description':
				return $this->arrProperties['Description'];
			case 'constant':
				return $this->arrProperties['Constant'];
			case 'cssclass':
				return str_replace('_', '-', strtolower($this->arrProperties['Constant']));
		}
	}

	public static function getAvailableCategoriesForUser($user)
	{
		$available = array();
		if ($user->isUser())
		{
			$available = self::listAll();
		}
		return $available;
	}

	public static function listAll()
	{
		static $instances;
		if (!isset($instances))
		{
			$arrNameSort = array();
			foreach ($GLOBALS['*arrConstant'][strtolower(__CLASS__)] as $id => $props)
			{
				$props['Id'] = $id;
				$arrNameSort[$props['Name']] = $props;
			}
			ksort($arrNameSort);
			$instances = array();
			foreach ($arrNameSort as $props)
			{
				$instances[$props['Id']] = new self($props);
			}
		}
		return $instances;
	}

	public static function getForId($id)
	{
		$instances = self::listAll();
		return $instances[intval($id)];
	}
}

?>
