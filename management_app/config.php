<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DEFINITIONS
//----------------------------------------------------------------------------//
/**
 * config
 *
 * ApplicationConfig Definitions
 *
 * This file exclusively declares application config constants
 *
 * @file		config.php
 * @language	PHP
 * @package		management_app
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//
$arrConfig = Array();

// Lock Manager
$arrLockConfig = Array();

$arrMenu = Array();
$arrMenu[LOCK_ADD]	['Name']	= "Add a New Page Lock";
$arrMenu[LOCK_VIEW]	['Name']	= "View/Manage Active Page Locks";
$arrMenu[MENU_EXIT]	['Name']	= "Exit";
$arrLockConfig['MainMenu'] = $arrMenu;

$arrConfig['Application']['LockManager'] = $arrLockConfig;



?>