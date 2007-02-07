<?php

// cdr totals by status

// load application
require_once('application_loader.php');

// page title
$objPage->AddPageTitle('viXen CDRs by Status');

// page link
$objPage->SetPageLink('[ CDRs by Status ]');

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");

// show list
$objPage->ShowCDRStatusList();

// display the page
$objPage->Render();

?>
