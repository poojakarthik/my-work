<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// delete_record.php
//----------------------------------------------------------------------------//
/**
 * delete_record
 *
 * Page Template for the Delete Record popup window
 *
 * Page Template for the Delete Record popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		delete_record.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Set the name
if (DBO()->DeleteRecord->Method->Value == "DeleteCharge")
{
	// Check if the charge is associated with a service
	$intServiceId = DBO()->Charge->Service->Value;
	if ($intServiceId != NULL)
	{
		$objService = Service::getForId($intServiceId);
		$this->Page->SetName("Charge - Service: {$objService->FNN}");
	}
}
elseif (DBO()->DeleteRecord->Method->Value == "DeleteRecurringCharge")
{
	// Check if the recurring charge is associated with a service
	$intServiceId = DBO()->RecurringCharge->Service->Value;
	if ($intServiceId != NULL)
	{
		$objService = Service::getForId($intServiceId);
		$this->Page->SetName("Recurring Charge - Service: {$objService->FNN}");
	}
}
else if (DBO()->DeleteRecord->Method->Value == "DeleteAdjustment")
{
	// Check if the charge is associated with a service
	$intServiceId = DBO()->Charge->Service->Value;
	if ($intServiceId != NULL)
	{
		$objService = Service::getForId($intServiceId);
		$this->Page->SetName("Adjustment - Service: {$objService->FNN}");
	}
}
else
{
	// Just use the predefined name
}

// add the Html Objects to their respective columns
$this->Page->AddObject('DeleteRecord', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
