<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// new_customer
//----------------------------------------------------------------------------//
/**
 * new_customer
 *
 * New Customer Setup Script
 *
 * New Customer Setup Script
 *
 * @file		new_customer.php
 * @language	PHP
 * @package		setup_scripts
 * @author		Rich 'Waste01' Davis
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require("../framework/require.php");

// Check for customer parameter
if (!($strCustomer = trim($argv[1])))
{
	Debug("Please specify a Customer name as the first parameter!");
	die;
}

// Download
$arrDirectory['download']	['Owner']	= "www-data";
$arrDirectory['download']	['Group']	= "www-data";
$arrDirectory['download']	['Perms']	= "700";
$arrDirectory['download']	['SubDir']	[]	= "unzip";

// Import
$arrDirectory['import']	['Owner']	= "www-data";
$arrDirectory['import']	['Group']	= "www-data";
$arrDirectory['import']	['Perms']	= "700";
$arrDirectory['import']	['SubDir']	[]	= "aapt";
$arrDirectory['import']	['SubDir']	[]	= "iseek";
$arrDirectory['import']	['SubDir']	[]	= "optus";
$arrDirectory['import']	['SubDir']	[]	= "telstra";
$arrDirectory['import']	['SubDir']	[]	= "unitel";
$arrDirectory['import']	['SubDir']	[]	= "vodafone";

// Upload
$arrDirectory['upload']	['Owner']	= "www-data";
$arrDirectory['upload']	['Group']	= "www-data";
$arrDirectory['upload']	['Perms']	= "700";
$arrDirectory['upload']	['SubDir']	[]	= "aapt";
$arrDirectory['upload']	['SubDir']	[]	= "iseek";
$arrDirectory['upload']	['SubDir']	[]	= "optus";
$arrDirectory['upload']	['SubDir']	[]	= "telstra";
$arrDirectory['upload']	['SubDir']	[]	= "unitel";
$arrDirectory['upload']	['SubDir']	[]	= "unitel/dailyorderfiles";
$arrDirectory['upload']	['SubDir']	[]	= "unitel/preselectionfiles";
$arrDirectory['upload']	['SubDir']	[]	= "vodafone";

// Bill Output
$arrDirectory['bill_output']	['Owner']	= "www-data";
$arrDirectory['bill_output']	['Group']	= "mysql";
$arrDirectory['bill_output']	['Perms']	= "770";
$arrDirectory['bill_output']	['SubDir']	[]	= "sample";
$arrDirectory['bill_output']	['SubDir']	[]	= "pdf";

// Invoices
$arrDirectory['invoices']	['Owner']	= "www-data";
$arrDirectory['invoices']	['Group']	= "www-data";
$arrDirectory['invoices']	['Perms']	= "777";

// Log
$arrDirectory['log']	['Owner']	= "www-data";
$arrDirectory['log']	['Group']	= "www-data";
$arrDirectory['log']	['Perms']	= "744";
$arrDirectory['log']	['SubDir']	[]	= "billing_app";
$arrDirectory['log']	['SubDir']	[]	= "charges_app";
$arrDirectory['log']	['SubDir']	[]	= "collection_app";
$arrDirectory['log']	['SubDir']	[]	= "master";
$arrDirectory['log']	['SubDir']	[]	= "mistress";
$arrDirectory['log']	['SubDir']	[]	= "normalisation_app";
$arrDirectory['log']	['SubDir']	[]	= "payment_app";
$arrDirectory['log']	['SubDir']	[]	= "provisioning_app";
$arrDirectory['log']	['SubDir']	[]	= "rating_app";

$strHomeBase	= "/home/vixen/$strCustomer";

// Header
CliEcho("+-----------------------------+");
CliEcho("| viXen Directory Setup v7.08 |");
CliEcho("+-----------------------------+");

// Create Directories
Debug("Creating viXen directories for '$strCustomer'...");
foreach ($arrDirectory as $strDir=>$arrProperties)
{
	// Create parent dir
	CliEcho("\nCreating directory '$strHomeBase/$strDir'...");
	//mkdir("$strHomeBase/$strDir");
	
	// Create subdirectories
	if ($arrProperties['SubDir'])
	{
		foreach ($arrProperties['SubDir'] as $strSubDir)
		{
			// Create sub dir
			CliEcho("\tCreating subdirectory '$strHomeBase/$strDir/$strSubDir'...");
			//mkdir("$strHomeBase/$strDir/$strSubDir");
		}
	}
	
	// Set ownership and permissions
	CliEcho("Setting '$strDir' Owner and Permissions...");
	//shell_exec("chown -R {$arrProperties['Owner']}.{$arrProperties['Group']} $strHomeBase/$strDir");
	//shell_exec("chmod -R {$arrProperties['Perms']} $strHomeBase/$strDir");
}

Debug("Setup complete!");
?>