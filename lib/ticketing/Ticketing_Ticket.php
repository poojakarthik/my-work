<?php

// Ensure that we have the Ticketing_Ticket_Message class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Correspondance.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Contact.php';

class Ticketing_Ticket
{
	protected $id = NULL;
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
	protected $modifiedByUserId = NULL;
	protected $arrServices = NULL;

	protected $_saved = FALSE;
	protected $owner = NULL;

	public static function forCorrespondence(Ticketing_Correspondance $correspondence)
	{
		$mxdTicketId = $correspondence->ticketId;

		$ticket = NULL;

		// If ticket number is set
		if ($mxdTicketId)
		{
			// Check that the ticket number exists. If not, we need to create a new ticket
			$ticket = self::loadForTicketId($mxdTicketId);
		}
		if (!$ticket)
		{
			$ticket = self::createNew($correspondence->getContact(), $correspondence->summary, $correspondence->getCustomerGroupEmail()->customerGroupId);
		}

		return $ticket;
	}

	public static function createBlank()
	{
		
		return new self();
	}

	public function isSaved()
	{
		return $this->id !== NULL;
	}

	public static function createNew(Ticketing_Contact $contact, $strSubject, $custGroupId)
	{
		// At this point, about all we will know is the subject, the 
		// contact (which we might know nothing about other than email address)
		// and the customer group id (which might be null)
		// Set the rest of the properties to defaults
		$objTicket = new Ticketing_Ticket();
		$objTicket->subject = $strSubject;
		$objTicket->priorityId = TICKETING_PRIORITY_MEDIUM;
		$objTicket->contactId = $contact->id;
		$objTicket->statusId = TICKETING_STATUS_UNASSIGNED;
		$customerGroup = Customer_Group::getForId($custGroupId);
		if ($customerGroup == NULL)
		{
			throw new Exception("Customer group $custGroupId does not exist.");
		}
		$objTicket->customerGroupId = $custGroupId;
		$objTicket->categoryId = Ticketing_Category::TICKETING_CATEGORY_UNCATEGORIZED;
		$objTicket->creationDatetime = $objTicket->modifiedDatetime = GetCurrentISODateTime();

		// We can check to see if the contact is associated with just one account. 
		// If so, should we set that account on this correspondence by default?
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

	public function assignTo($user)
	{
		$this->statusId = TICKETING_STATUS_ASSIGNED;
		$this->ownerId = $user->id;
		$this->_saved = FALSE;
		$this->save();
		
		// Email the new Owner
		require_once 'Zend/Mail.php';
		
		$strUserEmail		= $user->getEmail();
		if ($strUserEmail)
		{
			// DEBUG
			//$strUserEmail	= "rdavis@ybs.net.au";
			// DEBUG
			
			$objCustomerGroup	= Customer_Group::getForId($this->customerGroupId);
			
			$strManagerName		= Flex::getDisplayName();
			$strUserName		= $user->getName();
			$strUrl				= $objCustomerGroup->flexUrl."/admin/reflex.php/Ticketing/Ticket/{$this->id}/View";
			$strTicketSubject	= htmlspecialchars($this->subject);
			$strEmailSubject	= "You have been allocated a new ticket (#{$this->id})";
			$strEmailContent	=	"<div style='font-family: Calibri,sans-serif;'>\n" .
									"You have been allocated ticket number <a href='{$strUrl}'>{$this->id}</a> by {$strManagerName} with the subject of '{$strTicketSubject}'.<br /><br />\n" .
									"<a href='{$strUrl}'>Click here</a> to go to the Ticket Overview page.\n" .
									"</div>";
			//SendEmail($strUserEmail, $strEmailSubject, $strEmailContent, "noreply@{$objCustomerGroup->emailDomain}", true);
			
			$objEmail	= new Zend_Mail();
			$objEmail->setBodyHtml($strEmailContent);
			$objEmail->setFrom("noreply@{$objCustomerGroup->emailDomain}");
			$objEmail->addTo($strUserEmail, $strUserName);
			$objEmail->setSubject($strEmailSubject);
			$objEmail->send();
		}
	}

	public function delete()
	{
		$this->statusId = TICKETING_STATUS_DELETED;
		$this->_saved = FALSE;
		$this->save();
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
			'modified_by_user_id'
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
		
		// Set the user who modified the ticket (if not instigated by a user, this will set it to NULL)
		$objCurrentTicketUser = Ticketing_User::getCurrentUser();
		$this->modifiedByUserId = $objCurrentTicketUser->id;
		
		$arrValues = $this->getValuesToSave();

		$now = GetCurrentISODateTime();
		$this->modifiedDatetime = $arrValues['modified_datetime'] = $now;

		// No id means that this must be a new record
		if (!$this->id)
		{
			// Check if the creation timestamp has been set yet
			if ($arrValues['creation_datetime'] === null)
			{
				// Set it now
				$this->creationDatetime = $arrValues['creation_datetime'] = $now;
			}
			
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
			// (should not be possible for this class, but maybe true for subclasses)
			if ($this->group_ticket_id === NULL)
			{
				$this->groupTicketId = $outcome;
				$this->_saved = FALSE;
				return $this->save();
			}
		}
		if (get_class($this) !== 'Ticketing_Ticket_History')
		{
			// Each time we update the record, we need to copy the details to the history table
			$this->recordHistoricCopy();

			// If an account has been defined for this ticket then associate all contacts referenced in this ticket, with the account
			if ($this->accountId != null)
			{ 
				Ticketing_Contact_Account::associate($this, $this->accountId);
			}
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
		$selProperties = new StatementSelect('ticketing_ticket', self::getColumns(), "id = <Id>");
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

	public static function countMatching($filter=NULL, $relatedString=NULL)
	{
		$where = self::getRelatedStringWhereClause($relatedString);
		$arrWhere = array();
		foreach ($filter as $column => $style)
		{
			if (!property_exists(__CLASS__, $column)) continue;
			$column = self::uglifyName($column);
			switch($style['comparison'])
			{
				case '=':
					if ($style['value'] === NULL || (is_array($style['value']) && empty($style['value'])))
					{
						$where .= ($where ? ' AND ' : '') . " $column IS NULL";
					}
					else if (is_array($style['value']))
					{
						$where .= ($where ? ' AND ' : '') . " $column IN ('" . implode("','", $style['value']) . "')";
					}
					else
					{
						$where .= ($where ? ' AND ' : '') . " $column = <" . strtoupper($column) . ">";
						$arrWhere[strtoupper($column)] = $style['value'];
					}
			}
		}
		if (!$where)
		{
			$where = NULL;
		}
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			array('nr' => 'count(id)'), 
			$where);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to count tickets: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return 0;
		}
		$outcome = $selMatches->Fetch();
		return intval($outcome['nr']);
	}
	
	public static function findMatching($columns=NULL, $sort=NULL, $filter=NULL, $offset=0, $limit=NULL, $relatedString=NULL)
	{
		$where = self::getRelatedStringWhereClause($relatedString);
		$arrWhere = array();

		foreach ($filter as $column => $style)
		{
			if (!property_exists(__CLASS__, $column)) continue;
			$column = self::uglifyName($column);
			switch($style['comparison'])
			{
				case '=':
					if ($style['value'] === NULL || (is_array($style['value']) && empty($style['value'])))
					{
						$where .= ($where ? ' AND ' : '') . " $column IS NULL";
					}
					else if (is_array($style['value']))
					{
						$where .= ($where ? ' AND ' : '') . " $column IN ('" . implode("','", $style['value']) . "')";
					}
					else
					{
						$where .= ($where ? ' AND ' : '') . " $column = <" . strtoupper($column) . ">";
						$arrWhere[strtoupper($column)] = $style['value'];
					}
			}
		}
		if (!$where)
		{
			$where = NULL;
		}
		
		
		$strSort = '';
		$arrExtendedSortingFields = array("userCorrespondenceLastActionedOn");
		foreach ($sort as $column => $asc)
		{
			if (property_exists(__CLASS__, $column) || in_array($column, $arrExtendedSortingFields))
			{
				$strSort .= ($strSort ? ', ' : '') . self::uglifyName($column) . ' ' . ($asc ? ' ASC ' : ' DESC ');
			}
		}
		if (!$strSort || empty($strSort))
		{
			$strSort = 'creation_datetime DESC, id DESC';
		}

		$strLimit = intval($limit) ? (intval($offset) . ", " . intval($limit)): NULL;
		
		$strTables = "ticketing_ticket LEFT JOIN (
SELECT ticket_id, MAX(COALESCE(delivery_datetime, creation_datetime)) AS user_correspondence_last_actioned_on
FROM ticketing_correspondance
WHERE user_id IS NOT NULL
GROUP BY ticket_id
) AS user_created_correspondence ON ticketing_ticket.id = user_created_correspondence.ticket_id";

		$selMatches = new StatementSelect($strTables, self::getColumns(), $where, $strSort, $strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load tickets: " . $selMatches->Error());
		}

		$arrInstances = array();
		if ($outcome)
		{
			while($details = $selMatches->Fetch())
			{
				$arrInstances[] = new Ticketing_Ticket($details);
			}
		}
		return $arrInstances;
	}

