<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_add.php
//----------------------------------------------------------------------------//
/**
 * charge_add
 *
 * Page Template for the Add Charge popup window
 *
 * Page Template for the Add Charge popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		charge_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$sChargeModel	= Constant_Group::getConstantGroup('charge_model')->getConstantName(DBO()->ChargeModel->Id->Value);

if (DBO()->Service->Id->Value)
{
	$this->Page->SetName("Request Service {$sChargeModel} - Service: ". DBO()->Service->FNN->Value);
}
else
{
	$this->Page->SetName("Request {$sChargeModel}");
}

// Set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ChargeAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "AddChargeDiv");

?>
