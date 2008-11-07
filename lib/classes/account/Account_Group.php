<?php

class Account_Group extends ORM
{
	protected	$_strTableName	= "AccountGroup";
	
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"AccountGroup", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("AccountGroup");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("AccountGroup");
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
