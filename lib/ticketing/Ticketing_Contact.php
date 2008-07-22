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

	private $_saved = FALSE;

	private function __construct($id)
	{
		if ($id)
		{
			$selContacts = new StatementSelect("ticketing_contact", array("id", "title", "first_name", "last_name", "job_title", "email", "fax", "mobile", "phone", "status"), "id = <Id>");

			if (($outcome = $selContacts->Execute(array("Id" => $id))) === FALSE)
			{
				throw new Exception("Failed to load existing contact by id '$id': " . $selContacts->Error());
			}
			if (!$outcome)
			{
				throw new Exception("Contact not found for id " . $id);
			}

			$properties = $selContacts->Fetch();

			foreach($properties as $name => $value)
			{
				$this->{$name} = $value;
			}

			// Load up the details of the contact
			$this->_saved = TRUE;
		}
	}

	public static function getForCorrespondance(Ticketing_Correspondance $objCorrespondance)
	{
		return new Ticketing_Contact($objCorrespondance->contactId);
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

	public static function getForEmailAddress($strEmailAddress, $name)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selContacts = new StatementSelect("ticketing_contact", "id", "email = <Email>");
		if ($selContacts->Execute(array("Email" => $strEmailAddress)) === FALSE)
		{
			throw new Exception("Failed to check for existing contact by email address: " . $selContacts->Error());
		}
		$contact = $selContacts->Fetch();
		if ($contact)
		{
			$id = $contact["id"];
		}
		else
		{
			$id = NULL;
		}

		$contact = new Ticketing_Contact($id);

		if (!$id)
		{
			$contact->email = trim(strtolower($strEmailAddress));
			if ($name)
			{
				$name = explode(' ', $name);
				$contact->lastName = array_pop($name);
				$contact->firstName = implode(' ', $name);
				$contact->status = ACTIVE_STATUS_ACTIVE;
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
			'status' => $this->status
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
