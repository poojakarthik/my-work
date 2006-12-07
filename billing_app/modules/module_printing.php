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
 * @author		Jared 'flame' Herbohn
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
 	function __construct($ptrThisDB)
 	{
		// Set up the database reference
		$this->db = $ptrThisDB;
				
		//----------------------------------------------------------------------------//
		// Define the file format
		//----------------------------------------------------------------------------//
		
		// Invoice Details record
		$arrDefine['InvoiceDetails']	['RecordType']		['Start']	= 0;
		$arrDefine['InvoiceDetails']	['RecordType']		['Length']	= 2;
		$arrDefine['InvoiceDetails']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['InvoiceDetails']	['RecordType']		['Value']	= "10";
		
		$arrDefine['InvoiceDetails']	['InvoiceNo']		['Start']	= 2;
		$arrDefine['InvoiceDetails']	['InvoiceNo']		['Length']	= 10;
		$arrDefine['InvoiceDetails']	['InvoiceNo']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['InvoiceDetails']	['InvoiceNo']		['Value']	= "<INVOICE#>";
		
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Start']	= 12;
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Length']	= 12;
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['InvoiceDetails']	['IssueDate']		['Start']	= 24;
		$arrDefine['InvoiceDetails']	['IssueDate']		['Length']	= 11;
		$arrDefine['InvoiceDetails']	['IssueDate']		['Type']	= BILL_TYPE_LONGDATE;
		
		$arrDefine['InvoiceDetails']	['AccountNo']		['Start']	= 35;
		$arrDefine['InvoiceDetails']	['AccountNo']		['Length']	= 20;
		$arrDefine['InvoiceDetails']	['AccountNo']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrDefine['InvoiceDetails']	['OpeningBalance']	['Start']	= 55;
		$arrDefine['InvoiceDetails']	['OpeningBalance']	['Length']	= 13;
		$arrDefine['InvoiceDetails']	['OpeningBalance']	['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['InvoiceDetails']	['WeReceived']		['Start']	= 68;
		$arrDefine['InvoiceDetails']	['WeReceived']		['Length']	= 13;
		$arrDefine['InvoiceDetails']	['WeReceived']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['InvoiceDetails']	['Adjustments']		['Start']	= 81;
		$arrDefine['InvoiceDetails']	['Adjustments']		['Length']	= 13;
		$arrDefine['InvoiceDetails']	['Adjustments']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['InvoiceDetails']	['Balance']			['Start']	= 94;
		$arrDefine['InvoiceDetails']	['Balance']			['Length']	= 13;
		$arrDefine['InvoiceDetails']	['Balance']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['InvoiceDetails']	['BillTotal']		['Start']	= 107;
		$arrDefine['InvoiceDetails']	['BillTotal']		['Length']	= 13;
		$arrDefine['InvoiceDetails']	['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Start']	= 120;
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Length']	= 13;
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['InvoiceDetails']	['CustomerName']	['Start']	= 133;
		$arrDefine['InvoiceDetails']	['CustomerName']	['Length']	= 60;
		$arrDefine['InvoiceDetails']	['CustomerName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['InvoiceDetails']	['PropertyName']	['Start']	= 193;
		$arrDefine['InvoiceDetails']	['PropertyName']	['Length']	= 60;
		$arrDefine['InvoiceDetails']	['PropertyName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['InvoiceDetails']	['AddressLine1']	['Start']	= 253;
		$arrDefine['InvoiceDetails']	['AddressLine1']	['Length']	= 60;
		$arrDefine['InvoiceDetails']	['AddressLine1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['InvoiceDetails']	['AddressLine2']	['Start']	= 313;
		$arrDefine['InvoiceDetails']	['AddressLine2']	['Length']	= 60;
		$arrDefine['InvoiceDetails']	['AddressLine2']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Start']	= 373;
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Length']	= 11;
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Type']	= BILL_TYPE_LONGDATE;
		
		
		// Account History Bargraph record
		$arrDefine['BarGraph']			['RecordType']		['Start']	= 0;
		$arrDefine['BarGraph']			['RecordType']		['Length']	= 2;
		$arrDefine['BarGraph']			['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['BarGraph']			['RecordType']		['Value']	= "11";
		
		$arrDefine['BarGraph']			['Title']			['Start']	= 2;
		$arrDefine['BarGraph']			['Title']			['Length']	= 20;
		$arrDefine['BarGraph']			['Title']			['Type']	= BILL_TYPE_CHAR;
		$arrDefine['BarGraph']			['Title']			['Value']	= "Account History";
		
		$arrDefine['BarGraph']			['XTitle']			['Start']	= 22;
		$arrDefine['BarGraph']			['XTitle']			['Length']	= 10;
		$arrDefine['BarGraph']			['XTitle']			['Type']	= BILL_TYPE_CHAR;
		$arrDefine['BarGraph']			['XTitle']			['Value']	= "Month";
		
		$arrDefine['BarGraph']			['YTitle']			['Start']	= 32;
		$arrDefine['BarGraph']			['YTitle']			['Length']	= 10;
		$arrDefine['BarGraph']			['YTitle']			['Type']	= BILL_TYPE_CHAR;
		$arrDefine['BarGraph']			['YTitle']			['Value']	= "$ Value";
		
		$arrDefine['BarGraph']			['YMax']			['Start']	= 42;
		$arrDefine['BarGraph']			['YMax']			['Length']	= 5;
		$arrDefine['BarGraph']			['YMax']			['Type']	= BILL_TYPE_INTEGER;
		
		
		// Account History Bargraph Data record
		$arrDefine['BarGraphData']		['RecordType']		['Start']	= 0;
		$arrDefine['BarGraphData']		['RecordType']		['Length']	= 2;
		$arrDefine['BarGraphData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['BarGraphData']		['RecordType']		['Value']	= "12";
		
		$arrDefine['BarGraphData']		['Title']			['Start']	= 2;
		$arrDefine['BarGraphData']		['Title']			['Length']	= 6;
		$arrDefine['BarGraphData']		['Title']			['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['BarGraphData']		['Value']			['Start']	= 8;
		$arrDefine['BarGraphData']		['Value']			['Length']	= 11;
		$arrDefine['BarGraphData']		['Value']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		// Charge Totals record
		$arrDefine['ChargeTotals']		['RecordType']		['Start']	= 0;
		$arrDefine['ChargeTotals']		['RecordType']		['Length']	= 2;
		$arrDefine['ChargeTotals']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['ChargeTotals']		['RecordType']		['Value']	= "13";
		
		$arrDefine['ChargeTotals']		['ChargeName']		['Start']	= 2;
		$arrDefine['ChargeTotals']		['ChargeName']		['Length']	= 40;
		$arrDefine['ChargeTotals']		['ChargeName']		['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Start']	= 42;
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Length']	= 11;
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		// Bill Total record
		$arrDefine['BillTotal']			['RecordType']		['Start']	= 0;
		$arrDefine['BillTotal']			['RecordType']		['Length']	= 2;
		$arrDefine['BillTotal']			['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['BillTotal']			['RecordType']		['Value']	= "14";
		
		$arrDefine['BillTotal']			['BillTotal']		['Start']	= 2;
		$arrDefine['BillTotal']			['BillTotal']		['Length']	= 11;
		$arrDefine['BillTotal']			['BillTotal']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		// Payment Data record
		$arrDefine['PaymentData']		['RecordType']		['Start']	= 0;
		$arrDefine['PaymentData']		['RecordType']		['Length']	= 2;
		$arrDefine['PaymentData']		['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['PaymentData']		['RecordType']		['Value']	= "15";
		
		$arrDefine['PaymentData']		['BillExpBillerId']	['Start']	= 2;
		$arrDefine['PaymentData']		['BillExpBillerId']	['Length']	= 6;
		$arrDefine['PaymentData']		['BillExpBillerId']	['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['PaymentData']		['BillExpBillerId']	['Value']	= 000376;
		
		$arrDefine['PaymentData']		['BillExpRef']		['Start']	= 8;
		$arrDefine['PaymentData']		['BillExpRef']		['Length']	= 25;
		$arrDefine['PaymentData']		['BillExpRef']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrDefine['PaymentData']		['BPayBillerId']	['Start']	= 33;
		$arrDefine['PaymentData']		['BPayBillerId']	['Length']	= 5;
		$arrDefine['PaymentData']		['BPayBillerId']	['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['PaymentData']		['BPayBillerId']	['Value']	= 63412;
		
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Start']	= 38;
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Length']	= 25;
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Type']	= BILL_TYPE_INTEGER;
		
		$arrDefine['PaymentData']		['AccountNo']		['Start']	= 53;
		$arrDefine['PaymentData']		['AccountNo']		['Length']	= 20;
		$arrDefine['PaymentData']		['AccountNo']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrDefine['PaymentData']		['DateDue']			['Start']	= 73;
		$arrDefine['PaymentData']		['DateDue']			['Length']	= 13;
		$arrDefine['PaymentData']		['DateDue']			['Type']	= BILL_TYPE_LONGDATE;
		
		$arrDefine['PaymentData']		['TotalOwing']		['Start']	= 86;
		$arrDefine['PaymentData']		['TotalOwing']		['Length']	= 11;
		$arrDefine['PaymentData']		['TotalOwing']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		$arrDefine['PaymentData']		['CustomerName']	['Start']	= 97;
		$arrDefine['PaymentData']		['CustomerName']	['Length']	= 60;
		$arrDefine['PaymentData']		['CustomerName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['PaymentData']		['PropertyName']	['Start']	= 157;
		$arrDefine['PaymentData']		['PropertyName']	['Length']	= 60;
		$arrDefine['PaymentData']		['PropertyName']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['PaymentData']		['AddressLine1']	['Start']	= 217;
		$arrDefine['PaymentData']		['AddressLine1']	['Length']	= 60;
		$arrDefine['PaymentData']		['AddressLine1']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['PaymentData']		['AddressLine2']	['Start']	= 277;
		$arrDefine['PaymentData']		['AddressLine2']	['Length']	= 60;
		$arrDefine['PaymentData']		['AddressLine2']	['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['PaymentData']		['SpecialOffer']	['Start']	= 337;
		$arrDefine['PaymentData']		['SpecialOffer']	['Length']	= 250;
		$arrDefine['PaymentData']		['SpecialOffer']	['Type']	= BILL_TYPE_CHAR;
		
		
		// Service Summary Service record
		$arrDefine['ServiceSummary']	['RecordType']		['Start']	= 0;
		$arrDefine['ServiceSummary']	['RecordType']		['Length']	= 2;
		$arrDefine['ServiceSummary']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['ServiceSummary']	['RecordType']		['Value']	= "20";
		
		$arrDefine['ServiceSummary']	['FNN']				['Start']	= 2;
		$arrDefine['ServiceSummary']	['FNN']				['Length']	= 10;
		$arrDefine['ServiceSummary']	['FNN']				['Type']	= BILL_TYPE_INTEGER;
		
		$arrDefine['ServiceSummary']	['TotalCharge']		['Start']	= 12;
		$arrDefine['ServiceSummary']	['TotalCharge']		['Length']	= 11;
		$arrDefine['ServiceSummary']	['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		// Service Summary Data record
		$arrDefine['SvcSummaryData']	['RecordType']		['Start']	= 0;
		$arrDefine['SvcSummaryData']	['RecordType']		['Length']	= 2;
		$arrDefine['SvcSummaryData']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['SvcSummaryData']	['RecordType']		['Value']	= "21";
		
		$arrDefine['SvcSummaryData']	['CallType']		['Start']	= 2;
		$arrDefine['SvcSummaryData']	['CallType']		['Length']	= 40;
		$arrDefine['SvcSummaryData']	['CallType']		['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['SvcSummaryData']	['CallCount']		['Start']	= 22;
		$arrDefine['SvcSummaryData']	['CallCount']		['Length']	= 10;
		$arrDefine['SvcSummaryData']	['CallCount']		['Type']	= BILL_TYPE_INTEGER;
		
		$arrDefine['SvcSummaryData']	['Charge']			['Start']	= 32;
		$arrDefine['SvcSummaryData']	['Charge']			['Length']	= 11;
		$arrDefine['SvcSummaryData']	['Charge']			['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		// Itemised Call Service record
		$arrDefine['ItemisedService']	['RecordType']		['Start']	= 0;
		$arrDefine['ItemisedService']	['RecordType']		['Length']	= 2;
		$arrDefine['ItemisedService']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['ItemisedService']	['RecordType']		['Value']	= "30";
		
		$arrDefine['ItemisedService']	['FNN']				['Start']	= 2;
		$arrDefine['ItemisedService']	['FNN']				['Length']	= 10;
		$arrDefine['ItemisedService']	['FNN']				['Type']	= BILL_TYPE_INTEGER;
		
		
		// Itemised Call Type record
		$arrDefine['ItemisedCallType']	['RecordType']		['Start']	= 0;
		$arrDefine['ItemisedCallType']	['RecordType']		['Length']	= 2;
		$arrDefine['ItemisedCallType']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['ItemisedCallType']	['RecordType']		['Value']	= "31";
		
		$arrDefine['ItemisedCallType']	['CallType']		['Start']	= 2;
		$arrDefine['ItemisedCallType']	['CallType']		['Length']	= 20;
		$arrDefine['ItemisedCallType']	['CallType']		['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['ItemisedCallType']	['TotalCharge']		['Start']	= 22;
		$arrDefine['ItemisedCallType']	['TotalCharge']		['Length']	= 11;
		$arrDefine['ItemisedCallType']	['TotalCharge']		['Type']	= BILL_TYPE_SHORTCURRENCY;
		
		
		// Itemised Call Data Call record
		$arrDefine['ItemisedDataCall']	['RecordType']		['Start']	= 0;
		$arrDefine['ItemisedDataCall']	['RecordType']		['Length']	= 2;
		$arrDefine['ItemisedDataCall']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['ItemisedDataCall']	['RecordType']		['Value']	= "32";
		
		$arrDefine['ItemisedDataCall']	['Date']			['Start']	= 2;
		$arrDefine['ItemisedDataCall']	['Date']			['Length']	= 10;
		$arrDefine['ItemisedDataCall']	['Date']			['Type']	= BILL_TYPE_SHORTDATE;
		
		$arrDefine['ItemisedDataCall']	['Time']			['Start']	= 12;
		$arrDefine['ItemisedDataCall']	['Time']			['Length']	= 8;
		$arrDefine['ItemisedDataCall']	['Time']			['Type']	= BILL_TYPE_TIME;
		
		$arrDefine['ItemisedDataCall']	['CalledParty']		['Start']	= 20;
		$arrDefine['ItemisedDataCall']	['CalledParty']		['Length']	= 30;
		$arrDefine['ItemisedDataCall']	['CalledParty']		['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['ItemisedDataCall']	['Duration']		['Start']	= 20;
		$arrDefine['ItemisedDataCall']	['Duration']		['Length']	= 8;
		$arrDefine['ItemisedDataCall']	['Duration']		['Type']	= BILL_TYPE_DURATION;
		
		$arrDefine['ItemisedDataCall']	['Charge']			['Start']	= 28;
		$arrDefine['ItemisedDataCall']	['Charge']			['Length']	= 13;
		$arrDefine['ItemisedDataCall']	['Charge']			['Type']	= BILL_TYPE_LONGCURRENCY;
		
		
		// Itemised Call Data S&E record
		$arrDefine['ItemisedDataS&E']	['RecordType']		['Start']	= 0;
		$arrDefine['ItemisedDataS&E']	['RecordType']		['Length']	= 2;
		$arrDefine['ItemisedDataS&E']	['RecordType']		['Type']	= BILL_TYPE_INTEGER;
		$arrDefine['ItemisedDataS&E']	['RecordType']		['Value']	= "33";
		
		$arrDefine['ItemisedDataS&E']	['Description']		['Start']	= 2;
		$arrDefine['ItemisedDataS&E']	['Description']		['Length']	= 50;
		$arrDefine['ItemisedDataS&E']	['Description']		['Type']	= BILL_TYPE_CHAR;
		
		$arrDefine['ItemisedDataS&E']	['Charge']			['Start']	= 52;
		$arrDefine['ItemisedDataS&E']	['Charge']			['Length']	= 13;
		$arrDefine['ItemisedDataS&E']	['Charge']			['Type']	= BILL_TYPE_LONGCURRENCY;
		
		$this->_arrDefine = $arrDefine;
		
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
		// TODO	
	
		// Retrieve the data we'll need to do the invoice 
		
		$arrCustomerDetails = $this->_selCustomerDetails->Execute();
		
		
		
		
		// Invoice Details record
		
		$arrDefine['InvoiceDetails']	['InvoiceNo']		['Value']	= 3000987654;
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Value']	= "December 06";
		$arrDefine['InvoiceDetails']	['IssueDate']		['Value']	= "6 Dec 2006";
		$arrDefine['InvoiceDetails']	['AccountNo']		['Value']	= 1000123456;
		$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= "500.12";
		$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= "500.12";
		$arrDefine['InvoiceDetails']	['Adjustments']		['Value']	= "0.00";
		$arrDefine['InvoiceDetails']	['Balance']			['Value']	= "0.00";
		$arrDefine['InvoiceDetails']	['BillTotal']		['Value']	= "655.35";
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Value']	= "655.35";
		$arrDefine['InvoiceDetails']	['CustomerName']	['Value']	= "Shooter McGaven";
		$arrDefine['InvoiceDetails']	['PropertyName']	['Value']	= "The Green";
		$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= "10 Happy Way";
		$arrDefine['InvoiceDetails']	['AddressLine2']	['Value']	= "GILMORE   QLD   4531";
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Value']	= "20 Dec 2006";
		$arrFileData[] = $arrDefine['InvoiceDetails'];
		
		// Account History Bargraph record
		$arrDefine['BarGraph']			['YMax']			['Value']	= 700;
		$arrFileData[] = $arrDefine['BarGraph'];
		
		// Account History Bargraph Data record
		$arrDefine['BarGraphData']		['Title']			['Value']	= "Jul 06";
		$arrDefine['BarGraphData']		['Value']			['Value']	= 22.22;
		$arrFileData[] = $arrDefine['BarGraphData'];
		$arrDefine['BarGraphData']		['Title']			['Value']	= "Aug 06";
		$arrDefine['BarGraphData']		['Value']			['Value']	= 123.45;
		$arrFileData[] = $arrDefine['BarGraphData'];
		$arrDefine['BarGraphData']		['Title']			['Value']	= "Sep 06";
		$arrDefine['BarGraphData']		['Value']			['Value']	= 680.15;
		$arrFileData[] = $arrDefine['BarGraphData'];
		$arrDefine['BarGraphData']		['Title']			['Value']	= "Oct 06";
		$arrDefine['BarGraphData']		['Value']			['Value']	= 2.15;
		$arrFileData[] = $arrDefine['BarGraphData'];
		$arrDefine['BarGraphData']		['Title']			['Value']	= "Nov 06";
		$arrDefine['BarGraphData']		['Value']			['Value']	= 500.12;
		$arrFileData[] = $arrDefine['BarGraphData'];
		$arrDefine['BarGraphData']		['Title']			['Value']	= "Dec 06";
		$arrDefine['BarGraphData']		['Value']			['Value']	= 655.35;
		$arrFileData[] = $arrDefine['BarGraphData'];
		
		// Charge Totals record
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Local Calls";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 25.12;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "National Calls";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 25.25;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Calls to Mobiles";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 12.12;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Calls to 13/1300 Numbers";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 5.12;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "13/1300 Inbound";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 20.12;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Mobile to National";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 25.02;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Mobile to Mobile";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 25.10;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Mobile - Other Charges";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 15.12;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Other Call Types";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 21.12;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "Service & Equipment";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 25.11;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		$arrDefine['ChargeTotals']		['ChargeName']		['Value']	= "GST Total";
		$arrDefine['ChargeTotals']		['ChargeTotal']		['Value']	= 65.54;
		$arrFileData[] = $arrDefine['ChargeTotals'];
		
		// Bill Total record
		$arrDefine['BillTotal']			['BillTotal']		['Value']	= 655.35;
		$arrFileData[] = $arrDefine['BillTotal'];
		
		// Payment Data record
		$arrDefine['PaymentData']		['BillExpRef']		['Value']	= 10001234567;
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Value']	= 10001234567;
		$arrDefine['PaymentData']		['AccountNo']		['Value']	= 1000123456;
		$arrDefine['PaymentData']		['DateDue']			['Value']	= "20 Dec 2006";
		$arrDefine['PaymentData']		['TotalOwing']		['Value']	= 655.35;
		$arrDefine['PaymentData']		['CustomerName']	['Value']	= "Shooter McGaven";
		$arrDefine['PaymentData']		['PropertyName']	['Value']	= "The Green";
		$arrDefine['PaymentData']		['AddressLine1']	['Value']	= "10 Happy Way";
		$arrDefine['PaymentData']		['AddressLine2']	['Value']	= "GILMORE   QLD   4531";
		$arrDefine['PaymentData']		['SpecialOffer']	['Value']	= "FREE One Month Trial for our unlimited " .
																		  "Dial Up Internet. Call customer care to " .
																		  "get connected.\\n" .
																		  "\\n" .
																		  "View your bill online, simply go to " .
																		  "www.telcoblue.com.au click on " .
																		  "Customer Login, and use your " .
																		  "supplied username and password. " .
																		  "See calls made in the last few days plus " .
																		  "your local calls itemised, or copy all your " .
																		  "calls to a spreadsheet for analysis.";
		$arrFileData[] = $arrDefine['PaymentData'];
		
		
		
		
		// Service Summary Service record
		$arrDefine['ServiceSummary']	['FNN']				['Value']	= "0733531219";
		$arrDefine['ServiceSummary']	['TotalCharge']		['Value']	= 23.00;
		$arrFileData[] = $arrDefine['ServiceSummary'];
			// Service Summary Data record
			$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Local Calls";
			$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 5;
			$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 5.00;
			$arrFileData[] = $arrDefine['SvcSummaryData'];
			$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "National Calls";
			$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 13;
			$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 13.00;
			$arrFileData[] = $arrDefine['SvcSummaryData'];
			$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Service & Equipment";
			$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 0;
			$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 10.00;
			$arrFileData[] = $arrDefine['SvcSummaryData'];
		
		
		// Service Summary Service record
		$arrDefine['ServiceSummary']	['FNN']				['Value']	= "0408295199";
		$arrDefine['ServiceSummary']	['TotalCharge']		['Value']	= 50.50;
		$arrFileData[] = $arrDefine['ServiceSummary'];
			// Service Summary Data record
			$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Mobile to Mobile";
			$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 25;
			$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 50.00;
			$arrFileData[] = $arrDefine['SvcSummaryData'];
			$arrDefine['SvcSummaryData']	['CallType']		['Value']	= "Mobile - Other Charges";
			$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= 2;
			$arrDefine['SvcSummaryData']	['Charge']			['Value']	= 0.50;
			$arrFileData[] = $arrDefine['SvcSummaryData'];
		
		
		
		
		
		// Itemised Call Service record
		$arrDefine['ItemisedService']	['FNN']				['Value']	= "0733531219";
		$arrFileData[] = $arrDefine['ItemisedService'];
			// Itemised Call Type record
			$arrDefine['ItemisedCallType']	['CallType']		['Value']	= "National Calls";
			$arrDefine['ItemisedCallType']	['TotalCharge']		['Value']	= 13.00;
			$arrFileData[] = $arrDefine['ItemisedCallType'];
				// Itemised Call Data Call record
				$arrDefine['ItemisedDataCall']	['Date']			['Value']	= "20/11/2006";
				$arrDefine['ItemisedDataCall']	['Time']			['Value']	= "10:54:01";
				$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= 0233531929;
				$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= "12:15";
				$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= "0.1531";
				$arrFileData[] = $arrDefine['ItemisedDataCall'];
				// Itemised Call Data Call record
				$arrDefine['ItemisedDataCall']	['Date']			['Value']	= "21/11/2006";
				$arrDefine['ItemisedDataCall']	['Time']			['Value']	= "11:54:01";
				$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= 0333531929;
				$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= "22:15";
				$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= "2.1531";
				$arrFileData[] = $arrDefine['ItemisedDataCall'];
			// Itemised Call Type record
			$arrDefine['ItemisedCallType']	['CallType']		['Value']	= "Service & Equipment";
			$arrDefine['ItemisedCallType']	['TotalCharge']		['Value']	= 10.00;
			$arrFileData[] = $arrDefine['ItemisedCallType'];
				// Itemised Call Data S&E record
				$arrDefine['ItemisedDataS&E']	['Description']		['Value']	= "0733531219: Business Telephone Line 11 Nov 2006 to 10 Dec 2006";
				$arrDefine['ItemisedDataS&E']	['Charge']			['Value']	= 10.0;
				$arrFileData[] = $arrDefine['ItemisedDataS&E'];
		
		// Itemised Call Service record
		$arrDefine['ItemisedService']	['FNN']				['Value']	= "0408295199";
		$arrFileData[] = $arrDefine['ItemisedService'];
			// Itemised Call Type record
			$arrDefine['ItemisedCallType']	['CallType']		['Value']	= "Mobile to Mobile";
			$arrDefine['ItemisedCallType']	['TotalCharge']		['Value']	= 13.00;
			$arrFileData[] = $arrDefine['ItemisedCallType'];
				// Itemised Call Data Call record
				$arrDefine['ItemisedDataCall']	['Date']			['Value']	= "11/11/2006";
				$arrDefine['ItemisedDataCall']	['Time']			['Value']	= "10:24:01";
				$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= 0415151515;
				$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= "12:15";
				$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= "0.1531";
				$arrFileData[] = $arrDefine['ItemisedDataCall'];
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
	 * @return		boolean
	 *
	 * @method
	 */
 	function BuildOutput()
 	{
		// TODO
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
		// TODO
 	}
 }

?>
