<?php

// CDR View

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen / etech Invoice Comparison');

// page link
$objPage->SetPageLink('[ Invoice List ]');

// menu
$objPage->AddBackLink();

// get input
$intMinDifference = (int)$_GET['Min'];

// display list
$objPage->ShowInvoiceCompareList($intMinDifference);

// display the page
$objPage->Render();
?>
