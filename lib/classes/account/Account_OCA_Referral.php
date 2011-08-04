<?php
/**
 * Account_Collection_Scenario
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Account_Collection_Scenario
 */
class Account_OCA_Referral extends ORM_Cached
{
	protected 			$_strTableName			= "account_oca_referral";
	protected static	$_strStaticTableName	= "account_oca_referral";
	
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

        public function cancel()
        {
            if ($this->account_oca_referral_status_id === ACCOUNT_OCA_REFERRAL_STATUS_PENDING)
            {
                $this->account_oca_referral_status_id = ACCOUNT_OCA_REFERRAL_STATUS_CANCELLED;
                $this->save();
            }
            else if ($this->account_oca_referral_status_id === ACCOUNT_OCA_REFERRAL_STATUS_COMPLETE)
            {
                throw new Exception("Trying to cancel an OCA Referral that was previously completed.");
            }
        }

       

        public static function getForAccountId($iAccountId, $mStatus = NULL)
        {
             $aWhere;
            if ($mStatus != NULL)
            {
                if (is_array($mStatus))
                {
                    $oStatus = new stdClass();
                    $aWhere = array('account_id' => $iAccountId, 'account_oca_referral_status_id'=>(object)$mStatus);
                }
                else
                {
                    $aWhere = array('account_id' => $iAccountId, 'account_oca_referral_status_id'=>$mStatus);
                }
            }
            else
            {
                $aWhere = array('account_id' => $iAccountId);
            }
            
            $aResult = self::getFor( $aWhere);
            return count($aResult) > 0 ? $aResult : null;
        }

	public static function getFor($aCriteria)
        {
            $aWhere	= StatementSelect::generateWhere(null, $aCriteria);
            $oQuery	= new StatementSelect(self::$_strStaticTableName, "*", $aWhere['sClause']);
            $mixResult			= $oQuery->Execute($aWhere['aValues']);
            $arrRecordSet	= $oQuery->FetchAll();
            $aResult = array();
            foreach($arrRecordSet as $aRecord)
            {
                $aResult[] = new self($aRecord);
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

	public function action()
	{
		$this->actioned_datetime 				= DataAccess::getDataAccess()->getNow();
		$this->actioned_employee_id				= Flex::getUserId();
		$this->account_oca_referral_status_id	= ACCOUNT_OCA_REFERRAL_STATUS_COMPLETE;
		$this->save();
	}

	public static function getLedger($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases = array(
						'account_oca_referral_id' 			=> "aor.id",
						'account_id' 						=> "aor.account_id",
						'account_name' 						=> "a.BusinessName",
						'customer_group_name' 				=> "cg.internal_name",
						'file_export_id' 					=> "aor.file_export_id",
						'file_export_filename'				=> "fe.FileName",
						'invoice_id' 						=> "i.Id",
						'account_oca_referral_status_id' 	=> "aor.account_oca_referral_status_id",
						'account_oca_referral_status_name'	=> "aors.name",
						'actioned_datetime' 				=> "aor.actioned_datetime",
						'actioned_employee_id' 				=> "aor.actioned_employee_id",
						'actioned_employee_name'			=> "CONCAT(e_actioned.FirstName, ' ', e_actioned.LastName)",
						'can_action'						=> "IF(DAY(NOW()) IN (pt.invoice_day, pt.invoice_day - 1), 0, 1)" 
					);
		
		$sFrom	= "	account_oca_referral aor
					JOIN		Account a ON (a.Id = aor.account_id)
					JOIN		CustomerGroup cg ON (cg.Id = a.CustomerGroup)
					JOIN		payment_terms pt ON (pt.customer_group_id = cg.Id)
					JOIN		account_oca_referral_status aors ON (aors.id = aor.account_oca_referral_status_id)
					LEFT JOIN 	Employee e_actioned ON (e_actioned.Id = aor.actioned_employee_id)
					LEFT JOIN	FileExport fe ON (fe.Id = aor.file_export_id)
					LEFT JOIN	Invoice i ON (
									i.Account = a.Id
									AND i.invoice_run_id = aor.invoice_run_id
								)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(aor.id) AS count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "{$aAliases['account_oca_referral_id']} AS account_oca_referral_id,
						{$aAliases['account_id']} AS account_id, 
						{$aAliases['account_name']} AS account_name, 
						{$aAliases['customer_group_name']} AS customer_group_name, 
						{$aAliases['file_export_id']} AS file_export_id,
						{$aAliases['file_export_filename']} AS file_export_filename,
						{$aAliases['invoice_id']} AS invoice_id, 
						{$aAliases['account_oca_referral_status_id']} AS account_oca_referral_status_id,
						{$aAliases['account_oca_referral_status_name']} AS account_oca_referral_status_name, 
						{$aAliases['actioned_datetime']} AS actioned_datetime,
						{$aAliases['actioned_employee_id']} AS actioned_employee_id, 
						{$aAliases['actioned_employee_name']} AS actioned_employee_name, 
						{$aAliases['can_action']} AS can_action";
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere 	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere		= $aWhere['sClause'];
		$oSelect	=	new StatementSelect(
							$sFrom, 
							$sSelect, 
							$sWhere, 
							$sOrderBy, 
							$sLimit
						);
		
		if ($oSelect->Execute($aWhere['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to get Account OCA Referral items. ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['account_oca_referral_id']] = $aRow;
		}
		
		return $aResults;
	}

	public function accountExists($iAccountId, $bCurrentOnly=false)
	{
		$oSelect 	= self::_preparedStatement('selByAccountId');
		$iRowCount	= $oSelect->Execute(array('account_id' => $iAccountId, 'current_only'=>(int)$bCurrentOnly));
		if ($iRowCount === false)
		{
			throw new Exception_Database("Failed to find if account exists. ".$oSelect->Error());
		}
		return $iRowCount > 0;
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
				case 'selByAccountId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "id", "account_id = <account_id> AND (<current_only> = 0 OR account_oca_referral_status_id = ".ACCOUNT_OCA_REFERRAL_STATUS_PENDING.")");
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