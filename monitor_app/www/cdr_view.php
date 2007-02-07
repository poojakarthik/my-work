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
$intCDR = (int)$_GET['Id'];

// display CDR
$objPage->ShowNormalisedCDR($intCDR);

// display the page
$objPage->Render();
?>
