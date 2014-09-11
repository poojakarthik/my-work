<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// survey.php
//----------------------------------------------------------------------------//
/**
 * survey
 *
 * Page Template for the client app survey page
 *
 * Page Template for the client app survey page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		survey.php
 * @language	PHP
 * @package		web_app
 * @author		Ryan Forrester
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('4column');

// add the Html Objects to their respective columns
//$this->Page->AddObject('Console', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('CustomerSurvey', COLUMN_TWO, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ConsoleOptions', COLUMN_THREE, HTML_CONTEXT_DEFAULT);

?>
