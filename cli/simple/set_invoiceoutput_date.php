<?php

require_once("../../lib/framework/require.php");

// Update DB
$arrCols = Array();
$arrCols['Data']	= NULL;
$ubiInvoiceOutput	= new StatementUpdateById("InvoiceOutput", $arrCols);
$selInvoiceOutput	= new StatementSelect("InvoiceOutput", "*");

$selInvoiceOutput->Execute();
while ($arrOutput = $selInvoiceOutput->Fetch())
{
	CliEcho(" + {$arrOutput['Account']}... ", FALSE);
	
	$mixResult	= 0;
	while ($mixResult !== FALSE)
	{
		if ($mixResult = strpos($arrOutput['Data'], '15 Mar 2008', $mixResult+1))
		{
			CliEcho($mixResult." ", FALSE);
		}
	}
	
	$arrOutput['Data']	= str_replace('15 Mar 2008', '18 Mar 2008', $arrOutput['Data']);
	CliEcho('');
	$ubiInvoiceOutput->Execute($arrOutput);
}

CliEcho("Editing file...");

// Update File
$ptrInputFile	= fopen("/home/vixen_bill_output/2008-03-03.vbf", 'r');
$ptrOutputFile	= fopen("/home/vixen_bill_output/2008-03-03.out.vbf", 'w');

while (!feof($ptrInputFile))
{
	$strLine	= fgets($ptrInputFile);
	$strLine	= str_replace('15 Mar 2008', '18 Mar 2008', $strLine);
	fwrite($ptrOutputFile, $strLine);
}

fclose($ptrInputFile);
fclose($ptrOutputFile);

?>