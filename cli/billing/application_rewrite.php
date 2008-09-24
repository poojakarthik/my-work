<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing
//----------------------------------------------------------------------------//
/**
 * Billing
 *
 * Billing Application
 *
 * Billing Application
 *
 * @prefix		app
 *
 * @package		cli.billing
 * @class		Billing
 */
class Billing
{
	//------------------------------------------------------------------------//
	// generateRun
	//------------------------------------------------------------------------//
	/**
	 * generateRun()
	 *
	 * Generates an Invoice Run
	 *
	 * Generates an Invoice Run
	 * 
	 * @param	integer	$intInvoiceRunType						The invoice_run_type (eg. INVOICE_RUN_TYPE_SAMPLES)
	 * @param	integer	$intInvoiceDatetime						The effective Datetime for this Invoice Run, invoiceable items must have been BEFORE this!
	 * @param	integer	$intScheduledInvoiceRun		[optional]	The invoice_run_schedule.id to Run
	 *
	 * @method
	 */
	public static function generateRun($intInvoiceRunType, $intInvoiceDatetime, $intScheduledInvoiceRun=NULL)
	{
		// Init variables
		$dbaDB					= DataAccess::getDataAccess();
		$strInvoiceDatetime		= date($intInvoiceDatetime);
		
		// If there is a Temporary InvoiceRun, then Revoke it
		Billing::revoke();
		
		//------------------- START INVOICE RUN GENERATION -------------------//
		// Create the initial InvoiceRun record
		$insInvoiceRun	= new StatementInsert("InvoiceRun");
		$arrInvoiceRun	= Array();
		$arrInvoiceRun['InvoiceRun']				= date("YmdHis", time());
		$arrInvoiceRun['BillingDate']				= date("Y-m-d", $intInvoiceDatetime);
		$arrInvoiceRun['invoice_run_type_id']		= $intInvoiceRunType;
		$arrInvoiceRun['invoice_run_schedule_id']	= $intScheduledInvoiceRun;
		$arrInvoiceRun['invoice_run_status']		= INVOICE_RUN_STATUS_TEMPORARY;
		$arrInvoiceRun['intInvoiceDatetime']		= $intInvoiceDatetime;					// Not stored in the DB, but used by other functions
		if ($insInvoiceRun->Execute($arrInvoiceRun) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$insInvoiceRun->Error());
		}
		
		// Retrieve the Bill Date of the last Invoice Run...
		$selInvoiceRun	= new StatementSelect("InvoiceRun", "BillingDate", "invoice_status_id = ".INVOICE_STATUS_COMMITTED, "BillingDate DESC", 1);
		if ($selInvoiceRun->Execute())
		{
			// We have an old InvoiceRun
			$arrLastInvoiceRun	= $selInvoiceRun->Fetch();
			$arrInvoiceRun['intLastInvoiceDatetime']	= strtotime($arrLastInvoiceRun['BillingDate']);
		}
		elseif ($selInvoiceRun->Error())
		{
			throw new Exception("DB ERROR: ".$selInvoiceRun->Error());
		}
		else
		{
			// No InvoiceRuns, so lets calculate when it should have been
			// For now, we will (and can probably always) assume that the Bill was supposed to be run exactly 1 month ago
			$arrInvoiceRun['intLastInvoiceDatetime']	= strtotime("-1 month", $intInvoiceDatetime);
		}
		
