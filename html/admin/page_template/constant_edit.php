<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// constant_edit.php
//----------------------------------------------------------------------------//
/**
 * constant_edit
 *
 * Page Template for the Add/Edit Constant popup window
 *
 * Page Template for the Add/Edit Constant popup window
 *
 * @file		constant_edit.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Set the page title (This should already be set)
if (DBO()->ConfigConstant->Id->Value)
{
	$this->Page->SetName("Edit Constant");
}
else
{
	$this->Page->SetName("Create New Constant");
}

$this->Page->AddObject('ConfigConstantEdit', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "ConfigConstantEditDiv");

?>
