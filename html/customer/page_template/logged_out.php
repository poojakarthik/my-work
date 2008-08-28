<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// logged_out.php
//----------------------------------------------------------------------------//
/**
 * logged_out
 *
 * Page Template for the client app "Logged Out" page
 *
 * Page Template for the client app "Logged Out" page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		logged_out.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// Set the page title
//$this->Page->SetName('Logout');
$this->Page->SetName('');

$this->Page->SetLayout('logout_layout');

$this->Page->AddObject('LoggedOut', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "LoggedOutDiv");

?>
