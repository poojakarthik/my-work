<?php

// Framework
require_once("../../flex.require.php");

$insCode	= new StatementInsert("ProvisioningTranslation");

// Open File
$strPath	= "/home/richdavis/Desktop/Unitel Error Codes.csv";
if (!$ptrFile = fopen($strPath, 'r'))
{
	// Error
	die;
}

// Parse File
while (!feof($ptrFile))
{
	if (!$strLine = trim(fgets($ptrFile)))
	{
		continue;
	}
	
	$arrLine	= explode(',', $strLine);
	foreach ($arrLine as $intIndex=>$strValue)
	{
		$arrLine[$intIndex]	= trim(trim(trim($strValue), '"'));
	}
	
	$arrCode['Context']		= PROVISIONING_CONTEXT_REJECT_UNITEL;
	$arrCode['CarrierCode']	= str_pad($arrLine[0], 3, '0', STR_PAD_LEFT);
	$arrCode['flex_code']	= $arrLine[1];
	CliEcho("Code: {$arrCode['CarrierCode']};\tValue: {$arrCode['Description']}...\t\t\t", FALSE);
	if ($insCode->Execute($arrCode) === FALSE)
	{
		CliEcho("[ FAILED ]");
	}
	else
	{
		CliEcho("[   OK   ]");
	}
}
CliEcho("");


?>