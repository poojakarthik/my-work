<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_override.php
//----------------------------------------------------------------------------//
/**
 * rate_group_override.php
 *
 * Page Template for the Rate Group Override popup window
 *
 * Page Template for the Rate Group Override popup window
 *
 * @file		rate_group_override.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the title of the popup
$this->Page->SetName("Override Rate Group");

// Set the layout template for the page
$this->Page->SetLayout('popup_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('RateGroupOverride', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "RateGroupOverrideDiv");

?>
