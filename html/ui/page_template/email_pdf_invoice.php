<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// email_pdf_invoice.php
//----------------------------------------------------------------------------//
/**
 * email_pdf_invoice
 *
 * Page Template for the Email PDF Invoice popup window
 *
 * Page Template for the Email PDF Invoice popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		email_pdf_invoice.php
 * @language	PHP
 * @package		ui_app
 * @author		Nathan Abussi
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
$this->Page->SetName('Email Invoice PDF');

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// add the Html Objects to their respective columns
$this->Page->AddObject('InvoiceEmail', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
