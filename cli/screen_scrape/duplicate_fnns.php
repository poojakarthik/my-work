<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];


$strPath		= '/usr/share/vixen/screen_scrape/duplicate_fnn_2007_02_28.csv';
$strDelimiter	= ',';
$strEnclosed	= '"';

$selMatchFNNToAccount = new StatementSelect(	"Service",
												"Id",
												"FNN = <FNN> AND Account = <Account> AND ClosedOn IS NULL");
$arrColumns = Array();
$arrColumns['ClosedOn']	= "1987-10-21";
$updCloseServices	= new StatementUpdate(	"Service",
											"FNN = <FNN> AND Account != <Account>",
											$arrColumns);

echo	"\n\n" .
		"DUPLICATE FNN REMOVER\n" .
		"=-=-=-=-=-=-=-=-=-=-=\n\n";
		
// Open and parse CSV

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

// Ignore the first row (header), enter all other non-blanks to an array
fgets($ptrFile);
$arrFNNs = Array();
while ($strLine = fgets($ptrFile))
{
	if ($strLine = trim($strLine))
	{
		$strLine	= str_replace($strEnclosed, "", $strLine);
		$arrFNNs[]	= explode($strDelimiter, $strLine);
	}
}

// perform a lookup on each service and modify as necessary
$intPassed = 0;
$intClosed = 0;
foreach ($arrFNNs as $arrFNN)
{
	// field 0 is the FNN, field 1 is the FNN Count, field 3 is the correct account
	echo str_pad(" + Matching {$arrFNN[0]} to Account #{$arrFNN[2]}...", 70, " ", STR_PAD_RIGHT);
	
	// Match FNN to account
	$arrWhere = Array();
	$arrWhere['FNN']		= $arrFNN[0];
	$arrWhere['Account']	= (int)$arrFNN[2];
	if (!$selMatchFNNToAccount->Execute($arrWhere))
	{
		echo "[ FAILED ]\n";
		continue;
	}
	
	// Close all Services with this FNN not on this account
	$arrColumns = Array();
	$arrColumns['ClosedOn']	= "1985-10-21";
	if (($mixResult = $updCloseServices->Execute($arrColumns, $arrWhere)) === FALSE)
	{
		echo "[ FAILED ]\n";
		continue;
	}
	echo "[   OK   ]\n";
	$intPassed++;
	$intClosed += $mixResult;
}

// Report and exit
echo "\nRemoved $intClosed instances of ".count($arrFNNs)." FNNs. $intPassed passed, ".(count($arrFNNs)-$intPassed)." failed.\n\n";
?>
