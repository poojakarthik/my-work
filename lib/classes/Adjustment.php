<?php
/**
 * Adjustment
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Adjustment
 */
class Adjustment extends ORM_Cached
{
	protected 			$_strTableName			= "adjustment";
	protected static	$_strStaticTableName	= "adjustment";



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

    public static function resetBalanceForAccount($iAccountId)
    {
        $oQuery = new Query();
        $sSql = "   UPDATE adjustment a
                    LEFT JOIN adjustment a2 ON ( a2.reversed_adjustment_id = a.id)
                    SET a.balance = IF (a2.id is not null || a.adjustment_nature_id = ".ADJUSTMENT_NATURE_REVERSAL." , 0, a.amount)
                    WHERE a.account_id = $iAccountId";
        $oQuery->Execute($sSql);


    }

    public function getSignType()
    {
         $sSQL = "   SELECT IF(an.value_multiplier * tn.value_multiplier = -1 ,".Logic_Adjustment::CREDIT.",".Logic_Adjustment::DEBIT.")  as 'sign',
                                an.value_multiplier * tn.value_multiplier as 'multiplier'
                    FROM adjustment a
                    JOIN adjustment_nature an ON (a.adjustment_nature_id = an.id)
                    JOIN adjustment_type at ON (a.adjustment_type_id = at.id)
                    JOIN transaction_nature tn ON (at.transaction_nature_id = tn.id)
                    WHERE a.Id = $this->id
                    ";
        $oQuery = new Query();
        $mResult = $oQuery->Execute($sSQL);
         if ($mResult)
        {
           return $mResult->fetch_assoc();

        }

        return null;
    }

