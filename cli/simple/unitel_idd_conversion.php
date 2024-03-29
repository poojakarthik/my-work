<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

Log::getLog()->log("UNITEL RATE MATCHER");

$arrWordFilter					= array('to', 'and', '&', '-', 'is');
$arrWordFilterNeedRegularMatch	= array('offnet', 'onnet', 'off-net', 'on-net', 'off', 'on', 'net', 'telstra', 'mobile');

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
	throw new Exception_Database($qryQuery->Error());
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
$intPerfectMatches	= 0;
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
	$strMatchFlexCode	= null;
	foreach ($arrDestinations as $mixFlexCode=>$arrFlexDestination)
	{
		if ($arrFlexDestination['fixed_description'] === trim(strtolower($arrLine[1])))
		{
			$strMatchFlexCode	= $mixFlexCode;
		}
	}
	
	if ($strMatchFlexCode !== null)
	{
		// Found an exact match
		Log::getLog()->log("\t+ Perfect Match found on Destination with code ".$strMatchFlexCode.': '.$arrDestinations[$strMatchFlexCode]['Description']);
		$arrLine[]		= $strMatchFlexCode.':'.$arrDestinations[$strMatchFlexCode]['Description'];
		$intPerfectMatches++;
	}
	else
	{
		// Filter out any useless words that will just give us junk matches
		$arrKeywords	= explode(' ', $arrLine[1]);
		//Log::getLog()->log(print_r($arrKeywords, true));
		
		// Attempt to match
		$arrMatches			= array();
		$arrPartialMatches	= array();
		foreach ($arrDestinations as $mixFlexCode=>$arrFlexDestination)
		{
			foreach ($arrKeywords as $mixIndex=>$strWord)
			{
				$arrDestinationDescription	= explode(' ', $arrFlexDestination['fixed_description']);
				if (!in_array(strtolower($strWord), $arrWordFilter) && in_array(strtolower($strWord), $arrDestinationDescription))
				{
					// Match Found
					$arrCommonKeywords[$strWord]	= (array_key_exists($strWord, $arrCommonKeywords)) ? $arrCommonKeywords[$strWord] + 1 : 1;
					if (in_array(strtolower($strWord), $arrWordFilterNeedRegularMatch))
					{
						$strMatchType						= 'PARTIAL MATCH';
						$arrPartialMatches[$mixFlexCode]	= (array_key_exists($mixFlexCode, $arrMatches)) ? $arrMatches[$mixFlexCode] + 1 : 1;
					}
					else
					{
						$strMatchType						= 'SOLID MATCH';
						$arrMatches[$mixFlexCode]			= (array_key_exists($mixFlexCode, $arrMatches)) ? $arrMatches[$mixFlexCode] + 1 : 1;
					}
					
					$intMatchIndex	= stripos($arrFlexDestination['fixed_description'], $strWord);
					$strMatchString	= substr($arrFlexDestination['fixed_description'], 0, $intMatchIndex).'['.substr($arrFlexDestination['fixed_description'], $intMatchIndex, strlen($strWord)).']'.substr($arrFlexDestination['fixed_description'], $intMatchIndex+strlen($strWord));
					Log::getLog()->log("\t- {$strMatchType} found on Destination with code {$mixFlexCode}: '{$strMatchString}'");
				}
			}
			
			// Make sure that we have at least one solid match, and not just partial matches
			if (array_key_exists($mixFlexCode, $arrMatches) && array_key_exists($mixFlexCode, $arrPartialMatches))
			{
				// There is at least one solid match, so add the partial matches to the match count
				$arrMatches[$mixFlexCode]	+= $arrPartialMatches[$mixFlexCode];
			}
		}
		
		// Order Matches by match count, then add to output file
		arsort($arrMatches, SORT_NUMERIC);
		foreach ($arrMatches as $mixFlexCode=>$intMatchCount)
		{
			$arrLine[]		= $mixFlexCode.':'.$arrDestinations[$mixFlexCode]['Description'];
			Log::getLog()->log("\t- (Matches: {$intMatchCount})".$mixFlexCode.':'.$arrDestinations[$mixFlexCode]['Description']);
		}
		
		if (!count($arrMatches))
		{		
			Log::getLog()->log("\t! No Matches Found!");
		}
	}
	
	// Write the modified line to the Output File
	fwrite($resOutputFile, '"'.implode('","', $arrLine).'"'."\n");
}

Log::getLog()->log("\nCommon Keywords:");

// Sort the common keywords
asort($arrCommonKeywords, SORT_NUMERIC);
foreach ($arrCommonKeywords as $strKeyword=>$intCount)
{
	if ($intCount >= $intCommonKeywordMinimum)
	{
		$strOutput	= str_pad(substr($strKeyword, 0, 40), 40, ' ', STR_PAD_RIGHT);
		Log::getLog()->log(("\t[+] ".$strOutput." : {$intCount} instances"));
	}
}

Log::getLog()->log("\nExact Match Count: {$intPerfectMatches}");

// Cleanup
fclose($resInputFile);
fclose($resOutputFile);
exit(0);


?>