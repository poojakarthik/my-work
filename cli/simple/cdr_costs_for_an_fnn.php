<?php

// Framework
require_once("../../flex.require.php");

$selCDR			= new StatementSelect("CDR"								, "DATE_FORMAT(StartDatetime, '%d/%m/%Y') AS `Date`, DATE_FORMAT(StartDatetime, '%H:%i:%s') AS `Time`, Destination AS `Called Party`, Description, Units as Duration, Cost", "Service = <Service> AND StartDatetime >= <StartDatetime>", "StartDatetime ASC");
$selCDRInvoiced	= new StatementSelect("flex_telcoblue_cdr.CDRInvoiced"	, "DATE_FORMAT(StartDatetime, '%d/%m/%Y') AS `Date`, DATE_FORMAT(StartDatetime, '%H:%i:%s') AS `Time`, Destination AS `Called Party`, Description, Units as Duration, Cost", "Service = <Service> AND StartDatetime >= <StartDatetime>", "StartDatetime ASC");

/*define('PARAMETER_MODE_FNN'		, 1);
define('PARAMETER_MODE_ACCOUNT'	, 2);

// Determine Parameter Type
switch ($argv[$i])
{
	case '-a':
		$intMode	= PARAMETER_MODE_ACCOUNT;
		break;
	
	case '-s':
		$intMode	= PARAMETER_MODE_FNN;
		break;
		
	default:
		CliEcho("ERROR: The first parameter must be -a (Account) or -s (Service FNN)\n");
		exit(1);
		break;
}

// Parse Parameters
$intMode	= NULL;
for ($i = 2; $i < $argc; $i++)
{
	
}*/

$intService			= 45754;
$strStartDatetime	= '2008-03-13 00:00:00';

if ($selCDR->Execute(Array('Service' => $intService, 'StartDatetime' => $strStartDatetime)) === FALSE)
{
	CliEcho("ERROR: selCDR -- ".$selCDR->Error());
	exit(2);
}
elseif ($selCDRInvoiced->Execute(Array('Service' => $intService, 'StartDatetime' => $strStartDatetime)) === FALSE)
{
	CliEcho("ERROR: selCDRInvoiced -- ".$selCDRInvoiced->Error());
	exit(3);
}
else
{
	$arrOutput	= Array();
	$arrCDR		= Array();
	while ($arrCDR = $selCDR->Fetch())
	{
		foreach ($arrCDR as $strField=>&$mixValue)
		{
			$mixValue	= '"'.$mixValue.'"';
		}
		
		$arrOutput[]	= implode(',', $arrCDR);
	}
	
	while ($arrCDR = $selCDRInvoiced->Fetch())
	{
		foreach ($arrCDR as $strField=>&$mixValue)
		{
			$mixValue	= '"'.$mixValue.'"';
		}
		
		$arrOutput[]	= implode(',', $arrCDR);
	}
	
	// Output to CSV
	if (count($arrOutput))
	{
		//array_unshift($arrOutput, array_keys($arrCDR));
		CliEcho("Writing file...".file_put_contents("/home/rdavis/cdr_cost_0298369100.csv", implode("\n", $arrOutput))." bytes");
	}
	else
	{
		CliEcho("No CDRs");
		
	}
}

?>