<?php

//----------------------------------------------------------------------------//
// CUSTOMER CONFIG
//----------------------------------------------------------------------------//


$GLOBALS['**arrCustomerConfig'] = Array();

//----------------------------------------------------------------------------//
// General
//----------------------------------------------------------------------------//
$GLOBALS['**arrCustomerConfig']	['Customer']			= CUSTOMER_URL_NAME;
//$GLOBALS['**arrCustomerConfig']	['EmailNotifications']	= "billing-notifications@yellowbilling.com.au";
$GLOBALS['**arrCustomerConfig']	['EmailNotifications']	= EMAIL_NOTIFICATIONS;

//----------------------------------------------------------------------------//
// Billing
//----------------------------------------------------------------------------//
$arrBillingConfig = Array();

	// Billing-Time modules						Class						Property
		// Late Payment Fee
		//$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['Amount']			= 17.27;
		//$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['MinimumOverdue']	= 10.0;
		$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['Amount']			= CHARGE_LATE_PAYMENT_AMOUNT;
		$arrBillingConfig['BillingTimeModules']	['ChargeLatePayment']		['MinimumOverdue']	= CHARGE_LATE_PAYMENT_MINIMUM_OVERDUE;
		// Non-DDR Fee
		//$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Amount']			= 2.75;
		//$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['MinimumTotal']	= 2.75;
		//$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Code']			= "AP275";
		//$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Description']		= "Account Processing Fee";
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Amount']			= CHARGE_NON_DIRECT_DEBIT_AMOUNT;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['MinimumTotal']	= CHARGE_NON_DIRECT_DEBIT_MINIMUM_TOTAL;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Code']			= CHARGE_NON_DIRECT_DEBIT_CODE;
		$arrBillingConfig['BillingTimeModules']	['ChargeNonDirectDebit']	['Description']		= CHARGE_NON_DIRECT_DEBIT_DESCRIPTION;
		
	
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
	$arrBillingConfig['PrintingModule']	['SpecialOffer1']	[CUSTOMER_GROUP_TELCOBLUE]		= "Check for changes that may affect you. We may have a better plan so request a discount analysis. Check for new products that maybe suitable for you. Check for changes to services Visit our Terms & Conditions for updates.";
	$arrBillingConfig['PrintingModule']	['SpecialOffer2']	[CUSTOMER_GROUP_TELCOBLUE]		= "Check your responsibilities for canceling your services. Questions or help required? Go to our website at www.telcoblue.com.au or phone customer service on 1300 835 262.";
	
	$arrBillingConfig['PrintingModule']	['SpecialOffer1']	[CUSTOMER_GROUP_IMAGINE]		= "Check for changes that may affect you. We may have a better plan so request a discount analysis. Check for new products that maybe suitable for you. Check for changes to services Visit our Terms & Conditions for updates.";
	$arrBillingConfig['PrintingModule']	['SpecialOffer2']	[CUSTOMER_GROUP_IMAGINE]		= "Check your responsibilities for canceling your services. Questions or help required? Go to our website at www.telcoblue.com.au or phone customer service on 1300 835 262.";
	
	$arrBillingConfig['PrintingModule']	['SpecialOffer1']	[CUSTOMER_GROUP_VOICETALK]		= "Check for changes that may affect you. We may have a better plan so request a discount analysis. Check for new products that maybe suitable for you. Check for changes to services Visit our Terms & Conditions for updates.";
	$arrBillingConfig['PrintingModule']	['SpecialOffer2']	[CUSTOMER_GROUP_VOICETALK]		= "Check your responsibilities for canceling your services. Questions or help required? Go to our website at www.voicetalk.com.au or phone customer service on 1300 882 172.";
	
	// Bill Printer FTP Server
	/*
	$arrBillingConfig['PrinterFTP']	['Server']				= '121.223.224.237';
	$arrBillingConfig['PrinterFTP']	['User']				= 'vixen';
	$arrBillingConfig['PrinterFTP']	['Password']			= 'v1xen';
	$arrBillingConfig['PrinterFTP']	['UploadPath']			= '/Incoming/';
	$arrBillingConfig['PrinterFTP']	['UploadPathSamples']	= '/Incoming/Samples/';
	$arrBillingConfig['PrinterFTP']	['DownloadPath']		= '/Outgoing/';
	$arrBillingConfig['PrinterFTP']	['DownloadPathSamples']	= '/Outgoing/Samples/';
	*/
	$arrBillingConfig['PrinterFTP']	['Server']				= PRINTER_FTP_SERVER;
	$arrBillingConfig['PrinterFTP']	['User']				= PRINTER_FTP_USER;
	$arrBillingConfig['PrinterFTP']	['Password']			= PRINTER_FTP_PASSWORD;
	$arrBillingConfig['PrinterFTP']	['UploadPath']			= PRINTER_FTP_UPLOAD_PATH;
	$arrBillingConfig['PrinterFTP']	['UploadPathSamples']	= PRINTER_FTP_UPLOAD_PATH_SAMPLES;
	$arrBillingConfig['PrinterFTP']	['DownloadPath']		= PRINTER_FTP_DOWNLOAD_PATH;
	$arrBillingConfig['PrinterFTP']	['DownloadPathSamples']	= PRINTER_FTP_DOWNLOAD_PATH_SAMPLES;
	
