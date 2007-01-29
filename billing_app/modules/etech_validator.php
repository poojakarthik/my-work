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
		$arrPrintDefine['FileHeader']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['FileHeader']		['RecordType']		['Value']	= 1;
		$arrPrintDefine['SPDetails']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SPDetails']		['RecordType']		['Value']	= 2;
		$arrPrintDefine['GlobalMessage']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['GlobalMessage']	['RecordType']		['Value']	= 3;
		$arrPrintDefine['Brands']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['Brands']			['RecordType']		['Value']	= 4;
		$arrPrintDefine['SequenceNo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SequenceNo']		['RecordType']		['Value']	= 5;
		$arrPrintDefine['InvoiceNo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['InvoiceNo']		['RecordType']		['Value']	= 6;
		$arrPrintDefine['BrandId']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['BrandId']			['RecordType']		['Value']	= 7;
		$arrPrintDefine['InvoiceCharge']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['InvoiceCharge']	['RecordType']		['Value']	= 10;
		$arrPrintDefine['Customer']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['Customer']			['RecordType']		['Value']	= 11;
		$arrPrintDefine['ChargeSummary']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ChargeSummary']	['RecordType']		['Value']	= 15;
		$arrPrintDefine['DetailsHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['DetailsHeader']	['RecordType']		['Value']	= 19;
		$arrPrintDefine['CategoryHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CategoryHeader']	['RecordType']		['Value']	= 20;
		$arrPrintDefine['CategoryFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CategoryFooter']	['RecordType']		['Value']	= 21;
		$arrPrintDefine['DetailsFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['DetailsFooter']	['RecordType']		['Value']	= 29;
		$arrPrintDefine['SvcSummHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SvcSummHeader']	['RecordType']		['Value']	= 39;
		$arrPrintDefine['ServiceHeader']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ServiceHeader']	['RecordType']		['Value']	= 40;
		$arrPrintDefine['ServiceDetail']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ServiceDetail']	['RecordType']		['Value']	= 41;
		$arrPrintDefine['ServiceTotals']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ServiceTotals']	['RecordType']		['Value']	= 45;
		$arrPrintDefine['ServiceFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ServiceFooter']	['RecordType']		['Value']	= 48;
		$arrPrintDefine['SvcSummFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SvcSummFooter']	['RecordType']		['Value']	= 49;
		$arrPrintDefine['SpecialMsg1']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SpecialMsg1']		['RecordType']		['Value']	= 50;
		$arrPrintDefine['SpecialMsg2']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SpecialMsg2']		['RecordType']		['Value']	= 51;
		$arrPrintDefine['SpecialMsg3']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SpecialMsg3']		['RecordType']		['Value']	= 52;
		$arrPrintDefine['SpecialMsg4']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SpecialMsg4']		['RecordType']		['Value']	= 53;
		$arrPrintDefine['SpecialMsg5']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['SpecialMsg5']		['RecordType']		['Value']	= 54;
		$arrPrintDefine['CreditHeader']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CreditHeader']		['RecordType']		['Value']	= 60;
		$arrPrintDefine['CreditBalance']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CreditBalance']	['RecordType']		['Value']	= 61;
		$arrPrintDefine['CreditAdded']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CreditAdded']		['RecordType']		['Value']	= 62;
		$arrPrintDefine['CreditUsed']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CreditUsed']		['RecordType']		['Value']	= 63;
		$arrPrintDefine['CreditLeft']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CreditLeft']		['RecordType']		['Value']	= 64;
		$arrPrintDefine['CreditFooter']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CreditFooter']		['RecordType']		['Value']	= 69;
		$arrPrintDefine['GraphInfo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['GraphInfo']		['RecordType']		['Value']	= 70;
		$arrPrintDefine['GraphData']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['GraphData']		['RecordType']		['Value']	= 71;
		$arrPrintDefine['ExtraInfo']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ExtraInfo']		['RecordType']		['Value']	= 80;
		$arrPrintDefine['InvoiceFooter']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['InvoiceFooter']	['RecordType']		['Value']	= 95;
		$arrPrintDefine['FileFooter']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['FileFooter']		['RecordType']		['Value']	= 99;
		$arrPrintDefine['ItemisedCall']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['ItemisedS&E']		['RecordType']		['Type']	= ETECH_ROW;
		
		// Call Details
		$arrPrintDefine['CallLLNational']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallLLNational']	['RecordType']		['Value']	= 102;
		$arrPrintDefine['CallLLTo1300']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallLLTo1300']		['RecordType']		['Value']	= 103;
		$arrPrintDefine['CallLLToMob']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallLLToMob']		['RecordType']		['Value']	= 104;
		$arrPrintDefine['CallLLToIDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallLLToIDD']		['RecordType']		['Value']	= 105;
		$arrPrintDefine['CallMobToMob']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMobToMob']		['RecordType']		['Value']	= 106;
		$arrPrintDefine['CallMobToNat']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMobToNat']		['RecordType']		['Value']	= 107;
		$arrPrintDefine['CallMobOther']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMobOther']		['RecordType']		['Value']	= 110;
		$arrPrintDefine['CallMobRoam']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMobRoam']		['RecordType']		['Value']	= 111;
		$arrPrintDefine['CallMobToIDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMobToIDD']		['RecordType']		['Value']	= 112;
		$arrPrintDefine['CallMobTo18']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMobTo18']		['RecordType']		['Value']	= 113;
		$arrPrintDefine['Call13IDD']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['Call13IDD']		['RecordType']		['Value']	= 114;
		$arrPrintDefine['CallLLOther']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallLLOther']		['RecordType']		['Value']	= 116;
		$arrPrintDefine['Call18Inbound']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['Call18Inbound']	['RecordType']		['Value']	= 117;
		$arrPrintDefine['Call13Inbound']	['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['Call13Inbound']	['RecordType']		['Value']	= 118;
		$arrPrintDefine['CallSMS']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallSMS']			['RecordType']		['Value']	= 119;
		$arrPrintDefine['CallMMS']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallMMS']			['RecordType']		['Value']	= 120;
		$arrPrintDefine['CallUnknown']		['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallUnknown']		['RecordType']		['Value']	= 135;
		$arrPrintDefine['CallS&E']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallS&E']			['RecordType']		['Value']	= 208;				// Record type is 108 in footer
		$arrPrintDefine['CallOC&C']			['RecordType']		['Type']	= ETECH_ROW;
		$arrPrintDefine['CallOC&C']			['RecordType']		['Value']	= 237;				// Record type is 137 in footer
		
		// Row Type					Field Name			Property	Value
		//------------------------------------------------------------------------------
		
		// File Header
		$arrPrintDefine['FileHeader']		['Date']			['Type']	= ETECH_DATE_YYYYMMDD;
		$arrPrintDefine['FileHeader']		['Filename']		['Type']	= ETECH_STRING;
		$arrPrintDefine['FileHeader']		['SPName']			['Type']	= ETECH_STRING;
		$arrPrintDefine['FileHeader']		['BillingPeriod']	['Type']	= ETECH_DATE_MONTH_YY;
		
		// SP Details
		$arrPrintDefine['SPDetails']		['BusinessName']	['Type']	= ETECH_STRING;
		$arrPrintDefine['SPDetails']		['Address1']		['Type']	= ETECH_STRING;
		$arrPrintDefine['SPDetails']		['Address2']		['Type']	= ETECH_STRING;
		$arrPrintDefine['SPDetails']		['Suburb']			['Type']	= ETECH_STRING;
		$arrPrintDefine['SPDetails']		['Postcode']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['SPDetails']		['State']			['Type']	= ETECH_STRING;
		$arrPrintDefine['SPDetails']		['Phone']			['Type']	= ETECH_FNN;
		$arrPrintDefine['SPDetails']		['Fax']				['Type']	= ETECH_FNN;
		$arrPrintDefine['SPDetails']		['URL']				['Type']	= ETECH_STRING;
		$arrPrintDefine['SPDetails']		['BPayCode']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['SPDetails']		['InvoiceDate']		['Type']	= ETECH_DATE_YYYY_MM_DD;
		$arrPrintDefine['SPDetails']		['DueByDate']		['Type']	= ETECH_DATE_YYYY_MM_DD;
		
		// Global Message (unused)
		$arrPrintDefine['GlobalMessage']	['Message']			['Type']	= ETECH_STRING;
		
		// Brands (Telco Blue, Voicetalk, etc)
		$arrPrintDefine['Brands']			['Brand1']			['Type']	= ETECH_STRING;
		$arrPrintDefine['Brands']			['Brand2']			['Type']	= ETECH_STRING;
		$arrPrintDefine['Brands']			['Brand3']			['Type']	= ETECH_STRING;
		
		// Invoice Sequence Number
		$arrPrintDefine['SequenceNo']		['SequenceNo']		['Type']	= ETECH_INTEGER;
		
		// Invoice Number
		$arrPrintDefine['InvoiceNo']		['InvoiceNo']		['Type']	= ETECH_INTEGER;
		
		// Invoice Brand Id
		$arrPrintDefine['BrandId']			['BrandId']			['Type']	= ETECH_INTEGER;
		
		// Invoice Charges
		$arrPrintDefine['InvoiceCharge']	['PreviousBalance']	['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['InvoiceCharge']	['Received']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['InvoiceCharge']	['Adjustments']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['InvoiceCharge']	['Overdue']			['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['InvoiceCharge']	['NewCharges']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['InvoiceCharge']	['AmountOwing']		['Type']	= ETECH_SHORT_CURRENCY;
		
		// Customer Details
		$arrPrintDefine['Customer']			['AccountNo']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['Customer']			['CheckDigit']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['Customer']			['Address1']		['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['Address2']		['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['Suburb']			['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['Postcode']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['Customer']			['State']			['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['BusinessName']	['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['Firstname']		['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['Lastname']		['Type']	= ETECH_STRING;
		$arrPrintDefine['Customer']			['InvoiceType']		['Type']	= ETECH_STRING;
		
		// Charge Summaries
		$arrPrintDefine['ChargeSummary']	['Category']		['Type']	= ETECH_STRING;
		$arrPrintDefine['ChargeSummary']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Category Header
		$arrPrintDefine['CategoryHeader']	['Category']		['Type']	= ETECH_STRING;
		$arrPrintDefine['CategoryHeader']	['FNN']				['Type']	= ETECH_FNN;
		
		// Itemised Call Data
		$arrPrintDefine['ItemisedCall']		['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['ItemisedCall']		['Datetime']		['Type']	= ETECH_DATETIME;
		$arrPrintDefine['ItemisedCall']		['CalledParty']		['Type']	= ETECH_FNN;
		$arrPrintDefine['ItemisedCall']		['Description']		['Type']	= ETECH_STRING;
		$arrPrintDefine['ItemisedCall']		['Duration']		['Type']	= ETECH_DURATION;
		$arrPrintDefine['ItemisedCall']		['Charge']			['Type']	= ETECH_LONG_CURRENCY;
		
		// Itemised S&E Data
		$arrPrintDefine['ItemisedS&E']		['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['ItemisedS&E']		['Description']		['Type']	= ETECH_STRING;
		$arrPrintDefine['ItemisedS&E']		['Charge']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Charge Footer
		$arrPrintDefine['CategoryFooter']	['CategoryId']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['CategoryFooter']	['RecordTotal']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['CategoryFooter']	['FNN']				['Type']	= ETECH_FNN;
		$arrPrintDefine['CategoryFooter']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Service Header
		$arrPrintDefine['ServiceHeader']	['FNN']				['Type']	= ETECH_FNN;
		//$arrPrintDefine['ServiceHeader']	['CostCentre']		['Type']	= ETECH_STRING;
		
		// Service Detail
		$arrPrintDefine['ServiceDetail']	['RecordCount']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['ServiceDetail']	['ChargeType']		['Type']	= ETECH_STRING;
		$arrPrintDefine['ServiceDetail']	['Charge']			['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['ServiceDetail']	['CallCount']		['Type']	= ETECH_INTEGER;
		
		// Service Totals
		$arrPrintDefine['ServiceTotals']	['RecordTotal']		['Type']	= ETECH_INTEGER;
		$arrPrintDefine['ServiceTotals']	['FNN']				['Type']	= ETECH_FNN;
		$arrPrintDefine['ServiceTotals']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Service Footer
		//$arrPrintDefine['ServiceFooter']	['CostCentre']		['Type']	= ETECH_STRING;
		//$arrPrintDefine['ServiceFooter']	['Total']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Special Message 1
		$arrPrintDefine['SpecialMsg1']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 2
		$arrPrintDefine['SpecialMsg2']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 3
		$arrPrintDefine['SpecialMsg3']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 4
		$arrPrintDefine['SpecialMsg4']		['Message']			['Type']	= ETECH_STRING;
		
		// Special Message 5
		$arrPrintDefine['SpecialMsg5']		['Message']			['Type']	= ETECH_STRING;
		
		// Credit Balance
		$arrPrintDefine['CreditBalance']	['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Added
		$arrPrintDefine['CreditAdded']		['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Left
		$arrPrintDefine['CreditLeft']		['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Credit Remaining
		$arrPrintDefine['CreditLeft']		['Message']			['Type']	= ETECH_SHORT_CURRENCY;
		
		// Global Graph Info
		$arrPrintDefine['GraphInfo']		['DateRange']		['Type']	= ETECH_DATE_RANGE;
		
		// Graph Data
		$arrPrintDefine['GraphData']		['CurrentMonth']	['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['GraphData']		['LastMonth']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['GraphData']		['2MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['GraphData']		['3MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['GraphData']		['4MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		$arrPrintDefine['GraphData']		['5MonthsAgo']		['Type']	= ETECH_SHORT_CURRENCY;
		
		// File Footer
		$arrPrintDefine['FileFooter']		['InvoiceCount']	['Type']	= ETECH_INTEGER;
		
		$GLOBALS['FileFormat'] = $arrPrintDefine;
		
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
	foreach ($GLOBALS['FileFormat'] as $arrRecordType)
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
	
	Debug($arrRecordDefine);
	
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
				$strRegex = "/^\d+\.\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					// Not a number - invalid
					Debug("'$mixData'");
					return "Data is not of type ETECH_SHORT_CURRENCY in field '$strKey' at index $i on line";
				}
				$mixData = "$".$mixData;
				break;
			case ETECH_LONG_CURRENCY:
				$strRegex = "/^\d+\.\d{4}$/";
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
