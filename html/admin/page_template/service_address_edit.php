<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_address_edit.php
//----------------------------------------------------------------------------//
/**
 * service_address_edit.php
 *
 * Page Template for the Edit Service Address popup window
 *
 * Page Template for the Edit Service Address popup window
 *
 * @file		service_address_edit.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
$this->Page->SetName('Service Address - '. DBO()->Service->FNN->Value);

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
$this->Page->AddObject('ServiceAddressEdit', COLUMN_ONE, HTML_CONTEXT_POPUP);

?>
