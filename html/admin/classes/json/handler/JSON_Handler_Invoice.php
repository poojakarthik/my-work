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
					throw new Exception_Database("Failed to find ServiceRatePlan record, query failed. ".$oQuery->Error());
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
				throw new Exception_Database("Failed to get reratable invoices for account {$iAccountId}. ".$oQuery->Error());
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
	
	public function createRerateTicket($iInvoiceId, $iRerateInvoiceRunId, $iAdjustmentId=null, $sAdditionalComments=null)
	{
		try
		{
			// Employee who will create the ticket
			$oEmployee	= Employee::getForId(Flex::getUserId());
			
			// Original Invoice
			$oInvoice	= Invoice::getForId($iInvoiceId);
			
			// Account invoice belongs to
			$oAccount	= Account::getForId($oInvoice->Account);
			
			// Ticketing_Customer_Group_Email for the accounts customer group
			$oCustomerGroupConfig	= Ticketing_Customer_Group_Config::getForId($oAccount->CustomerGroup);
			$oCustomerGroupEmail 	= $oCustomerGroupConfig->getDefaultCustomerGroupEmail();
			
			// Get PDF content
			$iBillingDate	= strtotime($oInvoice->CreatedOn);
			$iYear 			= (int)date('Y', $iBillingDate);
			$iMonth 		= (int)date('m', $iBillingDate);
			$sInvoice 		= GetPDFContent($oAccount->Id, $iYear, $iMonth, $iInvoiceId, $iRerateInvoiceRunId);
			if (!$sInvoice)
			{
				throw new Exception("Rerated Invoice PDF not found.");
			}
			
			// Get PDF Filename
			$sInvoiceFilename	= GetPdfFilename($oAccount->Id, $iYear, $iMonth, $iInvoiceId, $iRerateInvoiceRunId);
			
			// Build the message
			$sMessage	= "Invoice {$iInvoiceId} has been rerated, the PDF is attached.\n";
			if ($iAdjustmentId !== null)
			{
				$oCharge	= Charge::getForId($iAdjustmentId);
				$sMessage	.= "\nAn adjustment has been requested.\n";
				$sMessage	.= "Adjustment Type: {$oCharge->ChargeType}.\n";
				$sMessage	.= "Amount: $".round($oCharge->Amount, 2)."\n";
			}
			
			if ($sAdditionalComments !== null)
			{
				$sMessage	.= "\nAdditional Comments:\n{$sAdditionalComments}";
			}
			
			// Details used to create the ticketing correspondence (& the ticket)
			$aDetails	=	array(
								'default_email_id'	=> $oCustomerGroupEmail->id,
								'from'				=>	array(
															'address'	=> $oEmployee->Email,
															'name'		=> $oEmployee->getName(),
														),
								'delivery_status'	=> 	TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED,
								'source_id'			=> 	TICKETING_CORRESPONDANCE_SOURCE_WEB,
								'creation_datetime'	=> 	DataAccess::getDataAccess()->getNow(),
								'subject'			=> 	"Rerated Invoice for ".date('jS M Y', strtotime($oInvoice->CreatedOn)),
								'message'			=> 	$sMessage,
								'attachments'		=>	array(
															array(
																'name'	=> $sInvoiceFilename,
																'type'	=> 'application/pdf',
																'data'	=> $sInvoice
															)
														)
							);
			
			// Create the ticketing correspondence
			$oCorrespondence	= Ticketing_Correspondance::createForDetails($aDetails);
			if ($oCorrespondence === null)
			{
				throw new Exception("Ticketing Correspondence not created, most likely invalid customer group.");
			}
			
			// Set the account id of the ticket
			$oTicket			= $oCorrespondence->getTicket();
			$oTicket->accountId	= $oAccount->Id;
			$oTicket->save();
			
			// Success!
			return	array(
						'bSuccess'	=> true,
						'iTicketId'	=> $oTicket->id
					);
		}
		catch (Exception $oException)
		{
			$bGod	= Employee::getForId(Flex::getUserId())->isGod();
			return	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bGod ? 'Failed to save ticket. '.$oException->getMessage() : 'An error occured accessing the database')
					);
		}
	}
	
	public function getDatasetForAccount($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Invoice::getDatasetForAccount(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Invoice::getDatasetForAccount(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			
			foreach ($aData as $aRecord)
			{
				$iDate 					= strtotime("-1 month", strtotime($aRecord['created_on']));
				$iYear 					= (int)date("Y", $iDate);
				$iMonth 				= (int)date("m", $iDate);
				$aRecord['pdf_exists'] 	= InvoicePDFExists($aRecord['account_id'], $iYear, $iMonth, $aRecord['id'], $aRecord['invoice_run_id']);
				$aResults[$i] 			= $aRecord;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getInvoiceListPermissions()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$bUserHasOperatorPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
			$bUserHasViewPerm			= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR_VIEW);
			$bUserHasExternalPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR_EXTERNAL);
			$bUserHasInterimPerm		= (AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT) || AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN));
			$bUserHasRedistributePerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
			return	array(
						'bSuccess' 		=> true,
						'oPermissions'	=> 	array(
												'bUserHasOperatorPerm'		=> $bUserHasOperatorPerm,
												'bUserHasViewPerm'			=> $bUserHasViewPerm,
												'bUserHasExternalPerm'	 	=> $bUserHasExternalPerm,
												'bUserHasInterimPerm'		=> $bUserHasInterimPerm,
												'bUserHasRedistributePerm'	=> $bUserHasRedistributePerm,
												'bUserIsGod'				=> $bUserIsGod
											)
					);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'),
						'sDebug'	=> ($bUserIsGod ? $this->_sJSONDebug : '')
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
				
				$bPlanCharge	= in_array($oCharge->ChargeType, array('PCAD', 'PCAR', 'PCR', 'PDCR'));
				$bCredit		= in_array($oCharge->ChargeType, array('PCR', 'PDCR'));
				if ($oCharge->Service !== null)
				{
					$aStdCharges[$oCharge->ChargeType]['service_totals'][$oCharge->Service]	+= $fAmount;
				}
				else if ($bPlanCharge)
				{
					if ($bCredit)
					{
						$fSharedPlanDiscounts	+= $fAmount;
					}
					else
					{
						$fSharedPlanCharges	+= $fAmount;
					}
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
			
			$oStdInvoice->payment_total		= Invoice_Export::getPaymentTotal($oInvoice->toArray());
			$oStdInvoice->opening_balance 	= Account::getForId($oInvoice->Account)->getHistoricalBalance(date('Y-m-d H:i:s', strtotime($oInvoice->billing_period_start_datetime) - 1));
			$oStdInvoice->closing_balance 	= Account::getForId($oInvoice->Account)->getHistoricalBalance(date('Y-m-d H:i:s', strtotime($oInvoice->billing_period_end_datetime)));
			
			// Adjustment total & tax
			$oTaxType 						= Tax_Type::getGlobalTaxType();
			$fAdjustments					= Rate::roundToCurrencyStandard($oInvoice->getAdjustmentTotal());
			$oStdInvoice->adjustment_total	= Rate::roundToCurrencyStandard($fAdjustments / (1 + ($oTaxType ? $oTaxType->rate_percentage : 0)));
			$oStdInvoice->adjustment_tax	= Rate::roundToCurrencyStandard($oStdInvoice->adjustment_total * ($oTaxType ? $oTaxType->rate_percentage : 1));
			
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
				throw new Exception_Database("Failed to retrieve CDR usage totals per service. ".$oQuery->Error());
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