	<?php

class Ticketing_Ticket_Service
{
	private $id = NULL;
	private $ticketId = NULL;
	private $serviceId = NULL;

	private $_saved = FALSE;
	private $_loadedServiceDetails = FALSE;
	private $_arrServiceDetails = NULL;

	protected static $cache = array();

	private function __construct($arrProperties=NULL, $bolPropertiesIncludeServiceDetails=FALSE)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
			$this->_loadedServiceDetails = $bolPropertiesIncludeServiceDetails;
		}
	}

	public static function listAll()
	{
		return self::getFor(array(), array(), TRUE);
	}

	public function getFNN()
	{
		$this->loadServiceDetails();
		if ($this->_arrServiceDetails === NULL)
		{
			return NULL;
		}
		return $this->_arrServiceDetails['FNN'];
	}

	private function loadServiceDetails()
	{
		if (!$this->_loadedServiceDetails)
		{
			$this->_loadedServiceDetails = TRUE;
			$arrWhere = array("Id" => $this->serviceId);
			$arrColumns = array(
				'FNN',
			);
			$selService = new StatementSelect(
				"Service", 
				$arrColumns, 
				$arrWhere);
			if (($outcome = $selService->Execute($arrWhere)) === FALSE)
			{
				throw new Exception("Failed to check for existing service: " . $selService->Error());
			}
			if (!$outcome)
			{
				throw new Exception("No service record exists for ticketing ticket service " . $this->id);
			}
			$this->_arrServiceDetails  = $selService->Fetch();
		}
	}

	public static function listForTicket(Ticketing_Ticket $objTicket)
	{
		return Ticketing_Ticket_Service::getFor('ticket_id = <TICKET_ID>', array('TICKET_ID'=>$objTicket->id), TRUE);
	}

	public static function createForTicket(Ticketing_Ticket $ticket, $intServiceId)
	{
		$ticketService = new Ticketing_Ticket_Service();
		$ticketService->ticketId = $ticket->id;
		$ticketService->serviceId = $intServiceId;
		$ticketService->_saved = FALSE;
		$ticketService->save();
		return $ticketService;
	}

	public function delete()
	{
		$delService = new Query();
		$strSQL = "DELETE FROM " . strtolower(__CLASS__) . " WHERE id = " . $this->id;
		if (($option = $delService->Execute($strSQL)) === FALSE)
		{
			throw new Exception('Failed to disassociate service ' . $this->id . ' from ticket ' . $this->ticketId . ': ' . $delService->Error());
		}
		$this->id = NULL;
		$this->_saved = FALSE;
	}

	private static function getFor($where, $arrWhere, $bolAsArray=FALSE)
	{
		$selUsers = new StatementSelect(
			"ticketing_ticket_service", 
			self::getColumns(), 
			$where);
		if (($outcome = $selUsers->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing ticketing ticket service: " . $selUsers->Error());
		}
		if (!$outcome && !$bolAsArray)
		{
			return NULL;
		}

		$records = array();
		while ($props = $selUsers->Fetch())
		{
			if (!array_key_exists($props['id'], self::$cache))
			{
				self::$cache[$props['id']] = new Ticketing_Ticket_Service($props);
			}
			$records[] = self::$cache[$props['id']];
			if (!$bolAsArray)
			{
				return $records[0];
			}
		}
		return $records;
	}

	public static function getForId($id)
	{
		if (array_key_exists($id, self::$cache))
		{
			return self::$cache[$id];
		}
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	protected static function getColumns()
	{
		return array(
			'id',
			'service_id',
			'ticket_id',
		);
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach($arrColumns as $strColumn)
		{
			if ($strColumn == 'id') 
			{
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	} 

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = $this->getValuesToSave();

		// No id means that this must be a new record
		if (!$this->id)
		{
			$statement = new StatementInsert('ticketing_ticket_service', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_ticket_service', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save ticketing ticket service details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;
		return TRUE;
	}

	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{$name} = $value;
		}
		$this->_saved = TRUE;
		
	}

	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}

	public function __set($strName, $mxdValue)
	{
		if ($strName[0] === '_') return; // It is read only!
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} !== $mxdValue)
			{
				$this->{$strName} = $mxdValue;
				$this->_saved = FALSE;
			}
		}
	}

	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
}

?>
