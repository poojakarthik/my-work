<?php

// cdr list by status, recordtype

// load application
require_once('application_loader.php');

// page title
$objPage->AddPageTitle('viXen CDRs by Status, RecordType');

// page link
$objPage->SetPageLink('[ CDRs by Status, RecordType ]');

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");

// show list
$objPage->ShowCDRStatusRecordTypeList();

// display the page
$objPage->Render();

?>
