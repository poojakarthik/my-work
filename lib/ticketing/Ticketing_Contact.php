<?php

class Ticketing_Contact
{
	private $id = NULL;
	private $title = NULL;
	private $firstName = NULL;
	private $lastName = NULL;
	private $jobTitle = NULL;
	private $email = NULL;
	private $fax = NULL;
	private $mobile = NULL;
	private $phone = NULL;
	private $status = NULL;
	private $autoReply = NULL;

	private $_saved = FALSE;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public function getName()
	{
		return ($this->firstName ? $this->firstName : '') . ($this->firstName && $this->lastName ? ' ' : '') . ($this->lastName ? $this->lastName : $this->email);
	}

	public static function getForCorrespondence(Ticketing_Correspondance $objCorrespondence)
	{
		return Ticketing_Contact::getForId($objCorrespondence->contactId);
	}

	public static function listById($arrIds)
	{
		// WIP ?? Not implemented!! Would be useful in version 2 (?) when correspondances can have multiple contacts
		throw new Exception(__CLASS__ . "::" . __FUNCTION__ . " (" . __FILE__ . "@line " . __LINE__ . ") Not implemented.");
	}

	public static function listForAccountAndTicket($mixAccount, $ticket=NULL)
	{
		$contacts = array();
		if ($mixAccount)
		{
			$accountContacts = self::listForAccount($mixAccount);
			if (is_array($accountContacts))
			{
				$contacts = $accountContacts;
			}
		}
		if ($ticket)
		{
			$ticketContact = $ticket->getContact();
			if ($ticketContact && !array_key_exists($ticketContact->id, $contacts))
			{
				$contacts[$ticketContact->id] = $ticketContact;
			}
		}
		return $contacts;
	}

	public static function listForAccount($mixAccount)
	{
		$accountId = $mixAccount ? ($mixAccount instanceof Account ? $mixAccount->id : intval($mixAccount)) : NULL;

		if (!$accountId)
		{
			return array();
		}

		$arrCols = self::getColumns();
		$arrColumns = array();
		foreach($arrCols as $strCol)
		{
			$arrColumns[$strCol] = 'c.' . $strCol;
		}

		$selContacts = new StatementSelect(
			'ticketing_contact_account t JOIN ticketing_contact c ON t.ticketing_contact_id = c.id', 
			$arrColumns,
			'account_id = <ACCOUNT_ID>');
		$arrWhere = array('ACCOUNT_ID' => $accountId);

		if (($outcome = $selContacts->Execute($arrWhere)) === FALSE)
		{
			throw new Exception('Failed to list contacts for account ' . $accountId . ': ' . $selContacts->Error());
		}

		$arrContacts = array();
		while ($arrContact = $selContacts->Fetch())
		{
			$arrContacts[$arrContact['id']] = new self($arrContact);
		}

		return $arrContacts;
	}

	public function getAccountIds()
	{
		// Need to load the account ids associated with this contact
		$selAccounts = new StatementSelect('ticketing_contact_account', array('account_id'), 'ticketing_contact_id = <ContactId>');
		$arrWhere = array('ContactId' => $this->id);
		if (($outcome = $selAccounts->Execute($arrWhere)) === FALSE)
		{
			throw new Exception('Fialed to list accounts for contact: ' . $selAccounts->Error());
		}
		$accountIds = array();
		while($accId = $selAccounts->Fetch())
		{
			$accountIds[] = $accId['account_id'];
		}
		return $accountIds;
	}

	private static function getFor($where, $arrWhere)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selContacts = new StatementSelect(
			"ticketing_contact", 
			array("id", "title", "first_name", "last_name", "job_title", "email", "fax", "mobile", "phone", "status", "auto_reply"), 
			$where);
		if (($outcome = $selContacts->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing contact: " . $selContacts->Error());
		}
		if (!$outcome)
		{
			return NULL;
		}
		return new Ticketing_Contact($selContacts->Fetch());
	}
	
	public function getWithProperties($properties)
	{
		$id = NULL;
		if (array_key_exists('id', $properties))
		{
			$id = $properties['id'];
			unset($properties['id']);
			$contact = Ticketing_Contact::getForId($id);
		}
		else
		{
			$contact = new Ticketing_Contact();
			$contact->status = ACTIVE_STATUS_ACTIVE;
			$contact->autoReply = ACTIVE_STATUS_ACTIVE;
		}
		$contact->init($properties);
		$contact->_saved = false;
		$contact->save();
		return $contact;
	}

	public function autoReply()
	{
		return $this->autoReply === ACTIVE_STATUS_ACTIVE;
	}

	public static function getForId($id)
	{
		if (!$id) return NULL;
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	public static function getForEmailAddress($strEmailAddress, $name)
	{
		$strEmailAddress = self::sanitizeAddresses($strEmailAddress);
		$name = self::sanitizeAddresses($name);

		// Note: Email address should be unique, so only fetch the first record
		$contact = self::getFor("email = <Email>", array("Email" => $strEmailAddress));

		if (!$contact)
		{
			$contact = new Ticketing_Contact();
			$contact->email = trim(strtolower($strEmailAddress));
			$contact->status = ACTIVE_STATUS_ACTIVE;
			$contact->autoReply = ACTIVE_STATUS_ACTIVE;
			if ($name)
			{
				$name = explode(' ', $name);
				$contact->lastName = array_pop($name);
				$contact->firstName = implode(' ', $name);
			}
			$contact->save();
		}

		return $contact;
	}

	private static function sanitizeAddresses($strAddresses)
	{
		if (!$strAddresses) return $strAddresses;
		return trim(str_replace(array('"', "'", "<", '>'), '', $strAddresses));
	}

	protected static function getColumns()
	{
		return array(
			'id',
			'title',
			'first_name',
			'last_name',
			'job_title',
			'email',
			'fax',
			'mobile',
			'phone',
			'status',
			'auto_reply',
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
			$statement = new StatementInsert('ticketing_contact', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_contact', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save contact details: ' . $statement->Error());
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

	public function __set($strName, $mixValue)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} != $mixValue)
			{
				$this->{$strName} = $mixValue;
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
