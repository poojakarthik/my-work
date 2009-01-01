<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Email list of Accounts with Samples ready
//----------------------------------------------------------------------------//

// load application
require_once("../../lib/classes/Flex.php");
Flex::load();

Debug("[ GENERATING SAMPLES LIST ]");

// Look for any Sample Invoice Runs
$selInvoiceRuns	= new StatementSelect(	"InvoiceRun JOIN invoice_run_schedule ON InvoiceRun.invoice_run_schedule_id = invoice_run_schedule.id", 
										"InvoiceRun.*, invoice_run_schedule.description", 
										"invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY);
if (!$selInvoiceRuns->Execute())
{
	if ($selInvoiceRuns->Error())
	{
		throw new Exception($selInvoiceRuns->Error());
	}
	else
	{
		throw new Exception("No Temporary Invoice Runs!");
	}
}

$selSampleAccounts	= new StatementSelect("Account JOIN Invoice ON Account.Id = Invoice.Account", "Account.Id, Account.BusinessName", "Invoice.invoice_run_id = <Id> AND Account.Sample != 0");
while ($arrInvoiceRun = $selInvoiceRuns->Fetch())
{	
	// Get list of Accounts
	$arrAccounts		= Array();
	$selSampleAccounts->Execute($arrInvoiceRun);
	while ($arrAccount = $selSampleAccounts->Fetch())
	{
		$arrAccounts[]	= "<a href='https://telcoblue.yellowbilling.com.au/admin/flex.php/Account/Overview/?Account.Id={$arrAccount['Id']}'>{$arrAccount['Id']}: {$arrAccount['BusinessName']}</a>";
	}
	
	$strCustomerGroup	= GetConstantDescription($arrInvoiceRun['customer_group_id'], 'CustomerGroup');
	
	$strTo		= "turdminator@hotmail.com, rdavis@yellowbilling.com.au";//, msergeant@yellowbilling.com.au";
	$strContent	= ($arrInvoiceRun['invoice_run_type_id'] === INVOICE_RUN_TYPE_INTERNAL_SAMPLES) ? "NOTE: THIS IS AN INTERNAL SAMPLE RUN -- DO NOT FORWARD TO CUSTOMERS <br/>\n<br/>\n" : "";
	$strContent	.= implode("<br/>\n", $arrAccounts);
	SendEmail($strTo, date("F", strtotime("-2 days", strtotime($arrInvoiceRun['BillingDate'])))." {$strCustomerGroup} {$arrInvoiceRun['description']} Samples", date("F", strtotime("-2 days", strtotime($arrInvoiceRun['BillingDate'])))." {$strCustomerGroup} {$arrInvoiceRun['description']} Samples<br>\n<br>\n".$strContent);
}

?>
