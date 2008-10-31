<?php

//NOTE: this uses the MDB2 database connection functionality.  All code that interacts with the Flex database should be using the Orginal "StatementSelect" functionality, so that
// it can all be used within the one Transaction.

class Dealer
{
	// The key to this array will be the tidy names as defined by the getColumns function
	private	$_arrProperties	= array();

	private $_bolSaved = NULL;
	
	private static $_arrPaginationDetails = array(	"TotalRecordCount"	=> NULL,
													"PageRecordCount"	=> NULL,
													"CurrentOffset"		=> NULL,
													"FirstOffset"		=> NULL,
													"PreviousOffset"	=> NULL,
													"NextOffset"		=> NULL,
													"LastOffset"		=> NULL
												);

	// Tidy names must be used for the keys of $arrProperties
	public function __construct($arrProperties=array())
	{
		$arrTidyNames = array_keys(self::getColumns());
		foreach ($arrTidyNames as $strTidyName)
		{
			$this->_arrProperties[$strTidyName] = (array_key_exists($strTidyName, $arrProperties))? $arrProperties[$strTidyName] : NULL;
		}
	}
	
	// Returns TRUE if the dealer, can use the upline dealer
	public function canHaveUpLineManager($intManagerDealerId)
	{
		//TODO! implement this
		return TRUE;
	}
	
	public function getName($bolIncludeTitle=FALSE)
	{
		$strName = trim(trim($this->firstName) ." ". trim($this->lastName));
		
		if ($bolIncludeTitle && $this->titleId !== NULL)
		{
			if (($objTitle = Contact_Title::getForId($this->titleId)) !== NULL)
			{
				$strName = $objTitle->name . $strName;
			}
		}
		return $strName;
	}

	private static function getFor($strWhere=NULL, $strOrderBy=NULL, $intMaxRecords=NULL, $intOffset=NULL)
	{
		$strWhere	= ($strWhere === NULL)? "" : "WHERE $strWhere";
		$strOrderBy	= ($strOrderBy === NULL)? "" : "ORDER BY $strOrderBy";
		if ($intMaxRecords === NULL)
		{
			// No max number of records has been specified, so return them all, and disregard $intOffset
			$strLimit = "";
			$strCalcFoundRows = "";
		}
		else
		{
			$strCalcFoundRows = "SQL_CALC_FOUND_ROWS";
			$strLimit = "LIMIT $intMaxRecords";
			if ($intOffset !== NULL)
			{
				$strLimit .= " OFFSET $intOffset";
			}
			else
			{
				$intOffset = 0;
			}
		}
		
		$arrColumns = self::getColumns();
		$arrColumnParts = array();
		foreach ($arrColumns as $strTidyName=>$strColumn)
		{
			$arrColumnParts[] = "$strColumn AS $strTidyName";
		}
		$strColumns = implode(", ", $arrColumnParts);
		
		$strQuery = "SELECT $strCalcFoundRows $strColumns FROM dealer $strWhere $strOrderBy $strLimit;";

		$objDB = Data_Source::get();
		
		$mixResult = $objDB->queryAll($strQuery, self::getColumnDataTypes(), MDB2_FETCHMODE_ASSOC);
		if (PEAR::isError($mixResult))
		{
			throw new Exception("Failed to retrieve dealer records using query: '$strQuery' - ". $mixResult->getMessage());
		}
		
		$arrDealers = array();
		foreach ($mixResult as $arrRecord)
		{
			$arrDealers[$arrRecord['id']] = new self($arrRecord);
		}
		
		// Calculate pagination details
		if ($intMaxRecords === NULL)
		{
			// All records were retrieved
			self::$_arrPaginationDetails = array(	"TotalRecordCount"	=> count($arrDealers),
													"PageRecordCount"	=> count($arrDealers),
													"CurrentOffset"		=> 0,
													"FirstOffset"		=> 0,
													"PreviousOffset"	=> 0,
													"NextOffset"		=> 0,
													"LastOffset"		=> 0
												);
		}
		else
		{
			$mixResult = $objDB->query("SELECT FOUND_ROWS();", array("integer"));
			if (PEAR::isError($mixResult))
			{
				throw new Exception("Failed to retrieve pagination details for query: '$strQuery' - ". $mixResult->getMessage());
			}
			$intTotalRecordCount	= $mixResult->fetchOne();
			$intPageRecordCount		= count($arrDealers);
			$intCurrentOffset		= $intOffset;
			$intFirstOffset			= 0;
			$intPreviousOffset		= max($intCurrentOffset - $intMaxRecords, 0);
			$intLastOffset			= max(floor(($intTotalRecordCount - 1) / $intMaxRecords) * $intMaxRecords, 0);
			$intNextOffset			= min($intCurrentOffset + $intMaxRecords, $intLastOffset);
			
			self::$_arrPaginationDetails = array(	"TotalRecordCount"	=> $intTotalRecordCount,
													"PageRecordCount"	=> $intPageRecordCount,
													"CurrentOffset"		=> $intCurrentOffset,
													"FirstOffset"		=> $intFirstOffset,
													"PreviousOffset"	=> $intPreviousOffset,
													"NextOffset"		=> $intNextOffset,
													"LastOffset"		=> $intLastOffset
												);
		}
		
		return $arrDealers;
	}
	
