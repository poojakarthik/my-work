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
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();
		
		$arrEmails = Array();
		$arrEmailList = Array();
		$arrExploded = Array();
		$arrPDFtoSend = Array();

		//check if the form was submitted
		if (SubmittedForm('EmailPDFInvoice', 'Email Invoice'))
		{
			foreach (DBO()->Email as $strPropertyName=>$mixProperty)
			{
				// Using the custom email box
				if ($strPropertyName == "Extra" && $mixProperty->Value != '')
				{
					$arrEmails[] = $mixProperty->Value; // NOT WORKING!!!
				}
				
				// Using the checkboxes
				elseif ($strPropertyName != "Extra" && $mixProperty->Value == 1)
				{
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
						array_merge($arrEmails, $arrEmailList);
					}
					
					// Add all emails to internal array
					$arrEmails[] = DBO()->Contact->Email->Value;
				}
			}
			
			// Do stuff with the array
			$strGlob = "/home/vixen_invoices/". DBO()->Invoice->Year->Value . "/" . DBO()->Invoice->Month->Value . "/" . DBO()->Account->Id->Value . "_*.pdf";
			$arrPDFtoSend = glob($strGlob);
			print_r($strGlob);
	
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
	

    //----- DO NOT REMOVE -----//
	
}
