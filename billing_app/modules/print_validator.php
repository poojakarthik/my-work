<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
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
 * @version		7.1
 * @copyright	2006 VOIPTEL Pty Ltd
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

$strPath		= "/home/vixen_bill_output/sample/";
$strPDFPath		= "/home/vixen_bill_output/sample/pdf/";
$strFilename	= $strPath."sample".date("Y-m-d")."vbf";

$GLOBALS['FileFormat']	= $arrDefine;

echo "<pre>";

if(!file_exists($strFilename))
{
	// File doesn't exist
	Debug("There is no billing file to validate.  Please run the Billing App first. '$strFilename'");
	die;
}

// This could take a while =\, so echo start and finish
echo "\nReading file contents...";
$arrFileContents = explode("\n", file_get_contents($strFilename));
echo "\t\t\t\t[   OK   ]\n\n";
echo "[ Generating PDF ]\n\n";

$arrInvoices = Array();
$arrInvoice = Array();

$i = 0;
foreach ($arrFileContents as $strLine)
{
	$i++;
	$mixResult = SplitLine($strLine);
	if (is_string($mixResult))
	{
		echo "- ERROR: $mixResult $i\n";
		die;
	}
	
	// Add to Invoice Array
	$arrInvoice[] = $mixResult;
	switch ($mixResult['RecordType'])
	{
		case 10:
			// Invoice Header
			$arrInvoice	= Array();

			// Init PDF File
			$pdfInvoice	= new Cezpdf("a4", "portrait");
			$pdfInvoice->selectFont($strPDFDir."fonts/Helvetica.afm");
			
			// Add data to PDF
			$arrTableData['INVOICE NUMBER']		= $mixResult['InvoiceNo'];
			$arrTableData['BILL PERIOD']		= $mixResult['BillPeriod'];
			$arrTableData['DATE OF ISSUE']		= $mixResult['DateIssued'];
			$arrTableData['ACCOUNT NUMBER']		= $mixResult['AccountNo'];
			$pdf->ezTable($arrTableData);
			
			$arrTableData = Array();
			$arrTableData['Opening Balance']	= $mixResult['OpeningBalance'];
			$arrTableData['We Received']		= $mixResult['WeReceived'];
			$arrTableData['Adjustments']		= $mixResult['Adjustments'];
			$arrTableData['Balance']			= $mixResult['Balance'];
			$arrTableData['Total of this Bill'] = $mixResult['BillTotal'];
			$arrTableData['Total Owing']		= $mixResult['TotalOwing'];
			$pdf->ezTable($arrTableData);
			
			$arrTableData = Array();
			$arrTableData['Opening Balance']	= $mixResult['OpeningBalance'];
			$arrTableData['We Received']		= $mixResult['WeReceived'];
			$arrTableData['Adjustments']		= $mixResult['Adjustments'];
			$arrTableData['Balance']			= $mixResult['Balance'];
			$arrTableData['Total of this Bill'] = $mixResult['BillTotal'];
			$arrTableData['Total Owing']		= $mixResult['TotalOwing'];
			$pdf->ezTable($arrTableData);
			break;
		case 19:
			// Invoice Footer
			$arrInvoices[]	= $arrInvoice;
			$strFilename	= date("Y-m-d")."_".$arrInvoice[0]['InvoiceNo'].".pdf";
			$pdfInvoice->ezStream(Array('Content-Disposition' => $strPDFPath.$strFilename));
			break;
	}
	$intLastRecordType = $mixResult['RecordType'];
/*
	elseif (!$bolHaveInvoice)
	{
		// Unexpected row between invoices
		echo "- ERROR: Unexpected record between invoices on line $i\n";
		die;
	}
*/
}












 function SplitLine($strLine)
 {
 	// clean the array
	$arrLine = Array();
	
	$strRecordType = substr($strLine, 0, 2);
	$arrRecordDefine = NULL;
	foreach ($GLOBALS['FileFormat'] as $arrRecordType)
	{
		if ($arrRecordType['RecordType'] == $strRecordType)
		{
			$arrRecordDefine = $arrRecordType;
			break;
		}
	}
	if (!$arrRecordDefine)
	{
		// Unknown Record Type (ie. invalid file)
		return "Unknown Record Type for line ";
	}
	$i = 0;
	// fixed width record
	foreach($arrRecordDefine as $strKey=>$strValue)
	{
		$arrLine[$strKey] = trim(substr($strLine, $strValue['Start'], $strValue['Length']));
		$mixData = $arrLine[$strKey];

		// Process the field
		switch ($strValue['Type'])
		{
			case BILL_TYPE_INTEGER:
				$strRegex = "/^\d{".$strValue['Length']."}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_INTEGER in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}						
				$mixData = (int)$mixData;
				break;
			case BILL_TYPE_CHAR:
				$strRegex = "/^[^\r\t\n\f]{".$strValue['Length']."}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_CHAR in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}						
				break;
			case BILL_TYPE_BINARY:
				$strRegex = "/^[01]{".$strValue['Length']."}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_BINARY in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}		
				break;
			case BILL_TYPE_FLOAT:
				$strRegex = "/^\d+\.d+$/";
				if (!preg_match($strRegex, $mixData) || strlen($mixData) > $strValue['Length'])
				{
					// Not a number - invalid
					return "Data is not of type BILL_TYPE_FLOAT in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}
				$mixData = (float)$mixData;
				break;
			case BILL_TYPE_SHORTDATE:
				$strRegex = "/^\d{2}\/d{2}/\d{4}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_SHORTDATE in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}						
				break;
			case BILL_TYPE_LONGDATE:
				$strRegex = "/^[A-Za-z]{3} \d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_LONGDATE in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}
				break;
			case BILL_TYPE_TIME:
				$strRegex = "/^\d{2}:\d{2}:\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_TIME in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}
				break;
			case BILL_TYPE_DURATION:
				$strRegex = "/^\d{3}:\d{2}:\d{2}$/";
				if (!preg_match($strRegex, $mixData))
				{
					return "Data is not of type BILL_TYPE_DURATION in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}
				break;
			case BILL_TYPE_SHORTCURRENCY:
				$strRegex = "/^\d+\.d+$/";
				if (!preg_match($strRegex, $mixData) || strlen($mixData) > $strValue['Length'])
				{
					// Not a number - invalid
					return "Data is not of type BILL_TYPE_SHORTCURRENCY in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
				}
				$mixData = (float)$mixData;
				break;
			default:
				// Invalid data type
				return "Invalid Data Type in field '$strKey' ({$strValue['Start']}:{$strValue['Length']}) on line ";
		}
	}
	
	return $arrLine;
 }
?>
