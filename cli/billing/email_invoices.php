<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Emails Invoices to specified accounts
//----------------------------------------------------------------------------//

// load application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Check Parameters
$strInvoiceRun	= $argv[1];
$selInvoiceRun	= new StatementSelect("InvoiceRun", "*, Id AS invoice_run_id", "InvoiceRun = <InvoiceRun>");
if (!$selInvoiceRun->Execute(Array('InvoiceRun' => $strInvoiceRun)))
{
	CliEcho("\n'$strInvoiceRun' is not a valid InvoiceRun!\n");
	exit(1);
}
$arrInvoiceRun	= $selInvoiceRun->Fetch();

// Email them Invoices
EmailInvoices($arrInvoiceRun);

exit(0);



//------------------------------------------------------------------------//
// EmailInvoices()
//------------------------------------------------------------------------//
/**
 * EmailInvoices()
 *
 * Sends invoices in emails from the specified directory
 *
 * Sends invoices in emails from the specified directory
 *
 * @return		array		$arrInvoiceRun			Invoice Run details
 *
 * @method
 */
function EmailInvoices($arrInvoiceRun)
{
	CliEcho("[ EMAILING INVOICES ]\n");
	
	// Get $strBillingPeriod & InvoiceRun
	$strBillingPeriod 	= date("F Y", strtotime("-1 month", strtotime($arrInvoiceRun['BillingDate'])));
	
	$selInvoices		= new StatementSelect(	"Invoice", "*", "invoice_run_id = <invoice_run_id> AND DeliveryMethod = 1");
	
	$selAccountEmail	= new StatementSelect(	"Account JOIN Contact ON Account.Id = Contact.Account",
												"Contact.Account, CustomerGroup, Email, FirstName",
												"Account.Id = <Account> AND Email != '' AND Contact.Archived = 0");
	
	$updDeliveryMethod	= new StatementUpdate("Invoice", "invoice_run_id = <invoice_run_id> AND Account = <Account>", Array('DeliveryMethod' => NULL));
	
	$selCustomerGroup	= new StatementSelect("CustomerGroup", "*", "1");
	if ($selCustomerGroup->Execute() === FALSE)
	{
		// DB Error
		Debug($selCustomerGroup->Error());
		return FALSE;
	}
	$arrCustomerGroups	= Array();
	while ($arrCustomerGroup = $selCustomerGroup->Fetch())
	{
		$arrCustomerGroups[$arrCustomerGroup['Id']]	= $arrCustomerGroup;
	}
	
	// Loop through each Invoice
	$intPassed	= 0;
	$intIgnored	= 0;
	if ($selInvoices->Execute($arrInvoiceRun) === FALSE)
	{
		Debug($selInvoices->Error());
		return FALSE;
	}
	while ($arrInvoice = $selInvoices->Fetch()) 
	{
		// Get the account number from the filename, then find the account's email address 			
		if ($selAccountEmail->Execute($arrInvoice) === FALSE)
		{
			Debug($selAccountEmail->Error());
			return FALSE;
		}
		if (!$arrDetails = $selAccountEmail->FetchAll())
		{
			// Bad Account Number or Non-Email Account
			continue;
			$intIgnored++;
		}
		
 		CliEcho("\n\t+ Emailing Invoice(s) for Account #{$arrInvoice['Account']}...");
		
		// for each email-able contact
		foreach ($arrDetails as $arrDetail)
		{
 			// Set email details based on Customer Group
 			$arrHeaders = Array	(
 									'From'		=> $arrCustomerGroups[$arrDetail['CustomerGroup']]['OutboundEmail'],
 									'Subject'	=> "Your {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']} Invoice for $strBillingPeriod"
 								);
			$strContent	=	"Your {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']} Invoice, for Account Number {$arrInvoice['Account']}, is now ready for viewing via the online customer portal.  To access the portal please go to: {$arrCustomerGroups[$arrDetail['CustomerGroup']]['customer_exit_url']} and enter your username & password.\n\n" .
							"If you are yet to setup your customer account go to: {$arrCustomerGroups[$arrDetail['CustomerGroup']]['customer_exit_url']} and click on “Setup Account” and follow the prompts. Should you have any difficulties accessing the customer portal please email {$arrCustomerGroups[$arrDetail['CustomerGroup']]['OutboundEmail']}.\n\n" .
							"Regards,\n\n" .
							"The team at {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']}.";
	 		
	 		// Does the customer have a first name?
	 		if (trim($arrDetail['FirstName']))
	 		{
	 			$strContent = "Dear ".$arrDetail['FirstName'].",\n\n" . $strContent;
	 		}
	 		
 			// Account for , separated email addresses
 			$arrEmails = explode(',', $arrDetail['Email']);
 			foreach ($arrEmails as $strEmail)
 			{
	 			$strEmail = trim($strEmail);
	 			
	 			CliEcho(str_pad("\t\tAddress: '$strEmail'...", 70, " ", STR_PAD_RIGHT), FALSE);
	 			
	 			// Validate email address
	 			if (!preg_match('/^([[:alnum:]]([+-_.]?[[:alnum:]])*)@([[:alnum:]]([.]?[-[:alnum:]])*[[:alnum:]])\.([[:alpha:]]){2,25}$/', $strEmail))
	 			{
	 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Email address is invalid");
	 				continue;
	 			}
	 			
	 			$mimMime = new Mail_mime("\n");
	 			$mimMime->setTXTBody($strContent);
				$strBody = $mimMime->get();
				$strHeaders = $mimMime->headers($arrHeaders);
	 			$emlMail =& Mail::factory('mail');
	 			
	 			// Uncomment this to Debug
	 			$strEmail			= 'rich@voiptelsystems.com.au';
	 			$arrDebugEmails		= Array();
	 			$arrDebugEmails[]	= 'rdavis@yellowbilling.com.au';
	 			$arrDebugEmails[]	= 'turdminator@hotmail.com';
	 			
	 			$strEmail	= (count($arrDebugEmails)) ? implode(', ', $arrDebugEmails) : $strEmail;
	 				 			
	 			// Send the email
	 			if (!$emlMail->send($strEmail, $strHeaders, $strBody))
	 			{
	 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
	 				continue;
	 			}
	 			die;
				
				// Update DeliveryMethod
				$arrWhere					= Array();
				$arrWhere['invoice_run_id']	= $arrInvoiceRun['invoice_run_id'];
				$arrWhere['Account']		= $arrDetail['Account'];
				
				$arrUpdateData						= Array();
				$arrUpdateData['DeliveryMethod']	= BILLING_METHOD_EMAIL_SENT;
				
				if ($updDeliveryMethod->Execute($arrUpdateData, $arrWhere))
				{
					//Debug("Success!");
				}
				else
				{
					//Debug("Failure!");
				}
				//Debug($arrWhere);
				//die;*/
 				
 				CliEcho("[   OK   ]");
 				$intPassed++;
 				
 				// Uncomment this to Debug
				//die;
 			}
		}
	}
	
	CliEcho("\n\t* $intPassed emails sent.\n");
}
?>