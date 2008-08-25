<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selFindDuplicate	= new StatementSelect(	"CDR",
											"*",
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

$selGetCDR			= new StatementSelect("CDR", "*", "Id = <Id>");

// Get Command line argument
$arrSourceCDR['Id']	= (int)$argv[1];

CliEcho("\n\t + CDR #{$arrSourceCDR['Id']} $intCount/$intTotal...\t\t", FALSE);

// Does this already exist?
if ($selGetCDR->Execute($arrSourceCDR))
{
	$arrSourceCDR	= $selGetCDR->Fetch();
	if ($selFindDuplicate->Execute($arrSourceCDR))
	{
		$arrDuplicateCDR	= $selFindDuplicate->Fetch();
		CliEcho("Duplicate CDR Id: '{$arrDuplicateCDR['Id']}'\n");
		
		foreach ($arrSourceCDR as $strField=>$mixValue)
		{
			CliEcho("[ $strField ]");
			CliEcho("Source\t\t: $mixValue");
			CliEcho("Duplicate\t: {$arrDuplicateCDR[$strField]}");
		}
	}
	else
	{
		CliEcho("No duplicate found\n");
	}
}
CliEcho();
?>