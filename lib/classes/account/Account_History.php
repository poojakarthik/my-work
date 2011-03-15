<?php

//----------------------------------------------------------------------------//
// Account_History
//----------------------------------------------------------------------------//
/**
 * Account_History
 *
 * Encapsulates functionality regarding the account history table
 *
 * Encapsulates functionality regarding the account history table
 *
 * @class	Account_History
 */
class Account_History
{
	const SYSTEM_ACTION_EMPLOYEE_ID = USER_ID;

	protected  			$_sTableName		= 'account_history';
	protected static	$_sStaticTableName	= 'account_history';

	//------------------------------------------------------------------------//
	// recordCurrentState
	//------------------------------------------------------------------------//
	/**
	 * recordCurrentState()
	 *
	 * Records the current state of an account record in the account_history table, if any of the tracked properties have changed
	 * 
	 * Records the current state of an account record in the account_history table, if any of the tracked properties have changed
	 * Tracked properties are those listed in the account_history table
	 * Throws an exception on error
	 * Throws an exception if $strTimestamp is less than the timestamp of the record in the account_history table which is currently modelling the state of the account
	 * This scenario represents a data integrity issue, and any changes made to the account record should be rolled back
	 *
	 * @param	int 	$intAccountId		id of the account to record
	 * @param	int		$intEmployeeId		[optional] id of the employee who was responsible for any changes made to the state of the account record.
	 * 										Defaults to NULL, signifying that the account's state was changed by an automated, backend process (no employee)
	 * @param	string	$strTimestamp		[optional] time at which the account's state is considered to have been changed. 
	 * 										Defaults to NULL, which will use the current time on the database server
	 *  
	 * @return	boolean						TRUE if the Account's state was actually changed necessitating a new record to be added to the account_history table
	 * 										FALSE if the account's state hadn't changed, in which case, a record IS NOT added to the account_history table
	 *
	 * @method
	 */
	public static function recordCurrentState($intAccountId, $intEmployeeId=NULL, $strTimestamp=NULL)
	{
		static $selAccountHistory;
		static $selAccount;
		static $insAccountHistory;
		
		if ($intEmployeeId == NULL)
		{
			$intEmployeeId = self::SYSTEM_ACTION_EMPLOYEE_ID;
		}
		if ($strTimestamp === NULL)
		{
			$strTimestamp = GetCurrentISODateTime();
		}
		
		// Retrieve the account record in question
		if (!isset($selAccount))
		{
			$selAccount = new StatementSelect("Account", self::getAccountColumns(), "Id = <account_id>", 1);
		}
		if (($intRecCount = $selAccount->Execute(array("account_id"=>$intAccountId))) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve Account record with id: $intAccountId - ". $selAccount->Error());
		}
		if ($intRecCount == 0)
		{
			throw new Exception("Account with id: $intAccountId, cannot be found in the account table");
		}
		$arrAccount = $selAccount->Fetch();

		// Find the most recently added record from the account_history table modelling this account
		if (!isset($selAccountHistory))
		{
			$selAccountHistory = new StatementSelect("account_history", self::getAccountHistoryColumns(), "account_id = <AccountId>", "change_timestamp DESC", 1);
		}
		
		if (($intRecCount = $selAccountHistory->Execute(array("AccountId"=>$intAccountId))) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve current account_history record for account id: $intAccountId - ". $selAccountHistory->Error());
		}
		
		$bolHistoryNeedsUpdating	= FALSE;
		$arrTrackedProperties		= self::getTrackedAccountColumns();
		if ($intRecCount == 1)
		{
			// An Account History record exists
			$arrAccountHistory = $selAccountHistory->Fetch();
			
			// Check that at least one of the tracked properties has actually been changed
			foreach ($arrTrackedProperties as $strAlias=>$strProp)
			{
				if ($arrAccountHistory[$strAlias] != $arrAccount[$strAlias])
				{
					// The properties differ
					$bolHistoryNeedsUpdating = TRUE;
					break;
				}
			}
			
			// Check that the requested timestamp > that of the account history record
			if ($strTimestamp < $arrAccountHistory['change_timestamp'])
			{
				throw new Exception("Requested Timestamp for recording of account state ($strTimestamp) is less than or equal to the timestamp marked against the current record in the account_history table modelling the current state of the account ({$arrAccountHistory['change_timestamp']}), signifying a breach of data integrity");
			}
		}
		else
		{
			// There are currently no records in the account_history table relating to this account.  The record should be added
			$bolHistoryNeedsUpdating = TRUE;
		}

		if ($bolHistoryNeedsUpdating)
		{
			// Build the data for the new record
			$arrData = self::getAccountHistoryColumns();
			$arrData['id']					= NULL;
			$arrData['change_timestamp']	= $strTimestamp;
			$arrData['employee_id']			= $intEmployeeId;
			$arrData['account_id']			= $intAccountId;
			
			foreach ($arrTrackedProperties as $strAlias=>$strProp)
			{
				$arrData[$strAlias] = $arrAccount[$strAlias];
			}
			
			if (!isset($insAccountHistory))
			{
				$insAccountHistory = new StatementInsert("account_history", $arrData);
			}
			
			// Make the insert
			if (($intNewId = $insAccountHistory->Execute($arrData)) === FALSE)
			{
				throw new Exception_Database("Failed to insert record into account_history table for account: $intAccountId - ". $insAccountHistory->Error());
			}
			
			return TRUE;
		}

		return FALSE;
	}

// This version makes use of the lib/classes/Account.php Account class.  It is not currently used, because this functionality is required by the frontend Framework 1 stuff
// which has its own Account class defined, which is significantly different
/*	public static function recordCurrentState($intAccountId, $intEmployeeId=NULL, $strTimestamp=NULL)
	{
		static $selAccountHistory;
		static $selAccount;
		static $insAccountHistory;
		
		if ($intEmployeeId === NULL)
		{
			$intEmployeeId = self::SYSTEM_ACTION_EMPLOYEE_ID;
		}
		if ($strTimestamp === NULL)
		{
			$strTimestamp = GetCurrentISODateTime();
		}
		
		// Retrieve the account record in question
		$objAccount = Account::getForId($intAccountId);
		if ($objAccount === NULL)
		{
			throw new Exception("Account with id: $intAccountId, cannot be found in the account table");
		}
		
		// Find the most recently added record from the account_history table modelling this account
		if (!isset($selAccountHistory))
		{
			$selAccountHistory = new StatementSelect("account_history", self::getAccountHistoryColumns(), "account_id = <AccountId>", "change_timestamp DESC", 1);
		}
		
		if (($intRecCount = $selAccountHistory->Execute(array("account_id"=>$intAccountId))) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve current account_history record for account id: $intAccountId - ". $selAccountHistory->Error());
		}
		
		$bolHistoryNeedsUpdating	= FALSE;
		$arrTrackedProperties		= self::getTrackedAccountColumns();
		if ($intRecCount == 1)
		{
			// An Account History record exists
			$arrAccountHistory = $selAccountHistory->Fetch();
			
			// Check that at least one of the tracked properties has actually been changed
			foreach ($arrTrackedProperties as $strAlias=>$strProp)
			{
				if ($arrAccountHistory[$strAlias] != $objAccount->{$strProp})
				{
					// The properties differ
					$bolHistoryNeedsUpdating = TRUE;
					break;
				}
			}
			
			// Check that the requested timestamp > that of the account history record
			if ($strTimestamp <= $arrAccountHistory['change_timestamp'])
			{
				throw new Exception("Requested Timestamp for recording of account state ($strTimestamp) is less than or equal to the timestamp marked against the current record in the account_history table modelling the current state of the account ({$arrAccountHistory['change_timestamp']}), signifying a breach of data integrity");
			}
		}
		else
		{
			// There are currently no records in the account_history table relating to this account.  The record should be added
			$bolHistoryNeedsUpdating = TRUE;
		}

		if ($bolHistoryNeedsUpdating)
		{
			// Build the data for the new record
			$arrData = self::getAccountHistoryColumns();
			$arrData['id']					= NULL;
			$arrData['change_timestamp']	= $strTimestamp;
			$arrData['employee_id']			= $intEmployeeId;
			
			foreach ($arrTrackedProperties as $strAlias=>$strProp)
			{
				$arrData[$strAlias] = $objAccount->{$strProp};
			}
			
			if (!isset($insAccountHistory))
			{
				$insAccountHistory = new StatementInsert("account_history", $arrData);
			}
			
			// Make the insert
			if (($intNewId = $insAccountHistory->Execute($arrData)) === FALSE)
			{
				throw new Exception_Database("Failed to insert record into account_history table for account: $intAccountId - ". $insAccountHistory->Error());
			}
			
			return TRUE;
		}

		return FALSE;
	}
*/

