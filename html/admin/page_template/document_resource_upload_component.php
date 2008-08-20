<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_resource_upload_component.php
//----------------------------------------------------------------------------//
/**
 * document_resource_upload_component.php
 *
 * Page Template for the Embedded component of the Document Resource Upload functionality
 *
 * Page Template for the Embedded component of the Document Resource Upload functionality
 *
 * @file		document_resource_upload_component.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the layout template for the page
$this->Page->SetLayout('embedded_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('DocumentResourceAdd', COLUMN_ONE, HTML_CONTEXT_IFRAME, "ResourceUploadComponentDiv");

?>
