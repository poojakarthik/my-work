<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// employee_list.php
//----------------------------------------------------------------------------//
/**
 * employee_list

 */

// set the page title
$this->Page->SetName('Viewing all Employees');

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('EmployeeView', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);
?>
