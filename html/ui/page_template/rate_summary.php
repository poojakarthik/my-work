<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_summary.php
//----------------------------------------------------------------------------//
/**
 * rate_summary.php
 *
 * Page Template for the Rate Summary popup window
 *
 * Page Template for the Rate Summary popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		rate_summary.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
$id = $this->Page->AddObject('RateSummary', COLUMN_ONE, HTML_CONTEXT_DEFAULT, 'RateSummaryDiv');

?>
