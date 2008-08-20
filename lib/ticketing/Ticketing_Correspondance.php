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
	private $deliveryDatetime = NULL;
	private $creationDatetime = NULL;

	private $customerGroupEmail = NULL;
	private $contact = NULL;
	private $user = NULL;

	private static $custGroupDomains = NULL;

	private function __construct()
	{
		$arrArgs = func_get_args();
		// Ticket message number - Existing message!
		if (func_num_args() == 1 && is_int($arrArgs[0]))
		{
			$this->loadForCorrespondenceId($arrArgs[0]);
		}
		else if (func_num_args() >= 1 && is_array($arrArgs[0]))
		{
			$this->init($arrArgs[0]);
		}
	}

	public function getAttachments()
	{
		return Ticketing_Attachment::listForCorrespondence($this);
	}

	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{$name} = $value;
		}
		$this->_saved = TRUE;
		
	}

	public static function createBlank()
	{
		
		return new self();
	}

	/**
	 * Create a new correspondence for the given details
	 * 
	 * @param array $arrDetails An array containing the following:
	 * 
	 * 			source_id 	=>	The source id (TICKETING_CORRESPONDANCE_SOURCE_xxx)
	 * 
	 * 			summary		=>	String summary (single line) (eg: Email subject) of the correspondence
	 * 			details		=>	String description of the email, much more detailed than the summary
	 * 
	 * 		Either:
	 * 			user_id		=>	FOR OUTBOUND EMAILS Integer id of ticketing system user creating the record (NULL if created by customer)
	 * 		-or-
	 * 			contact_id	=>	Integer id of ticketing system contact creating the record (NULL if created by user)
	 *
	 *		Optionally:
	 *
	 *			ticket_id			=> If not specified, a new ticket will be created for the correspondence
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
					// TODO:: Don't give up so easy! If a ticket has been specified, check for a previous correspondence and use the address from that.
					throw new Exception('Unable to create correspondence as email address could not be determined for a sender.');
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

		$objCorrespondence = new Ticketing_Correspondance();

		$objCorrespondence->customerGroupEmailId = $custGroupEmail->{$emailIdField};

		if (!array_key_exists('delivery_status', $arrDetails))
		{
			$objCorrespondence->deliveryStatusId = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT;
		}
		else
		{
			$objCorrespondence->deliveryStatusId = $arrDetails['delivery_status'];
		}

		$objCorrespondence->userId = $arrDetails['user_id'];
		$objCorrespondence->sourceId = $arrDetails['source_id'];
		
		if (!array_key_exists('creation_datetime', $arrDetails) || !$arrDetails['creation_datetime'])
		{
			$objCorrespondence->creationDatetime = date('Y-m-d H:i:s');
		}
		else
		{
			$objCorrespondence->creationDatetime = $arrDetails['creation_datetime'];
		}

		if (!array_key_exists('delivery_datetime', $arrDetails) || !$arrDetails['delivery_datetime'])
		{
			$objCorrespondence->deliveryDatetime = NULL;
		}
		else
		{
			if ($objCorrespondence->deliveryStatusId == TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT)
			{
				$objCorrespondence->deliveryDatetime = $arrDetails['delivery_datetime'];
			}
			else
			{
				$objCorrespondence->deliveryDatetime = NULL;
			}
		}

		// Set either the userId (for outbound) or contactId (for inbound)
		if ($bolOutbound)
		{
			$objCorrespondence->userId = $arrDetails['user_id'];
			$objCorrespondence->contact = NULL;
			$objCorrespondence->contactId = NULL;
		}
		else
		{
			// If a contact does not exists for the email address, one will be creation
			$objCorrespondence->userId = NULL;
			$objCorrespondence->contact = Ticketing_Contact::getForEmailAddress($from, $name);
			$objCorrespondence->contactId = $objCorrespondence->contact->id;
		}

		$objCorrespondence->summary = $arrDetails['subject'];
		$objCorrespondence->details = $arrDetails['message'];

		// Check the subject for a ticket number
		// TODO: Q: Could this apply to more than one ticket?
		$arrMatches = array();
		$strTReg = "/\[?T[0-9 ]+Z\]? */i";
		$strBReg = "/\[T[0-9]+Z\]/";
		if (preg_match($strTReg, $objCorrespondence->summary, $arrMatches))
		{
			$objCorrespondence->summary = preg_replace($strTReg, "", $objCorrespondence->summary);
			$objCorrespondence->ticketId = intval(preg_replace("/[^0-9]*/", "", $arrMatches[0]));
		}
		else if(preg_match($strBReg, $objCorrespondence->details, $arrMatches))
		{
			$objCorrespondence->ticketId = intval(preg_replace("/[^0-9]*/", "", $arrMatches[0]));
		}

		if (array_key_exists('ticket_id', $arrDetails) && $arrDetails['ticket_id'])
		{
			$objCorrespondence->ticketId = $arrDetails['ticket_id'];
		}

		// Load the ticket for this correspondence (if a ticket does not exist, one will be creation)
		// Note: If this record has a ticket number that does not exist, a new ticket will be created.
		$ticket = Ticketing_Ticket::forCorrespondence($objCorrespondence);

		// Update this instances ticket id, as a new ticket may have been created if an existing ticket was not found
		$objCorrespondence->ticketId = $ticket->id;

		// Need to save this correspondence to get assigned the id, 
		// which we need in order that we may create associated attchments
		$objCorrespondence->save();

		foreach ($arrDetails['attachments'] as $attachmentDetails)
		{
			$objAttchment = Ticketing_Attachment::create($objCorrespondence, $attachmentDetails['name'], $attachmentDetails['type'], $attachmentDetails['data']);
			$objCorrespondence->arrAttchments[$objAttchment->id] = $objAttchment;
		}

		return $objCorrespondence;
	}

	public function getTicket()
	{
		if (!$this->ticketId)
		{
			return NULL;
		}
		return Ticketing_Ticket::forCorrespondence($this);
	}

	public function getContact()
	{
		if ($this->contact == NULL)
		{
			$this->contact = Ticketing_Contact::getForId($this->contactId);
		}
		return $this->contact;
	}

	public function getUser()
	{
		if ($this->user == NULL)
		{
			$this->user = Ticketing_User::getForId($this->userId);
		}
		return $this->user;
	}

	public function getSource()
	{
		return Ticketing_Correspondance_Source::getForId($this->sourceId);
	}

	public function getDeliveryStatus()
	{
		return Ticketing_Correspondance_Delivery_Status::getForId($this->deliveryStatusId);
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

	private function loadForCorrespondenceId($intMessageNumber)
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

	public static function getForTicket(Ticketing_Ticket $ticket)
	{
		return self::getFor('ticket_id = <TicketId>', array('TicketId' => $ticket->id), TRUE);
	}

	public static function getForId($intCorrespondenceId)
	{
		if (!$intCorrespondenceId)
		{
			return NULL;
		}
		return self::getFor('id = <CorrespondenceId>', array('CorrespondenceId' => $intCorrespondenceId));
	}

	public function isSaved()
	{
		return $this->id ? TRUE : FALSE;
	}

	public function isNotSent()
	{
		return $this->deliveryStatusId === TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT;
	}

	public function isSent()
	{
		return !$this->isNotSent();
	}

	public function isNotReceived()
	{
		return $this->deliveryStatusId !== TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED;
	}

	public function isIncomming()
	{
		return $this->deliveryStatusId === TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED;
	}

	public function isOutgoing()
	{
		return !$this->isIncomming();
	}

	public function isEmail()
	{
		return $this->sourceId === TICKETING_CORRESPONDANCE_SOURCE_EMAIL;
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		// Note: Email address should be unique, so only fetch the first record
		if (!$strSort || empty($strSort))
		{
			$strSort = 'creation_datetime DESC';
		}
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			self::getColumns(), 
			$strWhere,
			$strSort,
			$strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load correspondences: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Correspondance($details);
			if (!$multiple)
			{
				return $arrInstances[0];
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

		$strDoNotReply = "/do *not *reply/i";
		if (preg_match($strDoNotReply, $this->summary) || preg_match($strDoNotReply, $this->details))
		{
			// This is probably an auto-generated response from another system.
			// Do not send a reply.
			return;
		}

		$customerGroupName = Customer_Group::getForId($custGroupEmail->customerGroupId)->name;

		// Load up the customer group ticketing configuration to see if this customer group acknowledeges receipts
		// and to get the message for the email
		$customerGroupId = $this->getTicket()->customerGroupId;
		$custGroupConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($customerGroupId);
		if (!$custGroupConfig->acknowledgeEmailReceipts())
		{
			return;
		}

		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'Email_Notification.php';

		// Check that we have a valid recipient address for the acknowledgement
		$emailAddress = $this->getContact()->email;
		if (!$emailAddress || !EmailAddressValid($emailAddress))
		{
			return;
		}

		$email = new Email_Notification(EMAIL_NOTIFICATION_TICKETING_SYSTEM, $customerGroupId);
		$email->to = $emailAddress;
		$email->from = $custGroupEmail->email;
		$email->subject = $this->summary . " [T" . $this->ticketId . "Z]";
		$emailText = $custGroupConfig->emailReceiptAcknowledgement;
		$emailText = str_replace('[TICKET_ID]', 'T'.$this->ticketId.'Z', $emailText);
		$emailText = str_replace('[CUSTOMER_GROUP_NAME]', $customerGroupName, $emailText);
		$email->text = $emailText;
		$email->send();
	}

	// TODO: Change this to allow emails to be sent to multiple contacts (and Cc'd)
	public function emailToCustomer()
	{
		$ticket = $this->getTicket();
		$contact = $this->getContact();
		$customerGroupEmail = $this->getCustomerGroupEmail();

		if (!$contact->email || !EmailAddressValid($contact->email))
		{
			throw new Exception('Unable to send email to ' . $contact->getName() . ' as they do not have a valid email address.');
		}

		if (!$ticket)
		{
			throw new Exception('No ticket found for correspondence.');
		}
		if (!$contact)
		{
			throw new Exception('No contact found for correspondence.');
		}
		if (!$customerGroupEmail)
		{
			throw new Exception('No customer group email found for correspondence.');
		}

		$email = new Email_Notification(EMAIL_NOTIFICATION_TICKETING_SYSTEM);

		$email->addTo($contact->email, $contact->getName()); // The contact from the ticket (contact_id)

		// TODO: Check for previous outgoing correspondences and send from same address 
		$email->setFrom($customerGroupEmail->email, $customerGroupEmail->name);

		$email->subject = $this->summary . " [T" . $this->ticketId . "Z]";
		$email->text = $this->details;

		$email->send();

		$this->deliveryStatusId = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT;
		$this->deliveryDatetime = date("Y-m-d H:i:s");
		$this->_saved = FALSE;
		$this->save();
	}

	public function delete()
	{
		$delInstance = new Query();
		$strSQL = "DELETE FROM ticketing_attachment WHERE correspondance_id = " . $this->id;
		if (($outcome = $delInstance->Execute($strSQL)) === FALSE)
		{
			throw new Exception('Failed to delete attachments for correspondence ' . $this->id . ' from ticket ' . $this->ticketId . ': ' . $delInstance->Error());
		}

		$strSQL = "DELETE FROM " . strtolower(__CLASS__) . " WHERE id = " . $this->id;
		if (($outcome = $delInstance->Execute($strSQL)) === FALSE)
		{
			throw new Exception('Failed to delete correspondence ' . $this->id . ' from ticket ' . $this->ticketId . ': ' . $delInstance->Error());
		}
		$this->id = NULL;
		$this->_saved = FALSE;
	}

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = $this->getValuesToSave();

		$now = date('Y-m-d H:i:s');

		// No id means that this must be a new record
		if (!$this->id)
		{
			$this->creationDatetime = $arrValues['creation_datetime'] = $now;
			$statement = new StatementInsert('ticketing_correspondance', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['Id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_correspondance', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save correspondence details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;

		Ticketing_Contact_Account::associate($this, $this->getTicket());

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

				if ($strName == 'userId')
				{
					$this->user = NULL;
				}
				else if ($strName == 'user')
				{
					$this->userId = $mxdValue->id;
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
