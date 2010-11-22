<?php

class Invoice_Interim
{
	private static 	$_aInterimEligibilityColumns	=	
		array
		(
			'ACCOUNT_ID'							=> 'Account',
			'ACCOUNT_NAME'							=> 'Account Name',
			'DELIVERY_METHOD'						=> 'Delivery Method',
			'SERVICE_FNN'							=> 'Service FNN',
			'ACTIVE_SERVICES'						=> 'Total No. Active Services',
			'ACTIVE_PENDING_SERVICES'				=> 'Total No. Active & Pending Services',
			'HAS_TOLLED'							=> 'Has Tolled',
			'CURRENT_PLAN'							=> 'Current Plan',
			'REQUIRES_CDR'							=> 'Requires CDR',
			'MONTHLY_PLAN_FEE'						=> 'Monthly Plan Fee',
			'DAILY_RATE'							=> 'Daily Plan Fee',
			'PLAN_CHARGE'							=> 'Plan Charge',
			'PLAN_CHARGE_DAYS'						=> 'Plan Charge Days',
			'PLAN_CHARGE_DESCRIPTION'				=> 'Plan Charge Description',
			'INTERIM_PLAN_CREDIT'					=> 'Plan Credit - Interim Bill',
			'INTERIM_PLAN_CREDIT_DAYS'				=> 'Plan Credit - Interim Bill - Days',
			'INTERIM_PLAN_CREDIT_DESCRIPTION'		=> 'Plan Credit Description - Interim Bill',
			'PRODUCTION_PLAN_CREDIT'				=> 'Plan Credit - 1st Bill',
			'PRODUCTION_PLAN_CREDIT_DAYS'			=> 'Plan Credit - 1st Bill - Days',
			'PRODUCTION_PLAN_CREDIT_DESCRIPTION'	=> 'Plan Credit Description - 1st Bill',
			'SERVICE_ELIGIBLE'						=> 'Service Eligible',
			'ACCOUNT_ELIGIBLE'						=> 'Account Eligible',
			'EXCLUDED_REQUIRES_TOLLING'				=> 'Excluded Requires Tolling',
			'EXCLUDED_PENDING_SERVICES'				=> 'Excluded Pending Services'
		);
	
	private static 	$_aInterimExceptionsColumns	=	
		array
		(
			'ACCOUNT_ID'	=> 'Account',
			'SERVICE_FNN'	=> 'Service FNN',
			'REASON'		=> 'Reason for Exception'
		);
	
	private static	$_aInterimProcessingColumns	=	
		array
		(
			'ACCOUNT_ID'							=> 'Account',
			'SERVICE_FNN'							=> 'Service FNN',
			'PLAN_CHARGE'							=> 'Plan Charge',
			'PLAN_CHARGE_DESCRIPTION'				=> 'Plan Charge Description',
			'INTERIM_PLAN_CREDIT'					=> 'Plan Credit - Interim Bill',
			'INTERIM_PLAN_CREDIT_DESCRIPTION'		=> 'Plan Credit Description - Interim Bill',
			'PART_MONTH_PLAN_DEBIT'					=> 'Part Month - Plan Charge Debit',
			'PART_MONTH_PLAN_DEBIT_DESCRIPTION'		=> 'Description Part Month - Plan Charge Debit',
			'PRODUCTION_PLAN_CREDIT'				=> 'Plan Credit - 1st Bill',
			'PRODUCTION_PLAN_CREDIT_DESCRIPTION'	=> 'Plan Credit Description - 1st Bill',
		);
	
	/*
	 * Public functions
	 */
	
	// submitAllEligible
	public static function submitAllEligible()
	{
		Log::getLog()->log("Getting list of eligible accounts & services");
		
		$aServices	= self::_getEligibleServices();
		
		Log::getLog()->log("Generating the eligiblity report");
		
		$oCSVFile	= self::generateEligibilityReport($aServices);
		
		Log::getLog()->log("Creating account list where all services are white listed");
		
		// Create account list where all services are white listed
		$aAccounts	= array();
		foreach ($aServices as $aService)
		{
			// Determine the proper FNN --> Dirty hacks to prepend 0s to FNNs which have had them stripped off
			$sFNN	= $aService['fnn'];
			$sFNN	= self::preg_match_string("/\d{9,10}(i)?$/", $sFNN);
			if ($sFNN[0] != '0' && !preg_match("/^(13\d{4}|1[38]00\d{6})$/", $sFNN))
			{
				$sFNN	= '0'.$sFNN;
			}
			
			Log::getLog()->log("Adding Account {$iAccountId} -> Service {$sFNN}");
			
			// Cache the FNN as white listed, due there being no re-submission of the eligibility report
			$iAccountId	= (int)$aService['account_id'];
			if (!isset($aAccounts[$iAccountId]))
			{
				$aAccounts[$iAccountId]	=	array(
												'aBlacklist'	=> array(), 
												'aWhitelist'	=> array(), 
												'aGreylist'		=> array()
											);
			}
			$aAccounts[$iAccountId]['aWhitelist'][$sFNN]	= true;
			
			Log::getLog()->log("Account {$iAccountId} -> Service {$sFNN} added to white list");
		}
		
		Log::getLog()->log("Generating customer group grouping of account/service data");
		
		$aCustomerGroups	= self::_preProcessEligibleAccounts($aServices, $aAccounts);
		
		Log::getLog()->log("Processing account/service data");
		
		self::_processEligibleAccounts(
			$aServices, 
			$aCustomerGroups, 
			$oCSVFile, 
			"auto-interim-invoice-eligibility-report-".date('Ymd')
		);
		
		Log::getLog()->log("All eligible interim first invoices submitted");
	}
	
