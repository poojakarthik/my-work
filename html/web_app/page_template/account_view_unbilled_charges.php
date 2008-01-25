<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_view_unbilled_charges.php
//----------------------------------------------------------------------------//
/**
 * account_view_unbilled_charges
 *
 * Page Template for the client app View Unbilled Charges page
 *
 * Page Template for the client app View Unbilled Charges page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		account_view_unbilled_charges.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// Set the page title
$this->Page->SetName('Unbilled Charges for Account# '. DBO()->Account->Id->Value);

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//$this->Page->AddObject('AccountUnbilledChargeTotal', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('UnbilledChargeList', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('AccountServiceList', COLUMN_ONE, HTML_CONTEXT_DEFAULT);




?>
