<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recurring_charge_add.php
//----------------------------------------------------------------------------//
/**
 * recurring_charge_add
 *
 * Page Template for the Add Recurring Charge popup window
 *
 * Page Template for the Add Recurring Charge popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		recurring_charge_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
if (DBO()->Service->Id->Value)
{
	$this->Page->SetName("Request Recurring Service Charge - Service: ". DBO()->Service->FNN->Value);
}
else
{
	$this->Page->SetName('Request Recurring Charge');
}

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// add the Html Objects to their respective columns
$this->Page->AddObject('RecurringChargeAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
