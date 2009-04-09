<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// invoices_and_payments.php
//----------------------------------------------------------------------------//
/**
 * invoices_and_payments
 *
 * Page Template for the invoices_and_payments webpage
 *
 * Page Template for the invoices_and_payments webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		invoices_and_payments.php
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
$this->Page->SetLayout('3Column_65_35');

// add the Html Objects to their respective columns
$this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_VIEW);
$this->Page->AddObject('AccountContactsList', COLUMN_ONE, HTML_CONTEXT_PAGE);
$this->Page->AddObject('InvoiceList', COLUMN_ONE, HTML_CONTEXT_LEDGER_DETAIL);

//DEPRECATED! Old Notes Functionality
//$this->Page->AddObject('NoteList', COLUMN_ONE, HTML_CONTEXT_PAGE, "NoteListDiv");

$this->Page->AddObject('ActionsAndNotesList', COLUMN_ONE, HTML_CONTEXT_PAGE, "ActionsAndNotesListDiv");
$this->Page->AddObject('AccountPaymentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
$this->Page->AddObject('AdjustmentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
$this->Page->AddObject('RecurringAdjustmentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);

?>
