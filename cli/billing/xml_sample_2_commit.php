<?php

// Framework
require_once("../../flex.require.php");
$selInvoice		= new StatementSelect("Invoice", "Id", "Account = <Account> AND InvoiceRun = <InvoiceRun>");
$selInvoiceRun	= new StatementSelect("InvoiceRun", "Id", "InvoiceRun = <InvoiceRun>");

// Read Command Line Parameter
$strInvoiceRun	= $argv[1];
$strXMLPath	= FILES_BASE_PATH."invoices/xml/{$strInvoiceRun}/";
if ($selInvoiceRun->Execute(Array('InvoiceRun' => $strInvoiceRun)))
{
	// Get File List
	chdir($strXMLPath);
	$arrFiles	= glob('*.xml');
	
	foreach ($arrFiles as $strFile)
	{
		$intAccount		= (int)basename($strFile, 'xml');
		
		if ($selInvoice->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun)))
		{
			CliEcho($intAccount);
			$arrInvoice	= $selInvoice->Fetch();
			$strCommand	= "perl -pi -e 's/<Invoice Id=\\\"SAMPLE\\\">/<Invoice Id=\\\"{$arrInvoice['Id']}\\\">/g' {$strXMLPath}{$strFile}";
			//CliEcho($strCommand);
			//die;
			CliEcho(shell_exec($strCommand));
		}
		else
		{
			CliEcho("Error retrieving Invoice Id for '{$intAccount}'");
			exit(1);
		}
	}
}
else
{
	CliEcho("'{$strInvoiceRun}' is not a committed InvoiceRun!");
	exit(2);
}
?>