<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

$qryQuery	= new Query();

$arrWordFilter	= array('to', 'and', '&', '-', 'is', 'offnet', 'onnet', 'off-net', 'on-net', 'off', 'on', 'net', 'telstra', 'mobile');

$resOutputFile	= fopen(FILES_BASE_PATH."null_destinations.csv", 'w');
if (!$resOutputFile)
{
	exit(1);
}

// Cache Flex Destination Codes
$resDestinations	= $qryQuery->Execute("SELECT * FROM Destination WHERE Context = 1");
if ($resDestinations === false)
{
	throw new Exception_Database($qryQuery->Error());
}
$arrDestinations	= array();
while ($arrDestination = $resDestinations->fetch_assoc())
{
	$arrDestinations[$arrDestination['Code']]	= $arrDestination;
}

// Get all DestinationTranslation entries that have a Code of 0
$resDestinationTranslation	= $qryQuery->Execute("SELECT * FROM DestinationTranslation WHERE Code = 0");
if ($resDestinationTranslation === false)
{
	throw new Exception_Database($qryQuery->Error());
}

while ($arrDestinationTranslation = $resDestinationTranslation->fetch_assoc())
{
	CliEcho("\t[+] ".Carrier::getForId($arrDestinationTranslation['Carrier'])->description.": ({$arrDestinationTranslation['CarrierCode']}) {$arrDestinationTranslation['Description']}...", false);
	
	fwrite($resOutputFile, "\"{$arrDestinationTranslation['Carrier']}\",{$arrDestinationTranslation['CarrierCode']},\"{$arrDestinationTranslation['Description']}\"");
	
	// Filter out any useless words that will just give us junk matches
	$arrCarrierDestination	= explode(' ', $arrDestinationTranslation['Description']);
	foreach ($arrCarrierDestination as $mixIndex=>$strWord)
	{
		if (in_array(strtolower($strWord), $arrWordFilter))
		{
			unset($arrCarrierDestination[$mixIndex]);
		}
	}
	
	// See if there are any Flex Destinations that have a similar description
	$intTotalWords	= count($arrCarrierDestination);
	foreach ($arrDestinations as $intCode=>$arrDestination)
	{
		$intWordCount			= 0;
		$arrDestinationSplit	= explode(' ', $arrDestination['Description']);
		foreach ($arrDestinationSplit as $mixIndex=>$strWord)
		{
			$arrDestinationSplit[$mixIndex]	= strtolower($strWord);
		}
		foreach ($arrCarrierDestination as $strWord)
		{
			$intWordCount	+= (in_array(strtolower($strWord), $arrDestinationSplit)) ? 1 : 0;
		}
		
		if ($intWordCount)
		{
			CliEcho(" {$arrDestination['Description']}({$intWordCount}/{$intTotalWords})", false);
			fwrite($resOutputFile, ",{$intCode},\"{$arrDestination['Description']}\"");
		}
	}
	CliEcho();
	fwrite($resOutputFile, "\n");
}

fclose($resOutputFile);

?>