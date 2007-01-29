<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-7 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// etech_validator
//----------------------------------------------------------------------------//
/**
 * etech_validator
 *
 * Validates an Etech Billing File (*.txt)
 *
 * Validates an Etech Billing File (*.txt).  It will only validate a txt file
 * created today
 *
 * @file		etech_validator.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste' Davis
 * @version		7.01
 * @copyright	2006-7 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$strFrameworkDir	= "../../framework/";
$strPDFDir			= $strFrameworkDir."pdf/";

// load framework
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");
require_once($strPDFDir."class.ezpdf.php");
require_once($strPDFDir."class.pdf.php");
require_once("../config.php");

//$strPath		= "/home/vixen_bill_output/sample/";
$strPath		= "/home/vixen_bill_output/";
//$strPDFPath		= "/home/vixen_bill_output/sample/pdf/";
$strPDFPath		= "/home/vixen_bill_output/pdf/";
//$strFilename	= $strPath."sample".date("Y-m-d").".vbf";
$strFilename	= $strPath."inv_telcoblue_".date("Ymd")."_0123456789.txt";
//$strFilename	= $strPath."sample2007-01-12.vbf";
//$strFilename	= $strPath."2007-01-12.vbf";
//$strFilename	= "/home/richdavis/2007-01-12.vbf";


		//----------------------------------------------------------------------------//
		// Define the file format
		//----------------------------------------------------------------------------//
		
		$GLOBALS['Delimiter'] = "|";
		
		// Define RecordTypes
		$arrPrintDefineEtech['FileHeader']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['FileHeader']		['RecordType']		['Value']	= 1;
		$arrPrintDefineEtech['SPDetails']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SPDetails']		['RecordType']		['Value']	= 2;
		$arrPrintDefineEtech['GlobalMessage']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['GlobalMessage']	['RecordType']		['Value']	= 3;
		$arrPrintDefineEtech['Brands']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['Brands']			['RecordType']		['Value']	= 4;
		$arrPrintDefineEtech['SequenceNo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SequenceNo']		['RecordType']		['Value']	= 5;
		$arrPrintDefineEtech['InvoiceNo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['InvoiceNo']		['RecordType']		['Value']	= 6;
		$arrPrintDefineEtech['BrandId']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['BrandId']			['RecordType']		['Value']	= 7;
		$arrPrintDefineEtech['InvoiceCharge']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['InvoiceCharge']	['RecordType']		['Value']	= 10;
		$arrPrintDefineEtech['Customer']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['Customer']			['RecordType']		['Value']	= 11;
		$arrPrintDefineEtech['ChargeSummary']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ChargeSummary']	['RecordType']		['Value']	= 15;
		$arrPrintDefineEtech['DetailsHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['DetailsHeader']	['RecordType']		['Value']	= 19;
		$arrPrintDefineEtech['CategoryHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CategoryHeader']	['RecordType']		['Value']	= 20;
		$arrPrintDefineEtech['CategoryFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CategoryFooter']	['RecordType']		['Value']	= 21;
		$arrPrintDefineEtech['DetailsFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['DetailsFooter']	['RecordType']		['Value']	= 29;
		$arrPrintDefineEtech['SvcSummHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SvcSummHeader']	['RecordType']		['Value']	= 39;
		$arrPrintDefineEtech['ServiceHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ServiceHeader']	['RecordType']		['Value']	= 40;
		$arrPrintDefineEtech['ServiceDetail']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ServiceDetail']	['RecordType']		['Value']	= 41;
		$arrPrintDefineEtech['ServiceTotals']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ServiceTotals']	['RecordType']		['Value']	= 45;
		$arrPrintDefineEtech['ServiceFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ServiceFooter']	['RecordType']		['Value']	= 48;
		$arrPrintDefineEtech['SvcSummFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SvcSummFooter']	['RecordType']		['Value']	= 49;
		$arrPrintDefineEtech['SpecialMsg1']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SpecialMsg1']		['RecordType']		['Value']	= 50;
		$arrPrintDefineEtech['SpecialMsg2']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SpecialMsg2']		['RecordType']		['Value']	= 51;
		$arrPrintDefineEtech['SpecialMsg3']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SpecialMsg3']		['RecordType']		['Value']	= 52;
		$arrPrintDefineEtech['SpecialMsg4']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SpecialMsg4']		['RecordType']		['Value']	= 53;
		$arrPrintDefineEtech['SpecialMsg5']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['SpecialMsg5']		['RecordType']		['Value']	= 54;
		$arrPrintDefineEtech['CreditHeader']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CreditHeader']		['RecordType']		['Value']	= 60;
		$arrPrintDefineEtech['CreditBalance']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CreditBalance']	['RecordType']		['Value']	= 61;
		$arrPrintDefineEtech['CreditAdded']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CreditAdded']		['RecordType']		['Value']	= 62;
		$arrPrintDefineEtech['CreditUsed']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CreditUsed']		['RecordType']		['Value']	= 63;
		$arrPrintDefineEtech['CreditLeft']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CreditLeft']		['RecordType']		['Value']	= 64;
		$arrPrintDefineEtech['CreditFooter']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CreditFooter']		['RecordType']		['Value']	= 69;
		$arrPrintDefineEtech['GraphInfo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['GraphInfo']		['RecordType']		['Value']	= 70;
		$arrPrintDefineEtech['GraphData']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['GraphData']		['RecordType']		['Value']	= 71;
		$arrPrintDefineEtech['ExtraInfo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ExtraInfo']		['RecordType']		['Value']	= 80;
		$arrPrintDefineEtech['InvoiceFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['InvoiceFooter']	['RecordType']		['Value']	= 95;
		$arrPrintDefineEtech['FileFooter']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['FileFooter']		['RecordType']		['Value']	= 99;
		$arrPrintDefineEtech['ItemisedCall']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['ItemisedS&E']		['RecordType']		['Type']	= ETECH_ROW;
		
		// Call Details
		$arrPrintDefineEtech['CallLLNational']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallLLNational']	['RecordType']		['Value']	= 102;
		$arrPrintDefineEtech['CallLLTo1300']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallLLTo1300']		['RecordType']		['Value']	= 103;
		$arrPrintDefineEtech['CallLLToMob']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallLLToMob']		['RecordType']		['Value']	= 104;
		$arrPrintDefineEtech['CallLLToIDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallLLToIDD']		['RecordType']		['Value']	= 105;
		$arrPrintDefineEtech['CallMobToMob']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMobToMob']		['RecordType']		['Value']	= 106;
		$arrPrintDefineEtech['CallMobToNat']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMobToNat']		['RecordType']		['Value']	= 107;
		$arrPrintDefineEtech['CallMobOther']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMobOther']		['RecordType']		['Value']	= 110;
		$arrPrintDefineEtech['CallMobRoam']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMobRoam']		['RecordType']		['Value']	= 111;
		$arrPrintDefineEtech['CallMobToIDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMobToIDD']		['RecordType']		['Value']	= 112;
		$arrPrintDefineEtech['CallMobTo18']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMobTo18']		['RecordType']		['Value']	= 113;
		$arrPrintDefineEtech['Call13IDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['Call13IDD']		['RecordType']		['Value']	= 114;
		$arrPrintDefineEtech['CallLLOther']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallLLOther']		['RecordType']		['Value']	= 116;
		$arrPrintDefineEtech['Call18Inbound']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['Call18Inbound']	['RecordType']		['Value']	= 117;
		$arrPrintDefineEtech['Call13Inbound']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['Call13Inbound']	['RecordType']		['Value']	= 118;
		$arrPrintDefineEtech['CallSMS']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallSMS']			['RecordType']		['Value']	= 119;
		$arrPrintDefineEtech['CallMMS']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallMMS']			['RecordType']		['Value']	= 120;
		$arrPrintDefineEtech['CallUnknown']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallUnknown']		['RecordType']		['Value']	= 135;
		$arrPrintDefineEtech['CallS&E']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallS&E']			['RecordType']		['Value']	= 208;				// Record type is 108 in footer
		$arrPrintDefineEtech['CallOC&C']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefineEtech['CallOC&C']			['RecordType']		['Value']	= 237;				// Record type is 137 in footer
		
		// Row Type					Field Name			Property	Value
		//------------------------------------------------------------------------------
		
		// File Header
		$arrPrintDefineEtech['FileHeader']		['Date']			['Type']	= ETECH_DATE_YYYYMMDD;
		$arrPrintDefineEtech['FileHeader']		['Filename']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['FileHeader']		['SPName']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['FileHeader']		['BillingPeriod']	['Type']	= ETECH_DATE_MONTH_YY;
		
		// SP Details
		$arrPrintDefineEtech['SPDetails']		['BusinessName']	['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['SPDetails']		['Address1']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['SPDetails']		['Address2']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['SPDetails']		['Suburb']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['SPDetails']		['Postcode']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['SPDetails']		['State']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['SPDetails']		['Phone']			['Type']	= ETECH_FNN;
		$arrPrintDefineEtech['SPDetails']		['Fax']				['Type']	= ETECH_FNN;
		$arrPrintDefineEtech['SPDetails']		['URL']				['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['SPDetails']		['BPayCode']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['SPDetails']		['InvoiceDate']		['Type']	= ETECH_DATE_YYYY_MM_DD;
		$arrPrintDefineEtech['SPDetails']		['DueByDate']		['Type']	= ETECH_DATE_YYYY_MM_DD;
		
		// Global Message (unused)
		$arrPrintDefineEtech['GlobalMessage']	['Message']			['Type']	= ETECH_STRING;
		
		// Brands (Telco Blue, Voicetalk, etc)
		$arrPrintDefineEtech['Brands']			['Brand1']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Brands']			['Brand2']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Brands']			['Brand3']			['Type']	= ETECH_STRING;
		
		// Invoice Sequence Number
		$arrPrintDefineEtech['SequenceNo']		['SequenceNo']		['Type']	= ETECH_INTEGER;
		
		// Invoice Number
		$arrPrintDefineEtech['InvoiceNo']		['InvoiceNo']		['Type']	= ETECH_INTEGER;
		
		// Invoice Brand Id
		$arrPrintDefineEtech['BrandId']			['BrandId']			['Type']	= ETECH_INTEGER;
		
		// Invoice Charges
		$arrPrintDefineEtech['InvoiceCharge']	['PreviousBalance']	['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['InvoiceCharge']	['Received']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['InvoiceCharge']	['Adjustments']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['InvoiceCharge']	['Overdue']			['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['InvoiceCharge']	['NewCharges']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['InvoiceCharge']	['AmountOwing']		['Type']	= ETECH_SHORT_CURRENCY;
		
		// Customer Details
		$arrPrintDefineEtech['Customer']			['AccountNo']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['Customer']			['CheckDigit']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['Customer']			['Address1']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['Address2']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['Suburb']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['Postcode']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['Customer']			['State']			['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['BusinessName']	['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['Firstname']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['Lastname']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['Customer']			['InvoiceType']		['Type']	= ETECH_STRING;
		
		// Charge Summaries
		$arrPrintDefineEtech['ChargeSummary']	['Category']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['ChargeSummary']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Category Header
		$arrPrintDefineEtech['CategoryHeader']	['Category']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['CategoryHeader']	['FNN']				['Type']	= ETECH_FNN;
		
		// Itemised Call Data
		$arrPrintDefineEtech['ItemisedCall']		['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['ItemisedCall']		['Datetime']		['Type']	= ETECH_DATETIME;
		$arrPrintDefineEtech['ItemisedCall']		['CalledParty']		['Type']	= ETECH_FNN;
		$arrPrintDefineEtech['ItemisedCall']		['Description']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['ItemisedCall']		['Duration']		['Type']	= ETECH_DURATION;
		$arrPrintDefineEtech['ItemisedCall']		['Charge']			['Type']	= ETECH_LONG_CURRENCY;
		
		// Itemised S&E Data
		$arrPrintDefineEtech['ItemisedS&E']		['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['ItemisedS&E']		['Description']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['ItemisedS&E']		['Charge']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Charge Footer
		$arrPrintDefineEtech['CategoryFooter']	['CategoryId']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['CategoryFooter']	['RecordTotal']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['CategoryFooter']	['FNN']				['Type']	= ETECH_FNN;
		$arrPrintDefineEtech['CategoryFooter']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Service Header
		$arrPrintDefineEtech['ServiceHeader']	['FNN']				['Type']	= ETECH_FNN;
		//$arrPrintDefineEtech['ServiceHeader']	['CostCentre']		['Type']	= ETECH_STRING;
		
		// Service Detail
		$arrPrintDefineEtech['ServiceDetail']	['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['ServiceDetail']	['ChargeType']		['Type']	= ETECH_STRING;
		$arrPrintDefineEtech['ServiceDetail']	['Charge']			['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['ServiceDetail']	['CallCount']		['Type']	= ETECH_INTEGER;
		
		// Service Totals
		$arrPrintDefineEtech['ServiceTotals']	['RecordTotal']		['Type']	= ETECH_INTEGER;
		$arrPrintDefineEtech['ServiceTotals']	['FNN']				['Type']	= ETECH_FNN;
		$arrPrintDefineEtech['ServiceTotals']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Service Footer
		//$arrPrintDefineEtech['ServiceFooter']	['CostCentre']		['Type']	= ETECH_STRING;
		//$arrPrintDefineEtech['ServiceFooter']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Special Message 1
		$arrPrintDefineEtech['SpecialMsg1']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 2
		$arrPrintDefineEtech['SpecialMsg2']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 3
		$arrPrintDefineEtech['SpecialMsg3']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 4
		$arrPrintDefineEtech['SpecialMsg4']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 5
		$arrPrintDefineEtech['SpecialMsg5']		['Message']			['Type']	= ETECH_STRING;
		
		// Credit Balance
		$arrPrintDefineEtech['CreditBalance']	['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Added
		$arrPrintDefineEtech['CreditAdded']		['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Left
		$arrPrintDefineEtech['CreditLeft']		['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Remaining
		$arrPrintDefineEtech['CreditLeft']		['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Global Graph Info
		$arrPrintDefineEtech['GraphInfo']		['DateRange']		['Type']	= ETECH_DATE_RANGE;
		
		// Graph Data
		$arrPrintDefineEtech['GraphData']		['CurrentMonth']	['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['GraphData']		['LastMonth']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['GraphData']		['2MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['GraphData']		['3MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['GraphData']		['4MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefineEtech['GraphData']		['5MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		
		// File Footer
		$arrPrintDefineEtech['FileFooter']		['InvoiceCount']	['Type']	= ETECH_INTEGER;
		
		$GLOBALS['FileFormatEtech'] = $arrPrintDefineEtech;
		
		//----------------------------------------------------------------------------//


echo "<pre>";

if(!file_exists($strFilename))
{
	// File doesn't exist
	Debug("There is no billing file to validate.  Please run the Billing App first. '$strFilename'");
	die;
}

// This could take a while =\, so echo start and finish
echo "Filesize: ".filesize($strFilename)." bytes\n";
echo "\nReading file contents...";
$arrFileContents = explode("\n", rtrim(file_get_contents($strFilename), "\n"));
echo "\t\t\t\t[   OK   ]\n\n";
echo "[ Generating PDF ]\n\n";

$arrInvoices		= Array();
$arrInvoice			= Array();
$arrChargeTotals	= Array();
$arrGraphData		= Array();
$arrServiceSummary	= Array();
$arrItemisedTable	= Array();
$intLastRecordType	= 18;

$i = 0;
foreach ($arrFileContents as $strLine)
{
	$i++;
	$mixResult = SplitLine($strLine);
	if (is_string($mixResult))
	{
		echo "- ERROR: $mixResult $i\n\n'".$strLine."'\n";
		die;
	}
	
	// REMOVE ME
	continue;
	// REMOVE ME
	
	// Add to Invoice Array
	$arrInvoice[] = $mixResult;
	$arrExpectedRecordTypes = Array();
	switch ($mixResult['RecordType'])
	{
		//---------------------------- FRONT PAGE ----------------------------//
		case 10:
			// Make sure that this record is expected
			ExpectedRecordType($intLastRecordType, 18, $i);
			
			// Invoice Header
			// Init PDF File
			$pdf	= new Cezpdf("a4", "portrait");
			$pdf->selectFont($strPDFDir."fonts/Helvetica.afm");
			
			// Add data to PDF
			$arrTableData	= Array();
			$arrTable		= Array();
			$arrTableData['INVOICE NUMBER']		= $mixResult['InvoiceNo'];
			$arrTableData['BILL PERIOD']		= $mixResult['BillPeriod'];
			$arrTableData['DATE OF ISSUE']		= $mixResult['IssueDate'];
			$arrTableData['ACCOUNT NUMBER']		= $mixResult['AccountNo'];
			$arrTable[] = $arrTableData;
			$pdf->ezTable($arrTable);
			
			$arrTableData	= Array();
			$arrTable		= Array();
			$arrTableData['Opening Balance']	= $mixResult['OpeningBalance'];
			$arrTableData['We Received']		= $mixResult['WeReceived'];
			$arrTableData['Adjustments']		= $mixResult['Adjustments'];
			$arrTableData['Balance']			= $mixResult['Balance'];
			$arrTableData['Total of this Bill'] = $mixResult['BillTotal'];
			$arrTableData['Total Owing']		= $mixResult['TotalOwing'];
			$arrTable[] = $arrTableData;
			$pdf->ezTable($arrTable);
			
			$pdf->ezText($mixResult['CustomerName']);
			$pdf->ezText($mixResult['AddressLine1']);
			$pdf->ezText($mixResult['AddressLine2']);
			$pdf->ezText($mixResult['AddressLine3']);
			$pdf->ezText($mixResult['AddressLine4']);
			$pdf->ezText($mixResult['Suburb']."   ".$mixResult['State']."   ".$mixResult['Postcode']);
			$pdf->ezText($mixResult['PaymentDueDate']."\n");
			break;
		case 11:
			// Make sure that this record is expected
			ExpectedRecordType($intLastRecordType, 49, $i);
			
			// Payment Information
			$arrData = Array();
			$arrData['Biller']				= "BillExpress";
			$arrData['Biller Id']			= $mixResult['BillExpBillerID'];
			$arrData['Customer Reference']	= $mixResult['BillExpRef'];
			$arrTableData[] = $arrData;
			
			$arrData = Array();
			$arrData['Biller']				= "BPay";
			$arrData['Biller Id']			= $mixResult['BPayBillerCode'];
			$arrData['Customer Reference']	= $mixResult['BPayCustomerRef'];
			$arrTableData[] = $arrData;
			$pdf->ezTable($arrTableData);
			
			$pdf->ezText($mixResult['CustomerName']);
			$pdf->ezText($mixResult['AddressLine1']);
			$pdf->ezText($mixResult['AddressLine2']);
			$pdf->ezText($mixResult['AddressLine3']);
			$pdf->ezText($mixResult['AddressLine4']);
			$pdf->ezText($mixResult['AddressLine5']);
			$pdf->ezText($mixResult['PaymentDueDate']."\n");
			
			$arrData = Array();
			$arrData['ACCOUNT NUMBER']	= $mixResult['AccountNo'];
			$arrData['DATE DUE']		= $mixResult['DateDue'];
			$arrData['TOTAL OWING']		= $mixResult['TotalOwing'];
			$arrTableData[] = $arrData;
			$pdf->ezTable($arrTableData);
			
			$pdf->ezText($mixResult['SpecialOffer1']."\n");
			$pdf->ezText($mixResult['SpecialOffer2']."\n");
			break;
		case 18:
			// Invoice Footer
			$arrInvoices[]	= $arrInvoice;
			$strFilename	= $strPDFPath.date("Y-m-d")."_".$arrInvoice[0]['InvoiceNo'].".pdf";
			$strFileData	= $pdf->ezOutput();
			
			echo "Writing PDF to... '$strFilename'\n";
			
			$ptrFile		= fopen($strFilename, "w");
			$arrPass[]		= (bool)$ptrFile;
			$arrPass[]		= fwrite($ptrFile, $strFileData);
			$arrPass[]		= fclose($ptrFile);
			
			foreach($arrPass as $bolPass)
			{
				if (!bolPass)
				{
					echo "\t- There was an error";
				}
			}
			
			$arrInvoice = Array();
			break;
		case 19:
			// File Footer
			// Nothing useful in here
			break;
			
		//----------------------------- GRAPHING -----------------------------//
		case 20:
			// Graph Header
			
			// PDF stuff
			$arrGraphData	= Array();
			$intGraphType	= $mixResult['GraphType'];
			$strGraphXTitle	= $mixResult['XTitle'];
			$strGraphYTitle	= $mixResult['YTitle'];
			$strGraphZTitle	= $mixResult['ZTitle'];			// Not used yet
			$strGraphDesc	= $mixResult['Description'];	// Not used yet
			break;
		case 21:
			// Graph Data
			
			// Check for GraphType, make sure its in correct place
			$arrExpectedRecordTypes[] = 21;
			$arrExpectedRecordTypes[] = 20;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			// FIXME: This will work for Vertical Bargraphs, but nothing else
			$arrData = Array();
			$arrData[$strGraphXTitle]	= $mixResult['Title'];
			$arrData[$strGraphYTitle]	= $mixResult['Value1'];
			$arrGraphData[]				= $arrData;
			break;
		case 29:
			// Graph Footer
			// Data is only for validation
			
			$pdf->ezTable($arrGraphData);
			break;
		
		//-------------------------- CHARGE  TOTALS --------------------------//
		case 40:
			// Charge Totals Header
			
			$arrChargeTotals = Array();
			break;
		case 41:
			// Charge Total Data
			$arrExpectedRecordTypes[]	= 40;
			$arrExpectedRecordTypes[]	= 41;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Call Type']	= $mixResult['ChargeName'];
			$arrData['Subtotal']	= $mixResult['ChargeTotal'];
			$arrChargeTotals[] = $arrData;
			break;
		case 49:
			// Charge Totals Footer
			ExpectedRecordType($intLastRecordType, 41, $i);
			
			$arrData = Array();
			$arrData['Call Type']	= "Total of this bill";
			$arrData['Subtotal']	= $mixResult['BillTotal'];
			$arrChargeTotals[] = $arrData;
			$pdf->ezTable($arrChargeTotals);
			break;
			
		//------------------------- SERVICE  SUMMARY -------------------------//
		case 50:
			// Service Summary Header
			// No data in this record
			ExpectedRecordType($intLastRecordType, 11, $i);
			$pdf->ezNewPage();
			break;
		case 59:
			// Service Summary Footer
			// No data in this record
			$arrExpectedRecordTypes[]	= 69;
			$arrExpectedRecordTypes[]	= 50;				// FIXME: Remove later
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			break;
		case 60:
			// Service Summary Service Header
			$arrExpectedRecordTypes[] = 50;
			$arrExpectedRecordTypes[] = 69;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$strCurrentFNN = $mixResult['FNN'];
			
			$pdf->ezText("Service Summary for ".$strCurrentFNN."\n");
			$arrServiceSummary = Array();
			break;
		case 61:
			// Service Summary Data
			$arrExpectedRecordTypes[] = 60;
			$arrExpectedRecordTypes[] = 61;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Service Name']	= $mixResult['CallType'];
			$arrData['Items']			= $mixResult['CallCount'];
			$arrData['Charge']			= $mixResult['Charge'];
			$arrServiceSummary[] = $arrData;
			break;
		case 69:
			// Service Summary Service Footer
			$arrExpectedRecordTypes[] = 60;
			$arrExpectedRecordTypes[] = 61;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Service Name']	= "Total service charges for $strCurrentFNN";
			$arrData['Items']			= "";
			$arrData['Charge']			= $mixResult['TotalCharge'];
			$arrServiceSummary[] = $arrData;
			$pdf->ezTable($arrServiceSummary);
			break;
			
		//-------------------------- ITEMISED CALLS --------------------------//
		case 70:
			// Itemised Call Header
			// No data in this record
			ExpectedRecordType($intLastRecordType, 59, $i);
			$pdf->ezNewPage();
			break;
		case 79:
			// Itemised Call Footer
			// No data in this record
			$arrExpectedRecordTypes[] = 70;
			$arrExpectedRecordTypes[] = 89;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			break;
		case 80:
			// Itemised Call Service Header
			$arrExpectedRecordTypes[] = 70;
			$arrExpectedRecordTypes[] = 89;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$strCurrentFNN = $mixResult['FNN'];			
			break;
		case 89:
			// Itemised Call Service Footer
			// No data in this record
			ExpectedRecordType($intLastRecordType, 99, $i);
			break;
		case 90:
			// Itemised Call Type Header
			$arrExpectedRecordTypes[] = 80;
			$arrExpectedRecordTypes[] = 99;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrItemisedTable = Array();
			$pdf->ezText("\n\t".$mixResult['CallType']." for ".$strCurrentFNN."\n");
			break;
		case 91:
			// Itemised Data Call
			$arrExpectedRecordTypes[] = 90;
			$arrExpectedRecordTypes[] = 91;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Date']			= $mixResult['Date'];
			$arrData['Time']			= $mixResult['Time'];
			$arrData['Called Party']	= $mixResult['CalledParty'];
			$arrData['Description']		= $mixResult['Description'];
			$arrData['Duration']		= $mixResult['Duration'];
			$arrData['Charge']			= $mixResult['Charge'];
			$arrItemisedTable[] = $arrData;
			break;
		case 92:
			// Itemised Data S&E
			$arrExpectedRecordTypes[] = 90;
			$arrExpectedRecordTypes[] = 92;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Description']		= $mixResult['Description'];
			$arrData['Items']			= $mixResult['Duration'];
			$arrData['Charge']			= $mixResult['Charge'];
			$arrItemisedTable[] = $arrData;
			break;
		case 93:
			// Itemised Data KB
			$arrExpectedRecordTypes[] = 90;
			$arrExpectedRecordTypes[] = 93;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Date']				= $mixResult['Date'];
			$arrData['Time']				= $mixResult['Time'];
			$arrData['Called Party']		= $mixResult['CalledParty'];
			$arrData['Description']			= $mixResult['Description'];
			$arrData['Data Transferred']	= $mixResult['DataTransferred'];
			$arrData['Charge']				= $mixResult['Charge'];
			$arrItemisedTable[] = $arrData;
			break;
		case 94:
			// Itemised Data SMS
			$arrExpectedRecordTypes[] = 90;
			$arrExpectedRecordTypes[] = 94;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Date']				= $mixResult['Date'];
			$arrData['Time']				= $mixResult['Time'];
			$arrData['Called Party']		= $mixResult['CalledParty'];
			$arrData['Description']			= $mixResult['Description'];
			$arrData['Items']				= $mixResult['SMSCount'];
			$arrData['Charge']				= $mixResult['Charge'];
			$arrItemisedTable[] = $arrData;
			break;
		case 99:
			// Itemised Call Type Footer
			$arrExpectedRecordTypes[] = 91;
			$arrExpectedRecordTypes[] = 92;
			$arrExpectedRecordTypes[] = 93;
			$arrExpectedRecordTypes[] = 94;
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			$arrData = Array();
			$arrData['Date']				= "Total Charges for ".$strCurrentFNN;
			$arrData['Charge']				= $mixResult['TotalCharge'];
			$arrItemisedTable[] = $arrData;
			$pdf->ezTable($arrItemisedTable);
			break;
	}
	$intLastRecordType = $mixResult['RecordType'];
}

echo "\nValidation succeeded!\n\n";

die;




function ExpectedRecordType($intLastRecordType, $mixExpectedTypes, $intLine)
{
	if (!is_array($mixExpectedTypes))
	{
		$mixExpectedTypes = Array($mixExpectedTypes);
	}
	
	$bolMatch = FALSE;
	foreach($mixExpectedTypes as $intExpectedType)
	{
		if($intExpectedType == $intLastRecordType)
		{
			$bolMatch = TRUE;
		}
	}
	$strExpectedTypes = implode("|", $mixExpectedTypes);
	
	if(!$bolMatch)
	{
		$intLine--;
		echo "- ERROR: Unexpected record on line $intLine (Expected RecordType(s) $strExpectedTypes, found $intLastRecordType)\n";
		die;
	}
}


 function SplitLine($strLine)
 {
 	// clean the array
	$arrLine = Array();
	
	
	// Make sure this is a recognised type
	$strRecordType = substr($strLine, 0, 3);
	$arrRecordDefine = NULL;
	foreach ($GLOBALS['FileFormatEtech'] as $arrRecordType)
	{
		if ((int)$arrRecordType['RecordType']['Value'] == (int)$strRecordType)
		{
			$arrRecordDefine = $arrRecordType;
			break;
		}
	}
	if (!$arrRecordDefine)
	{
		// Unknown Record Type (ie. invalid file)
		return "Unknown Record Type for line";
	}
	
	//Debug($arrRecordDefine);
	
	// Explode the line
	$arrRawLine = explode($GLOBALS['Delimiter'], $strLine);
		
	// Validate the fields
	$i = 0;
	foreach($arrRecordDefine as $strKey=>$strValue)
	{
		// Is the field optional (must be last field on line)?
		if (($strValue['Optional'] === TRUE) && ($arrRawLine[$i] == NULL))
		{
			continue;
		}
		
		$arrLine[$strKey] = $arrRawLine[$i];
		$mixData = $arrLine[$strKey];

		// Process the field
		switch ($strValue['Type'])
		{
			case ETECH_ROW:
				$strRegex = "/^\d{3}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_ROW in field '$strKey' at index $i on line";
				}
				$mixData = (int)$mixData;
				break;
			case ETECH_INTEGER:
				$strRegex = "/^\d+$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_INTEGER in field '$strKey' at index $i on line";
				}						
				$mixData = (int)$mixData;
				break;
			case ETECH_FNN:
				$strRegex = "/^[A-Za-z0-9 ]+$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_FNN in field '$strKey' at index $i on line";
				}						
				$mixData = $mixData;
				break;
			case ETECH_STRING:
				$strRegex = "/^[^\r\t\n\f]*$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_STRING in field '$strKey' at index $i on line";
				}						
				break;
			case ETECH_DATE_YYYY_MM_DD:
				$strRegex = "/^\d{4}-\d{2}-\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_DATE_YYYY_MM_DD in field '$strKey' at index $i on line";
				}
				break;
			case ETECH_DATE_RANGE:
				$strRegex = "/^\d{2}\/\d{2}\/\d{4}:\d{2}\/\d{2}\/\d{4}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_DATE_RANGE in field '$strKey' at index $i on line";
				}
				break;
			case ETECH_DATE_YYYYMMDD:
				$strRegex = "/^\d{8}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_DATE_YYYYMMDD in field '$strKey' at index $i on line";
				}
				break;
			case ETECH_DATETIME:
				$strRegex = "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_DATETIME in field '$strKey' at index $i on line";
				}
				break;
			case ETECH_DATE_MONTH_YY:
				$strRegex = "/^[A-Za-z]{3,9} \d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_DATE_MONTH_YY in field '$strKey' at index $i on line";
				}
				break;
			case ETECH_DURATION:
				$strRegex = "/^\d+:\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type ETECH_DURATION in field '$strKey' at index $i on line";
				}
				break;
			case ETECH_SHORT_CURRENCY:
				$strRegex = "/^[\d\,]+\.\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					// Not a number - invalid
					Debug("'$mixData'");
					return "Data is not of type ETECH_SHORT_CURRENCY in field '$strKey' at index $i on line";
				}
				$mixData = "$".$mixData;
				break;
			case ETECH_LONG_CURRENCY:
				$strRegex = "/^[\d\,]+\.\d{4}$/";
				if (!preg_match($strRegex, $mixData))
				{
					// Not a number - invalid
					Debug("'$mixData'");
					return "Data is not of type ETECH_LONG_CURRENCY in field '$strKey' at index $i on line";
				}
				$mixData = "$".$mixData;
				break;
			default:
				// Invalid data type
				Debug("'$mixData'");
				return "Invalid Data Type (".(int)$strValue['Type'].") in field '$strKey' at index $i on line";
		}
		
		$arrLine[$strKey] = trim($mixData);
		$i++;
	}
	
	return $arrLine;
 }
?>