	// returns an array defining the pagination details of the last 
	// This relates to the last call to getFor()
	public static function getPaginationDetails()
	{
		return self::$_arrPaginationDetails;
	}

	public static function getForId($intId)
	{
		$arrDealers = self::getFor("id = $intId");
		return (count($arrDealers) == 1) ? current($arrDealers) : NULL;
	}

	public static function getAll($arrFilter=NULL, $arrSort=NULL, $intLimit=NULL, $intOffset=NULL)
	{
		$arrWhereParts		= array();
		$arrOrderByParts	= array();
		$arrColumns			= self::getColumns();
		$arrColumnTypes		= self::getColumnDataTypes();
		$objDB				= Data_Source::get();

		// Build WHERE clause
		if (is_array($arrFilter))
		{
			foreach ($arrFilter as $strColumn=>$arrStyle)
			{
				if (!array_key_exists($strColumn, $arrColumns))
				{
					// The column doesn't exist in the dealer table
					continue;
				}
				$strColumnType = $arrColumnTypes[$strColumn];
				
				switch($arrStyle['Comparison'])
				{
					case '=':
						if ($arrStyle['Value'] === NULL || (is_array($arrStyle['Value']) && empty($arrStyle['Value'])))
						{
							$arrWhereParts[] = $arrColumns[$strColumn] ." IS NULL";
						}
						else if (is_array($arrStyle['Value']))
						{
							$arrValues = array();
							foreach ($arrStyle['Value'] as $mixValue)
							{
								$arrValues[] = $objDB->quote($mixValue, $strColumnType);
							}
							
							$arrWhereParts[] = $arrColumns[$strColumn] ." IN (". implode(", ", $arrValues) .")";
						}
						else
						{
							$arrWhereParts[] = $arrColumns[$strColumn] ." = ". $objDB->quote($arrStyle['Value'], $strColumnType);
						}
				}
			}
		}
		
		$strWhere = (count($arrWhereParts) > 0)? implode(" AND ", $arrWhereParts) : NULL;
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				if (array_key_exists($strColumn, $arrColumns))
				{
					$arrOrderByParts[] = "{$arrColumns[$strColumn]} " . ($bolAsc ? "ASC" : "DESC");
				}
			}
		}
		$strOrderBy = (count($arrOrderByParts) > 0)? implode(", ", $arrOrderByParts) : NULL;
		
		return self::getFor($strWhere, $strOrderBy, $intLimit, $intOffset);
	}

	// Retrieves all dealers who manager other dealers
	public static function getManagers()
	{
		return self::getFor("id IN (SELECT DISTINCT up_line_id FROM dealer WHERE up_line_id IS NOT NULL)", "first_name ASC, last_name ASC");
	}
	
	// Retrieves all dealers who can safely be made the manager of $intDealerId
	public static function getAllowableManagersForDealer($intDealerId)
	{
		// All dealers who aren't decendents of $intDealerId, can be made the manager of $intDealerId
		$objDealer = self::getForId($intDealerId);
		$arrExcludedDealers = self::getDealersUnderManager($objDealer);
		
		$arrExcludedDealerIds = array($objDealer->id);
		foreach ($arrExcludedDealers as $objDealer)
		{
			$arrExcludedDealerIds[] = $objDealer->id;
		}
		
		// $arrExcludedDealerIds should always contain $intDealerId, so we don't have to worry about it being empty
		$strWhere = "id NOT IN (". implode(", ", $arrExcludedDealerIds) .") AND dealer_status_id = ". Dealer_Status::ACTIVE;
		
		return self::getFor($strWhere, "CONCAT(first_name, ' ', last_name) ASC");
	}
	
	// Returns array containing all Dealers under the management hierarchy of $objDealer
	// This will NOT include $objDealer in the array
	// the array is not associative
	public static function getDealersUnderManager($objDealer)
	{
		$arrManagedDealers = self::getFor("up_line_id = {$objDealer->id}");
		
		// Add the dealers immediately under $objDealer's management to the array of all dealers under $objDealer's management
		$arrDealers = $arrManagedDealers;
		
		// For each dealer that is immediately under $objDealer's management, find the dealers under their management (the recursion part)
		foreach ($arrManagedDealers as $objManagedDealer)
		{
			$arrDealers = array_merge($arrDealers, self::getDealersUnderManager($objManagedDealer));
		}
		return $arrDealers;
	}
	
	// Returns array containing all Dealers under the management hierarchy of $objDealer
	// This will include $objDealer in the array
	// the array is not associative
	public static function getDealersUnderManagerOld($objDealer)
	{
		$arrDealers = array($objDealer);
		
		$arrManagedDealers = self::getFor("up_line_id = {$objDealer->id}");
		foreach ($arrManagedDealers as $objManagedDealer)
		{
			$arrDealers = array_merge($arrDealers, self::getDealersUnderManager($objManagedDealer));
		}
		return $arrDealers;
	}
	
	public function save()
	{
		if ($this->_bolSaved)
		{
			// Nothing to save
			return TRUE;
		}
		
		// Make sure the dealer's upLineManager does not cause recursion in the management tree
		if ($this->upLineId !== NULL && !$this->canHaveUpLineManager($this->upLineId))
		{
			throw new Exception("Setting the upline manager to dealer id: {$this->upLineId} would cause recursion in the management tree");
		}
		
		$arrColumns		= self::getColumns();
		$arrColumnTypes	= self::getColumnDataTypes();
		$objDB			= Data_Source::get();
		
		// Do we have an Id for this instance?
		if ($this->id !== NULL)
		{
			// Update
			
			// Build the SET clause for the UPDATE SQL statement
			$arrSetClauseParts = array();
			foreach ($this->_arrProperties as $strName=>$mixValue)
			{
				if ($strName == 'id')
				{
					continue;
				}
				$arrSetClauseParts = "{$arrColumns[$strName]} = ". $objDB->quote($mixValue, $arrColumnTypes[$strName]);
			}
			$strSetClause = implode(", ", $arrSetClauseParts);
			
			$strUpdate = "UPDATE dealer SET $strSetClause WHERE id = {$this->id};";
			
			$mixResult = $objDB->query($strUpdate);
			if (PEAR::isError($mixResult))
			{
				throw new Exception("Failed to update dealer record with id: {$this->id}, SQL: '$strUpdate' - ". $mixResult->getMessage());
			}
		} 
		else
		{
			// Insert
			$arrValuesClauseParts = array();
			foreach ($this->_arrProperties as $strName=>$mixValue)
			{
				$arrValuesClauseParts = $objDB->quote($mixValue, $arrColumnTypes[$strName]);
			}
			$strValuesClause	= implode(", ", $arrValuesClauseParts);
			$strColumns			= implode(", ", $arrColumns);
			
			$strInsert = "INSERT INTO dealer ($strColumns) VALUES ($strValuesClause);";
			
			$mixResult = $objDB->query($strInsert);
			if (PEAR::isError($mixResult))
			{
				throw new Exception("Failed to insert new dealer record using SQL: '$strInsert' - ". $mixResult->getMessage());
			}
			
			// Store the new value for the id of the dealer
			$mixResult = $objDB->lastInsertID("database_version", "id");
			if (PEAR::isError($mixResult))
			{
				throw new Exception("Failed to retrieve the id of the newly inserted dealer record - ". $mixResult->getMessage());
			}
			$this->id = $mixResult;
		}
		
		$this->_bolSaved = TRUE;
		return TRUE;
	}
	
	// The keys are the tidy names and the values are the actual table column names
	protected static function getColumns()
	{
		return array(
			"id"					=> "id",
			"upLineId"				=> "up_line_id",
			"username"				=> "username",
			"password"				=> "password",
			"canVerify"				=> "can_verify",
			"firstName"				=> "first_name",
			"lastName"				=> "last_name",
			"titleId"				=> "title_id",
			"businessName"			=> "business_name",
			"tradingName"			=> "trading_name",
			"abn"					=> "abn",
			"abnRegistered"			=> "abn_registered",
			"addressLine1"			=> "address_line_1",
			"addressLine2"			=> "address_line_2",
			"suburb"				=> "suburb",
			"stateId"				=> "state_id",
			"countryId"				=> "country_id",
			"postcode"				=> "postcode",
			"postalAddressLine1"	=> "postal_address_line_1",
			"postalAddressLine2"	=> "postal_address_line_2",
			"postalSuburb"			=> "postal_suburb",
			"postalStateId" 		=> "postal_state_id",
			"postalCountryId" 		=> "postal_country_id",
			"postalPostcode"		=> "postal_postcode",
			"phone"					=> "phone",
			"mobile"				=> "mobile",
			"fax"					=> "fax",
			"email"					=> "email",
			"commissionScale"		=> "commission_scale",
			"royaltyScale"			=> "royalty_scale",
			"bankAccountBsb"		=> "bank_account_bsb",
			"bankAccountNumber"		=> "bank_account_number",
			"bankAccountName"		=> "bank_account_name",
			"gstRegistered"			=> "gst_registered",
			"terminationDate"		=> "termination_date",
			"dealerStatusId"		=> "dealer_status_id",
			"createdOn"				=> "created_on",
			"employeeId"			=> "employee_id"
		);
	}

	// The keys are the tidy names and the values are the actual table column names
	protected static function getColumnDataTypes()
	{
		return array(
			"id"					=> "integer",
			"upLineId"				=> "integer",
			"username"				=> "text",
			"password"				=> "text",
			"canVerify"				=> "integer",
			"firstName"				=> "text",
			"lastName"				=> "text",
			"titleId"				=> "integer",
			"businessName"			=> "text",
			"tradingName"			=> "text",
			"abn"					=> "text",
			"abnRegistered"			=> "integer",
			"addressLine1"			=> "text",
			"addressLine2"			=> "text",
			"suburb"				=> "text",
			"stateId"				=> "integer",
			"countryId"				=> "integer",
			"postcode"				=> "text",
			"postalAddressLine1"	=> "text",
			"postalAddressLine2"	=> "text",
			"postalSuburb"			=> "text",
			"postalStateId" 		=> "integer",
			"postalCountryId" 		=> "integer",
			"postalPostcode"		=> "text",
			"phone"					=> "text",
			"mobile"				=> "text",
			"fax"					=> "text",
			"email"					=> "text",
			"commissionScale"		=> "integer",
			"royaltyScale"			=> "integer",
			"bankAccountBsb"		=> "text",
			"bankAccountNumber"		=> "text",
			"bankAccountName"		=> "text",
			"gstRegistered"			=> "integer",
			"terminationDate"		=> "text",
			"dealerStatusId"		=> "integer",
			"createdOn"				=> "text",
			"employeeId"			=> "integer"
		);
	}
	

	public function __get($strName)
	{
		if (array_key_exists($strName, $this->_arrProperties))
		{
			return $this->_arrProperties[$strName];
		}
		else
		{
			throw new Exception("Unknown property: $strName for object of class: ".__CLASS__);
		}
	}

	// This is only used to set attributes relating to the dealer table
	// It will throw an exception if it doesn't know of the property to set
	// $strName should be the tidy name of the property
	public function __set($strName, $mixValue)
	{
		if (array_key_exists($strName, $this->_arrProperties))
		{
			// The thing to set is one of the propper attributes of the dealer table
			if ($this->_arrProperties[$strName] !== $mixValue)
			{
				// This property is being updated
				$this->_bolSaved = FALSE;
			}
			
			// Set the property
			$this->_arrProperties[$strName]	= $mixValue;
		}
		else
		{
			// The thing being set doesn't relate to an attribute of the dealer table
			throw new Exception("Class ".__CLASS__." does not have member variable: $strName");
		}
	}

	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 * 
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray()
	{
		return $this->_arrProperties;
	}
	
	
	public static function parseDealerDetails($arrDetails)
	{
		
	}
}

?>
