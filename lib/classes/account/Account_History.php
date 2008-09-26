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
	const SYSTEM_ACTION_EMPLOYEE_ID = NULL;

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
			$selAccountHistory = new StatementSelect("account_history", self::getAccountHistoryColumns(), "account_id = <AccountId>", "creation_timestamp DESC", 1);
		}
		
		if (($intRecCount = $selAccountHistory->Execute(array("account_id"=>$intAccountId))) === FALSE)
		{
			throw new Exception("Failed to retrieve current account_history record for account id: $intAccountId - ". $selAccountHistory->Error());
		}
		
		$bolHistoryNeedsUpdating = FALSE;
		$arrTrackedProperties = self::getTrackedAccountColumns();
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
			if ($strTimestamp <= $arrAccountHistory['creation_timestamp'])
			{
				throw new Exception("Requested Timestamp for recording of account state ($strTimestamp) is less than or equal to the timestamp marked against the current record in the account_history table modelling the current state of the account ({$arrAccountHistory['creation_timestamp']}), signifying a breach of data integrity");
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
			$arrData['creation_timestamp']	= $strTimestamp;
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
				throw new Exception("Failed to insert record into account_history table for account: $intAccountId - ". $insAccountHistory->Error());
			}
			
			return TRUE;
		}

		return FALSE;
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
						"billing_method"		=> "BillingMethod",
						"disable_ddr"			=> "DisableDDR",
						"late_payment_amnesty"	=> "LatePaymentAmnesty",
						"tio_reference_number"	=> "tio_reference_number"
					);
	}
	
	public static function getAccountHistoryColumns()
	{
		return array(	
						"id"					=> "id",
						"creation_timestamp"	=> "creation_timestamp",
						"employee_id"			=> "employee_id",
						"account_id"			=> "account_id",
						"billing_type"			=> "BillingType",
						"billing_method"		=> "BillingMethod",
						"disable_ddr"			=> "DisableDDR",
						"late_payment_amnesty"	=> "LatePaymentAmnesty",
						"tio_reference_number"	=> "tio_reference_number"
					);
	}

}

?>
