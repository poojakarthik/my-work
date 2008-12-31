<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

$qryQuery	= new Query();

$arrWordFilter	= array('to', '-');

// Cache Flex Destination Codes
$resDestinations	= $qryQuery->Execute("SELECT * FROM Destination WHERE Context = 1");
if ($resDestinations === false)
{
	throw new Exception($qryQuery->Error());
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
	throw new Exception($qryQuery->Error());
}

while ($arrDestinationTranslation = $resDestinationTranslation->fetch_assoc())
{
	CliEcho("\t[+] ".GetConstantDescription($arrDestinationTranslation['Carrier'], 'Carrier').": ({$arrDestinationTranslation['CarrierCode']}) {$arrDestinationTranslation['Description']}...", false);
	
	// Filter out any useless words that will just give us junk matches
	$arrCarrierDestination	= explode(' ', $arrWordFilter);
	foreach ($arrCarrierDestination as $mixIndex=>$strWord)
	{
		if (in_array($strWord, $arrWordFilter))
		{
			unset($arrCarrierDestination[$mixIndex]);
		}
	}
	
	// See if there are any Flex Destinations that have a similar description
	$intTotalWords	= count($arrCarrierDestination);
	foreach ($arrDestinations as $intCode=>$arrDestination)
	{
		$intWordCount	= 0;
		foreach ($arrCarrierDestination as $strWord)
		{
			$intWordCount	+= (stripos($arrDestination['Description'], $strWord) !== false) ? 1 : 0;
		}
		
		if ($intWordCount)
		{
			CliEcho(" {$arrDestination['Description']}({$intWordCount}/{$intTotalWords})", false);
		}
	}
	CliEcho();
}

?>