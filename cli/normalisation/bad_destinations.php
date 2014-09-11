<?php

// display a list of bad destinations

// require stuff
require_once("../../flex.require.php");

// Create an instance of each Normalisation module
$arrNormalisationModule[CDR_UNITEL_RSLCOM]		= new NormalisationModuleRSLCOM();
$arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
$arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
$arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
$arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();

// init fetch query
$strQuery = "Select CDR.CDR, FileImport.FileType AS FileType FROM CDR JOIN FileImport ON CDR.File = FileImport.Id WHERE CDR.Status = ".CDR_BAD_DESTINATION;
$fchBadDestination = new QueryFetch($strQuery);

// clean output array
$arrOutput = Array();

// Get Results
while ($arrCDR = $fchBadDestination->FetchNext())
{
	// Check for a Normalisation Module
	if ($arrNormalisationModule[$arrCDR['FileType']])
	{
		// get file type
		$intFileType = $arrCDR['FileType'];
		
		// Get Raw CDR
		$arrRawCDR = $arrNormalisationModule[$intFileType]->RawCDR($arrCDR['CDR']);
		
		// Get DestinationCode
		$strRawDestinationCode = $arrNormalisationModule[$intFileType]->RawDestinationCode();
		
		// Get DestinationCode
		$strRawDescription = $arrNormalisationModule[$intFileType]->RawDescription();
		
		// stick it all in the output array
		$arrOutput[$intFileType][$strRawDestinationCode][] = $strRawDescription;
	}
	else
	{
		echo "No Normalisation Module found for this CDR.\n";
		die;
	}
}

// Display Results
echo "<pre>";

foreach ($arrOutput AS $intFileType=>$arrDestination)
{
	// get a Description for this File Type
	$strFileType = GetConstantDescription($intFileType, 'CDRType');
	echo "$strFileType :\n\n";
	echo "Raw Code\tCount\tDescription\n";
	
	// output Destination Codes
	foreach ($arrDestination AS $strDestinationCode=>$arrDescription)
	{
		$intCount = Count($arrDescription);
		$arrDescription = array_unique($arrDescription);
		foreach ($arrDescription AS $strDescription)
		{
			echo "$strDestinationCode\t\t$intCount\t$strDescription\n";
		}
	}
	
	echo "\n\n\n\n";
}

echo "</pre>";
// Done
die();
?>
