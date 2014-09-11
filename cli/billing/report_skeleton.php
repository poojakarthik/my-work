<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// report_skeleton
//----------------------------------------------------------------------------//
/**
 * report_skeleton
 *
 * Billing App Report Skeletons
 *
 * Billing App Report Skeletons
 *
 * @file		report_skeleton.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste' Davis
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
$arrConfig['ReportSkeleton']	= Array();


//----------------------------------------------------------------------------//
// Billing::Execute
//----------------------------------------------------------------------------//
$arrReport = Array();

$arrReport['Title']			= "Billing::Execute";		// <Application>::<Process>
$arrReport['DefaultMode']	= REPORT_MODE_VERBOSE;
$arrReport['Section']		= Array();

	// Init
	$arrSection			= Array();
	
	$arrSection['Task']	= Array();
	$arrSection['Task']['Revoke']	['Text']		= "Force Billing::Revoke";
	
$arrReport['Section']['Init'] = $arrSection;

	// Generate Invoices
	$arrSection				= Array();
	
		// Account
		$arrProperty			= Array();
		$arrProperty['Title']	= "Account #<Account>";
		
		$arrProperty['Task']['LinkCDRs']			['Text']		= "Linking CDRs";
		$arrProperty['Task']['ServiceTypeTotals']	['Text']		= "Generating ServiceTypeTotals";
		$arrProperty['Task']['ServiceData']			['Text']		= "Retrieving Service Data";
		
	
	
	$arrSection['Property']['Account']	= $arrProperty;


$arrReport['Section']['Generate Invoices'] = $arrSection;
?>