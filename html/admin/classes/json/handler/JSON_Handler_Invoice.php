<?php

class JSON_Handler_Invoice extends JSON_Handler
{
	protected	$_sJSONDebug				= '';
	private		$_oNewInvoiceSummary		= null;
			
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_sJSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getServicesForInvoice($iInvoiceId)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		try
		{
			$aServices	= array();
			$oInvoice	= Invoice::getForId($iInvoiceId);
			
			// Get service totals info
			$aServiceTotals		= Service_Total::getForInvoiceRunAndAccount($oInvoice->invoice_run_id, $oInvoice->Account);
			foreach ($aServiceTotals as $oServiceTotal)
			{
				$oService		= Service::getForId($oServiceTotal->Service);
				$oStdService	= $oService->toStdClass();
				
				// Get the rate plan for the service during the time of invoice
				$oQuery		= new Query();
				$mResult	= $oQuery->Execute("	SELECT 	* 
													FROM 	ServiceRatePlan 
													WHERE Service = {$oService->Id}  
													AND '{$oInvoice->billing_period_end_datetime}' BETWEEN StartDatetime AND EndDatetime 
													ORDER BY CreatedOn DESC 
													LIMIT 1");
				if ($mResult === false)
				{
					throw new Exception("Failed to find ServiceRatePlan record, query failed. ".$oQuery->Error());
				}
				$aRow	= $mResult->fetch_assoc();
				if (!isset($aRow['RatePlan']))
				{
					throw new Exception("Failed to find ServiceRatePlan record, query result empty. ");
				}
				
				$oStdService->rate_plan		= Rate_Plan::getForId($aRow['RatePlan'])->toStdClass();
				$aServices[$oService->Id]	= $oStdService;
			}
			
			return	array(
						'bSuccess'	=> true,
						'aServices'	=> $aServices,
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (Exception $oEx)
		{
			return array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bIsGod ? $oEx->getMessage() : ''),
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
	}
	
	public function hasUnarchivedCDRs($iInvoiceId)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$bHasUnarchivedCDRs	= Invoice::getForId($iInvoiceId)->hasUnarchivedCDRs();
			return	array(
						'bSuccess'				=> true,
						'bHasUnarchivedCDRs'	=> $bHasUnarchivedCDRs,
						'sDebug'				=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (Exception $oEx)
		{
			return array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bIsGod ? $oEx->getMessage() : ''),
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
	}
	
	public function rerateInvoice($iInvoiceId, $aServiceRatePlans)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		// Start transaction (never to be commited)
		$oDA	= DataAccess::getDataAccess();
		$oDA->TransactionStart();
		try
		{
			Log::getLog()->log("Rerating Invoice {$iInvoiceId}");
			
			// Retrieve the original invoice to re-rate
			$oOriginalInvoice	= Invoice::getForId($iInvoiceId);
			
			Log::getLog()->log("START: Changing rate plans for services");
			
			// Change the rate plans for the services temporarily (is rolled back at the end of this process)
			foreach ($aServiceRatePlans as $iService => $iRatePlan)
			{
				Log::getLog()->log("Service {$iService} -> RatePlan {$iRatePlan}");
				
				$oRatePlan	= new Rate_Plan(array('Id' => $iRatePlan), true);
				Service::getForId($iService)->setPlanFromStartDatetime($oRatePlan, 1, $oOriginalInvoice->billing_period_start_datetime);
				
				Log::getLog()->log("... Complete");
			}
			
			Log::getLog()->log("END: Changing rate plans for services");
			
			// Regenerate the invoice (re-rating of CDRs is done within)
			$oNewInvoice	= Invoice::regenerate($oOriginalInvoice, Callback::create('regenerateFinished', $this, array($oOriginalInvoice)));
			
			Log::getLog()->log("Rolling back transaction");
			
			// ALWAYS ROLLBACK THIS TRANSACTION, NEVER COMMIT. ALL CHANGES ARE TEMPORARY
			$oDA->TransactionRollback();
			
			Log::getLog()->log("Getting summary of the ORIGINAL invoice");
		
			$oOriginalInvoiceSummary	= $this->_generateInvoiceSummary($oOriginalInvoice);
			
			Log::getLog()->log("Complete with no errors");
			
			return	array(
						'bSuccess' 			=> true,
						'oNewInvoice' 		=> $this->_oNewInvoiceSummary, 
						'oOriginalInvoice' 	=> $oOriginalInvoiceSummary,
						'sDebug'			=> ($bUserIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (Exception $oException)
		{
			$oDA->TransactionRollback();
			return	array(
						'bSuccess'	=> false, 
						'sMessage'	=> "Failed to Re-Rate Invoice '{$iInvoiceId}'. ".($bUserIsGod ? $oException->getMessage() : "There was an error accessing the database."),
						'sDebug'	=> ($bUserIsGod ? $this->_sJSONDebug : false)
					);
		}
	}
	
	public function regenerateFinished($oOriginalInvoice, $oNewInvoice)
	{
		Log::getLog()->log("Getting summary of the NEW (Rerated) invoice");
		
		$this->_oNewInvoiceSummary	= $this->_generateInvoiceSummary($oNewInvoice);
	}
	
	public function getReratableInvoicesForAccount($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			if (!Account::getForId((int)$iAccountId))
			{
				throw new JSON_Handler_Invoice_Exception('Invalid Account Id supplied');
			}
			
			$sQuery		= "	SELECT	i.*
							FROM	Invoice i
							JOIN	Account a ON a.Id = i.Account
							WHERE	1 = (
										SELECT 	1
										FROM 	CDR 
										WHERE 	invoice_run_id = i.invoice_run_id
										AND		Account = a.Id
										LIMIT 1
									)
							AND		a.Id = {$iAccountId}";
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				throw new Exception("Failed to get reratable invoices for account {$iAccountId}. ".$oQuery->Error());
			}
			
			$aResults	= array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$aRow['CreatedOn']	= date('d/m/y', strtotime($aRow['CreatedOn']));
				$aResults[]			= $aRow;
			}
			
			return 	array(
						'bSuccess'	=> true,
						'aResults'	=> $aResults,
						'sDebug'	=> ($bUserIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (JSON_Handler_Invoice_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage(),
						'sDebug'	=> ($bUserIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oException->getMessage() : "There was an error accessing the database."),
						'sDebug'	=> ($bUserIsGod ? $this->_sJSONDebug : false)
					);
		}
	}
	
	private function _generateInvoiceSummary($oInvoice)
	{
		try
		{
			Log::getLog()->log("START: Generating summary for invoice '{$oInvoice->Id}'");
			// Get the invoice object
			$oStdInvoice	= $oInvoice->toStdClass();
			
			Log::getLog()->log("Getting service totals");
			
			// Attach service totals
			$aServiceTotals		= Service_Total::getForInvoiceRunAndAccount($oInvoice->invoice_run_id, $oInvoice->Account);
			$aStdServiceTotals	= array();
			foreach ($aServiceTotals as $iId => $oServiceTotal)
			{
				$oStdServiceTotal					= $oServiceTotal->toStdClass();
				$oRatePlan							= Rate_Plan::getForId($oServiceTotal->RatePlan);
				$oStdServiceTotal->rate_plan_name	= $oRatePlan->Name;
				$aStdServiceTotals[$iId]			= $oStdServiceTotal;
			}
			
			$oStdInvoice->service_totals	= $aStdServiceTotals;
			
			Log::getLog()->log("Getting charges (CHARGE_MODE_CHARGE only, no adjustments)");
			
			// Attach charges (CHARGE_MODEL_CHARGE only)
			$aFilter 		= 	array(
									array(
										'Type'	=> Charge::SEARCH_CONSTRAINT_INVOICE_RUN_ID,
										'Value'	=> $oInvoice->invoice_run_id
									),
									array(
										'Type'	=> Charge::SEARCH_CONSTRAINT_ACCOUNT_ID,
										'Value'	=> $oInvoice->Account
									),
									array(
										'Type'	=> Charge::SEARCH_CONSTRAINT_CHARGE_MODEL_ID,
										'Value'	=> CHARGE_MODEL_CHARGE
									)
								);
			$aCharges		= Charge::searchFor($aFilter, array(Charge::ORDER_BY_CREATED_ON => true), null, 0);
			
			Log::getLog()->log("Building charge totals summary");
			
			$aStdCharges				= array();
			$fAccountChargesAndCredits	= 0;
			$fSharedPlanCharges			= 0;
			$fSharedPlanDiscounts		= 0;
			foreach ($aCharges as $iId => $oCharge)
			{
				if (!isset($aStdCharges[$oCharge->ChargeType]))
				{
					$aStdCharges[$oCharge->ChargeType]	= array('name' => Charge_Type::getByCode($oCharge->ChargeType)->Description, 'charges' => array());
				}
				$aStdCharges[$oCharge->ChargeType]['charges'][$iId]	= $oCharge->toStdClass();
				
				$fAmount	= $oCharge->Amount;
				if ($oCharge->Nature == 'CR')
				{
					$fAmount	= 0 - $fAmount;
				}
				
				$bIsPlanCharge	= in_array($oCharge->ChargeType, array('PCAD', 'PCAR', 'PCR', 'PDCR'));
				if ($oCharge->Service !== null)
				{
					$aStdCharges[$oCharge->ChargeType]['service_totals'][$oCharge->Service]	+= $fAmount;
				}
				else if ($bIsPlanCharge)
				{
					$fSharedPlanCharges	+= $fAmount;
				}
				else 
				{
					$fAccountChargesAndCredits	+= $fAmount;
					
					Log::getLog()->log("Adding to account charges and credits total: {$fAmount}, current total is {$fAccountChargesAndCredits}");
				}
			}
			
			$oStdInvoice->charges						= $aStdCharges;
			$oStdInvoice->account_charges_and_credits	= $fAccountChargesAndCredits;
			$oStdInvoice->shared_plan_charges			= $fSharedPlanCharges;
			$oStdInvoice->shared_plan_discounts			= $fSharedPlanDiscounts;
			
			Log::getLog()->log("Getting CDR usages totals per service");
			
			// Attach CDR usage totals per service
			$sCDRQuery	= "	SELECT 	Service, SUM(Charge) AS \"Usage\"
							FROM 	CDR
							WHERE 	Account = {$oStdInvoice->Account}
							AND 	invoice_run_id = {$oStdInvoice->invoice_run_id}
							GROUP BY Service";
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute($sCDRQuery);
			if ($mResult === false)
			{
				throw new Exception("Failed to retrieve CDR usage totals per service. ".$oQuery->Error());
			}
			
			$aCDRUsage	= array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$aCDRUsage[$aRow['Service']]	= $aRow['Usage'];
			}
			
			$oStdInvoice->cdr_usage	= $aCDRUsage;
			
			Log::getLog()->log("END: Generating summary for invoice '{$oInvoice->Id}'");
			
			return $oStdInvoice;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to generate invoice summary for Invoice '{$oInvoice->Id}'. ".$oException->getMessage());
		}
	}
}

class JSON_Handler_Invoice_Exception 	extends Exception{}

?>