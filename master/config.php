<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
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
 * This file exclusively declares application config
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// MASTER CONFIG
//----------------------------------------------------------------------------//

// MaxRuns
//		Int		Maximum number of runs performed before script exits
$arrConfig['MaxRuns']			= 100000;

// Sleep
//		Int		Time in seconds to sleep between runs
$arrConfig['Sleep']				= 30;

// Verbose
//		Bool	TRUE	Display output as the script runs
//				FALSE	Quiet mode
$arrConfig['Verbose']			= TRUE;



//----------------------------------------------------------------------------//
// SCRIPT CONFIG
//----------------------------------------------------------------------------//

/*
// Example Script
$arrScript 							= Array();
	
	// StartTime
	//		Int		Earliest time that the script can run during the day
	//				Time in seconds from 00:00:00
	$arrScript['StartTime']			=	0;
	
	// FinishTime
	//		Int		optional Latest time that the script can run during the day
	//				Time in seconds from 00:00:00
	//				Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']		=	86400;
	
	// Interval
	//		Int		Interval time in seconds. 
	//				Script will be run every Interval seconds.
	$arrScript['Interval']			=	3600;
	
	// Command
	//		String	Command to run the script (include full path to script).
	$arrScript['Command']			=	'\home\vixen\scripts\example_script.php';
	
	// Directory
	//		String	optional Directory to run the script in.
	$arrScript['Directory']			=	'\home\vixen\scripts\\';
	
	$arrConfig['Script']['ScriptName'] 	= $arrScript;
*/

// Test Script
$arrScript 							= Array();
	
	// StartTime
	//		Int		Earliest time that the script can run during the day
	//				Time in seconds from 00:00:00
	$arrScript['StartTime']			=	0;
	
	// FinishTime
	//		Int		optional Latest time that the script can run during the day
	//				Time in seconds from 00:00:00
	//				Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']		=	86400;
	
	// Interval
	//		Int		Interval time in seconds. 
	//				Script will be run every Interval seconds.
	$arrScript['Interval']			=	300;
	
	// Command
	//		String	Command to run the script (include full path to script).
	$arrScript['Command']			=	'php /home/flame/vixen/payment_app/payments.php';
	
	// Directory
	//		String	optional Directory to run the script in.
	$arrScript['Directory']			=	'/home/flame/vixen/payment_app/';
	
	$arrConfig['Script']['Payment'] 	= $arrScript;


?>
