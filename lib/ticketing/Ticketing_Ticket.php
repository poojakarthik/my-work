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

	public static function forCorrespondance(Ticketing_Correspondance $correspondance, $custGroupId)
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
			$ticket = self::createNew($correspondance->getContact(), $correspondance->summary, $custGroupId);
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

		// WIP:: We can check to see if the contact is associated with just one account. 
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
		$arrProperties = $selProperties->Fetch();
		foreach($arrProperties as $name => $property)
		{
			$this->{$name} = $property;
		}
		$this->_saved = TRUE;
	}

	protected function getValuesToSave()
	{
		return array(
			'group_ticket_id' => $this->group_ticket_id, 
			'subject' => $this->subject, 
			'priority_id' => $this->priority_id, 
			'owner_id' => $this->owner_id, 
			'contact_id' => $this->contact_id, 
			'status_id' => $this->status_id, 
			'customer_group_id' => $this->customer_group_id, 
			'account_id' => $this->account_id, 
			'category_id' => $this->category_id, 
			'creation_datetime' => $this->creation_datetime, 
			'modified_datetime' => $this->modified_datetime
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

	public function getCorrespondances()
	{
		// WIP: Implement this
	}

	public function addCorrespondance($strSubject, $strMessage, $arrAttchments=NULL)
	{
		// WIP: Create and return a correspondance for this ticket with the details given
	}
}

?>
