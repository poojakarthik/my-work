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

// Normalisation
$arrSubscript = Array();
$arrSubscript['Command']			= 'php normalisation.php -i';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'normalisation/';
$arrConfig['Normalise']				= $arrSubscript;

// Rating
$arrSubscript = Array();
$arrSubscript['Command']			= 'php rating.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'rating/';
$arrConfig['Rate']					= $arrSubscript;

// Rate LL S&E Credits
$arrSubscript = Array();
$arrSubscript['Command']			= 'php rate_ll_se_credits.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'rating/';
$arrConfig['RateLLSECredits']		= $arrSubscript;

// Backup Invoice Output
$arrSubscript = Array();
$arrSubscript['Command']			= 'php backup_invoice_output.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'billing/';
$arrConfig['BackupInvoiceOutput']	= $arrSubscript;

// Check Un-Invoiced Special Charges
$arrSubscript = Array();
$arrSubscript['Command']			= 'php charges_check_special.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'charges/';
$arrConfig['CheckSpecialCharges']	= $arrSubscript;

// Special Charges
$arrSubscript = Array();
$arrSubscript['Command']			= 'php special_charges.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'charges/';
$arrConfig['SpecialCharges']		= $arrSubscript;

// Recurring Charges
$arrSubscript = Array();
$arrSubscript['Command']			= 'php recurring_charges.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'charges/';
$arrConfig['RecurringCharges']		= $arrSubscript;

// Payments
$arrSubscript = Array();
$arrSubscript['Command']			= 'php payments.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'payment/';
$arrConfig['Payments']				= $arrSubscript;

// Check CDR Files
$arrSubscript = Array();
$arrSubscript['Command']			= 'php cdrcheck.php -v';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'billing/';
$arrConfig['CDRFileCheck']			= $arrSubscript;		

// Billing Execute
$arrSubscript = Array();
$arrSubscript['Command']			= 'php billing_execute.php silver';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'billing/';
$arrConfig['BillExecute']			= $arrSubscript;

// Billing Print
$arrSubscript = Array();
$arrSubscript['Command']			= 'php billing_print.php BILL_FLEX_XML';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'billing/';
$arrConfig['BillPrint']				= $arrSubscript;

// Billing Samples
$arrSubscript = Array();
$arrSubscript['Command']			= 'php billing_samples_list.php silver';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'billing/';
$arrConfig['BillSamples']			= $arrSubscript;

// Management Reports
$arrSubscript = Array();
$arrSubscript['Command']			= 'php billing_reports.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'billing/';
//$arrConfig['ManagementReports']		= $arrSubscript;

?>