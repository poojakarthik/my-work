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
$strInvoiceRun		= '460c6dfc434a7';
$intRowOffset		= 1;
$intColOffset		= 1;
$intDividerWidth	= 3;
$intSpacerWidth		= 2;

// Statements
$selExtensions		= new StatementSelect(	"Service",
											"Id AS Service, FNN",
											"Indial100 = 1 AND Account = <Account>");
$selRecordGroups	= new StatementSelect(	"(CDR USE INDEX (Service_3) JOIN RecordType ON CDR.RecordType = RecordType.Id) JOIN RecordType RecordGroup ON RecordType.GroupId = RecordGroup.Id",
											"RecordGroup.DisplayType AS DisplayType, RecordGroup.Description AS Description, RecordGroup.Id AS RecordGroup",
											"CDR.FNN LIKE <FNN> AND CDR.Service = <Service> AND CDR.Status = 199 AND CDR.InvoiceRun = '$strInvoiceRun' AND RecordType.Itemised = 1",
											"RecordGroup.Description DESC",
											NULL,
											"RecordGroup.Id");
$selCDR				= new StatementSelect(	"CDR USE INDEX (FNN_2) JOIN RecordType ON CDR.RecordType = RecordType.Id",
											"CDR.*",
											"CDR.InvoiceRun = '$strInvoiceRun' AND CDR.Service = <Service> AND CDR.FNN LIKE <FNN> AND RecordType.GroupId = <RecordGroup>",
											"CDR.StartDatetime");
											
echo "\n\n[ EXTENSION-LEVEL BILLING XLS GENERATOR ]\n\n";
Debug("_");
ob_flush();
/*
// Get all Accounts with Services with Extension Level Billing
$selAccounts	= new StatementSelect("Service JOIN ServiceExtension ON Service.Id = ServiceExtension.Service", "DISTINCT Account");
$intCount		= $selAccounts->Execute();
if ($intCount === FALSE)
{
	Debug($selAccounts->Error());
	die;
}
$arrAccounts = $selAccounts->FetchAll();*/
$arrAccounts = Array();
$arrAccounts[]	= Array('Account' => 1000156611);
$arrAccounts[]	= Array('Account' => 1000160843);
$arrAccounts[]	= Array('Account' => 1000157789);

