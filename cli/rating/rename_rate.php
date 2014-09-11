#!/usr/bin/php
<?php

// Describe Rates

// require stuff
require_once("include.php");

// init query
$sqlQuery = new Query();

// Get Rates
$strQuery = "SELECT * FROM Rate WHERE Id < 155 ORDER BY Id";
$sqlRate = $sqlQuery->Execute($strQuery);

// Get RateGroups
$strQuery = "SELECT * FROM RateGroup";
$sqlRateGroup = $sqlQuery->Execute($strQuery);

// Get RecordTypes
$arrRecordTypes = Array();
$strQuery = "SELECT * FROM RecordType";
$sqlRecordType = $sqlQuery->Execute($strQuery);
while($arrRecordType = $sqlRecordType->fetch_assoc())
{
	$arrRecordTypes[$arrRecordType['Id']] = $arrRecordType['Code'];
}


while($arrRate = $sqlRate->fetch_assoc())
{
	$intRecordType 		= $arrRate['RecordType'];
	$strRecordType 		= $arrRecordTypes[$intRecordType];
	$strName 			= $arrRate['Name'];
	$arrDescription 	= Array();
	if ($arrRate['StdUnits'])
	{
		// rate
		$c = sprintf("%02s",round($arrRate['StdRatePerUnit'] * 6000/$arrRate['StdUnits']));
		$f = sprintf("%02s",round($arrRate['StdFlagfall'] * 100));
		$s = sprintf("%02s",$arrRate['StdUnits']);
		$m = sprintf("%02s",$arrRate['StdMinCharge'] * 100);
		$strRate = "{$c}c-{$f}f-{$s}s-{$m}m";
		
		// description
		if ((int)$c)
		{
			$arrDescription[] = (int)$c."c/min";
		}
		if ((int)$f)
		{
			$arrDescription[] = (int)$f."c/ff";
		}
		$arrDescription[] = (int)$s."sec billing";
		if ((int)$m)
		{
			$arrDescription[] = (int)$m."c min. charge";
		}
		
		// cap
		if ($arrRate['CapCost'] && $arrRate['CapUsage'])
		{
			// rate
			$cc = sprintf("%02s",$arrRate['CapCost'] * 100);
			$cu = sprintf("%02s",$arrRate['CapUsage'] / 60);
			$strRate .= ":{$cc}c{$cu}m";
			
			// description
			$arrDescription[] = (int)$cc."c Cap for ".(int)$cu."min";
		}
	}
	$strDescription = implode(', ', $arrDescription);
	echo "\"$strName\";\"$strRecordType-$strRate\";\"$strDescription\"\n";
}


?>
