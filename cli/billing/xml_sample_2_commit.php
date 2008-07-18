<?php

// Framework
require_once("../../flex.require.php");
$strInvoiceRun	= "20080701112204";
$selInvoice		= new StatementSelect("Invoice", "Id", "Account = <Account> AND InvoiceRun = <InvoiceRun>");


$strXMLPath	= FILES_BASE_PATH."invoices/xml/{$strInvoiceRun}/";

// Get File List
chdir($strXMLPath);
$arrFiles	= glob('*.xml');

foreach ($arrFiles as $strFile)
{
	$intAccount		= (int)basename($strFile, 'xml');
	
	if ($selInvoice->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun)))
	{
		$arrInvoice	= $selInvoice->Fetch();
		$strCommand	= "perl -pi -e 's/20080701112204/{$arrInvoice['Id']}/g' {$strFile}";
		//shell_exec($strCommand);
		CliEcho($strCommand);
	}
	else
	{
		CliEcho("Error retrieving Invoice Id for '{$intAccount}'");
		exit(1);
	}
}
?>