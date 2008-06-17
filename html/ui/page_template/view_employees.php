<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// view_employees.php
//----------------------------------------------------------------------------//
/**
 * view_employees

 */

/*
 * NOT USED - REPLACED BY employee_list.php
 */

// set the page title
$this->Page->SetName('Viewing all Employees');

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
//$this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_LEDGER_DETAIL);


$this->Page->AddObject('EmployeeView', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);

//$this->Page->AddObject('InvoiceList', COLUMN_ONE, HTML_CONTEXT_LEDGE_DETAIL);


//$this->Page->AddObject('AccountPaymentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
//$this->Page->AddObject('AdjustmentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);
//$this->Page->AddObject('RecurringAdjustmentList', COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);

?>
