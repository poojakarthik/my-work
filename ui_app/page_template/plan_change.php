<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// plan_change.php
//----------------------------------------------------------------------------//
/**
 * plan_change
 *
 * Page Template for the Change Plan
 *
 * Page Template for the Change Plan
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		plan_change.php
 * @language	PHP
 * @package		ui_app
 * @author		Nathan Abussi
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
$this->Page->SetName('Change Plan');

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);
$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_MINIMUM_DETAIL);
$this->Page->AddObject('ServicePlanChange', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
?>
