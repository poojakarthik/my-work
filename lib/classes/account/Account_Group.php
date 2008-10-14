<?php

class Account_Group
{
	private $id				= NULL;
	private $createdBy		= NULL;
	private $createdOn		= NULL;
	private $managedBy		= NULL;
	private $archived		= NULL;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	private static function getFor($strWhere, $arrWhere)
	{
		$selAccountGroup = new StatementSelect("AccountGroup", self::getColumns(), $strWhere);
		if (($outcome = $selAccountGroup->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to retrieve AccountGroup: ". $selAccountGroup->Error());
		}
		if (!$outcome)
		{
			return NULL;
		}
		return new self($selAccountGroup->Fetch());
	}

	public static function getForAccountId($intAccountId)
	{
		// Retrieve the account
		$objAccount = Account::getForId($intAccountId);
		
		if ($objAccount === NULL)
		{
			return NULL;
		}
		return self::getForId($objAccount->accountGroup);
	}
	
	public static function getForId($intAccountGroupId)
	{
		return self::getFor("Id = <Id>", array("Id" => $intAccountGroupId));
	}
	
	protected static function getColumns()
	{
		return array(
			'id'		=> 'Id',
			'createdBy'	=> 'CreatedBy',
			'createdOn'	=> 'CreatedOn',
			'managedBy'	=> 'ManagedBy',
			'archived'	=> 'Archived'
		);
	}

	// Returns a list of AccountIds or Account objects, defining the Accounts that can be associated with this contact
	// In both cases, the key to the array will be the id of the account
	// This will return an empty array if there are no Accounts for this AccountGroup
	public function getAccounts($bolAsObjects=FALSE)
	{
		$strQuery = "	SELECT Id
						FROM Account
						WHERE AccountGroup = {$this->id}
						ORDER BY Id ASC;";
		$qryQuery = new Query();
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve accounts for AccountGroup: {$this->id} - " . $qryQuery->Error());
		}

		$arrAccounts = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrAccounts[$arrRecord['Id']] = ($bolAsObjects)? Account::getForId($arrRecord['Id']) : $arrRecord['Id'];
		}

		return $arrAccounts;
	}

	// Returns a list of ContactIds or Contact objects, defining the Contacts that belong to this AccountGroup
	// In both cases, the key to the array will be the id of the Contact
	// They will be ordered by FirstName + LastName
	public function getContacts($bolAsObjects=FALSE)
	{
		$strQuery = "	SELECT Id, CONCAT(FirstName, ' ', LastName) AS FullName
						FROM Contact
						WHERE AccountGroup = {$this->id}
						ORDER BY FullName ASC;";
		$qryQuery = new Query();
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve contacts for AccountGroup: {$this->id} - " . $qryQuery->Error());
		}

		$arrContacts = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrContacts[$arrRecord['Id']] = ($bolAsObjects)? Contact::getForId($arrRecord['Id']) : $arrRecord['Id'];
		}

		return $arrContacts;
	}
	

	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{$name} = $value;
		}
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