    public static function getForAccountId($iAccountId, $iSignType = null, $iStatus = null, $bWithDistributableBalanceOnly = true )
    {
        $iValueMultiplier = $iSignType == Logic_Adjustment::CREDIT ? -1 : 1;
        $sSignTypeWhereClause = $iSignType !== null ? "AND an.value_multiplier * tn.value_multiplier = $iValueMultiplier" : "";
        $sStatusWhereClause = $iStatus !== null ? "AND a.status_id = $iStatus" : "";
        $sBalanceWhereClause = $bWithDistributableBalanceOnly ? " AND a.balance > 0" : "";
        $sSQL = "   SELECT a.*
                    FROM adjustment a
                    JOIN adjustment_nature an ON (a.adjustment_nature_id = an.id)
                    JOIN adjustment_type at ON (a.adjustment_type_id = at.id)
                    JOIN transaction_nature tn ON (at.transaction_nature_id = tn.id)
                    WHERE a.account_id = $iAccountId
                    $sSignTypeWhereClause $sStatusWhereClause $sBalanceWhereClause
                    ";
        $oQuery = new Query();
        $mResult = $oQuery->Execute($sSQL);
        $aResult = array();
        if ($mResult)
        {
            while ($aRecord = $mResult->fetch_assoc())
            {
                $aResult[] = new self($aRecord);
            }
        }

        return $aResult;
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

	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public function calculateTaxComponent()
	{
		$oTaxType 				= Tax_Type::getGlobalTaxType();
		$fModifiedTaxRate		= 1 + $oTaxType->rate_percentage;
		$fTaxComponent			= $this->amount - ($this->amount / $fModifiedTaxRate);
		$this->tax_component 	= Rate::roundToCurrencyStandard($fTaxComponent, 2);
	}

	public function reverse($iReasonId)
	{
		$oReversal = new Adjustment();
		
		// Copy fields from this adjustment
		$oReversal->adjustment_type_id	= $this->adjustment_type_id;
		$oReversal->amount 				= $this->amount;
		$oReversal->tax_component 		= $this->tax_component;
		$oReversal->balance 			= $this->amount;
		$oReversal->account_id 			= $this->account_id;
		$oReversal->service_id 			= $this->service_id;
		$oReversal->invoice_id 			= $this->invoice_id;
		$oReversal->invoice_run_id 		= $this->invoice_run_id;
		
		// Different fields
		$oReversal->effective_date 		= date('Y-m-d');
		$oReversal->created_employee_id = Flex::getUserId();
		$oReversal->created_datetime 	= date('Y-m-d H:i:s');
		
		// Reversal specific fields
		$oReversal->adjustment_nature_id 			= ADJUSTMENT_NATURE_REVERSAL;
		$oReversal->reversed_adjustment_id 			= $this->id;
		$oReversal->adjustment_reversal_reason_id 	= $iReasonId;
		
		// Review/approval related fields
		$oReversal->reviewed_employee_id 			= Employee::SYSTEM_EMPLOYEE_ID;
		$oReversal->reviewed_datetime 				= date('Y-m-d H:i:s');
		$oReversal->adjustment_review_outcome_id	= Adjustment_Review_Outcome::getForSystemName('APPROVED')->id;
		$oReversal->adjustment_status_id			= ADJUSTMENT_STATUS_APPROVED;
		
		$oReversal->save();
		
		return $oReversal;		
	}
	
	public function approve()
	{
		$this->reviewed_employee_id 		= Flex::getUserId();
		$this->reviewed_datetime 			= date('Y-m-d H:i:s');
		$this->adjustment_review_outcome_id	= Adjustment_Review_Outcome::getForSystemName('APPROVED')->id;
		$this->adjustment_status_id			= ADJUSTMENT_STATUS_APPROVED;
		$this->save();
	}
	
	public function decline($iAdjustmentReviewOutcomeId)
	{
		$this->reviewed_employee_id 		= Flex::getUserId();
		$this->reviewed_datetime 			= date('Y-m-d H:i:s');
		$this->adjustment_review_outcome_id	= $iAdjustmentReviewOutcomeId;
		$this->adjustment_status_id			= ADJUSTMENT_STATUS_DECLINED;
		$this->save();
	}

	public static function searchForApproved($bCountOnly, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases =	array(
						'adjustment_id' 				=> "a.id",
						'adjustment_type_code' 			=> "at.code",
						'effective_date'				=> "a.effective_date",
						'amount'						=> "a.amount",
						'account_id'					=> "a.account_id",
						'service_id'					=> "a.service_id",
						'service_fnn'					=> "s.FNN",
						'adjustment_status_description'	=> "a_s.description",
						'is_reversed'					=> "IF(
																a_reversed.id IS NOT NULL
																AND a_s_reversed.system_name = 'APPROVED'
																AND arot_reversed.system_name = 'APPROVED', 
																1, 
																0
															)",
						'transaction_nature_code'		=> "tn.code",
						'created_employee_name' 		=> "CONCAT(e_created.FirstName, ' ', e_created.LastName)",
						'reviewed_employee_name' 		=> "IF(e_reviewed.Id IS NOT NULL, CONCAT(e_reviewed.FirstName, ' ', e_reviewed.LastName), NULL)"
					);
		
		$sFrom		= "				adjustment a
						JOIN		adjustment_status a_s ON (
										a_s.id = a.adjustment_status_id
										AND a_s.system_name = 'APPROVED'
									)
						JOIN		adjustment_review_outcome aro ON (aro.id = a.adjustment_review_outcome_id)
						JOIN  		adjustment_review_outcome_type arot ON (
										arot.id = aro.adjustment_review_outcome_type_id AND 
										arot.system_name = 'APPROVED'
									)
						JOIN		adjustment_type at ON (at.id = a.adjustment_type_id)
						JOIN		transaction_nature tn ON (tn.id = at.transaction_nature_id)
						JOIN		Employee e_created ON (e_created.id = a.created_employee_id)
						LEFT JOIN	Employee e_reviewed ON (e_reviewed.id = a.reviewed_employee_id)
						LEFT JOIN	adjustment a_reversed ON (a_reversed.reversed_adjustment_id = a.id)
						LEFT JOIN	adjustment_status a_s_reversed ON (
										a_s_reversed.id = a_reversed.adjustment_status_id
										AND a_s_reversed.system_name = 'APPROVED'
									)
						LEFT JOIN	adjustment_review_outcome aro_reversed ON (aro_reversed.id = a_reversed.adjustment_review_outcome_id)
						LEFT JOIN  	adjustment_review_outcome_type arot_reversed ON (
										arot_reversed.id = aro_reversed.adjustment_review_outcome_type_id AND 
										arot_reversed.system_name = 'APPROVED'
									)
						LEFT JOIN	Service s ON (s.Id = a.service_id)";
		
		if ($bCountOnly)
		{
			$sSelect 	= "COUNT(a.id) AS count";
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
		
		$aWhere	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere	= $aWhere['sClause'];
		$sWhere	.= 	($sWhere != '' ? " AND " : '')."a.reversed_adjustment_id IS NULL";	
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to get adjustment search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
	}

	public static function searchForPending($bCountOnly, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases =	array(
						'id' 							=> "a.id",
						'effective_date'				=> "a.effective_date",
						'created_employee_id'			=> "a.created_employee_id",
						'created_employee_name'			=> "CONCAT(e_created.FirstName, ' ', e_created.LastName)",
						'amount'						=> "a.amount",
						'account_id'					=> "a.account_id",
						'account_name'					=> "acc.BusinessName",
						'service_fnn'					=> "COALESCE(s.FNN, '')",
						'adjustment_type_code' 			=> "at.code",
						'adjustment_type_description' 	=> "at.description",
						'transaction_nature_id'			=> "tn.id",
						'transaction_nature_name'		=> "tn.name"
					);
		
		$sFrom		= "				adjustment a
						JOIN		adjustment_status a_s ON (
										a_s.id = a.adjustment_status_id
										AND a_s.system_name = 'PENDING'
									)
						JOIN		adjustment_type at ON (at.id = a.adjustment_type_id)
						JOIN		transaction_nature tn ON (tn.id = at.transaction_nature_id)
						JOIN		Account acc ON (acc.Id = a.account_id)
						JOIN		Employee e_created ON (e_created.Id = a.created_employee_id)
						LEFT JOIN	Service s ON (s.Id = a.service_id)";
		
		if ($bCountOnly)
		{
			$sSelect 	= "COUNT(a.id) AS count";
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
		
		$aWhere	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere	= $aWhere['sClause'];
		$sWhere	.= 	($sWhere != '' ? " AND " : '')."a.reversed_adjustment_id IS NULL";	
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to get adjustment search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
	}

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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
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
				 case 'resetBalanceForAccount':
                                        $arrPreparedStatements[$strStatement]	= new StatementUpdate(self::$_strStaticTableName, "account_id = <account_id> ", array());
					break;
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>