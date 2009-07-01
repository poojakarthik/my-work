<?php
//----------------------------------------------------------------------------//
// Charge
//----------------------------------------------------------------------------//
/**
 * Charge
 *
 * Models a record of the Charge table
 *
 * Models a record of the Charge table
 *
 * @class	Service
 */
class Charge extends ORM_Cached
{
	protected 			$_strTableName			= "Charge";
	protected static	$_strStaticTableName	= "Charge";

	const SEARCH_CONSTRAINT_CHARGE_STATUS	= "Charge|Status";

	const ORDER_BY_ACCOUNT_NAME		= "Account|accountName";
	const ORDER_BY_ACCOUNT_ID		= "Charge|Account";
	const ORDER_BY_SERVICE_FNN		= "Service|serviceFNN";
	const ORDER_BY_CHARGE_TYPE		= "Charge|ChargeType";
	const ORDER_BY_DESCRIPTION		= "Charge|Description";
	const ORDER_BY_CREATED_ON		= "Charge|CreatedOn";
	const ORDER_BY_NATURE			= "Charge|Nature";

	// This will store the pagination details of the last call to searchFor
	private static $lastSearchPaginationDetails = null;

	public static function getLastSearchPaginationDetails()
	{
		return self::$lastSearchPaginationDetails;
	}

	// Retrieves a list of column names (array[tidyName] = 'ActualColumnName')
	private static function _getColumns()
	{
		static $arrColumns;
		if (!isset($arrColumns))
		{
			$arrTableDefine = DataAccess::getDataAccess()->FetchTableDefine(self::$_strStaticTableName);
			
			foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
			{
				$arrColumns[self::tidyName($strName)] = $strName;
			}
			$arrColumns[self::tidyName($arrTableDefine['Id'])] = $arrTableDefine['Id'];
		}
		
		return $arrColumns;
	}


	// Note that this currently only handles "prop IS NULL", "prop IN (list of unquoted values)", "prop = unquoted value"
	private static function _prepareSearchConstraint($strProp, $mixValue)
	{
		$strSearch = "";
		if ($mixValue === NULL)
		{
			$strSearch = "$strProp IS NULL";
		}
		elseif (is_array($mixValue))
		{
			$strSearch = "$strProp IN (". implode(", ", $mixValue) .")";
		}
		else
		{
			$strSearch = "$strProp = $mixValue";
		}
		return $strSearch;
	}

