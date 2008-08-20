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

$this->Page->AddObject('EmployeeTable', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);

?>
