<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// delinquent_cdrs_move.php
//----------------------------------------------------------------------------//
/**
 * delinquent_cdrs_move
 *
 * Page Template for the delinquent_cdrs_move webpage
 *
 * Page Template for the delinquent_cdrs_move webpage
 *
 * @file		delinquent_cdrs_move.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->Page->SetName("Delinquent CDRs");

// set the layout template for the page
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('DelinquentCDRs', COLUMN_ONE);

?>
