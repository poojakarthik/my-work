<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_view.php
//----------------------------------------------------------------------------//
/**
 * rate_view.php
 *
 * Page Template for the View Rate popup window
 *
 * Page Template for the View Rate popup window
 *
 * @file		rate_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
$this->Page->SetName('Rate');

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
$this->Page->AddObject('RateView', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
