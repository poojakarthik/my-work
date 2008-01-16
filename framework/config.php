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

$GLOBALS['**arrCustomerConfig'] = Array();

// General Config
$GLOBALS['**arrCustomerConfig']	['Customer']	= "telcoblue";

// Billing Config
$arrBillingConfig = Array();

	// Billing-Time modules						Class						Property
		// Late Payment Fee
		$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['Amount']			= 17.27;
		$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['MinimumOverdue']	= 10.0;
		// Non-DDR Fee
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Amount']			= 2.75;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['MinimumTotal']	= 2.75;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Code']			= "AP275";
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Description']		= "Account Processing Fee";
		
	
	// Printing
	$arrBillingConfig['PrintingModule']	['Class']				= "BillingModulePrint";
	$arrBillingConfig['PrintingModule']	['SendMinimumTotal']	= 5.0;
	$arrBillingConfig['PrintingModule']	['AlwaysEmailBill']		= TRUE;
	
		// Bill Inserts
		$arrBillingConfig['PrintingModule']	['Inserts']	[0]			= "directdebit_telcoblue.pdf";
		$arrBillingConfig['PrintingModule']	['Inserts']	[1]			= "directdebit_voicetalk.pdf";
		$arrBillingConfig['PrintingModule']	['Inserts']	[2]			= NULL;
		$arrBillingConfig['PrintingModule']	['Inserts']	[3]			= NULL;
		$arrBillingConfig['PrintingModule']	['Inserts']	[4]			= NULL;
		$arrBillingConfig['PrintingModule']	['Inserts']	[5]			= NULL;
	
	//$arrBillingConfig['PrintingModule']	['SpecialOffer1']			= "Dear Customer, There is a minor adjustment to some of our service charges of up to 68c. However, while this has been unavoidable, you can rest assured that our call rates and customer service still remain as exceptional as ever!  ";
	//$arrBillingConfig['PrintingModule']	['SpecialOffer2']			= "For your convenience we have included a direct debit application form should you wish to pay by this method, simply fill in your EFT or credit card details and fax to the Customer Service Team on 1300 733 393";
	$arrBillingConfig['PrintingModule']	['SpecialOffer1']	[CUSTOMER_GROUP_TELCOBLUE]		= "As a valued customer, please feel free to contact customer service for a bill analysis or plan upgrade. Visit our website to view full disclosure of our Terms and Conditions at www.telcoblue.com.au.";
	$arrBillingConfig['PrintingModule']	['SpecialOffer2']	[CUSTOMER_GROUP_TELCOBLUE]		= "One change as of 1st November will be a 1.5% surcharge on some credit card payments. Any enquiries can be made with our friendly customer service Team on 1300 835 262";
	
	$arrBillingConfig['PrintingModule']	['SpecialOffer1']	[CUSTOMER_GROUP_IMAGINE]		= "As a valued customer, please feel free to contact customer service for a bill analysis or plan upgrade. Visit our website to view full disclosure of our Terms and Conditions at www.telcoblue.com.au.";
	$arrBillingConfig['PrintingModule']	['SpecialOffer2']	[CUSTOMER_GROUP_IMAGINE]		= "One change as of 1st November will be a 1.5% surcharge on some credit card payments. Any enquiries can be made with our friendly customer service Team on 1300 835 262";
	
	$arrBillingConfig['PrintingModule']	['SpecialOffer1']	[CUSTOMER_GROUP_VOICETALK]		= "As a valued customer, please feel free to contact customer service for a bill analysis or plan upgrade. Visit our website to view full disclosure of our Terms and Conditions at www.voicetalk.com.au.";
	$arrBillingConfig['PrintingModule']	['SpecialOffer2']	[CUSTOMER_GROUP_VOICETALK]		= "One change as of 1st November will be a 1.5% surcharge on some credit card payments. Any enquiries can be made with our friendly customer service Team on 1300 882 172";

$GLOBALS['**arrCustomerConfig']	['Billing']	= 	$arrBillingConfig;





// Account Notice Generation Config
$arrAccountNoticeConfig = Array();

	// Late Payment Notice: Acceptable Overdue Balance
	// If an account is overdue by this amount or less, then late notices will not be generated for it
	$arrAccountNoticeConfig['LateNoticeModule']['AcceptableOverdueBalance']	= 23.00;

$GLOBALS['**arrCustomerConfig']['AccountNotice'] = $arrAccountNoticeConfig;


?>
