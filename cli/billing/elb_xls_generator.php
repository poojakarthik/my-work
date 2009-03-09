<?php

// load framework
LoadFramework();

// Definitions
//$strInvoiceRun		= '460c6dfc434a7';	// March
$strInvoiceRun		= '45f4cb0c0a135';	// February
$intRowOffset		= 1;
$intColOffset		= 1;
$intDividerWidth	= 3;
$intSpacerWidth		= 2;

// Statements
$selExtensions		= new StatementSelect(	"Service",
											"Id AS Service, FNN",
											"Indial100 = 1 AND Account = <Account>");
$selRecordGroups	= new StatementSelect(	"(CDR JOIN RecordType ON CDR.RecordType = RecordType.Id) JOIN RecordType RecordGroup ON RecordType.GroupId = RecordGroup.Id",
											"RecordGroup.DisplayType AS DisplayType, RecordGroup.Description AS Description, RecordGroup.Id AS RecordGroup, COUNT(CDR.Id) AS CallCount, RecordGroup.Itemised AS Itemised",
											"CDR.FNN LIKE <FNN> AND CDR.Service = <Service> AND CDR.Status = 199 AND CDR.InvoiceRun = '$strInvoiceRun'",
											"RecordGroup.Description DESC",
											NULL,
											"RecordGroup.Id");
$selCDR				= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id",
											"CDR.*",
											"CDR.InvoiceRun = '$strInvoiceRun' AND CDR.Service = <Service> AND CDR.FNN LIKE <FNN> AND RecordType.GroupId = <RecordGroup>",
											"CDR.StartDatetime");
$selIndialTotal		= new StatementSelect(	"ServiceTotal",
											"TotalCharge",
											"Service = <Service> AND InvoiceRun = '$strInvoiceRun'");
$selCharges			= new StatementSelect(	"Charge",
											"SUM(CASE WHEN Nature = 'DR' THEN Amount WHEN Nature = 'CR' THEN (Amount * -1) END) AS Total",
											"Service = <Service> AND InvoiceRun = '$strInvoiceRun'",
											NULL,
											NULL,
											"Service");
											
