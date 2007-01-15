<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-7 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// print_validator
//----------------------------------------------------------------------------//
/**
 * print_validator
 *
 * Validates a Vixen Billing File (*.vbf)
 *
 * Validates a Vixen Billing File (*.vbf).  It will only validate a vbf file
 * created today
 *
 * @file		print_validator.php
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
$strFilename	= $strPath.date("Y-m-d").".vbf";
//$strFilename	= $strPath."sample2007-01-12.vbf";
//$strFilename	= $strPath."2007-01-12.vbf";
//$strFilename	= "/home/richdavis/2007-01-12.vbf";


$GLOBALS['FileFormat']	= $arrPrintDefine;

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
$intLastRecordType	= 19;

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
	
	// Add to Invoice Array
	$arrInvoice[] = $mixResult;
	$arrExpectedRecordTypes = Array();
	switch ($mixResult['RecordType'])
	{
		//---------------------------- FRONT PAGE ----------------------------//
		case 10:
			// Make sure that this record is expected
			ExpectedRecordType($intLastRecordType, 19, $i);
			
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
			
			$pdf->ezText($mixResult['CustomerName']."\n");
			$pdf->ezText($mixResult['PropertyName']."\n");
			$pdf->ezText($mixResult['AddressLine1']."\n");
			$pdf->ezText($mixResult['Suburb']."   ".$mixResult['State']."   ".$mixResult['Postcode']."\n");
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
			
			$pdf->ezText($mixResult['CustomerName']."\n");
			$pdf->ezText($mixResult['PropertyName']."\n");
			$pdf->ezText($mixResult['AddressLine1']."\n");
			$pdf->ezText($mixResult['AddressLine2']."\n");
			$pdf->ezText($mixResult['PaymentDueDate']."\n");
			
			$arrData = Array();
			$arrData['ACCOUNT NUMBER']	= $mixResult['AccountNo'];
			$arrData['DATE DUE']		= $mixResult['DateDue'];
			$arrData['TOTAL OWING']		= $mixResult['TotalOwing'];
			$arrTableData[] = $arrData;
			$pdf->ezTable($arrTableData);
			
			$pdf->ezText($mixResult['SpecialOffer1']."\n\n");
			$pdf->ezText($mixResult['SpecialOffer2']."\n");
			break;
		case 19:
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
			switch ($intGraphType)
			{
				case 1:
					// Vertical Bar Graph
					$arrExpectedRecordTypes[] = 20;
					break;
				case 2:
					// Vertical Breakdown Bar Graph
					$arrExpectedRecordTypes[] = 30;
					break;
				case 3:
					// Horizontal Split Bar Graph
					$arrExpectedRecordTypes[] = 32;
					break;
				case 4:
					// Line Graph
				case 5:
					// Line Graph XYZ
					$arrExpectedRecordTypes[] = 34;
					break;
				default:
					// Unknown Graph Type
					echo "- ERROR: Unknown Graph Type (0$intGraphType) on line $i\n";
					die;
			}
			ExpectedRecordType($intLastRecordType, $arrExpectedRecordTypes, $i);
			
			// FIXME: This will work for Vertical Bargraphs, but nothing else
			$arrData[$strGraphXTitle]	= $mixResult['Title'];
			$arrData[$strGraphYTitle]	= $mixResult['Value'];
			$arrGraphData[]				= $arrData;
			break;
		case 29:
			// Graph Footer
			// Data is only for validation
			break;
		case 30:
			// Vertical Breakdown Bargraph Column Header
			// Not used yet
			break;
		case 31:
			// Vertical Breakdown Bargraph Column Footer
			// Not used yet
			break;
		case 32:
			// Hortizontal Bargraph Row Header
			// Not used yet
			break;
		case 33:
			// Hortizontal Bargraph Row Footer
			// Not used yet
			break;
		case 34:
			// Linegraph Line Header
			// Not used yet
			break;
		case 35:
			// Linegraph Line Footer
			// Not used yet
			break;
		
		//-------------------------- CHARGE  TOTALS --------------------------//
		case 40:
			// Charge Totals Header
			
			$arrChargeTotals = Array();
			break;
		case 41:
			// Charge Total Data
			ExpectedRecordType($intLastRecordType, 40, $i);
			
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
			ExpectedRecordType($intLastRecordType, 61, $i);
			
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
			ExpectedRecordType($intLastRecordType, 89, $i);
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
	$strRecordType = substr($strLine, 0, 2);
	$arrRecordDefine = NULL;
	foreach ($GLOBALS['FileFormat'] as $arrRecordType)
	{
		if ($arrRecordType['RecordType']['Value'] == $strRecordType)
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
	
	
	// Validate the fields
	$i = 0;
	foreach($arrRecordDefine as $strKey=>$strValue)
	{
		$arrLine[$strKey] = substr($strLine, $strValue['Start'], $strValue['Length']);
		$mixData = $arrLine[$strKey];

		// Process the field
		switch ($strValue['Type'])
		{
			case BILL_TYPE_INTEGER:
				$strRegex = "/^\d{".$strValue['Length']."}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_INTEGER in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}						
				$mixData = (int)$mixData;
				break;
			case BILL_TYPE_CHAR:
				$strRegex = "/^[^\r\t\n\f]{".$strValue['Length']."}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_CHAR in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}						
				break;
			case BILL_TYPE_BINARY:
				$strRegex = "/^[01]{".$strValue['Length']."}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_BINARY in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}		
				break;
			case BILL_TYPE_FLOAT:
				$strRegex = "/^\d+(\.\d+)?$/";
				if (!preg_match($strRegex, $mixData) || strlen($mixData) > $strValue['Length'])
				{
					// Not a number - invalid
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_FLOAT in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}
				$mixData = (float)$mixData;
				break;
			case BILL_TYPE_SHORTDATE:
				$strRegex = "/^\d{2}\/\d{2}\/\d{4}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_SHORTDATE in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}						
				break;
			case BILL_TYPE_LONGDATE:
				$strRegex = "/^\d{2} [A-Za-z]{3} \d{4}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_LONGDATE in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}
				break;
			case BILL_TYPE_TIME:
				$strRegex = "/^\d{2}:\d{2}:\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_TIME in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}
				break;
			case BILL_TYPE_DURATION:
				$strRegex = "/^\d{3}:\d{2}:\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_DURATION in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}
				break;
			case BILL_TYPE_SHORTCURRENCY:
				$strRegex = "/^-?\d+\.\d{2}$/";	// FIXME: Remove -? in final version
				if (!preg_match($strRegex, $mixData) || strlen($mixData) > $strValue['Length'])
				{
					// Not a number - invalid
					Debug("'$mixData'");
					return "Data is not of type BILL_TYPE_SHORTCURRENCY in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
				}
				$mixData = (float)$mixData;
				break;
			default:
				// Invalid data type
				Debug("'$mixData'");
				return "Invalid Data Type in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line";
		}
		
		$arrLine[$strKey] = trim($mixData);
	}
	
	return $arrLine;
 }
?>
