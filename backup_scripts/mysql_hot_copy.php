<?php

// we use the actual tables not the db def in case it is out of date

require_once('../framework/require.php');
LoadApplication();

CliEcho("\n[ VIXENWORKING HOT COPY ]\n");

if ($argc > 1)
{
	CliEcho(" * Non-Backup Tables to be skipped: ");
	
	// Load command line paramaters as excluded tables
	for ($intI = 1; $intI < $argc; $intI++)
	{
		$arrSkipTables[trim($argv[$intI])]	= TRUE;
		CliEcho("\t + ".trim($argv[$intI]));
	}
}
else
{
	CliEcho(" * No Non-Backup tables will be skipped.");
}
CliEcho("\n * Copying Tables...");

// set up list tables object
$qltListTable = new QueryListTables();

// get tables from vixen
$arrTables = $qltListTable->Execute('vixen');

// set up copy table object
$qctCopyTable = new QueryCopyTable();

// clean tables list
foreach($arrTables AS $mixKey=>$strTable)
{
	CliEcho(str_pad("\t + $strTable...", 35, ' ', STR_PAD_RIGHT), FALSE);
	
	if (strpos($strTable, '_') !== FALSE)
	{
		// tables with an '_' are temporary backups
		//CliEcho("Skipping Backup Table\t: $strTable");
		CliEcho(str_pad("[  SKIP  ]", 25, ' ', STR_PAD_LEFT));
	}
	elseif ($arrSkipTables[$strTable])
	{
		//CliEcho("Skipping Table\t: $strTable");
		CliEcho(str_pad("[  SKIP  ]", 25, ' ', STR_PAD_LEFT));
	}
	else
	{
		//CliEcho("Copying table\t\t: $strTable");
		
		$GLOBALS['fwkFramework']->StartWatch();
		
		// copy a table
		$qctCopyTable->Execute($strTable, "vixen.$strTable");
		
		sleep(1);
		$intTime = (int)$GLOBALS['fwkFramework']->LapWatch();
		CliEcho(str_pad("{$intTime}s     [   OK   ]", 25, ' ', STR_PAD_LEFT));
	}
}

$intTotalTime	= (int)$GLOBALS['fwkFramework']->Uptime();
CliEcho("\n * vixenworking Hot Copy completed in {$intTotalTime}s\n");
?>
