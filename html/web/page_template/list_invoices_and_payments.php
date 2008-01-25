<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// list_invoices_and_payments.php
//----------------------------------------------------------------------------//
/**
 * list_invoices_and_payments
 *
 * Page Template for the client app "List Invoices and Payments for a given account" page
 *
 * Page Template for the client app "List Invoices and Payments for a given account" page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		list_invoices_and_payments.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// Set the page title
$this->Page->SetName('Invoices and Payments for Account# '. DBO()->Account->Id->Value);

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
$this->Page->AddObject('InvoiceAndPaymentList', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "CDRListDiv");




?>
