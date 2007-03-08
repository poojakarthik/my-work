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
		
		// Init member variables
		$this->_strFilename		= NULL;
		$this->_strSampleFile	= NULL;
		
		// Init database statements
		$this->_insInvoiceOutput		= new StatementInsert("InvoiceOutput");
		
		$arrColumns['CustomerGroup']	= "Account.CustomerGroup";
		$arrColumns['Account']			= "Account.Id";
		$arrColumns['PaymentTerms']		= "Account.PaymentTerms";
		$arrColumns['FirstName']		= "Contact.FirstName";
		$arrColumns['LastName']			= "Contact.LastName";
		$arrColumns['Suburb']			= "Account.Suburb";
		$arrColumns['State']			= "Account.State";
		$arrColumns['Postcode']			= "Account.Postcode";
		$arrColumns['AddressLine1']		= "Account.Address1";
		$arrColumns['AddressLine2']		= "Account.Address2";
		$arrColumns['BusinessName']		= "Account.BusinessName";
		$arrColumns['TradingName']		= "Account.TradingName";
		$arrColumns['DeliveryMethod']	= "Account.BillingMethod";
		$this->_selCustomerDetails		= new StatementSelect(	"Account LEFT OUTER JOIN Contact ON Account.PrimaryContact = Contact.Id",
																$arrColumns,
																"Account.Id = <Account>");
		
		$arrColumns = Array();
		$arrColumns[]					= "Total";
		$arrColumns[]					= "Tax";
		$arrColumns[]					= "Balance";
		$arrColumns[]					= "AccountBalance";
		$arrColumns[]					= "CreatedOn";
		$arrColumns[]					= "InvoiceRun";
		$this->_selLastBills			= new StatementSelect(	"Invoice",
																$arrColumns,
																"Account = <Account>",
																"CreatedOn DESC",
																BILL_PRINT_HISTORY_LIMIT - 1);
		/*														
		$arrColumns = Array();
		$arrColumns['RecordTypeName']	= "RType.Description";
		$arrColumns['Charge']			= "SUM(ServiceTypeTotal.Charge)";
		$this->_selServiceTypeTotals	= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, " .
																"RecordType AS RType",
																$arrColumns,
																"RecordType.GroupId = RType.Id AND ServiceTypeTotal.Account = <Account> AND ServiceTypeTotal.InvoiceRun = <InvoiceRun>",
																"ServiceTypeTotal.FNN",
																NULL,
																"RType.Id");*/
		
		$this->_selServices				= new StatementSelect(	"Service LEFT OUTER JOIN ServiceExtension ON Service.Id = ServiceExtension.Service, " .
																"Service Service2 LEFT OUTER JOIN CostCentre ON Service2.CostCentre = CostCentre.Id",
																"Service.FNN AS FNN, Service.Id AS Id, CostCentre.Name AS CostCentre, ServiceExtension.Name AS ExtensionName, ServiceExtension.RangeStart AS RangeStart, ServiceExtension.RangeEnd as RangeEnd",
																"Service.Account = <Account> AND (ISNULL(Service.ClosedOn) OR Service.ClosedOn > NOW()) AND Service.Id = Service2.Id",
																"CostCentre.Name",
																NULL,
																"Service.Id, ServiceExtension.RangeStart");
		
		$this->_selServiceTotal			= new StatementSelect(	"ServiceTotal",
																"TotalCharge",
																"Service = <Service> AND InvoiceRun = <InvoiceRun>");
		
 		$arrColumns = Array();
 		$arrColumns['RecordType']	= "GroupType.Description";
 		$arrColumns['Total']		= "SUM(ServiceTypeTotal.Charge)";
 		$arrColumns['Records']		= "SUM(Records)";
 		$this->_selServiceTypeTotals	= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, RecordType AS GroupType",
 																$arrColumns,
 																"Account = <Account> AND InvoiceRun = <InvoiceRun> AND GroupType.Id = RecordType.GroupId",
 																"ServiceTypeTotal.FNN, GroupType.Description",
 																NULL,
 																"GroupType.Description");
 		
		$this->_selServiceChargesTotal	= new StatementSelect(	"Charge",
																"SUM(Amount) AS Charge, 'Other Charges & Credits' AS RecordType, COUNT(Id) AS Records",
																"Service = <Service> AND InvoiceRun = <InvoiceRun>");
																

		$arrColumns = Array();
		$arrColumns['Charge']			= "Charge.Amount";
		$arrColumns['Description']		= "Charge.Description";
		$arrColumns['ChargeType']		= "Charge.ChargeType";
		$arrColumns['Nature']			= "Charge.Nature";
		$this->_selItemisedCharges		= new StatementSelect(	"Charge JOIN Service ON Service.Id = Charge.Service",
																$arrColumns,
																"Charge.Account = <Account> AND Charge.Service <=> <Service> AND Charge.InvoiceRun = <InvoiceRun>");
		
		/*$arrColumns = Array();
		$arrColumns['Charge']			= "CDR.Charge";
		$arrColumns['FNN']				= "CDR.FNN";
		$arrColumns['Source']			= "CDR.Source";
		$arrColumns['Destination']		= "CDR.Destination";
		$arrColumns['StartDatetime']	= "CDR.StartDatetime";
		$arrColumns['EndDatetime']		= "CDR.EndDatetime";
		$arrColumns['Units']			= "CDR.Units";
		$arrColumns['Description']		= "CDR.Description";
		$arrColumns['DestinationCode']	= "CDR.DestinationCode";
		$arrColumns['RecordTypeName']	= "RType.Name";
		$arrColumns['DisplayType']		= "RType.DisplayType";
		$this->_selItemisedCalls		= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id," .
																"RecordType AS RType",
																$arrColumns,
																"RType.Itemised = 1 AND CDR.Account = <Account> AND RecordType.GroupId = RType.Id AND CDR.Credit = 0 AND CDR.InvoiceRun = <InvoiceRun> AND Status = ".CDR_TEMP_INVOICE,
																"CDR.FNN, RType.Name, CDR.StartDatetime");*/
		$arrColumns = Array();
		$arrColumns['Charge']			= "CDR.Charge";
		$arrColumns['Source']			= "CDR.Source";
		$arrColumns['Destination']		= "CDR.Destination";
		$arrColumns['StartDatetime']	= "CDR.StartDatetime";
		$arrColumns['EndDatetime']		= "CDR.EndDatetime";
		$arrColumns['Units']			= "CDR.Units";
		$arrColumns['Description']		= "CDR.Description";
		$arrColumns['DestinationCode']	= "CDR.DestinationCode";
		$arrColumns['DisplayType']		= "RecordGroup.DisplayType";
		$arrColumns['RecordGroup']		= "RecordGroup.Description";
 		$this->_selItemisedCalls	= new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id" .
 															", RecordType as RecordGroup",
 															$arrColumns,
 															"CDR.Service = <Service> AND " .
 															"RecordGroup.Id = RecordType.GroupId AND " .
 															"RecordGroup.Id = <RecordGroup> AND " .
 															"RecordGroup.Itemised = 1 AND " .
 															"CDR.InvoiceRun = <InvoiceRun> " .
 															"AND (" .
 															"	<RangeStart> IS NULL " .
 															" OR " .
	 														"	CAST(SUBSTRING(CDR.FNN, -2) AS UNSIGNED) BETWEEN <RangeStart> AND <RangeEnd> " .
 															")",
 															"CDR.StartDatetime");
 															
		$this->_selItemisedRecordTypes = new StatementSelect(	"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id, RecordType AS RecordGroup",
																"RecordGroup.Id AS RecordType, RecordGroup.Description AS Description, RecordGroup.DisplayType AS DisplayType", 
	 															"Service = <Service> AND " .
	 															"RecordGroup.Id = RecordType.GroupId AND " .
	 															"RecordGroup.Itemised = 1 AND " .
	 															"CDR.InvoiceRun = <InvoiceRun> AND " .
	 															"(" .
	 															"	<RangeStart> <=> NULL " .
	 															" OR " .
	 															"	CAST(SUBSTRING(CDR.FNN, -2) AS UNSIGNED) BETWEEN <RangeStart> AND <RangeEnd> " .
	 															")",
	 															"CDR.StartDatetime",
	 															NULL,
	 															"RecordGroup.Id");
																
		$this->_selRecordTypeTotal		= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id," .
																"RecordType AS RType",
																"ServiceTypeTotal.Charge AS Charge",
																"RecordType.GroupId = RType.Id AND RType.Name = <RecordTypeName> AND ServiceTypeTotal.FNN = <FNN> AND ServiceTypeTotal.InvoiceRun = <InvoiceRun>",
																NULL,
																"1");
		
		$this->_selWeReceived			= new StatementSelect(	"InvoicePayment",
																"SUM(Amount) AS WeReceived",
																"(InvoiceRun = <ThisInvoiceRun> OR InvoiceRun = <LastInvoiceRun>) AND Account = <Account>");
		
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
		
		$this->_arrInvoiceDetails	= $arrInvoiceDetails;
		$this->_strInvoiceRun		= $arrInvoiceDetails['InvoiceRun'];
		
		// clean the file data array
		$this->_arrFileData = Array();
		
		//--------------------------------------------------------------------//
		//                          FRONT PAGE
		//--------------------------------------------------------------------//
		
		// HEADER
		// get details from invoice & customer
		$arrWhere['Account'] = $arrInvoiceDetails['Account'];
		
		if ($this->_selCustomerDetails->Execute($arrWhere) === FALSE)
		{
			return FALSE;
		}
		
		$bolHasBillHistory	= $this->_selLastBills->Execute(Array('Account' => $arrInvoiceDetails['Account'])) ? TRUE : FALSE;
		$arrCustomerData	= $this->_selCustomerDetails->Fetch();
		$arrBillHistory		= $this->_selLastBills->FetchAll();
		
		// build output
		$arrDefine['InvoiceDetails']	['InvoiceGroup']	['Value']	= $arrCustomerData['CustomerGroup'];
		$arrDefine['InvoiceDetails']	['Inserts']			['Value']	= "000000";								// FIXME: Actually determine these?  At a later date.
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Value']	= date("F y", strtotime("-1 month", time()));	// FIXME: At a later date.  This is fine for now.
		$arrDefine['InvoiceDetails']	['IssueDate']		['Value']	= date("j M Y");
		$arrDefine['InvoiceDetails']	['AccountNo']		['Value']	= $arrInvoiceDetails['Account'];
		if($bolHasBillHistory)
		{
			// Display the previous bill details
			$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= $arrBillHistory[0]['AccountBalance'] + $arrBillHistory[0]['Total'] + $arrBillHistory[0]['Tax'];						
			
			// WeReceived = payments from last invoice + payments from this invoice
			$arrWeReceivedData['LastInvoiceRun']	= $arrBillHistory[0]['InvoiceRun'];
			$arrWeReceivedData['ThisInvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
			$arrWeReceivedData['Account']			= $arrInvoiceDetails['Account'];
			$this->_selWeReceived->Execute($arrWeReceivedData);
			$arrWeReceived = $this->_selWeReceived->Fetch();

			// If there were no payments, this will return NULL, so account for this
			if ($arrWeReceived['WeReceived'] == NULL)
			{
				$arrWeReceived['WeReceived'] = 0.0;
			}
			$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= $arrWeReceived['WeReceived'];
		}
		else
		{
			// There is no previous bill
			$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= 0;						
			$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= 0;
		}
		$arrDefine['InvoiceDetails']	['Adjustments']		['Value']	= $arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceDetails']	['Balance']			['Value']	= $arrInvoiceDetails['AccountBalance'];
		$arrDefine['InvoiceDetails']	['BillTotal']		['Value']	= $arrInvoiceDetails['Balance'];
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Value']	= ((float)$arrInvoiceDetails['Balance'] + (float)$arrInvoiceDetails['AccountBalance']) - (float)$arrInvoiceDetails['Credits'];
		$arrDefine['InvoiceDetails']	['CustomerName']	['Value']	= $arrCustomerData['FirstName']." ".$arrCustomerData['LastName'];
		
		$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= $arrCustomerData['BusinessName'];
		$arrDefine['InvoiceDetails']	['AddressLine2']	['Value']	= $arrCustomerData['TradingName'];
		$arrDefine['InvoiceDetails']	['AddressLine3']	['Value']	= $arrCustomerData['AddressLine1'];
		$arrDefine['InvoiceDetails']	['AddressLine4']	['Value']	= $arrCustomerData['AddressLine2'];

		$arrDefine['InvoiceDetails']	['Suburb']			['Value']	= $arrCustomerData['Suburb'];
		$arrDefine['InvoiceDetails']	['State']			['Value']	= $arrCustomerData['State'];
		$arrDefine['InvoiceDetails']	['Postcode']		['Value']	= $arrCustomerData['Postcode'];
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Value']	= date("j M Y", strtotime("+".$arrCustomerData['PaymentTerms']." days", time()));
		
		$this->_arrFileData[] = $arrDefine['InvoiceDetails'];
		
		// MONTHLY COMPARISON BAR GRAPH
		// build output
		// FIXME
		$arrDefine['GraphHeader']		['GraphType']		['Value']	= GRAPH_TYPE_VERTICALBAR;
		$arrDefine['GraphHeader']		['GraphTitle']		['Value']	= "Account History";
		$arrDefine['GraphHeader']		['XTitle']			['Value']	= "Month";
		$arrDefine['GraphHeader']		['YTitle']			['Value']	= "$ Value";
		$arrDefine['GraphHeader']		['ValueCount']		['Value']	= 1;
		$arrDefine['GraphHeader']		['LegendText1']		['Value']	= "Monthly Spending";
		$this->_arrFileData[] = $arrDefine['GraphHeader'];
		$intCount = 1;
		$arrBillHistory = array_reverse($arrBillHistory, TRUE);
		foreach($arrBillHistory as $arrBill)
		{
			$arrDefine['GraphData']		['Title']			['Value']	= date("M y", strtotime("-1 month", strtotime($arrBill['CreatedOn'])));
			$arrDefine['GraphData']		['Value1']			['Value']	= max($arrBill['Total'] + $arrBill['Tax'], 0.0);
			$this->_arrFileData[] = $arrDefine['GraphData'];
			$intCount++;
		}
		$arrDefine['GraphData']		['Title']			['Value']	= date("F y", strtotime("-1 month", time()));
		$arrDefine['GraphData']		['Value1']			['Value']	= $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax'];
		$this->_arrFileData[] = $arrDefine['GraphData'];
		$this->_arrFileData[] = $arrDefine['GraphFooter'];
		
		// SUMMARY CHARGES
		// get details from servicetype totals
		$arrServiceTypeTotalVars['Account']		= $arrInvoiceDetails['Account'];
		$arrServiceTypeTotalVars['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		$mixResult = $this->_selServiceTypeTotals->Execute($arrServiceTypeTotalVars);
		
		if ($mixResult === FALSE)
		{
			Debug($this->_selServiceTypeTotals->Error());
			return FALSE;
		}
		
		$arrServiceTypeTotals = $this->_selServiceTypeTotals->FetchAll();
		if(!is_array($arrServiceTypeTotals))
		{
			$arrServiceTypeTotals = Array();
		}
		// build output
		$this->_arrFileData[] = $arrDefine['ChargeTotalsHeader'];
		foreach($arrServiceTypeTotals as $arrTotal)
		{
			$arrDefine['ChargeTotal']	['ChargeName']		['Value']	= $arrTotal['RecordTypeName'];
			$arrDefine['ChargeTotal']	['ChargeTotal']		['Value']	= $arrTotal['Charge'];
			$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		}
		$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "GST Total";
		$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= $arrInvoiceDetails['Tax'];
		$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		$arrDefine['ChargeTotalsFooter']['BillTotal']		['Value']	= $arrInvoiceDetails['Balance'];
		$this->_arrFileData[] = $arrDefine['ChargeTotalsFooter'];
		
		// PAYMENT DETAILS
		// build output
		$arrDefine['PaymentData']		['BillExpRef']		['Value']	= $arrInvoiceDetails['Account'].MakeLuhn($arrInvoiceDetails['Account']);
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Value']	= $arrInvoiceDetails['Account'].MakeLuhn($arrInvoiceDetails['Account']);
		$arrDefine['PaymentData']		['AccountNo']		['Value']	= $arrInvoiceDetails['Account'];
		$arrDefine['PaymentData']		['DateDue']			['Value']	= date("j M Y", strtotime("+".$arrCustomerData['PaymentTerms']." days"));
		$arrDefine['PaymentData']		['TotalOwing']		['Value']	= ((float)$arrInvoiceDetails['Balance'] + (float)$arrInvoiceDetails['AccountBalance']) - (float)$arrInvoiceDetails['Credits'];
		$arrDefine['PaymentData']		['CustomerName']	['Value']	= $arrCustomerData['FirstName']." ".$arrCustomerData['LastName'];
		$arrDefine['PaymentData']		['AddressLine1']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine1']['Value'];
		$arrDefine['PaymentData']		['AddressLine2']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine2']['Value'];
		$arrDefine['PaymentData']		['AddressLine3']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine3']['Value'];
		$arrDefine['PaymentData']		['AddressLine4']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine4']['Value'];
		$arrDefine['PaymentData']		['AddressLine5']	['Value']	= "{$arrDefine['InvoiceDetails']['Suburb']['Value']}   {$arrDefine['InvoiceDetails']['State']['Value']}   {$arrDefine['InvoiceDetails']['Postcode']['Value']}";
		$arrDefine['PaymentData']		['PaymentMethod']	['Value']	= $arrCustomerData['BillingType'];
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
		$this->_arrFileData[] = $arrDefine['PaymentData'];
		
		
		// get details from services
		if (($intCount = $this->_selServices->Execute(Array('Account' => $arrInvoiceDetails['Account']))) === FALSE)
		{
			Debug("Error on _selServices!");
			Debug($this->_selServices->Error());
		}
		$arrServices = $this->_selServices->FetchAll();
		
		// Only generate Service Summaries and Itemised calls if there are services to generate for
		if ($intCount >= 1)
		{
			//--------------------------------------------------------------------//
			// SERVICE SUMMARIES
			//--------------------------------------------------------------------//
			
			// build output
			$this->_arrFileData[] = $arrDefine['SvcSummaryHeader'];
			foreach($arrServices as $arrService)
			{
				if ($arrService['RangeStart'] && $arrService['RangeEnd'])
				{
					$strFNN	= $arrService['FNN']."(".$arrService['ExtensionName'].")";
				}
				else
				{
					$strFNN	= $arrService['FNN'];
				}
				
				// Add the Service Summary
				$this->GenerateServiceSummary($arrService['Id'], $strFNN, $arrService['CostCentre']);
			}
			$this->_arrFileData[] = $arrDefine['SvcSummaryFooter'];
			
			
			//--------------------------------------------------------------------//
			// ITEMISED CALLS
			//--------------------------------------------------------------------//
		
			// add start record (70)
			$this->_arrFileData[] = $arrDefine['ItemisedHeader'];
			
			// loop through the services
			foreach($arrServices as $arrService)
			{
				// add service record (80)
				if ($arrService['RangeStart'] && $arrService['RangeEnd'])
				{
					$strFNN	= $arrService['FNN']."(".$arrService['ExtensionName'].")";
				}
				else
				{
					$strFNN	= $arrService['FNN'];
				}
				$arrDefine['ItemSvcHeader']	['FNN']				['Value']	= $strFNN;
				$this->_arrFileData[] = $arrDefine['ItemSvcHeader'];
				
			 	// Fetch the record type data
			 	$arrWhere = Array();
			 	$arrWhere['Service']	= $arrService['Id'];
			 	$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
			 	$arrWhere['RangeStart']	= $arrService['RangeStart'];
			 	$arrWhere['RangeEnd']	= $arrService['RangeEnd'];
			 	if ($this->_selItemisedRecordTypes->Execute($arrWhere) === FALSE)
			 	{
			 		// ERROR
			 		return FALSE;
			 	}
			 	$arrItemisedRecordTypes	= $this->_selItemisedRecordTypes->FetchAll();
			 	
			 	$arrCreditRecordType['Description']	= "Other Charges & Credits";
			 	$arrCreditRecordType['DisplayType']	= RECORD_DISPLAY_S_AND_E;
			 	$arrCreditRecordType['IsCharge']	= TRUE;
			 	$arrItemisedRecordTypes[] = $arrCreditRecordType;
			 	
			 	// Generate the Itemised Call list
			 	foreach ($arrItemisedRecordTypes as $arrRecordType)
			 	{
				 	if ($this->GenerateItemisedCalls($arrService, $arrRecordType) === FALSE)
				 	{
				 		// ERROR
				 		return FALSE;
				 	}
			 	}
			 	
				// add service total record (89)
				$this->_arrFileData[] = $arrDefine['ItemSvcFooter'];
			}
		}
		
		//--------------------------------------------------------------------//
		// ITEMISED ACCOUNT CHARGES
		//--------------------------------------------------------------------//
	 	// Fetch the itemised charge data
	 	$arrWhere = Array();
	 	$arrWhere['Account']	= $arrInvoiceDetails['Id'];
	 	$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
	 	$arrWhere['Service']	= NULL;
		if (($intChargeCount = $this->_selItemisedCharges->Execute($arrWhere)) === FALSE)
		{
			// ERROR
			return FALSE;
		}
		elseif ($intChargeCount)
		{
			// If there were no itemised calls, add the itemised header
			if (!$intCount)
			{
				// add start record (70)
				$this->_arrFileData[] = $arrDefine['ItemisedHeader'];
			}
			
			// Add the itemised charges
			$arrCharges = $this->_selItemisedCharges->FetchAll();
			
			// build header records
			$arrDefine['ItemSvcHeader']		['FNN']				['Value']	= $arrInvoiceDetails['Id'];
			$this->_arrFileData[] = $arrDefine['ItemSvcHeader'];
			$arrDefine['ItemCallTypeHeader']['CallType']		['Value']	= "Account Charges and Credits";
			$this->_arrFileData[] = $arrDefine['ItemCallTypeHeader'];
			
			// Add account charges
			$fltTotalCharge = 0.0;
			foreach ($arrCharges as $arrCharge)
			{
				// Make sure that the Credits appear as a -ve figure
				if ($arrCharge['Nature'] == NATURE_CR)
				{
					$arrCharge['Charge'] = 0 - $arrCharge['Charge'];
				}
				$strDescription = $arrCharge['ChargeType']." - ".$arrCharge['Description'];
				$arrDefine['ItemisedDataS&E']	['Description']		['Value']	= $strDescription;
				$arrDefine['ItemisedDataS&E']	['Items']			['Value']	= 1;
				$arrDefine['ItemisedDataS&E']	['Charge']			['Value']	= $arrCharge['Charge'];
				$this->_arrFileData[] = $arrDefine['ItemisedDataS&E'];
				
				$fltTotalCharge += $arrCharge['Charge'];
			}
			
			// add call type total
			$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= $fltTotalCharge;
			$this->_arrFileData[] = $arrDefine['ItemCallTypeFooter'];
			 	
			// add service total record (89)
			$this->_arrFileData[] = $arrDefine['ItemSvcFooter'];
			
			// add end itemised charges record (79)
			$this->_arrFileData[] = $arrDefine['ItemisedFooter'];
		}
		
		
		//--------------------------------------------------------------------//
		// INVOICE FOOTERS
		//--------------------------------------------------------------------//
		
		// add invoice footer (18)
		if ($arrInvoiceDetails['Balance'] >= BILLING_MINIMUM_TOTAL || $arrCustomerData['DeliveryMethod'] == BILLING_METHOD_EMAIL)
		{
			$arrDefine['InvoiceFooter']	['Delivery']	['Value']	= $arrCustomerData['DeliveryMethod'];
		}
		else
		{
			$arrDefine['InvoiceFooter']	['Delivery']	['Value']	= BILLING_METHOD_DO_NOT_SEND;
		}
		$this->_arrFileData[] = $arrDefine['InvoiceFooter'];
		
		// Process and implode the data so it can be inserted into the DB
		$strFileContents = $this->GenerateInvoiceData($this->_arrFileData);
		
		// Insert into InvoiceOutput table
		$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		$arrWhere['Account']	= $arrInvoiceDetails['Account'];
		$arrWhere['Data']		= $strFileContents;
		if ($this->_insInvoiceOutput->Execute($arrWhere) === FALSE)
		{
			// Error

			return FALSE;			
		}
		return TRUE;
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
	 * @param		boolean		bolSample		optional This is a sample billing file
	 *
	 * @return		string						filename
	 *
	 * @method
	 */
 	function BuildOutput($intOutputType = BILL_COMPLETE)
 	{
		// generate filenames
		switch ($intOutputType)
		{
			case BILL_SAMPLE:
				$strFilename		= BILLING_LOCAL_PATH_SAMPLE."sample".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH_SAMPLE."sample".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH_SAMPLE."sample".date("Y-m-d").".zip";
				$strInvoiceTable	= 'InvoiceTemp';
				$bolSample			= TRUE;
				break;
			
			case BILL_COMPLETE:
				$strFilename		= BILLING_LOCAL_PATH.date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH.date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH.date("Y-m-d").".zip";
				$strInvoiceTable	= 'Invoice';
				$bolSample			= FALSE;
				break;
				
			case BILL_REPRINT:
				$strFilename		= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".zip";
				$strInvoiceTable	= 'Invoice';
				$bolSample			= FALSE;
				break;	
		}
		
		$selMetaData = new StatementSelect($strInvoiceTable, "MIN(Id) AS MinId, MAX(Id) AS MaxId, COUNT(Id) AS Invoices, InvoiceRun", "Status = ".INVOICE_PRINT, NULL, NULL, "Status");
		if ($selMetaData->Execute() === FALSE)
		{
			Debug('$selMetaData : '.$selMetaData->Error());
			return FALSE;
		}
		$arrMetaData = $selMetaData->Fetch();
		
		Debug("{$arrMetaData['MinId']} {$arrMetaData['MaxId']} {$arrMetaData['Invoices']} {$arrMetaData['InvoiceRun']}");

		// Set the InvoiceRun
		$strInvoiceRun = $arrMetaData['InvoiceRun'];
		
		if($arrMetaData['Invoices'] == 0)
		{
			// Nothing to do
			Debug("Nothing to do");
			return FALSE;
		}

		$qryBuildFile	= new Query();
		$strColumns		= "'0010', LPAD(CAST($strInvoiceTable.Id AS CHAR), 10, '0'), InvoiceOutput.Data";
		$strWhere		= "InvoiceOutput.InvoiceRun = '$strInvoiceRun' AND InvoiceOutput.InvoiceRun = $strInvoiceTable.InvoiceRun";
		$strQuery		=	"SELECT $strColumns INTO OUTFILE '$strFilename' FIELDS TERMINATED BY '' ESCAPED BY '' LINES TERMINATED BY '\\n'\n" .
							"FROM InvoiceOutput JOIN $strInvoiceTable USING (Account)\n".
							"WHERE $strWhere\n";
		if($bolSample)
		{
			if((int)$arrMetaData['MaxId'] < BILL_PRINT_SAMPLE_LIMIT)
			{
				$strQuery .= "LIMIT ".(int)$arrMetaData['MaxId'];
				$arrMetaData['Invoices'] = (int)$arrMetaData['MaxId'];
			}
			else
			{
				$strQuery .= "LIMIT ".rand((int)$arrMetaData['MinId'] , (int)$arrMetaData['MaxId'] - BILL_PRINT_SAMPLE_LIMIT).", ".BILL_PRINT_SAMPLE_LIMIT;
				$arrMetaData['Invoices'] = BILL_PRINT_SAMPLE_LIMIT;
			}
		}
		if (file_exists($strFilename))
		{
			unlink($strFilename);
			unlink($strMetaName);
			unlink($strZipName);
		}
		if ($qryBuildFile->Execute($strQuery) === FALSE)
		{
			// ERROR
			Debug($qryBuildFile->Error());
			return FALSE;
		}
		
		// Append metadata to bill output file
		$strFooter		=	"0019" .
							date("d/m/Y") .
							str_pad($arrMetaData['Invoices'], 10, "0", STR_PAD_LEFT) .
							str_pad(0, 10, "0", STR_PAD_LEFT) .
							str_pad(0, 10, "0", STR_PAD_LEFT) .
							str_pad(0, 10, "0", STR_PAD_LEFT) .
							str_pad(0, 10, "0", STR_PAD_LEFT) .
							str_pad(0, 10, "0", STR_PAD_LEFT) .
							str_pad(0, 10, "0", STR_PAD_LEFT);
		
		$ptrFile		= fopen($strFilename, "a");
		fwrite($ptrFile, $strFooter);
		fclose($ptrFile);
		
		// create metadata file
		$ptrMetaFile	= fopen($strMetaName, "w");
		// TODO - get actual insert ids for this billing run
		$strLine		= 	date("d/m/Y").
							basename($strFilename).
							sha1_file($strFilename);
		fwrite($ptrMetaFile, $strLine);
		fclose($ptrMetaFile);
		
		// zip files
		$strCommand = "zip $strZipName $strFilename $strMetaName";
		exec($strCommand);
		
		// set filename internally
		$this->_strFilename = $strZipName;
		
		// return zip's filename
		return $strZipName;
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
	 * @param		boolean		bolSample		optional This is a sample billing file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function SendOutput($bolSample)
 	{
		// Set the remote directory
		if ($bolSample)
		{
			$strRemoteDir	= BILL_PRINT_REMOTE_DIR_SAMPLE;
			$strFile		= $this->_strSampleFile;
		}
		else
		{
			$strRemoteDir	= BILL_PRINT_REMOTE_DIR;
			$strFile		= $this->Filename;
		}
		
		/*
		// Connect to FTP
		$ptrFTP = ftp_connect(BILL_PRINT_HOST);
		if (!ftp_login($ptrFTP, BILL_PRINT_USERNAME, BILL_PRINT_PASSWORD))
		{
			// Log in failed
			return FALSE;
		}
		ftp_chdir($ptrFTP, $strRemoteDir);
		
		// Upload file
		if(!ftp_put($ptrFTP, basename($strFile), $strFile, FTP_ASCII))
		{
			return FALSE;
		}
		
		// Close the FTP connection
		ftp_close($ptrFTP);
		*/
		return TRUE;
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
 	 * @param		string		strInvoiceRun	The Invoice Run to build from
 	 * 
	 * @return		string						filename
	 *
	 * @method
	 */
 	function BuildSample($strInvoiceRun)
 	{
		return $this->BuildOutput(BILL_SAMPLE);
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
		return $this->SendOutput(TRUE);
 	}
 	
 	
 	
 	//------------------------------------------------------------------------//
	// GenerateServiceSummary()
	//------------------------------------------------------------------------//
	/**
	 * GenerateServiceSummary()
	 *
	 * Generates a Service Summary for a specified service
	 *
	 * Generates a Service Summary for a specified service
	 * 
	 * @param		integer		$intService		The service to generate a summary for
	 * @param		string		$strFNN			The FNN for this service
	 * @param		string		$strCostCentre	The CostCentre (can be null) for this service
	 *
	 * @return		mixed						float: total charge
	 * 											FALSE: an error occurred
	 *
	 * @method
	 */
 	function GenerateServiceSummary($intService, $strFNN, $strCostCentre)
 	{
		$arrDefine = $this->_arrDefine;
		
  		// Get ServiceTypeTotals
 		$arrColumns = Array();
 		$arrColumns['Service']		= $intService;
 		$arrColumns['InvoiceRun']	= $this->_strInvoiceRun;													
 		if ($this->_selServiceTypeTotals->Execute($arrColumns) === FALSE)
 		{
 			// ERROR
 			Debug($this->_selServiceTypeTotals->Error());
 			return FALSE;
 		}
 		$arrServiceSummaries = $this->_selServiceTypeTotals->FetchAll();
 		
 		// Get Charge Totals
 		$arrColumns = Array();
 		$arrColumns['Service']		= $intService;
 		$arrColumns['InvoiceRun']	= $this->_strInvoiceRun;
 		if ($this->_selServiceChargesTotal->Execute($arrColumns) === FALSE)
 		{
 			// ERROR
 			Debug($this->_selServiceChargesTotal->Error());
 			return FALSE;
 		}
 		$arrChargeSummary =  $this->_selServiceChargesTotal->Fetch();
 		if ($arrChargeSummary['Records'] > 0)
 		{
 			$arrServiceSummaries[] = $arrChargeSummary;
 		}
 		
 		// if we have anything to add to the invoice...
 		if (count($arrServiceSummaries))
 		{
			// Service Header
			$arrDefine['SvcSummSvcHeader']		['FNN']				['Value']	= $strFNN;
			$arrDefine['SvcSummSvcHeader']		['CostCentre']		['Value']	= $strCostCentre;
			$this->_arrFileData[] = $arrDefine['SvcSummSvcHeader'];
	 		 		
	 		// Add each to the invoice
	 		$fltTotal = 0.0;
	 		foreach ($arrServiceSummaries as $arrServiceSummary)
	 		{
				$arrDefine['SvcSummaryData']	['CallType']		['Value']	= $arrServiceSummary['RecordType'];
				$arrDefine['SvcSummaryData']	['CallCount']		['Value']	= $arrServiceSummary['Records'];
				$arrDefine['SvcSummaryData']	['Charge']			['Value']	= $arrServiceSummary['Total'];
				$this->_arrFileData[] = $arrDefine['SvcSummaryData'];
				
				$fltTotal += $arrServiceSummary['Total'];
	 		}
	 		
			// Footer and total (can't use ServiceTotal, because it doesn't include credits/charges)
			$arrDefine['SvcSummSvcFooter']		['TotalCharge']		['Value']	= $fltTotal;
			$this->_arrFileData[] = $arrDefine['SvcSummSvcFooter'];
 		}
 		
 		return $fltTotal;
 	}
 	
 	//------------------------------------------------------------------------//
	// GenerateItemisedCalls()
	//------------------------------------------------------------------------//
	/**
	 * GenerateItemisedCalls()
	 *
	 * Generates a list of itemised calls for a specified service and record type
	 *
	 * Generates a list of itemised calls for a specified service and record type
	 * 
	 * @param		array		$arrService		Array of service data returned from the database
	 * @param		array		$arrRecordGroup	Array of recored group data returned from the database
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function GenerateItemisedCalls($arrService, $arrRecordGroup)
 	{
 		$arrDefine = $this->_arrDefine;
 		
 		if ($arrRecordGroup['IsCharge'] !== TRUE)
 		{
	 		$arrWhere = Array();
	 		$arrWhere['Service']		= $arrService['Id'];
	 		$arrWhere['RecordGroup']	= $arrRecordGroup['RecordType'];
	 		if (!$arrService['RangeStart'])
	 		{
	 			$arrWhere['RangeStart']		= NULL;
	 			$arrWhere['RangeEnd']		= NULL;
	 		}
	 		else
	 		{
	 			$arrWhere['RangeStart']		= $arrService['RangeStart'];
	 			$arrWhere['RangeEnd']		= $arrService['RangeEnd'];
	 		}
		 	$arrWhere['InvoiceRun']		= $this->_arrInvoiceDetails['InvoiceRun'];
		 	
			if ($this->_selItemisedCalls->Execute($arrWhere) === FALSE)
			{
				// ERROR
				Debug($this->_selItemisedCalls->Error());
				return FALSE;
			}
			$arrItemisedCalls = $this->_selItemisedCalls->FetchAll();
 		}
		else
		{
			// Get Service's Charges
		 	$arrWhere = Array();
		 	$arrWhere['Account']	= $this->_arrInvoiceDetails['Id'];
		 	$arrWhere['InvoiceRun']	= $this->_arrInvoiceDetails['InvoiceRun'];
		 	$arrWhere['Service']	= $arrService['Id'];
			if (($intChargeCount = $this->_selItemisedCharges->Execute($arrWhere)) === FALSE)
			{
				// ERROR
				return FALSE;
			}
			while ($arrCharge = $this->_selItemisedCharges->Fetch())
			{
				// Make sure that the Credits appear as a -ve figure
				if ($arrCharge['Nature'] == NATURE_CR)
				{
					$arrCharge['Charge'] = 0 - $arrCharge['Charge'];
				}
				$arrCharge['Units']			= 1;
				$arrCharge['Description']	= $arrCharge['ChargeType']." - ".$arrCharge['Description'];
				
				// Add to itemised calls array
				$arrItemisedCalls[] = $arrCharge;
			}
			
			if ($intChargeCount == 0)
			{
				return TRUE;
			}
		}
		
		// build header record (90)
		$arrDefine['ItemCallTypeHeader']['CallType']		['Value']	= $arrRecordGroup['Description'];
		$this->_arrFileData[] = $arrDefine['ItemCallTypeHeader'];
		
		// Create output for each call
		$fltTotalCharge = 0.0;
		foreach ($arrItemisedCalls as $arrItemisedCall)
		{
			// build charge record
			switch($arrRecordGroup['DisplayType'])
			{
				// Type 92
				case RECORD_DISPLAY_S_AND_E:
					$strDescription = $arrItemisedCall['Description'];
					$arrDefine['ItemisedDataS&E']	['Description']		['Value']	= $strDescription;
					$arrDefine['ItemisedDataS&E']	['Items']			['Value']	= (int)$arrItemisedCall['Units'];
					$arrDefine['ItemisedDataS&E']	['Charge']			['Value']	= $arrItemisedCall['Charge'];
					$this->_arrFileData[] = $arrDefine['ItemisedDataS&E'];
					break;
				// Type 93
				case RECORD_DISPLAY_DATA:
					$arrDefine['ItemisedDataKB']	['Date']			['Value']	= date("d/m/Y", strtotime($arrItemisedCall['StartDatetime']));
					$arrDefine['ItemisedDataKB']	['Time']			['Value']	= date("H:i:s", strtotime($arrItemisedCall['StartDatetime']));
					$arrDefine['ItemisedDataKB']	['CalledParty']		['Value']	= $arrItemisedCall['Destination'];
					$arrDefine['ItemisedDataKB']	['DataTransfered']	['Value']	= (int)$arrItemisedCall['Units'];
					$arrDefine['ItemisedDataKB']	['Description']		['Value']	= $arrItemisedCall['Description'];
					$arrDefine['ItemisedDataKB']	['Charge']			['Value']	= $arrItemisedCall['Charge'];
					$this->_arrFileData[] = $arrDefine['ItemisedDataKB'];
					break;
				// Type 94
				case RECORD_DISPLAY_SMS:
					$arrDefine['ItemisedDataSMS']	['Date']			['Value']	= date("d/m/Y", strtotime($arrItemisedCall['StartDatetime']));
					$arrDefine['ItemisedDataSMS']	['Time']			['Value']	= date("H:i:s", strtotime($arrItemisedCall['StartDatetime']));
					$arrDefine['ItemisedDataSMS']	['CalledParty']		['Value']	= $arrItemisedCall['Destination'];
					$arrDefine['ItemisedDataSMS']	['Items']			['Value']	= (int)$arrItemisedCall['Units'];
					$arrDefine['ItemisedDataSMS']	['Description']		['Value']	= $arrItemisedCall['Description'];
					$arrDefine['ItemisedDataSMS']	['Charge']			['Value']	= $arrItemisedCall['Charge'];
					$this->_arrFileData[] = $arrDefine['ItemisedDataSMS'];
					break;
				// Type 91
				case RECORD_DISPLAY_CALL:
				// Unknown Record Type (should never happen) - just display as a normal Call
				default:
					$arrDefine['ItemisedDataCall']	['Date']			['Value']	= date("d/m/Y", strtotime($arrItemisedCall['StartDatetime']));
					$arrDefine['ItemisedDataCall']	['Time']			['Value']	= date("H:i:s", strtotime($arrItemisedCall['StartDatetime']));
					$arrDefine['ItemisedDataCall']	['CalledParty']		['Value']	= $arrItemisedCall['Destination'];
					$intHours		= floor((int)$arrItemisedCall['Units'] / 3600);
					$strDuration	= "$intHours:".date("i:s", (int)$arrItemisedCall['Units']);
					$arrDefine['ItemisedDataCall']	['Duration']		['Value']	= $strDuration;
					$arrDefine['ItemisedDataCall']	['Description']		['Value']	= $arrItemisedCall['Description'];
					$arrDefine['ItemisedDataCall']	['Charge']			['Value']	= $arrItemisedCall['Charge'];
					$this->_arrFileData[] = $arrDefine['ItemisedDataCall'];
					break;
			}
			
			$fltTotalCharge += $arrItemisedCall['Charge'];
		}
		
		// add call type total
		$arrDefine['ItemCallTypeFooter']['TotalCharge']		['Value']	= $fltTotalCharge;
		$this->_arrFileData[] = $arrDefine['ItemCallTypeFooter'];
		
		return TRUE;
 	}

 
  	//------------------------------------------------------------------------//
	// GenerateInvoiceData()
	//------------------------------------------------------------------------//
	/**
	 * GenerateInvoiceData()
	 *
	 * Generates a block of invoice data
	 *
	 * Generates a block of invoice data from the passed indexed array
	 * 
	 * @param	array					Indexed array of data to be imploded and validated
	 *
	 * @return	mixed					string	: invoice data
	 * 									FALSE	: invalid input
	 *
	 * @method
	 */
 	function GenerateInvoiceData($arrFileData)
 	{
		if (!is_array($this->_arrFileData))
		{
			return FALSE;
		}
		
		$strFileContents = "";
		$i = 0;
		// Loop through Records
		foreach ($this->_arrFileData as $strKey=>$arrRecord)
		{
			$i++;
			$t = 0;
			
			// Loop through Fields
			foreach ($arrRecord as $arrField)
			{
				// If this is a non-print field, then skip it
				if($arrField['Print'] === FALSE)
				{
					continue;
				}
				
				$strValue = $arrField['Value'];
				$t++;
				
				// Process the field
				switch ($arrField['Type'])
				{
					case BILL_TYPE_INTEGER:
						if (!$strValue)
						{
							$strValue = "0";
						}
						$strTemp = sprintf("% ".$arrField['Length']."d", ((int)$strValue));
						if(substr($strValue, 0, 1) == "-")
						{
							$strTemp = "-".substr($strTemp, 1);
						}
						$strValue = str_pad($strValue, $arrField['Length'], " ", STR_PAD_LEFT);
						break;
					case BILL_TYPE_CHAR:
						if ($strValue == NULL)
						{
							$strValue = "";
						}
						$strValue = str_pad($strValue, $arrField['Length'], " ", STR_PAD_RIGHT);
						break;
					case BILL_TYPE_BINARY:
						if ($strValue == NULL)
						{
							$strValue = "0";
						}
						$strValue = str_pad($strValue, $arrField['Length'], "0", STR_PAD_RIGHT);
						break;
					case BILL_TYPE_FLOAT:
						if (!$strValue)
						{
							$strValue = "0";
						}
						$strValue = str_pad((float)$strValue, $arrField['Length'], " ", STR_PAD_LEFT);
						break;
					case BILL_TYPE_SHORTDATE:
						if (!$strValue)
						{
							$strValue = "00/00/0000";
						}
						$strValue = str_pad($strValue, 10, " ", STR_PAD_LEFT);
						break;
					case BILL_TYPE_LONGDATE:
						if (!$strValue)
						{
							$strValue = "00 Jan 0000";
						}
						$strValue = str_pad($strValue, 11, " ", STR_PAD_RIGHT);
						break;
					case BILL_TYPE_TIME:
						if (!$strValue)
						{
							$strValue = "00:00:00";
						}
						$strValue = str_pad($strValue, 8, " ", STR_PAD_LEFT);
						break;
					case BILL_TYPE_DURATION:
						if ($strValue == NULL)
						{
							$strValue = "0:00:00";
						}
						$strValue = str_pad($strValue, 9, " ", STR_PAD_LEFT);
						break;
					case BILL_TYPE_SHORTCURRENCY:
						if (!$strValue)
						{
							$strValue = "0";
						}
						
						$strTemp = sprintf("%01.2f", ((float)$strValue));
						if(substr($strValue, 0, 1) == "-")
						{
							$strTemp = "-".substr($strTemp, 1);
						}
						$strValue = str_pad($strTemp, 11, " ", STR_PAD_LEFT);
						break;
					default:
						// Unknown Data Type
						Debug("BIG FLOPPY DONKEY DICK (Unknown Bill Printing Data Type: {$arrField['Type']})");
						Debug($arrRecord);
						return FALSE;
				}
				
				$strFileContents .= $strValue;
			}
			
			$strFileContents .= "\n";
		}
				
		// Return the data
		return rtrim($strFileContents);
 	}
 }

?>
