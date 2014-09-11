<?php

require_once("../../flex.require.php");

// Open File
if (!$ptrFile	= fopen('/home/richdavis/Desktop/CDR Fix.csv', 'r'))
{
	// Failed
	CliEcho("Could not open file!\n");
	die;
}

CliEcho("Parsing Headers...");

// Parse Header for Fields to Update
$arrCols	= Array();
$arrHeader	= fgetcsv($ptrFile, 0, ';');
foreach ($arrHeader as $strField)
{
	$arrCols[$strField]	= NULL;
}
$ubiCDR	= new StatementUpdateById("CDR", $arrCols);
$selCDR	= new StatementSelect("CDR", "*", "Id = <Id>");

CliEcho("Parsing and Updating CDRs...");

// Update each CDR
$i		= 0;
while (!feof($ptrFile))
{
	// Get Line
	$arrRow	= fgetcsv($ptrFile, 0, ';');
	
	// Parse data
	$arrCDR	= Array();
	foreach ($arrRow as $intCol=>$mixField)
	{
		$arrCDR[$arrHeader[$intCol]]	= $mixField;
	}
	
	// Find CDR for comparison
	//$selCDR->Execute($arrCDR);
	//$arrOldCDR	= $selCDR->Fetch();
	
	CliEcho("$i...");
	/*
	if ($arrOldCDR['Account'] != $arrCDR['Account'])
	{
		Debug($arrOldCDR);
		Debug($arrCDR);
	}*/
	
	// Update CDR
	$ubiCDR->Execute($arrCDR);
	$i++;
}

CliEcho("Updated $i CDRs!\n");

fclose($ptrFile);
?>