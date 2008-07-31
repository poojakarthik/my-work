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
