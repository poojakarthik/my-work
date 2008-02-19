<?php

// Make sure that FLEX_BASE_PATH is defined
if (!defined('FLEX_BASE_PATH'))
{
	echo "\nERROR: This script should not be run directly!\n";
	die;
}

//----------------------------------------------------------------------------//
// BILLING MULTIPART SCRIPT
//----------------------------------------------------------------------------//
$arrConfig = Array();

// Collection
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'collection/collection.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'collection/';
$arrScript['Collect']		= $arrSubscript;

// Normalisation
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'normalisation/normalisation.php -i';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'normalisation/';
$arrScript['Normalise']		= $arrSubscript;

// Rating
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'rating/rating.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'rating/';
$arrScript['Rate']		= $arrSubscript;

// Rate LL S&E Credits
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'rating/rate_ll_se_credits.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'rating/';
$arrScript['RateLLSECredits']		= $arrSubscript;

// Backup Invoice Output
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'billing/backup_invoice_output.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'billing/';
$arrScript['BackupInvoiceOutput']	= $arrSubscript;

// Check Un-Invoiced Special Charges
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'charges/charges_check_special.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'charges/';
$arrScript['CheckSpecialCharges']	= $arrSubscript;

// Special Charges
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'charges/special_charges.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'charges/';
$arrScript['SpecialCharges']		= $arrSubscript;

// Recurring Charges
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'charges/recurring_charges.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'charges/';
$arrScript['RecurringCharges']		= $arrSubscript;

// Payments
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'payment/payments.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'payment/';
$arrScript['Payments']				= $arrSubscript;

// Check CDR Files
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'billing/cdrcheck.php -v';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'billing/';
$arrScript['CDRFileCheck']				= $arrSubscript;		

// Billing Execute
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'billing/billing_execute.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'billing/';
$arrScript['BillExecute']				= $arrSubscript;

// Billing Print
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'billing/billing_print.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'billing/';
$arrScript['BillPrint']				= $arrSubscript;

// Billing Samples
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'billing/billing_samples.php gold';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'billing/';
$arrScript['BillSamples']				= $arrSubscript;

// Management Reports
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.FLEX_BASE_PATH.'billing/billing_reports.php';
$arrSubscript['Directory']	=       FLEX_BASE_PATH.'billing/';
$arrScript['ManagementReports']		= $arrSubscript;

?>