	// Performs a search for Charges
	// It is assumed that none of the arguments are escaped yet
	// This will just return the TotalRecordCount if $bolGetTotalRecordCountOnly == true
	public static function searchFor($arrFilter=null, $arrSort=null, $intLimit=null, $intOffset=null, $bolGetTotalRecordCountOnly=false)
	{
		$arrWhereParts		= array();
		$arrOrderByParts	= array();
		
		// Build WHERE clause
		$arrWhereClauseParts = array();
		if (is_array($arrFilter))
		{
			foreach ($arrFilter as $arrConstraint)
			{
				switch ($arrConstraint['Type'])
				{
					case self::SEARCH_CONSTRAINT_CHARGE_STATUS:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint(str_replace( '|', '.', self::SEARCH_CONSTRAINT_CHARGE_STATUS), $arrConstraint['Value']);
						break;
				}
			}
		}
		$strWhereClause = (count($arrWhereClauseParts))? implode(" AND ", $arrWhereClauseParts) : "";
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				switch ($strColumn)
				{
					case self::ORDER_BY_ACCOUNT_NAME:
					case self::ORDER_BY_SERVICE_FNN:
					case self::ORDER_BY_CHARGE_TYPE:
					case self::ORDER_BY_DESCRIPTION:
					case self::ORDER_BY_CREATED_ON:
					case self::ORDER_BY_NATURE:
						$arrOrderByParts[] = str_replace('|', '.', $strColumn) . ($bolAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__METHOD__ ." - Illegal sorting identifier: $strColumn");
						break;
				}
			}
		}
		$strOrderByClause = (count($arrOrderByParts) > 0)? implode(", ", $arrOrderByParts) : NULL;
		
		// Build LIMIT clause
		if ($intLimit !== NULL)
		{
			$strLimitClause = intval($intLimit);
			if ($intOffset !== NULL)
			{
				$strLimitClause .= " OFFSET ". intval($intOffset);
			}
			else
			{
				$intOffset = 0;
			}
		}
		else
		{
			$strLimitClause = "";
		}
		
		// Build SELECT statement
		$strFromClause = "Charge INNER JOIN Account ON Charge.Account = Account.Id LEFT JOIN Service ON Charge.Service = Service.Id";
		// Create the SELECT clause
		$arrColumns = self::_getColumns();

		$arrColumnsForSelectClause = array();
		foreach ($arrColumns as $strTidyName=>$strName)
		{
			$arrColumnsForSelectClause[] = "Charge.{$strName} AS $strTidyName";
		}
		// Add the ones that aren't from the charge table
		$arrColumnsForSelectClause[] = "COALESCE(Account.BusinessName, Account.TradingName) AS accountName";
		$arrColumnsForSelectClause[] = "Service.FNN AS serviceFNN";

		$strSelectClause = implode(',', $arrColumnsForSelectClause);
		
		

		// Create query to find out how many rows there are in total
		$strRowCountQuery = "SELECT COUNT(Charge.Id) as row_count FROM $strFromClause WHERE $strWhereClause;";
		
		// Check how many rows there are
		$objQuery = new Query();
		
		$mixResult = $objQuery->Execute($strRowCountQuery);
		if ($mixResult === FALSE)
		{
			throw new Exception("Failed to retrieve total record count for 'Charge Search' query - ". $objQuery->Error());
		}
		
		$intTotalRecordCount = intval(current($mixResult->fetch_assoc()));
		
		if ($bolGetTotalRecordCountOnly)
		{
			// return the total record count
			return $intTotalRecordCount;
		}
		
		// Create the proper query
		$selCharges = new StatementSelect($strFromClause, $strSelectClause, $strWhereClause, $strOrderByClause, $strLimitClause);
		
		
		
		
		if ($selCharges->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve records for 'Charge Search' query - ". $selCharges->Error());
		}

		// Create the Charge objects (these objects will also include the fields accountName and serviceFNN)
		$arrChargeObjects = array();
		while ($arrRecord = $selCharges->Fetch())
		{
			$arrChargeObjects[$arrRecord['id']] = new self($arrRecord);
		}
		
		// Create the pagination details, if a Limit clause was used
		if ($intLimit === NULL || count($arrChargeObjects) == 0)
		{
			// Don't bother calulating pagination details
			self::$lastSearchPaginationDetails = null;
		}
		else
		{
			self::$lastSearchPaginationDetails = new PaginationDetails($intTotalRecordCount, $intLimit, intval($intOffset));
		}
		
		return $arrChargeObjects;
	}

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}

	public function setToDeclined($intEmployeeId=null, $bolLogAction=true, $strReason=null)
	{
		$intEmployeeId	= ($intEmployeeId == null)? Employee::SYSTEM_EMPLOYEE_ID : $intEmployeeId;

		// Check that the charge is currently awaiting approval
		if ($this->status != CHARGE_WAITING)
		{
			$strCurrentSatus = GetConstantDescription($this->status, 'ChargeStatus');
			throw new Exception("Cannot decline the request for adjustment because it isn't currently awaiting approval.  Its current status is: $strCurrentSatus");
		}
		
		if ($bolLogAction)
		{
			// Log the 'Adjustment Request Outcome' action, having taken place
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature = ($this->nature == NATURE_DR)? "Debit" : "Credit";

			$strChargeAmount = number_format(AddGST($this->amount), 2, '.', ''); 
			
			$strNote = 	"Request for adjustment has been REJECTED.\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature}) (id: {$this->id})\n".
						"Requested on: {$strCreatedOn} by {$strCreatedByEmployeeName}\n".
						"Amount (inc GST): \${$strChargeAmount} {$strNature}";

			$strReason = trim($strReason);
			if ($strReason != '')
			{
				$strNote .= "\nReason:\n{$strReason}";
			}
			
			Action::createAction('Adjustment Request Outcome', $strNote, $this->account, $this->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}

		// Update the Charge record
		$this->status = CHARGE_DECLINED;
		$this->save();
	}
	
	public function setToApproved($intEmployeeId=null, $bolLogAction=true)
	{
		$intEmployeeId	= ($intEmployeeId == null)? Employee::SYSTEM_EMPLOYEE_ID : $intEmployeeId;
		
		// Check that the charge is currently awaiting approval
		if ($this->status != CHARGE_WAITING)
		{
			$strCurrentSatus = GetConstantDescription($this->status, 'ChargeStatus');
			throw new Exception("Cannot approve the request for adjustment because it isn't currently awaiting approval.  Its current status is: $strCurrentSatus");
		}
		
		if ($bolLogAction)
		{
			// Log the 'Adjustment Request Outcome' action, having taken place
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature = ($this->nature == NATURE_DR)? "Debit" : "Credit";

			$strChargeAmount = number_format(AddGST($this->amount), 2, '.', ''); 
			
			$strNote = 	"Request for adjustment has been APPROVED.\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature}) (id: {$this->id})\n".
						"Requested on: {$strCreatedOn} by {$strCreatedByEmployeeName}\n".
						"Amount (inc GST): \${$strChargeAmount} {$strNature}";
			
			Action::createAction('Adjustment Request Outcome', $strNote, $this->account, $this->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}

		// Update the Charge record
		$this->approvedBy = $intEmployeeId;
		$this->status = CHARGE_APPROVED;
		$this->save();
	}

	// Builds a description which can be used to identify the adjustment
	public function getIdentifyingDescription($bolIncludeAccountAndServiceIds=false, $bolIncludeChargedOnDate=false, $bolIncludeChargeId=false)
	{
		$strAmountFormatted = number_format(AddGST($this->amount), 2, '.', '');
		
		$strDesc = "{$this->chargeType} - {$this->description}, \${$strAmountFormatted} {$this->nature} (inc GST) ";
		
		if ($bolIncludeAccountAndServiceIds)
		{
			$strDesc .= ", Account: {$this->account}";
			if ($this->service != null)
			{
				$objService = Service::getForId($this->service);
				
				$strDesc .= ", Service: {$objService->FNN}";
			}
		}
		
		if ($bolIncludeChargedOnDate)
		{
			$strDesc .= ", Dated: ". date('d-m-Y', strtotime($this->chargedOn));
		}
		
		if ($bolIncludeChargeId)
		{
			$strDesc .= " (Id: {$this->id})";
		}
		
		return $strDesc;
	}
	

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "Id ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
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