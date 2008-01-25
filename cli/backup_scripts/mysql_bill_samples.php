<?php

require_once("../framework/require.php");

// we use the actual tables not the db def in case it is out of date

// tables to be skipped
$arrSkipTables = Array();
$arrSkipTables['CDR']					= TRUE;
$arrSkipTables['CDRECreditLink']		= TRUE;
$arrSkipTables['CDREtech']				= TRUE;
$arrSkipTables['InvoiceOutput']			= TRUE;
$arrSkipTables['InvoiceOutputArchive']	= TRUE;
$arrSkipTables['InvoiceOutputTemp']		= TRUE;

// require application loader
require_once('application_loader.php');

CliEcho("\n[ BILL SAMPLES HOT COPY ]\n");
CliEcho(" * Copy Most Tables...");

// load backup application
$appBackup = new ApplicationBackup();

// run MySQL backup
//$appBackup->MysqlHotCopy($arrSkipTables);

// Run Custom CDR Sync
CliEcho(" * Copy Uninvoiced CDRs...");
$selUnbilledCDRMaxId	= new StatementSelect("CDR", "MAX(Id) AS Start", "1");
if (!$selUnbilledCDRMaxId->Execute())
{
	Debug("No Invoiced CDRs in Source Database");
	die;
}
$arrMaxId	= $selUnbilledCDRMaxId->Fetch();

// Copy CDRs from CATWALK.vixen
$qryCopyCDRs	= new Query();
$strCopyCDRs	= "INSERT INTO CDR SELECT * FROM vixen.CDR WHERE Id > {$arrMaxId['Start']} AND Status IN (100, 101, 150)";
//$qryCopyCDRs->Execute($strCopyCDRs);
?>