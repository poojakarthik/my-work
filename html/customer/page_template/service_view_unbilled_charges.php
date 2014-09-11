<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_view_unbilled_charges.php
//----------------------------------------------------------------------------//
/**
 * service_view_unbilled_charges
 *
 * Page Template for the client app "View Unbilled Charges for a given Service" page
 *
 * Page Template for the client app "View Unbilled Charges for a given Service" page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		service_view_unbilled_charges.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// Set the page title
$this->Page->SetName('Unbilled Charges for Service# '. DBO()->Service->FNN->Value);

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//Only add the list of unbilled Charges if on the first page
if (DBO()->Page->CurrentPage->Value == 1)
{
	$this->Page->AddObject('UnbilledChargeList', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "ChargeListDiv");
}

$this->Page->AddObject('ServiceCDRList', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "CDRListDiv");




?>
