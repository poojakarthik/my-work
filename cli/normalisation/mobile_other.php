#!/usr/bin/php
<?php

// display a list of calls to mobile that have RecordType Other

// require stuff
require_once('../../flex.require.php');

// Create an instance of each Normalisation module
$arrNormalisationModule[CDR_UNITEL_RSLCOM]		= new NormalisationModuleRSLCOM();
$arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
$arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
$arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
$arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();

// init fetch query
$strQuery = "SELECT CDR.CDR, FileImport.FileType AS FileType FROM CDR JOIN FileImport ON CDR.File = FileImport.Id WHERE CDR.Destination LIKE '04%' AND CDR.RecordType = 26 AND CDR.ServiceType = 102";
$fchMobileOther = new QueryFetch($strQuery);

// clean output array
$arrOutput = Array();

// init counter
$intCounter = 0;

$stdout = fopen("php://stdout","w"); 

// Get Results
while ($arrCDR = $fchMobileOther->FetchNext())
{
	$intCounter++;
	if ($intCounter > 1000)
	{
		$intCounter = 0;
		fwrite($stdout, ".\n");
	}
	// Check for a Normalisation Module
	if ($arrNormalisationModule[$arrCDR['FileType']])
	{
		// get file type
		$intFileType 		= $arrCDR['FileType'];
		
		// Get Raw CDR
		$arrRawCDR 			= $arrNormalisationModule[$intFileType]->RawCDR($arrCDR['CDR']);

		// Get RecordType
		$intRawRecordType 	= $arrNormalisationModule[$intFileType]->RawRecordType();

		if (!$arrOutput[$intFileType][$intRawRecordType])
		{
			// get a Description for this File Type
			$strFileType 	= GetConstantDescription($intFileType, 'CDRType');
			fwrite($stdout, "$strFileType : $intRawRecordType\n");
		}

		// stick it all in the output array
		$arrOutput[$intFileType][$intRawRecordType]++;
	}
	else
	{
		echo "No Normalisation Module found for this CDR.\n";
		fclose($stdout);
		die;
	}
}

// Done
echo "Done\n";
fclose($stdout);
die();
?>
