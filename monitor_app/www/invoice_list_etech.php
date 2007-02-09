<?php

// viXen/Etech Invoice List

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// clean output
$arrOutput = Array();

// page title
$objPage->AddPageTitle('viXen/Etech Invoice Comparison');

// page link
$objPage->SetPageLink('[ viXen/Etech Invoice Comparison ]');

$objPage->AddBackLink();

// display invoice list
$objPage->ShowEtechInvoiceList();

// display the page
$objPage->Render();
?>