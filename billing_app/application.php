<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		Billing
 * @author		Jared 'flame' Herbohn, Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Application entry point - create an instance of the application object
$appSkel = new ApplicationBilling($arrConfig);

// finished
echo("\n-- End of Billing --\n");
die();



//----------------------------------------------------------------------------//
// ApplicationBilling
//----------------------------------------------------------------------------//
/**
 * ApplicationBilling
 *
 * Billing Module
 *
 * Billing Module
 *
 *
 * @prefix		app
 *
 * @package		billing_app
 * @class		ApplicationBilling
 */
 class ApplicationBilling extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		$this->_rptBillingReport = new Report("Billing Report for ".date("Y-m-d H:i:s"), "flame@telcoblue.com.au");
	}
	
	//------------------------------------------------------------------------//
	// Execute
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Execute the billing run 
	 *
	 * Generates temporary Invoices. This proccess is scheduled to run once each
	 * day at around 4am. After temporary invoices are created they can be checked
	 * and if there are no problems they can be commited. This allows testing of
	 * the billing run. Bill printing file is also produced here.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Execute()
 	{
		// Report header
		$this->_rptBillingReport->AddMessage("\n".MSG_HORIZONTAL_RULE);
		
		// Empty the temporary invoice table
		// This is safe, because there should be no CDRs with CDR_TEMP_INVOICE status anyway
		$this->_rptBillingReport->AddMessage(MSG_CLEAR_TEMP_TABLE, FALSE);
		if (!$this->Revoke())
		{
			$this->_rptBillingReport->AddMessage(MSG_FAILED."\n");
			
			//return;		// TODO: FIXME - Should we return if this fails??
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK."\n");
		}
				
		// Init Statements
		$arrCDRCols['Status']	= CDR_TEMP_INVOICE;
		$updCDRs				= new StatementUpdate("CDR", "Account = <Account> AND Status = ".CDR_RATED, $arrCDRCols);
		$insTempInvoice			= new StatementInsert("InvoiceTemp");
		
		// get a list of all accounts that require billing today
		//TODO!!!!
		
		// Report
		$this->_rptBillingReport->AddMessage(MSG_BUILD_TEMP_INVOICES."\n");
		
		foreach ($arrAccounts as $arrAccount)
		{
			// Set status of CDR_RATED CDRs for this account to CDR_TEMP_INVOICE
			if(!$updCDRs->Execute($arrCDRCols, Array('Account' => $arrAccount['Id'])))
			{
				// Report and fail out
				$this->_rptBillingReport->AddMessageVariables(MSG_FAILED.MSG_LINE_FAILED, Array('Reason' => "Cannot link CDRs"));
				continue;
			}
			
			// calculate totals
			//TODO!!!
			$fltTotal = 0.0;
			
			// write to temporary invoice table
			$arrInvoiceData['AccountGroup']	= $arrAccount['AccountGroup'];
			$arrInvoiceData['Account']		= $arrAccount['Id'];
			$arrInvoiceData['CreatedOn']	= new MySQLFunction("NOW()");
			$arrInvoiceData['DueOn']		= ""; // TODO: wtfmate?!
			$arrInvoiceData['Credits']		= 0.0; // TODO: wtfmate?!
			$arrInvoiceData['Debits']		= 0.0; // TODO: wtfmate?!
			$arrInvoiceData['Total']		= $fltTotal;
			$arrInvoiceData['Tax']			= $fltTotal + ($fltTotal / 10); // TODO: is this right?
			$arrInvoiceData['Balance']		= 0.0; // TODO: wtfmate?!
			$arrInvoiceData['Status']		= INVOICE_WTF_MATE; // TODO: wtfmate?!
			
			// report error or success
			if(!$insTempInvoice->Execute($arrInvoiceData))
			{
				// Report and fail out
				$this->_rptBillingReport->AddMessageVariables(MSG_FAILED.MSG_LINE_FAILED, Array('Reason' => "Unable to create temporary invoice"));
				continue;
			}
			
			// build output
			//TODO!!! - LATER
			
			// write to billing file
			//TODO!!! - LATER
		}
	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit()
	 *
	 * Commit temporary invoices 
	 *
	 * Commit temporary invoices. Once invoices have been commited they can not
	 * be revoked.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Commit()
 	{
		// copy temporary invoices to invoice table
		$this->_rptBillingReport->AddMessageVariables(MSG_COMMIT_TEMP_INVOICES, FALSE);
		$siqInvoice = new QuerySelectInto();
		if(!$siqInvoice->Execute('Invoice', 'InvoiceTemp'))
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessageVariables(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessageVariables(MSG_OK);
		}
		
		// apply invoice no. to all CDRs for this invoice
		$strQuery  = "UPDATE CDR INNER JOIN Invoice using (Account)";
		$strQuery .= " SET CDR.Invoice = Invoice.Id, CDR.Status = {CDR_INVOICED}";
		$strQuery .= " WHERE CDR.Status = {CDR_TEMP_INVOICE} AND Invoice.Status = {INVOICE_TEMP}";
		$qryCDRInvoice = new Query();
		if(!$qryCDRInvoice->Execute($strQuery))
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessageVariables(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessageVariables(MSG_OK);
		}
	}
	
	//------------------------------------------------------------------------//
	// Revoke
	//------------------------------------------------------------------------//
	/**
	 * Revoke()
	 *
	 * Revoke temporary invoices 
	 *
	 * Revoke all temporary invoices. Once invoices have been commited they can not
	 * be revoked.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Revoke()
 	{
		// empty temp invoice table
		$trqTruncateTempTable = new QueryTruncate();
		$trqTruncateTempTable->Execute("InvoiceTemp");
		
		// report error
		//TODO!!!!	
		
		// change status of CDR_TEMP_INVOICE status CDRs to CDR_RATED
		$updCDRStatus = new StatementUpdate("CDR", "Status = ".CDR_TEMP_INVOICE, Array('Status' => CDR_RATED));
		$updCDRStatus->Execute(Array('Status' => CDR_RATED), Array());
		
		// report error
		//TODO!!!!	
		
		return TRUE;
	}
 }


?>
