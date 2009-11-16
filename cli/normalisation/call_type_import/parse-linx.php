<?php

// Framework
require_once('../../../lib/classes/Flex.php');
Flex::load();

$sFilename	= $argv[1];
if (!file_exists($sFilename) || !is_readable($sFilename))
{
	throw new Exception("Unable to open file '{$sFilename}' for reading");
}

$rImportFile	= fopen($sFilename, 'r');

// Get Call Types
$iRecords	= 0;
$iIgnored	= 0;
$iParsed	= 0;
while (!feof($rImportFile))
{
	$iRecords++;
	$sLine	= fgets($rImportFile);
	
	// Record Type
	$sRecordType	= substr($sLine, 0, 3);
	if ($sRecordType != 'UTR')
	{
		// Skip -- we only want Usage Tariff Records
		$iIgnored++;
		continue;
	}
	
	// Product Billing Identifier
	$sPBI	= trim(substr($sLine, 12, 8));
	
	// Billing Element Code
	$sBEC	= trim(substr($sLine, 20, 8));
	
	// Description
	$sDescription	= trim(substr($sLine, 77, 80));
	
	// Unit of Measure (reference only)
	$sUnitOfMeasure	= trim(substr($sLine, 157, 5));
	
	// Distance Range Code
	$sDistanceRangeCode	= trim(substr($sLine, 229, 4));
	
	Log::getLog()->log("[+] PBI: {$sPBI}; BEC: {$sBEC}; UoM: {$sUnitOfMeasure}; DRC: {$sDistanceRangeCode}; Description: {$sDescription}");
	
	$iParsed++;
}

Log::getLog()->log("Total Records: {$iRecords}");
Log::getLog()->log("Total Ignored: {$iIgnored}");
Log::getLog()->log("Total Parsed: {$iParsed}");

exit(0);

?>