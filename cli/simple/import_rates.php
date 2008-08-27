<?php

// Framework
require_once("../../flex.require.php");

CliEcho("\n[ IMPORT RATES ]\n");

// Command line arguments
$strFilePath	= trim($argv[1]);
if (!file_exists($strFilePath))
{
	CliEcho("USAGE: 'php import_rates.php <FilePath>'\n");
	CliEcho("\t<FilePath>\t: The path to the Rate CSV file to import");
	CliEcho();
	
	throw new Exception("'{$strFilePath}' -- The Path provided does not exist!");
}

// Open the File
CliEcho("Opening File '{$strFilePath}'...");
$ptrFile	= fopen($strFilePath, 'r');
if ($ptrFile)
{
	CliEcho("Parsing...");
	
	$selRateGroup	= new StatementSelect("RateGroup", "*", "Name = <Name>");
	$selRate		= new StatementSelect("Rate", "*", "Name = <Name>");
	
	$arrRateGroup				= Array();
	$arrRateGroup['**Rates']	= Array();
	
	$intLine		= 0;
	while ($arrLine = fgetcsv($ptrFile))
	{
		$intLine++;
		if (trim($arrLine[1]) && !trim($arrLine[6]))
		{
			// Make sure we ignore Header Rows
			if ((int)$arrLine[0])
			{
				CliEcho("\t * Parsing RateGroup '{$arrLine[1]}'...");
				
				// Parse RateGroup Section
				//$arrRateGroup['Id']			= (int)$arrLine[0];				// Unused
				$arrRateGroup['Name']			= $arrLine[1];
				$arrRateGroup['Description']	= $arrLine[2];
				$arrRateGroup['ServiceType']	= (int)$arrLine[3];
				$arrRateGroup['RecordType']		= (int)$arrLine[4];
				$arrRateGroup['Fleet']			= (int)$arrLine[5];
				$arrRateGroup['Archived']		= 0;
				
				// Does this RateGroup already exist?
				if ($selRateGroup->Execute($arrRateGroup))
				{
					// Yes, do all of the fields match up?
					CliEcho("\t\t [!] RateGroup with name '{$arrRateGroup['Name']}' already exists!  Checking if all fields match...");
					
					$arrCompare	= $selRateGroup->Fetch();
					foreach ($arrRateGroup as $strField=>$mixValue)
					{
						if ($mixValue !== $arrCompare[$mixValue])
						{
							// This field doesn't match!
							throw new Exception("ERROR: Imported {$strField} === '{$mixValue}', but Existing value is '{$arrCompare[$mixValue]}'!");
						}
					}
					
					// We wont insert this RateGroup
					$arrRateGroup['Id']	= $arrCompare['Id'];
					CliEcho("\t\t [+] All fields match -- will use RateGroup with Id {$arrCompare[$mixValue]} instead of inserting");
				}
			}
		}
		elseif (trim($arrLine[6]))
		{
			// Make sure we ignore Header Rows
			if ((int)$arrLine[0])
			{
				CliEcho("\t * Parsing Rate '{$arrLine[4]}'...");
				
				// Parse Rate Section
				$arrRate	= Array();
				$arrRate['ServiceType']		= $arrRateGroup['ServiceType'];
				$arrRate['RecordType']		= $arrRateGroup['RecordType'];
				$arrRate['Fleet']			= $arrRateGroup['Fleet'];
				$arrRate['Archived']		= 0;
				
				//$arrRate['Id']			= (int)$arrLine[0];						// Unused
				//							= $arrLine[1];							// Unused
				$arrRate['Destination']		= (int)$arrLine[2];
				//							= $arrLine[3];							// Unused
				$arrRate['Name']			= $arrLine[4];
				$arrRate['Description']		= $arrLine[5];
				$arrRate['StartTime']		= $arrLine[6];
				$arrRate['EndTime']			= $arrLine[7];
				$arrRate['Monday']			= (int)$arrLine[8];
				$arrRate['Tuesday']			= (int)$arrLine[9];
				$arrRate['Wednesday']		= (int)$arrLine[10];
				$arrRate['Thursday']		= (int)$arrLine[11];
				$arrRate['Friday']			= (int)$arrLine[12];
				$arrRate['Saturday']		= (int)$arrLine[13];
				$arrRate['Sunday']			= (int)$arrLine[14];
				$arrRate['PassThrough']		= (int)$arrLine[15];
				$arrRate['Uncapped']		= (int)$arrLine[16];
				$arrRate['Prorate']			= (int)$arrLine[17];
				$arrRate['StdMinCharge']	= (float)$arrLine[18];
				$arrRate['StdFlagfall']		= (float)$arrLine[19];
				$arrRate['StdUnits']		= (int)$arrLine[20];
				$arrRate['StdRatePerUnit']	= (float)$arrLine[21];
				$arrRate['StdMarkup']		= (float)$arrLine[22];
				$arrRate['StdPercentage']	= (float)$arrLine[23];
				$arrRate['CapUnits']		= (int)$arrLine[24];
				$arrRate['CapCost']			= (float)$arrLine[25];
				$arrRate['CapUsage']		= (int)$arrLine[26];
				$arrRate['CapLimit']		= (float)$arrLine[27];
				$arrRate['ExsFlagfall']		= (float)$arrLine[28];
				$arrRate['ExsUnits']		= (int)$arrLine[29];
				$arrRate['ExsRatePerUnit']	= (float)$arrLine[30];
				$arrRate['ExsMarkup']		= (float)$arrLine[31];
				$arrRate['ExsPercentage']	= (float)$arrLine[32];
					
				// Does this Rate already exist?
				if ($selRate->Execute($arrRate))
				{
					// Yes, do all of the fields match up?
					CliEcho("\t\t [!] Rate with name '{$arrRate['Name']}' already exists!  Checking if all fields match...");
					
					$arrCompare	= $selRate->Fetch();
					foreach ($arrRate as $strField=>$mixValue)
					{
						if ($mixValue !== $arrCompare[$mixValue])
						{
							// This field doesn't match!
							throw new Exception("ERROR: Imported {$strField} === '{$mixValue}', but Existing value is '{$arrCompare[$mixValue]}'!");
						}
					}
					
					// We wont insert this Rate
					$arrRate['Id']	= $arrCompare['Id'];
					CliEcho("\t\t [+] All fields match -- will use Rate with Id {$arrCompare[$mixValue]} instead of inserting");
				}
				elseif ($arrRateGroup['Id'])
				{
					// No, but the RateGroup does!  WTF!?  Impossible to work with.
					throw new Exception("ERROR: RateGroup '{$arrRateGroup['Name']}' exists, but Rate '{$arrRate['Name']}' doesn't!");
				}
				
				$arrRateGroup['**Rates'][]	= $arrRate;
			}
		}
		elseif($arrLine[0] === '' && $arrLine[1] == '')
		{
			// I think we can assume this is a blank line
			continue;
		}
		else
		{
			throw new Exception("Unexpected Row '".implode(',', $arrLine)."'");
		}
	}
	CliEcho();
	
	// Import Rate Data into Flex
	$insRate			= new StatementInsert("Rate");
	$insRateGroup		= new StatementInsert("RateGroup");
	$insRateGroupRate	= new StatementInsert("RateGroupRate");
	
	// Insert RateGroup & Rates
	if (!$arrRateGroup['Id'])
	{
		// Insert RateGroup
		CliEcho("Inserting RateGroup...");
		if (/*($arrRateGroup['Id'] = $insRateGroup->Execute($arrRateGroup)) === */FALSE)
		{
			throw new Exception("ERROR: ".$insRateGroup->Error());
		}
		
		// Insert Rates
		CliEcho("Inserting Rates...");
		foreach ($arrRateGroup['**Rates'] as &$arrRate)
		{
			if (/*($arrRate['Id'] = $insRate->Execute($arrRate)) === */FALSE)
			{
				throw new Exception("ERROR: ".$insRate->Error());
			}
		}
	}
	
	// Insert RateGroupRates
	CliEcho("Inserting RateGroupRates...");
	foreach ($arrRateGroup['**Rates'] as $arrRate)
	{
		$arrRateGroupRate	= Array();
		$arrRateGroupRate['RateGroup']	= $arrRateGroup['Id'];
		$arrRateGroupRate['Rate']		= $arrRate['Id'];
		
		if (/*$insRateGroupRate->Execute($arrRateGroupRate) === */FALSE)
		{
			throw new Exception("ERROR: ".$insRateGroupRate->Error());
		}
	}
	CliEcho();
}
else
{
	throw new Exception("'{$strFilePath}' -- There was an error opening the file!");
}

// Exit gracefully
exit(0);
?>