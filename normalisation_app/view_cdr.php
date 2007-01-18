<?php

// require stuff
require_once("include.php");


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

	// get cdr
	$selCDR = new StatementSelect("CDR JOIN FileImport ON CDR.File = FileImport.Id", "CDR.CDR AS CDR, FileImport.FileType AS FileType", "CDR.Id = <Id>");
	if (!$selCDR->Execute(Array('Id' => $intCDR)))
	{
		echo "Invalid CDR record requested.  Please double-check the Id ($intCDR).\n";
		die;
	}
	$arrCDR = $selCDR->Fetch();
		
	// normalise and debug it
	$arrDebugCDR = $arrNormalisationModule[$arrCDR['FileType']]->DebugCDR($arrCDR['CDR']);
	
	// display it
	Debug($arrDebugCDR);
}
else
{
	echo "No CDR record requested.\n";
}

?>
