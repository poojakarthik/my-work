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



//----------------------------------------------------------------------------//
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
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
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
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
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
//----------------------------------------------------------------------------//



//----------------------------------------------------------------------------//
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
//----------------------------------------------------------------------------//
	


//----------------------------------------------------------------------------//
// Import Single
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       0;	// 0000
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       3600;	// 1 Hour
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/normalisation_app/import.php 1';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/normalisation_app/';
	
	$arrConfig['Script']['ImportSingle']         = $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Normalise 10,000 New CDRs
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       0;	// 0000
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       3600;	// 1 Hour
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/normalisation_app/normalise.php -n 10000';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/normalisation_app/';
	
	$arrConfig['Script']['Normalise10kNew']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Import & Normalise All Remaining
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       86400 - (3600 * 2);	// 2200
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       86400;	// 24 Hours
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/normalisation_app/normalise.php -i';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/normalisation_app/';
	
	$arrConfig['Script']['FullNormalise']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Rate New CDRs
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       0;	// 0000
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       3600;	// 1 Hour
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/rating_app/rating.php -n';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/rating_app/';
	
	$arrConfig['Script']['RateNew']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Rate All CDRs
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       86400 - 3600;	// 2300
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       86400;	// 24 Hours
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/rating_app/rating.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/rating_app/';
	
	$arrConfig['Script']['RateAll']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Recurring Charges
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       0;	// 0000
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       86400;	// 2400
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       86400;	// 24 Hours
	
	// Command
	//              String  Command to run the script (include full path to script).
	$arrScript['Command']                   =       'php /usr/share/vixen/charges_app/recurring_charges.php';
	
	// Directory
	//              String  optional Directory to run the script in.
	$arrScript['Directory']                 =       '/usr/share/vixen/charges_app/';
	
	$arrConfig['Script']['RecurringCharges']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Billing
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       3600*4;	// 0400
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       3600*5;	// 0500 - 1 Hour Window
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	
	// RecurringDay
	//              Int				The Day on which the script is to run
	//								eg. value of "1" would run on the first of every month
	$arrScript['RecurringDay']				= 		1;
	
	// Subscripts
	//				Array			List of subscripts to run
	//								Each each script requires the preceeding script to finish
	$arrScript['SubScript'] = Array();
	
		// Collection
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/collection_app/collection.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/collection_app/';
		$arrScript['SubScript']['Collect']		= $arrSubscript;
		
		// Normalisation
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/normalisation_app/normalise.php -i';
		$arrSubscript['Directory']	=       '/usr/share/vixen/normalisation_app/';
		$arrScript['SubScript']['Normalise']		= $arrSubscript;
		
		// Rating
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/rating_app/rate_ll_se_credits.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/rating_app/';
		$arrScript['SubScript']['Rate']		= $arrSubscript;
		
		// Rate LL S&E Credits
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/rating_app/rate_ll_se_credits.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/rating_app/';
		$arrScript['SubScript']['RateLLSECredits']		= $arrSubscript;
		
		// Backup Invoice Output
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/backup_invoice_output.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['BackupInvoiceOutput']	= $arrSubscript;
		
		// Special Charges
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/charges_app/special_charges.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/charges_app/';
		$arrScript['SubScript']['SpecialCharges']		= $arrSubscript;
		
		// Recurring Charges
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/charges_app/recurring_charges.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/charges_app/';
		$arrScript['SubScript']['RecurringCharges']		= $arrSubscript;
		
		// Payments
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/payment_app/payments.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/payment_app/';
		$arrScript['SubScript']['Payments']				= $arrSubscript;
		
		// Billing Execute
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/billing_execute.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['BillExecute']				= $arrSubscript;
		
		// Billing Print
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/billing_print.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['BillPrint']				= $arrSubscript;
		
		// Billing Samples
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/billing_reprint_sample.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['BillSamples']				= $arrSubscript;
	
	$arrConfig['Script']['BillingRun']	= $arrScript;
//----------------------------------------------------------------------------//




?>
