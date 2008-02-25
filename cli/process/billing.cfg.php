<?php

// Make sure that BACKEND_BASE_PATH is defined
if (!defined('BACKEND_BASE_PATH'))
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
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'collection/collection.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'collection/';
$arrConfig['Collect']		= $arrSubscript;

// Normalisation
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'normalisation/normalisation.php -i';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'normalisation/';
$arrConfig['Normalise']		= $arrSubscript;

// Rating
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'rating/rating.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'rating/';
$arrConfig['Rate']		= $arrSubscript;

// Rate LL S&E Credits
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'rating/rate_ll_se_credits.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'rating/';
$arrConfig['RateLLSECredits']		= $arrSubscript;

// Backup Invoice Output
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'billing/backup_invoice_output.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'billing/';
$arrConfig['BackupInvoiceOutput']	= $arrSubscript;

// Check Un-Invoiced Special Charges
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'charges/charges_check_special.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'charges/';
$arrConfig['CheckSpecialCharges']	= $arrSubscript;

// Special Charges
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'charges/special_charges.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'charges/';
$arrConfig['SpecialCharges']		= $arrSubscript;

// Recurring Charges
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'charges/recurring_charges.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'charges/';
$arrConfig['RecurringCharges']		= $arrSubscript;

// Payments
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'payment/payments.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'payment/';
$arrConfig['Payments']				= $arrSubscript;

// Check CDR Files
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'billing/cdrcheck.php -v';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'billing/';
$arrConfig['CDRFileCheck']				= $arrSubscript;		

// Billing Execute
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'billing/billing_execute.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'billing/';
$arrConfig['BillExecute']				= $arrSubscript;

// Billing Print
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'billing/billing_print.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'billing/';
$arrConfig['BillPrint']				= $arrSubscript;

// Billing Samples
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'billing/billing_samples.php gold';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'billing/';
$arrConfig['BillSamples']				= $arrSubscript;

// Management Reports
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'billing/billing_reports.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'billing/';
$arrConfig['ManagementReports']		= $arrSubscript;

?>