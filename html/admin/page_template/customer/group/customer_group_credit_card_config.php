<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_group_view.php
//----------------------------------------------------------------------------//
/**
 * customer_group_view
 *
 * Page Template for the "View Customer Group" webpage
 *
 * Page Template for the "View Customer Group" webpage
 *
 * @file		customer_group_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$customerGroupName = ($mxdDataToRender && array_key_exists('customerGroup', $mxdDataToRender) && $mxdDataToRender['customerGroup']) ? ' ' . $mxdDataToRender['customerGroup']->name : '';

$this->Page->SetName("Customer Group$customerGroupName - Secure Pay Configuration");
$this->Page->SetLayout('full_area');
$this->Page->AddObject('Customer_Group_Credit_Card_Config');

?>
