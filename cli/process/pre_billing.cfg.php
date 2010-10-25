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
$arrSubscript['Command']			= 'php collection.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'collection/';
$arrSubscript['ChildDie']			= TRUE;
//$arrConfig['Collect']				= $arrSubscript;

// Normalisation
$arrSubscript = Array();
$arrSubscript['Command']			= 'php normalisation.php -i';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'normalisation/';
$arrSubscript['ChildDie']			= TRUE;
$arrConfig['Normalise']				= $arrSubscript;

// Rating
$arrSubscript = Array();
$arrSubscript['Command']			= 'php rating.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH;
$arrSubscript['ChildDie']			= TRUE;
$arrConfig['Rate']					= $arrSubscript;

// Recurring Charges
$arrSubscript = Array();
$arrSubscript['Command']			= 'php recurring_charges.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH;
$arrSubscript['ChildDie']			= TRUE;
$arrConfig['RecurringCharges']		= $arrSubscript;

// Payments
$arrSubscript = Array();
$arrSubscript['Command']			= 'php payments.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'payment/';
$arrSubscript['ChildDie']			= TRUE;
$arrConfig['Payments']				= $arrSubscript;

?>