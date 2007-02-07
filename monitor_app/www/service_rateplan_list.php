<?php

// cdr list

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen Service RatePlan List');

// page link
$objPage->SetPageLink('[ Service RatePlan List ]');

// get values
$intStart 		= (int)$_GET['Start'];
$intLimit 		= (int)$_GET['Limit'];
if (!$intLimit)
{
	$intLimit 	= 30;
}
$intService		= (int)$_GET['Service'];
$intMaxId 		= $intStart;

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");
$objPage->AddBackLink();

// show list
$objPage->ShowServiceRatePlanList($intService);

// display the page
$objPage->Render();

?>