$GLOBALS['**arrCustomerConfig']	['Billing']	= 	$arrBillingConfig;

//----------------------------------------------------------------------------//
// Provisioning
//----------------------------------------------------------------------------//

$arrProvisioningConfig = Array();

	// General Constants
	//$arrProvisioningConfig['ProvisioningEmail']	= "billing-notifications@yellowbilling.com.au";
	$arrProvisioningConfig['ProvisioningEmail']	= EMAIL_PROVISIONING;

	// Unitel Constants
	/*
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['SenderCode']									= 'sa';
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['CSPCode']										= '058';
	
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Server']										= 'rslcom.com.au';
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['User']										= 'sp058';
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Password']									= 'BuzzaBee06*#';
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Path']		[PRV_UNITEL_PRESELECTION_EXP]	= 'dailychurn';
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Path']		[PRV_UNITEL_DAILY_ORDER_EXP]	= 'ebill_dailyorderfiles';
	*/
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['SenderCode']									= PRV_CONFIG_UNITEL_SENDER_CODE;
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['CSPCode']										= PRV_CONFIG_UNITEL_CSP_CODE;
	
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Server']										= PRV_CONFIG_UNITEL_SERVER;
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['User']										= PRV_CONFIG_UNITEL_USER;
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Password']									= PRV_CONFIG_UNITEL_PASSWORD;
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Path']		[PRV_UNITEL_PRESELECTION_EXP]	= PRV_CONFIG_UNITEL_PATH_PRESELECTION_EXP;
	$arrProvisioningConfig['Carrier']	[CARRIER_UNITEL]	['Path']		[PRV_UNITEL_DAILY_ORDER_EXP]	= PRV_CONFIG_UNITEL_PATH_DAILY_ORDER_EXP;
	
	// Optus Constants
	$arrProvisioningConfig['Carrier']	[CARRIER_OPTUS]		['CustomerCode']								= PRV_CONFIG_OPTUS_CUSTOMER_CODE;

$GLOBALS['**arrCustomerConfig']	['Provisioning']	= 	$arrProvisioningConfig;

//----------------------------------------------------------------------------//
// Notices
//----------------------------------------------------------------------------//

// Account Notice Generation Config
$arrAccountNoticeConfig = Array();

	// Late Payment Notice: Acceptable Overdue Balance
	// A late notice is only generated if the account is overdue by more than this amount
	//$arrAccountNoticeConfig['LateNoticeModule']['AcceptableOverdueBalance']	= 23.00;
	$arrAccountNoticeConfig['LateNoticeModule']['AcceptableOverdueBalance']	= LATE_NOTICE_ACCEPTABLE_OVERDUE_BALANCE;

$GLOBALS['**arrCustomerConfig']['AccountNotice'] = $arrAccountNoticeConfig;


?>