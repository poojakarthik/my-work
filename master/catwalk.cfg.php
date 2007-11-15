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
//$arrConfig['Verbose']			= FALSE;


$arrConfig['GMTOffset']			= 10;

// RefreshRate
//		Int		Number of seconds between each refresh
$arrConfig['RefreshRate']		= 1;


//----------------------------------------------------------------------------//
// FULL DB COPY (BRONZE SAMPLES)
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       3600*20;	// 2000
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       (3600*23)+3599;	// 2399 - 4 Hour Window
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	
	// RecurringDay
	//              Int				The Day on which the script is to run
	//								eg. value of "1" would run on the first of every month
	//									value of "-7" would run a week before the end of the month (calculated from the 1st)
	$arrScript['RecurringDay']				= 		-8;
	
	// DieOnChildDeath
	//				Boolean			Whether the failure of a child script should kill the parent
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	// Subscripts
	//				Array			List of subscripts to run
	//								Each each script requires the preceeding script to finish
	$arrScript['SubScript'] = Array();
		
		// Billing Samples
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/backup_scripts/mysql_hot_copy.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/backup_scripts/';
		$arrScript['SubScript']['CopyDB']				= $arrSubscript;
	
	$arrConfig['Script']['FullDBCopy']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// BRONZE SAMPLES
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       3600*4;	// 0400
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       3600*12;	// 1200 - 6 Hour Window
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	
	// RecurringDay
	//              Int				The Day on which the script is to run
	//								eg. value of "1" would run on the first of every month
	//									value of "-7" would run a week before the end of the month (calculated from the 1st)
	$arrScript['RecurringDay']				= 		-7;
	
	// DieOnChildDeath
	//				Boolean			Whether the failure of a child script should kill the parent
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	// Subscripts
	//				Array			List of subscripts to run
	//								Each each script requires the preceeding script to finish
	$arrScript['SubScript'] = Array();
		
		// Normalisation
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/normalisation_app/normalisation.php -i';
		$arrSubscript['Directory']	=       '/usr/share/vixen/normalisation_app/';
		$arrScript['SubScript']['Normalise']		= $arrSubscript;
		
		// Rating
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/rating_app/rating.php';
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
		
		// Check Un-Invoiced Special Charges
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/charges_app/charges_check_special.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/charges_app/';
		$arrScript['SubScript']['CheckSpecialCharges']	= $arrSubscript;
		
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
		
		// Check CDR Files
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/cdrcheck.php -v';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['CDRFileCheck']				= $arrSubscript;		
		
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
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/billing_samples.php bronze';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['BillSamples']				= $arrSubscript;
	
	$arrConfig['Script']['BronzeSamples']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// PARTIAL TABLE COPY + CDR UPDATE (SILVER SAMPLES)
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       3600*20;	// 2000
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       (3600*23)+3599;	// 2399 - 4 Hour Window
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	
	// RecurringDay
	//              Int				The Day on which the script is to run
	//								eg. value of "1" would run on the first of every month
	//									value of "-7" would run a week before the end of the month (calculated from the 1st)
	$arrScript['RecurringDay']				= 		-2;
	
	// DieOnChildDeath
	//				Boolean			Whether the failure of a child script should kill the parent
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	// Subscripts
	//				Array			List of subscripts to run
	//								Each each script requires the preceeding script to finish
	$arrScript['SubScript'] = Array();
		
		// Partial DB Copy
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/backup_scripts/mysql_hot_copy.php CDR';
		$arrSubscript['Directory']	=       '/usr/share/vixen/backup_scripts/';
		$arrScript['SubScript']['DBCopy']				= $arrSubscript;
		
		// CDR Sync
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/backup_scripts/cdr_sync.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/backup_scripts/';
		$arrScript['SubScript']['CDRSync']				= $arrSubscript;
	
	$arrConfig['Script']['PartialDBCopy']	= $arrScript;
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// SILVER SAMPLES
$arrScript                                                      = Array();
	
	// StartTime
	//              Int             Earliest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	$arrScript['StartTime']                 =       3600*4;	// 0400
	
	// FinishTime
	//              Int             optional Latest time that the script can run during the day
	//                              Time in seconds from 00:00:00
	//                              Defaults to 86400 (24:00:00:00)
	$arrScript['FinishTime']                =       3600*12;	// 1200 - 6 Hour Window
	
	// Interval
	//              Int             Interval time in seconds.
	//                              Script will be run every Interval seconds.
	$arrScript['Interval']                  =       2419200;	// 28 Days (this is really irrelevant)
	
	// RecurringDay
	//              Int				The Day on which the script is to run
	//								eg. value of "1" would run on the first of every month
	//									value of "-7" would run a week before the end of the month (calculated from the 1st)
	$arrScript['RecurringDay']				= 		-1;
	
	// DieOnChildDeath
	//				Boolean			Whether the failure of a child script should kill the parent
	$arrScript['DieOnChildDeath']			=		TRUE;
	
	// Subscripts
	//				Array			List of subscripts to run
	//								Each each script requires the preceeding script to finish
	$arrScript['SubScript'] = Array();
		
		// Normalisation
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/normalisation_app/normalisation.php -i';
		$arrSubscript['Directory']	=       '/usr/share/vixen/normalisation_app/';
		$arrScript['SubScript']['Normalise']		= $arrSubscript;
		
		// Rating
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/rating_app/rating.php';
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
		
		// Check Un-Invoiced Special Charges
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/charges_app/charges_check_special.php';
		$arrSubscript['Directory']	=       '/usr/share/vixen/charges_app/';
		$arrScript['SubScript']['CheckSpecialCharges']	= $arrSubscript;
		
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
		
		// Check CDR Files
		$arrSubscript = Array();
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/cdrcheck.php -v';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['CDRFileCheck']				= $arrSubscript;		
		
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
		$arrSubscript['Command']	=       'php /usr/share/vixen/billing_app/billing_samples.php silver';
		$arrSubscript['Directory']	=       '/usr/share/vixen/billing_app/';
		$arrScript['SubScript']['BillSamples']				= $arrSubscript;
	
	$arrConfig['Script']['SilverSamples']	= $arrScript;
//----------------------------------------------------------------------------//

?>
