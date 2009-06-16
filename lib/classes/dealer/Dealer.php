<?php

class Dealer
{
	const SYSTEM_DEALER_ID = 1;
	
	// The key to this array will be the tidy names as defined by the getColumns function
	private	$_arrProperties	= array();

	private $_arrCustomerGroupIds	= NULL;
	private $_arrRatePlanIds		= NULL;
	private $_arrSaleTypeIds		= NULL;

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
		$this->_arrProperties = self::getColumns();
		foreach ($this->_arrProperties as $strTidyName=>$mixValue)
		{
			$this->{$strTidyName} = (array_key_exists($strTidyName, $arrProperties))? $arrProperties[$strTidyName] : NULL;
		}
	}
	
	// Returns TRUE if the dealer, can use the upline dealer
	public static function canHaveUpLineManager($intDealerId, $intManagerDealerId)
	{
		if ($intDealerId === NULL)
		{
			// The dealer is new, and therefor can't possibly cause a recursion problem with the management hierarchy
			return TRUE;
		}

		// Find the dealers that are currently under the management of this dealer
		$objDealer = self::getForId($intDealerId);
		$arrSubordinates = $objDealer->getSubordinates();
		
		// Make sure $intManagerDealerId isn't in this array
		foreach ($arrSubordinates as $objSubordinate)
		{
			if ($objSubordinate->id == $intManagerDealerId)
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	public function getName($bolIncludeTitle=FALSE)
	{
		$strName = trim(trim($this->firstName) ." ". trim($this->lastName));
		
		if ($bolIncludeTitle && $this->titleId !== NULL)
		{
			if (($objTitle = Contact_Title::getForId($this->titleId)) !== NULL)
			{
				$strName = $objTitle->name . " ". $strName;
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

		$qryQuery = new Query();
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve dealer records using query: '$strQuery' - ". $qryQuery->Error());
		}
		
		$arrDealers = array();
		while ($arrRecord = $objRecordSet->fetch_assoc())
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
			$objRecordSet = $qryQuery->Execute("SELECT FOUND_ROWS() AS RowCount;");
			if (!$objRecordSet)
			{
				throw new Exception("Failed to retrieve pagination details for query: '$strQuery' - ". $qryQuery->Error());
			}
			
			$arrRecord = $objRecordSet->fetch_assoc();
			
			$intTotalRecordCount	= $arrRecord['RowCount'];
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

	public static function getForId($intId, $bolExceptionOnNotFound=FALSE)
	{
		$arrDealers = self::getFor("id = $intId");
		
		if (count($arrDealers) == 1)
		{
			return current($arrDealers);
		}
		elseif ($bolExceptionOnNotFound)
		{
			throw new Exception("Can't find dealer with id: $intId");
		}
		else
		{
			return NULL;
		}
	}
	
	// Returns the dealer record, based on the employeeId
	public static function getForEmployeeId($intEmployeeId)
	{
		$arrDealers = self::getFor("employee_id = $intEmployeeId");
		return (count($arrDealers) == 1)? current($arrDealers) : NULL;
	}

	public static function getAll($mixFilter=NULL, $arrSort=NULL, $intLimit=NULL, $intOffset=NULL)
	{
		$arrOrderByParts	= array();
		$arrColumns			= self::getColumns();
		$arrColumnTypes		= self::getColumnDataTypes();
		$objDB				= new Query();

		// Build WHERE clause
		if (is_array($mixFilter))
		{
			$arrWhereParts = array();
			foreach ($mixFilter as $strColumn=>$arrStyle)
			{
				if (!array_key_exists($strColumn, $arrColumns))
				{
					// The column doesn't exist in the dealer table
					continue;
				}
				$strColumnType = $arrColumnTypes[$strColumn];
				
				switch ($arrStyle['Comparison'])
				{
					case '!=':
					case '=':
						if ($arrStyle['Value'] === NULL || (is_array($arrStyle['Value']) && empty($arrStyle['Value'])))
						{
							$strNegate = ($arrStyle['Comparison'] == '!=')? "NOT" : "";
							$arrWhereParts[] = $arrColumns[$strColumn] ." IS $strNegate NULL";
						}
						elseif (is_array($arrStyle['Value']))
						{
							$arrValues = array();
							foreach ($arrStyle['Value'] as $mixValue)
							{
								// prepare the values for being used in sql code
								switch ($strColumnType)
								{
									case 'integer':
									case 'boolean':
										$arrValues[] = intval($mixValue);
										break;
									case 'text':
										$arrValues[] = "'". $objDB->EscapeString($mixValue) ."'";
										break;
									default:
										throw new exception(__CLASS__ ."::". __METHOD__ ." - don't know how to handle data type, '$strColumnType'");
								}
							}
							
							$strNegate = ($arrStyle['Comparison'] == '!=')? "NOT" : "";
							$arrWhereParts[] = $arrColumns[$strColumn] ." $strNegate IN (". implode(", ", $arrValues) .")";
						}
						else
						{
							// prepare the values for being used in sql code
							switch ($strColumnType)
							{
								case 'integer':
								case 'boolean':
									$mixValue = intval($arrStyle['Value']);
									break;
								case 'text':
									$mixValue = "'". $objDB->EscapeString($arrStyle['Value']) ."'";
									break;
								default:
									throw new exception(__CLASS__ ."::". __METHOD__ ." - don't know how to handle data type, '$strColumnType'");
							}
							$strComparison = ($arrStyle['Comparison'] == '!=')? "!=" : "=";
							$arrWhereParts[] = $arrColumns[$strColumn] ." $strComparison $mixValue";
						}
						break;
				}
			}
			$strWhere = (count($arrWhereParts) > 0)? implode(" AND ", $arrWhereParts) : NULL;
		}
		elseif (is_string($mixFilter))
		{
			$strWhere = $mixFilter;
		}
		else
		{
			$strWhere = NULL;
		}
		
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
	
	// Retrieves all dealers who have no upline manager
	public static function getRootLevelDealers($strSort="username ASC")
	{
		return self::getFor("up_line_id IS NULL AND id != ". self::SYSTEM_DEALER_ID, $strSort);
	}

	// Retrieves all top-level Dealers (Call Centres)
	public static function getCallCentres()
	{
		return self::getFor("up_line_id IS NULL AND carrier_id IS NOT NULL AND dealer_status_id = ". Dealer_Status::ACTIVE);
	}
	
	// Retrieves all dealers who can safely be made the manager of $intDealerId
	public static function getAllowableManagersForDealer($intDealerId)
	{
		if ($intDealerId === NULL)
		{
			// All dealers can potentially be the manager of a new dealer (except the system dealer)
			return self::getFor("id != ". self::SYSTEM_DEALER_ID, "CONCAT(first_name, ' ', last_name) ASC");
		}
		
		// All dealers who aren't decendents of $intDealerId, can be made the manager of $intDealerId
		$objDealer = self::getForId($intDealerId);
		$arrExcludedDealers =  $objDealer->getSubordinates();
		
		$arrExcludedDealerIds = array($objDealer->id);
		foreach ($arrExcludedDealers as $objDealer)
		{
			$arrExcludedDealerIds[] = $objDealer->id;
		}
		
		// $arrExcludedDealerIds should always contain $intDealerId, so we don't have to worry about it being empty
		$strWhere = "id NOT IN (". implode(", ", $arrExcludedDealerIds) .") AND dealer_status_id = ". Dealer_Status::ACTIVE ." AND id != ". self::SYSTEM_DEALER_ID;
		
		return self::getFor($strWhere, "CONCAT(first_name, ' ', last_name) ASC");
	}
	
	// Returns array containing all Dealers under the management hierarchy of the current dealer
	// the array is not associative
	public function getSubordinates($bolImmediateSubordinatesOnly=FALSE)
	{
		$arrManagedDealers = self::getFor("up_line_id = {$this->id}");
		
		// Add the dealers immediately under this dealer's management to the array of all dealers under this dealer's management
		$arrDealers = $arrManagedDealers;
		
		if (!$bolImmediateSubordinatesOnly)
		{
			// For each dealer that is immediately under this dealer's management, find the dealers under their management (the recursion part)
			foreach ($arrManagedDealers as $objManagedDealer)
			{
				$arrDealers = array_merge($arrDealers, $objManagedDealer->getSubordinates());
			}
		}
		return $arrDealers;
	}
	
	// Returns an array of employee ids for all those employees who aren't yet dealers
	// These will be ordered by their name (firstname + last name)
	public static function getEmployeesWhoArentYetDealers()
	{
		$strQuery = "SELECT Id FROM Employee WHERE Archived = 0 AND Id NOT IN (SELECT DISTINCT employee_id FROM dealer WHERE employee_id IS NOT NULL) ORDER BY CONCAT(FirstName, ' ', LastName) ASC;";
		
		$qryQuery = new Query();
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve employees who are eligible to become new dealers, using query: '$strQuery' - " . $qryQuery->Error());
		}
		
		$arrEmployeeIds = array();
		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrEmployeeIds[] = intval($arrRecord['Id']);
		}
		
		return $arrEmployeeIds;
	}
	
	// Sets the customer groups that this dealer can make sales on behalf of
	// Note that this does not save this information to the database until Dealer->save() is called
	// $arrCustomerGroupIds is an array of customer group ids
	public function setCustomerGroups($arrCustomerGroupIds)
	{
		// Set the keys to the customerGroupIds as well, if they aren't already
		$arrCustomerGroupIds		= array_unique($arrCustomerGroupIds);
		$this->_arrCustomerGroupIds	= (count($arrCustomerGroupIds) > 0)? array_combine($arrCustomerGroupIds, $arrCustomerGroupIds) : Array();
		$this->_bolSaved			= FALSE;
	}
	
	// Returns array of customer group ids for the customer groups that this dealer can sell on behalf of
	public function getCustomerGroups()
	{
		if ($this->_arrCustomerGroupIds === NULL)
		{
			// The customer groups have not been retrieved from the database yet
			if ($this->id !== NULL)
			{
				$this->_arrCustomerGroupIds = Dealer_Customer_Group::getCustomerGroupsForDealer($this->id);
			}
			else
			{
				// An id hasn't been declared for this dealer yet
				$this->_arrCustomerGroupIds = array();
			}
		}
		return $this->_arrCustomerGroupIds;
	}
	
	// Sets the RatePlans that this dealer can sell
	// Note that this does not save this information to the database until Dealer->save() is called
	// $arrRatePlanIds is an array of RatePlan ids
	public function setRatePlans($arrRatePlanIds)
	{
		// Set the keys to the ratePlanIds as well, if they aren't already
		$arrRatePlanIds			= array_unique($arrRatePlanIds);
		$this->_arrRatePlanIds	= (count($arrRatePlanIds) > 0)? array_combine($arrRatePlanIds, $arrRatePlanIds) : Array();
		$this->_bolSaved		= FALSE;
	}
	
	// Returns array of RatePlan ids for the RatePlans that this dealer can sell
	public function getRatePlans()
	{
		if ($this->_arrRatePlanIds === NULL)
		{
			// The RatePlans have not been retrieved from the database yet
			if ($this->id !== NULL)
			{
				$this->_arrRatePlanIds = Dealer_Rate_Plan::getRatePlansForDealer($this->id);
			}
			else
			{
				// An id hasn't been declared for this dealer yet
				$this->_arrRatePlanIds = array();
			}
		}
		return $this->_arrRatePlanIds;
	}
	
	// Sets the SaleTypes that this dealer can sell
	// Note that this does not save this information to the database until Dealer->save() is called
	// $arrSaleTypeIds is an array of sale_type ids
	public function setSaleTypes($arrSaleTypeIds)
	{
		// Set the keys to the saleTypeIds as well, if they aren't already
		$arrSaleTypeIds			= array_unique($arrSaleTypeIds);
		$this->_arrSaleTypeIds	= (count($arrSaleTypeIds) > 0)? array_combine($arrSaleTypeIds, $arrSaleTypeIds) : Array();
		$this->_bolSaved		= FALSE;
	}
	
	// Returns array of sale_type ids for the SaleTypes that this dealer can do
	public function getSaleTypes()
	{
		if ($this->_arrSaleTypeIds === NULL)
		{
			// The sale Types have not been retrieved from the database yet
			if ($this->id !== NULL)
			{
				$this->_arrSaleTypeIds = Dealer_Sale_Type::getSaleTypesForDealer($this->id);
			}
			else
			{
				// An id hasn't been declared for this dealer yet
				$this->_arrSaleTypeIds = array();
			}
		}
		return $this->_arrSaleTypeIds;
	}
	
	// Validates the internal state of the object against the DataSource (database).  If valid, then it is considered safe to save the object
	// Throws an exception on the first validation issue found
	// This does not currently check datatypes of properties, it checks logically validation issues such as the username being unique if amoungst active dealers, if the dealer
	// is active. and also checking that the up_line_id property will not cause recursion in the management tree 
	protected function _validate()
	{
		// Make sure all manditory fields have been set
		$arrManditoryProps = array("username", "password", "firstName", "lastName", "dealerStatusId");
		
		foreach ($arrManditoryProps as $strManProp)
		{
			if (!array_key_exists($strManProp, $this->_arrProperties) || $this->_arrProperties[$strManProp] === NULL)
			{
				throw new Exception("dealer.{$strManProp} is manditory");
			}
		}
		
		// Make sure the dealer's upLineManager does not cause recursion in the management tree
		if ($this->upLineId !== NULL && !self::canHaveUpLineManager($this->id, $this->upLineId))
		{
			throw new Exception("Setting the upline manager to dealer id: {$this->upLineId} would cause recursion in the management hierarchy");
		}
		
		// Check that the username is unique (regardles of the status of the dealer)

		// Try finding a dealer with the same username
		$arrDealers = self::getFor("username = '{$this->username}' AND dealer_status_id = ". Dealer_Status::ACTIVE);
			
		$intCount = count($arrDealers);
		
		if ($intCount > 1)
		{
			// There are multiple dealers with the same username
			// This should never happen
			throw new Exception("Multiple dealers exist with this username.  Please notify your system administrator, to rectify this problem");
		}
		elseif ($intCount == 1)
		{
			// Check if it is the current dealer
			$objDealer = current($arrDealers);
			if (!array_key_exists('id', $this->_arrProperties) || $this->_arrProperties['id'] === NULL || $this->_arrProperties['id'] != $objDealer->id)
			{
				throw new Exception("Another dealer in the database is already using this username");
			}
		}
	}
	
	public function save()
	{
		// Set the dealer properties that should be kept in sync with their upline manager, if they have one
		if ($this->upLineId !== NULL)
		{
			$objManager = self::getForId($this->upLineId, TRUE);
			
			if ($this->carrierId !== $objManager->carrierId)
			{
				$this->carrierId = $objManager->carrierId;
				$this->_bolSaved = FALSE;
			}
			if ($this->clawbackPeriod !== $objManager->clawbackPeriod)
			{
				$this->clawbackPeriod = $objManager->clawbackPeriod;
				$this->_bolSaved = FALSE;
			}
		}
		
		if ($this->_bolSaved)
		{
			// Nothing to save
			return;
		}
		
		// Validate the object
		$this->_validate();
		
		$arrColumns		= self::getColumns();
		$arrColumnTypes	= self::getColumnDataTypes();
		
		// Do we have an Id for this instance?
		if ($this->id !== NULL)
		{
			// Update
			
			// Build the SET clause for the UPDATE SQL statement
			$arrUpdate = array();
			foreach ($arrColumns as $strTidyName=>$strName)
			{
				$arrUpdate[$strName] = $this->{$strTidyName};
			}

			$updDealer = new StatementUpdateById("dealer", $arrUpdate);
			
			if ($updDealer->Execute($arrUpdate) === FALSE)
			{
				throw new Exception("Failed to update dealer record with id: {$this->id} - ". $updDealer->Error());
			}
		}
		else
		{
			// Insert
			
			// Build the VALUES clause for the INSERT SQL statement
			$arrInsert = array();
			foreach ($arrColumns as $strTidyName=>$strName)
			{
				$arrInsert[$strName] = $this->{$strTidyName};
			}
			
			$insDealer = new StatementInsert("dealer", $arrInsert);

			$mixResult = $insDealer->Execute($arrInsert);
			if ($mixResult === FALSE)
			{
				throw new Exception("Failed to create new dealer record - ". $insDealer->Error());
			}
			
			$this->id = $mixResult;
		}
		
		// Save the current state of the dealer_rate_plan, dealer_customer_group and dealer_sale_type associations, if they have been set and differ from those already stored
		if (is_array($this->_arrCustomerGroupIds))
		{
			// Check if there are differences between the object and the database
			$arrCurrentCustomerGroupIds	= Dealer_Customer_Group::getCustomerGroupsForDealer($this->id);
			
			$arrThoseNotInTheDatabase	= array_diff($this->_arrCustomerGroupIds, $arrCurrentCustomerGroupIds);
			$arrThoseNotInTheObject		= array_diff($arrCurrentCustomerGroupIds, $this->_arrCustomerGroupIds);

			if (count($arrThoseNotInTheDatabase) > 0 || count($arrThoseNotInTheObject) > 0)
			{
				// There are discrepancies, which means new relationships have been defined, and should be saved to the database
				Dealer_Customer_Group::setCustomerGroupsForDealer($this->id, $this->_arrCustomerGroupIds);
			}
		}

		if (is_array($this->_arrRatePlanIds))
		{
			// Check if there are differences between the object and the database
			$arrCurrentRatePlanIds	= Dealer_Rate_Plan::getRatePlansForDealer($this->id);
			
			$arrThoseNotInTheDatabase	= array_diff($this->_arrRatePlanIds, $arrCurrentRatePlanIds);
			$arrThoseNotInTheObject		= array_diff($arrCurrentRatePlanIds, $this->_arrRatePlanIds);
			
			if (count($arrThoseNotInTheDatabase) > 0 || count($arrThoseNotInTheObject) > 0)
			{
				// There are discrepancies, which means new relationships have been defined, and should be saved to the database
				Dealer_Rate_Plan::setRatePlansForDealer($this->id, $this->_arrRatePlanIds);
			}
		}

		if (is_array($this->_arrSaleTypeIds))
		{
			// Check if there are differences between the object and the database
			$arrCurrentSaleTypeIds	= Dealer_Sale_Type::getSaleTypesForDealer($this->id);
			
			$arrThoseNotInTheDatabase	= array_diff($this->_arrSaleTypeIds, $arrCurrentSaleTypeIds);
			$arrThoseNotInTheObject		= array_diff($arrCurrentSaleTypeIds, $this->_arrSaleTypeIds);
			
			if (count($arrThoseNotInTheDatabase) > 0 || count($arrThoseNotInTheObject) > 0)
			{
				// There are discrepancies, which means new relationships have been defined, and should be saved to the database
				Dealer_Sale_Type::setSaleTypesForDealer($this->id, $this->_arrSaleTypeIds);
			}
		}
		
		// Update those fields that should cascade down to subordinates of the dealer, if they have any subordinates
		$arrSubbies = $this->getSubordinates(TRUE);
		foreach ($arrSubbies as $objSubbie)
		{
			$objSubbie->carrierId		= $this->carrierId;
			$objSubbie->clawbackPeriod	= $this->clawbackPeriod;
			$objSubbie->save();
		}
		
		$this->_bolSaved = TRUE;
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
			"clawbackPeriod"		=> "clawback_period",
			"employeeId"			=> "employee_id",
			"carrierId"				=> "carrier_id"
		);
	}
	
	public function isActive()
	{
		return $this->dealerStatusId == Dealer_Status::ACTIVE;
	}

	// The keys are the tidy names and the values are the actual table column names
	protected static function getColumnDataTypes()
	{
		return array(
			"id"					=> "integer",
			"upLineId"				=> "integer",
			"username"				=> "text",
			"password"				=> "text",
			"canVerify"				=> "boolean",
			"firstName"				=> "text",
			"lastName"				=> "text",
			"titleId"				=> "integer",
			"businessName"			=> "text",
			"tradingName"			=> "text",
			"abn"					=> "text",
			"abnRegistered"			=> "boolean",
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
			"gstRegistered"			=> "boolean",
			"terminationDate"		=> "text",
			"dealerStatusId"		=> "integer",
			"createdOn"				=> "text",
			"clawbackPeriod"		=> "integer",
			"employeeId"			=> "integer",
			"carrierId"				=> "integer"
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
	// Note the this properly type casts the value to the correct data type for the property 
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
			$arrColumnDataTypes = self::getColumnDataTypes();
			
			// Type cast the value to the properties correct type
			if ($mixValue !== NULL)
			{
				switch ($arrColumnDataTypes[$strName])
				{
					case 'integer':
						$mixValue = intval($mixValue);
						break;
					case 'boolean':
						$mixValue = (intval($mixValue) == 1)? TRUE : FALSE;
						break;
					case 'text':
					default:
						// Assume it's a string (don't have to do anything)
				}
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
		$arrDetails = $this->_arrProperties;
		$arrDetails['customerGroupIds']	= $this->getCustomerGroups();
		$arrDetails['ratePlanIds']		= $this->getRatePlans();
		$arrDetails['saleTypeIds']		= $this->getSaleTypes();
		return $arrDetails;
	}
	
	// Returns a Dealer object representing the passed details (if the details are valid)
	// Returns array of strings defining the problems encountered with the details passed
	public static function parseDealerDetails($arrDetails)
	{
		$arrProblems	= array();
		$objDb			= Data_Source::get();
		
		// Trim all strings, and nullify anything that becomes an empty string
		foreach ($arrDetails as &$prop)
		{
			if (is_string($prop))
			{
				$prop = trim($prop);
				if ($prop == '')
				{
					$prop = NULL;
				}
			}
		}
		
		$objOldDealer	= NULL;
		if ($arrDetails['id'] != NULL)
		{
			// Existing Dealer
			$objOldDealer = Dealer::getForId($arrDetails['id']);
			
			// Copy across values that should not be changed
			$arrDetails['createdOn'] = $objOldDealer->createdOn;
			$arrDetails['employeeId'] = $objOldDealer->employeeId;

			// Copy over the password, if it wasn't changed
			if ($arrDetails['password'] === NULL)
			{
				// This property has already had the SHA1 hash applied
				$arrDetails['password'] = $objOldDealer->password;
			}
			else
			{
				// A new password has been specified.  SHA1 it
				$arrDetails['password'] = sha1($arrDetails['password']);
			}
		}
		else 
		{
			// This is a new dealer
			$arrDetails['createdOn'] = GetCurrentISODateTime();
			
			// SHA1 the password (if one has been applied)
			if ($arrDetails['password'] !== NULL)
			{
				$arrDetails['password'] = sha1($arrDetails['password']);
			}
		}

		// If the dealer is an employee then copy accross values that should never differ from their employee record
		if ($arrDetails['employeeId'] !== NULL)
		{
			$objEmployee = Employee::getForId($arrDetails['employeeId'], true);
			if ($objEmployee == NULL)
			{
				$arrProblems[] = "Could not find employee that this dealer is based on";
			}
			else
			{
				// Copy across the details that should never differ
				$arrDetails['firstName']		= (($strFirstName = trim($objEmployee->firstName)) == '')? NULL : $strFirstName;
				$arrDetails['lastName']			= (($strLastName = trim($objEmployee->lastName)) == '')? NULL : $strLastName;
				$arrDetails['username']			= (($strUsername = trim($objEmployee->username)) == '')? NULL : $strUsername;
				$arrDetails['password']			= (($strPassword = trim($objEmployee->password)) == '')? NULL : $strPassword;
				$arrDetails['phone']			= (($strPhone = trim($objEmployee->phone)) == '')? NULL : $strPhone;
				$arrDetails['mobile']			= (($strMobile = trim($objEmployee->mobile)) == '')? NULL : $strMobile;
				$arrDetails['email']			= (($strEmail = trim($objEmployee->email)) == '')? NULL : $strEmail;
				$arrDetails['dealerStatusId']	= ($objEmployee->archived == 0)? Dealer_Status::ACTIVE : Dealer_Status::INACTIVE;
			}
		}

		// Check that all manditory fields have been defined
		if ($arrDetails['firstName'] === NULL)
		{
			$arrProblems[] = "First Name was not specified";
		}
		if ($arrDetails['lastName'] === NULL)
		{
			$arrProblems[] = "Last Name was not specified";
		}
		if ($arrDetails['username'] === NULL)
		{
			$arrProblems[] = "Username was not specified";
		}
		if ($arrDetails['password'] === NULL)
		{
			$arrProblems[] = "Password was not specified";
		}
		
		// Check that the username is unique
		$strUsername	= $objDb->escape($arrDetails['username'], TRUE);
		$strWhereId		= ($arrDetails['id'] != NULL)? "AND id != ". intval($arrDetails['id']) : "";
		$arrDealers		= self::getFor("username LIKE '$strUsername' $strWhereId");
		
		if (count($arrDealers) > 0)
		{
			$arrProblems[] = 'Username is currently being used by another dealer';
		}
		
		// Check that the upLineId can be used, and doesn't form recursion
		if ($arrDetails['upLineId'] !== NULL && !self::canHaveUpLineManager($arrDetails['id'], $arrDetails['upLineId']))
		{
			$arrProblems[] = 'Up Line Manager can not be used (would cause recursion in the management hierarchy)';
		}
		
		if ($arrDetails['upLineId'] !== NULL)
		{
			// The dealer has an manager. Nullify those fields that should be kept in sync with the manager
			$arrDetails['carrierId']		= NULL;
			$arrDetails['clawbackPeriod']	= NULL;
		}
		else
		{
			// The dealer doesn't have a manager.  Force the clawback period to be an integer
			$arrDetails['clawbackPeriod'] = intval($arrDetails['clawbackPeriod']);
		}
		
		// Check that if the dealer is the default Emplyee Dealer, then they are active
		if ($arrDetails['employeeId'] !== NULL)
		{
			$objDealerConfig = Dealer_Config::getConfig();
			if ($objDealerConfig->defaultEmployeeManagerDealerId !== NULL && $arrDetails['id'] == $objDealerConfig->defaultEmployeeManagerDealerId && $arrDetails['dealerStatusId'] != Dealer_Status::ACTIVE)
			{
				// This dealer is the default Employee Manager, but is currently inactive
				$arrProblems[] = "This dealer is the Default Employee Manager, and must therefore stay active";
			}
		}
		
		// Fix up dependent properties
		if ($arrDetails['abn'] === NULL)
		{
			$arrDetails['abnRegistered'] = NULL;
		}

		if (count($arrProblems) > 0)
		{
			// Problems were encountered
			return $arrProblems;
		}
		else
		{
			// No problems were encountered
			$objDealer = new self($arrDetails);
			if (array_key_exists("saleTypes", $arrDetails) && is_array($arrDetails['saleTypes']))
			{
				$objDealer->setSaleTypes($arrDetails['saleTypes']);
			}
			if (array_key_exists("customerGroups", $arrDetails) && is_array($arrDetails['customerGroups']))
			{
				$objDealer->setCustomerGroups($arrDetails['customerGroups']);
			}
			if (array_key_exists("ratePlans", $arrDetails) && is_array($arrDetails['ratePlans']))
			{
				$objDealer->setRatePlans($arrDetails['ratePlans']);
			}
			
			return $objDealer;
		}
	}
}

?>
