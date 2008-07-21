<?php

// Ensure that we have the Ticketing_Ticket_Message class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Correspondance.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Config.php';

class Ticketing_Ticket
{
	private $intCustomerId;

	public static function forMessage(Ticketing_Correspondance $message)
	{
		$mxdTicketNumber = $message->getTicketNumber();

		// If ticket number is set
		if ($mxdTicketNumber)
		{
			// Check that the ticket number exists
			
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

	private function __construct()
	{
		
	}

	private function loadForTicketNumber($intTicketNumber)
	{
		// WIP:: Load and return the ticket for the given ticket number
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
