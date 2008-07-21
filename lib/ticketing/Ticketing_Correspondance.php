<?php

// Ensure that we have the Ticketing_Ticket class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Ticket.php';


// Ensure that we have the email notification class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'Email_Notification.php';

class Ticketing_Correspondance
{
	private $summary;
	private $details;
	private $arrAttachments;
	private $ticketId;
	private $id;
	private $contactId = NULL;
	private $sourceId = NULL;
	private $userId = NULL;
	private $deliveryStatusId = NULL;

	private $contact = NULL;

	public function __construct()
	{
		$arrArgs = func_get_args();
		// Array of details parsed from an incoming email
		if (count($arrArgs) == 1 && is_array($arrArgs[0]))
		{
			$this->createForDetails($arrArgs);
		}
		// Ticket message number - Existing message!
		else if (count($arrArgs) == 1 && is_int($arrArgs[0]))
		{
			$this->loadForCorrespondanceId($arrArgs[0]);
		}
	}

	private function createForDetails($arrDetails)
	{
		// We should look at the to address to determine which customer group this is for.

		// We should use the 'from' address to determine the contact
		$from = $arrDetails['from']['address'];
		$name = $arrDetails['from']['name'];

		$this->deliveryStatusId = $arrDetails['delivery_status'];
		$this->userId = $arrDetails['user_id'];
		$this->sourceId = $arrDetails['source_id'];
		$this->creationDatetime = $arrDetails['creation_datetime'];
		$this->deliveryDatetime = $arrDetails['delivery_datetime'];

		// If a contact does not exists for the email address, one will be created
		$this->contact = Ticketing_Contact::getForEmailAddress($from, $name);
		$this->contactId = $this->contact->id;

		$this->summary = $arrDetails['subject'];
		$this->details = $arrDetails['message'];

		// Check the subject for a ticket number
		// TODO: Q: Could this apply to more than one ticket?
		$arrMatches = array();
		if (preg_match("/\[ *#[0-9 ]+\]/", $this->summary, $arrMatches))
		{
			$this->summary = str_replace("/\[ *#([0-9 ]+)\]/", "", $arrMatches[0]);
			$this->ticketId = intval(preg_replace("/[^0-9]*/", "", $arrMatches[0]));
		}

		// Load the ticket for this correspondance (if a ticket does not exist, one will be created)
		// Note: If we have a ticket number that does not exist, it will be overwritten.
		$ticket = Ticketing_Ticket::forCorrespondance($this);

		// Need to save this correspondance to get assigned the id, 
		// which we need in order that we may create associated attchments
		$this->save();

		foreach ($arrDetails['attachments'] as $attachmentDetails)
		{
			$objAttchment = Ticketing_Attachment::create($this, $attachmentDetails['name'], $attachmentDetails['type'], $attachmentDetails['data']);
			$this->arrAttchments[$objAttchment->id] = $objAttchment;
		}
	}

	private function loadForCorrespondanceId($intMessageNumber)
	{
		// Query the DB to get the details of the message
		$this->intMessageNumber = $intMessageNumber;
	}

	public function acknowledgeReceipt()
	{
		// WIP:: Send an email to customer to acknowledge receipt
	}

	public function emailToCustomer()
	{
		// WIP:: Send an email to customer containing the details of this message
	}

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = array(
			'ticket_id' => $this->ticket_id, 
			'summary' => $this->summary, 
			'details' => $this->details, 
			'user_id' => $this->user_id, 
			'contact_id' => $this->contact_id, 
			'source_id' => $this->source_id, 
			'delivery_status_id' => $this->delivery_status_id,
			'creation_datetime' => $this->creation_datetime, 
			'delivery_datetime' => $this->delivery_datetime
		);
		// No id means that this must be a new record
		if (!$this->id)
		{
			$statement = new StatementInsert('ticketing_correspondance', $arrValues);
		}
		// This must be an update
		else
		{
			
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_correspondance', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save correspondance details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;
		return TRUE;
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
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} != $mxdValue)
			{
				$this->{$strName} = $mxdValue;
				$this->_saved = FALSE;
			}
		}
	}

	private function tidyName($name)
	{
		return strtolower(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
	}

}

?>
