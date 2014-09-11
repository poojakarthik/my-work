<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_add.php
//----------------------------------------------------------------------------//
/**
 * rate_add.php
 *
 * Page Template for the Add Rate popup window
 *
 * Page Template for the Add Rate popup window
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		rate_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
$id = $this->Page->AddObject('RateAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT, 'RateAddDiv');

?>
