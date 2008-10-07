<?php
//----------------------------------------------------------------------------//
// Customer_Search
//----------------------------------------------------------------------------//
/**
 * Customer_Search
 *
 * Encapsulates Customer Search functionality
 *
 * Encapsulates Customer Search functionality
 *
 * @class	Customer_Search
 */
class Customer_Search
{
	const CONSTRAINT_TYPE_ACCOUNT_ID	= 1;
	const CONSTRAINT_TYPE_CONTACT_NAME	= 2;
	const CONSTRAINT_TYPE_ACCOUNT_NAME	= 3;
	const CONSTRAINT_TYPE_SERVICE_ID	= 4;
	const CONSTRAINT_TYPE_FNN			= 5;
	const CONSTRAINT_TYPE_INVOICE_ID	= 6;
	const CONSTRAINT_TYPE_ABN			= 7;
	const CONSTRAINT_TYPE_ACN			= 8;
	const CONSTRAINT_TYPE_EMAIL			= 9;
	
	private static $arrConstraintTypes = array(	self::CONSTRAINT_TYPE_ACCOUNT_ID	=> array(	"Name"			=> "Account Id",
																								"Description"	=> "Account Id"
																							),
												self::CONSTRAINT_TYPE_CONTACT_NAME	=> array(	"Name"			=> "Contact Name",
																								"Description"	=> "Contact Name"
																							),
												self::CONSTRAINT_TYPE_ACCOUNT_NAME	=> array(	"Name"			=> "Account Name",
																								"Description"	=> "Account Name"
																							),
												self::CONSTRAINT_TYPE_SERVICE_ID	=> array(	"Name"			=> "Service Id",
																								"Description"	=> "Service Id"
																							),
												self::CONSTRAINT_TYPE_FNN			=> array(	"Name"			=> "FNN",
																								"Description"	=> "FNN"
																							),
												self::CONSTRAINT_TYPE_INVOICE_ID	=> array(	"Name"			=> "Invoice Id",
																								"Description"	=> "Invoice Id"
																							),
												self::CONSTRAINT_TYPE_ABN			=> array(	"Name"			=> "ABN",
																								"Description"	=> "ABN"
																							),
												self::CONSTRAINT_TYPE_ACN			=> array(	"Name"			=> "ACN",
																								"Description"	=> "ACN"
																							),
												self::CONSTRAINT_TYPE_EMAIL			=> array(	"Name"			=> "Email Address",
																								"Description"	=> "Email Address"
																							)
												);
	
	const SEARCH_TYPE_ACCOUNTS			= 1;
	const SEARCH_TYPE_CONTACTS			= 2;
	const SEARCH_TYPE_SERVICES			= 3;
	
	private static $arrSearchTypes = array(	self::SEARCH_TYPE_ACCOUNTS	=> array(	"Name"			=> "Accounts",
																					"Description"	=> "Accounts"
																				),
											self::SEARCH_TYPE_CONTACTS	=> array(	"Name"			=> "Contacts",
																					"Description"	=> "Contacts"
																				),
											self::SEARCH_TYPE_SERVICES	=> array(	"Name"			=> "Services",
																					"Description"	=> "Services"
																				)
							);
	
	// Maximum records that a single query can return
	const MAX_RECORDS					= 1000;
	
	public static function getConstraintTypes()
	{
		return self::$arrConstraintTypes;
	}
	
