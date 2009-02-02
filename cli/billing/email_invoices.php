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
$intInvoiceRunId	= (int)$argv[1];
$bolIncludePDF		= (strtolower($argv[2]) === 'includepdf') ? TRUE : FALSE;
$selInvoiceRun		= new StatementSelect("InvoiceRun", "*, Id AS invoice_run_id", "Id = <invoice_run_id>");
if (!$selInvoiceRun->Execute(Array('invoice_run_id' => $intInvoiceRunId)))
{
	CliEcho("\n'{$intInvoiceRunId}' is not a valid InvoiceRun Id!\n");
	exit(1);
}
$arrInvoiceRun	= $selInvoiceRun->Fetch();

// Email them Invoices
EmailInvoices($arrInvoiceRun, $bolIncludePDF);

exit(0);
//----------------------------- END OF ENTRY CODE -----------------------------/







//----------------------------------------------------------------------------//
// EmailInvoices()
//----------------------------------------------------------------------------//
/**
 * EmailInvoices()
 *
 * Sends invoices in emails from the specified directory
 *
 * Sends invoices in emails from the specified directory
 *
 * @param	array	$arrInvoiceRun					Invoice Run details
 * @param	boolean	$bolIncludePDF		[optional]	TRUE: Attach the PDF version of this Invoice
 *
 * @method
 */
