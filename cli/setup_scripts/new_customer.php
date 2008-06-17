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

$GLOBALS['**arrDatabase']['flex']['User']		= "root";
$GLOBALS['**arrDatabase']['flex']['Password']	= "zeemu";

require("../../flex.require.php");

// Check for customer parameter
if (!($strCustomer = trim($argv[1])) || !preg_match("/^([A-Za-z0-9])*(_([A-Za-z0-9])+)*$/", $strCustomer))
{
	CliEcho("\nPlease specify a vaild Customer name as the first parameter! (You entered '$strCustomer')\n" .
			"Valid Customers names are alphlanumeric with words optionally separated by underscores (eg. 'telcoblue', 'yellow_billing101')\n");
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
$arrDirectory['log']	['Perms']	= "700";
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
$strVixenBase	= "/usr/share/vixen/customers/$strCustomer";

// Header
CliEcho("\t\t+----------------------------+");
CliEcho("\t\t| viXen Customer Setup v7.08 |");
CliEcho("\t\t+----------------------------+");

//----------------------------------------------------------------------------//
// Config Setup
//----------------------------------------------------------------------------//
Debug("[ CONFIG FILE SETUP ]");
Debug("Creating Customer Config File '/etc/vixen/$strCustomer'");

// Is there aready a config (and therefore an instance)
if (file_exists("/etc/vixen/$strCustomer.conf"))
{
	CliEcho("!!WARNING!! Instance '$strCustomer' already exists. Overwrite?  You will lose ALL data if you continue (y/[N])? ", FALSE);
	while (true)
	{
		$strInput = trim(strtoupper(fgets(STDIN)));
		if ($strInput == 'Y')
		{
			// Overwrite
			Debug("Overwriting previous config...");
			break;
		}
		elseif ($strInput == 'N' || !$strInput)
		{
			// exit
			Debug("Customer Setup Aborted by User!");
			die;
		}
		else
		{
			CliEcho("Invalid Input ('$strInput')! Overwrite (y/[N])? ", FALSE);
		}
	}
}

// Open template & export files
$ptrTemplate	= fopen("customer.conf", 'r');
$ptrExport		= fopen("/etc/vixen/$strCustomer.conf", 'w');

// write each line to new file, filling in placeholders
while ($strLine = fgets($ptrTemplate))
{
	fwrite($ptrExport, str_replace('<customer>', $strCustomer, $strLine));
}

// Close files
fclose($ptrTemplate);
fclose($ptrExport);

//----------------------------------------------------------------------------//
// Directory Setup
//----------------------------------------------------------------------------//

// Create Directories
Debug("[ DIRECTORY SETUP ]");

Debug(" * Clearing viXen directories for '$strCustomer'...");
foreach ($arrDirectory as $strDir=>$arrProperties)
{	
	// Remove subdirectories
	if ($arrProperties['SubDir'])
	{
		// Remove sub dirs in reverse order
		$strSubDir = end($arrProperties['SubDir']);
		while ($strSubDir)
		{
			// Remove dir, and get next dir to remove
			@unlink("$strHomeBase/$strDir/$strSubDir/*");
			@rmdir("$strHomeBase/$strDir/$strSubDir");
			$strSubDir = prev($arrProperties['SubDir']);
		}
	}
	
	// Remove parent dir
	@unlink("$strHomeBase/$strDir/*");
	@rmdir("$strHomeBase/$strDir");
}


Debug(" * Creating viXen directories for '$strCustomer'...");
@unlink("$strHomeBase/*");
@rmdir("$strHomeBase");
mkdir("$strHomeBase/");
foreach ($arrDirectory as $strDir=>$arrProperties)
{
	// Create parent dir
	CliEcho("\n\t + Creating directory '$strHomeBase/$strDir/'...");
	mkdir("$strHomeBase/$strDir");
	
	// Create subdirectories
	if ($arrProperties['SubDir'])
	{
		foreach ($arrProperties['SubDir'] as $strSubDir)
		{			
			// Create sub dir
			CliEcho("\t\t + Creating subdirectory '$strHomeBase/$strDir/$strSubDir'...");
			mkdir("$strHomeBase/$strDir/$strSubDir");
		}
	}
	
	// Set ownership and permissions
	CliEcho("\t\t * Setting '$strDir' Owner and Permissions...");
	shell_exec("chown -R {$arrProperties['Owner']}.{$arrProperties['Group']} $strHomeBase/$strDir");
	shell_exec("chmod -R {$arrProperties['Perms']} $strHomeBase/$strDir");
}

Debug(" * Creating viXen interface directories for '$strCustomer'...");
mkdir("$strVixenBase/");
mkdir("$strVixenBase/ui_app");
mkdir("$strVixenBase/web");
mkdir("$strVixenBase/intranet");
shell_exec("chown -R www-data.www-data $strVixenBase");
shell_exec("chmod -R 777 $strVixenBase");

Debug(" # Directory Setup complete!");

//----------------------------------------------------------------------------//
// Database Setup
//----------------------------------------------------------------------------//
Debug("[ DATABASE SETUP ]");

// Check to see if database exists
$qryDBExists = new Query();
$resResult	 = $qryDBExists->Execute("SHOW DATABASES WHERE `Database` = '$strCustomer'");
if ($resResult->fetch_assoc())
{
	Debug("!!ERROR!! Database '$strCustomer' already exists! Please manually drop this database, and re-run this script.\n");
	die;
}

// Copy from template database
Debug("Creating new Database for '$strCustomer'...");

// Set up ListTables object
$qctCopyTable = new QueryListTables();

// Get tables from vixen_template
//$arrTables = $qctCopyTable->Execute('vixen_template');

// Set up CopyTable object
$qctCopyTable = new QueryCopyTable();

// Clean Tables List
foreach($arrTables AS $mixKey=>$strTable)
{
	// Copy a table
	//$qctCopyTable->Execute($strTable, "$strCustomer.$strTable");
}
?>