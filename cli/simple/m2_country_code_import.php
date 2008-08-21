<?php

// Framework
require_once("../../flex.require.php");

$arrDestinationTranslation					= Array();
$arrDestinationTranslation['Code']			= NULL;
$arrDestinationTranslation['Carrier']		= CARRIER_M2;
$arrDestinationTranslation['CarrierCode']	= NULL;
$arrDestinationTranslation['Description']	= NULL;
$insDestinationTranslation	= new StatementInsert("DestinationTranslation", $arrDestinationTranslation);
$selDestination				= new StatementSelect("Destination", "Code", "Context = 1 AND Description = <Destination>");

$strInFile		= "/home/rdavis/m2_country_code_conversion_import.csv";

$resInputFile	= fopen($strInFile, 'r');

// Parse the Input File
while ($arrLine = fgetcsv($resInputFile))
{
	if (substr($arrLine[0], 0, 1) !== '/')
	{
		// Non-Data Row
		continue;
	}
	
	$arrDestinationTranslation					= Array();
	$arrDestinationTranslation['Carrier']		= CARRIER_M2;
	$arrDestinationTranslation['CarrierCode']	= trim($arrLine[0]);
	$arrDestinationTranslation['Description']	= trim($arrLine[1]);
	$arrDestinationTranslation['Destination']	= trim($arrLine[2]);
	
	// Get Destination Code
	if ($selDestination->Execute($arrDestinationTranslation))
	{
		$arrDestination	= $selDestination->Fetch();
		
		// Insert into the Database
		$arrDestinationTranslation['Code']	= $arrDestination['Code'];
		if ($insDestinationTranslation->Execute($arrDestinationTranslation))
		{
			// Success
			CliEcho("Inserted '{$arrDestinationTranslation['Description']}'!");
		}
		else
		{
			// DB Error
			throw new Exception($selDestination->Error());
		}
	}
	elseif ($selDestination->Error())
	{
		// DB Error
		throw new Exception($selDestination->Error());
	}
	else
	{
		throw new Exception("'{$arrDestinationTranslation['Destination']} is not a valid Flex Destination!'");
	}
}

// Cleanup
fclose($resInputFile);
?>