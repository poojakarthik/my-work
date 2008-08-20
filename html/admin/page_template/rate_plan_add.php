<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_plan_add.php
//----------------------------------------------------------------------------//
/**
 * rate_plan_add.php
 *
 * Page Template for the rate_plan_add webpage
 *
 * Page Template for the rate_plan_add webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 *
 * @file		rate_plan_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
if (DBO()->RatePlan->Id->Value > 0)
{
	// We are displaying a draft Rate Plan
	$strPageName = "Edit Draft Rate Plan";
}
else
{
	// We want to add a new rate plan
	$strPageName = "Add Rate Plan";
}

$this->Page->SetName($strPageName);

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('PlanAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "PlanDiv");

?>
