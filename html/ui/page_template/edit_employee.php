<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// edit_employee.php
//----------------------------------------------------------------------------//
/**
 * edit_employee

 */

// set the page title
$this->Page->SetName('Employee');

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// add the Html Objects to their respective columns
//$this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_LEDGER_DETAIL);


$this->Page->AddObject('EmployeeEdit', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);

//$this->Page->AddObject('InvoiceList', COLUMN_ONE, HTML_CONTEXT_LEDGE_DETAIL);


//$this->Page->AddObject('AccountPaymentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
//$this->Page->AddObject('AdjustmentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
//$this->Page->AddObject('RecurringAdjustmentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);

?>
