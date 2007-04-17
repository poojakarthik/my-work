<?php

// we use the actual tables not the db def in case it is out of date

// tables to be skipped
$arrSkipTables = Array();
$arrSkipTables['CDR']	= TRUE;


// require application loader
require_once('application_loader.php');

// set up list tables object
$qctCopyTable = new QueryListTables();

// get tables from vixen
$arrTables = $qctCopyTable->Execute('vixen');

// clean tables list
foreach($arrTables AS $mixKey=>$strTable)
{
	if (strpos($strTable, '_') !== FALSE)
	{
		// tables with an '_' are temporary backups
		echo "skip table : $strTable\n";
	}
	elseif ($arrSkipTables[$strTable])
	{
		echo "skip table : $strTable\n";
	}
	else
	{
		echo "copy table : $strTable\n";
		// set up copy table object
		$qctCopyTable = new QueryCopyTable();
		
		// copy a table
		$qctCopyTable->Execute($strTable, "vixen.$strTable");
	}
}
?>
