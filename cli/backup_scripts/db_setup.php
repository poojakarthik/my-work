<?php

// Make sure we are the root database user
$GLOBALS['**arrDatabase']['flex']['URL']		= "192.168.2.224";
$GLOBALS['**arrDatabase']['flex']['User']		= "rich";
$GLOBALS['**arrDatabase']['flex']['Password']	= "richEEE";
$GLOBALS['**arrDatabase']['flex']['Database']	= "vixen";

// Destination Database
$strDestination	= "flexdemodata";

// Tables to copy structure and data for
$arrFullCopy = Array();
$arrFullCopy['Carrier']						= TRUE;
$arrFullCopy['Destination']					= TRUE;
$arrFullCopy['DestinationTranslation']		= TRUE;
$arrFullCopy['Documentation']				= TRUE;
$arrFullCopy['LetterTemplate']				= TRUE;
$arrFullCopy['LetterTemplateVar']			= TRUE;
$arrFullCopy['NoteType']					= TRUE;
$arrFullCopy['RecordType']					= TRUE;
$arrFullCopy['RecordTypeTranslation']		= TRUE;
$arrFullCopy['Tip']							= TRUE;
$arrFullCopy['UIAppDocumentation']			= TRUE;
$arrFullCopy['UIAppDocumentationOptions']	= TRUE;

// we use the actual tables not the db def in case it is out of date
require_once('../../flex.require.php');
LoadApplication();

/*$selTest	= new StatementSelect("flexdemodata.CDR", "MAX(StartDatetime)");
$selTest->Execute();
Debug($selTest->Fetch());
die;*/

CliEcho("\n[ FLEX DATABASE INSTALLER ]\n");

if ($argc > 1)
{
	CliEcho(" * Data to Copy: ");
	
	// Load command line paramaters as excluded tables
	foreach ($arrFullCopy as $strTable=>$arrTable)
	{
		CliEcho("\t + $strTable");
	}
}
else
{
	CliEcho(" * No Data to Copy.");
}
CliEcho("\n * Copying Tables...");

// set up list tables object
$qltListTable = new QueryListTables();

// get tables from vixen
$arrTables = $qltListTable->Execute('vixen');

// set up copy table object
$qctCopyTable		= new QueryCopyTable();
$qryDropIfExists	= new Query();
$qryCopySructure	= new Query();
$qryCopyData		= new Query();

// clean tables list
foreach($arrTables AS $mixKey=>$strTable)
{
	CliEcho(str_pad("\t + $strTable...", 35, ' ', STR_PAD_RIGHT), FALSE);
	
	if (strpos($strTable, '_') !== FALSE)
	{
		// tables with an '_' are temporary backups
		CliEcho(str_pad("[  SKIP  ]", 25, ' ', STR_PAD_LEFT));
	}
	else
	{
		$GLOBALS['fwkFramework']->StartWatch();
		
		// Copy the table structure
		$qryDropIfExists->Execute("DROP TABLE IF EXISTS $strDestination.$strTable");
		$qryCopySructure->Execute("CREATE TABLE $strDestination.$strTable LIKE $strTable");
		
		if ($arrFullCopy[$strTable])
		{
			// Copy data across
			$qryCopyData->Execute("INSERT INTO $strDestination.$strTable (SELECT * FROM $strTable)");
		}
		
		$intTime = (int)$GLOBALS['fwkFramework']->LapWatch();
		CliEcho(str_pad("{$intTime}s     [   OK   ]", 25, ' ', STR_PAD_LEFT));
	}
}

$intTotalTime	= (int)$GLOBALS['fwkFramework']->Uptime();
CliEcho("\n * Flex Database Creation completed in {$intTotalTime}s\n");
?>
