<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// plans_list.php
//----------------------------------------------------------------------------//
/**
 * plans_list
 *
 * Page Template for the "Available Plans" webpage
 *
 * Page Template for the "Available Plans" webpage
 *
 * @file		plans_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Nathan
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
$this->Page->SetName('Available Plans');

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add the Html Objects to their respective columns
$this->Page->AddObject('PlanList', COLUMN_ONE);


?>
