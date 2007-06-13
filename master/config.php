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
$arrConfig['Sleep']				= 60;

// Verbose
//		Bool	TRUE	Display output as the script runs
//				FALSE	Quiet mode
$arrConfig['Verbose']			= TRUE;


$arrConfig['GMTOffset']			= 10;


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



// Payments
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       25200;
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       64800;
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       300;
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/payment_app/payments.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/payment_app/';
	
	$arrConfig['Script']['Payment']         = $arrScript;

// Collection
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       25200;
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       72000;
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       3600;
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/collection_app/collection.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/collection_app/';
	
	$arrConfig['Script']['Collection']         = $arrScript;


// Provisioning Export
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       25200;	// 0700
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       72000;	// 2000
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       3600;	// 1 hour
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/provisioning_app/provisioning_export.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/provisioning_app/';
	
	$arrConfig['Script']['ProvisioningExport']         = $arrScript;



// Report Execute
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       25200;	// 0700
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       1800;	// 30 mins
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/report_app/report_execute.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/report_app/';
	
	$arrConfig['Script']['ReportExecute']         = $arrScript;

?>
