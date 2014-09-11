<?php

// Framework
require_once("../../flex.require.php");

// Statements
$arrInsert	= Array();
$arrInsert['Context']		= NULL;
$arrInsert['CarrierCode']	= NULL;
$arrInsert['flex_code']	= NULL;
$insEPID	= new StatementInsert("ProvisioningTranslation", $arrInsert);

$strFile	= "/home/richdavis/Desktop/EPID_Import.csv";
$ptrFile	= fopen($strFile, 'r');

// Parse File
$intLine	= 0;
while (!feof($ptrFile))
{
	$intLine++;
	if (!$strLine	= trim(trim(trim(fgets($ptrFile)), '",')))
	{
		// Skip empty lines
		continue;
	}
	//$strLine	= "1037";
	$arrMatches	= Array();
	if ($intLine % 2)
	{
		$arrMatches	= Array();
		preg_match_all("/^[\ ]*([A-Z\d]{2}\ |)[\ ]*([A-Z\d]{3}\ |)[\ ]*(\d{0,3}|)[\ ]*(\d{2,4})[\ ]*$/misU", $strLine, $arrMatches);
		
		if (!$arrMatches[4][0])
		{
			Debug($arrMatches);
			CliEcho($strLine);
			die;
		}
		
		$arrEPID	= Array('Context' => CARRIER_TRANSLATION_CONTEXT_EPID, 'CarrierCode' => (string)(int)$arrMatches[4][0]);
	}
	else
	{
		preg_match_all("/^[\ ]*([\-\&\/\,\.\-\w\d\ \(\)]+)[\ ]*([A-Za-z]{2,3})[\ ]*$/misU", $strLine, $arrMatches);
		
		if (!$arrMatches[1][0])
		{
			Debug($arrMatches);
			CliEcho($strLine);
		}
		
		$arrEPID['flex_code']	= trim($arrMatches[1][0]);
		
		CliEcho(" + '{$arrEPID['Description']}': {$arrEPID['CarrierCode']}");
		Debug($arrEPID);
		die;
		$insEPID->Execute($arrEPID);
	}
}

die;

?>