		// Retrieve a list of Accounts to be Invoiced
		$selInvoiceableAccounts	= new StatementSelect("Account JOIN account_status ON Account.Archived = account_status.id", "Account.*", "Account.CreatedOn < '{$strInvoiceDatetime}' AND account_status.can_invoice = 1");
		if ($selInvoiceableAccounts->Execute() === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableAccounts->Error());
		}
		
		// Generate an Invoice for each Account
		while ($arrAccount = $selInvoiceableAccounts->Fetch())
		{
			Billing::generateInvoice($arrAccount, $arrInvoiceRun);
		}
		//--------------------------------------------------------------------//
	}
	
	//------------------------------------------------------------------------//
	// generateInvoice
	//------------------------------------------------------------------------//
	/**
	 * generateInvoice()
	 *
	 * Generates an Invoice for a given Account and InvoiceRun
	 *
	 * Generates an Invoice for a given Account and InvoiceRun.  The frontend can use this
	 * to predict the amount that will be Invoiced by setting the $bolTotalsOnly parameter to TRUE
	 * 
	 * @param	array	$arrAccount					Details for the Account we're Invoicing
	 * @param	array	$arrInvoiceRun				Details for the Invoice Run we're generating
	 * @param	boolean	$bolTotalsOnly	[optional]	Don't insert any values -- just return the total Invoiced
	 *
	 * @return	integer								Insert Id for the Invoice
	 *
	 * @method
	 */
	public static function generateInvoice($arrAccount, $arrInvoiceRun, $bolTotalsOnly=FALSE)
	{
		static	$selInvoiceableFNNs;
		static	$selPlanDetails;
		static	$selCurrentService;
		static	$arrMultiServiceStatements	= Array();
		static	$selCDRTotals;
		static	$selEarliestCDR;
		static	$selHasInvoicedCDRs;
		static	$selLastPlanInvoiced;
		
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		// Is there already an Invoice for this Account?  If so, revoke it
		Billing::revokeInvoiceByAccount($arrAccount['Id']);
		
		//----------------- INVOICEABLE SERVICE PREPROCESSING ----------------//
		// Retrieve a list of Invoiceable FNNs for this Account
		$selInvoiceableFNNs	= ($selInvoiceableFNNs) ? $selInvoiceableFNNs : new StatementSelect("Service JOIN service_status ON Service.Status = service_status.id",
																								"Id, FNN, Indial100",
																								"Account = <Account> AND CreatedOn < <InvoiceDatetime> AND (ClosedOn > <InvoiceDatetime> OR service_status.can_invoice = 1)");
		if ($selInvoiceableFNNs->Execute($arrAccount) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableFNNs->Error());
		}
		
		// Process each Invoiceable FNN
		$arrServices	= Array();
		while ($arrFNN = $selInvoiceableFNNs->Fetch())
		{
			// Get the Service Details for the current owner of this FNN (or indial range), on this Account
			$selCurrentService	= ($selCurrentService) ? $selCurrentService : new StatementSelect(	"Service",
																									"*",
																									"");
			if ($selCurrentService->Execute($arrFNN) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selCurrentService->Error());
			}
			if (!($arrCurrentService = $selCurrentService->Fetch()))
			{
				// Error
				throw new Exception("WTF -- No Invoiceable Service Id for apparently Invoiceable FNN '{$arrFNN['FNN']}'!");
			}
			
			// Add this Service to our Invoicing Array
			if (!isset($arrServices[$arrCurrentService['Id']]))
			{
				$arrServices[$arrCurrentService['Id']]	= $arrCurrentService;
			}
			$arrServices[$arrCurrentService['Id']]['Ids'][]	= $arrFNN['Id'];
		}
		//--------------------------------------------------------------------//
		
		//----------------------- GENERATE SERVICE DATA ----------------------//
		// Generate Service Data
		$arrSharedPlans	= Array();
		foreach ($arrServices as $intServiceId=>&$arrServiceDetails)
		{
			$arrServiceDetails['ServiceTotal']	= self::generateInvoiceService($arrServiceDetails, $arrAccount, $arrInvoiceRun);
			
			// Is this a Shared Plan?
			if ($arrServiceDetails['ServiceTotal']['Shared'])
			{
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']][]	= &$arrServiceDetails;
			} 
		}
		
		// Calculate and Add in Shared Plan Charges and Credits as Account
		foreach ($arrSharedPlans as $intRatePlan=>$arrServices)
		{
			// Get Plan Details
			// TODO
			
			// Determine & Add in Plan Charge & Usage Limit Details
			// TODO: Make sure we also calculate the inc GST value Plan Charges and Usage levels
			
			// Calculate Shared Usage
			// TODO
			
			// Add in Plan Credit
			// TODO
		}
		//--------------------------------------------------------------------//
		
		//----------------------- GENERATE INVOICE DATA ----------------------//
		// Mark Account Adjustments
		// TODO
		
		// Calculate GST Total
		// TODO
		//--------------------------------------------------------------------//
	}
	
	//------------------------------------------------------------------------//
	// generateInvoiceService
	//------------------------------------------------------------------------//
	/**
	 * generateInvoiceService()
	 *
	 * Generates a Service on an Invoice for a given Account and InvoiceRun
	 *
	 * Generates a Service on an Invoice for a given Account and InvoiceRun
	 * 
	 * @param	array	$arrService					Details for the Service we're Invoicing
	 * @param	array	$arrAccount					Details for the Account we're Invoicing
	 * @param	array	$arrInvoiceRun				Details for the Invoice Run we're generating
	 *
	 * @return	array								ServiceTotal data for this Service
	 *
	 * @method
	 */
	private static function generateInvoiceService($arrServiceDetails, $arrAccount, $arrInvoiceRun)
	{
		static	$selPlanDetails;
		static	$selEarliestCDR;
		static	$selHasInvoicedCDRs;
		static	$selLastPlanInvoiced;
		
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		$arrServiceTotal	= Array();
		$intServiceId		= $arrServiceDetails['Id'];
		
		//--------------------------- PLAN CHARGES ---------------------------//
		// Retrieve Plan Details for the current Service
		$selPlanDetails	= ($selPlanDetails) ? $selPlanDetails : new StatementSelect(	"ServiceRatePlan JOIN RatePlan ON RatePlan.Id = ServiceRatePlan.RatePlan", 
																						"RatePlan.*, ServiceRatePlan.Id AS ServiceRatePlan",
																						"ServiceRatePlan.Service = <Service> AND <EffectiveDate> >= StartDatetime AND <EffectiveDate> < EndDatetime",
																						"CreatedOn DESC",
																						"1");
		if ($selPlanDetails->Execute(Array('Service' => $intServiceId, 'EffectiveDate' => $arrInvoiceRun['intInvoiceDatetime'])) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selPlanDetails->Error());
		}
		$arrPlanDetails	= $selPlanDetails->Fetch();
		
		// Determine & Add in Plan Charge & Usage Limit Details
		// TODO: Make sure we also calculate the inc GST value Plan Charges and Usage levels
		if ($arrPlanDetails['Shared'])
		{
			// This is a Shared Plan --  skip the Plan Charge and Usage Stage
			$arrServiceTotal['Shared']	= TRUE;
			$fltMinimumCharge	= 0.0;
			$fltUsageStart		= 0.0;
			$fltUsageLimit		= 0.0;
		}
		else
		{
			$selEarliestCDR	= ($selEarliestCDR) ? $selEarliestCDR : new StatementSelect("Service", "EarliestCDR AS MinEarliestCDR", "Id = <Service>");
			if ($selEarliestCDR->Execute(Array('Service' => $intServiceId)))
			{
				$arrEarliestCDR	= $selEarliestCDR->Fetch();
			}
			elseif ($selEarliestCDR->Error())
			{
				throw new Exception("DB ERROR: ".$selEarliestCDR->Error());
			}
			else
			{
				throw new Exception("Unable to get EarliestCDR details for {$arrAccount['Id']}{$arrServiceDetails['FNN']}!");
			}
			
			// Is the Service tolling?
			if ($arrEarliestCDR['MinEarliestCDR'])
			{
				$fltMinimumCharge	= (float)$arrPlanDetails['MinMonthly'];
				$fltUsageStart		= (float)$arrPlanDetails['ChargeCap'];
				$fltUsageLimit		= (float)$arrPlanDetails['UsageCap'];
				
				// Yes -- Does this Service have any Invoiced CDRs?
				$selHasInvoicedCDRs	= ($selHasInvoicedCDRs) ? $selHasInvoicedCDRs : new StatementSelect("ServiceTypeTotal", "SUM(Records)", "Service = <Service>");
				$mixResult			= $selHasInvoicedCDRs->Execute(Array('Service' => $intServiceId));
				if ($mixResult === FALSE)
				{
					throw new Exception("DB ERROR: ".$selHasInvoicedCDRs->Error());
				}
				elseif (!$mixResult)
				{
					// No -- Is this on a Charge-in-Advance Plan?
					if ($arrPlanDetails['InAdvance'])
					{
						$selLastPlanInvoiced	= ($selLastPlanInvoiced) ? $selLastPlanInvoiced : new StatementSelect("ServiceTotal", "Id", "Service = <Service> AND RatePlan = <RatePlan>", "Id DESC", "1");
						if ($selLastPlanInvoiced->Execute(Array('Service' => $intServiceId)) !== FALSE)
						{
							$arrLastPlanInvoiced	= $selLastPlanInvoiced->Fetch();
							if ($arrLastPlanInvoiced === FALSE || $arrLastPlanInvoiced['RatePlan'] !== $arrPlanDetails['Id'])
							{
								// The this Plan has not been invoiced before, so generate a Charge in Advance
								$intPeriodStart	= $arrInvoiceRun['intInvoiceDatetime'];
								$intPeriodEnd	= strtotime("-1 day", strtotime("+1 month", $arrInvoiceRun['intInvoiceDatetime']));
								self::addPlanCharge('PCAD', $fltMinimumCharge, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
							}
						}
						else
						{
							throw new Exception("DB ERROR: ".$selLastPlanInvoiced->Error());
						}
					}
					
					// Prorate the Charges and Usage details in Arrears
					$fltMinimumCharge	= self::prorate($fltMinimumCharge	, strtotime($arrEarliestCDR['MinEarliestCDR']), $arrInvoiceRun['intLastInvoiceDatetime'], $arrInvoiceRun['intInvoiceDatetime']);
					$fltUsageStart		= self::prorate($fltUsageStart		, strtotime($arrEarliestCDR['MinEarliestCDR']), $arrInvoiceRun['intLastInvoiceDatetime'], $arrInvoiceRun['intInvoiceDatetime']);
					$fltUsageLimit		= self::prorate($fltUsageLimit		, strtotime($arrEarliestCDR['MinEarliestCDR']), $arrInvoiceRun['intLastInvoiceDatetime'], $arrInvoiceRun['intInvoiceDatetime']);
					
					$strChargeType	= 'PCAR';
					$intPeriodStart	= strtotime($arrEarliestCDR['MinEarliestCDR']);
					$intPeriodEnd	= strtotime("-1 day", $arrInvoiceRun['intInvoiceDatetime']);
					self::addPlanCharge('PCAR', $fltMinimumCharge, $arrPlanDetails['Name'], $arrInvoiceRun['intLastInvoiceDatetime'], strtotime("-1 day", $arrInvoiceRun['intLastInvoiceDatetime']), $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
				}
				else
				{
					// Charge the Standard Plan Charge
					if ($arrPlanDetails['InAdvance'])
					{
						$strChargeType	= 'PCAD';
						$intPeriodStart	= $arrInvoiceRun['intInvoiceDatetime'];
						$intPeriodEnd	= strtotime("-1 day", strtotime("+1 month", $arrInvoiceRun['intInvoiceDatetime']));
					}
					else
					{
						$strChargeType	= 'PCAR';
						$intPeriodStart	= $arrInvoiceRun['intLastInvoiceDatetime'];
						$intPeriodEnd	= strtotime("-1 day", $arrInvoiceRun['intInvoiceDatetime']);
					}
					self::addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
				}
			}
			else
			{
				// No -- ignore all Plan Charges, because we haven't tolled yet
				$fltMinimumCharge	= 0.0;
				$fltUsageStart		= 0.0;
				$fltUsageLimit		= 0.0;
			}
		}
		
		// Determine Tax-inclusive Charge and Cap details
		$fltMinimumChargeGST	= $fltMinimumCharge + self::addGlobalTax($fltMinimumCharge, $arrInvoiceRun['intInvoiceDatetime'], $arrTaxTotals);
		$fltUsageStartGST		= $fltUsageStart + self::addGlobalTax($fltUsageStart, $arrInvoiceRun['intInvoiceDatetime'], $arrTaxTotals);
		$fltUsageLimitGST		= $fltUsageLimit + self::addGlobalTax($fltUsageLimit, $arrInvoiceRun['intInvoiceDatetime'], $arrTaxTotals);
		//--------------------------------------------------------------------//
		
		//--------------------------- SERVICE TOTALS -------------------------//
		// Mark all CDRs for this Service as TEMPORARY_INVOICE
		$strSQL		= "UPDATE CDR SET Status = ".CDR_TEMP_INVOICE." WHERE Status = ".CDR_RATED." AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")";
		$resResult	= $qryQuery->Execute($strSQL);
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		
		// Generate ServiceTypeTotals
		// TODO
		
		// Get CDR Total Details
		static	$selCDRTotals;
		// TODO: Uncapped Inc GST
		// TODO: Uncapped Ex GST
		// TODO: Uncapped Inc GST Credit
		// TODO: Uncapped Ex GST Credit
		// TODO: Capped Inc GST
		// TODO: Capped Ex GST
		// TODO: Capped Inc GST Credit
		// TODO: Capped Ex GST Credit
		
		// Calculate Service Plan Usage
		// TODO: Ensure that we have a GST and GST-free Plan Credit
		// TODO: All CDR Credits must be applied pre-Cap (ie. $0 Usage - CreditTotal + DebitTotal)
		// TODO: Include all capped sub-Service Totals in the Primary Cap
		// TODO
		
		// Add in Plan Credit Adjustment (except for Shared Plans)
		if (!$arrPlanDetails['Shared'])
		{
			// Add GST Credit
			if ($fltPlanCreditTaxAffected)
			{
				self::addPlanCharge('PCRG', $fltPlanCreditGST, $arrPlanDetails['Name'], $arrInvoiceRun['intLastInvoiceDatetime'], strtotime("-1 day", $arrInvoiceRun['intLastInvoiceDatetime']), $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
			}
			
			// Add GST Exempt Credit
			if ($fltPlanCreditTaxExempt)
			{
				self::addPlanCharge('PCR', $fltPlanCreditGSTExempt, $arrPlanDetails['Name'], $arrInvoiceRun['intLastInvoiceDatetime'], strtotime("-1 day", $arrInvoiceRun['intLastInvoiceDatetime']), $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
			}
		}
		
		// Mark all Service Charges as TEMPORARY_INVOICE
		// TODO
		
		// Add in Service Billing-time Charges
		// TODO
		
		// Retrieve Charge Totals (excluding Plan Charges)
		// TODO
		
		// Finalise Service Total
		// TODO
		//--------------------------------------------------------------------//
	}
	
	//------------------------------------------------------------------------//
	// revokeRun
	//------------------------------------------------------------------------//
	/**
	 * revokeRun()
	 *
	 * Revokes an Invoice Run
	 *
	 * If there is a Temporary Invoice Run, this will Revoke it
	 *
	 * @return										ApplicationCollection
	 *
	 * @method
	 */
	public static function revokeRun()
	{
		
	}
	
	//------------------------------------------------------------------------//
	// revokeInvoice
	//------------------------------------------------------------------------//
	/**
	 * revokeInvoice()
	 *
	 * Generates an Invoice Run
	 *
	 * Generates an Invoice Run
	 * 
	 * @param	integer	$intInvoiceId				The Id of the Invoice to Revoke
	 *
	 * @return										ApplicationCollection
	 *
	 * @method
	 */
	private static function revokeInvoice($intInvoiceId)
	{
		
	}
	
	//------------------------------------------------------------------------//
	// commitRun
	//------------------------------------------------------------------------//
	/**
	 * commitRun()
	 *
	 * Commits an Invoice Run
	 *
	 * If there is a Temporary Invoice Run, this will Commit it
	 *
	 * @return										ApplicationCollection
	 *
	 * @method
	 */
	public static function commitRun()
	{
		
	}
	
	//------------------------------------------------------------------------//
	// commitInvoice
	//------------------------------------------------------------------------//
	/**
	 * commitInvoice()
	 *
	 * Commits
	 *
	 * If there is a Temporary Invoice Run, this will Revoke it
	 *
	 * @return										ApplicationCollection
	 *
	 * @method
	 */
	private static function commitInvoice($intInvoiceId)
	{
		
	}
}

?>