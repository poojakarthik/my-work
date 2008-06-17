<?php

// we use the actual tables not the db def in case it is out of date
require_once('../../flex.require.php');

// Set up the databases correctly
$strDestinationDB	= $GLOBALS['**arrDatabase']['flex']['Database'] . '_working';

LoadApplication();

define("MODE_INCLUDE"	, 1);
define("MODE_EXCLUDE"	, 2);

CliEcho("\n[ MYSQL HOT COPY ]\n");

CliEcho("Copying to database '$strDestinationDB' from '{$GLOBALS['**arrDatabase']['Database']}'");

$arrSpecifiedTables = Array();
if ($argc > 2)
{	
	// Load command line paramaters as excluded tables
	for ($intI = 2; $intI < $argc; $intI++)
	{
		$arrSpecifiedTables[trim($argv[$intI])]	= TRUE;
	}
}

switch ($argv[1])
{
	case '-i':
		$intMode	= MODE_INCLUDE;
		CliEcho("Tables to be copied:");
		foreach ($arrSpecifiedTables as $strTable=>$bolCopy)
		{
			CliEcho("\t$strTable");
		}
		break;
	
	case '-e':
		$intMode	= MODE_EXCLUDE;
		CliEcho("Tables to be skipped:");
		foreach ($arrSpecifiedTables as $strTable=>$bolSkip)
		{
			CliEcho("\t$strTable");
		}
		break;
	
	default:
		$intMode	= NULL;
		CliEcho("All tables will be copied");		
}




CliEcho("\n * Copying Tables...");

// set up list tables object
$qltListTable = new QueryListTables();

// get tables from Source DB
$arrTables = $qltListTable->Execute();

// set up copy table object
$qctCopyTable = new QueryCopyTable();

// clean tables list
foreach($arrTables AS $mixKey=>$strTable)
{
	CliEcho(str_pad("\t + $strTable...", 35, ' ', STR_PAD_RIGHT), FALSE);
	$strStatus	= '[   OK   ]';
	
	if (strpos($strTable, '_') !== FALSE)
	{
		// tables with an '_' are temporary backups
		//CliEcho("Skipping Backup Table\t: $strTable");
		CliEcho(str_pad("[  SKIP  ]", 25, ' ', STR_PAD_LEFT));
	}
	elseif ($arrSpecifiedTables[$strTable])
	{
		if ($intMode == MODE_EXCLUDE)
		{
			CliEcho(str_pad("[  SKIP  ]", 25, ' ', STR_PAD_LEFT));
		}
		elseif ($intMode == MODE_INCLUDE)
		{
			$GLOBALS['fwkFramework']->StartWatch();
			
			// copy a table
			if (!$qctCopyTable->Execute("$strDestinationDB.$strTable", $strTable))
			{
				$strStatus	= '[ FAILED ]';
			}
			
			$intTime = (int)$GLOBALS['fwkFramework']->LapWatch();
			CliEcho(str_pad("{$intTime}s     $strStatus", 25, ' ', STR_PAD_LEFT));
		}
	}
	elseif (!$intMode || ($intMode == MODE_EXCLUDE))
	{
		//CliEcho("Copying table\t\t: $strTable");
		
		$GLOBALS['fwkFramework']->StartWatch();
		
		// copy a table
		if (!$qctCopyTable->Execute("$strDestinationDB.$strTable", $strTable))
		{
			$strStatus	= '[ FAILED ]';
		}
		
		$intTime = (int)$GLOBALS['fwkFramework']->LapWatch();
		CliEcho(str_pad("{$intTime}s     $strStatus", 25, ' ', STR_PAD_LEFT));
	}
	else
	{
		CliEcho(str_pad("[  SKIP  ]", 25, ' ', STR_PAD_LEFT));
	}
}

$intTotalTime	= (int)$GLOBALS['fwkFramework']->Uptime();
CliEcho("\n * vixenworking Hot Copy completed in {$intTotalTime}s\n");
?>
