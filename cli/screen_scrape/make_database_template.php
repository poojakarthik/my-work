<?php

$GLOBALS['**arrDatabase']['User']		= "root";
$GLOBALS['**arrDatabase']['Password']	= "zeemu";

require_once("../framework/require.php");

// Makes a template database from Minx's "vixen" database
Debug("[ CREATE TEMPLATE DATABASE ]\n");

$qryDBExists		= new Query();
$qryDBDrop			= new Query();
$qryDBCreate		= new Query();
$qryListTables		= new Query();
$qryCopyStructure	= new Query();
$qryCopyData		= new Query();
$qryListColumns		= new Query();

$arrDataTables = Array();
$arrDataTables[]	= "Carrier";
$arrDataTables[]	= "ConditionalContexts";
$arrDataTables[]	= "Config";
$arrDataTables[]	= "DataReport";
$arrDataTables[]	= "Destination";
$arrDataTables[]	= "DestinationTranslation";
$arrDataTables[]	= "Documentation";
$arrDataTables[]	= "NoteType";
$arrDataTables[]	= "Tip";
$arrDataTables[]	= "UIAppDocumentation";
$arrDataTables[]	= "UIAppDocumentationOptions";

$resResult	 = $qryDBExists->Execute("SHOW DATABASES WHERE `Database` = 'vixen_template'");
if ($resResult->fetch_assoc())
{
	CliEcho("Template database already exists.  Overwrite ([Y]/n)? ", FALSE);
	while (true)
	{
		$strInput = trim(strtoupper(fgets(STDIN)));
		if ($strInput == 'Y')
		{
			// Overwrite
			Debug("Overwriting previous database...");
			break;
		}
		elseif ($strInput == 'N' || !$strInput)
		{
			// exit
			Debug("Template Database Setup Aborted by User!");
			die;
		}
		else
		{
			CliEcho("Invalid Input ('$strInput')! Overwrite (y/[N])? ", FALSE);
		}
	}
}

// Drop previous DB
$qryDBDrop->Execute("DROP DATABASE IF EXISTS vixen_template");
if ($qryDBDrop->Error())
{
	Debug($qryDBDrop->Error());
}

// Create new DB
$qryDBCreate->Execute("CREATE DATABASE vixen_template");
if ($qryDBCreate->Error())
{
	Debug($qryDBCreate->Error());
}


// List tables, then create templates from them
$resResult = $qryListTables->Execute("SHOW TABLES FROM vixen");
while ($arrTable = $resResult->fetch_assoc())
{
	$strTable = reset($arrTable);
	CliEcho(str_pad("Copying $strTable...", 64, ' ', STR_PAD_RIGHT), FALSE);
	
	if (($intPos = strpos($strTable, '_')) !== FALSE)
	{
		// Tables with an '_' are temporary backups
		CliEcho("[  SKIP  ]");
	}
	else
	{
		// Copy Structure to vixen_template
		$qryCopyStructure->Execute("CREATE TABLE vixen_template.$strTable LIKE vixen.$strTable");
		
		// If we need the data, then copy
		if (in_array($strTable, $arrDataTables))
		{
			// Get columns
			$resColumns = $qryListColumns->Execute("SHOW COLUMNS FROM vixen.$strTable");
			$arrColumns = Array();
			while ($arrColumn = $resColumns->fetch_assoc())
			{
				$arrColumns[]	= $arrColumn['Field'];
			}
			
			$strQuery =	"INSERT INTO vixen_template.$strTable (".implode(', ', $arrColumns).") \n" .
						"(SELECT * FROM vixen.$strTable)";
			$qryCopyData->Execute($strQuery);
		}
		
		CliEcho("[   OK   ]");	
	}
}
?>