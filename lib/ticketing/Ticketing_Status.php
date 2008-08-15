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
			case 'cssclass':
				return str_replace('_', '-', strtolower($this->arrProperties['Constant']));
		}
	}

	public function getStatusIds()
	{
		return "{$this->id}";
	}

	public static function getAvailableStatusesForUserAndTicket($user, $ticket=NULL)
	{
		$available = array();
		if ($user->isUser())
		{
			if ($ticket && !$ticket->isAssigned())
			{
				$available[] = self::getForId(TICKETING_STATUS_UNASSIGNED);
			}
			$available[] = self::getForId(TICKETING_STATUS_IN_PROGRESS);
			$available[] = self::getForId(TICKETING_STATUS_WITH_CUSTOMER);
			$available[] = self::getForId(TICKETING_STATUS_WITH_CARRIER);
			$available[] = self::getForId(TICKETING_STATUS_WITH_INTERNAL);
			$available[] = self::getForId(TICKETING_STATUS_COMPLETED);
		}
		if ($user->isAdminUser() && $ticket && $ticket->isSaved())
		{
			//$available[] = self::getForId(TICKETING_STATUS_UNASSIGNED);	// This status is set by the system when a ticket is first created
			//$available[] = self::getForId(TICKETING_STATUS_ASSIGNED);		// This status is set by the system when the ticket is assigned/reassigned
			$available[] = self::getForId(TICKETING_STATUS_DELETED);
		}
		return $available;
	}

	public function validForUserAndTicket($user, $ticket=NULL)
	{
		if (!$user->isUser())
		{
			return FALSE;
		}
		switch ($this->id)
		{
			case TICKETING_STATUS_IN_PROGRESS:
			case TICKETING_STATUS_WITH_CUSTOMER:
			case TICKETING_STATUS_WITH_CARRIER:
			case TICKETING_STATUS_WITH_INTERNAL:
			case TICKETING_STATUS_COMPLETED:
				return TRUE;
			case TICKETING_STATUS_UNASSIGNED:
				return ($ticket && !$ticket->isAssigned());
			case TICKETING_STATUS_DELETED:
				return ($user->isAdminUser() && $ticket && $ticket->isSaved());
			default:
				return FALSE;
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
