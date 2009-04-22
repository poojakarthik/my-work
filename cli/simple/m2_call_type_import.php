<?php

// Framework
require_once("../../flex.require.php");

$arrRecordTypeTranslation					= Array();
$arrRecordTypeTranslation['code']			= NULL;
$arrRecordTypeTranslation['carrier_id']		= CARRIER_M2;
$arrRecordTypeTranslation['carrier_code']	= NULL;
$arrRecordTypeTranslation['description']	= NULL;
$insRecordTypeTranslation	= new StatementInsert("cdr_call_group_translation", $arrDestinationTranslation);
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
	$arrRecordTypeTranslation['carrier_id']		= CARRIER_M2;
	$arrRecordTypeTranslation['carrier_code']	= trim($arrLine[0]);
	$arrRecordTypeTranslation['description']	= trim($arrLine[1]);
	$arrRecordTypeTranslation['code']			= trim($arrLine[2]);
	
	// Insert into the Database
	if ($insRecordTypeTranslation->Execute($arrRecordTypeTranslation))
	{
		// Success
		CliEcho("Inserted '{$arrRecordTypeTranslation['description']}'!");
	}
	else
	{
		// DB Error
		throw new Exception($insRecordTypeTranslation->Error());
	}
}

// Cleanup
fclose($resInputFile);
?>