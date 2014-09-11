<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import_component.php
//----------------------------------------------------------------------------//
/**
 * rate_group_import_component.php
 *
 * Page Template for the Embedded component of the Import RateGroup popup window
 *
 * Page Template for the Embedded component of the Import RateGroup popup window
 *
 * @file		rate_group_import_component.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the layout template for the page
$this->Page->SetLayout('embedded_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('RateGroupImportComponent', COLUMN_ONE, HTML_CONTEXT_IFRAME, "RateGroupImportComponentDiv");

?>
