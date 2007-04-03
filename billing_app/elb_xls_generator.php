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

// Statements
$selExtensions = new StatementSelect(	"ServiceExtension SE JOIN Service ON SE.Service = Service.Id",
										"Service.Id AS Service, SE.Name AS Name, CONCAT(SUBSTR(Service.FNN, 0, -2), SE.RangeStart) AS FNNMin, CONCAT(SUBSTR(Service.FNN, 0, -2), SE.RangeEnd) AS FNNMax");


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
	// Create a new Workbook
	$wkbWorkbook = new Spreadsheet_Excel_Writer();
	$wkbWorkbook->send($strXLSPath.$arrAccount['Account'].".xls");
	$wksSummary =& $wkbWorkbook->addWorksheet();
	$arrSummaryData = Array();
	$arrItemisedWorksheets = Array();
	
	// Get all Service Extensions
	$selExtensions->Execute(Array('Account' => $arrAccount['Account']));
	$arrServiceExtensions = $selExtensions->FetchAll();
	
	// For each Service Extension
	foreach ($arrServiceExtensions as $arrServiceExtension)
	{
		// Add to Service Summary Worksheet
		$arrSummaryData[$arrServiceExtension['Name']] = $fltTotal;
		
		// Add Header Row
		// TODO
		
		// Create new Service Extension Worksheet
		$arrItemisedWorksheets[$arrServiceExtension['Name']] =& $wkbWorkbook->addWorksheet();
		
		// Get all RecordGroups
		$arrData = Array();
		$arrData['FNNMin']	= $arrServiceExtension['FNNMin'];
		$arrData['FNNMax']	= $arrServiceExtension['FNNMax'];
		$arrData['Service']	= $arrServiceExtension['Service'];
		$selRecordGroups->Execute($arrData);
		$arrRecordGroups = $selRecordGroups->FetchAll();
		
		// For each RecordGroup
		foreach ($arrRecordGroups as $arrRecordGroup)
		{
			// Get all CDRs for this RecordGroup
			$arrData = Array();
			$arrData['FNNMin']		= $arrServiceExtension['FNNMin'];
			$arrData['FNNMax']		= $arrServiceExtension['FNNMax'];
			$arrData['Service']		= $arrServiceExtension['Service'];
			$arrData['RecordGroup']	= $arrRecordGroup['RecordGroup'];
			$selCDR->Execute($arrData);
			$arrCDRs = $selCDR->FetchAll();
			
			// For each CDR
			foreach ($arrCDRs as $arrCDR)
			{
				// Itemise
				// TODO
			}
			
			// Add RecordGroupTotal
			// TODO
		}
	}
}

echo "Generated $intPassed of $intCount XLS documents\n\n";

?>