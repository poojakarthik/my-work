<?php

class Ticketing_Ticket_History extends Ticketing_Ticket
{
	protected $ticketId = NULL;

	protected function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	protected function createForTicket($objTicket)
	{
		$arrPropertyNames = $objTicket->getValuesToSave();
		$objHistory = new Ticketing_Ticket_History();
		foreach($arrPropertyNames as $strName => $strValue)
		{
			$objHistory->{$strName} = $strValue;
		}
		$objHistory->ticketId = $objTicket->id;
		$objHistory->save();
		return $objHistory;
	}

	protected function recordHistoricCopy()
	{
		// Do nothing! This is the historic copy!
	}
	
	// Note that the objects returned by this will be of class Ticketing_Ticket_History 
	public static function getForTicket($ticket, $strSort='')
	{
		return self::getFor('ticket_id = <TicketId>', array('TicketId'=>$ticket->id), TRUE, 'creation_datetime DESC, id DESC');
	}
	
	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		if (!$strSort || empty($strSort))
		{
			$strSort = 'creation_datetime DESC, id DESC';
		}
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			self::getColumns(), 
			$strWhere,
			$strSort,
			$strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load Ticketing_Ticket_History objects: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Ticket_History($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}
	
	public static function getColumns()
	{
		return array_unique(array_merge(parent::getColumns(), array('ticket_id')));
	}
	

	protected function getValuesToSave()
	{
		$arrValuesToSave = parent::getValuesToSave();
		$arrValuesToSave['ticket_id'] = $this->ticketId;
		return $arrValuesToSave;
	}

	protected function getTableName()
	{
		return 'ticketing_ticket_history';
	}

}

?>
