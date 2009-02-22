<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

$arrIntervals	= array	(
							'y'	=> 'Year',
							'm'	=> 'Month',
							'd'	=> 'Day',
							'h'	=> 'Hour',
							'i'	=> 'Minute',
							's'	=> 'Second'
						);

$strEarlierDate	= $argv[1];
$strLaterDate	= $argv[2];
$strInterval	= strtolower($argv[3]);

switch (strtolower($argv[4]))
{
	case 'ceil':
		$bolCeil	= true;
		break;
		
	case 'floor':
		$bolCeil	= false;
		break;
		
	case 'round':
	default:
		$bolCeil	= null;
		break;
}

if (!strtotime($strEarlierDate))
{
	throw new Exception("Earlier Date '{$strEarlierDate}' is not a valid UNIX Date");
}
if (!strtotime($strLaterDate))
{
	throw new Exception("Later Date '{$strLaterDate}' is not a valid UNIX Date");
}
if (!in_array($strInterval, array_keys($arrIntervals)))
{
	throw new Exception("Interval '{$strInterval}' is not a valid Date Interval (expected y|m|d|h|i|s)");
}

$intDifference	= Flex_Date::difference($strEarlierDate, $strLaterDate, $strInterval, $bolCeil);

echo "The difference is {$intDifference} ".$arrIntervals[$strInterval].(($intDifference) != 1 ? 's' : '')."\n";

?>