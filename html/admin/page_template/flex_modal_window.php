<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// flex_modal_window.php
//----------------------------------------------------------------------------//
/**
 * flex_modal_window
 */

// set the layout template for the page.
$GLOBALS['*arrJavaScript'][] = "vixen_modal";
$this->Page->SetLayout('flex_modal_window');

// add the Html Objects to their respective columns
//$this->Page->AddObject('EmployeeEdit', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);

?>
