<?php

class Contact
{
	private $id = NULL;
	private $accountGroup = NULL;
	private $title = NULL;
	private $firstName = NULL;
	private $lastName = NULL;
	private $dob = NULL;
	private $jobTitle = NULL;
	private $email = NULL;
	private $account = NULL;
	private $customerContact = NULL;
	private $fax = NULL;
	private $mobile = NULL;
	private $phone = NULL;
	//private $username = NULL;
	private $password = NULL;
	private $archived = NULL;

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
		return (($this->title && !$this->firstName && $this->lastName) ? $this->title . ' ' : '') . // Output a title if we have a title and surname but no christian name 
				($this->firstName ? $this->firstName : '') . // Output a cristian name if we have one
				($this->firstName && $this->lastName ? ' ' : '') . // If we have both christian and surname, put a space between them
				($this->lastName ? $this->lastName : '') . // If we have a surname, output it
				(!$this->firstName && !$this->lastName ? $this->email : ''); // If we have neither christian name nor surname, output the email address
	}

	private static function getFor($where, $arrWhere)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selContacts = new StatementSelect(
			"Contact", 
			self::getColumns(), 
			$where);
		if (($outcome = $selContacts->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing contact: " . $selContacts->Error());
		}
		if (!$outcome)
		{
			return NULL;
		}
		return new Contact($selContacts->Fetch());
	}

	public function getWithProperties($properties)
	{
		$id = NULL;
		if (array_key_exists('Id', $properties))
		{
			$id = $properties['Id'];
			unset($properties['Id']);
			$contact = Contact::getForId($id);
		}
		else
		{
			$contact = new Contact();
		}
		$contact->init($properties);
		$contact->_saved = false;
		$contact->save();
		return $contact;
	}

	public static function getForId($id)
	{
		if (!$id) return NULL;
		return self::getFor("Id = <Id>", array("Id" => $id));
	}

	protected static function getColumns()
	{
		return array(
			'Id',
			'AccountGroup',
			'Title',
			'FirstName',
			'LastName',
			'DOB',
			'JobTitle',
			'Email',
			'Account',
			'CustomerContact',
			'Fax',
			'Mobile',
			'Phone',
			//'Username',
			'Password',
			'Archived',
		);
	}

	public function canAccessAccount($objAccount)
	{
		/*
		 *  A contact can access an account if:
		 * 	The contact is flagged as a CustomerContact (Contact.CustomerContact == 1) and the contact is in the same AccountGroup as the Account
		 * 	OR
		 * 	Account.Id == Contact.Account
		 * 	OR
		 * 	Account.PrimaryContract == Contact.Id
		 */
		return ($this->customerContact == 1 && $this->accountGroup == $objAccount->accountGroup) || ($this->account == $objAccount->id) || ($objAccount->primaryContact == $this->account);
	}
	
	// Returns a list of AccountIds or Account objects, defining the Accounts that can be associated with this contact
	// In both cases, the key to the array will be the id of the account
	// This will return an empty string if there are no Accounts for this Contact
	public function getAccounts($bolAsObjects=FALSE)
	{
		$strQuery = "	SELECT a.Id AS AccountId
						FROM Account AS a INNER JOIN Contact AS c ON (c.CustomerContact = 1 AND a.AccountGroup = c.AccountGroup) OR (c.Account = a.Id) OR (c.Id = a.PrimaryContact)
						WHERE c.Id = {$this->id}";
		$qryQuery = new Query();
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve accounts for contact: {$this->id} - " . $qryQuery->Error());
		}

		$arrAccounts = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrAccounts[$arrRecord['AccountId']] = ($bolAsObjects)? Account::getForId($arrRecord['AccountId']) : $arrRecord['AccountId'];
		}

		return $arrAccounts;
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

	public function passwordIsValid($strPassword)
	{
		return $strPassword && sha1($strPassword) == $this->password;
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
			$statement = new StatementInsert('Contact', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['Id'] = $this->id;
			$statement = new StatementUpdateById('Contact', $arrValues);
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
		if (preg_match("/^[A-Z]+$/", $name)) $name = strtolower($name);
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
}

?>
