<?php

// require stuff
require_once("include.php");


// show cdr
$intCDR = (int)$_REQUEST['id'];
if ($intCDR)
{
	// Create an instance of each Normalisation module
	$arrNormalisationModule[CDR_UNTIEL_RSLCOM]		= new NormalisationModuleRSLCOM();
	$arrNormalisationModule[CDR_UNTIEL_SE]			= new NormalisationModuleRSLCOM();
	$arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
	$arrNormalisationModule[CDR_UNTIEL_COMMANDER]	= new NormalisationModuleCommander();
	$arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
	$arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();
	
	// get the CDR record
	//TODO!rich! get CDR with Id $intCDR
	
	// normalise it
	// $arrNormalisedCDR =
	//TODO!rich! normalise the CDR
	
	// display it
	Debug($arrNormalisedCDR);
}
else
{
	echo "no CDR record requested";
}

?>
