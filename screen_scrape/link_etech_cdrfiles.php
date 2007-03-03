<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

$selMatchFile	= new StatementSelect("FileImport", "Id", "FileName = <FileName>");

$arrColumns = Array();
$arrColumns['Id']		= NULL;
$arrColumns['Status']	= NULL;
$ubiUpdateFile	= new StatementUpdateById("FileImport", $arrColumns);

$arrColumns = Array();
$arrColumns['Status']	= NULL;
$updUpdateCDRs	= new StatementUpdate("CDR", "File = <File> AND Status = ".CDR_READY, $arrColumns);

$arrPaths[]		= '/home/richdavis/Desktop/2007_01.txt';
$arrPaths[]		= '/home/richdavis/Desktop/2007_02.txt';
$strDelimiter	= ',';
$strEnclosed	= '"';

echo	"\n\n" .
		"ETECH CDRFILE LINKER\n" .
		"=-=-=-=-=-=-=-=-=-=-\n\n";
		
// Open and parse CSVs
foreach ($arrPaths as $strPath)
{
	if (!file_exists($strPath))
	{
		echo "File '$strPath' doesn't exist!\n\n";
		die;
	}
	
	if (!$ptrFile = fopen($strPath, "r"))
	{
		echo "There was an error opening the file...\n\n";
		die;
	}
	
	echo "Reading and parsing '$strPath'...\n";
	
	// Ignore the first 3 rows (header), enter all other non-blanks to an array
	fgets($ptrFile);fgets($ptrFile);fgets($ptrFile);
	$arrFiles = Array();
	while ($strLine = fgets($ptrFile))
	{
		if ($strLine = trim($strLine))
		{
			$strLine	= str_replace($strEnclosed, "", $strLine);
			$arrFiles[]	= explode($strDelimiter, $strLine);
		}
	}
}

echo "\n";

// update the cdrfiles and cdrs
$intPassed		= 0;
$intCDRCount	= 0;
$intFileCount	= 0;
foreach ($arrFiles as $arrFile)
{
	echo str_pad("+ Updating CDR File and CDRs for {$arrFile[1]}...", 90, " ", STR_PAD_RIGHT);	
	
	// match the file
	$mixResponse = $selMatchFile->Execute(Array('FileName' => $arrFile[1]));
	if ($mixResponse === FALSE)
	{
		echo "[ FAILED ]\n\t- Reason: Matching query failed\n";
		continue;
	}
	elseif ($mixResponse == 0)
	{
		echo "[ FAILED ]\n\t- Reason: No match.  FILE DOESN'T EXIST IN VIXEN!!!!\n";
		continue;
	}
	$arrFileData = $selMatchFile->Fetch(); 
	
	// update the file
	$arrData = Array();
	$arrData['Id']		= $arrFileData['Id'];
	$arrData['Status']	= CDRFILE_ETECH_INVOICED;
	$mixResponse = $ubiUpdateFile->Execute($arrData);
	if ($mixResponse === FALSE)
	{
		echo "[ FAILED ]\n\t- Reason: Update file query failed\n";
		continue;
	}
	$intFileCount += $mixResponse;
	
	// update the cdrs
	$arrData = Array();
	$arrData['Status']	= CDR_ETECH_INVOICED;
	$arrWhere = Array();
	$arrWhere['File']	= $arrFileData['Id'];
	$mixResponse = $updUpdateCDRs->Execute($arrData, $arrWhere);
	if ($mixResponse === FALSE)
	{
		echo "[ FAILED ]\n\t- Reason: Update CDR query failed\n";
		continue;
	}
	$intCDRCount += $mixResponse;
	
	echo "[   OK   ]\n";
	$intPassed++;
}

echo "\nUpdated $intFileCount files and $intCDRCount CDRs.  $intPassed files passed, ".(count($arrFiles)-$intPassed)." failed.\n\n";
die;


?>