<?php

// Framework
require_once("../../flex.require.php");

$arrRecordTypeTranslation					= Array();
$arrRecordTypeTranslation['Code']			= NULL;
$arrRecordTypeTranslation['Carrier']		= CARRIER_M2;
$arrRecordTypeTranslation['CarrierCode']	= NULL;
$arrRecordTypeTranslation['Description']	= NULL;
$insRecordTypeTranslation	= new StatementInsert("RecordTypeTranslation", $arrDestinationTranslation);
$selRecordType				= new StatementSelect("RecordType", "Code", "ServiceType = 101 AND Code = <Code>");

$strInFile		= "/home/rdavis/m2_call_type_import.csv";

$resInputFile	= fopen($strInFile, 'r');

// Parse the Input File
while ($arrLine = fgetcsv($resInputFile))
{
	if (strtolower(trim($arrLine[0])) === 'tariff')
	{
		// Non-Data Row
		continue;
	}
	
	$arrRecordTypeTranslation					= Array();
	$arrRecordTypeTranslation['Carrier']		= CARRIER_M2;
	$arrRecordTypeTranslation['CarrierCode']	= trim($arrLine[0]);
	$arrRecordTypeTranslation['Description']	= trim($arrLine[1]);
	$arrRecordTypeTranslation['Destination']	= trim($arrLine[2]);
	
	// Get Destination Code
	if ($selRecordType->Execute($arrRecordTypeTranslation))
	{
		$arrDestination	= $selRecordType->Fetch();
		
		// Insert into the Database
		$arrRecordTypeTranslation['Code']	= $arrDestination['Code'];
		if ($insRecordTypeTranslation->Execute($arrRecordTypeTranslation))
		{
			// Success
			CliEcho("Inserted '{$arrRecordTypeTranslation['Description']}'!");
		}
		else
		{
			// DB Error
			throw new Exception($insRecordTypeTranslation->Error());
		}
	}
	elseif ($selRecordType->Error())
	{
		// DB Error
		throw new Exception($selRecordType->Error());
	}
	else
	{
		// We don't handle this Call Type
		CliEcho("SKIPPING '{$arrRecordTypeTranslation['Description']}'!");
	}
}

// Cleanup
fclose($resInputFile);
?>