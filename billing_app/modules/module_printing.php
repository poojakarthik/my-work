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
		
		// HEADER
		// get details from invoice & customer
		$arrCustomerData = $this->_selCustomerDetails->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		$arrLastBill = $this->_selLastBill->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		
		// build output
		$arrDefine['InvoiceDetails']	['BillType']		['Value']	= $arrCustomerData['CustomerGroup'];
		$arrDefine['InvoiceDetails']	['Inserts']			['Value']	= "000000";								// FIXME: Actually determine these?  At a later date.
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
			// There are 2 components to the address line
			$arrDefine['InvoiceDetails']	['PropertyName']	['Value']	= $arrCustomerData['AddressLine1'];
			$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= $arrCustomerData['AddressLine2'];
		}
		else
		{
			// There is 1 component to the address line
			$arrDefine['InvoiceDetails']	['PropertyName']	['Value']	= "";
			$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= $arrCustomerData['AddressLine1'];
		}
		$arrDefine['InvoiceDetails']	['Suburb']			['Value']	= $arrCustomerData['Suburb'];
		$arrDefine['InvoiceDetails']	['State']			['Value']	= $arrCustomerData['State'];
		$arrDefine['InvoiceDetails']	['Postcode']		['Value']	= $arrCustomerData['Postcode'];
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Value']	= date("j M Y", strtotime("+".$arrCustomerData['PaymentTerms']." days"));
		$this->_arrFileData[] = $arrDefine['InvoiceDetails'];
		
		// MONTHLY COMPARISON BAR GRAPH
		// TODO: get details from invoice table
		// TODO: build output
		
		// SUMMARY CHARGES
		// get details from servicetype totals
		$arrServiceTypeTotalVars['Account']		= $arrInvoiceDetails['Account'];
		$arrServiceTypeTotalVars['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		$arrServiceTypeTotals = $this->_selServiceTypeTotals->Execute($arrServiceTypeTotalVars);
		// build output
		$arrFileData[] = $arrDefine['ChargeTotalsHeader'];
		foreach($arrServiceTypeTotals as $arrTotal)
		{
			$arrDefine['ChargeTotal']	['ChargeName']		['Value']	= $arrTotal['RecordType'];
			$arrDefine['ChargeTotal']	['ChargeTotal']		['Value']	= $arrTotal['Charge'];
			$arrFileData[] = $arrDefine['ChargeTotal'];
		}
		$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "GST Total";
		$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= $arrInvoiceDetails['Tax'];
		$arrFileData[] = $arrDefine['ChargeTotal'];
		$arrDefine['ChargeTotalsFooter']['BillTotal']		['Value']	= $arrTotal['BillTotal'];
		$arrFileData[] = $arrDefine['ChargeTotalsFooter'];
		
		// PAYMENT DETAILS
		// TODO: get details from account table
		// TODO: build output
		
		// SUMMARY SERVICES
		// get details from servicetype totals
		$this->_selServices->Execute();
		$arrServiceSummaries = $this->_selServices->FetchAll();
		// build output
		$strCurrentService = "";
		$arrServices = $this->_selServices->FetchAll();
		$arrFileData[] = $arrDefine['SvcSummaryHeader'];
		foreach($arrServices as $arrService)
		{
			$arrDefine['SvcSummSvcHeader']		['FNN']				['Value']	= $arrService['FNN'];
			$arrFileData[] = $arrDefine['SvcSummSvcHeader'];
			
			// The individual RecordTypes for each Service
			$this->_selServiceSummaries->Execute(Array('Service' => $arrService['Id']));
			$arrServiceSummaries = $this->_selServiceSummaries->FetchAll();
			foreach($arrServiceSummaries as $arrServiceSummary)
			{
				$arrDefine['SvcSummaryData']	['CallType']		['Value']	= $arrServiceSummary['RecordType'];
				$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= $arrServiceSummary['Records'];
				$arrDefine['SvcSummaryData']	['Charge']			['Value']	= $arrServiceSummary['Charge'];
				$arrFileData[] = $arrDefine['SvcSummaryData'];
			}
			
			$arrDefine['SvcSummSvcFooter']		['TotalCharge']		['Value']	= $arrService['TotalCharge'];
			$arrFileData[] = $arrDefine['SvcSummSvcFooter'];
		}
		$arrFileData[] = $arrDefine['SvcSummaryFooter'];
		
		// DETAILS
		// get list of CDRs grouped by service no, record type
		// ignoring any record types that do not get itemised
		// reset counters
		$strCurrentService		= "";
		$strCurrentRecordType	= "";
		// add start record (70)
		$arrFileData[] = $arrDefine['ItemisedHeader'];
		// for each record
		foreach($arrItemisedCalls as $arrData)
		{
			// if new service
			if($arrData['FNN'] != $strCurrentService)
			{
				// if old service exists
				if ($strCurrentService != "")
				{
					// add service total record (89)
					$arrDefine['ItemSvcFooter']		['TotalCharge']		['Value']	= 23.00;
					$arrFileData[] = $arrDefine['ItemSvcFooter'];					
				}
				// add service record (80)
				$arrDefine['ItemSvcHeader']	['FNN']				['Value']	= "0408295199";
				$arrFileData[] = $arrDefine['ItemSvcHeader'];
				
				$strCurrentService = $arrData['FNN'];
			}
			// if new type
			if($arrData['RecordTypeName'] != $strCurrentRecordType)
			{
				// if old type exists
				if($strCurrentRecordType == "")
				{
					$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= 13.00;
					$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
				}
				// build header record (90)
				$arrDefine['ItemCallTypeHeader']['CallType']		['Value']	= "Mobile to Mobile";
				$arrFileData[] = $arrDefine['ItemCallTypeHeader'];
				// reset counters
				$strCurrentRecordType = $arrData['RecordTypeName'];
			}
			// build charge record
			switch($arrData['DisplayType'])
			{
				// Type 91
				case RECORD_DISPLAY_CALL:
					// TODO
					break;
				// Type 92
				case RECORD_DISPLAY_S_AND_E:
					// TODO
					break;
				// Type 93
				case RECORD_DISPLAY_DATA:
					// TODO
					break;
				// Type 94
				case RECORD_DISPLAY_SMS:
					// TODO
					break;
				// Unknown Record Type (should never happen)
				default:
					// TODO
					break;
			}
		}
		$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= 13.00;
		$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
		// add end record (79)
		$arrFileData[] = $arrDefine['ItemisedFooter'];
		// add invoice footer (19)		
		$arrFileData[] = $arrDefine['InvoiceFooter'];
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
