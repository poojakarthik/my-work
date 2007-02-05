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

// get Rate Group list
$arrWhere = Array();
if ($intService)
{
	$arrRatePlans = $appMonitor->ListServiceRatePlan($intService);
	if (is_array($arrRatePlans))
	{
		$objPage->AddTitle("RatePlans for Service: $intService");
	
		// table
		$tblRatePlan = $objPage->NewTable('Border');
		$tblRatePlan->AddRow(Array('Id', 'Name', 'Description', 'Shared', 'Min Montly', 'ChargeCap', 'UsageCap', 'Start Date', 'End Date'));
		foreach($arrRatePlans AS $arrRatePlan)
		{
			$arrRow = Array($arrRatePlan['Id'], $arrRatePlan['Name'], $arrRatePlan['Description'], $arrRatePlan['Shared'], $arrRatePlan['MinMonthly'], $arrRatePlan['ChargeCap'], $arrRatePlan['UsageCap'], $arrRatePlan['StartDateTime'], $arrRatePlan['EndDateTime']);
			$tblRatePlan->AddRow($arrRow);
		}
		$objPage->AddTable($tblRatePlan);
	}
	else
	{
		$objPage->AddError("NO RatePlans FOUND");
	}
}
else
{
	$objPage->AddError("NO Service Selected");
}

// display the page
$objPage->Render();

?>