	// generateEligibilityReport
	public static function generateEligibilityReport($aServices=null)
	{
		if (is_null($aServices))
		{
			$aServices	= self::_getEligibleServices();
		}
		
		// Prepare the CSV File
		$oCSVFile	= new File_CSV();
		$oCSVFile->setColumns(array_values(self::$_aInterimEligibilityColumns));
		
		// Determine account level eligibility
		$aAccountEligible	= array();
		foreach ($aServices as $aService)
		{
			$sAccountId	= $aService['account_id'];
			if (!isset($aAccountEligible[$sAccountId]))
			{
				$aAccountEligible[$sAccountId]	= true;
			}
			
			// Account is eligible if the service doesn't require tolling and has no pending services
			$aAccountEligible[$sAccountId]	= ($aAccountEligible[$sAccountId] && ($aService['requires_tolling'] == 0) && ($aService['has_pending_services'] == 0));
		}
		
		// Get data & insert into the CSV Report
		foreach ($aServices as &$aService)
		{
			$aOutput	= array();
			
			$aOutput[self::$_aInterimEligibilityColumns['ACCOUNT_ID']]							= $aService['account_id'];
			$aOutput[self::$_aInterimEligibilityColumns['ACCOUNT_NAME']]						= $aService['account_name'];
			$aOutput[self::$_aInterimEligibilityColumns['DELIVERY_METHOD']]						= $aService['delivery_method'];
			$aOutput[self::$_aInterimEligibilityColumns['SERVICE_FNN']]							= $aService['fnn'];
			$aOutput[self::$_aInterimEligibilityColumns['ACTIVE_SERVICES']]						= $aService['services_active'];
			$aOutput[self::$_aInterimEligibilityColumns['ACTIVE_PENDING_SERVICES']]				= ((int)$aService['services_pending'] + (int)$aService['services_active']);
			$aOutput[self::$_aInterimEligibilityColumns['HAS_TOLLED']]							= ($aService['has_tolled']) ? 'Yes' : 'No';
			$aOutput[self::$_aInterimEligibilityColumns['CURRENT_PLAN']]						= $aService['rate_plan_name'];
			$aOutput[self::$_aInterimEligibilityColumns['REQUIRES_CDR']]						= ($aService['cdr_required']) ? 'Yes' : 'No';
			$aOutput[self::$_aInterimEligibilityColumns['MONTHLY_PLAN_FEE']]					= number_format((float)$aService['plan_charge'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['DAILY_RATE']]							= (float)$aService['aCharges']['daily_rate'];
			$aOutput[self::$_aInterimEligibilityColumns['PLAN_CHARGE']]							= number_format((float)$aService['aCharges']['plan_charge'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DAYS']]					= $aService['aCharges']['plan_charge_days'];
			$aOutput[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DESCRIPTION']]				= $aService['aCharges']['plan_charge_description'];
			$aOutput[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT']]					= number_format((float)$aService['aCharges']['interim_plan_credit'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DAYS']]			= $aService['aCharges']['interim_plan_credit_days'];
			$aOutput[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']]		= $aService['aCharges']['interim_plan_credit_description'];
			$aOutput[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT']]				= number_format((float)$aService['aCharges']['production_plan_credit'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DAYS']]			= $aService['aCharges']['production_plan_credit_days'];
			$aOutput[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']]	= $aService['aCharges']['production_plan_credit_description'];
			$aOutput[self::$_aInterimEligibilityColumns['SERVICE_ELIGIBLE']]					= ((($aService['requires_tolling'] == 0) && ($aService['has_pending_services'] == 0)) ? 'Yes' : 'No');
			$aOutput[self::$_aInterimEligibilityColumns['ACCOUNT_ELIGIBLE']]					= ($aAccountEligible[$aService['account_id']] ? 'Yes' : 'No');
			$aOutput[self::$_aInterimEligibilityColumns['EXCLUDED_REQUIRES_TOLLING']]			= $aService['requires_tolling'];
			$aOutput[self::$_aInterimEligibilityColumns['EXCLUDED_PENDING_SERVICES']]			= $aService['has_pending_services'];
			
			// Add the CSV
			$oCSVFile->addRow($aOutput);
		}
		
		unset($aService);
		
		return $oCSVFile;
	}
	
	// processEligibilityReport
	public static function processEligibilityReport($sFilePath)
	{
		// Ensure the submitted file meets a few contraints
		// MIME Type
		$sMIMEType	= mime_content_type($sFilePath);
		if (!in_array($sMIMEType, array('text/csv', 'text/plain')))
		{
			throw new Exception("The submitted File is of the wrong File Type. (Expected: text/csv; Actual: {$sMIMEType})");
		}
		
		// Parse the Report (header row is assumed to be included and is parsed from the file) 
		$oCSVImportFile	= new File_CSV();
		$oCSVImportFile->importFile($sFilePath, true, true);
		
		// Get updated eligibility list
		$aServices	= self::_getEligibleServices();
		$aAccounts	= array();
		
		// Verify the details for all of the submitted Services
		foreach ($oCSVImportFile as $aImportService)
		{
			$sAccountId	= $aImportService[self::$_aInterimEligibilityColumns['ACCOUNT_ID']];
			if (!is_numeric($sAccountId))
			{
				// Invalid account id, skip
				Log::getLog()->log("Invalid account id '{$sAccountId}', skipped line");
				continue;
			}
			
			$iAccountId	= (int)$sAccountId;
			
			// Dirty hacks to prepend 0s to FNNs which have had them stripped off
			$sFNN	= $aImportService[self::$_aInterimEligibilityColumns['SERVICE_FNN']];
			$sFNN	= self::preg_match_string("/\d{9,10}(i)?$/", $sFNN);
			if ($sFNN[0] != '0' && !preg_match("/^(13\d{4}|1[38]00\d{6})$/", $sFNN))
			{
				$sFNN	= '0'.$sFNN;
			}
			
			Log::getLog()->log("Verifying account & FNN: {$iAccountId}, {$sFNN}");
			
			if (!array_key_exists($iAccountId, $aAccounts))
			{
				$aAccounts[$iAccountId]	= array('aBlacklist'=>array(), 'aWhitelist'=>array(), 'aGreylist'=>array());
			}
			
			$sAccountServiceIndex	= "{$iAccountId}.{$sFNN}";
			
			try
			{
				// Does this Service exist in the current eligibility list?
				if (array_key_exists($sAccountServiceIndex, $aServices))
				{
					// Found it -- do our figures match?
					$aService	= &$aServices[$sAccountServiceIndex];
					
					// Monthly Plan Fee
					self::_compareInterimEligible(	
						(float)$aImportService[self::$_aInterimEligibilityColumns['MONTHLY_PLAN_FEE']],
						(float)$aService['plan_charge'],
						"Monthly Plan Fee mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['MONTHLY_PLAN_FEE']]."'; Calculated: '".(float)$aService['plan_charge']."')"
					);
					
					// Daily Rate
					self::_compareInterimEligible(
						(float)$aImportService[self::$_aInterimEligibilityColumns['DAILY_RATE']],
						(float)$aService['aCharges']['daily_rate'],
						"Daily Rate mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['DAILY_RATE']]."'; Calculated: '".(float)$aService['aCharges']['daily_rate']."')"
					);
					
					// Plan Charge
					self::_compareInterimEligible(
						(float)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE']],
						(float)$aService['aCharges']['plan_charge'],
						"Plan Charge mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE']]."'; Calculated: '".(float)$aService['aCharges']['plan_charge']."')"
					);
					
					// Plan Charge Days
					self::_compareInterimEligible(
						(int)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DAYS']],
						(int)$aService['aCharges']['plan_charge_days'],
						"Plan Charge Days mismatch (Supplied: '".(int)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DAYS']]."'; Calculated: '".(int)$aService['aCharges']['plan_charge_days']."')"
					);
					
					// Plan Charge Description
					self::_compareInterimEligible(
						(string)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DESCRIPTION']],
						(string)$aService['aCharges']['plan_charge_description'],
						"Plan Charge Description mismatch (Supplied: '".$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DESCRIPTION']]."'; Calculated: '".$aService['aCharges']['plan_charge_description']."')",
						false
					);
					
					// Interim Plan Credit
					self::_compareInterimEligible(
						(float)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT']],
						(float)$aService['aCharges']['interim_plan_credit'],
						"Interim Plan Credit mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT']]."'; Calculated: '".(float)$aService['aCharges']['interim_plan_credit']."')"
					);
					
					// Interim Plan Credit Days
					self::_compareInterimEligible(
						(int)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DAYS']],
						(int)$aService['aCharges']['interim_plan_credit_days'],
						"Interim Plan Credit Days mismatch (Supplied: '".(int)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DAYS']]."'; Calculated: '".(int)$aService['aCharges']['interim_plan_credit_days']."')"
					);
					
					// Interim Plan Credit Description
					self::_compareInterimEligible(
						(string)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']],
						(string)$aService['aCharges']['interim_plan_credit_description'],
						"Interim Plan Credit Description mismatch (Supplied: '".$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']]."'; Calculated: '".$aService['aCharges']['interim_plan_credit_description']."')",
						false
					);
					
					// Production Plan Credit
					self::_compareInterimEligible(
						(float)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT']],
						(float)$aService['aCharges']['production_plan_credit'],
						"Production Plan Credit mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT']]."'; Calculated: '".(float)$aService['aCharges']['production_plan_credit']."')"
					);
					
					// Production Plan Credit Days
					self::_compareInterimEligible(
						(int)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DAYS']],
						(int)$aService['aCharges']['production_plan_credit_days'],
						"Production Plan Credit Days mismatch (Supplied: '".(int)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DAYS']]."'; Calculated: '".(int)$aService['aCharges']['production_plan_credit_days']."')"
					);
					
					// Production Plan Credit Description
					self::_compareInterimEligible(
						(string)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']],
						(string)$aService['aCharges']['production_plan_credit_description'],
						"Production Plan Credit Description mismatch (Supplied: '{$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']]}'; Calculated: '{$aService['aCharges']['production_plan_credit_description']}')",
						false
					);
					
					// Excluded Requires Tolling
					self::_compareInterimEligible(
						(int)$aImportService[self::$_aInterimEligibilityColumns['EXCLUDED_REQUIRES_TOLLING']],
						(int)$aService['requires_tolling'],
						"Excluded Requires Tolling mismatch (Supplied: '".$aImportService[self::$_aInterimEligibilityColumns['EXCLUDED_REQUIRES_TOLLING']]."'; Calculated: '".$aService['requires_tolling']."')"
					);
					
					// Excluded Pending Services
					self::_compareInterimEligible(
						(int)$aImportService[self::$_aInterimEligibilityColumns['EXCLUDED_PENDING_SERVICES']],
						(int)$aService['has_pending_services'],
						"Excluded Pending Services mismatch (Supplied: '".$aImportService[self::$_aInterimEligibilityColumns['EXCLUDED_PENDING_SERVICES']]."'; Calculated: '".$aService['has_pending_services']."')"
					);
					
					// Everthing appears to match -- add to Action list
					$aAccounts[$iAccountId]['aWhitelist'][$sFNN]	= true;
					
					// Clear the $aService reference
					unset($aService);
				}
				else
				{
					// Can't find it -- this Service is probably not eligible anymore
					throw new Exception_Invoice_Interim_EligibilityMismatch("{$sFNN} exists in the resubmitted Authentication Report, but not in the current Eligibility Report");
				}
			}
			catch (Exception_Invoice_Interim_EligibilityMismatch $eException)
			{
				// Add to the Blacklist
				$aAccounts[$iAccountId]['aBlacklist'][$sFNN]	= $eException->getMessage();
			}
		}
		
