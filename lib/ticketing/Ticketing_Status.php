<?php

class Ticketing_Status 
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
		}
	}

	public static function getAvailableStatusesForUser($user)
	{
		$available = array();
		if ($user->isUser())
		{
			$available[] = self::getForId(TICKETING_STATUS_IN_PROGRESS);
			$available[] = self::getForId(TICKETING_STATUS_WITH_CUSTOMER);
			$available[] = self::getForId(TICKETING_STATUS_WITH_CARRIER);
			$available[] = self::getForId(TICKETING_STATUS_COMPLETED);
		}
		if ($user->isAdminUser())
		{
			//$available[] = self::getForId(TICKETING_STATUS_UNASSIGNED);	// This status is set by the system when a ticket is first created
			//$available[] = self::getForId(TICKETING_STATUS_ASSIGNED);		// This status is set by the system when the ticket is assigned/reassigned
			$available[] = self::getForId(TICKETING_STATUS_DELETED);
		}
		return $available;
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
