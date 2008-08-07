<?php

class Ticketing_Attachment_Blacklist_Status
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

	public function isBlacklisted()
	{
		return $this->id === TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK;
	}

	public function isGreylisted()
	{
		return $this->id === TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY;
	}

	public function isWhitelisted()
	{
		return $this->id === TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE;
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
