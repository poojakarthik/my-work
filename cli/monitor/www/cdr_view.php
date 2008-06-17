<?php

// CDR View

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

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
