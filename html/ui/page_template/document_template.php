<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_template.php
//----------------------------------------------------------------------------//
/**
 * document_template
 *
 * Page Template for the document template Add/Edit/View webpages
 *
 * Page Template for the document template Add/Edit/View webpages
 *
 * @file		document_template.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->Page->SetName("Template - ". DBO()->DocumentTemplateType->Name->Value ." - Version ". DBO()->DocumentTemplate->Version->Value);

// set the layout template for the page
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('DocumentTemplate', COLUMN_ONE, DBO()->Render->Context->Value);

?>