// For each Account
$intPassed		= 0;
foreach ($arrAccounts as $arrAccount)
{
	echo " + Generating XLS for {$arrAccount['Account']}...\n";
	ob_flush();
	
	$strFileName = "/home/richdavis/Desktop/".$arrAccount['Account'].".xls";
	if (file_exists($strFileName))
	{
		unlink($strFileName);
	}
	
	// Create a new Workbook
	$wkbWorkbook = new Spreadsheet_Excel_Writer($strFileName);
	$wksSummary =& $wkbWorkbook->addWorksheet("Service Summaries");
	$arrSummaryData = Array();
	$arrItemisedWorksheets = Array();
	
	// Create Formatting Styles
	$arrFormats = AssignFormats($wkbWorkbook);
	
	// Get all Service Extensions
	$selExtensions->Execute(Array('Account' => $arrAccount['Account']));
	$arrServiceExtensions = $selExtensions->FetchAll();
	
	// For each Service Extension
	foreach ($arrServiceExtensions as $arrServiceExtension)
	{
		echo "\t + Generating Data for {$arrServiceExtension['FNN']}...";
		ob_flush();
		
		$strPrefix		= substr($arrServiceExtension['FNN'], 0, -2);
		$strRangeName	= $strPrefix . "00-99";
		
		// Create new Service Extension Worksheet
		$arrItemisedWorksheets[$strRangeName] =& $wkbWorkbook->addWorksheet($strRangeName);
		$arrItemisedWorksheets[$strRangeName]->hideGridlines();
		
		// For each possible FNN
		$intRow = $intRowOffset;
		for ($intExtension = 0; $intExtension < 100; $intExtension++)
		{
			//echo ".";
			ob_flush();
			
			$strExtensionFNN = $strPrefix.str_pad($intExtension, 2, '0', STR_PAD_LEFT);
			
			// Get all RecordGroups
			$arrData = Array();
			$arrData['Service']	= $arrServiceExtension['Service'];
			$arrData['FNN']		= $strExtensionFNN;
			$intGroups = $selRecordGroups->Execute($arrData);
			$arrRecordGroups = $selRecordGroups->FetchAll();
			
			if ($intGroups)
			{
				// Add to Service Summary Worksheet
				$wksSummary->writeString($intSummaryRow, $intColOffset, "Service Summary for $strExtensionFNN", $arrFormats['Title']);
				$intSummaryRow++;
								
				// Header
				$arrItemisedWorksheets[$strRangeName]->writeString($intRow, 0, "Indial :", $arrFormats['CostCentre']);
				$arrItemisedWorksheets[$strRangeName]->writeString($intRow, 1, $strExtensionFNN, $arrFormats['CostCentre']);
				$intRow += $intDividerWidth;
				
				// For each RecordGroup
				foreach ($arrRecordGroups as $arrRecordGroup)
				{
					//echo ",";
					ob_flush();
					
					// Header
					$strTitle = $arrRecordGroup['Description'] . " for " . $strExtensionFNN;
					$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset, $strTitle, $arrFormats['TitleRow']);
					$intRow += $intSpacerWidth;
					
					if ($arrRecordGroup['DisplayType'] == RECORD_DISPLAY_S_AND_E)
					{
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset	, "Description"	, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+5	, "Charge"		, $arrFormats['Bold']);
					}
					else
					{
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset	, "Date"			, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+1	, "Time"			, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+2	, "Calling Party"	, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+3	, "Description"		, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+4	, "Duration"		, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+5	, "Charge"			, $arrFormats['Bold']);
					}
					$intRow++;
					
					// Get all CDRs for this RecordGroup
					$arrData = Array();
					$arrData['FNN']	= $strExtensionFNN;
					$arrData['Service']		= $arrServiceExtension['Service'];
					$arrData['RecordGroup']	= $arrRecordGroup['RecordGroup'];
					$selCDR->Execute($arrData);
					$arrCDRs = $selCDR->FetchAll();
					
					// For each CDR
					$fltTotal = 0;
					foreach ($arrCDRs as $arrCDR)
					{
						// Itemise
						switch($arrRecordGroup['DisplayType'])
						{
							// Type 92
							case RECORD_DISPLAY_S_AND_E:
								$strDescription = $arrCDR['Description'];
								$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset, $strDescription);
								//$arrItemisedWorksheets[$strRangeName]->writeNumber($intRow, $intColOffset+1, (int)$arrCDR['Units']);
								$arrItemisedWorksheets[$strRangeName]->writeNumber($intRow, $intColOffset+5, (float)$arrCDR['Charge'], $arrFormats['Currency']);
								break;
							// Type 91
							case RECORD_DISPLAY_CALL:
							// Unknown Record Type (should never happen) - just display as a normal Call
							default:
								$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset, date("d/m/Y", strtotime($arrCDR['StartDatetime'])));
								$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+1, date("H:i:s", strtotime($arrCDR['StartDatetime'])));
								$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+2, $arrCDR['Destination']);
								$intHours		= floor((int)$arrCDR['Units'] / 3600);
								$strDuration	= "$intHours:".date("i:s", (int)$arrCDR['Units']);
								$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+3, $arrCDR['Description']);
								$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+4, $strDuration);
								$arrItemisedWorksheets[$strRangeName]->writeNumber($intRow, $intColOffset+5, $arrCDR['Charge'], $arrFormats['Currency']);
								break;
						}
						$fltTotal += (float)$arrCDR['Charge'];
						$intRow++;
					}
					
					// Add RecordGroupTotal
					$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset	, "Total Charges"	, $arrFormats['Bold']);
					$arrItemisedWorksheets[$strRangeName]->writeNumber($intRow, $intColOffset+5	, $fltTotal			, $arrFormats['Total']);
					
					$intRow += $intSpacerWidth;
					
					// Add Totals to Service Summary Worksheet
					// TODO
				}
			
				// Add Service Total to Service Summary
				// TODO
				$intSummaryRow += $intDividerWidth;
			}
		}
		
		echo "\t[ DONE ]\n";
	}	
	
	// Send the XLS file
	$wkbWorkbook->close();
	chmod($strFileName, 0777);
	$intPassed++;
}

$intCount = count($arrAccounts);
echo "Generated $intPassed of $intCount XLS documents\n\n";

exit;



// returns an array for formats for use in xls generation
function AssignFormats ($wkbWorkbook)
{
	$arrFormats = Array();
	
	//========================================================================//
	// Service Summary
	//========================================================================//
	
	// Cost Centre
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setBold();
	$fmtFormat->setSize(18);
	$arrFormats['CostCentre']	= $fmtFormat;
	
	// eg. Service Summary for 0409004224
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setBold();
	$fmtFormat->setSize(14);
	$arrFormats['TitleRow']	= $fmtFormat;
	
	// eg. Service Name				Calls		Charge
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setBold();
	$arrFormats['Bold']	= $fmtFormat;
		
	// Currency format
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setNumFormat('$#,##0.00;$#,##0.00 CR');
	$arrFormats['Currency']	= $fmtFormat;
		
	// Total
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setNumFormat('$#,##0.00;$#,##0.00 CR');
	$fmtFormat->setBold();
	$arrFormats['Total']	= $fmtFormat;
	
	return $arrFormats;
}

?>