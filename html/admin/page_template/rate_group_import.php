<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import.php
//----------------------------------------------------------------------------//
/**
 * rate_group_import.php
 *
 * Page Template for the Import RateGroup popup window
 *
 * Page Template for the Import RateGroup popup window
 *
 * @file		rate_group_import.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the title of the popup
$strPopupTitle = (DBO()->RateGroup->Fleet->Value) ? "Import Fleet Rate Group" : "Import Rate Group";
$this->Page->SetName($strPopupTitle);

// Set the layout template for the page
$this->Page->SetLayout('popup_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('RateGroupImport', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "RateGroupImportDiv");

?>
