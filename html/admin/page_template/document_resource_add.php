<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_resource_add.php
//----------------------------------------------------------------------------//
/**
 * document_resource_add.php
 *
 * Page Template for the Add Document Resource popup window
 *
 * Page Template for the Add Document Resource popup window
 *
 * @file		document_resource_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the title of the popup
$this->Page->SetName("New Resource - ". DBO()->DocumentResourceType->PlaceHolder->Value);

// Set the layout template for the page
$this->Page->SetLayout('popup_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('DocumentResourceAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
