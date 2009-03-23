<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// require
//----------------------------------------------------------------------------//
/**
 * require
 *
 * Handles all file requirements for an application
 *
 * This file should load all files required by an application.
 * This file should not set up any objects or produce any output
 *
 * @file		require.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		7.04
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
$strApplicationDir	= "cli/payment/";
 
// load modules
$strModuleDir 		= "cli/payment/modules/";
VixenRequire($strModuleDir."base_module.php");
VixenRequire($strModuleDir."module_billexpress.php");
VixenRequire($strModuleDir."module_bpay_westpac.php");
VixenRequire($strModuleDir."module_securepay.php");
VixenRequire($strModuleDir."module_directentryreport.php");

$strDirectDebitDir	= "cli/payment/directdebit/";
VixenRequire($strApplicationDir."Payment_DirectDebit.php");
VixenRequire($strDirectDebitDir."Payment_DirectDebit_File.php");
VixenRequire($strDirectDebitDir."securepay/file/Payment_DirectDebit_File_SecurePay_BankTransfer.php");
VixenRequire($strDirectDebitDir."securepay/file/Payment_DirectDebit_File_SecurePay_CreditCard.php");
VixenRequire($strDirectDebitDir."australiandirectentry/file/Payment_DirectDebit_File_AustralianDirectEntry_BankTransfer.php");

VixenRequire('lib/classes/customer/Customer_Group.php');
VixenRequire('lib/classes/Employee.php');

?>
