<?php

// cdr index

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen CDR Menu');

// page link
$objPage->SetPageLink('[ CDR Menu ]');

// menu
$objPage->AddLink("cdr_list_status.php","[ CDRs by Status ]");
$objPage->AddLink("cdr_list_status_recordtype.php","[ CDRs by Status, RecordType ]");
$objPage->AddLink("cdr_list_rate.php","[ CDRs by Rate ]");
$objPage->AddLink("cdr_list_rate.php?Compare=Etech","[ CDRs by Rate, Etech Comparison ]");
// display the page
$objPage->Render();

?>