	//------------------------------------------------------------------------//
	// recordCurrentStateForAll
	//------------------------------------------------------------------------//
	/**
	 * recordCurrentStateForAll()
	 *
	 * Records the current state of each account in the Account table (useful for batch processing)
	 * 
	 * Records the current state of each account in the Account table (useful for batch processing)
	 * Throws an exception on error
	 * Throws an exception if $strTimestamp is less than the timestamp of the record in the account_history table which is currently modelling the state of the account, for any account
	 * (This scenario represents a data integrity issue, and any changes made to the account record should be rolled back)
	 * This also assumes there is at least one tracked Account column.  It will break (throw an exception) if there isn't
	 *
	 * @param	int		$intEmployeeId		[optional] id of the employee who was responsible for any changes made to the state of the account record.
	 * 										Defaults to NULL, signifying that the account's state was changed by an automated, backend process (no employee)
	 * @param	string	$strTimestamp		[optional] time at which the account's state is considered to have been changed. 
	 * 										Defaults to NULL, which will use the current time on the database server
	 *  
	 * @return	boolean						TRUE if the Account's state was actually changed necessitating a new record to be added to the account_history table
	 * 										FALSE if the account's state hadn't changed, in which case, a record IS NOT added to the account_history table
	 *
	 * @method
	 */
	public static function recordCurrentStateForAll($intEmployeeId=NULL, $strTimestamp=NULL)
	{
		if ($intEmployeeId === NULL)
		{
			$intEmployeeId = self::SYSTEM_ACTION_EMPLOYEE_ID;
		}
		if ($strTimestamp === NULL)
		{
			$strTimestamp = GetCurrentISODateTime();
		}
		
		// Check that there are no records in the account_history table with a creation_timestamp >= $strTimestamp, as this would flag a data integrity problem
		$selCheckTimestamp = new StatementSelect("account_history", array("id"), "change_timestamp >= <Timestamp>", NULL, 1);
		if (($intRecCount = $selCheckTimestamp->Execute(array("Timestamp"=>$strTimestamp))) === FALSE)
		{
			throw new Exception_Database("Failed during check for account_history records with timestamp >= $strTimestamp - ". $selCheckTimestamp->Error());
		}
		if ($intRecCount != 0)
		{
			throw new Exception("Requested Timestamp for recording of account state ($strTimestamp) is less than or equal to the timestamp marked against at least one account in the account_history table, signifying a breach of data integrity");
		}
		
		$arrTrackedAccountColumns	= self::getTrackedAccountColumns();
		$arrTrackedHistoryColumns	= array_keys($arrTrackedAccountColumns);
		$strTrackedColumnsForInsert	= implode(", ", $arrTrackedHistoryColumns);
		$strTackedAccountColumns	= "a.". implode(", a.", $arrTrackedAccountColumns);
		$arrComparingValues			= array();
		foreach ($arrTrackedAccountColumns as $strHistoryColumn=>$strAccountColumn)
		{
			$arrComparingValues[] = "a.$strAccountColumn != ah.$strHistoryColumn";
		}
		$strCheckForChanges = implode(" OR ", $arrComparingValues);
		
		// Build the insert query
		/*	It should look like this:
			INSERT INTO account_history (change_timestamp, employee_id, account_id, billing_type, credit_card_id, direct_debit_id, billing_method, disable_ddr, late_payment_amnesty, tio_reference_number)
			SELECT <Timestamp>, <EmployeeId>, a.id, a.BillingType, a.CreditCard, a.DirectDebit, a.BillingMethod, a.DisableDDR, a.LatePaymentAmnesty, a.tio_reference_number
			FROM Account AS a LEFT JOIN account_history AS ah 
				ON a.id = ah.account_id AND ah.id = (	SELECT id
									FROM account_history
									WHERE account_id = a.id
									ORDER BY change_timestamp DESC
									LIMIT 1
								)
			WHERE ah.id IS NULL OR (	a.BillingType != ah.billing_type
										OR a.CreditCard != ah.credit_card_id
										OR a.DirectDebit != ah.direct_debit_id
										OR a.BillingMethod != ah.billing_method
										OR a.DisableDDR != ah.disable_ddr
										OR a.LatePaymentAmnesty != ah.late_payment_amnesty
										OR a.tio_reference_number != ah.tio_reference_number
									)
		 */
		 
		$strInsertQuery = "	INSERT INTO account_history (change_timestamp, employee_id, account_id, $strTrackedColumnsForInsert)
							SELECT '$strTimestamp', $intEmployeeId, a.Id, $strTackedAccountColumns
							FROM Account AS a LEFT JOIN account_history AS ah 
							ON a.Id = ah.account_id AND ah.id = (	SELECT id
																	FROM account_history
																	WHERE account_id = a.Id
																	ORDER BY change_timestamp DESC
																	LIMIT 1
																)
							WHERE ah.id IS NULL OR ($strCheckForChanges)";

		$qryQuery = new Query();
		$objRecordSet = $qryQuery->Execute($strInsertQuery);
		if (!$objRecordSet)
		{
			throw new Exception_Database("Updating the account_history table failed. Query: $strInsertQuery Error: " . $qryQuery->Error());
		}
	}
	
