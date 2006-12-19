<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_printing
//----------------------------------------------------------------------------//
/**
 * module_printing
 *
 * Module for Bill Printing
 *
 * Module for Bill Printing
 *
 * @file		module_printing.php
 * @language	PHP
 * @package		billing
 * @author		Jared 'flame' Herbohn, Rich 'Waste' Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// BillingModulePrint
//----------------------------------------------------------------------------//
/**
 * BillingModulePrint
 *
 * Billing module for Bill Printing
 *
 * Billing module for Bill Printing
 *
 * @prefix		bil
 *
 * @package		billing
 * @class		BillingModulePrint
 */
 class BillingModulePrint
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for BillingModulePrint
	 *
	 * Constructor method for BillingModulePrint
	 *
	 * @return		BillingModulePrint
	 *
	 * @method
	 */
 	function __construct($ptrThisDB, $arrConfig)
 	{
		// Set up the database reference
		$this->db = $ptrThisDB;
				
		//----------------------------------------------------------------------------//
		// Define the file format
		//----------------------------------------------------------------------------//
		
		//TODO!!!! - Include the billprint define file
		$this->_arrDefine = $arrConfig['BillPrintDefine'];
		
		//----------------------------------------------------------------------------//


 	}
 	
	//------------------------------------------------------------------------//
	// Clean()
	//------------------------------------------------------------------------//
	/**
	 * Clean()
	 *
	 * Cleans the database table where our data is stored
	 *
	 * Cleans the database table where our data is stored
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function Clean()
 	{
		// Truncate the InvoiceOutput table
		$qryTruncateInvoiceOutput = new QueryTruncate();
		if (!$qryTruncateInvoiceOutput->Execute("InvoiceOutput"))
		{
			// There was an error
			return FALSE;
		}
		
		return TRUE;
 	}
 	
 	//------------------------------------------------------------------------//
	// AddInvoice()
	//------------------------------------------------------------------------//
	/**
	 * AddInvoice()
	 *
	 * Adds an invoice to the bill
	 *
	 * Adds an invoice to the bill
	 * 
	 * @param		array		$arrInvoiceDetails		Associative array of details for this Invoice
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function AddInvoice($arrInvoiceDetails)
 	{
		$arrDefine = $this->_arrDefine;
	
		// Retrieve the data we'll need to do the invoice 
		//TODO!!!!
		// Account Details
		$arrCustomerData = $this->_selCustomerDetails->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		$arrLastBill = $this->_selLastBill->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		
		// HEADER
		// get details from invoice & customer
		// build output
		$arrDefine['InvoiceDetails']	['BillType']		['Value']	= $arrCustomerData['CustomerGroup'];
		$arrDefine['InvoiceDetails']	['Inserts']			['Value']	= "000000";								// FIXME: Actually determine these?  At a later date.
		$arrDefine['InvoiceDetails']	['InvoiceNo']		['Value']	= "<Invoice#>";							// Temporary value that is str_replace'd in Commit()
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Value']	= date("F y", strtotime("-1 month"));	// FIXME: At a later date.  This is fine for now.
		$arrDefine['InvoiceDetails']	['IssueDate']		['Value']	= date("j M Y");
		$arrDefine['InvoiceDetails']	['AccountNo']		['Value']	= $arrCustomerData['Account.Id'];
		$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= $arrLastBill['Total'] + $arrLastBill['Tax'];						
		$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= $arrLastBill['Balance'];				// TODO: Get last bill
		$arrDefine['InvoiceDetails']	['Adjustments']		['Value']	= $arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceDetails']	['Balance']			['Value']	= $arrInvoiceDetails['AccountBalance'];
		$arrDefine['InvoiceDetails']	['BillTotal']		['Value']	= $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax'];
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Value']	= ($arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax']) - $arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceDetails']	['CustomerName']	['Value']	= $arrCustomerData['Contact.FirstName']." ".$arrCustomerData['Contact.LastName'];
		if($arrCustomerData['Account.Address2'])
		{
			$arrDefine['InvoiceDetails']	['PropertyName']	['Value']	= $arrCustomerData['AddressLine1'];
			$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= $arrCustomerData['AddressLine2'];
		}
		else
		{
			$arrDefine['InvoiceDetails']	['PropertyName']	['Value']	= "";
			$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= $arrCustomerData['AddressLine1'];
		}
		$arrDefine['InvoiceDetails']	['Suburb']			['Value']	= $arrCustomerData['Suburb'];
		$arrDefine['InvoiceDetails']	['State']			['Value']	= $arrCustomerData['State'];
		$arrDefine['InvoiceDetails']	['Postcode']		['Value']	= $arrCustomerData['Postcode'];
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Value']	= date("j M Y", strtotime("+".$arrCustomerData['PaymentTerms']." days"));
		$this->_arrFileData[] = $arrDefine['InvoiceDetails'];
		
		// SUMMARY CHARGES
		// get details from servicetype totals
		// build output
		
		// SUMMARY SERVICES
		// get details from servicetype totals
		// build output
		
		// DETAILS
		// get list of CDRs grouped by service no, record type
		// ignoring any record types that do not get itemised
		// reset counters
		// add start record (70)
		// for each record
			// if new service
				// if old service exists
					// add service total record (89)
				// add service record (80)
			// if new type
				// if old type exists
					// build type total record (99)
				// build header record (90)
				// reset counters
			// build charge record (91 || 92)
		// add end record (79)
 	}
 	
 	//------------------------------------------------------------------------//
	// BuildOutput()
	//------------------------------------------------------------------------//
	/**
	 * BuildOutput()
	 *
	 * Builds the bill file
	 *
	 * Builds the bill file
	 *
	 * @return		string	filename
	 *
	 * @method
	 */
 	function BuildOutput()
 	{
		// generate filename
		$strFilename = "tbl".date("Y-m-d").".bof";
		
		// Use a MySQL select into file Query to generate the file
		//TODO!!!!
		
		// create metadata file
		// TODO!!!!
		
		// zip files
		//TODO!!!!
		
		// set filename internaly
		//TODO!!!!
		
		// return filename
		return $strFilename;
 	}
 	
 	//------------------------------------------------------------------------//
	// SendOutput()
	//------------------------------------------------------------------------//
	/**
	 * SendOutput()
	 *
	 * Sends the bill file
	 *
	 * Sends the bill file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function SendOutput()
 	{
		// Upload to FTP server
		//TODO!!!!
 	}
	
	//------------------------------------------------------------------------//
	// BuildSample()
	//------------------------------------------------------------------------//
	/**
	 * BuildSample()
	 *
	 * Builds a sample bill file
	 *
	 * Builds a sample bill file
	 *
	 * @return		string	filename
	 *
	 * @method
	 */
 	function BuildSample()
 	{
		// generate filename
		$strFilename = "tbl".date("Y-m-d").".bof";
		
		// Use a MySQL select into file Query to generate the file
		//TODO!!!!
		
		// create metadata file
		// TODO!!!!
		
		// zip files
		//TODO!!!!
		
		// set filename internaly
		//TODO!!!!
		
		// return filename
		return $strFilename;
 	}
 	
 	//------------------------------------------------------------------------//
	// SendSample()
	//------------------------------------------------------------------------//
	/**
	 * SendOutput()
	 *
	 * Sends a sample bill file
	 *
	 * Sends a sample bill file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function SendSample()
 	{
		// Upload to FTP server
		//TODO!!!!
 	}
 }

?>
