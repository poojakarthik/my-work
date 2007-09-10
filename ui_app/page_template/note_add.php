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
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Render the NoteAdd HtmlTemplate in its appropriate context
if (DBO()->Service->Id->IsSet)
{
	$strPageName = "Add Service Note";
	$this->Page->AddObject('NoteAdd', COLUMN_ONE, HTML_CONTEXT_SERVICE_NOTE, "AddNoteDiv");
}
elseif (DBO()->Contact->Id->IsSet)
{
	$strPageName = "Add Contact Note";
	$this->Page->AddObject('NoteAdd', COLUMN_ONE, HTML_CONTEXT_CONTACT_NOTE, "AddNoteDiv");
}
else
{
	$strPageName = "Add Account Note";
	$this->Page->AddObject('NoteAdd', COLUMN_ONE, HTML_CONTEXT_ACCOUNT_NOTE, "AddNoteDiv");
}

// I don't even know why we are doing this because Page Names aren't displayed for popups.
// It would be nice to display the page name in the title bar
$this->Page->SetName($strPageName);


?>
