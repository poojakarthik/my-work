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
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('4column');

// add the Html Objects to their respective columns
//$this->Page->AddObject('Console', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('AccountDetails', COLUMN_TWO, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ConsoleOptions', COLUMN_THREE, HTML_CONTEXT_DEFAULT);

// Only list all the user's available accounts if there is more than 1
if (DBL()->Account->RecordCount() > 1)
{
	$this->Page->AddObject('AccountList', COLUMN_FOUR, HTML_CONTEXT_DEFAULT);
}

?>
