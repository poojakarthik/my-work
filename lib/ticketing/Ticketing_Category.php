<?php

class Ticketing_Category
{
	const TICKETING_CATEGORY_UNCATEGORIZED = 0;
	
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
				return $this->arrProperties['id'];
			case 'name':
				return $this->arrProperties['name'];
			case 'description':
				return $this->arrProperties['description'];
			case 'cssclass':
				return str_replace('_', '-', strtolower($this->arrProperties['css_name']));
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
			$db = Data_Source::get();
			
			$strSQL = "SELECT id, name, description, css_name FROM ticketing_category ORDER BY name ASC";
			
			if (MDB2::isError(($result = $db->query($strSQL, array("integer", "text", "text", "text")))))
			{
				throw new Exception("Unable to retrieve the list of available Ticketing Categories: " . $result->getMessage());
			}
			
			$arrNameSort = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
			$instances = array();
			foreach ($arrNameSort as $props)
			{
				$instances[$props['id']] = new self($props);
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