	public static function getAllowableConstraintTypes($intSearchType = self::SEARCH_TYPE_ACCOUNTS)
	{
		$arrConstraints = array();
		switch ($intSearchType)
		{
			case self::SEARCH_TYPE_ACCOUNTS:
				$arrConstraints[self::CONSTRAINT_TYPE_ACCOUNT_ID]	= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_ACCOUNT_ID];
				$arrConstraints[self::CONSTRAINT_TYPE_ACCOUNT_NAME]	= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_ACCOUNT_NAME];
				$arrConstraints[self::CONSTRAINT_TYPE_FNN]			= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_FNN];
				$arrConstraints[self::CONSTRAINT_TYPE_INVOICE_ID]	= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_INVOICE_ID];
				$arrConstraints[self::CONSTRAINT_TYPE_ABN]			= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_ABN];
				$arrConstraints[self::CONSTRAINT_TYPE_ACN]			= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_ACN];
				$arrConstraints[self::CONSTRAINT_TYPE_EMAIL]		= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_EMAIL];
				break;
			
			case self::SEARCH_TYPE_CONTACTS:
				$arrConstraints[self::CONSTRAINT_TYPE_CONTACT_NAME]	= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_CONTACT_NAME];
				$arrConstraints[self::CONSTRAINT_TYPE_ACCOUNT_ID]	= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_ACCOUNT_ID];
				$arrConstraints[self::CONSTRAINT_TYPE_EMAIL]		= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_EMAIL];
				break;
			
			case self::SEARCH_TYPE_SERVICES:
				$arrConstraints[self::CONSTRAINT_TYPE_FNN]			= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_FNN];
				$arrConstraints[self::CONSTRAINT_TYPE_SERVICE_ID]	= self::$arrConstraintTypes[self::CONSTRAINT_TYPE_SERVICE_ID];
				break;
			
			default:
				throw new exception("Invalid Search Type: $intServiceType");
				break;
		}
		return $arrConstraints;
	}
	
	public static function getSearchTypes()
	{
		// I currently have not implemented the search functionality for finding services
		$arrSearchTypes = self::$arrSearchTypes;
		if (array_key_exists(self::SEARCH_TYPE_SERVICES, $arrSearchTypes))
		{
			unset($arrSearchTypes[self::SEARCH_TYPE_SERVICES]);
		}
		
		return $arrSearchTypes;
	}
	
	// returns an array of account Ids, based on the search
	// $intSearchType must be one of the SearchType constants
	// $mixSearchItem cannot be NULL or an empty string
	// It is a precondition that the SearchItem has already been appropriately escaped and is safe to insert into queries
	// Returns an array of unique account Ids if $intSearchType == SEARCH_TYPE_ACCOUNTS
	// Returns an array of unique contact Ids if $intSearchType == SEARCH_TYPE_CONTACTS
	// Returns an array of unique service Ids if $intSearchType == SEARCH_TYPE_SERVICES
	// Returns an empty array if nothing matched the search criteria
	public static function findFor($intSearchType, $mixConstraint, $intConstraintType=NULL, $bolIncludeArchived=FALSE)
	{
		if (array_search($intSearchType, array_keys(self::getSearchTypes())) === FALSE)
		{
			throw new exception("Invalid search type: $intSearchType");
		}
		if ($intConstraintType !== NULL && array_search($intConstraintType, array_keys(self::getAllowableConstraintTypes($intSearchType))) === FALSE)
		{
			$arrSearchTypes = self::getSearchTypes();
			throw new exception("Invalid constraint type ($intConstraintType) for {$arrSearchTypes[$intSearchType]['Name']} searches");
		}
		
		$arrIds = array();
		switch ($intSearchType)
		{
			case self::SEARCH_TYPE_ACCOUNTS:
				$arrIds = self::_findAccountsFor($mixConstraint, $intConstraintType, $bolIncludeArchived);
				break;
				
			case self::SEARCH_TYPE_SERVICES:
				$arrIds = self::_findServicesFor($mixConstraint, $intConstraintType, $bolIncludeArchived);
				break;
				
			case self::SEARCH_TYPE_CONTACTS:
				$arrIds = self::_findContactsFor($mixConstraint, $intConstraintType, $bolIncludeArchived);
				break;
		}

		// The array of Ids should already have duplicates removed
		return $arrIds;
	} 

	private static function _findAccountsFor($mixConstraint, $intConstraintType, $bolIncludeArchived)
	{
		$arrAccounts = array();
		switch ($intConstraintType)
		{
			case self::CONSTRAINT_TYPE_ACCOUNT_ID:
				if (self::_findAccountForAccountId($mixConstraint, $bolIncludeArchived) !== NULL)
				{
					// The account could be found
					$arrAccounts[] = $mixConstraint;
				}
				break;
			
			case self::CONSTRAINT_TYPE_ACCOUNT_NAME:
				// BusinessName and TradingName
				$arrAccounts = self::_findAccountsForAccountName($mixConstraint, $bolIncludeArchived);
				break;
				
			case self::CONSTRAINT_TYPE_FNN:
				$arrAccounts = self::_findAccountsForFNN($mixConstraint, $bolIncludeArchived);
				break;
				
			case self::CONSTRAINT_TYPE_INVOICE_ID:
				if (($intAccountId = self::_findAccountForInvoiceId($mixConstraint, $bolIncludeArchived)) !== NULL)
				{
					$arrAccounts[] = $intAccountId;
				}
				break;
				
			case self::CONSTRAINT_TYPE_ABN:
				$arrAccounts = self::_findAccountsForABN($mixConstraint, $bolIncludeArchived);
				break;
				
			case self::CONSTRAINT_TYPE_ACN:
				$arrAccounts = self::_findAccountsForACN($mixConstraint, $bolIncludeArchived);
				break;

			case self::CONSTRAINT_TYPE_EMAIL:
				$arrAccounts = self::_findAccountsForEmail($mixConstraint, $bolIncludeArchived);
				break;
				
			default:
				// We don't know what sort of search it is
				if (is_numeric($mixConstraint))
				{
					// It's numeric.  Test for Account Id, InvoiceId, ABN, ACN and FNN
					if (self::_findAccountForAccountId($mixConstraint, $bolIncludeArchived) !== NULL)
					{
						// The account could be found
						$arrAccounts[] = intval($mixConstraint);
					}
					if (($intAccountId = self::_findAccountForInvoiceId($mixConstraint, $bolIncludeArchived)) !== NULL)
					{
						$arrAccounts[] = $intAccountId;
					}
					if (IsValidFNN($mixConstraint))
					{
						$arrAccounts = array_merge($arrAccounts, self::_findAccountsForFNN($mixConstraint, $bolIncludeArchived));
					}
					// Check if $mixSearchItem is a valid ABN
					//TODO! actually implement this validation.  (It currently only lives in a class within the oblib stuff.  It should be moved to lib/framework/functions.php)
					if (TRUE)
					{
						$arrAccounts = array_merge($arrAccounts, self::_findAccountsForABN($mixConstraint, $bolIncludeArchived));
					}
					//Check if $mixSearchItem is a valid ACN
					//TODO! actually implement this validation.  (we don't currently do any ACN validation)
					if (TRUE)
					{
						$arrAccounts = array_merge($arrAccounts, self::_findAccountsForACN($mixConstraint, $bolIncludeArchived));
					}
				}
				else
				{
					// It must be a string.  Test for AccountName and FNN
					$arrAccounts = array_merge($arrAccounts, self::_findAccountsForAccountName($mixConstraint, $bolIncludeArchived));
					if (IsValidFNN($mixConstraint))
					{
						$arrAccounts = array_merge($arrAccounts, self::_findAccountsForFNN($mixConstraint, $bolIncludeArchived));
					}
					$arrAccounts = array_merge($arrAccounts, self::_findAccountsForEmail($mixConstraint, $bolIncludeArchived));
				}
				
				break;
		}
		
		return array_values(array_unique($arrAccounts));
	}

	private static function _findServicesFor($mixConstraint, $intConstraintType, $bolIncludeArchived)
	{
		//TODO! Implement this
		$arrServices = array();
		return array_values(array_unique($arrServices));
	}
	
	private static function _findContactsFor($mixConstraint, $intConstraintType, $bolIncludeArchived)
	{
		$arrContacts = array();
		switch ($intConstraintType)
		{
			case self::CONSTRAINT_TYPE_ACCOUNT_ID:
				$arrContacts = self::_findContactsForAccountId($mixConstraint, $bolIncludeArchived);
				break;
			
			case self::CONSTRAINT_TYPE_CONTACT_NAME:
				$arrContacts = self::_findContactsForContactName($mixConstraint, $bolIncludeArchived);
				break;
				
			case self::CONSTRAINT_TYPE_EMAIL:
				$arrContacts = self::_findContactsForEmail($mixConstraint, $bolIncludeArchived);
				break;
			
			default:
				if (is_numeric($mixConstraint))
				{
					$arrContacts = self::_findContactsForAccountId($mixConstraint, $bolIncludeArchived);
				}
				else
				{
					$arrContacts = self::_findContactsForContactName($mixConstraint, $bolIncludeArchived);
					$arrContacts = array_merge($arrContacts, self::_findContactsForEmail($mixConstraint, $bolIncludeArchived));
				}
				break;
		}
		
		return array_values(array_unique($arrContacts));
	}
	
	// If $strFieldToKeep is specified, then this field is extracted from each record of the record set, and the function returns an indexed array of only these values
	// If $strFieldToKeep is NULL, then the function returns the entire recordset
	private static function _find($strQuery, $strFieldToKeep=NULL)
	{
		static $qryQuery;
		if (!isset($qryQuery))
		{
			$qryQuery = new Query();
		}

		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to execute query: $strQuery - " . $qryQuery->Error());
		}

		$arrRecordSet = array();

		if ($strFieldToKeep === NULL)
		{
			while ($arrRecord = $objRecordSet->fetch_assoc())
			{
				$arrRecordSet[] = $arrRecord;
			}
		}
		else
		{
			while ($arrRecord = $objRecordSet->fetch_assoc())
			{
				
				$arrRecordSet[] = $arrRecord[$strFieldToKeep];
			}
		}

		return $arrRecordSet;
	}
	

	// Returns NULL if the account cannot be found
	// Returns the account id if the account can be found
	private static function _findAccountForAccountId($intAccountId, $bolIncludeArchived)
	{
		if (!is_numeric($intAccountId))
		{
			throw new Exception("Invalid Account Id: ". $intAccountId);
		}
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND Archived != ". ACCOUNT_STATUS_ARCHIVED;
		
		$strQuery = "SELECT Id FROM Account WHERE Id = $intAccountId $strArchivedConstraint";

		$arrRecordSet = self::_find($strQuery);
		
		if (count($arrRecordSet) == 0)
		{
			// Could not find the account
			return NULL;
		}
		return $intAccountId;
	}
	
	// Returns NULL if the service cannot be found
	// Returns the service id if the service can be found
	private static function _findAccountForServiceId($intServiceId, $bolIncludeArchived)
	{
		if (!is_numeric($intServiceId))
		{
			throw new Exception("Invalid Service Id: ". $intServiceId);
		}

		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND a.Archived != ". ACCOUNT_STATUS_ARCHIVED;
		
		$strQuery = "	SELECT DISTINCT a.Id AS AccountId 
						FROM Account AS a INNER JOIN Service AS s ON a.Id = s.Account
						WHERE s.Id = $intServiceId $strArchivedConstraint";
		
		$arrRecordSet = self::_find($strQuery, "AccountId");
		
		if (count($arrRecordSet) == 0)
		{
			return NULL;
		}
		return $arrRecordSet[0];
	}
	
	// Returns array of all Accounts (account ids) matching the name passed.  If there aren't any, then this will be an empty array
	private static function _findAccountsForAccountName($strAccountName, $bolIncludeArchived)
	{
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND Archived != ". ACCOUNT_STATUS_ARCHIVED;

		// Tokenise the contact name
		$arrBusinessNameConditionParts	= array();
		$arrTradingNameConditionParts	= array();
		$strToken = strtok($strAccountName, " \n\t,");
		while ($strToken !== FALSE)
		{
			$arrBusinessNameConditionParts[]	= "BusinessName LIKE '%$strToken%'";
			$arrTradingNameConditionParts[]		= "TradingName LIKE '%$strToken%'";
			$strToken = strtok(" \n\t,");
		}
		
		$strBusinessNameCondition	= implode(" AND ", $arrBusinessNameConditionParts);
		$strTradingNameCondition	= implode(" AND ", $arrTradingNameConditionParts);

		$strQuery = "	SELECT Id
						FROM Account
						WHERE (($strBusinessNameCondition) OR ($strTradingNameCondition)) $strArchivedConstraint
						ORDER BY BusinessName ASC, TradingName ASC, Id DESC
						LIMIT ". self::MAX_RECORDS;

		return self::_find($strQuery, "Id");
	}
	
	// Returns array of all Accounts (account ids) that have at some point owned the FNN.  If there aren't any, then this will be an empty array
	// This will also account for Indial100 ranges
	private static function _findAccountsForFNN($strFNN, $bolIncludeArchived)
	{
		$strFnnIndial = substr($strFNN, 0, -2) . '__';
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND a.Archived != ". ACCOUNT_STATUS_ARCHIVED;
		
		$strQuery = "	SELECT DISTINCT a.Id AS AccountId
						FROM Account AS a INNER JOIN Service AS s ON a.Id = s.Account
						WHERE ((s.FNN = '$strFNN' OR (s.Indial100 = 1 AND s.FNN LIKE '$strFnnIndial')) AND (s.ClosedOn IS NULL OR s.CreatedOn <= s.ClosedOn)) $strArchivedConstraint
						LIMIT ". self::MAX_RECORDS;
		
		return self::_find($strQuery, "AccountId");
	}
	
	// returns the account id if it can be found, else returns NULL
	private static function _findAccountForInvoiceId($intInvoiceId, $bolIncludeArchived)
	{
		if (!is_numeric($intInvoiceId))
		{
			throw new Exception("Invalid Invoice Id: ". $intInvoiceId);
		}
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND a.Archived != ". ACCOUNT_STATUS_ARCHIVED;
		
		$strQuery = "	SELECT DISTINCT a.Id AS AccountId
						FROM Account AS a INNER JOIN Invoice AS i ON a.Id = i.Account
						WHERE i.Id = $intInvoiceId $strArchivedConstraint";
		
		$arrRecordSet = self::_find($strQuery, "AccountId");
		if (count($arrRecordSet) == 0)
		{
			return NULL;
		}
		return $arrRecordSet[0];
	}
	
	private static function _findAccountsForABN($strABN, $bolIncludeArchived)
	{
		$strABN = str_replace(" ", "", $strABN);
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND Archived != ". ACCOUNT_STATUS_ARCHIVED;
		$strQuery = "	SELECT DISTINCT Id
						FROM Account
						WHERE REPLACE(ABN, ' ', '') = '$strABN' $strArchivedConstraint
						LIMIT ". self::MAX_RECORDS;
		
		return self::_find($strQuery, "Id");
	}
	
	private static function _findAccountsForACN($strACN, $bolIncludeArchived)
	{
		$strACN = str_replace(" ", "", $strACN);
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND Archived != ". ACCOUNT_STATUS_ARCHIVED;
		$strQuery = "	SELECT DISTINCT Id
						FROM Account
						WHERE REPLACE(ACN, ' ', '') = '$strACN' $strArchivedConstraint
						LIMIT ". self::MAX_RECORDS;
		
		return self::_find($strQuery, "Id");
	}
	
	// Returns array of all Contacts matching the name passed.  If there aren't any, then this will be an empty array
	private static function _findContactsForContactName($strContactName, $bolIncludeArchived)
	{
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND Archived != 1";

		// Tokenise the contact name
		$arrNameConditionParts = array();
		$strToken = strtok($strContactName, " \n\t,");
		while ($strToken !== FALSE)
		{
			$arrNameConditionParts[] = "FullName LIKE '%$strToken%'";
			$strToken = strtok(" \n\t,");
		}
		
		$strNameCondition = implode(" AND ", $arrNameConditionParts);
				
		$strQuery = "	SELECT Id, LastName, FirstName, Title, CONCAT(FirstName, ' ', LastName) AS FullName
						FROM Contact
						WHERE TRUE $strArchivedConstraint
						GROUP BY Id, LastName, FirstName, Title
						HAVING ($strNameCondition)
						ORDER BY LastName ASC, FirstName ASC, Id DESC
						LIMIT ". self::MAX_RECORDS;

		return self::_find($strQuery, "Id");
	}
	
	// Returns array of all Contacts that match the email address passed.  If there aren't any, then this will be an empty array
	private static function _findContactsForEmail($strEmail, $bolIncludeArchived)
	{
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND Archived != 1";
		$strQuery = "	SELECT DISTINCT Id
						FROM Contact
						WHERE Email LIKE '%$strEmail%' $strArchivedConstraint
						ORDER BY LastName ASC, FirstName ASC, Id DESC
						LIMIT ". self::MAX_RECORDS;

		return self::_find($strQuery, "Id");
	}

	// Returns array of AccountIds for the Contact Email address.  If there aren't any then it returns an empty array
	private static function _findAccountsForEmail($strEmail, $bolIncludeArchived)
	{
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND a.Archived != ". ACCOUNT_STATUS_ARCHIVED;
		$strQuery = "	SELECT DISTINCT a.Id AS AccountId
						FROM Account AS a INNER JOIN Contact AS c ON (c.CustomerContact = 1 AND a.AccountGroup = c.AccountGroup) OR (c.Account = a.Id) OR (c.Id = a.PrimaryContact)
						WHERE c.Email LIKE '%$strEmail%' $strArchivedConstraint
						LIMIT ". self::MAX_RECORDS;
		
		return self::_find($strQuery, "AccountId");
	}
	
	// Returns array of AccountIds for the Contact Id passed.  If there aren't any then it returns an empty array
	private static function _findAccountsForContactId($intContactId, $bolIncludeArchived)
	{
		if (!is_numeric($intContactId))
		{
			throw new Exception("Invalid Contact Id: ". $intContactId);
		}
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND a.Archived != ". ACCOUNT_STATUS_ARCHIVED;
		$strQuery = "	SELECT DISTINCT a.Id AS AccountId
						FROM Account AS a INNER JOIN Contact AS c ON (c.CustomerContact = 1 AND a.AccountGroup = c.AccountGroup) OR (c.Account = a.Id) OR (c.Id = a.PrimaryContact)
						WHERE c.Id = $intContactId $strArchivedConstraint
						LIMIT ". self::MAX_RECORDS;
		
		return self::_find($strQuery, "AccountId");
	}

	// Finds all contacts that can view the account
	private static function _findContactsForAccountId($intAccountId, $bolIncludeArchived)
	{
		if (!is_numeric($intAccountId))
		{
			throw new Exception("Invalid Account Id: ". $intAccountId);
		}
		
		$strArchivedConstraint = ($bolIncludeArchived)? "" : "AND c.Archived != 1";
		$strQuery = "	SELECT DISTINCT c.Id AS ContactId
						FROM Account AS a INNER JOIN Contact AS c ON (c.CustomerContact = 1 AND a.AccountGroup = c.AccountGroup) OR (c.Account = a.Id) OR (c.Id = a.PrimaryContact)
						WHERE a.Id = $intAccountId $strArchivedConstraint
						LIMIT ". self::MAX_RECORDS;
		
		return self::_find($strQuery, "ContactId");
	}
	
	
}
 
?>
