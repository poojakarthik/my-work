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

// GET data
$strStartDate	= NULL;
$strEndDate		= NULL;
if (count($_GET) >= 6)
{
	$strStartDate	= "{$_GET['intYearStart']}-{$_GET['intMonthStart']}-{$_GET['intDayStart']}";
	$strEndDate		= "{$_GET['intYearEnd']}-{$_GET['intMonthEnd']}-{$_GET['intDayEnd']}";
}

//Debug($strStartDate);
//Debug($strEndDate);

// Date Range
$objPage->AddText($strJS);
$objPage->AddTitle("<br />Date Range to Search");
$objPage->AddDateRangeSelect(basename(__FILE__), $strStartDate, $strEndDate);

// show list
if ($strStartDate && $strEndDate)
{
	$objPage->ShowCDRStatusList(Array('Start' => $strStartDate, 'End' => $strEndDate));
}

// display the page
$objPage->Render();
?>
