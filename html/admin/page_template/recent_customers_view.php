<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recent_customers_view.php
//----------------------------------------------------------------------------//
/**
 * recent_customers_view
 *
 * Page Template for the Recent Customers popup window
 *
 * Page Template for the Recent Customers popup window
 *
 * @file		recent_customers_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->Page->SetName('Recent Customers');

$this->Page->SetLayout('popup_layout');

$this->Page->AddObject('EmployeeRecentCustomerList', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "RecentCustomerListDiv");

?>
