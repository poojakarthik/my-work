<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// view_notes.php
//----------------------------------------------------------------------------//
/**
 * view_notes
 *
 * Page Template for the View Notes popup window
 *
 * Page Template for the View Notes popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		view_notes.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
switch (DBO()->Note->NoteClass->Value)
{	
	case NOTE_CLASS_ACCOUNT_NOTES:
		$this->Page->SetName('Account Notes');
		break;
	case NOTE_CLASS_CONTACT_NOTES:
		$this->Page->SetName('Contact Notes');
		break;
	case NOTE_CLASS_SERVICE_NOTES:
		$this->Page->SetName('Service Notes');
		break;
	default:
		$this->Page->SetName('Notes');
		break;
}

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// This is just an example of overriding the page style of the popup
$this->Page->SetStyleOverride("background-color : #F3F3F3");

// add the Html Objects to their respective columns
$this->Page->AddObject('NoteView', COLUMN_ONE, HTML_CONTEXT_POPUP);

?>
