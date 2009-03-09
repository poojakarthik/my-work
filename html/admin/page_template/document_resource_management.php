<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_resource_management.php
//----------------------------------------------------------------------------//
/**
 * document_resource_management
 *
 * Page Template for the "Document Resource Management" webpage
 *
 * Page Template for the "Document Resource Management" webpage
 *
 * @file		document_resource_management.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("Document Resources - ". DBO()->CustomerGroup->internal_name->Value);

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('DocumentResourceManagement', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
