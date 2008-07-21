<?php

// Ensure that we have the Ticketing_Ticket_Message class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Correspondance.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Config.php';

class Ticketing_Ticket
{
	private $groupTicketId = NULL;
	private $subject = NULL;
	private $priorityId = NULL;
	private $ownerId = NULL;
	private $contactId = NULL;
	private $statusId = NULL;
	private $accountId = NULL;
	private $categoryId = NULL;
	private $creationDatetime = NULL;
	private $modifiedDatetime = NULL;

	private $_saved = FALSE;

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
	}

	public static function createNew(Ticketing_Contact $contact, $strSubject)
	{
		// At this point, all we will know is the subject and the 
		// contact (which we might know nothing about other than email address)

		// WIP:: We can check to see if the contact is associated with just one account. 
		// If so, should we set that account on this correspondance by default?

		// Save and return a new ticket (which must have a ticket id!)
	}

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	private function init($arrProperties)
	{
		$arrProperties = $selProperties->Fetch();
		foreach($arrProperties as $name => $property)
		{
			$this->{$name} = $property;
		}
		$this->_saved = TRUE;
	}

	private static function loadForTicketId($intTicketNumber)
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

	public function getMessages()
	{
		
	}

	public function addMessage($strSubject, $strMessage, $arrAttchments=NULL)
	{
		// Create and return a message for this ticket with the details given
	}
}

?>
