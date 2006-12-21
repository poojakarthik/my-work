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
		
		// Init database statements
		$arrColumns['CustomerGroup']	= "Account.CustomerGroup";
		$arrColumns['Account']			= "Account.Id";
		$arrColumns['PaymentTerms']		= "Account.PaymentTerms";
		$arrColumns['FirstName']		= "Contact.FirstName";
		$arrColumns['LastName']			= "Contact.LastName";
		$arrColumns['Suburb']			= "Contact.Suburb";
		$arrColumns['State']			= "Contact.State";
		$arrColumns['Postcode']			= "Contact.Postcode";
		$arrColumns['AddressLine1']		= "Contact.AddressLine1";
		$arrColumns['AddressLine2']		= "Contact.AddressLine2";
		$this->_selCustomerDetails		= new StatementSelect(	"Account JOIN Contact ON Account.PrimaryContact = Contact.Id",
																$arrColumns,
																"Account.Id = <Account>");
		
		$arrColumns = Array();
		$arrColumns[]					= "Total";
		$arrColumns[]					= "Tax";
		$arrColumns[]					= "Balance";
		$arrColumns[]					= "CreatedOn";
		$this->_selLastBills			= new StatementSelect(	"Invoice",
																$arrColumns,
																"Account = <Account>",
																"CreatedOn DESC",
																BILL_PRINT_HISTORY_LIMIT - 1);
																
		$arrColumns = Array();
		$arrColumns['RecordTypeName']	= "RecordType.Name";
		$arrColumns['Charge']			= "ServiceTypeTotals.Charge";
		$this->_selServiceTypeTotals	= new StatementSelect(	"ServiceTypeTotals JOIN RecordType ON ServiceTypeTotals.RecordType = RecordType.Id, " .
																"RecordType JOIN RecordType RType ON RecordType.Group = RType.Id," .
																"ServiceTypeTotals STypeTotals JOIN Service ON STypeTotals.Service = Service.Id",
																$arrColumns,
																"Account = <Account> AND InvoiceRun = <InvoiceRun> AND STypeTotals.Id = ServiceTypeTotals.Id",
																"Service.ServiceType, Service.FNN");
		
		$this->_selServices				= new StatementSelect(	"Service",
																"FNN",
																"Account = <Account> AND Archived = 0");
		
		$arrColumns = Array();
		$arrColumns['RecordTypeName']	= "RecordType.Name";
		$arrColumns['Charge']			= "SUM(CDR.Charge)";
		$this->_selServiceSummaries		= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id," .
																"RecordType JOIN RecordType RType ON RecordType.Group = RType.Id",
																$arrColumns,
																"CDR.Service = <Service> AND (NOT ISNULL(CDR.RatedOn)) AND ISNULL(CDR.Invoice)",
																"RType.Name",
																NULL,
																"RType.Id\n" .
																"HAVING SUM(CDR.Charge) > 0.0");
		
		$arrColumns = Array();
		$arrColumns['Charge']			= "CDR.Charge";
		$arrColumns['FNN']				= "Service.FNN";
		$arrColumns['Source']			= "CDR.Source";
		$arrColumns['Destination']		= "CDR.Destination";
		$arrColumns['StartDatetime']	= "CDR.StartDatetime";
		$arrColumns['EndDatetime']		= "CDR.EndDatetime";
		$arrColumns['Units']			= "CDR.Units";
		$arrColumns['Description']		= "CDR.Description";
		$arrColumns['DestinationCode']	= "CDR.DestinationCode";
		$arrColumns['RecordType']		= "RType.Name";
		$arrColumns['DisplayType']		= "RType.DisplayType";
		$arrColumns['RecordTypeTotal']	= "SUM(CDR.Charge)";
		$this->_selItemisedCalls		= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id," .
																"RecordType JOIN RecordType RType ON RecordType.Group = RType.Id," .
																"Service JOIN CDR ON Service.Id = CDR.Service",
																$arrColumns,
																"RType.Itemised = 1 AND Service.Account = <Account>",
																"Service.FNN, RType.Name",
																NULL,
																"Service.Id, RType.Id");
		
				
		//----------------------------------------------------------------------------//
		// Define the file format
		//----------------------------------------------------------------------------//
		
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
	
		// HEADER
		// get details from invoice & customer
		$this->_selCustomerDetails->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		$bolHasBillHistory = $this->_selLastBills->Execute(Array('Account' => $arrInvoiceDetails['Account'])) ? TRUE : FALSE;
		$arrCustomerData	= $this->_selCustomerDetails->Fetch();
		$arrBillHistory		= $this->_selLastBills->FetchAll();
		
		// build output
		$arrDefine['InvoiceDetails']	['BillType']		['Value']	= $arrCustomerData['CustomerGroup'];
		$arrDefine['InvoiceDetails']	['Inserts']			['Value']	= "000000";								// FIXME: Actually determine these?  At a later date.
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Value']	= date("F y", strtotime("-1 month"));	// FIXME: At a later date.  This is fine for now.
		$arrDefine['InvoiceDetails']	['IssueDate']		['Value']	= date("j M Y");
		$arrDefine['InvoiceDetails']	['AccountNo']		['Value']	= $arrCustomerData['Account'];
		if($bolHasBillHistory)
		{
			// Display the previous bill details
			$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= $arrBillHistory[0]['Total'] + $arrBillHistory[0]['Tax'];						
			$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= $arrBillHistory[0]['Balance'];
		}
		else
		{
			// There is no previous bill
			$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= 0;						
			$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= 0;
		}
		$arrDefine['InvoiceDetails']	['Adjustments']		['Value']	= $arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceDetails']	['Balance']			['Value']	= $arrInvoiceDetails['AccountBalance'];
		$arrDefine['InvoiceDetails']	['BillTotal']		['Value']	= $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax'];
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Value']	= ($arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax']) - $arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceDetails']	['CustomerName']	['Value']	= $arrCustomerData['FirstName']." ".$arrCustomerData['LastName'];
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
		// build output
		$arrDefine['GraphHeader']		['GraphType']		['Value']	= GRAPH_TYPE_VERTICALBAR;
		$arrDefine['GraphHeader']		['GraphTitle']		['Value']	= "Account History";
		$arrDefine['GraphHeader']		['XTitle']			['Value']	= "Month";
		$arrDefine['GraphHeader']		['YTitle']			['Value']	= "\$ Value";
		$arrDefine['GraphData']			['Title']			['Value']	= date("M y", strtotime("-$intCount months"));
		$arrDefine['GraphData']			['Value']			['Value']	= $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax'];
		$arrFileData[] = $arrDefine['GraphData'];
		$intCount = 0;
		foreach($arrBillHistory as $arrBill)
		{
			$arrDefine['GraphData']		['Title']			['Value']	= date("M y", strtotime("-$intCount months"));
			$arrDefine['GraphData']		['Value']			['Value']	= $arrBill['Total'] + $arrBill['Tax'];
			$arrFileData[] = $arrDefine['GraphData'];
			$intCount++;
		}
		$arrDefine['GraphFooter']		['TotalSamples']	['Value']	= $intCount + 1;
		$arrFileData[] = $arrDefine['GraphFooter'];
		
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
		$arrDefine['ChargeTotalsFooter']['BillTotal']		['Value']	= $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax'];
		$arrFileData[] = $arrDefine['ChargeTotalsFooter'];
		
		// PAYMENT DETAILS
		// TODO: get details from account table
		// build output
		$arrDefine['PaymentData']		['BillExpRef']		['Value']	= $arrInvoiceDetails['Account']."9";	// FIXME: Where do we get the last digit from?
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Value']	= $arrInvoiceDetails['Account']."9";	// FIXME: Where do we get the last digit from?
		$arrDefine['PaymentData']		['AccountNo']		['Value']	= $arrInvoiceDetails['Account'];
		$arrDefine['PaymentData']		['DateDue']			['Value']	= date("j M Y", strtotime("+".$arrCustomerData['PaymentTerms']." days"));
		$arrDefine['PaymentData']		['TotalOwing']		['Value']	= ($arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax']) - $arrInvoiceDetails['Credits'];
		$arrDefine['PaymentData']		['CustomerName']	['Value']	= $arrCustomerData['FirstName']." ".$arrCustomerData['LastName'];
		$arrDefine['PaymentData']		['PropertyName']	['Value']	= $arrDefine['InvoiceDetails']['PropertyName']['Value'];
		$arrDefine['PaymentData']		['AddressLine1']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine1']['Value'];
		$arrDefine['PaymentData']		['AddressLine2']	['Value']	= "{$arrDefine['Suburb']}   {$arrDefine['State']}   {$arrDefine['Postcode']}";
		$arrDefine['PaymentData']		['SpecialOffer1']	['Value']	= "FREE One Month Trial for our unlimited " .
																		  "Dial Up Internet. Call customer care to " .
																		  "get connected.";
		$arrDefine['PaymentData']		['SpecialOffer2']	['Value']	= "View your bill online, simply go to " .
																		  "www.telcoblue.com.au click on " .
																		  "Customer Login, and use your " .
																		  "supplied username and password. " .
																		  "See calls made in the last few days plus " .
																		  "your local calls itemised, or copy all your " .
																		  "calls to a spreadsheet for analysis.";
		$arrFileData[] = $arrDefine['PaymentData'];
		
		// SUMMARY SERVICES
		// get details from servicetype totals
		$this->_selServices->Execute(Array('Account' => $arrInvoiceDetails['Account']));
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
		$this->_selItemisedCalls->Execute(Array('Account' => $arrInvoiceDetails['Account']));
		$arrItemisedCalls = $this->_selItemisedCalls->FetchAll();
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
					$arrFileData[] = $arrDefine['ItemSvcFooter'];					
				}
				// add service record (80)
				$arrDefine['ItemSvcHeader']	['FNN']				['Value']	= $arrData['FNN'];
				$arrFileData[] = $arrDefine['ItemSvcHeader'];
				
				$strCurrentService = $arrData['FNN'];
			}
			// if new type
			if($arrData['RecordTypeName'] != $strCurrentRecordType)
			{
				// if old type exists
				if($strCurrentRecordType == "")
				{
					$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= $arrData['RecordTypeTotal'];
					$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
				}
				// build header record (90)
				$arrDefine['ItemCallTypeHeader']['CallType']		['Value']	= $arrData['RecordTypeName'];
				$arrFileData[] = $arrDefine['ItemCallTypeHeader'];
				// reset counters
				$strCurrentRecordType = $arrData['RecordTypeName'];
			}
			// build charge record
			switch($arrData['DisplayType'])
			{
				// Unknown Record Type (should never happen) - just display as a normal Call
				default:
				// Type 91
				case RECORD_DISPLAY_CALL:
					$arrDefine['ItemisedDataCall']	['Date']			['Value']	= date("d/m/Y", strtotime($arrData['StartDatetime']));
					$arrDefine['ItemisedDataCall']	['Time']			['Value']	= date("H:i:s", strtotime($arrData['StartDatetime']));
					$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= $arrData['CalledParty'];
					$intHours		= floor((int)$arrData['Units'] / 3600);
					$strDuration	= "$intHours:".date("i:s", (int)$arrData['Units']);
					$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= $strDuration;
					$arrDefine['ItemisedDataCall']	['Description']		['Value']	= $arrData['Description'];
					$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= $arrData['Charge'];
					$arrFileData[] = $arrDefine['ItemisedDataCall'];
					break;
				// Type 92
				case RECORD_DISPLAY_S_AND_E:
					$strDescription = $arrData['FNN']." : ".$arrData['Description']." (".date("j M Y", strtotime($arrData['StartDatetime']))." to ".date("j M Y", strtotime($arrData['EndDatetime'])).")";
					$arrDefine['ItemisedDataS&E']	['Description']		['Value']	= $strDescription;
					$arrDefine['ItemisedDataS&E']	['Items']			['Value']	= (int)$arrData['Units'];
					$arrDefine['ItemisedDataS&E']	['Charge']			['Value']	= $arrData['Charge'];
					$arrFileData[] = $arrDefine['ItemisedDataCall'];
					break;
				// Type 93
				case RECORD_DISPLAY_DATA:
					$arrDefine['ItemisedDataKB']	['Date']			['Value']	= date("d/m/Y", strtotime($arrData['StartDatetime']));
					$arrDefine['ItemisedDataKB']	['Time']			['Value']	= date("H:i:s", strtotime($arrData['StartDatetime']));
					$arrDefine['ItemisedDataKB']	['CalledParty']		['Value']	= $arrData['CalledParty'];
					$arrDefine['ItemisedDataKB']	['DataTransfer']	['Value']	= (int)$arrData['Units'];
					$arrDefine['ItemisedDataKB']	['Description']		['Value']	= $arrData['Description'];
					$arrDefine['ItemisedDataKB']	['Charge']			['Value']	= $arrData['Charge'];
					$arrFileData[] = $arrDefine['ItemisedDataKB'];
					break;
				// Type 94
				case RECORD_DISPLAY_SMS:
					$arrDefine['ItemisedDataSMS']	['Date']			['Value']	= date("d/m/Y", strtotime($arrData['StartDatetime']));
					$arrDefine['ItemisedDataSMS']	['Time']			['Value']	= date("H:i:s", strtotime($arrData['StartDatetime']));
					$arrDefine['ItemisedDataSMS']	['CalledParty']		['Value']	= $arrData['CalledParty'];
					$arrDefine['ItemisedDataSMS']	['Items']			['Value']	= (int)$arrData['Units'];
					$arrDefine['ItemisedDataSMS']	['Description']		['Value']	= $arrData['Description'];
					$arrDefine['ItemisedDataSMS']	['Charge']			['Value']	= $arrData['Charge'];
					$arrFileData[] = $arrDefine['ItemisedDataSMS'];
					break;
			}
		}
		$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= $arrData['RecordTypeTotal'];
		$arrFileData[] = $arrDefine['ItemCallTypeFooter'];
		// add service total record (89)
		$arrFileData[] = $arrDefine['ItemSvcFooter'];	
		// add end record (79)
		$arrFileData[] = $arrDefine['ItemisedFooter'];
		// add invoice footer (19)		
		$arrFileData[] = $arrDefine['InvoiceFooter'];
		
		// DEBUG
		Debug($arrFileData);
		// DEBUG
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
		
		// set filename internally
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
		
		// set filename internally
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
