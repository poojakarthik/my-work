<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_view.php
//----------------------------------------------------------------------------//
/**
 * rate_group_view.php
 *
 * Page Template for the View RateGroup popup window
 *
 * Page Template for the View RateGroup popup window
 *
 * @file		rate_group_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
$this->Page->SetName('Rate Group');

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
$this->Page->AddObject('RateGroupView', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
