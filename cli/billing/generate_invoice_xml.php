<?php

// Framework & Billing Application
require_once("../../flex.require.php");
$arrConfig	= LoadApplication();

//define("BILLING_INVOICE_DEBUG"	, TRUE);

$arrConfig['PrintingMode']	= 'FINAL';
$bilInvoiceXML	= new BillingModuleInvoiceXML(DataAccess::getDataAccess(), $arrConfig);

// Get Command Line Arguments
$strInvoiceRun	= $argv[1];
CliEcho("\n[ REPRINTING INVOICE RUN '$strInvoiceRun' ]\n");
$arrAccounts	= Array();
for ($i = 2; $i <= $argc; $i++)
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
define("INVOICE_XML_PATH_SAMPLE",	INVOICE_XML_PATH.$strInvoiceRun.'/');

// Generate Invoice XML
if (count($arrAccounts))
{
	CliEcho("Generating ".count($arrAccounts)." Invoices...\n");
	
	// Select certain Accounts from an InvoiceRun
	$selInvoices	= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account", "Invoice.*, CustomerGroup", "InvoiceRun = <InvoiceRun> AND Account = <Account>");
	
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
			WriteXMLToFile($strXML, $arrInvoice);
		}
	}
}
else
{
	// Select entire InvoiceRun
	$selInvoices	= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account", "Invoice.*, CustomerGroup", "InvoiceRun = <InvoiceRun>");
	if (!$selInvoices->Execute(Array('InvoiceRun' => $strInvoiceRun)))
	{
		// Invalid Invoice Run
		CliEcho("Invoice Run '$strInvoiceRun' is invalid!\n");
	}
	else
	{
		while ($arrInvoice = $selInvoices->Fetch())
		{
			CliEcho("\t + {$arrInvoice['Account']}");
			
			// Print the Invoice
			$strXML	= $bilInvoiceXML->AddInvoice($arrInvoice, TRUE);
			WriteXMLToFile($strXML, $arrInvoice);
		}
	}
}
die;

function WriteXMLToFile($strXML, $arrInvoice)
{
	$intAccount			= $arrInvoice['Account'];
	$intCustomerGroup	= $arrInvoice['CustomerGroup'];
	
	@mkdir(INVOICE_XML_PATH_SAMPLE, 0777, TRUE);
	
	$strFilename	= INVOICE_XML_PATH_SAMPLE."$intAccount.xml";
	file_put_contents($strFilename, $strXML);
}



?>