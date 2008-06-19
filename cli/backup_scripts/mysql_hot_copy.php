<?php

// we use the actual tables not the db def in case it is out of date
require_once('../../flex.require.php');

// Are we running on a _working server?
if (!($intRPos = strrpos($GLOBALS['**arrDatabase']['flex']['Database'], '_working')))
{
	// '_working' is not present in the Database name, therefore we are probably connected to a live server
	exit(1);
}

// Set up the databases correctly
$strDestinationDB	= $GLOBALS['**arrDatabase']['flex']['Database'];
$strSourceDB		= substr($GLOBALS['**arrDatabase']['flex']['Database'], 0, $intRPos);

define("MODE_INCLUDE"	, 1);
define("MODE_EXCLUDE"	, 2);

CliEcho("\n[ MYSQL HOT COPY ]\n");

CliEcho("Copying from database '$strSourceDB' to '$strDestinationDB'");

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

// get tables from Source DB
$arrTables		= Array();
$qryListTables	= new Query();
if (!($mixResult = $qryListTables->Execute("SHOW FULL TABLES FROM $strSourceDB WHERE Table_type = 'BASE TABLE'")))
{
	// Error on ListTables
	CliEcho("ERROR: \$qryListTables failed -- ".$qryListTables->Error());
	exit(2);
}
else
{
	while ($arrRow = $mixResult->fetch_row())
	{
		$arrTables[]	= $arrRow[0];
	}
}

// Copy specified Tables
foreach($arrTables AS $strTable)
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
			if (!mysqlCopyTable($strTable, $strSourceDB, $strDestinationDB))
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
		if (!mysqlCopyTable($strTable, $strSourceDB, $strDestinationDB))
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

// Exit with error code 0
exit;




// COPY TABLE
function mysqlCopyTable($strTable, $strSourceDB, $strDestinationDB)
{
	$qryQuery	= new Query();
	if ($qryQuery->Execute("CREATE TABLE $strDestinationDB.$strTable LIKE $strSourceDB.$strTable") === FALSE)
	{
		CliEcho("ERROR: Unable to copy structure from $strSourceDB.$strTable to $strDestinationDB.$strTable -- ".$qryQuery->Error());
		exit(3);
	}
	else
	{
		if ($qryQuery->Execute("INSERT INTO $strDestinationDB.$strTable SELECT * FROM $strSourceDB.$strTable") === FALSE)
		{
			CliEcho("ERROR: Unable to copy data from $strSourceDB.$strTable to $strDestinationDB.$strTable -- ".$qryQuery->Error());
			exit(4);
		}
		else
		{
			return TRUE;
		}
	}
}
?>