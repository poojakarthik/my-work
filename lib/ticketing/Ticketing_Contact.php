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

	private function __construct($arrProperties)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public function getName()
	{
		return ($this->firstName ? $this->firstName : '') . ($this->firstName && $this->lastName ? ' ' : '') . ($this->lastName ? $this->lastName : '');
	}

	public static function getForCorrespondance(Ticketing_Correspondance $objCorrespondance)
	{
		return Ticketing_Contact::getForId($objCorrespondance->contactId);
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

	public function autoReply()
	{
		return $this->autoReply === ACTIVE_STATUS_ACTIVE;
	}

	public static function getForId($id)
	{
		$this->getFor("id = <Id>", array("Id" => $id));
	}

	public static function getForEmailAddress($strEmailAddress, $name)
	{
		// Note: Email address should be unique, so only fetch the first record
		$contact = $this->getFor("email = <Email>", array("Email" => $strEmailAddress));

		if (!$contact)
		{
			$contact = new Ticketing_Contact();
			$contact->email = trim(strtolower($strEmailAddress));
			if ($name)
			{
				$name = explode(' ', $name);
				$contact->lastName = array_pop($name);
				$contact->firstName = implode(' ', $name);
				$contact->status = ACTIVE_STATUS_ACTIVE;
				$contact->autoReply = ACTIVE_STATUS_ACTIVE;
			}
			$contact->save();
		}

		return $contact;
	}

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = array(
			'title' => $this->title, 
			'first_name' => $this->firstName, 
			'last_name' => $this->lastName, 
			'job_title' => $this->jobTitle, 
			'email' => $this->email, 
			'fax' => $this->fax, 
			'mobile' => $this->mobile, 
			'phone' => $this->phone, 
			'status' => $this->status,
			'auto_reply' => $this->autoReply
		);
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
