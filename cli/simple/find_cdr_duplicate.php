<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selFindDuplicate	= new StatementSelect(	"CDR",
											"Id",
											"Id != <Id> AND " .
											"Source = <Source> AND " .
											"Destination = <Destination> AND " .
											"StartDatetime = <StartDatetime> AND " .
											"EndDatetime = <EndDatetime> AND " .
											"Units = <Units> AND " .
											"Cost = <Cost> AND " .
											"SequenceNo = <SequenceNo> AND " .
											"RecordType = <RecordType> AND " .
											"RecordType NOT IN (10, 15, 33) AND " .
											"Credit = <Credit> AND " .
											"Status != ".CDR_DUPLICATE,
											NULL,
											1);

// Get Command line argument
$arrSourceCDR['Id']	= (int)$argv[1];

CliEcho("\n\t + CDR #{$arrSourceCDR['Id']} $intCount/$intTotal...\t\t", FALSE);

// Does this already exist?
if ($selFindDuplicate->Execute($arrSourceCDR))
{
	$arrCDR	= $selFindDuplicate->Fetch();
	CliEcho("Duplicate CDR Id: '$arrCDR'\n");
}
else
{
	CliEcho("No duplicate found\n");
}
?>