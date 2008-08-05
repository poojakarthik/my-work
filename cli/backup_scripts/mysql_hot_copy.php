<?php

define('MYSQL_HOT_COPY_DEBUG'			, FALSE);

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

$bolDumpTables	= FALSE;
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
	
	case '-de':
	case '-ed':
	case '-d':
		$bolDumpTables	= TRUE;
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


// Are we dumping all existing tables on the destination DB?
$qryListTables	= new Query();
if ($bolDumpTables === TRUE)
{
	// Yes, DROP all tables
	CliEcho("Dropping all existing tables from Destination DB '$strDestinationDB'");
	if (!($mixResult = $qryListTables->Execute("SHOW FULL TABLES FROM $strDestinationDB WHERE Table_type IN ('BASE TABLE', 'VIEW')")))
	{
		// Error on ListTables
		CliEcho("ERROR: \$qryListTables failed -- ".$qryListTables->Error());
		exit(2);
	}
	else
	{
		// Get list of tables
		while ($arrRow = $mixResult->fetch_row())
		{
			CliEcho("\t + Dropping '{$arrRow[0]}'...");
			// Drop each table
			if ($qryQuery->Execute("DROP TABLE IF EXISTS $strDestinationDB.{$arrRow[0]}") === FALSE)
			{
				CliEcho("ERROR: Unable to drop existing table $strDestinationDB.{$arrRow[0]} -- ".$qryQuery->Error());
				exit(5);
			}
		}
	}
}

CliEcho("\n * Copying Tables...");

// get tables from Source DB
$arrTables		= Array();
$qryListTables	= new Query();
if (!($mixResult = $qryListTables->Execute("SHOW FULL TABLES FROM $strSourceDB WHERE Table_type IN ('BASE TABLE', 'VIEW')")))
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
	
	
	if ($arrSpecifiedTables[$strTable])
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
CliEcho("\n * flex Hot Copy completed in {$intTotalTime}s\n");

// Exit with error code 0
exit;




// COPY TABLE
function mysqlCopyTable($strTable, $strSourceDB, $strDestinationDB)
{
	if (defined('MYSQL_HOT_COPY_DEBUG') && MYSQL_HOT_COPY_DEBUG)
	{
		// Debug mode, so don't perform the copy
		return TRUE;
	}
	else
	{
		$qryQuery	= new Query();
		
		// Drop Existing Table
		if ($qryQuery->Execute("DROP TABLE IF EXISTS $strDestinationDB.$strTable") !== FALSE)
		{
			// Replace with new Table
			if ($qryQuery->Execute("CREATE TABLE $strDestinationDB.$strTable LIKE $strSourceDB.$strTable") === FALSE)
			{
				CliEcho("ERROR: Unable to copy structure from $strSourceDB.$strTable to $strDestinationDB.$strTable -- ".$qryQuery->Error());
				exit(3);
			}
			else
			{
				// Copy data
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
		else
		{
			CliEcho("ERROR: Unable to drop existing table $strDestinationDB.$strTable -- ".$qryQuery->Error());
			exit(5);
		}
	}
}
?>