<?php

// CDR View

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// require stuff
$strNormalisationDir = $strVixenBaseDir."normalisation_app/";
require_once($strNormalisationDir."normalisation_modules/base_module.php");
require_once($strNormalisationDir."normalisation_modules/module_aapt.php");
require_once($strNormalisationDir."normalisation_modules/module_commander.php");
require_once($strNormalisationDir."normalisation_modules/module_iseek.php");
require_once($strNormalisationDir."normalisation_modules/module_optus.php");
require_once($strNormalisationDir."normalisation_modules/module_rslcom.php");

// clean output
$arrOutput = Array();

// page title
$objPage->AddPageTitle('viXen CDR View');

// page link
$objPage->SetPageLink('[ CDR View ]');

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");
$objPage->AddBackLink();

// get CDR Id
$intCDR = (int)$_REQUEST['Id'];
if (!$intCDR)
{
	$objPage->AddError("NO CDR Requested");
}
else
{
	// Create an instance of each Normalisation module
	$arrNormalisationModule[CDR_UNITEL_RSLCOM]		= new NormalisationModuleRSLCOM();
	$arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
	$arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
	$arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
	$arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();

	// get CDR
	$arrCDR = $appMonitor->GetCDR($intCDR);
	if (!$arrCDR)
	{
		$objPage->AddError("CDR Not Found");
	}
	else
	{
		// Check for a Normalisation Module
		if (!$arrNormalisationModule[$arrCDR['FileType']])
		{
			$objPage->AddError("Missing CDR Normalisation Module");
		}
		else
		{
			// normalise CDR
			$mixReturn = $arrNormalisationModule[$arrCDR['FileType']]->Normalise($arrCDR);
			
			// debug CDR
			$arrOutput = $arrNormalisationModule[$arrCDR['FileType']]->DebugCDR();
		}
	}
}


// Display CDR
if ($arrOutput)
{
	if (is_array($arrOutput['Normalised']))
	{
		// title
		$objPage->AddTitle("Normalised CDR");
		
		// table
		$tblCDR = $objPage->NewTable('Border');
		foreach($arrOutput['Normalised'] AS $strKey=>$strValue)
		{
			$arrRow = Array($strKey, $strValue);
			$tblCDR->AddRow($arrRow);
		}
		$objPage->AddTable($tblCDR);
	}
	
	if (is_array($arrOutput['Raw']))
	{
		// title
		$objPage->AddTitle("Raw CDR");
		
		// table
		$tblCDR = $objPage->NewTable('Border');
		foreach($arrOutput['Raw'] AS $strKey=>$strValue)
		{
			$arrRow = Array($strKey, $strValue);
			$tblCDR->AddRow($arrRow);
		}
		$objPage->AddTable($tblCDR);
	}
}


// display the page
$objPage->Render();
?>
