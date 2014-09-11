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
	const SEARCH_CONSTRAINT_CHARGE_MODEL_ID	= "Charge|charge_model_id";
	const SEARCH_CONSTRAINT_ACCOUNT_ID		= "Account|Id";
	const SEARCH_CONSTRAINT_INVOICE_RUN_ID	= "Charge|invoice_run_id";
	const SEARCH_CONSTRAINT_CHARGE_TYPE		= "Charge|ChargeType";

	const ORDER_BY_ACCOUNT_NAME		= "Account|accountName";
	const ORDER_BY_ACCOUNT_ID		= "Charge|Account";
	const ORDER_BY_SERVICE_FNN		= "Service|serviceFNN";
	const ORDER_BY_CHARGE_TYPE		= "Charge|ChargeType";
	const ORDER_BY_DESCRIPTION		= "Charge|Description";
	const ORDER_BY_CREATED_ON		= "Charge|CreatedOn";
	const ORDER_BY_NATURE			= "Charge|Nature";
	const ORDER_BY_ID				= "Charge|Id";
	const ORDER_BY_CHARGED_ON		= "Charge|ChargedOn";

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
					case self::SEARCH_CONSTRAINT_CHARGE_MODEL_ID:
					case self::SEARCH_CONSTRAINT_ACCOUNT_ID:
					case self::SEARCH_CONSTRAINT_INVOICE_RUN_ID:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint(str_replace( '|', '.', $arrConstraint['Type']), $arrConstraint['Value']);
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
					case self::ORDER_BY_ID:
					case self::ORDER_BY_CHARGED_ON:
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
			throw new Exception_Database("Failed to retrieve total record count for 'Charge Search' query - ". $objQuery->Error());
		}
		
		$intTotalRecordCount = intval(current($mixResult->fetch_assoc()));
		
		if ($bolGetTotalRecordCountOnly)
		{
			// return the total record count
			return $intTotalRecordCount;
		}
		
		// Create the proper query
		$selCharges = new StatementSelect($strFromClause, $strSelectClause, $strWhereClause, $strOrderByClause, $strLimitClause);
		
		//throw new Exception($selCharges->_strQuery);
		
		
		if ($selCharges->Execute() === FALSE)
		{
			throw new Exception_Database("Failed to retrieve records for 'Charge Search' query - ". $selCharges->Error());
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
	
	public static function getDatasetForAccountList($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		// Build the list of charge type visibilities to allow
		$bUserIsGod					= Employee::getForId(Flex::getUserId())->isGod();
		$bUserIsCreditManagement	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		$bUserCanDeleteCharges		= (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || $bUserIsCreditManagement);
		$aVisibleChargeTypes 		= array(CHARGE_TYPE_VISIBILITY_VISIBLE);
		if ($bUserIsCreditManagement)
		{
			$aVisibleChargeTypes[] = CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL;
		}
		
		if ($bUserIsGod)
		{
			$aVisibleChargeTypes[] = CHARGE_TYPE_VISIBILITY_HIDDEN;
		}
		
		$aAliases = array(
						'id' 					=> "c.Id",
						'charge_type_code' 		=> "c.ChargeType",
						'charge_model_id' 		=> "c.charge_model_id",
						'charge_status' 		=> "c.Status",
						'charged_on' 			=> "c.ChargedOn",
						'amount_inc_gst' 		=> "c.Amount + (
													c.Amount * (
														SELECT	tt.rate_percentage
														FROM	tax_type tt
														WHERE	tt.global = 1
														AND		NOW() BETWEEN tt.start_datetime AND tt.end_datetime
														LIMIT	1
													)
												)",
						'nature' 			=> "c.Nature",
						'created_by' 		=> "c.CreatedBy",
						'created_by_name' 	=> "IF(c.CreatedBy, CONCAT(e_created_by.FirstName, ' ', e_created_by.LastName), NULL)",
						'approved_by' 		=> "c.ApprovedBy",
						'approved_by_name' 	=> "IF(c.ApprovedBy, CONCAT(e_approved_by.FirstName, ' ', e_approved_by.LastName), NULL)",
						'service_id' 		=> "c.Service",
						'service_fnn' 		=> "s.FNN",
						'description' 		=> "c.Description",
						'invoice_run_id' 	=> "c.invoice_run_id",
						'link_type' 		=> "c.LinkType",
						'link_id' 			=> "c.LinkId",
						'account_id'		=> "c.Account",
						'notes'				=> "c.Notes"
					);
		
		$sFrom = "				Charge c 
					LEFT JOIN	Employee e_created_by ON (e_created_by.Id = c.CreatedBy)
					LEFT JOIN	Employee e_approved_by ON (e_approved_by.Id = c.ApprovedBy)
					LEFT JOIN	Service s ON (s.Id = c.Service)
					LEFT JOIN 	ChargeType ct ON (
						            (ct.Id = c.charge_type_id OR ct.ChargeType = c.ChargeType)
						            AND ct.charge_type_visibility_id IN (".implode(',', $aVisibleChargeTypes).")
						        )";
		
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(c.Id) AS count";
			$sOrderBy	= "";
			$sLimit		= "";
		}
		else
		{
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause)
			{
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect	= implode(', ', $aSelectLines);
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere 	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$oSelect 	= new StatementSelect($sFrom, $sSelect, $aWhere['sClause'], $sOrderBy, $sLimit);
		//throw new Exception($oSelect->_strQuery);
		$mRows 		= $oSelect->Execute($aWhere['aValues']);
		if ($mRows === false)
		{
			throw new Exception_Database("Failed to get Charge search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
	}
	

	public static function getUnbilledForAccountAndType($iAccountId, $iChargeTypeId)
    {
        $sSql = "   SELECT *
                    FROM Charge
                    WHERE Account = $iAccountId
                    AND charge_type_id = $iChargeTypeId
                    AND Status in (".CHARGE_APPROVED.",".CHARGE_TEMP_INVOICE.")";
        $oQuery = new Query();
        $mResult = $oQuery->Execute($sSql);
        $aResult = array();
        if ($mResult)
        {
            while ($aRow = $mResult->fetch_assoc())
            {
                    $aResult[]= new self($aRow);
            }
        }

        return count($aResult) > 0 ? $aResult : null;
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
			throw new Exception("Cannot decline the request for charge because it isn't currently awaiting approval.  Its current status is: $strCurrentSatus");
		}
		
		if ($bolLogAction)
		{
			// Log the 'Charge/Adjustment Request Outcome' action, having taken place
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature 			= ($this->nature == NATURE_DR)? "Debit" : "Credit";
			$strChargeAmount 	= number_format(AddGST($this->amount), 2, '.', ''); 
			$sChargeModel		= Constant_Group::getConstantGroup('charge_model')->getConstantName($this->charge_model_id);
			
			$strNote = 	"Request for {$sChargeModel} has been REJECTED.\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature}) (id: {$this->id})\n".
						"Requested on: {$strCreatedOn} by {$strCreatedByEmployeeName}\n".
						"Amount (inc GST): \${$strChargeAmount} {$strNature}";

			$strReason = trim($strReason);
			if ($strReason != '')
			{
				$strNote .= "\nReason:\n{$strReason}";
			}
			
			Action::createAction("{$sChargeModel} Request Outcome", $strNote, $this->account, $this->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}

		// Update the Charge record
		$this->approvedBy = $intEmployeeId;
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
			throw new Exception("Cannot approve the request for charge because it isn't currently awaiting approval.  Its current status is: $strCurrentSatus");
		}
		
		if ($bolLogAction)
		{
			// Log the 'Charge/Adjustment Request Outcome' action, having taken place
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature 			= ($this->nature == NATURE_DR)? "Debit" : "Credit";
			$strChargeAmount 	= number_format(AddGST($this->amount), 2, '.', ''); 
			$sChargeModel		= Constant_Group::getConstantGroup('charge_model')->getConstantName($this->charge_model_id);
			
			$strNote = 	"Request for {$sChargeModel} has been APPROVED.\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature}) (id: {$this->id})\n".
						"Requested on: {$strCreatedOn} by {$strCreatedByEmployeeName}\n".
						"Amount (inc GST): \${$strChargeAmount} {$strNature}";
			
			Action::createAction("{$sChargeModel} Request Outcome", $strNote, $this->account, $this->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}

		// Update the Charge record
		$this->approvedBy = $intEmployeeId;
		$this->status = CHARGE_APPROVED;
		$this->save();
	}

	// Builds a description which can be used to identify the charge
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
	
	//--------------------------------//
	// OVERRIDES
	//--------------------------------//
	
	public function save()
	{
		// Check for required charge_model_id, set to default (CHARGE) if not set
		if (!isset($this->charge_model_id))
		{
			$this->charge_model_id	= CHARGE_MODEL_CHARGE;
		}
		else if ($this->charge_model_id == CHARGE_MODEL_ADJUSTMENT)
		{
			// Assert that no adjustment charges can be created any more
			$mId = ORM::extractId($this);
			Flex::assert($mId !== null, "Adjustment Charge (charge with charge model of adjustment) being created, instead of using the Adjustment class (& table).", print_r($this->toArray(), true));
		}
		
		parent::save();
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