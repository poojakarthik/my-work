<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// console.php
//----------------------------------------------------------------------------//
/**
 * console
 *
 * Page Template for the client app console page
 *
 * Page Template for the client app console page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		console.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress'
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
$this->Page->SetName('Console');

// set the layout template for the page.
$this->Page->SetLayout('1column');

// add the Html Objects to their respective columns
$this->Page->AddObject('Console', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