		// Check to see if the number of submitted eligible Services for an Account matches the new list.
		foreach ($aServices as $sAccountServiceIndex => $aService)
		{
			$aAccountServiceIndex	= explode('.', $sAccountServiceIndex);
			$iAccountId				= $aAccountServiceIndex[0];
			$sFNN					= $aAccountServiceIndex[1];
			
			// Service is fully eligible
			if (!array_key_exists($iAccountId, $aAccounts))
			{
				$aAccounts[$iAccountId]	= array('aBlacklist'=>array(), 'aWhitelist'=>array(), 'aGreylist'=>array());
			}
			
			if (!array_key_exists($sFNN, $aAccounts[$iAccountId]['aWhitelist']) && !array_key_exists($sFNN, $aAccounts[$iAccountId]['aBlacklist']))
			{
				// Service wasn't referenced in the Submitted Report -- add to Greylist
				 $aAccounts[$iAccountId]['aGreylist'][$sFNN]	= "{$sFNN} exists in the current Eligibility Report, but not in the resubmitted Authentication Report";
			}
		}
		
		$aCustomerGroups	= self::_preProcessEligibleAccounts($aServices, $aAccounts);
		self::_processEligibleAccounts(
			$aServices, 
			$aCustomerGroups, 
			$oCSVImportFile, 
			basename($sFilePath), 
			self::generateEligibilityReport($aServices)
		);
	}
	
	public static function getTemporaryFirstInterimInvoiceBillingDates()
	{
		try
		{
			// Query for billing dates
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute("SELECT	DISTINCT BillingDate
											FROM	InvoiceRun
											WHERE	invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY."
											AND		invoice_run_type_id = ".INVOICE_RUN_TYPE_INTERIM_FIRST);
			if ($mResult === false)
			{
				throw new Exception("Query failed.");
			}
			
			// Build array of date strings
			$aDates	= array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$aDates[]	= $aRow['BillingDate'];
			}
			
			return $aDates;
		} 
		catch (Exception $oEx)
		{
			throw new Exception("Failed to get temporary First Interim Invoice billing dates. ".$oException->getMessage());
		}
	}
	
	/*
	 * Private functions
	 */
	
	// _preProcessEligibleAccounts
	private static function _preProcessEligibleAccounts(&$aServices, &$aAccounts)
	{
		// Remove any accounts that are ineligible
		$aAccountCustomerGroups	= array();
		foreach ($aServices as $sAccountServiceIndex => $aService)
		{
			$aAccountServiceIndex	= explode('.', $sAccountServiceIndex);
			$iAccountId				= $aAccountServiceIndex[0];
			$sFNN					= $aAccountServiceIndex[1];
			
			// Check the services full eligibility
			$bEligible	= ($aService['requires_tolling'] == 0) && ($aService['has_pending_services'] == 0);
			if (!$bEligible)
			{
				// Ineligible, remove the service
				unset($aServices[$sAccountServiceIndex]);
				
				// Remove the account (if not already done)
				if (isset($aAccounts[$iAccountId]))
				{
					unset($aAccounts[$iAccountId]);
				}
				
				Log::getLog()->log("Service ineligible: {$sAccountServiceIndex}, service & account removed");
			}
			
			// Cache the customer group against the account id for later
			$aAccountCustomerGroups[$iAccountId]	= $aService['customer_group'];
		}
		
		// Build (customer group) -> (account array) hash
		$aCustomerGroups	= array();
		foreach ($aAccounts as $iAccountId => $aAccount)
		{
			$iCustomerGroupId	= $aAccountCustomerGroups[$iAccountId];
			if (!isset($aCustomerGroups[$iCustomerGroupId]))
			{
				$aCustomerGroups[$iCustomerGroupId]	= array();
			}
			$aCustomerGroups[$iCustomerGroupId][$iAccountId]	= $aAccount;
		}
		
		return $aCustomerGroups;
	}
	
	// _processEligibleAccounts
	private static function _processEligibleAccounts($aServices, $aCustomerGroups, $oCSVEligibleReport, $sCSVEligibleFilename, $oOldCSVEligibleReport=null)
	{
		// Start the outer transaction
		$oFlexDataAccess	= DataAccess::getDataAccess();
		if (!$oFlexDataAccess->TransactionStart())
		{
			throw new Exception("There was an internal error in Flex.  Please notify YBS of this issue with the following message: 'Unable to start a Transaction'");
		}
		
		try
		{
			Log::getLog()->log("Create csv files for exception reporting");
			
			// Action the Eligible Accounts
			$oCSVExceptionsReport	= new File_CSV();
			$oCSVExceptionsReport->setColumns(array_values(self::$_aInterimExceptionsColumns));
			
			$oCSVProcessingReport	= new File_CSV();
			$oCSVProcessingReport->setColumns(array_values(self::$_aInterimProcessingColumns));
			
			$iAccountsInvoiced			= 0;
			$iAccountsIgnored			= 0;
			$iAccountsFailed			= 0;
			$iAccountsChargesAdded		= 0;
			$iServicesInvoiced			= 0;
			$iServicesIgnored			= 0;
			$iServicesFailed			= 0;
			$iServicesChargesAdded		= 0;
			$fTotalPlanCharge			= 0.0;
			$fTotalInterimPlanCredit	= 0.0;
			$fTotalProductionPlanCredit	= 0.0;
			
			// Process all accounts, one customer group at a time
			foreach ($aCustomerGroups as $iCustomerGroupId => $aAccounts)
			{
				Log::getLog()->log("Processing customer group {$iCustomerGroupId}");
				
				// Create an invoice run for all of the eligible accounts in the customer group
				if (!$oFlexDataAccess->TransactionStart())
				{
					throw new Exception("There was an internal error in Flex.  Please notify YBS of this issue with the following message: 'Unable to start the inner Transaction' ({$oFlexDataAccess->refMysqliConnection->error})");
				}
				try
				{
					// Build a list of accounts to create invoices for within this customer group
					$aAccountsToInvoice	= array();
					foreach ($aAccounts as $iAccountId => $aAccount)
					{
						Log::getLog()->log("Processing Account {$iAccountId}");
						
						// If we have any Exceptions, add all Black/Whitelisted Services to the Exceptions report
						if (count($aAccount['aBlacklist']))
						{
							Log::getLog()->log("Account {$iAccountId} has black listed services, adding all services to exception report");
							
							foreach ($aAccount['aBlacklist'] as $sFNN => $sReason)
							{
								$oCSVExceptionsReport->addRow(
									array 
									(
										self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
										self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> $sFNN,
										self::$_aInterimExceptionsColumns['REASON']			=> $sReason
									)
								);
								
								Log::getLog()->log("BLACK LIST: Account={$iAccountId}, FNN={$sFNN}, Reason={$sReason}");
								
								$iServicesFailed++;
							}
							
							foreach ($aAccount['aGreylist'] as $sFNN => $sReason)
							{
								$oCSVExceptionsReport->addRow(
									array 
									(
										self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
										self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> $sFNN,
										self::$_aInterimExceptionsColumns['REASON']			=> "Account {$iAccountId} Rejected -- check other Services for details"
									)
								);
								
								Log::getLog()->log("GREY LIST: Account={$iAccountId}, FNN={$sFNN}, Reason=Implied");
								
								$iServicesFailed++;
							}
							
							foreach ($aAccount['aWhitelist'] as $sFNN => $bWhitelisted)
							{
								$oCSVExceptionsReport->addRow(
									array 
									(
										self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
										self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> $sFNN,
										self::$_aInterimExceptionsColumns['REASON']			=> "Account {$iAccountId} Rejected -- check other Services for details"
									)
								);
								
								Log::getLog()->log("WHITE LIST: Account={$iAccountId}, FNN={$sFNN}, Reason=Implied");
								
								$iServicesFailed++;
							}
							
							$iAccountsFailed++;
						}
						elseif (!count($aAccount['aWhitelist']))
						{
							Log::getLog()->log("Account {$iAccountId} has no listed services, Ignoring the account");
							
							// Ignore this Account
							$iAccountsIgnored++;
							$iServicesIgnored	+= count($aAccount['aGreylist']);
						}
						else
						{
							Log::getLog()->log("Account {$iAccountId} is to be added to the run");
							
							// Action this Account! Add the Charges for each Service
							foreach($aAccount['aWhitelist'] as $sFNN=>$bWhitelisted)
							{
								Log::getLog()->log("Applying interim invoice charges to Account {$iAccountId} -> Service {$sFNN}");
								
								self::_applyInterimInvoiceCharges($aServices["{$iAccountId}.{$sFNN}"]);
							}
							
							// Add to the list of accounts to receive invoices
							$aAccountsToInvoice[]	= Account::getForId($iAccountId);
						}
					}
					// END: Build a list of accounts to create invoices for within this customer group
					
					try
					{
						Log::getLog()->log("Creating an interim first invoice run for customer group {$iCustomerGroupId} with ".(count($aAccountsToInvoice))." accounts");
						
						// Create an invoice run (of type 'INVOICE_RUN_TYPE_INTERIM_FIRST')
						$oInvoiceRun	= new Invoice_Run();
						$oInvoiceRun->generateForAccounts($iCustomerGroupId, $aAccountsToInvoice, INVOICE_RUN_TYPE_INTERIM_FIRST);
					}
					catch (Exception $oEx)
					{
						Log::getLog()->log("Failed to create invoice run, revoke it");
						
						// Perform a Revoke on the Temporary Invoice Run
						if ($oInvoiceRun->Id)
						{
							$oInvoiceRun->revoke();
						}
						throw $oEx;
					}
					
					// Add to Processing Report (all Services that had Debits/Credits added)
					foreach ($aAccounts as $iAccountId => $aAccount)
					{
						Log::getLog()->log("Adding account {$iAccountId} to processing report");
						
						if (!isset($aAccount['aWhitelist']))
						{
							throw new Exception("No white list for $iAccountId. debug::'".print_r($aAccounts, true)."'");
						}
						
						$bAccountHasCharges	= false;
						foreach ($aAccount['aWhitelist'] as $sFNN => $bWhitelisted)
						{
							$sServiceKey	= "{$iAccountId}.{$sFNN}";
							if ($aServices[$sServiceKey]['aCharges']['plan_charge'])
							{
								Log::getLog()->log("Adding account {$iAccountId} -> Service {$sFNN} to processing report");
								
								// Get 'Part Month Plan Charge Debit' & 'Description Plan Charge Debit'
								$aService			= $aServices[$sServiceKey];
								$oQuery				= new Query();
								$oPlanChargeDebit	=	$oQuery->Execute(
															"	SELECT	*
																FROM	Charge
																WHERE	invoice_run_id = {$oInvoiceRun->Id}
																AND		Account = ".$aService['account_id']."
																AND		Service = ".$aService['service_id']."
																AND		ChargeType = 'PCAR'
																AND		Id <> ".$aService['aCharges']['plan_charge_id']
														);
								if ($oPlanChargeDebit === false)
								{
									throw new Exception_Database($oQuery->Error());
								}
								$aPlanChargeDebitRow	= $oPlanChargeDebit->fetch_assoc();
								
								// Add row to the csv object
								$oCSVProcessingReport->addRow(
									array
									(
										self::$_aInterimProcessingColumns['ACCOUNT_ID']							=> $iAccountId,
										self::$_aInterimProcessingColumns['SERVICE_FNN']						=> $sFNN,
										self::$_aInterimProcessingColumns['PLAN_CHARGE']						=> number_format($aServices[$sServiceKey]['aCharges']['plan_charge'], 2, '.', ''),
										self::$_aInterimProcessingColumns['PLAN_CHARGE_DESCRIPTION']			=> $aServices[$sServiceKey]['aCharges']['plan_charge_description'],
										self::$_aInterimProcessingColumns['INTERIM_PLAN_CREDIT']				=> number_format($aServices[$sServiceKey]['aCharges']['interim_plan_credit'], 2, '.', ''),
										self::$_aInterimProcessingColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']	=> $aServices[$sServiceKey]['aCharges']['interim_plan_credit_description'],
										self::$_aInterimProcessingColumns['PART_MONTH_PLAN_DEBIT']				=> number_format($aPlanChargeDebitRow['Amount'], 2, '.', ''),
										self::$_aInterimProcessingColumns['PART_MONTH_PLAN_DEBIT_DESCRIPTION']	=> $aPlanChargeDebitRow['Description'],
										self::$_aInterimProcessingColumns['PRODUCTION_PLAN_CREDIT']				=> number_format($aServices[$sServiceKey]['aCharges']['production_plan_credit'], 2, '.', ''),
										self::$_aInterimProcessingColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']	=> $aServices[$sServiceKey]['aCharges']['production_plan_credit_description'],
									)
								);
								
								$fTotalPlanCharge			+= $aServices[$sServiceKey]['aCharges']['plan_charge'];
								$fTotalInterimPlanCredit	+= $aServices[$sServiceKey]['aCharges']['interim_plan_credit'];
								$fTotalProductionPlanCredit	+= $aServices[$sServiceKey]['aCharges']['production_plan_credit'];
								$bAccountHasCharges			= true;
								$iServicesChargesAdded++;
							}
						}
						
						$iAccountsChargesAdded	+= ($bAccountHasCharges) ? 1 : 0;
						$iServicesInvoiced		+= count($aAccount['aWhitelist']);
						$iAccountsInvoiced++;
					}
					// END: Add to Processing Report
					
					// Commit the inner transaction (invoice run created successfully)
					$oFlexDataAccess->TransactionCommit();
				}
				catch (Exception $oException)
				{
					// Exception caught when creating invoice run for the customer group, rollback any invoices created
					$oFlexDataAccess->TransactionRollback();
					$oCSVExceptionsReport->addRow(
						array
						(
							self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
							self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> '[ ALL ]',
							self::$_aInterimExceptionsColumns['REASON']			=> "Error while generating the Interim Invoice: ".$oException->getMessage()
						)
					);
					
					$iServicesFailed	+= count($aAccount['aWhitelist']);
					$iAccountsFailed++;
				}
				// END: Create an invoice run for all of the eligible accounts in the customer group
			}
			// END: Process all accounts, one customer group at a time
			
			Log::getLog()->log("Generate & Send Processing/Exceptions Report");
			
			// Generate & Send Processing/Exceptions Report
			$bHasExceptions	= (bool)$oCSVExceptionsReport->count();
			
			$oProcessingEmailNotification	= new Email_Notification(EMAIL_NOTIFICATION_FIRST_INTERIM_INVOICE_REPORT);
			$oProcessingEmailNotification->setSubject("First Interim Invoice Processing/Exceptions Report - ".date('Y-m-d H:i:s'));
			
			$sProcessingReportFileName	= "processing-report-".date("YmdHis").".csv";
			$oProcessingEmailNotification->addAttachment(
				$oCSVProcessingReport->save(), 
				$sProcessingReportFileName, 
				'text/csv'
			);
			
			if (is_null($oOldCSVEligibleReport))
			{
				// No 'old' (current) report, new one must be from an auto submit
				$sSubmittedEligibilityReportFileName	= "auto-submitted-{$sCSVEligibleFilename}.csv";
			}
			else
			{
				// Have an 'old' (current) report, new one must have been submitted
				$sSubmittedEligibilityReportFileName	= "submitted-{$sCSVEligibleFilename}.csv";
			}
			
			$oProcessingEmailNotification->addAttachment(
				$oCSVEligibleReport->save(), 
				$sSubmittedEligibilityReportFileName, 
				'text/csv'
			);
			
			if ($bHasExceptions)
			{
				Log::getLog()->log("We have exceptions");
				
				if (is_null($oOldCSVEligibleReport))
				{
					Log::getLog()->log("No older elibility report submitted, using the same dataset to generate one for the email");
					
					$oOldCSVEligibleReport	= self::generateEligibilityReport($aServices);
				}
				
				$sCurrentEligibilityReportFileName	= "current-interim-invoice-eligibility-report-".date("YmdHis").".csv";
				$oProcessingEmailNotification->addAttachment(
					$oOldCSVEligibleReport->save(false), 
					$sCurrentEligibilityReportFileName, 
					'text/csv'
				);
				
				$sExceptionsReportFileName	= "exceptions-report-".date("YmdHis").".csv";
				$oProcessingEmailNotification->addAttachment(
					$oCSVExceptionsReport->save(), 
					$sExceptionsReportFileName, 
					'text/csv'
				);
				
				$sReportsSummary	.= "<li><strong>Processing Report</strong><em> ({$sProcessingReportFileName})</em> &mdash;&nbsp;Lists which Accounts/Services had Interim Invoice Charges added to them</li>
										<li><strong>Exceptions Report</strong><em> ({$sExceptionsReportFileName})</em> &mdash;&nbsp;Lists which Accounts/Services failed in processing and the reasons why</li>
										<li><strong>Submitted Interim Eligibility Report</strong><em> ({$sSubmittedEligibilityReportFileName})</em> &mdash;&nbsp;The Report you submitted to initiate this process</li>
										<li><strong>Current Interim Eligibility Report</strong><em> ({$sCurrentEligibilityReportFileName})</em> &mdash;&nbsp;Current version of the Interim Eligibility Report</li>";
			}
			else
			{
				Log::getLog()->log("We have no exceptions");
				
				$sReportsSummary	.= "<li><strong>Processing Report</strong><em> ({$sProcessingReportFileName})</em> &mdash;&nbsp;Lists which Accounts/Services had Interim Invoice Charges added to them</li>
										<li><strong>Submitted Interim Eligibility Report</strong><em> ({$sSubmittedEligibilityReportFileName})</em> &mdash;&nbsp;The Report you submitted to initiate this process</li>";
			}
				
			$sEmailBody	= "	<div style='font-family: Calibri,Arial,sans-serif;'>
								<h1 style='font-size: 1.5em;'>First Interim Invoice Processing Report</h1>
								
								<p>
									Please find attached the following Reports:
									<ul>
										{$sReportsSummary}
									</ul>
								</p>
								
								<table style='font-family: Calibri,Arial,sans-serif; border: 1px solid #111; border-collapse: collapse;'>
									<tbody>
										<tr>
											<td style='vertical-align: top; padding: 1em;'>
												<h2 style='font-size: 1.2em;'>Invoice Summary</h2>
												<table style='font-family: Calibri,Arial,sans-serif; margin-left: 0.5em; font-family: inherit;'>
													<tbody>
														<tr>
															<th style='text-align: left;' >Accounts Invoiced&nbsp;:&nbsp;</th>
															<td>{$iAccountsInvoiced}</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Services Invoiced&nbsp;:&nbsp;</th>
															<td>{$iServicesInvoiced}</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Accounts Ignored&nbsp;:&nbsp;</th>
															<td>{$iAccountsIgnored}</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Services Ignored&nbsp;:&nbsp;</th>
															<td>{$iServicesIgnored}</td>
														</tr>
													</tbody>
												</table>
											</td>
											<td rowspan='2' style='vertical-align: top; border-left: 1px solid #111; padding: 1em;'>
												<h2 style='font-size: 1.2em;'>Charges Summary</h2>
												<table style='font-family: Calibri,Arial,sans-serif; margin-left: 0.5em; font-family: inherit;'>
													<tbody>
														<tr>
															<th style='text-align: left;' >Accounts with Charges&nbsp;:&nbsp;</th>
															<td>{$iAccountsChargesAdded}</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Services with Charges&nbsp;:&nbsp;</th>
															<td>{$iServicesChargesAdded}</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Total Plan Charge Value&nbsp;:&nbsp;</th>
															<td>\$".number_format($fTotalPlanCharge, 2, '.', ',')."</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Total Interim Plan Credit Value&nbsp;:&nbsp;</th>
															<td>\$".number_format($fTotalInterimPlanCredit, 2, '.', ',')."</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Total Production Plan Credit Value&nbsp;:&nbsp;</th>
															<td>\$".number_format($fTotalProductionPlanCredit, 2, '.', ',')."</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style='vertical-align: top; padding: 1em; border-top: 1px solid #111;'>
												<h2 style='font-size: 1.2em;'>Exceptions Summary (see Exceptions Report for details)</h2>
												<table style='font-family: Calibri,Arial,sans-serif; margin-left: 0.5em; font-family: inherit;'>
													<tbody>
														<tr>
															<th style='text-align: left;' >Accounts Failed&nbsp;:&nbsp;</th>
															<td>{$iAccountsFailed}</td>
														</tr>
														<tr>
															<th style='text-align: left;' >Services Failed&nbsp;:&nbsp;</th>
															<td>{$iServicesFailed}</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
								</table>
								
								<p>
									Regards<br />
									<strong>Flexor</strong>
								</p>
							</div>";
			
			$oProcessingEmailNotification->setBodyHTML($sEmailBody);
			$oProcessingEmailNotification->send();
			
			//throw new Exception("TEST MODE -- Everything worked");
			
			// Everything looks ok -- Commit
			$oFlexDataAccess->TransactionCommit();
		}
		catch (Exception $eException)
		{
			// Rollback the Transaction and pass the Exception through
			$oFlexDataAccess->TransactionRollback();
			throw $eException;
		}
		
		Log::getLog()->log("Completed processing eligible accounts");
		
		$aChanges							= array();
		$aChanges['iAccountsInvoiced']		= $iAccountsInvoiced;
		$aChanges['iAccountsIgnored']		= $iAccountsIgnored;
		$aChanges['iAccountsFailed']		= $iAccountsFailed;
		$aChanges['iAccountsChargesAdded']	= $iAccountsChargesAdded;
		$aChanges['iServicesInvoiced']		= $iServicesInvoiced;
		$aChanges['iServicesIgnored']		= $iServicesIgnored;
		$aChanges['iServicesFailed']		= $iServicesFailed;
		$aChanges['iServicesChargesAdded']	= $iServicesChargesAdded;
		
		return $aChanges;
	}
	
	private static function _getEligibleServices()
	{
		$iStart	= time();
		$oQuery	= new Query();
		
		// part 1
		$mResult	= $oQuery->Execute("	CREATE TEMPORARY TABLE service_status_count
											(
												account_id				BIGINT,
												services_active			INT,
												services_pending		INT,
												shared_services_active	INT
											)ENGINE=memory;");
		if ($mResult === false)
		{
			throw new Exception_Database("failed part 1. ".$oQuery->Error());
		}
		
		$iPart1	= time();
		Log::getLog()->log("Temp table created: ".($iPart1 - $iStart));
		
		// part 1a
		$mResult	= $oQuery->Execute("ALTER TABLE service_status_count ADD INDEX (account_id);");
		if ($mResult === false)
		{
			throw new Exception_Database("failed part 1. ".$oQuery->Error());
		}
		
		$iPart1a	= time();
		Log::getLog()->log("Index added: ".($iPart1a - $iStart));
		
		// part 2
		$mResult	= $oQuery->Execute("	INSERT INTO service_status_count (account_id, services_active, services_pending, shared_services_active)
											SELECT		Service.Account AS account_id,
														COUNT(IF(service_status.const_name = 'SERVICE_ACTIVE', Service.Id, NULL)) AS services_active,
														COUNT(IF(service_status.const_name = 'SERVICE_PENDING', Service.Id, NULL)) AS services_pending,
														COUNT(IF(RatePlan.Shared = 1 AND service_status.const_name = 'SERVICE_ACTIVE', Service.Id, NULL)) AS shared_services_active
											FROM		Service
														JOIN service_status ON (service_status.id = Service.Status)
														JOIN ServiceRatePlan ON (Service.Id = ServiceRatePlan.Service AND NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime)
														JOIN RatePlan ON (ServiceRatePlan.RatePlan = RatePlan.Id)
											WHERE		ServiceRatePlan.Id =	(
																					SELECT		Id
																					FROM		ServiceRatePlan
																					WHERE		NOW() BETWEEN StartDatetime AND EndDatetime
																								AND Service = Service.Id
																					ORDER BY	CreatedOn DESC
																					LIMIT		1
																				)
											GROUP BY	Service.Account
											HAVING		shared_services_active = 0
														AND services_active > 0");
		if ($mResult === false)
		{
			throw new Exception_Database("failed part 2. ".$oQuery->Error());
		}
		
		$iPart2	= time();
		Log::getLog()->log("Temp table populated: ".($iPart2 - $iPart1));
		
		$sSQL	= "	SELECT		a.Id																											AS account_id,
								a.BusinessName																									AS account_name,
								dm.name																											AS delivery_method,
								CASE
									WHEN CAST(DATE_FORMAT(CURDATE(), '%d') AS UNSIGNED) < pt.invoice_day
										THEN CAST(DATE_FORMAT(CURDATE(), CONCAT('%Y-%m-', LPAD(pt.invoice_day, 2, '0'))) AS DATE)
									ELSE
										ADDDATE(CAST(DATE_FORMAT(CURDATE(), CONCAT('%Y-%m-', LPAD(pt.invoice_day, 2, '0'))) AS DATE), INTERVAL 1 MONTH)
								END																												AS next_invoice_date,
								service_status_count.services_active																			AS services_active,
								service_status_count.services_pending																			AS services_pending,
								s.Id																											AS service_id,
								s.FNN																											AS fnn,
								srp.RatePlan																									AS rate_plan_id,
								rp.Name																											AS rate_plan_name,
								rp.MinMonthly																									AS plan_charge,
								IF(s.EarliestCDR IS NOT NULL, 1, 0)																				AS has_tolled,
								rp.cdr_required																									AS cdr_required,
								CASE
									WHEN rp.cdr_required THEN IF(CAST(s.EarliestCDR AS DATE) <= CURDATE(), s.EarliestCDR, NULL /* LAST INVOICE DATE */)
									ELSE IF(s.CreatedOn > srp.StartDatetime, s.CreatedOn, srp.StartDatetime)
								END																												AS invoice_from_date,
								IF((rp.cdr_required = 1) AND (s.EarliestCDR IS NULL), 1, 0)														AS requires_tolling,	/* Has not tolled and requires tolling */
								IF(service_status_count.services_active = service_status_count.services_pending, 1, 0)							AS has_pending_services,	/* The number of active services doesn't equal the number of active + number of pending services */
								cg.Id																											AS customer_group,
								ir_last.Id																										AS last_invoice_run,
								irt_last.const_name																								AS last_invoice_run_type_name
					FROM		Account a
								JOIN account_status a_s ON (
									a.Archived = a_s.id
									AND	a_s.const_name = 'ACCOUNT_STATUS_ACTIVE'
								)
								JOIN delivery_method dm ON (dm.id = a.BillingMethod)
								JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
								JOIN payment_terms pt ON (cg.Id = pt.customer_group_id)
								JOIN service_status_count ON (a.Id = service_status_count.account_id)
								JOIN Service s ON (a.Id = s.Account)
								JOIN service_status ss ON (
									s.Status = ss.id
									AND	ss.const_name IN ('SERVICE_ACTIVE')
								)
								JOIN ServiceRatePlan srp ON (
									srp.Service = s.Id 
									AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
									AND srp.Id = (
										SELECT		Id
										FROM		ServiceRatePlan
										WHERE		Service = s.Id
													AND NOW() BETWEEN StartDatetime AND EndDatetime
										ORDER BY	CreatedOn DESC
										LIMIT		1
									)
								)
								JOIN RatePlan rp ON (srp.RatePlan = rp.Id)
								LEFT JOIN
								(
									Invoice i_last
									JOIN InvoiceRun ir_last ON (i_last.invoice_run_id = ir_last.Id)
									JOIN invoice_run_type irt_last ON 
									(
										irt_last.id = ir_last.invoice_run_type_id
										AND irt_last.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES')
									)
								) ON (a.Id = i_last.Account)
					HAVING		next_invoice_date >= ADDDATE(CURDATE(), INTERVAL 7 DAY)
								AND services_active > 0";
		
		// Run Query
		$rResult	= $oQuery->Execute($sSQL);
		if ($rResult === false)
		{
			throw new Exception_Database($oQuery->Error());
		}
		
		$iPart3	= time();
		Log::getLog()->log("Select executed : ".($iPart3 - $iPart2));
		
		// Build list of services & accounts
		$aServices				= array();
		$aAccounts				= array();
		$iInvoiceRunCheck		= 0;
		$iInvoiceRunTypeCheck	= 0;
		$iChargeCheck			= 0;
		$iServiceTypeTotalCheck	= 0;
		$iInvoiceCheck			= 0;
		$iNoLastInvoiceRun		= 0;
		while ($aService = $rResult->fetch_assoc())
		{
			if ($aService['last_invoice_run'] !== null)
			{
				// Check that the last invoice run is the latest for the account and was not a 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES' or 'INVOICE_RUN_TYPE_SAMPLES'
				$mResult	= $oQuery->Execute("	SELECT		InvoiceRun.Id
													FROM		InvoiceRun
																JOIN invoice_run_type ON (invoice_run_type.id = InvoiceRun.invoice_run_type_id)
																JOIN Invoice ON (InvoiceRun.Id = Invoice.invoice_run_id)
													WHERE		Invoice.Account = {$aService['account_id']}
																AND invoice_run_type.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES')
													ORDER BY	Invoice.CreatedOn DESC
													LIMIT		1");
				if ($mResult === false)
				{
					throw new Exception_Database("Failed loop check 0. ".$oQuery->Error());
				}
				
				$aIR	= $mResult->fetch_assoc();
				if (!$aIR || $aIR['Id'] != $aService['last_invoice_run'])
				{
					// Skip this service
					$iInvoiceRunCheck++;
					continue;
				}
				
				// Check that the invoice_run_type of the last invoice run is NOT interim, final or interim first.
				if (in_array($aService['last_invoice_run_type_name'], array('INVOICE_RUN_TYPE_INTERIM', 'INVOICE_RUN_TYPE_FINAL', 'INVOICE_RUN_TYPE_INTERIM_FIRST')))
				{
					// Skip this service
					$iInvoiceRunTypeCheck++;
					continue;
				}
				
				// Check that the number of (non samples) plan charges for the account is 0
				$mResult	= $oQuery->Execute("	SELECT		COUNT(c.Id) as count
													FROM		Charge c
																JOIN InvoiceRun ir_charge ON (ir_charge.Id = c.invoice_run_id)
																JOIN invoice_run_type irt_charge ON (ir_charge.invoice_run_type_id = irt_charge.id AND irt_charge.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES'))
													WHERE		c.Account = {$aService['account_id']}
																AND c.ChargeType IN ('PCAD', 'PCAR')
													LIMIT		1");
				if ($mResult === false)
				{
					throw new Exception_Database("Failed loop check 1. ".$oQuery->Error());
				}
				
				$aCount	= $mResult->fetch_assoc();
				if (!$aCount || $aCount['count'] != 0)
				{
					// Skip this service
					$iChargeCheck++;
					continue;
				}
				
				// Check that the number of (non samples) S&E (service and equipment) service type total records for the account is 0
				$mResult	= $oQuery->Execute("	SELECT		COUNT(stt.Id) as count
													FROM		ServiceTypeTotal stt
																JOIN InvoiceRun ir_cdr ON (ir_cdr.Id = stt.invoice_run_id)
																JOIN invoice_run_type irt_cdr ON (ir_cdr.invoice_run_type_id = irt_cdr.id AND irt_cdr.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES'))
																JOIN RecordType rt ON (stt.RecordType = rt.Id)
													WHERE		stt.Account = {$aService['account_id']}
																AND rt.Code IN ('S&E')
																AND stt.Records > 0
													LIMIT		1");
				if ($mResult === false)
				{
					throw new Exception_Database("Failed loop check 2. ".$oQuery->Error());
				}
				
				$aCount	= $mResult->fetch_assoc();
				if (!$aCount || $aCount['count'] != 0)
				{
					// Skip this service
					$iServiceTypeTotalCheck++;
					continue;
				}
				
				// Check that there is 3 or less past invoices which are NOT temporary
				$mResult	= $oQuery->Execute("	SELECT 	COUNT(Invoice.Id) 
													FROM 	Invoice 
													WHERE 	Invoice.Account = {$aService['account_id']}
													AND 	Status NOT IN (100)");
				if ($mResult === false)
				{
					throw new Exception_Database("Failed loop check 3. ".$oQuery->Error());
				}
				
				$aCount	= $mResult->fetch_assoc();
				if (!$aCount || $aCount['count'] > 3)
				{
					// Skip this service
					$iInvoiceCheck++;
					continue;
				}
			}
			else
			{
				// No last invoice run
				$iNoLastInvoiceRun++;
			}
			
			$aService['aCharges']	= self::_calculateInterimInvoiceCharges($aService);
			if (!array_key_exists($aService['account_id'], $aAccounts))
			{
				$aAccounts[$aService['account_id']]	=	array(
															'aServices' 		=> array(), 
															'bChargeEligible'	=> false
														);
			}
			
			// Add to Account's list of Services
			$aAccounts[$aService['account_id']]['aServices'][$aService['fnn']]	= $aService;
			
			// If this Service will receive Interim Charges, then this Account is eligible for 
			// 1st Interim Invoicing
			if (!$aAccounts[$aService['account_id']]['bChargeEligible'])
			{
				if	($aService['aCharges']['plan_charge'])
				{
					$aAccounts[$aService['account_id']]['bChargeEligible']	= true;
				}
			}
		}
		
		$iPart4	= time();
		Log::getLog()->log("First loop : ".($iPart4 - $iPart3));
		Log::getLog()->log("Skipped:");
		Log::getLog()->log("\tInvoice Run check: {$iInvoiceRunCheck}");
		Log::getLog()->log("\tInvoice Run Type check: {$iInvoiceRunTypeCheck}");
		Log::getLog()->log("\tCharge check: {$iChargeCheck}");
		Log::getLog()->log("\tS&E ServiceTypeTotal check: {$iServiceTypeTotalCheck}");
		Log::getLog()->log("\tInvoice Count check: {$iInvoiceCheck}");
		Log::getLog()->log("No last invoice run: {$iNoLastInvoiceRun}");
		
		// Check Account-level Eligibility
		foreach ($aAccounts as $iAccount=>$aAccount)
		{
			// Add Services if at least one of the Account's Services will receive an Interim Charge
			if ($aAccount['bChargeEligible'])
			{
				foreach ($aAccount['aServices'] as $sFNN=>$aService)
				{
					$aServices["{$aService['account_id']}.{$aService['fnn']}"]	= $aService;
				}
			}
		}
		
		// Drop the service_status_count table
		/*$mResult	= $oQuery->Execute("	DROP TEMPORARY TABLE service_status_count;");
		if ($mResult === false)
		{
			throw new Exception_Database("failed part 5. ".$oQuery->Error());
		}*/
		
		$iEnd	= time();
		Log::getLog()->log("Finished : ".($iEnd - $iStart)." (Total)");
		
		return $aServices;
	}
	
	// _calculateInterimInvoiceCharges
	private static function _calculateInterimInvoiceCharges($aService)
	{
		$aCharges	=	array
							(
								'daily_rate'							=> 0.0,
								'plan_charge'							=> 0.0,
								'plan_charge_days'						=> 0,
								'plan_charge_description'				=> '',
								'interim_plan_credit'					=> 0.0,
								'interim_plan_credit_days'				=> 0,
								'interim_plan_credit_description'		=> '',
								'production_plan_credit'				=> 0.0,
								'production_plan_credit_days'			=> 0,
								'production_plan_credit_description'	=> ''
							);
		
		$iTime	= time();
		$sDate	= date('Y-m-d', $iTime);
		$iDate	= strtotime($sDate);
		
		$iNextInvoiceDate	= strtotime($aService['next_invoice_date']);
		$iLastInvoiceDate	= strtotime("-1 month", $iNextInvoiceDate);
		
		$iBillingPeriodEndDate	= $iNextInvoiceDate - Flex_Date::SECONDS_IN_DAY;
		
		$fPlanCharge			= (float)$aService['plan_charge'];
		
		if ($fPlanCharge && $aService['invoice_from_date'])
		{
			$iServiceInvoiceFromDate	= ($aService['invoice_from_date']) ? strtotime($aService['invoice_from_date']) : $iLastInvoiceDate;
			
			// Calculate Daily Rate
			$iDaysInBillingPeriod	= (int)date('t', $iLastInvoiceDate);
			$iBillingPeriodDays		= floor(Flex_Date::periodLength($iLastInvoiceDate, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aCharges['billing_period_start']	= date("Y-m-d H:i:s", $iLastInvoiceDate);
			$aCharges['billing_period_end']		= date("Y-m-d H:i:s", $iBillingPeriodEndDate);
			$aCharges['billing_period_days']	= $iBillingPeriodDays;
			
			// Tidy Plan Charge
			$iProratePeriodDays	= floor(Flex_Date::periodLength($iServiceInvoiceFromDate, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aCharges['daily_rate']			= $fPlanCharge / $iBillingPeriodDays;
			$aCharges['plan_charge_days']	= $iProratePeriodDays;
			
			$fltProratedAmount				= ($fPlanCharge / $iBillingPeriodDays) * $iProratePeriodDays;
			$aCharges['plan_charge']		= round($fltProratedAmount, 2);
			
			$aCharges['plan_charge_description']	= Invoice::buildPlanChargeDescription($aService['rate_plan_name'], Charge_Type::getByCode('PCAR')->Description, $iServiceInvoiceFromDate, $iBillingPeriodEndDate);
			
			// Interim Invoice Credit
			$iProratePeriodDays	= floor(Flex_Date::periodLength($iServiceInvoiceFromDate, $iDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			//$iBillingPeriodDays	= floor(Flex_Date::periodLength($iLastInvoiceDate, $iDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aCharges['interim_plan_credit_days']	= $iProratePeriodDays;
			
			$fltProratedAmount						= ($fPlanCharge / $iBillingPeriodDays) * $iProratePeriodDays;
			$aCharges['interim_plan_credit']		= round($fltProratedAmount, 2);
			
			$aCharges['interim_plan_credit_description']	= Invoice::buildPlanChargeDescription($aService['rate_plan_name'], Charge_Type::getByCode('PCAR')->Description, $iServiceInvoiceFromDate, $iDate);
			
			// Production Invoice Credit
			$iProratePeriodDays	= floor(Flex_Date::periodLength($iDate + Flex_Date::SECONDS_IN_DAY, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			//$iBillingPeriodDays	= floor(Flex_Date::periodLength($iLastInvoiceDate, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aCharges['production_plan_credit_days']	= $iProratePeriodDays;
			$fltProratedAmount							= ($fPlanCharge / $iBillingPeriodDays) * $iProratePeriodDays;
			$aCharges['production_plan_credit']			= round($fltProratedAmount, 2);
			
			$aCharges['production_plan_credit_description']	= Invoice::buildPlanChargeDescription($aService['rate_plan_name'], Charge_Type::getByCode('PCAR')->Description, $iDate + Flex_Date::SECONDS_IN_DAY, $iBillingPeriodEndDate);
		}
		
		return $aCharges;
	}
	
	// _applyInterimInvoiceCharges
	private static function _applyInterimInvoiceCharges(&$aService)
	{
		// Skip Charges with no value
		if (!(float)$aService['aCharges']['plan_charge'])
		{
			return;
		}
		
		static	$aChargeTypes;
		if (!isset($aChargeTypes))
		{
			$aChargeTypes			= array();
			$aChargeTypes['PCAR']	= Charge_Type::getByCode('PCAR');
			$aChargeTypes['PCAD']	= Charge_Type::getByCode('PCAD');
		}
		
		try
		{
			$oAccount	= Account::getForId($aService['account_id']);
			
			// Add new Plan Charge
			$oPlanCharge	= new Charge();
			
			$oPlanCharge->AccountGroup		= $oAccount->AccountGroup;
			$oPlanCharge->Account			= $oAccount->Id;
			$oPlanCharge->Service			= $aService['service_id'];
			$oPlanCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oPlanCharge->CreatedOn			= date("Y-m-d");
			$oPlanCharge->ApprovedBy		= Employee::SYSTEM_EMPLOYEE_ID;
			$oPlanCharge->ChargeType		= 'PCAR';
			$oPlanCharge->charge_type_id	= $aChargeTypes['PCAR']->Id;
			$oPlanCharge->Description		= $aService['aCharges']['plan_charge_description'];
			$oPlanCharge->ChargedOn			= date("Y-m-d");
			$oPlanCharge->Nature			= 'DR';
			$oPlanCharge->Amount			= $aService['aCharges']['plan_charge'];
			$oPlanCharge->Status			= CHARGE_APPROVED;
			$oPlanCharge->Notes				= '1st Interim Invoice Plan Debit';
			$oPlanCharge->global_tax_exempt	= false;
			$oPlanCharge->save();
			$aService['aCharges']['plan_charge_id']	= $oPlanCharge->Id;
			
			// Add Interim Plan Credit
			$oInterimPlanCredit	= new Charge();
			
			$oInterimPlanCredit->AccountGroup		= $oAccount->AccountGroup;
			$oInterimPlanCredit->Account			= $oAccount->Id;
			$oInterimPlanCredit->Service			= $aService['service_id'];
			$oInterimPlanCredit->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oInterimPlanCredit->CreatedOn			= date("Y-m-d");
			$oInterimPlanCredit->ApprovedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oInterimPlanCredit->ChargeType			= 'PCAR';
			$oInterimPlanCredit->charge_type_id		= $aChargeTypes['PCAR']->Id;
			$oInterimPlanCredit->Description		= $aService['aCharges']['interim_plan_credit_description'];
			$oInterimPlanCredit->ChargedOn			= date("Y-m-d");
			$oInterimPlanCredit->Nature				= 'CR';
			$oInterimPlanCredit->Amount				= abs($aService['aCharges']['interim_plan_credit']);
			$oInterimPlanCredit->Status				= CHARGE_APPROVED;
			$oInterimPlanCredit->Notes				= '1st Interim Invoice Plan Credit';
			$oInterimPlanCredit->global_tax_exempt	= false;
			
			$oInterimPlanCredit->save();
			
			// Add Production Plan Credit
			$oProductionPlanCredit	= new Charge();
			
			$oProductionPlanCredit->AccountGroup		= $oAccount->AccountGroup;
			$oProductionPlanCredit->Account				= $oAccount->Id;
			$oProductionPlanCredit->Service				= $aService['service_id'];
			$oProductionPlanCredit->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oProductionPlanCredit->CreatedOn			= date("Y-m-d");
			$oProductionPlanCredit->ApprovedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oProductionPlanCredit->ChargeType			= 'PCAR';
			$oProductionPlanCredit->charge_type_id		= $aChargeTypes['PCAR']->Id;
			$oProductionPlanCredit->Description			= $aService['aCharges']['production_plan_credit_description'];
			$oProductionPlanCredit->ChargedOn			= date("Y-m-d", strtotime("+1 day", time()));
			$oProductionPlanCredit->Nature				= 'CR';
			$oProductionPlanCredit->Amount				= abs($aService['aCharges']['production_plan_credit']);
			$oProductionPlanCredit->Status				= CHARGE_APPROVED;
			$oProductionPlanCredit->Notes				= '1st Production Invoice Plan Credit';
			$oProductionPlanCredit->global_tax_exempt	= false;
			
			$oProductionPlanCredit->save();
		}
		catch (Exception $eException)
		{
			throw new Exception("There was an error adding the Interim Charges: ".$eException->getMessage());
		}
	}
	
	// _compareInterimEligible
	private static function _compareInterimEligible($mLeftValue, $mRightValue, $sMessage, $bStrict=true)
	{
		// Round floats to 8 decimal places
		$mLeftValue		= (is_float($mLeftValue)) ? round($mLeftValue, 8) : $mLeftValue;
		$mRightValue	= (is_float($mRightValue)) ? round($mRightValue, 8) : $mRightValue;
		
		return self::_assertInterimEligible((($bStrict && $mLeftValue === $mRightValue) || $mLeftValue == $mRightValue), $sMessage);
	}
	
	// _assertInterimEligible
	private static function _assertInterimEligible($mExpression, $sMessage)
	{
		if (!(bool)$mExpression)
		{
			throw new Exception_Invoice_Interim_EligibilityMismatch($sMessage);
		}
		return true;
	}
	
	// preg_match_string
	public static function preg_match_string($sRegex, $sMatch)
	{
		$aMatches	= array();
		preg_match($sRegex, $sMatch, $aMatches);
		return (count($aMatches)) ? $aMatches[0] : null;
	}
}

class Exception_Invoice_Interim_EligibilityMismatch extends Exception{};

?>