function EmailInvoices($arrInvoiceRun, $bolIncludePDF=FALSE)
{
	CliEcho("[ EMAILING INVOICES ]\n");
	
	// Get $strBillingPeriod & InvoiceRun
	$strInvoiceDate 				= date("dmY", strtotime($arrInvoiceRun['BillingDate']));
	$strBillingPeriodEndMonth		= date("F", strtotime("-1 day", strtotime($arrInvoiceRun['BillingDate'])));
	$strBillingPeriodEndYear		= date("Y", strtotime("-1 day", strtotime($arrInvoiceRun['BillingDate'])));
	$strBillingPeriodStartMonth		= date("F", strtotime("-1 month", strtotime($arrInvoiceRun['BillingDate'])));
	$strBillingPeriodStartYear		= date("Y", strtotime("-1 month", strtotime($arrInvoiceRun['BillingDate'])));
	
	$strBillingPeriod				= $strBillingPeriodStartMonth;
	
	if ($strBillingPeriodStartYear !== $strBillingPeriodEndYear)
	{
		$strBillingPeriod			.= " {$strBillingPeriodStartYear} / {$strBillingPeriodEndMonth} {$strBillingPeriodEndYear}";
	}
	elseif ($strBillingPeriodStartMonth !== $strBillingPeriodEndMonth)
	{
		$strBillingPeriod			.= " / {$strBillingPeriodEndMonth} {$strBillingPeriodEndYear}";
	}
	else
	{
		$strBillingPeriod			.= " {$strBillingPeriodStartYear}";
	}
	
	$selInvoices		= new StatementSelect(	"Invoice", "*", "invoice_run_id = <invoice_run_id> AND DeliveryMethod = 1");
	
	$selAccountEmail	= new StatementSelect(	"Account JOIN Contact USING (AccountGroup)",
												"Contact.Account, CustomerGroup, Email, FirstName",
												"Account.Id = <Account> AND Email != '' AND Contact.Archived = 0 AND (Contact.Account = Account.Id OR Contact.CustomerContact = 1 OR Account.PrimaryContact = Contact.Id)");
	
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
	
	// Get list of PDFs
	$arrAccountPDFs	= Array();
	if ($bolIncludePDF)
	{
		$strPDFDirectory	= FILES_BASE_PATH."invoices/pdf/{$arrInvoiceRun['InvoiceRun']}";
		if (!is_dir($strPDFDirectory))
		{
			CliEcho("Cannot find PDF Path: '{$strPDFDirectory}'");
			$strPDFDirectory	= FILES_BASE_PATH."invoices/pdf/{$arrInvoiceRun['Id']}";
			if (!is_dir($strPDFDirectory))
			{
				CliEcho("Cannot find PDF Path: ''{$strPDFDirectory}'");
				return FALSE;
			}
		}
		CliEcho("PDFs found at: '{$strPDFDirectory}'");
		
		$arrPDFs		= glob($strPDFDirectory.'/*.pdf');
		foreach ($arrPDFs as $strPath)
		{
			$arrExplode								= explode('.', basename($strPath));
			$arrAccountPDFs[(int)$arrExplode[0]]	= $strPath;
		}
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
		// Get the account number from the invoice, then find the account's email address 			
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
 			
			$strFullInvoiceDate	= date('F jS, Y', strtotime($arrInvoice['CreatedOn']));
 			// Email Content
 			if ($bolIncludePDF)
 			{
				// PDF is included
				$strContent	=	"Your {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']} Invoice for account number {$arrInvoice['Account']} dated {$strFullInvoiceDate} is attached.\n\n" .
								"You have the option of accessing your invoice online via our customer online website {$arrCustomerGroups[$arrDetail['CustomerGroup']]['customer_exit_url']} which is designed to make viewing, paying and making changes to your account more cost and time effective. You can view previous invoices and payments, make changes to your account details and even report a fault with your service. We encourage you to log on and register if you haven't already.  Should you have any difficulties accessing the customer portal please email {$arrCustomerGroups[$arrDetail['CustomerGroup']]['OutboundEmail']}.\n\n" .
								"In the meantime, we will continue to send your monthly invoice via email as a PDF file.\n\n" .
								"Regards,\n\n" .
								"The team at {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']}.";
 			}
 			else
 			{
				// PDF is not included, only reference the Customer Portal
				$strContent	=	"Your {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']} Invoice for account number {$arrInvoice['Account']} dated {$strFullInvoiceDate} is now available for viewing;\n\n" .
								"To view your invoice go to {$arrCustomerGroups[$arrDetail['CustomerGroup']]['customer_exit_url']} and enter your log in information.\n\n" .
								"If you are yet to set up your customer account go to: {$arrCustomerGroups[$arrDetail['CustomerGroup']]['customer_exit_url']} and click on “First Time User?” and follow the prompts. Should you have any difficulties accessing the customer portal please email {$arrCustomerGroups[$arrDetail['CustomerGroup']]['OutboundEmail']} or call our Customer Care Team for assistance.\n\n" .
								"Additional benefits:\n\n" .
								"- View unbilled charges.\n" .
								"- Pay your bill online.\n" .
								"- Make changes to your account or services.\n" .
								"- Sign up to new services.\n" .
								"- Report faults directly at your own convenience.\n\n" .
								"Regards,\n\n" .
								"The team at {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']}.";
 			}
	 		
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
	 			
	 			if ($bolIncludePDF)
	 			{
	 				if (is_file($arrAccountPDFs[$arrInvoice['Account']]))
	 				{
	 					$mimMime->addAttachment(file_get_contents($arrAccountPDFs[$arrInvoice['Account']]), 'application/pdf', "{$arrInvoice['Account']}_{$strInvoiceDate}.pdf", FALSE);
	 				}
	 				else
	 				{
	 					CliEcho("[ FAILED ]\n\t\t\t-Reason: PDF not found at '{$arrAccountPDFs[$arrInvoice['Account']]}'");
	 					continue;
	 				}
 				}
 				
				$strBody = $mimMime->get();				
				$strHeaders = $mimMime->headers($arrHeaders);
	 			$emlMail =& Mail::factory('mail');
	 			
	 			// Uncomment this to Debug
	 			/*$arrDebugEmails		= Array();
	 			$arrDebugEmails[]	= 'rdavis@ybs.net.au';
	 			//$arrDebugEmails[]	= 'turdminator@hotmail.com';
	 			//$arrDebugEmails[]	= 'msergeant@ybs.net.au';
	 			//$arrDebugEmails[]	= 'msergeant@gmail.com';
	 			$strEmail	= (count($arrDebugEmails)) ? implode(', ', $arrDebugEmails) : $strEmail;*/
				
	 				 			
	 			// Send the email
	 			if (!$emlMail->send($strEmail, $strHeaders, $strBody))
	 			{
	 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
	 				continue;
	 			}
	 			
	 			// Uncomment this to Debug
	 			//die;
				
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
