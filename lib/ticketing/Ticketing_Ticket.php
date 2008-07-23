<?php

// Ensure that we have the Ticketing_Ticket_Message class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Correspondance.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Config.php';

class Ticketing_Ticket
{
	protected $groupTicketId = NULL;
	protected $subject = NULL;
	protected $priorityId = NULL;
	protected $ownerId = NULL;
	protected $contactId = NULL;
	protected $statusId = NULL;
	protected $customerGroupId = NULL;
	protected $accountId = NULL;
	protected $categoryId = NULL;
	protected $creationDatetime = NULL;
	protected $modifiedDatetime = NULL;

	protected $_saved = FALSE;

	public static function forCorrespondance(Ticketing_Correspondance $correspondance)
	{
		$mxdTicketId = $correspondance->ticketId;

		$ticket = NULL;

		// If ticket number is set
		if ($mxdTicketId)
		{
			// Check that the ticket number exists. If not, we need to create a new ticket
			$ticket = self::loadForTicketId($mxdTicketId);
		}
		else
		{
			$ticket = self::createNew($correspondance->getContact(), $correspondance->summary, $correspondance->getCustomerGroupEmail()->customerGroupId);
		}
	}

	public static function createNew(Ticketing_Contact $contact, $strSubject, $custGroupId)
	{
		// At this point, about all we will know is the subject, the 
		// contact (which we might know nothing about other than email address)
		// and the customer group id (which might be null)
		// Set the rest of thye properties top defaults
		$objTicket = new Ticketing_Ticket();
		$objTicket->subject = $strSubject;
		$objTicket->priorityId = TICKETING_PRIORITY_MEDIUM;
		$objTicket->contactId = $contact->id;
		$objTicket->statusId = TICKETING_STATUS_UNASSIGNED;
		$objTicket->customerGroupId = $custGroupId;
		$objTicket->categoryId = TICKETING_CATEGORY_UNCATEGORIZED;
		$objTicket->creationDatetime = $objTicket->modifiedDatetime = date('Y-m-d H-i-s');

		// We can check to see if the contact is associated with just one account. 
		// If so, should we set that account on this correspondance by default?
		$accountIds = $contact->getAccountIds();
		if (count($accountIds) === 1)
		{
			$objTicket->accountId = $accountIds[0];
		}

		// Save and return a new ticket (which must have a ticket id!)
		$objTicket->save();
		return $objTicket;
	}

	protected function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	protected function init($arrProperties)
	{
		foreach($arrProperties as $name => $property)
		{
			$this->{$name} = $property;
		}
		$this->_saved = TRUE;
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach ($arrColumns as $strColumn)
		{
			if ($strColumn == 'id') 
			{
				continue;
			}
			$arrValues->{$strColumn} = $this->{$strColumn};
		}
		return $arrColumns;
	}

	protected static function getColumns()
	{
		return array(
			'id',
			'group_ticket_id',
			'subject',
			'priority_id',
			'owner_id',
			'contact_id',
			'status_id',
			'customer_group_id',
			'account_id',
			'category_id',
			'creation_datetime',
			'modified_datetime',
		);
	}

	protected function getTableName()
	{
		return 'ticketing_ticket';
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
			$statement = new StatementInsert($this->getTableName(), $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById($this->getTableName(), $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save ' . (str_replace('_', ' ', $this->getTableName())) . ' details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
			// Don't overwrite a group ticket id if we already have one
			// (not possible for this class, but maybe true for subclasses)
			if ($this->group_ticket_id === NULL)
			{
				$this->group_ticket_id = $outcome;
			}
			return $this->save();
		}
		else
		{
			// Each time we update the record, we need to copy the details to the history table
			$this->recordHistoricCopy();
		}
		$this->_saved = TRUE;
		return TRUE;
	}

	protected function recordHistoricCopy()
	{
		Ticketing_Ticket_History::createForTicket($this);
	}

	protected static function loadForTicketId($intTicketNumber)
	{
		// Load and return the ticket for the given ticket number
		$selProperties = new StatementSelect('ticketing_ticket', array(), "id = <Id>");
		$arrWhere = array('Id' => $intTicketNumber);
		if (($outcome = $selProperties->Execute($arrWhere)) === FALSE)
		{
			throw new Exception('Failed to check for existance of ticket: ' . $selProperties->Error());
		}
		if (!$outcome)
		{
			// No such ticket exists
			return NULL;
		}
		// Instantiate the ticket with the loaded details
		return new Ticketing_Ticket($selProperties->Fetch());
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			$this->getColumns(), 
			$strWhere);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing customer group email: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Ticket($selMatches->Fetch());
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}

	public static function getForId($id)
	{
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	public function getCorrespondances()
	{
		return Ticketing_Correspondance::getForTicket($this);
	}

	public function addCorrespondance($strSubject, $strMessage, $arrAttchments=NULL, $intSource=TICKETING_CORRESPONDANCE_SOURCE_PHONE, $bolInbound=FALSE, $bolAlreadyCommunicated=TRUE, $defaultGroupEmail=NULL, $contactOrUserId=NULL)
	{
		// WIP: Create and return a correspondance for this ticket with the details given
		
		$now = date('Y-m-d H:i:s');
		$arrDetails = array(
			'source_id' 	=>	$intSource, 	// The source id (TICKETING_CORRESPONDANCE_SOURCE_xxx)
			'summary'		=>	$strSubject, 	// String summary (single line) (eg: Email subject) of the correspondance
			'details'		=>	$strMessage, 	// String description of the email, much more detailed than the summary
			'ticket_id'		=>	$this->id, 		//If not specified, a new ticket will be created for the correspondance
			'customer_group_id' => $this->customerGroupId, //(id of record in ticketing_customer_group_config table) to use default address for
			'creation_datetime'	=> $now, 		// Date in 'YYYY-mm-dd HH:ii:ss' format (Defaults to current date/time)
		);

		if ($defaultGroupEmail)
		{
			$arrDetails['default_email_id'] = $defaultGroupEmail; // (id of record in ticketing_customer_group_email table) of address to send email from
		}

		if ($bolAlreadyCommunicated)
		{
			$arrDetails['delivery_status'] = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT; // Delivery status (Default is TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT)
			$arrDetails['delivery_datetime'] = $now; // Date in 'YYYY-mm-dd HH:ii:ss' format (Defaults to null (not sent))
		}
		else
		{
			$arrDetails['delivery_status'] = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT;
		}

		if ($bolInbound)
		{
			$arrDetails['contact_id'] = $contactOrUserId; // Integer id of ticketing system contact creating the record (NULL if created by user)
		}
		else
		{
			$arrDetails['user_id'] = $contactOrUserId; // Integer id of ticketing system user creating the record (NULL if created by customer)
		}

		if (($correspondance=TicketingTicket::createForDetails($arrDetails)) === NULL)
		{
			throw new Exception('Failed to create the correspondance.');
		}
		return $correspondance;
	}
}

?>
