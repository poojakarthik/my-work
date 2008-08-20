<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// view_sample_pdf.php
//----------------------------------------------------------------------------//
/**
 * view_sample_pdf
 *
 * Page Template for the Sample PDF popup window
 *
 * Page Template for the Sample PDF popup window
 *
 * @file		view_sample_pdf.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->Page->SetLayout('popup_layout');

$this->Page->AddObject('DocumentTemplateSamplePDF', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
