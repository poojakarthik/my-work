<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_ledger.php
//----------------------------------------------------------------------------//
/**
 * account_ledger
 *
 * Page Template for the account_ledger webpage
 *
 * Page Template for the account_ledger webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		account_ledger.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
$this->Page->SetName('Invoices and Payments');

// set the layout template for the page.
$this->Page->SetLayout('2Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);
$this->Page->AddObject('AccountOptions', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);
$this->Page->AddObject('AccountInvoices', COLUMN_ONE, HTML_CONTEXT_LEDGER_DETAIL);
$this->Page->AddObject('AccountPayments', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
$this->Page->AddObject('AccountAdjustments', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
$this->Page->AddObject('AccountRecurringAdjustments', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);

?>
