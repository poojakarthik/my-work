<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

$strFileName	= $argv[1];
if (!is_file($strFileName))
{
	throw new Exception("The path '{$strFileName}' does not exist!");
}

$resInputFile	= fopen($strFileName, 'r');
if (!$resInputFile)
{
	throw new Exception("Unable to open '{$strFileName}' for reading!");
}

try
{
	DataAccess::getDataAccess()->TransactionStart();
	
	$selDestinationTranslation	= new StatementSelect("DestinationTranslation", "*", "CarrierCode = <CarrierCode> AND Carrier = <Carrier> AND Description = <Description>");
	$selDestination				= new StatementSelect("Destination", "Id", "Code = <Code> AND Description = <Description>");
	$ubiDestinationTranslation	= new StatementUpdateById("DestinationTranslation", array('Code'=>null));
	
	// Parse through each line
	$intLine	= 0;
	while (!feof($resInputFile))
	{
		$intLine++;
		$arrLine	= fgetcsv($resInputFile);
		
		CliEcho("\t[+] Record {$intLine}...");
		
		$arrDestinationTranslation	= array();
		$arrDestination				= array();
		
		// We need at least 5 columns
		
		// Column 1 is the Carrier
		$arrDestinationTranslation['Carrier']		= (int)$arrLine[0];
		// Column 2 is the Carrier's Destination Code
		$arrDestinationTranslation['CarrierCode']	= $arrLine[1];
		// Column 3 is the Carrier's Destination Description
		$arrDestinationTranslation['Description']	= $arrLine[2];
		// Column 4 is the Flex Destination Code
		$arrDestinationTranslation['Code']			= (int)$arrLine[3];
		$arrDestination['Code']						= (int)$arrLine[3];
		// Column 5 is the Flex Destination Description
		$arrDestination['Description']				= $arrLine[4];
		
		if (!$arrDestination['Code'] && !$arrDestination['Description'])
		{
			CliEcho("\t\tNo Flex Destination defined");
			continue;
		}
		
		if (!($arrDestinationTranslation['Carrier'] > 0))
		{
			throw new Exception("Record {$intLine}'s Carrier is invalid ('{$arrLine[0]}')");
		}
		if (!$arrDestinationTranslation['CarrierCode'])
		{
			throw new Exception("Record {$intLine}'s Carrier Code is invalid ('{$arrLine[1]}')");
		}
		if (!$arrDestinationTranslation['Description'])
		{
			throw new Exception("Record {$intLine}'s Carrier Description is invalid ('{$arrLine[2]}')");
		}
		if (!($arrDestinationTranslation['Code'] > 0))
		{
			throw new Exception("Record {$intLine}'s Flex Code is invalid ('{$arrLine[3]}')");
		}
		if (!$arrDestination['Description'])
		{
			throw new Exception("Record {$intLine}'s Flex Description is invalid ('{$arrLine[4]}')");
		}
		
		CliEcho("\t\t".GetConstantDescription($arrDestinationTranslation['Carrier'], 'Carrier').": '{$arrDestinationTranslation['Description']} ({$arrDestinationTranslation['CarrierCode']})' ==> '{$arrDestination['Description']}' ({$arrDestination['Code']})");
		
		// Get the DestinationTranslation details
		if ($selDestinationTranslation->Execute($arrDestinationTranslation))
		{
			$arrDestinationTranslationFlex		= $selDestinationTranslation->Fetch();
			$arrDestinationTranslation['Id']	= $arrDestinationTranslationFlex['Id'];
			
			// Ensure that the Flex Destination is valid
			if (!$selDestination->Execute($arrDestination))
			{
				if ($selDestination->Error())
				{
					throw new Exception($selDestination->Error());
				}
				else
				{
					throw new Exception("The Flex Destination '{$arrDestination['Description']}' ({$arrDestination['Code']}) doesn't exist!");
				}
			}
			else
			{
				// Everything appears to be valid, update!
				if ($ubiDestinationTranslation->Execute($arrDestinationTranslation) === false)
				{
					throw new Exception($ubiDestinationTranslation->Error());
				}
			}
		}
		elseif ($selDestinationTranslation->Error())
		{
			throw new Exception($selDestinationTranslation->Error());
		}
		else
		{
			throw new Exception("The ".GetConstantDescription($arrDestinationTranslation['Carrier'], 'Carrier')." Destination '{$arrDestinationTranslation['Description']} ({$arrDestinationTranslation['CarrierCode']})' doesn't exist!");
		}
	}
	
	throw new Exception("TEST MODE");
	
	DataAccess::getDataAccess()->TransactionCommit();
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}
?>