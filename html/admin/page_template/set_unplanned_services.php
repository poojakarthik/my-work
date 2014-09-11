<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// set_unplanned_services.php
//----------------------------------------------------------------------------//
/**
 * set_unplanned_services
 *
 * Page Template for the "Set Plans For Services That Currently Don't Have a Plan" webpage
 *
 * Page Template for the "Set Plans For Services That Currently Don't Have a Plan" webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		set_unplanned_services.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// set the page title
$this->Page->SetName("Services Without Plans");

$this->Page->SetLayout('1Column');

// add the HTML template objects to this page 
$this->Page->AddObject('ServicesWithoutPlans', COLUMN_ONE, HTML_CONTEXT_DEFAULT);


?>
