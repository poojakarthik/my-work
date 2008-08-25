<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selCDR				= new StatementSelect("CDR", "*", "Status IN (101, 107, 150, 151) AND StartDatetime >= '2008-07-01 00:00:00'");

$selFindDuplicate	= new StatementSelect(	"CDR",
											"Id",
											"Id != <Id> AND " .
											"FNN = <FNN> AND " .
											"Source <=> <Source> AND " .
											"Destination <=> <Destination> AND " .
											"StartDatetime <=> <StartDatetime> AND " .
											"EndDatetime <=> <EndDatetime> AND " .
											"Units = <Units> AND " .
											"Cost = <Cost> AND " .
											"RecordType = <RecordType> AND " .
											"RecordType NOT IN (10, 15, 33) AND " .
											"Credit = <Credit> AND " .
											"Description <=> <Description> AND " .
											"Status != ".CDR_DUPLICATE,
											NULL,
											1);

$arrCols			= Array();
$arrCols['Status']	= CDR_DUPLICATE;
$ubiCDR				= new StatementUpdateById("CDR", $arrCols);

// Run Processor
CliEcho("\n[ MARK DUPLICATE CDRS ]\n");

if (($intTotal = $selCDR->Execute()) === FALSE)
{
	Debug($selCDR->Error());
}
else
{
	$intCount		= 0;
	$intStartTime	= time();
	$intCurrentTime	= $intStartTime;
	
	// Foreach CDR
	while ($arrCDR = $selCDR->Fetch())
	{
		$intLastTime	= $intCurrentTime;
		
		$intCount++;
		CliEcho(" \t + CDR $intCount/$intTotal...\t\t", FALSE);
		
		// Does this already exist?
		if ($selFindDuplicate->Execute($arrCDR))
		{
			// Set this CDR to CDR_DUPLICATE
			$arrCDR['Status']	= CDR_DUPLICATE;
			if (false/*$ubiCDR->Execute($arrCDR) === FALSE*/)
			{
				CliEcho("[ FAILED ]");
				CliEcho("\t\t -- ".$ubiCDR->Error(), FALSE);
			}
			else
			{
				CliEcho("[   OK   ]", FALSE);
			}
		}
		else
		{
			CliEcho("[  SKIP  ]", FALSE);
		}
		
		$intCurrentTime	= time();
		$intProcessTime	= $intCurrentTime - $intLastTime;
		$intRunningTime	= $intCurrentTime - $intStartTime;
		CliEcho(" ({$intProcessTime}s/{$intRunningTime}s)");
	}
}

$intTotalTime	= time() - $intStartTime;
CliEcho("Total Time Taken: $intTotalTime seconds\n");

?>