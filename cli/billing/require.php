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
 * @author		Rich 'Waste' Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$strModuleDir = "cli/billing/modules/";

// Invoice Modules
//VixenRequire($strModuleDir."module_printing.php");
//VixenRequire($strModuleDir."module_etech.php");
VixenRequire($strModuleDir."invoice_base.php");
VixenRequire($strModuleDir."invoice_xml.php");
VixenRequire($strModuleDir."invoice_billprint.php");

// Management Report Modules
VixenRequire($strModuleDir."module_reports.php");

// Charge Modules
VixenRequire("cli/billing/Billing_Charge.php");

$strChargeDir	= "cli/billing/charge/";
VixenRequire($strChargeDir."Billing_Charge_Account.php");
VixenRequire($strChargeDir."Billing_Charge_Service.php");

VixenRequire($strChargeDir."account/Billing_Charge_Account_AccountProcessing.php");
VixenRequire($strChargeDir."account/Billing_Charge_Account_LatePayment.php");
VixenRequire($strChargeDir."account/Billing_Charge_Account_Postage.php");

VixenRequire($strChargeDir."service/Billing_Charge_Service_Inbound.php");
VixenRequire($strChargeDir."service/Billing_Charge_Service_Pinnacle.php");

// Remote Copy
VixenRequire("lib/framework/remote_copy.php");

 ?>