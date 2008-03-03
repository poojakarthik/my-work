<?php

// Framework
require_once("../../flex.require.php");

// Statements
$arrCols				= Array();
$arrCols['Description']	= NULL;
$ubiProRata	= new StatementUpdateById("Charge", $arrCols);
$selProRata	= new StatementSelect("Charge", "*", "ChargeType LIKE 'PCP%' AND Status = 102");


// Get all Temp Invoice Pro Rata Adjustments
$selProRata->Execute();
while ($arrCharge = $selProRata->Fetch())
{
	CliEcho("{$arrCharge['Account']}; {$arrCharge['ChargeType']}; {$arrCharge['Description']}", FALSE);
	
	// Fix Date in Description
	$arrCharge['Description']	= substr($arrCharge['Description'], 0, -10)."29/02/2008";
	
	// Make 'arrear' start with a capital
	$arrBroken					= explode('Arrear', $arrCharge['Description']);
	
	if (count($arrBroken) == 2)
	{
		$arrCharge['Description']	= $arrBroken[0].'Arrears'.$arrBroken[1];
	}
	CliEcho("; {$arrCharge['Description']}");
	
	// Update Charge
	/*if ($ubiProRata->Execute($arrCharge) === FALSE)
	{
		Debug($ubiProRata->Error());
		die;
	}*/
}




?>