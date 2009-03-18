<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

Log::getLog()->log("UNITEL RATE MATCHER");

$arrWordFilter	= array('to', 'and', '&', '-', 'is', 'offnet', 'onnet', 'off-net', 'on-net', 'off', 'on', 'net', 'telstra', 'mobile');

$intCommonKeywordMinimum	= 5;
$arrCommonKeywords			= array();

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
$intLine			= 0;
while ($arrLine = fgetcsv($resInputFile))
{
	$intUnitelRateId	= (int)$arrLine[0];
	if (!$intUnitelRateId)
	{
		// Non-Data Row
		continue;
	}
	
	Log::getLog()->log("[ ] Rate Id {$intUnitelRateId} ('{$arrLine[1]}')");
	
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
		Log::getLog()->log(print_r($arrKeywords, true));
		
		// Attempt to match
		$arrMatches	= array();
		foreach ($arrDestinations as $mixFlexCode=>$arrFlexDestination)
		{
			foreach ($arrKeywords as $mixIndex=>$strWord)
			{
				$intMatchIndex	= stripos($arrFlexDestination['fixed_description'], $strWord);
				if (!in_array(strtolower($strWord), $arrWordFilter) && $intMatchIndex !== false && $intMatchIndex >= 0)
				{
					// Match Found
					$strMatchString	= substr($arrFlexDestination['fixed_description'], 0, $intMatchIndex).'['.substr($arrFlexDestination['fixed_description'], $intMatchIndex, strlen($strWord)).']'.substr($arrFlexDestination['fixed_description'], $intMatchIndex+strlen($strWord));
					Log::getLog()->log("\t- Match found on Destination with code {$mixFlexCode}: '{$strMatchString}'");
					$arrCommonKeywords[$strWord]	= (array_key_exists($strWord, $arrCommonKeywords)) ? $arrCommonKeywords[$strWord] + 1 : 1;
					$arrMatches[$mixFlexCode]		= (array_key_exists($mixFlexCode, $arrMatches)) ? $arrMatches[$mixFlexCode] + 1 : 1;
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
		
		if (count($arrMatches))
		{		
			die;
		}
	}
	
	// Write the modified line to the Output File
	fwrite($resOutputFile, implode(',', $arrLine)."\n");
}

Log::getLog()->log("\nCommon Keywords:");
			
// Sort the common keywords
sort($arrCommonKeywords, SORT_NUMERIC);
foreach ($arrCommonKeywords as $strKeyword=>$intCount)
{
	if ($intCount >= $intCommonKeywordMinimum)
	{
		$strOutput	= str_pad(substr($strKeyword, 0, 40), 40, ' ', STR_PAD_RIGHT);
		Log::getLog()->log(("\t[+] ".$strOutput." : {$intCount} instances"));
	}
}

// Cleanup
fclose($resInputFile);
fclose($resOutputFile);
?>