<?php

// viXen/Etech CDR Comparison

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// clean output
$arrOutput = Array();

// page title
$objPage->AddPageTitle('viXen/Etech CDR Comparison');

// page link
$objPage->SetPageLink('[ viXen/Etech CDR Comparison ]');

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");
$objPage->AddLink("invoice_list_etech.php","[ viXen/Etech Invoice Comparison ]");

$objPage->AddBackLink();

// get Etech CDR Id
$intEtechCDR = (int)$_GET['Id'];

$objPage->AddTitle("Comparison of Etech CDR $intEtechCDR to viXen CDR");

// display CDR
$objPage->ShowEtechCDR($intEtechCDR);

// display the page
$objPage->Render();
?>
