<?php

// viXen/Etech Invoice Comparison

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// clean output
$arrOutput = Array();

// page title
$objPage->AddPageTitle('Etech CDR List');

// page link
$objPage->SetPageLink('[ Etech CDR List ]');

// menu
$objPage->AddLink("invoice_list_etech.php","[ Etech Invoice List ]");

$objPage->AddBackLink();

$objPage->AddTitle('Etech CDRs');

// get Etech Invoice Id
$strBillingPeriod = $_GET['period'];
$intStart = (int)$_GET['Start'];
$intLimit = (int)$_GET['Limit'];

if ($intLimit < 0 || !$intLimit)
{
	$intLimit = 30;
}

// display Invoice
$objPage->ShowEtechCDRList($strBillingPeriod, $intStart, $intLimit);

// display the page
$objPage->Render();
?>
