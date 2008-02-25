<?php

require_once("../../flex.require.php");

//----------------------------------------------------------------------------//
// DIRECTORY CONFIG
//----------------------------------------------------------------------------//

$strDefaultOwner	= "www-data";
$strDefaultGroup	= "www-data";
$strDefaultPerms	= "755";

$arrDirectory = Array();
$arrDirectory['**Perms']						= "755";
$arrDirectory['**Owner']						= "www-data";
$arrDirectory['**Group']						= "www-data";


// Download
$arrDirectory['download']		['unzip']		= Array();

// Import
$arrDirectory['import']			['aapt']		= Array();
$arrDirectory['import']			['iseek']		= Array();
$arrDirectory['import']			['optus']		= Array();
$arrDirectory['import']			['telstra']		= Array();
$arrDirectory['import']			['unitel']		= Array();
$arrDirectory['import']			['vodafone']	= Array();

// Upload
$arrDirectory['upload']			['aapt']		= Array();
$arrDirectory['upload']			['iseek']		= Array();
$arrDirectory['upload']			['optus']		= Array();
$arrDirectory['upload']			['telstra']		= Array();
$arrDirectory['upload']			['unitel']		= Array();
$arrDirectory['upload']			['vodafone']	= Array();

// Bill Output
$arrDirectory['bill_output']	['**Perms']		= "775";
$arrDirectory['bill_output']	['**Group']		= "mysql";
$arrDirectory['bill_output']	['sample']		= Array();
$arrDirectory['bill_output']	['pdf']			= Array();

// Invoices
$arrDirectory['invoices']						= Array();

// Log
$arrDirectory['log']							= Array();


//----------------------------------------------------------------------------//
// DIRECTORY CREATION
//----------------------------------------------------------------------------//

// Create Directories
CliEcho("[ DIRECTORY SETUP ]\n");

CliEcho(" * Creating viXen directories for '$strCustomer'...");
CreateDir($arrDirectory, FILES_BASE_PATH);
CliEcho(" # Directory Setup complete!");

die;



//----------------------------------------------------------------------------//
// CreateDir
//----------------------------------------------------------------------------//
function CreateDir($arrDirectories, $strParentDirectory, $strPerms='777', $strOwner='root', $strGroup='root')
{
	// Permissions and Ownership
	$strPerms	= ($arrDirectories['**Perms']) ? $arrDirectories['**Perms'] : $strPerms;
	$strOwner	= ($arrDirectories['**Owner']) ? $arrDirectories['**Owner'] : $strOwner;
	$strGroup	= ($arrDirectories['**Group']) ? $arrDirectories['**Group'] : $strGroup;
	
	foreach ($arrDirectories as $strDirectory=>$arrSubDirectories)
	{
		// Ignore Properties
		if (is_int(stripos($strDirectory, '**')))
		{
			continue;
		}
		
		$strFullPath	= $strParentDirectory.$strDirectory.'/';
		
		// Create this directory
		CliEcho("\t + Creating '$strFullPath'...");
		@mkdir($strFullPath, $strPerms);
		@chown($strFullPath, $strOwner);
		@chgrp($strFullPath, $strGroup);
		
		// Create Subdirectories
		CreateDir($arrSubDirectories, $strFullPath, $strPerms, $strOwner, $strGroup);
	}
}

?>