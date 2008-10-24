<?php

// Framework
require_once("../../flex.require.php");

// Start a Transaction
DataAccess::getDataAccess()->TransactionStart();

try
{
	// Statements
	$ubiServiceTotal		= new StatementUpdateById("ServiceTotal", Array('service_rate_plan'=>NULL));
	
	$selFuckedServiceTotals	= new StatementSelect(	"ServiceTotal JOIN InvoiceRun ON ServiceTotal.invoice_run_id = InvoiceRun.Id",
													"ServiceTotal.Id, ServiceTotal.RatePlan, ServiceTotal.Service, InvoiceRun.BillingDate",
													"service_rate_plan IS NULL AND RatePlan IS NOT NULL");
	
	$selBestServiceRatePlan	= new StatementSelect(	"ServiceRatePlan",
													"Id",
													"RatePlan = <RatePlan> AND Service = <Service> AND CreatedOn < <BillingDate> AND StartDatetime < EndDatetime",
													"Id DESC",
													"1"); 
	
	CliEcho("\n:: Fixing Old ServiceTotal.service_rate_plan ::\n");
	
	// Get list of Old ServiceTotals
	$intCount	= 0;
	$mixResult	= $selFuckedServiceTotals->Execute();
	if ($mixResult === FALSE)
	{
		throw new Exception($selFuckedServiceTotals->Error());
	}
	else
	{
		while ($arrServiceTotal = $selFuckedServiceTotals->Fetch())
		{
			$intCount++;
			CliEcho(" + ({$intCount}/{$mixResult}) {$arrServiceTotal['Service']} @ {$arrServiceTotal['BillingDate']}...", FALSE);
			
			// Find the Best ServiceRatePlan match
			if ($selBestServiceRatePlan->Execute($arrServiceTotal) === FALSE)
			{
				throw new Exception($selBestServiceRatePlan->Error());
			}
			elseif ($arrBestServiceRatePlan = $selBestServiceRatePlan->Fetch())
			{
				// Found a Plan
				$arrServiceTotal['service_rate_plan']	= $arrBestServiceRatePlan['Id'];
				if ($ubiServiceTotal->Execute($arrServiceTotal) === FALSE)
				{
					throw new Exception($ubiServiceTotal->Error());
				}
				else
				{
					CliEcho();
				}
			}
			else
			{
				// No Plan Found
				CliEcho("NO PLAN FOUND (RatePlan.Id: {$arrServiceTotal['RatePlan']})");
			}
		}
	}
	
	throw new Exception("TEST MODE!");
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}
?>