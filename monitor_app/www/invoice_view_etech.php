<?php

// viXen/Etech Invoice Comparison

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

// menu
$objPage->AddLink("invoice_list_etech.php","[ viXen/Etech Invoice Comparison ]");

$objPage->AddBackLink();

// get Etech Invoice Id
$strBillingPeriod = (int)$_GET['Period'];

// display Invoice
$objPage->ShowEtechInvoice($strBillingPeriod);

// display the page
$objPage->Render();
?>
