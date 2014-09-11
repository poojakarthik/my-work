<?php

// Framework
require_once("../../flex.require.php");

// Statements
$arrCols				= Array();
$arrCols['Description']	= NULL;
$ubiAdvance	= new StatementUpdateById("Charge", $arrCols);
$selAdvance	= new StatementSelect("Charge", "*", "ChargeType LIKE 'PCA%' AND Status = 102");


// Get all Temp Invoice Charge-in-Advance Adjustments
$selAdvance->Execute();
while ($arrCharge = $selAdvance->Fetch())
{
	CliEcho("{$arrCharge['Account']}; {$arrCharge['ChargeType']}; {$arrCharge['Description']}", FALSE);
	
	// Fix Date in Description
	$arrCharge['Description']	= substr($arrCharge['Description'], 0, -10)."29/03/2008";
	
	CliEcho("; {$arrCharge['Description']}");
	
	// Update Charge
	/*if ($ubiAdvance->Execute($arrCharge) === FALSE)
	{
		Debug($ubiAdvance->Error());
		die;
	}*/
}




?>