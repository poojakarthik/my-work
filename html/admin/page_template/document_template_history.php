<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_template_history.php
//----------------------------------------------------------------------------//
/**
 * document_template_history
 *
 * Page Template for the "View Document Template History" webpage
 *
 * Page Template for the "View Document Template History" webpage
 *
 * @file		document_template_history.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("Template History - ". DBO()->DocumentTemplateType->Name->Value);

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('DocumentTemplateHistory', COLUMN_ONE);

?>
