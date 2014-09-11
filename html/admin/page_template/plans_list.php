<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// plans_list.php
//----------------------------------------------------------------------------//
/**
 * plans_list
 *
 * Page Template for the "Available Plans" webpage
 *
 * Page Template for the "Available Plans" webpage
 *
 * @file		plans_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Nathan
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
if (DBO()->RatePlan->ServiceType->Value)
{
	// The filter has been used
	$strPageTitle = "Available " . GetConstantDescription(DBO()->RatePlan->ServiceType->Value, "service_type") . " Plans";
}
else
{
	// The user wants to view plans for all Service Types
	$strPageTitle = "Available Plans";
}

$this->Page->SetName($strPageTitle);

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('PlanList', COLUMN_ONE);


?>
