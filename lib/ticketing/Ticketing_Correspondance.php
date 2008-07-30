<?php

// Ensure that we have the Ticketing_Ticket class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Ticket.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Contact.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Attachment.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Customer_Group_Email.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Customer_Group_Config.php';


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
	private $customerGroupEmailId = NULL;
	private $customerGroupEmail = NULL;

	private $contact = NULL;

	private static $custGroupDomains = NULL;

	private function __construct()
	{
		$arrArgs = func_get_args();
		// Ticket message number - Existing message!
		if (count($arrArgs) == 1 && is_int($arrArgs[0]))
		{
			$this->loadForCorrespondanceId($arrArgs[0]);
		}
	}

	/**
	 * Create a new correspondance for the given details
	 * 
	 * @param array $arrDetails An array containing the following:
	 * 
	 * 			source_id 	=>	The source id (TICKETING_CORRESPONDANCE_SOURCE_xxx)
	 * 
	 * 			summary		=>	String summary (single line) (eg: Email subject) of the correspondance
	 * 			details		=>	String description of the email, much more detailed than the summary
	 * 
	 * 		Either:
	 * 			user_id		=>	FOR OUTBOUND EMAILS Integer id of ticketing system user creating the record (NULL if created by customer)
	 * 		-or-
	 * 			contact_id	=>	Integer id of ticketing system contact creating the record (NULL if created by user)
	 *
	 *		Optionally:
	 *
	 *			ticket_id			=> If not specified, a new ticket will be created for the correspondance
	 *
	 * 			creation_datetime	=> Date in 'YYYY-mm-dd HH:ii:ss' format (Defaults to current date/time)
	 * 
	 * 			delivery_status		=> Delivery status (Default is TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT)
	 * 			delivery_datetime	=> Date in 'YYYY-mm-dd HH:ii:ss' format (Defaults to null (not sent))
	 *
	 * 		Either:
	 *			default_email_id	=> FOR OUTBOUND EMAILS (id of record in ticketing_customer_group_email table) of address to send email from
	 *		-or-
	 *			customer_group_id	=> FOR OUTBOUND EMAILS (id of record in ticketing_customer_group_config table) to use default address for
	 */
	public static function createForDetails($arrDetails)
	{
		// Is this inbound or outbound?
		$bolOutbound = array_key_exists('user_id', $arrDetails) && array_key_exists('user_id', $arrDetails);

		// We should look at the 'to' addresses to determine which customer group this is for.
		// If the 'to' addresses are not for a customer group, we should check the 'cc' addresses
		// If the 'to' and 'cc' addresses are not for a customer broup, we should check the 'bcc' addresses
		$addresses = array();
		if (array_key_exists('to', $arrDetails)) 
		{
			$addresses[] = $arrDetails['to'];
		}
		if (array_key_exists('cc', $arrDetails)) 
		{
			$addresses[] = $arrDetails['cc'];
		}
		if (array_key_exists('bcc', $arrDetails)) 
		{
			$addresses[] = $arrDetails['bcc'];
		}

		$emailIdField = 'id';
		if (!array_key_exists('default_email_id', $arrDetails) || !$arrDetails['default_email_id'])
		{
			if ($bolOutbound)
			{
				if (!array_key_exists('customer_group_id', $arrDetails))
				{
					// TODO:: Don't give up so easy! If a ticket has been specified, check for a previous correspondance and use the address from that.
					throw new Exception('Unable to create correspondance as email address could not be determined for a sender.');
				}
				$custGroupEmail = Ticketing_Customer_Group_Config::getForId($arrDetails['customer_group_id']);
				$emailIdField = 'default_email_id';
			}
			else
			{
				$custGroupEmail = self::getCustomerGroupEmailForEmailAddresses($addresses);
			}
		}
		else
		{
			$custGroupEmail = Ticketing_Customer_Group_Email::getForId($arrDetails['default_email_id']);
		}

		if ($custGroupEmail === NULL)
		{
			return NULL;
		}

		// We should use the 'from' address to determine the contact
		$from = $arrDetails['from']['address'];
		$name = $arrDetails['from']['name'];

		$objCorrespondance = new Ticketing_Correspondance();

		$objCorrespondance->customerGroupEmailId = $custGroupEmail->{$emailIdField};

		if (!array_key_exists('delivery_status', $arrDetails))
		{
			$objCorrespondance->deliveryStatusId = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT;
		}
		else
		{
			$objCorrespondance->deliveryStatusId = $arrDetails['delivery_status'];
		}

		$objCorrespondance->userId = $arrDetails['user_id'];
		$objCorrespondance->sourceId = $arrDetails['source_id'];
		
		if (!array_key_exists('creation_datetime', $arrDetails) || !$arrDetails['creation_datetime'])
		{
			$objCorrespondance->creationDatetime = date('Y-m-d H:i:s');
		}
		else
		{
			$objCorrespondance->creationDatetime = $arrDetails['creation_datetime'];
		}

		if (!array_key_exists('delivery_datetime', $arrDetails) || !$arrDetails['delivery_datetime'])
		{
			$objCorrespondance->deliveryDatetime = NULL;
		}
		else
		{
			if ($objCorrespondance->deliveryStatusId == TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT)
			{
				$objCorrespondance->deliveryDatetime = $arrDetails['delivery_datetime'];
			}
			else
			{
				$objCorrespondance->deliveryDatetime = NULL;
			}
		}

		// Set either the userId (for outbound) or contactId (for inbound)
		if ($bolOutbound)
		{
			$objCorrespondance->userId = $arrDetails['user_id'];
			$objCorrespondance->contact = NULL;
			$objCorrespondance->contactId = NULL;
		}
		else
		{
			// If a contact does not exists for the email address, one will be created
			$objCorrespondance->userId = NULL;
			$objCorrespondance->contact = Ticketing_Contact::getForEmailAddress($from, $name);
			$objCorrespondance->contactId = $objCorrespondance->contact->id;
		}

		$objCorrespondance->summary = $arrDetails['subject'];
		$objCorrespondance->details = $arrDetails['message'];

		// Check the subject for a ticket number
		// TODO: Q: Could this apply to more than one ticket?
		$arrMatches = array();
		if (preg_match("/T[0-9 ]+Z/i", $objCorrespondance->summary, $arrMatches))
		{
			$objCorrespondance->summary = str_replace("/\T([0-9 ]+)Z/i", "", $arrMatches[0]);
			$objCorrespondance->ticketId = intval(preg_replace("/[^0-9]*/", "", $arrMatches[0]));
		}

		if (array_key_exists('ticket_id', $arrDetails) && $arrDetails['ticket_id'])
		{
			$objCorrespondance->ticketId = $arrDetails['ticket_id'];
		}

		// Load the ticket for this correspondance (if a ticket does not exist, one will be created)
		// Note: If this record has a ticket number that does not exist, a new ticket will be created.
		$ticket = Ticketing_Ticket::forCorrespondance($objCorrespondance);

		// Update this instances ticket id, as a new ticket may have been created if an existing ticket was not found
		$objCorrespondance->ticketId = $ticket->id;

		// Need to save this correspondance to get assigned the id, 
		// which we need in order that we may create associated attchments
		$objCorrespondance->save();

		foreach ($arrDetails['attachments'] as $attachmentDetails)
		{
			$objAttchment = Ticketing_Attachment::create($objCorrespondance, $attachmentDetails['name'], $attachmentDetails['type'], $attachmentDetails['data']);
			$objCorrespondance->arrAttchments[$objAttchment->id] = $objAttchment;
		}

		return $objCorrespondance;
	}

	public function getTicket()
	{
		return Ticketing_Ticket::forCorrespondance($this);
	}

	public function getContact()
	{
		if ($this->contact == NULL)
		{
			$this->contact = Ticketing_Contact::getForId($this->contactId);
		}
		return $this->contact;
	}

	public function getCustomerGroupEmail()
	{
		if ($this->customerGroupEmail !== NULL)
		{
			return $this->customerGroupEmail;
		}
		return Ticketing_Customer_Group_Email::getForId($this->customerGroupEmailId);
	}

	private static function getCustomerGroupEmailForEmailAddresses()
	{
		$args = func_get_args();
		$custGroupEmail = NULL;
		foreach($args as $arg)
		{
			if (is_array($arg))
			{
				$custGroupEmail = call_user_func_array(array('Ticketing_Correspondance', 'getCustomerGroupEmailForEmailAddresses'), $arg);
			}
			else if (is_string($arg) && ($offset = strrpos($arg, '@')) !== FALSE)
			{
				$arg = str_replace(array('<', '>', "'", '"'), '', $arg);
				$custGroupEmail = Ticketing_Customer_Group_Email::getForEmailAddress($arg);
			}
			if ($custGroupEmail !== NULL && $custGroupEmail !== FALSE)
			{
				break;
			}
		}
		return $custGroupEmail;
	}

	private function loadForCorrespondanceId($intMessageNumber)
	{
		// Query the DB to get the details of the message
		$this->intMessageNumber = $intMessageNumber;
	}

	protected static function getColumns()
	{
		return array(
			'id', 
			'ticket_id', 
			'summary', 
			'details', 
			'user_id', 
			'contact_id', 
			'source_id', 
			'delivery_status_id',
			'creation_datetime', 
			'customer_group_email_id', 
			'delivery_datetime',
		);
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
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	}

	public function getForTicket(Ticketing_Ticket $ticket)
	{
		$arrColumns = $this->getColumns();

		$selMatches = new StatementSelect('ticketing_correspondance', $arrColumns, 'ticket_id = <TicketId>');
		$arrWhere = array('TicketId' => $ticket->id);

		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing customer group email: " . $selMatches->Error());
		}
		$arrInstances = array();
		if ($outcome)
		{
			while($details = $selMatches->Fetch())
			{
				$arrInstances[] = new Ticketing_Correspondance($selMatches->Fetch());
			}
		}
		return $arrInstances;
	}

	public function acknowledgeReceipt()
	{
		// Load up the customer group email to see if we should reply to it, and to get the email address to email from
		$custGroupEmail = $this->getCustomerGroupEmail();
		if (!$custGroupEmail->autoReply())
		{
			return;
		}

		// Check to see if this contact gets auto reply emails
		if (!$this->getContact()->autoReply())
		{
			return;
		}

		// Do a sanity check at this point!
		// If the 'to' address is one of the ticketing system in-boxes, do not send mail to it!
		if (Ticketing_Customer_Group_Email::getForEmailAddress($this->getContact()->email))
		{
			$contact = $this->getContact();
			$contact->autoReply = ACTIVE_STATUS_INACTIVE;
			$contact->save();
			return;
		}

		// Load up the customer group ticketing configuration to see if this customer group acknowledeges receipts
		// and to get the message for the email
		$customerGroupId = $this->getTicket()->customerGroupId;
		$custGroupConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($customerGroupId);
		if (!$custGroupConfig->acknowledgeEmailReceipts())
		{
			return;
		}

		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'Email_Notification.php';

		$email = new Email_Notification(EMAIL_NOTIFICATION_TICKETING_SYSTEM, $customerGroupId);
		$email->to = $this->getContact()->email;
		$email->from = $custGroupEmail->email;
		$email->subject = $this->summary . " [T" . $this->ticketId . "Z]";
		$email->text = str_replace('[TICKET_ID]', 'T'.$this->ticketId.'Z', $custGroupConfig->emailReceiptAcknowledgement);
		$email->send();
	}

	// TODO: Change this to allow emails to be sent to multiple contacts (and Cc'd)
	public function emailToCustomer()
	{
		$ticket = $this->getTicket();
		$contact = $ticket->getContact();

		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'Email_Notification.php';

		$email = new Email_Notification(EMAIL_NOTIFICATION_TICKETING_SYSTEM);

		$email->addTo($contact->email, $contact->getName()); // The contact from the ticket (contact_id)
		
		// TODO: Check for previous outgoing correspondances and send to same address 
		$email->setFrom($this->getCustomerGroupEmail()->email, $this->getCustomerGroupEmail()->name);

		$email->subject = $this->summary . " [T" . $this->ticketId . "Z]";
		$email->text = $this->details;

		$email->send();

		$this->deliveryStatus = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT;
		$this->_saved = FALSE;
		$this->save();
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
				if ($strName == 'contactId')
				{
					$this->contact = NULL;
				}
				else if ($strName == 'contact')
				{
					$this->contactId = $mxdValue->id;
				}

				if ($strName == 'customerGroupEmailId')
				{
					$this->customerGroupEmail = NULL;
				}
				else if ($strName == 'customerGroupEmail')
				{
					$this->customerGroupEmailId = $mxdValue->id;
				}

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
