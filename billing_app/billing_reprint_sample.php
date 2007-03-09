<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Add in list of accounts
//---------------------------

// Voicetalk customers
$arrAccounts[]	= 1000162906;
$arrAccounts[]	= 1000162059;
$arrAccounts[]	= 1000159470;
$arrAccounts[]	= 1000162528;
$arrAccounts[]	= 1000007031;
$arrAccounts[]	= 1000162445;
$arrAccounts[]	= 1000161645;
$arrAccounts[]	= 1000162437;
$arrAccounts[]	= 1000162825;
$arrAccounts[]	= 1000159466;
$arrAccounts[]	= 1000159893;
$arrAccounts[]	= 1000010134;
$arrAccounts[]	= 1000161816;
$arrAccounts[]	= 1000163105;
$arrAccounts[]	= 1000161744;

// w/ Inbound Services
$arrAccounts[]	= 1000157275;
$arrAccounts[]	= 1000157088;
$arrAccounts[]	= 1000162420;
$arrAccounts[]	= 1000155448;
$arrAccounts[]	= 1000157889;
$arrAccounts[]	= 1000155226;
$arrAccounts[]	= 1000162199;
$arrAccounts[]	= 1000154974;
$arrAccounts[]	= 1000154909;
$arrAccounts[]	= 1000157423;
$arrAccounts[]	= 1000156613;
$arrAccounts[]	= 1000154838;
$arrAccounts[]	= 1000157129;
$arrAccounts[]	= 1000160069;
$arrAccounts[]	= 1000160496;

// w/ Mobiles
$arrAccounts[]	= 1000157278;
$arrAccounts[]	= 1000163258;
$arrAccounts[]	= 1000158462;
$arrAccounts[]	= 1000160638;
$arrAccounts[]	= 1000157524;
$arrAccounts[]	= 1000159582;
$arrAccounts[]	= 1000162277;
$arrAccounts[]	= 1000162126;
$arrAccounts[]	= 1000157548;
$arrAccounts[]	= 1000158255;
$arrAccounts[]	= 1000158558;
$arrAccounts[]	= 1000159107;
$arrAccounts[]	= 1000157175;
$arrAccounts[]	= 1000158156;
$arrAccounts[]	= 1000159979;

// w/ Cost Centres
$arrAccounts[]	= 1000155448;
$arrAccounts[]	= 1000157203;
$arrAccounts[]	= 1000155054;
$arrAccounts[]	= 1000155021;
$arrAccounts[]	= 1000156265;
$arrAccounts[]	= 1000155104;
$arrAccounts[]	= 1000158291;
$arrAccounts[]	= 1000157570;
$arrAccounts[]	= 1000155313;

// reprint
$bolResponse = $appBilling->PrintSampleAccounts($arrAccounts);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>

?>
