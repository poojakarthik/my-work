<?php

// Framework
require_once("../../flex.require.php");

$selFixableBalances	= new StatementSelect(	"Invoice",
											"Account, Id, Total + Tax AS TotalPlusTax, TotalOwing, Balance, Balance / 2 AS HalfBalance",
											"Balance > Total + Tax + 1 AND Total > 0");

$arrCols			= Array();
$arrCols['Balance']	= NULL;
$ubiInvoice			= new StatementUpdateById("Invoice", $arrCols);

CliEcho('');

$selFixableBalances->Execute();
while ($arrInvoice = $selFixableBalances->Fetch())
{
	CliEcho(" + {$arrInvoice['Id']}.Balance = {$arrInvoice['Balance']}; Total + Tax = {$arrInvoice['TotalPlusTax']}; Half Balance = {$arrInvoice['HalfBalance']}\t\t", FALSE);
	
	if (round($arrInvoice['TotalPlusTax'], 2) == round($arrInvoice['HalfBalance'], 2))
	{
		$arrInvoice['Balance']	= round($arrInvoice['HalfBalance'], 2);
		
		CliEcho("[   OK   ]");
		if ($ubiInvice->Execute($arrInvoice) === FALSE)
		{
			// FAIL
			Debug($ubiInvoice->Error());
		}
	}
	else
	{
		CliEcho("[  SKIP  ]");
	}
}



CliEcho('');



?>