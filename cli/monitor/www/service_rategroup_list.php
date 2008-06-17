<?php

// cdr list

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen Service RateGroup List');

// page link
$objPage->SetPageLink('[ Service RateGroup List ]');

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
$objPage->ShowServiceRateGroupList($intService);

// display the page
$objPage->Render();

?>
