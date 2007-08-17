<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-7 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DEFINITIONS
//----------------------------------------------------------------------------//
/**
 * config
 *
 * Per-Customer Config Definitions
 *
 * This file exclusively declares global config constants
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.07
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

// Data Access constants
// run setup_scripts/config.sh as root to add a default config file
if (!@include_once("/etc/vixen/vixen.conf"))
{
	echo "Missing database config script";
	die;
}

$GLOBALS['**arrVixenConfig'] = Array();

// Billing Config
$arrBillingConfig = Array();

	// Billing-Time modules						Class						Property
		// Late Payment Fee
		$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['Amount']			= 17.27;
		$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['MinimumOverdue']	= 10.0;
		// Non-DDR Fee
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Amount']			= 2.50;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['MinimumTotal']	= 2.50;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Code']			= "AP250";
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Description']		= "Account Processing Fee";
	
	// Printing
	$arrBillingConfig['PrintingModule']	['Class']				= "BillingModulePrint";
	$arrBillingConfig['PrintingModule']	['SendMinimumTotal']	= 5.0;
	$arrBillingConfig['PrintingModule']	['AlwaysEmailBill']		= TRUE;
	
	
?>
