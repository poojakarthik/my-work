<?php

// Carrier (lower case)
$strCarrier	= "optus";

// Include Framework
if (!@include("../../flex.require.php"))
{
	require_once('../../lib/framework/require.php');
}

if (!defined('FILES_BASE_PATH'))
{
	define('FILES_BASE_PATH', '/home/vixen_');
}

// Statements
$selFileImport	= new StatementSelect("FileImport", "*", "Location = <Location> AND Status = 207");

// Get list of files in the Carrier directory
$strImportDirectory	= FILES_BASE_PATH."import/$strCarrier/";
$arrList			= glob($strImportDirectory.'*');
$intTotal			= count($arrList);
$intInvalid			= 0;
$intValid			= 0;
$i					= 0;
foreach ($arrList as $strFilename)
{
	$i++;
	
	// Check FileImport status
	if (!$selFileImport->Execute(Array('Location' => $strFilename)))
	{
		CliEcho(" + ($i/$intTotal) Deleting '".basename($strFilename)."'...");
		
		// Delete file
		//@unlink($strFilename);
		$intInvalid++;
	}
	else
	{
		// Skip file
		$intValid++;
	}
}

CliEcho("\nDeleted $intInvalid of $intTotal files\n");
?>