	private static function getRelatedStringWhereClause($relatedString)
	{
		$relatedString = trim($relatedString);
		if (!$relatedString)
		{
			return '';
		}
		$words = array_slice(explode(' ', preg_replace("/ {2,*}/", " ", $relatedString)), 0, 8);
		$arrWords = array();
		$arrSubjects = array();
		$arrSummaries = array();
		$arrContact = array();
		$arrContactNr = array();
		$arrFNN = array();
		$arrAccounts = array();
		$arrAccountNrs = array();
		foreach ($words as $word)
		{
			$word = trim($word);
			if (!$word)
			{
				continue;
			}
			$bolIsNumber = preg_match("/^[0-9]+$/", $word);
			$bolIsPhoneNumber = $bolIsNumber && preg_match("/^[0-9]{8,10}$/", $word);
			$word = $arrWords[] = Data_Source::get()->escape($word);
			if ($bolIsNumber)
			{
				$arrAccountNrs[] = $word;
			}
			else
			{
				$arrAccounts[] = "BusinessName LIKE '%$word%'";
			}
			$arrSubjects[] = "subject LIKE '%$word%'";
			$arrSummaries[] = "CONCAT(CASE WHEN summary IS NULL THEN '' ELSE  summary END, \" \", CASE WHEN details IS NULL THEN '' ELSE  details END) LIKE '%$word%'";
			if ($bolIsPhoneNumber)
			{
				$arrContactNr[] = "phone LIKE '%$word' OR fax LIKE '%$word' OR mobile LIKE '%$word'";
			}
			else
			{
				$arrContact[] = "concat(CASE WHEN title IS NULL THEN '' ELSE  title END, \" \", CASE WHEN first_name IS NULL THEN '' ELSE  first_name END, \" \", CASE WHEN last_name IS NULL THEN '' ELSE  last_name END, \" \", CASE WHEN job_title IS NULL THEN '' ELSE  job_title END, \" \", CASE WHEN email IS NULL THEN '' ELSE  email END) LIKE '%$word%'";
			}
			if ($bolIsPhoneNumber) 
			{
				$arrFNN[] = "FNN LIKE \"%$word\"";
			}
		}
		if (!($nr = count($arrWords)))
		{
			return '';
		}

		return 	"id IN (" .
				"	SELECT distinct(id) FROM (" .
				"		(SELECT id FROM ticketing_ticket WHERE " . implode(' AND ', $arrSubjects) . ") " .
				" UNION (SELECT ticket_id as \"id\" FROM ticketing_correspondance WHERE (" . implode(' AND ', $arrSummaries) . ") ) " .
		(count($arrContact) 
			  ? (count($arrContactNr) 
			 	    ? " UNION (SELECT ticket_id as \"id\" FROM ticketing_correspondance, ticketing_contact WHERE ticketing_correspondance.contact_id = ticketing_contact.id AND " . implode(' AND ', $arrContact) . " AND (" . implode(' OR ', $arrContactNr) . "))" 
			        : " UNION (SELECT ticket_id as \"id\" FROM ticketing_correspondance, ticketing_contact WHERE ticketing_correspondance.contact_id = ticketing_contact.id AND " . implode(' AND ', $arrContact) . ")"
				)
			  : (count($arrContactNr) 
			        ? " UNION (SELECT ticket_id as \"id\" FROM ticketing_correspondance, ticketing_contact WHERE ticketing_correspondance.contact_id = ticketing_contact.id AND (" . implode(' OR ', $arrContactNr) . "))"
			        : ''
			    )
		) . 
		(count($arrAccounts) 
			  ? (count($arrAccountNrs) 
			 	    ? " UNION (SELECT ticketing_ticket.id as \"id\" FROM ticketing_ticket, Account WHERE ticketing_ticket.account_id = Account.Id AND ((" . implode(' AND ', $arrAccounts) . ") OR (Account.Id IN (" . implode(', ', $arrAccountNrs) . ") OR Account.AccountGroup IN (" . implode(', ', $arrAccountNrs) . "))))" 
			        : " UNION (SELECT ticketing_ticket.id as \"id\" FROM ticketing_ticket, Account WHERE ticketing_ticket.account_id = Account.Id AND " . implode(' AND ', $arrAccounts) . ")"
				)
			  : (count($arrAccountNrs) 
			        ? " UNION (SELECT ticketing_ticket.id as \"id\" FROM ticketing_ticket, Account WHERE ticketing_ticket.account_id = Account.Id AND (Account.Id IN (" . implode(', ', $arrAccountNrs) . ") OR Account.AccountGroup IN (" . implode(', ', $arrAccountNrs) . ")))"
			        : ''
			    )
		) . 
		(count($arrFNN) 
		 	  ? " UNION (SELECT ticket_id as \"id\" FROM ticketing_ticket_service, Service WHERE Service.Id = ticketing_ticket_service.service_id AND (" . implode(' OR ', $arrFNN) . "))" 
			  : ''
		) .
				") ids)";
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		// Note: Email address should be unique, so only fetch the first record
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
			throw new Exception("Failed to load tickets: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Ticket($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}

	public function getAccount()
	{
		return Account::getForId($this->accountId);
	}

	public function getContact()
	{
		return Ticketing_Contact::getForId($this->contactId);
	}

	public function getPriority()
	{
		return Ticketing_Priority::getForId($this->priorityId);
	}

	public function getStatus()
	{
		return Ticketing_Status::getForId($this->statusId);
	}

	// Returns an array of Ticketing_Ticket_Service objects with each record's service_id as its key
	public function getServices($forceReset=FALSE)
	{
		if (!$this->id)
		{
			$this->arrServices = array();
		}
		else if ($forceReset || $this->arrServices === NULL)
		{
			$arrServices = Ticketing_Ticket_Service::listForTicket($this);
			$this->arrServices = array();
			foreach($arrServices as $objService)
			{
				$this->arrServices[$objService->serviceId] = $objService;
			}
		}
		return $this->arrServices;
	}

	public function getServiceIds()
	{
		return array_keys($this->getServices());
	}

	public function setServices($arrServiceIds)
	{
		if (!$this->id)
		{
			throw new Exception('Internal System Error :: Cannot set services for a ticket before the ticket has been saved.');
		}
		$arrServicesToKeep = array();
		$this->getServices();
		foreach ($arrServiceIds as $serviceId)
		{
			if (!array_key_exists($serviceId, $this->arrServices))
			{
				$arrServiceToKeep[$serviceId] = Ticketing_Ticket_Service::createForTicket($this, $serviceId);
			}
			else
			{
				$arrServiceToKeep[$serviceId] = $this->arrServices[$serviceId];
				unset($this->arrServices[$serviceId]);
			}
		}
		foreach($this->arrServices as $objService)
		{
			$objService->delete();
		}
		$this->arrServices = $arrServiceToKeep;
	}

	public function isAssigned()
	{
		return $this->statusId !== TICKETING_STATUS_UNASSIGNED;
	}

	public function isAssignedTo($user)
	{
		return $this->ownerId === $user->id;
	}

	public function getCategory()
	{
		if ($this->owner !== NULL)
		{
			return $this->owner;
		}
		return Ticketing_Category::getForId($this->categoryId);
	}

	public function getOwner()
	{
		return Ticketing_User::getForId($this->ownerId);
	}

	public function ownedBy($user)
	{
		return $user->id === $this->ownerId;
	}

	public function getCustomerGroup()
	{
		return Customer_Group::getForId($this->customerGroupId);
	}

	public static function getForId($id)
	{
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	public function getCustomerGroupEmail()
	{
		$correspondances = $this->getCorrespondences();
		foreach($correspondances as $correspondance)
		{
			$customerGroupEmail = $correspondance->getCustomerGroupEmail();
			if ($customerGroupEmail)
			{
				if ($customerGroupEmail->isArchivedVersion())
				{
					// This customer group email record has been archived.  See if there is an active version of it for this customer group
					$customerGroupEmail = $customerGroupEmail->getActiveVersion();
					if ($customerGroupEmail !== null)
					{
						// Found one
						return $customerGroupEmail;
					}
					else
					{
						// Did not find one, so move on to the next correspondence item
					}
				}
				else
				{
					// This customer group email is still active, so use it
					return $customerGroupEmail;
				}
			}
		}
		
		// If it got this far, then it couldn't find an active email address to use, based on the ticket's existing correspondence
		// Use the customer group's default email address
		$config = Ticketing_Customer_Group_Config::getForCustomerGroupId($this->customerGroupId);
		if (!$config)
		{
			return NULL;
		}
		return $config->getDefaultCustomerGroupEmail();
	}

	public function getCorrespondences()
	{
		return Ticketing_Correspondance::getForTicket($this);
	}
	
	public function getHistory()
	{
		return Ticketing_Ticket_History::getForTicket($this);
	}

	public function addCorrespondence($strSubject, $strMessage, $arrAttchments=NULL, $intSource=TICKETING_CORRESPONDANCE_SOURCE_PHONE, $bolInbound=FALSE, $bolAlreadyCommunicated=TRUE, $defaultGroupEmail=NULL, $contactOrUserId=NULL)
	{
		$now = GetCurrentISODateTime();
		$arrDetails = array(
			'source_id' 	=>	$intSource, 	// The source id (TICKETING_CORRESPONDANCE_SOURCE_xxx)
			'summary'		=>	$strSubject, 	// String summary (single line) (eg: Email subject) of the correspondence
			'details'		=>	$strMessage, 	// String description of the email, much more detailed than the summary
			'ticket_id'		=>	$this->id, 		//If not specified, a new ticket will be created for the correspondence
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

		if (($correspondence=TicketingTicket::createForDetails($arrDetails)) === NULL)
		{
			throw new Exception('Failed to create the correspondence.');
		}
		return $correspondence;
	}

	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}

	// Note: This will only flag the object as not being saved, if the new value is not equal to the old value
	public function __set($strName, $mxdValue)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} !== $mxdValue)
			{
				if ($strName == 'contactId')
				{
					$this->contact = NULL;
				}
				else if ($strName == 'contact')
				{
					$this->contactId = $mxdValue->id;
				}

				if ($strName == 'ownerId')
				{
					$this->owner = NULL;
				}
				else if ($strName == 'owner')
				{
					$this->ownerId = $mxdValue->id;
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

	private function uglifyName($name)
	{
		$tidy = str_replace(' ', '_', strtolower(preg_replace("/([A-Z])/", " \${1}", $name)));
		return $tidy;
	}
}

?>
