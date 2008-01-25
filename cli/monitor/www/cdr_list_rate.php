<?php

// cdr totals by status

// load application
require_once('application_loader.php');

// page title
$objPage->AddPageTitle('viXen CDRs by Rate');

// page link
$objPage->SetPageLink('[ CDRs by Rate ]');

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");

// show list
$strCompare = $_GET['Compare'];
if ($strCompare)
{
	$objPage->ShowCDRRateCompareList($strCompare);
}
else
{
	$objPage->ShowCDRRateList();
}

// display the page
$objPage->Render();

?>
