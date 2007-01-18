<?php

// require stuff
require_once("include.php");

echo "<pre>";

// show cdr
$intCDR = (int)$_REQUEST['id'];
if ($intCDR)
{
	// Create an instance of each Normalisation module
	$arrNormalisationModule[CDR_UNTIEL_RSLCOM]		= new NormalisationModuleRSLCOM();
	$arrNormalisationModule[CDR_UNITEL_SE]			= new NormalisationModuleRSLCOM();
	$arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
	$arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
	$arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
	$arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();

	// get CDR
	$selCDR = new StatementSelect("CDR JOIN FileImport ON CDR.File = FileImport.Id", "CDR.*, FileImport.FileType AS FileType", "CDR.Id = <Id>");
	if (!$selCDR->Execute(Array('Id' => $intCDR)))
	{
		echo "Invalid CDR record requested.  Please double-check the Id ($intCDR).\n";
		die;
	}
	$arrCDR = $selCDR->Fetch();
		
	// Check for a Normalisation Module
	if ($arrNormalisationModule[$arrCDR['FileType']])
	{
		// normalise CDR
		$mixReturn = $arrNormalisationModule[$arrCDR['FileType']]->Normalise($arrCDR);
		
		// debug CDR
		$arrDebugCDR = $arrNormalisationModule[$arrCDR['FileType']]->DebugCDR();
	}
	else
	{
		echo "No Normalisation Module found for this CDR.\n";
		die;
	}
	
	// display CDR
	PrintPrettyDebugInfo($arrDebugCDR);
}
else
{
	echo "No CDR record requested.\n";
}
die;




function PrintPrettyDebugInfo($arrDebugCDR)
{
	// TODO!rich! Print pretty debug info
	Debug($arrDebugCDR);
}

?>
