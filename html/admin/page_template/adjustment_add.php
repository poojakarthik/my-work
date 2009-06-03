<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// adjustment_add.php
//----------------------------------------------------------------------------//
/**
 * adjustment_add
 *
 * Page Template for the Add Adjustment popup window
 *
 * Page Template for the Add Adjustment popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		adjustment_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
if (DBO()->Service->Id->Value)
{
	$this->Page->SetName('Request Service Adjustment - Service: '. DBO()->Service->FNN->Value);
}
else
{
	$this->Page->SetName('Request Adjustment');
}

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// add the Html Objects to their respective columns
$this->Page->AddObject('AdjustmentAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "AddAdjustmentDiv");

?>