	public static function getAccountColumns()
	{
		$arrTrackedColumns = self::getTrackedAccountColumns();
		$arrTrackedColumns['account_id'] = "Id";
		return $arrTrackedColumns;
	}
	
	public static function getTrackedAccountColumns()
	{
		return array(
						"billing_type"			=> "BillingType",
						"credit_card_id"		=> "CreditCard",
						"direct_debit_id"		=> "DirectDebit",
						"billing_method"		=> "BillingMethod",
						"disable_ddr"			=> "DisableDDR",
						"late_payment_amnesty"	=> "LatePaymentAmnesty",
						"tio_reference_number"	=> "tio_reference_number",
						"account_class_id"		=> "account_class_id"
					);
	}
	
	public static function getAccountHistoryColumns()
	{
		return array(	
						"id"					=> "id",
						"change_timestamp"		=> "change_timestamp",
						"employee_id"			=> "employee_id",
						"account_id"			=> "account_id",
						"billing_type"			=> "billing_type",
						"credit_card_id"		=> "credit_card_id",
						"direct_debit_id"		=> "direct_debit_id",
						"billing_method"		=> "billing_method",
						"disable_ddr"			=> "disable_ddr",
						"late_payment_amnesty"	=> "late_payment_amnesty",
						"tio_reference_number"	=> "tio_reference_number",
						"account_class_id"		=> "account_class_id"
					);
	}

	public static function getForAccountAndEffectiveDatetime($iAccountId, $sEffectiveDatetime=null)
	{
		// NOTE: 	Effective datetime: means that the change timestamp must be the latest 
		//			possible up to the and including effectived datetime 
				
		// Default effective datetime is now
		if ($sEffectiveDatetime === null)
		{
			$sEffectiveDatetime	= date('Y-m-d H:i:s');
		}
		
		// Use prepared statment
		$oStmt	= self::_preparedStatement('selByAccountIdAndEffectiveDatetime');
		$iRows	= $oStmt->Execute(array('account_id' => $iAccountId, 'effective_datetime' => $sEffectiveDatetime));
		if ($iRows === false)
		{
			throw new Exception_Database("Failed to get account history for account & effective date time. ".$oStmt->Error());
		}
		
		if ($aRow = $oStmt->Fetch())
		{
			return $aRow;	
		}
		
		return null;
	}

	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_sStaticTableName, "*", "id = <id>", NULL, 1);
					break;
				case 'selByAccountIdAndEffectiveDatetime':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_sStaticTableName, "*", "account_id = <account_id> AND change_timestamp <= <effective_datetime>", "change_timestamp DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert(self::$_sStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById(self::$_sStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}

?>
