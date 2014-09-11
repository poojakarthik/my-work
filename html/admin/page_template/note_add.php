<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// note_add.php
//----------------------------------------------------------------------------//
/**
 * note_add
 *
 * Page Template for the Add Note popup window
 *
 * Page Template for the Add Note popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		note_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

if (DBO()->Service->Id->IsSet)
{
	$strPageName = "Service Note - " . GetConstantDescription(DBO()->Service->ServiceType->Value, "service_type") . " - " . DBO()->Service->FNN->Value;
}
elseif (DBO()->Contact->Id->IsSet)
{
	$strPageName = "Contact Note";
}
else
{
	$strPageName = "Account Note";
}

$this->Page->SetName($strPageName);

$this->Page->AddObject('NoteAdd', COLUMN_ONE, HTML_CONTEXT_POPUP, "AddNoteDiv");

?>
