<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// account
//----------------------------------------------------------------------------//
/**
 * account
 *
 * contains all ApplicationTemplate extended classes relating to Account functionality
 *
 * contains all ApplicationTemplate extended classes relating to Account functionality
 *
 * @file		account.php
 * @language	PHP
 * @package		framework
 * @author		Nathan Abussi
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateAccount
//----------------------------------------------------------------------------//
/**
 * AppTemplateAccount
 *
 * The AppTemplateAccount class
 *
 * The AppTemplateAccount class.  This incorporates all logic for all pages
 * relating to accounts
 *
 *
 * @package	ui_app
 * @class	AppTemplateAccount
 * @extends	ApplicationTemplate
 */
class AppTemplateInvoice extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// EmailPDFInvoice
	//------------------------------------------------------------------------//
	/**
	 * EmailPDFInvoice()
	 *
	 * Performs the logic for emailing pdf invoices to customers
	 * 
	 * Performs the logic for emailing pdf invoices to customers
	 *
	 * @return		void
	 * @method
	 *
	 */
	function EmailPDFInvoice()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// Set up some variables to use
		$arrEmails = Array(); // List of emails from Other Email address box
		$arrEmailList = Array(); // Emails from checkboxes
		$arrExploded = Array();
		$arrPDFtoSend = Array();
		$intYear = DBO()->Invoice->Year->Value;
		$intMonth = DBO()->Invoice->Month->Value;

		// check if the form was submitted
		if (SubmittedForm('EmailPDFInvoice', 'Email Invoice'))
		{		
			foreach (DBO()->Email as $strPropertyName=>$mixProperty)
			{
				// Using the custom email box
				if ($strPropertyName == "Extra" && $mixProperty->Value != '')
				{
					// Load the email to the emails array
					$arrEmails[] = $mixProperty->Value; 
				}
				
				// Using the checkboxes
				elseif ($strPropertyName != "Extra" && $mixProperty->Value == 1)
				{
					// Load the Contact's info				
					DBO()->Contact->Id = $strPropertyName;
					DBO()->Contact->Load();
					
					// Extract emails from comma-separated list
					foreach (explode(',', DBO()->Contact->Email->Value) as $strEmail)
					{
						$arrExploded = explode(' ', trim($strEmail));
						$arrEmailList[] = $arrExploded[0];
					}
					if (sizeof($arrEmailList) > 1)
					{
						// We have emails from checkboxes, merge with $arrEmails
						array_merge($arrEmails, $arrEmailList);
					}
					
					// Add emails not in a list (i.e single ones)
					$arrEmails[] = DBO()->Contact->Email->Value;
				}
			}
			
			// Get PDF filenames
			$strGlob = "/home/vixen_invoices/". DBO()->Invoice->Year->Value . "/" . DBO()->Invoice->Month->Value . "/" . DBO()->Account->Id->Value . "_*.pdf";
			$arrPDFtoSend = glob($strGlob);
			$strPDFtoSend = $arrPDFtoSend[0];
			
			// Load account details for sending the email
			DBO()->Account->Load();
			
			// Set up the email message
			$strBillingPeriod = date("F", strtotime("2007-$intMonth-01")) . " " . $intYear; // eg 'May 2007'
			$strCustomerGroup = GetConstantDescription(DBO()->Account->CustomerGroup->Value, 'CustomerGroup');
			$strFromAddress = GetConstantDescription(DBO()->Account->CustomerGroup->Value, 'CustomerGroupEmail');
			$strContent = str_replace("<custgrp>", $strCustomerGroup, INVOICE_EMAIL_CONTENT);
			$strSubject = str_replace("<billperiod>", $strBillingPeriod, INVOICE_EMAIL_SUBJECT);
			$arrHeaders = Array('From' => $strFromAddress, 'Subject' => $strSubject);
			
			// Send them
			foreach ($arrEmails as $strEmailAddress)
			{
				$mimMime = new Mail_mime("\n");
				$mimMime->setTXTBody($strContent);
				$mimMime->addAttachment($strPDFtoSend, 'application/pdf');
				$strBody = $mimMime->get();
				$strHeaders = $mimMime->headers($arrHeaders);
				$emlMail =& Mail::factory('mail');
				
				if (!$emlMail->send($strEmailAddress, $strHeaders, $strBody))
				{
					// email sending died :(
					Ajax()->AddCommand("Alert", "Emails not sent successfully. The email addresses may be incorrect or there could be a problem with the email system.");
				}
				else
				{
					// email sending worked!!				
					Ajax()->AddCommand("ClosePopup", "EmailPDFInvoicePopupId");
					Ajax()->AddCommand("Alert", "Email(s) successfully sent.");
				}
			}
		}
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		DBO()->Account->Load();
		
		$strWhere  = "(Account = ". DBO()->Account->Id->Value ." OR ";
		$strWhere .= "(AccountGroup = ". DBO()->Account->AccountGroup->Value ." AND CustomerContact = 1))";
		$strWhere .= " AND Email != '' AND Email != 'no email'";
		
		DBL()->Contact->Where->SetString($strWhere);
		DBL()->Contact->Load();

		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('email_pdf_invoice');

		return TRUE;
	
	}
	
	//------------------------------------------------------------------------//
	// ExportAsCSV
	//------------------------------------------------------------------------//
	/**
	 * ExportAsCSV()
	 *
	 * Performs the logic for exporting an invoice in CSV format, to the user
	 * 
	 * Performs the logic for exporting an invoice in CSV format, to the user
	 * This function expects DBO()->Invoice->Id to be set
	 * 
	 * @return		void
	 * @method
	 */
	function ExportAsCSV()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Invoice->Load())
		{
			// The invoice could not be retrieved.  Show the error page
			DBO()->Error->Message = "The invoice with id: ". DBO()->Invoice->Id->value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// Retrieve the CDRs relating to the invoice
		$arrColumns = Array(	"ServiceType"			=>	"CDRI.ServiceType", 
								"FNN"					=>	"CDRI.FNN",
								"RecordType"			=>	"CDRI.RecordType",
								"RecordTypeDescription"	=>	"RT.Description",
								"Destination"			=>	"CDRI.Destination",
								"StartDatetime"			=>	"CDRI.StartDatetime",
								"Units"					=>	"CDRI.Units",
								"Charge"				=>	"CDRI.Charge");
		$strWhere = "CDRI.Account = <Account> AND CDRI.InvoiceRun = <InvoiceRun>";
		$strTables = "CDRInvoiced AS CDRI INNER JOIN RecordType AS RT ON CDRI.RecordType = RT.Id";
		
		$selCDR = new StatementSelect($strTables, $arrColumns, $strWhere, "ServiceType, FNN, RecordTypeDescription, StartDatetime");
		$intNumRecords = $selCDR->Execute(Array("Account"=> DBO()->Invoice->Account->Value, "InvoiceRun"=> DBO()->Invoice->InvoiceRun->Value));
		
		if ($intNumRecords === FALSE)
		{
			// An error occurred and the cdrs could not be retrieved
			DBO()->Error->Message = "Retrieving CDR records from the database failed, unexpectedly";
			$this->LoadPage('error');
			return TRUE;
		}
				
		$strCallDetailsCSV = "";
		$arrColumnNames = Array("ServiceType", "FNN", "RecordType", "Date", "Time", "Called Party", "Duration", "Charge (\$)");
		$arrColumnOrder = Array("ServiceType", "FNN", "RecordTypeDescription", "Date", "Time", "Destination", "Duration", "Charge");
		$arrBlankRecord = Array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL); 
		
		// Add the head record to the CSV file
		$strCallDetailsCSV = MakeCSVLine($arrColumnNames);
		
		// These variables are used to work out where blank records should be made in the csv file
		$intLastRecordType	= NULL;
		$intLastFNN			= NULL;
		
		// Add each call (CDR) to the CSV file
		while (($arrCDR = $selCDR->Fetch()) !== FALSE)
		{
			if (($intLastFNN !== NULL) && (($intLastRecordType != $arrCDR['RecordType']) || ($intLastFNN != $arrCDR['FNN']))) 
			{
				// Either the FNN has changed or the RecordType has changed from 
				// that of the last record added to the CSV file.  Stick in a blank record
				$strCallDetailsCSV .= MakeCSVLine($arrBlankRecord);
			}
			
			// Set up the values for the record
			$intStartTime			= strtotime($arrCDR['StartDatetime']);
			$arrCDR['ServiceType']	= GetConstantDescription($arrCDR['ServiceType'], "ServiceType");
			$arrCDR['Date']			= date("d/m/Y", $intStartTime);
			$arrCDR['Time']			= date("H:i:s", $intStartTime);
			$arrCDR['Duration']		= date("H:i:s", mktime(0, 0, $arrCDR['Units'], 0, 0, 0));
			
			$strCallDetailsCSV .= MakeCSVLine($arrCDR, $arrColumnOrder);
			
			// Update the details used for spacing 
			$intLastFNN = $arrCDR['FNN'];
			$intLastRecordType = $arrCDR['RecordType'];
		}
		
		// Build the Filename (<InvoiceDate>_<AccountId>_<InvoiceId>.csv)
		$strFilename = date("Ymd", strtotime(DBO()->Invoice->CreatedOn->Value)) . "_" . DBO()->Invoice->Account->Value .
							"_". DBO()->Invoice->Id->Value .".csv";
							
		// Send the csv file to the user
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"$strFilename\"");
		echo $strCallDetailsCSV;
		exit;
	}

    //----- DO NOT REMOVE -----//
	
}
?>