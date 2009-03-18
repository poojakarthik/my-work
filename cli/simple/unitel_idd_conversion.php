<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

Log::getLog()->log("UNITEL RATE MATCHER");

$arrWordFilter	= array('to', 'and', '&', '-', 'is', 'offnet', 'onnet', 'off-net', 'on-net', 'off', 'on', 'net', 'telstra', 'mobile');

$strInFile		= "/home/rdavis/unitel_rate_ids.csv";
$strOutFile		= "/home/rdavis/unitel_rate_ids_suggestions.csv";

$resInputFile	= fopen($strInFile, 'r');
$resOutputFile	= fopen($strOutFile, 'w');

$qryQuery	= new Query();

Log::getLog()->log("Getting list of Flex IDD Destinations...");

// Get Destinations
$resDestinations	= $qryQuery->Execute("SELECT * FROM Destination WHERE Context = 1");
if ($resDestinations === false)
{
	throw new Exception($qryQuery->Error());
}
$arrDestinations	= array();
while ($arrDestination = $resDestinations->fetch_assoc())
{
	//Log::getLog()->log(print_r($arrDestination, true));
	
	$arrDestination['fixed_description']		= trim(strtolower($arrDestination['Description']));
	
	//Log::getLog()->log(print_r($arrDestination, true));
	//die;
	$arrDestinations[$arrDestination['Code']]	= $arrDestination;
}

Log::getLog()->log("Processing Input File '{$strInFile}'...");

// Parse the Input File
$intLine	= 0;
while ($arrLine = fgetcsv($resInputFile))
{
	$intUnitelRateId	= (int)$arrLine[0];
	if (!$intUnitelRateId)
	{
		// Non-Data Row
		continue;
	}
	
	Log::getLog()->log("[ ] Rate Id {$intUnitelRateId}");
	
	// Check for an exact match
	if ($mixFlexCode = array_search(trim(strtolower($arrLine[1])), $arrDestinations))
	{
		// Found an exact match
		$arrLine[]		= $mixFlexCode.':'.$arrDestinations[$mixFlexCode]['Description'];
		Log::getLog()->log("\t+ ".$mixFlexCode.':'.$arrDestinations[$mixFlexCode]['Description']);
	}
	else
	{
		// Filter out any useless words that will just give us junk matches
		$arrKeywords	= explode(' ', $arrLine[1]);
		
		// Attempt to match
		$arrMatches	= array();
		foreach ($arrDestinations as $mixFlexCode=>$arrFlexDestination)
		{
			foreach ($arrKeywords as $mixIndex=>$strWord)
			{
				if (!in_array(strtolower($strWord), $arrWordFilter) && stripos($arrDestination['fixed_description'], $strWord) >= 0)
				{
					$arrMatches[$mixFlexCode]	= (array_key_exists($mixFlexCode, $arrMatches)) ? $arrMatches[$mixFlexCode] + 1 : 1;
				}
			}
		}
		
		// Order Matches by match count, then add to output file
		sort($arrMatches, SORT_NUMERIC);
		foreach ($arrMatches as $mixFlexCode=>$intMatchCount)
		{
			$arrLine[]		= $mixFlexCode.':'.$arrDestinations[$mixFlexCode]['Description'];
			Log::getLog()->log("\t- ".$mixFlexCode.':'.$arrDestinations[$mixFlexCode]['Description']);
		}
	}
	
	// Write the modified line to the Output File
	fwrite($resOutputFile, implode(',', $arrLine)."\n");
}

// Cleanup
fclose($resInputFile);
fclose($resOutputFile);
?>