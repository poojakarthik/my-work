<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

// load PEAR components
require_once("Spreadsheet/Excel/Writer.php");


// Definitions
$strInvoiceRun = '';

echo "\n\n[ EXTENSION-LEVEL BILLING XLS GENERATOR ]\n\n";

// Get all Accounts with Services with Extension Level Billing
$selAccounts	= new StatementSelect("Service JOIN ServiceExtension ON Service.Id = ServiceExtension.Service", "DISTINCT Account");
$intCount		= $selAccounts->Execute();
if ($intCount === FALSE)
{
	Debug($selAccounts->Error());
	die;
}
$arrAccounts = $selAccounts->FetchAll();

// For each Account
$intPassed		= 0;
foreach ($arrAccounts as $arrAccount)
{
	// Get all Service Extensions
	// TODO
	
	// For each Service Extension
	foreach ($arrServiceExtensions as $arrServiceExtension)
	{
		// Add to Service Summary Worksheet
		// TODO
		
		// Create new Service Extension Worksheet
		// TODO
		
		// Get all RecordGroups
		// TODO
		
		// For each RecordGroup
		foreach ($arrRecordGroups as $arrRecordGroup)
		{
			// Get all CDRs for this RecordGroup
			// TODO
			
			// For each CDR
			foreach ($arrCDRs as $arrCDR)
			{
				// Itemise
				// TODO
			}
		} 
	}
}

echo "Generated $intPassed of $intCount XLS documents\n\n";

?>