Debug("\n\n[ EXTENSION-LEVEL BILLING XLS GENERATOR ]\n\n");
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
	
	$strFileName = "/home/richdavis/Desktop/".$arrAccount['Account']."_February.xls";
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
	$intSummaryRow = $intRowOffset;
	foreach ($arrServiceExtensions as $arrServiceExtension)
	{
		echo "\t + Generating Data for {$arrServiceExtension['FNN']}...";
		ob_flush();
		
		$strPrefix		= substr($arrServiceExtension['FNN'], 0, -2);
		$strRangeName	= $strPrefix . "00-99";
		
		// Create new Service Extension Worksheet
		$arrItemisedWorksheets[$strRangeName] =& $wkbWorkbook->addWorksheet($strRangeName);
		$arrItemisedWorksheets[$strRangeName]->hideGridlines();
			
		// Add to Service Summary
		$wksSummary->writeString($intSummaryRow, 0, "Indial Summary for", $arrFormats['CostCentre']);
		$wksSummary->writeString($intSummaryRow, 1, $strRangeName, $arrFormats['CostCentre']);
		$intSummaryRow += $intSpacerWidth;
		
		// For each possible FNN
		$intRow = $intRowOffset;
		$fltIndialTotal = 0;
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
				$wksSummary->writeString($intSummaryRow, $intColOffset, "Extension Summary for $strExtensionFNN", $arrFormats['TitleRow']);
				$intSummaryRow++;
				$wksSummary->writeString($intSummaryRow, $intColOffset, "Service Name", $arrFormats['Bold']);
				$wksSummary->writeString($intSummaryRow, $intColOffset+2, "Calls", $arrFormats['Bold']);
				$wksSummary->writeString($intSummaryRow, $intColOffset+3, "Charge", $arrFormats['Bold']);
				$intSummaryRow++;
				
				// Header
				$arrItemisedWorksheets[$strRangeName]->writeString($intRow, 0, "Extension :", $arrFormats['CostCentre']);
				$arrItemisedWorksheets[$strRangeName]->writeString($intRow, 1, $strExtensionFNN, $arrFormats['CostCentre']);
				$intRow += $intDividerWidth;
				
				// For each RecordGroup
				$fltServiceTotal = 0;
				foreach ($arrRecordGroups as $arrRecordGroup)
				{
					//echo ",";
					ob_flush();
					
					if ($arrRecordGroup['Itemised'])
					{
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
							$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+2	, "Called Party"	, $arrFormats['Bold']);
							$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+3	, "Description"		, $arrFormats['Bold']);
							$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+4	, "Duration"		, $arrFormats['Bold']);
							$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset+5	, "Charge"			, $arrFormats['Bold']);
						}
						$intRow++;
					}
					
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
						if ($arrRecordGroup['Itemised'])
						{
							// Itemise
							switch($arrRecordGroup['DisplayType'])
							{
								// Type 92
								case RECORD_DISPLAY_S_AND_E:
									$strDescription = $arrCDR['Description'];
									$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset, $strDescription);
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
									$arrItemisedWorksheets[$strRangeName]->writeNumber($intRow, $intColOffset+5, (float)$arrCDR['Charge'], $arrFormats['Currency']);
									break;
							}
							$intRow++;
						}
						$fltTotal += (float)$arrCDR['Charge'];
					}
					
					if ($arrRecordGroup['Itemised'])
					{
						// Add RecordGroupTotal
						$arrItemisedWorksheets[$strRangeName]->writeString($intRow, $intColOffset	, "Total Charges"	, $arrFormats['Bold']);
						$arrItemisedWorksheets[$strRangeName]->writeNumber($intRow, $intColOffset+5	, $fltTotal			, $arrFormats['Total']);
						
						$intRow += $intSpacerWidth;
					}
					
					// Add Totals to Service Summary Worksheet
					$wksSummary->writeString($intSummaryRow, $intColOffset, $arrRecordGroup['Description']);
					$wksSummary->writeNumber($intSummaryRow, $intColOffset+2, $arrRecordGroup['CallCount']);
					$wksSummary->writeNumber($intSummaryRow, $intColOffset+3, $fltTotal, $arrFormats['Currency']);
					$intSummaryRow++;
					
					$fltServiceTotal += $fltTotal;
				}
				
				// Add Service Total to Service Summary
				$wksSummary->writeString($intSummaryRow, $intColOffset, "Total Charges", $arrFormats['TitleRow']);
				$wksSummary->writeNumber($intSummaryRow, $intColOffset+3, $fltServiceTotal, $arrFormats['TitleTotal']);
				$intSummaryRow += $intSpacerWidth;
				
				$fltIndialTotal += $fltServiceTotal;
			}
		}
		
		// Charge Summary
		$selCharges->Execute(Array('Service' => $arrServiceExtension['Service']));
		if ($arrCharges = $selCharges->Fetch())
		{
			$wksSummary->writeString($intSummaryRow, $intColOffset, "Adjustments for $strRangeName", $arrFormats['TitleRow']);
			$wksSummary->writeString($intSummaryRow, $intColOffset+3, $arrCharges['Total'], $arrFormats['Total']);
			$intSummaryRow += $intSpacerWidth;
		}
		
		$selIndialTotal->Execute(Array('Service' => $arrServiceExtension['Service']));
		$arrIndialTotal = $selIndialTotal->Fetch();
		
		// Add to Service Summary
		$wksSummary->writeString($intSummaryRow, 0, "Total Charges for", $arrFormats['CostCentre']);
		$wksSummary->writeString($intSummaryRow, 1, $strRangeName, $arrFormats['CostCentre']);
		$wksSummary->writeNumber($intSummaryRow, 4, $fltIndialTotal, $arrFormats['CostCentreTotal']);
		$intSummaryRow++;
		$wksSummary->writeString($intSummaryRow, 0, "Total Invoiced for", $arrFormats['CostCentre']);
		$wksSummary->writeString($intSummaryRow, 1, $strRangeName, $arrFormats['CostCentre']);
		$wksSummary->writeNumber($intSummaryRow, 4, $arrIndialTotal['TotalCharge'], $arrFormats['CostCentreTotal']);
		$intSummaryRow += $intDividerWidth;
		
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
	$fmtFormat->setSize(14);
	$arrFormats['CostCentre']	= $fmtFormat;
	
	// Cost Centre Total
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setBold();
	$fmtFormat->setSize(14);
	$fmtFormat->setNumFormat('$#,##0.00;$#,##0.00 CR');
	$arrFormats['CostCentreTotal']	= $fmtFormat;
	
	// eg. Service Summary for 0409004224
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setBold();
	$fmtFormat->setSize(12);
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
	
	// Title Total
	$fmtFormat = $wkbWorkbook->addFormat();
	$fmtFormat->setNumFormat('$#,##0.00;$#,##0.00 CR');
	$fmtFormat->setSize(12);
	$fmtFormat->setBold();
	$arrFormats['TitleTotal']	= $fmtFormat;
	
	return $arrFormats;
}

?>
