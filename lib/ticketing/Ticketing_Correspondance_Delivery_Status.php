<?php

class Ticketing_Correspondance_Delivery_Status 
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

	public static function getAvailableStatusesForUser($user=NULL)
	{
		if (!$user)
		{
			$user = Ticketing_User::getCurrentUser();
		}
		$available = array();
		if ($user->isUser())
		{
			$available[] = self::getForId(TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED);
			$available[] = self::getForId(TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT);
			$available[] = self::getForId(TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT);
		}
		if ($user->isAdminUser())
		{
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
