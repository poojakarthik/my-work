<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Add a new Data Report to viXen
//----------------------------------------------------------------------------//

// load application
require_once('../../flex.require.php');

//----------------------------------------------------------------------------//
// TODO: Specify the DataReport here!  See report_skeleton.php for tut
//----------------------------------------------------------------------------//

function showUsage()
{
	echo	"\nUsage:\n".
			"\tphp report_add.php REPORT_SOURCE_FILE\n".
			"where:".
			"\tREPORT_SOURCE_FILE is a php file defining the report to add (include the php file extension)\n\n";
}

// The file that defines the report, must stick all the details for the DataReport record, in this array
$arrDataReport	= array();


if ($argc != 2)
{
	showUsage();
	exit(1);
}

$strFilename = $argv[1];

if (substr($strFilename, -4) != '.php')
{
	echo "\nERROR: The report file must have the php extension\n";
	showUsage();
	exit(1);
}

if (!file_exists($strFilename))
{
	echo "\nERROR: Cannot find file: {$strFilename}\n";
	showUsage();
	exit(1);
}

// parse the report file
require $strFilename;

// Insert it into the database
TransactionStart();
$insDataReport = new StatementInsert("DataReport");

echo "\nImporting report: '{$arrDataReport['Name']}' from {$strFilename}... ";

if ($insDataReport->Execute($arrDataReport) === false)
{
	TransactionRollback();
	echo "FAIL!!!\n";
	Debug($insDataReport->Error());
	exit(1);
}

TransactionCommit();
echo "OK!\n";


// finished
echo("\n\n-- End of Report Generation --\n");

?>