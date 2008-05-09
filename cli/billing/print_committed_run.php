<?php

// Framework & Billing Application
require_once("../../flex.require.php");

define("INVOICE_XML_PATH",	FILES_BASE_PATH."invoices/xml/");

$bilInvoiceXML	= new BillingModuleInvoiceXML($GLOBALS['dbaDatabase']);

// Get Command Line Arguments
$strInvoiceRun	= $argv[2];
CliEcho("\n[ REPRINTING INVOICE RUN '$strInvoiceRun' ]\n");
$arrAccounts	= Array();
for ($i = 3; $i <= $argc; $i++)
{
	// Parse Account Numbers
	$intArgument	= (int)$argv[$i]; 
	if (strlen($intArgument) == 10)
	{
		// 'Valid' Account number
		$arrAccounts[]	= $intArgument;
	}
}

if (!$strInvoiceRun)
{
	CliEcho("No InvoiceRun specified!\n");
	die;
}

// Determine XML path
define("INVOICE_XML_PATH_SAMPLE",	INVOICE_XML_PATH."gold/".$strInvoiceRun."/");

// Generate Invoice XML
if (count($arrAccounts))
{
	CliEcho("Generating ".count($arrAccounts)." Invoices...\n");
	
	// Select certain Accounts from an InvoiceRun
	$selInvoices	= new StatementSelect("Invoice", "*", "InvoiceRun = <InvoiceRun> AND Account = <Account>");
	
	foreach ($arrAccounts as $intAccount)
	{
		CliEcho("\t + $intAccount");
		
		if (!$selInvoices->Execute(Array('InvoiceRun' => $strInvoiceRun, 'Account' => $intAccount)))
		{
			// Invalid Invoice Run
			CliEcho("\t\t ERROR: Invoice Run '$strInvoiceRun' and Account '$intAccount' is invalid pair!\n");
		}
		
		// Print the Invoice
		if ($arrInvoice = $selInvoices->Fetch())
		{ 
			$strXML	= $bilInvoiceXML->AddInvoice($arrInvoice, TRUE);
			WriteXMLToFile($strXML, $arrInvoice['Account']);
		}
	}
}
else
{
	/*// Select entire InvoiceRun
	$selInvoices	= new StatementSelect("Invoice", "*", "InvoiceRun = <InvoiceRun>");
	if (!$selInvoices->Execute(Array('InvoiceRun' => $strInvoiceRun)))
	{
		// Invalid Invoice Run
		CliEcho("Invoice Run '$strInvoiceRun' is invalid!\n");
	}
	
	while ($arrInvoice = $selInvoices->Fetch())
	{
		// Print the Invoice
		$strXML	= $bilInvoiceXML->AddInvoice($arrInvoice);
		WriteXMLToFile($strXML, $arrInvoice['Account']);
	}*/
}
die;

function WriteXMLToFile($strXML, $intAccount)
{
	@mkdir(INVOICE_XML_PATH_SAMPLE, 0777, TRUE);
	
	$strFilename	= INVOICE_XML_PATH_SAMPLE."$intAccount.xml";
	file_put_contents($strFilename, $strXML);
}



?>