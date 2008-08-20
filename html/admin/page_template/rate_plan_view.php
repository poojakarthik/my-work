<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_plan_view.php
//----------------------------------------------------------------------------//
/**
 * rate_plan_view.php
 *
 * Page Template for the rate_plan_view webpage
 *
 * Page Template for the rate_plan_view webpage
 *
 * @file		rate_plan_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
$this->Page->SetName("Rate Plan Details");

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('PlanDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "PlanDetailsDiv");
$this->Page->AddObject('PlanRateGroupDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "PlanRateGroupDetailsDiv");

?>
