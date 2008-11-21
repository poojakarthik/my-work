<?php

class Contact extends ORM
{
	protected	$_strTableName	= "Contact";

	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
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
			"*", 
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
		
		foreach ($properties as $strName=>$mixValue)
		{
			// Load from the Database
			$this->{$strName}	= $mixValue;
		}
		
		$contact->save();
		return $contact;
	}

	public static function getForId($id)
	{
		if (!$id) return NULL;
		return self::getFor("Id = <Id>", array("Id" => $id));
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

	public function passwordIsValid($strPassword)
	{
		return $strPassword && sha1($strPassword) == $this->password;
	}

	public static function isEmailInUse($strEmailAddress)
	{
		$selContactByEmail	= self::_preparedStatement('selContactByEmail');
		return $selContactByEmail->Execute(Array('Email' => trim($strEmailAddress), 'IncludeArchived' => 0));
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Contact", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selContactByEmail':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Contact", "*", "Email LIKE <Email> AND (<IncludeArchived> = 1 OR Archived = 0)", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Contact");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Contact");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}

?>
