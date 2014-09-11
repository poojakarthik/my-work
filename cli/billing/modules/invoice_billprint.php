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
		
		// Customer Config
		$this->_arrConfig = $GLOBALS['**arrCustomerConfig']['Billing']['PrintingModule'];
		
		// Init member variables
		$this->_strFilename		= NULL;
		$this->_strSampleFile	= NULL;
		
		// Init database statements
		$this->_insInvoiceOutput		= new StatementInsert("InvoiceOutput");
		
		$arrColumns = Array();
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
		$arrColumns['BillingType']		= "Account.BillingType";
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
		$arrColumns[]					= "TotalOwing";
		$this->_selLastBills			= new StatementSelect(	"Invoice",
																$arrColumns,
																"Account = <Account> AND CreatedOn < <CreatedOn>",
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
		
		/*$this->_selServices				= new StatementSelect(	"(Service LEFT OUTER JOIN ServiceExtension ON Service.Id = ServiceExtension.Service) LEFT JOIN CostCentre CostCentre2 ON ServiceExtension.CostCentre = CostCentre2.Id, " .
																"Service Service2 LEFT OUTER JOIN CostCentre ON Service2.CostCentre = CostCentre.Id",
																"Service.FNN AS FNN, Service.Id AS Id, (CASE WHEN CostCentre2.Id IS NULL THEN CostCentre.Name ELSE CostCentre2.Name END) AS CostCentre, Service.Indial100 as Indial100, ServiceExtension.Name AS ExtensionName, ServiceExtension.RangeStart AS RangeStart, ServiceExtension.RangeEnd as RangeEnd",
																"Service.Account = <Account> AND Service.Id = Service2.Id",
																"CostCentre, Service.ServiceType, Service.FNN, ServiceExtension.Name",
																NULL,
																"Service.FNN, ServiceExtension.RangeStart");*/
																
		$this->_selServices				= new StatementSelect(	"((Service LEFT JOIN ServiceExtension ON Service.Id = ServiceExtension.Service) LEFT JOIN CostCentre CostCentreExtension ON ServiceExtension.CostCentre = CostCentreExtension.Id) LEFT JOIN CostCentre ON Service.CostCentre = CostCentre.Id",
																"Service.FNN AS FNN, COUNT(Service.Id) AS ServiceCount, (CASE WHEN CostCentreExtension.Id IS NULL THEN CostCentre.Name ELSE CostCentreExtension.Name END) AS CostCentre, Service.Indial100 as Indial100, ServiceExtension.Name AS ExtensionName, ServiceExtension.RangeStart AS RangeStart, ServiceExtension.RangeEnd as RangeEnd",
																"Service.Account = <Account>",
																"CostCentre, Service.ServiceType, Service.FNN, ServiceExtension.Name, ForceInvoiceRender",
																NULL,
																"Service.FNN, ServiceExtension.RangeStart");
																
		$this->_selServiceInstances		= new StatementSelect(	"Service LEFT JOIN ServiceExtension ON Service.Id = ServiceExtension.Service", 
																"Service.Id AS Id", 
																"Service.Account = <Account> AND Service.FNN = <FNN> AND (ServiceExtension.RangeStart <=> <RangeStart>)");
																
		$this->_selRatePlan				= new StatementSelect(	"ServiceTotal JOIN RatePlan ON RatePlan.Id = ServiceTotal.RatePlan",
																"RatePlan.*",
																"Service = <Id> AND InvoiceRun = <InvoiceRun>");
		
		/*$this->_selServiceTotal			= new StatementSelect(	"ServiceTotal",
																"(TotalCharge + Debit - Credit) AS TotalCharge",
																"Service = <Service> AND InvoiceRun = <InvoiceRun>");*/
		
 		$arrColumns = Array();
 		$arrColumns['RecordType']	= "GroupType.Description";
 		$arrColumns['Charge']		= "SUM(ServiceTypeTotal.Charge)";
 		$this->_selServiceTypeTotals	= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, RecordType AS GroupType",
 																$arrColumns,
 																"Account = <Account> AND InvoiceRun = <InvoiceRun> AND GroupType.Id = RecordType.GroupId",
 																"ServiceTypeTotal.FNN, GroupType.Description",
 																NULL,
 																"GroupType.Description DESC");
		
 		/*$arrColumns = Array();
 		$arrColumns['RecordType']	= "GroupType.Description";
 		$arrColumns['Total']		= "SUM(ServiceTypeTotal.Charge)";
 		$arrColumns['Records']		= "SUM(Records)";
 		$this->_selServiceSummary	= new StatementSelect(	"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, RecordType AS GroupType",
 															$arrColumns,
 															"Service = <Service> AND FNN BETWEEN <RangeStart> AND <RangeEnd> AND InvoiceRun = <InvoiceRun> AND GroupType.Id = RecordType.GroupId",
 															"ServiceTypeTotal.FNN, GroupType.Description",
 															NULL,
 															"GroupType.Description DESC");*/
 															
 		/*$this->_selDisplayServiceSummary	= new StatementSelect(	"ServiceTotal",
 																	"Id",
 																	"Service = <Service> AND InvoiceRun = <InvoiceRun> AND " .
 																	"(CappedCharge != 0 OR " .
 																	"UncappedCharge != 0 OR " .
 																	"TotalCharge != 0 OR " .
 																	"Credit != 0 OR " .
 																	"Debit != 0)");*/
 		
		/*$this->_selServiceChargesTotal	= new StatementSelect(	"Charge",
																"SUM(Amount) AS Charge, 'Other Charges & Credits' AS RecordType, COUNT(Id) AS Records, Nature",
																"Service = <Service> AND InvoiceRun = <InvoiceRun>",
																"Nature",
																2,
																"Nature");*/
																
		$this->_selChargeTotal	= new StatementSelect(	"Charge",
														"SUM(Amount) AS Charge, 'Other Charges & Credits' AS RecordType, Nature",
														"Account = <Account> AND InvoiceRun = <InvoiceRun>",
														"Nature",
														NULL,
														"Nature");
														
		$this->_selInvoiceServiceTotal	= new StatementSelect(	"ServiceTotal",
																"SUM(CappedCharge + UncappedCharge) AS GrandTotal",
																"Account = <Account> AND InvoiceRun = <InvoiceRun>",
																NULL,
																1,
																"Account");
																
		$this->_selHasServiceTotal		= new StatementSelect(	"ServiceTotal",
																"Id",
																"TotalCharge != 0.0 AND FNN = <FNN> AND Service = <Service> AND InvoiceRun = <InvoiceRun>");
																

		$arrColumns = Array();
		$arrColumns['Charge']				= "Amount";
		$arrColumns['Description']			= "Description";
		$arrColumns['ChargeType']			= "ChargeType";
		$arrColumns['Nature']				= "Nature";
		
		$this->_selItemisedAccountCharges	= new StatementSelect(	"Charge",
																	$arrColumns,
																	"Account = <Account> AND Service IS NULL AND InvoiceRun = <InvoiceRun>");
		
		$this->_selWeReceived			= new StatementSelect(	"InvoicePayment",
																"SUM(Amount) AS WeReceived",
																"(InvoiceRun = <ThisInvoiceRun> OR InvoiceRun = <LastInvoiceRun>) AND Account = <Account>");
		
		$this->_selServicePlanCharges	= new StatementSelect("ServiceTotal", "SUM(TotalCharge) AS GrandTotalCharge, SUM(PlanCharge) AS GrandPlanCharge, SUM(CappedCharge + UncappedCharge) AS GrandRatedCharge", "Account = <Account> AND InvoiceRun = <InvoiceRun>");
		
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
		/*$qryTruncateInvoiceOutput = new QueryTruncate();
		if (!$qryTruncateInvoiceOutput->Execute("InvoiceOutput"))
		{
			return FALSE;
		}*/
		
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
	 * @param		boolean		$bolDebug				optional TRUE	: Doesn't insert to database, returns data array
	 * 															 FALSE	: Inserts to database, returns boolean
	 *
	 * @return		mixed
	 *
	 * @method
	 */
 	function AddInvoice($arrInvoiceDetails, $bolDebug = FALSE)
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
		
		$arrWhere = Array();
		$arrWhere['Account']	= $arrInvoiceDetails['Account'];
		$arrWhere['CreatedOn']	= $arrInvoiceDetails['CreatedOn'];
		$bolHasBillHistory	= $this->_selLastBills->Execute($arrWhere) ? TRUE : FALSE;
		$arrCustomerData	= $this->_selCustomerDetails->Fetch();
		$arrBillHistory		= $this->_selLastBills->FetchAll();
		
		// build output
		$arrDefine['InvoiceDetails']	['InvoiceGroup']	['Value']	= $arrCustomerData['CustomerGroup'];
		$arrDefine['InvoiceDetails']	['BillPeriod']		['Value']	= date("F y", strtotime("-1 month", strtotime($arrInvoiceDetails['CreatedOn'])));
		$arrDefine['InvoiceDetails']	['IssueDate']		['Value']	= date("j M Y", strtotime($arrInvoiceDetails['CreatedOn']));
		$arrDefine['InvoiceDetails']	['AccountNo']		['Value']	= $arrInvoiceDetails['Account'];
		
		// FIXME: Invoice Inserts (do this properly)
		$arrInserts = array_fill(0, 6, '0');
		
		$intLastBillDate	= strtotime("-1 month", date("Y-m-01", strtotime($arrInvoiceDetails['CreatedOn'])));
		if (($arrCustomerData['CustomerGroup'] == CUSTOMER_GROUP_VOICETALK) && (strtotime($arrCustomerData['CreatedOn']) >= $intLastBillDate) && ($arrInvoiceDetails['DeliveryMethod'] == DELIVERY_METHOD_POST))
		{
				$arrInserts[0]	= '1';
		}
		$arrDefine['InvoiceDetails']	['Inserts']			['Value']	= implode($arrInserts);
		
		if($bolHasBillHistory)
		{
			// Display the previous bill details
			$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= $arrBillHistory[0]['TotalOwing'];						
			/*
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
			*/
			// HACKHACKHACK: Not calculating this properly, but need it to account for Etech's gheyness
			$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= max($arrDefine['InvoiceDetails']['OpeningBalance']['Value'] - $arrInvoiceDetails['AccountBalance'], 0.0);
		}
		else
		{
			// There is no previous bill
			$arrDefine['InvoiceDetails']	['OpeningBalance']	['Value']	= 0;						
			$arrDefine['InvoiceDetails']	['WeReceived']		['Value']	= 0;
		}
		$arrDefine['InvoiceDetails']	['Adjustments']		['Value']	= /*$arrInvoiceDetails['Credits']*/ 0.0;
		$arrDefine['InvoiceDetails']	['Balance']			['Value']	= $arrInvoiceDetails['AccountBalance'];
		$arrDefine['InvoiceDetails']	['BillTotal']		['Value']	= ((float)$arrInvoiceDetails['Total'] + (float)$arrInvoiceDetails['Tax']);
		$arrDefine['InvoiceDetails']	['TotalOwing']		['Value']	= (float)$arrInvoiceDetails['TotalOwing'];
		$arrDefine['InvoiceDetails']	['CustomerName']	['Value']	= $arrCustomerData['FirstName']." ".$arrCustomerData['LastName'];
		
		$arrDefine['InvoiceDetails']	['AddressLine1']	['Value']	= $arrCustomerData['BusinessName'];
		$arrDefine['InvoiceDetails']	['AddressLine2']	['Value']	= $arrCustomerData['TradingName'];
		$arrDefine['InvoiceDetails']	['AddressLine3']	['Value']	= $arrCustomerData['AddressLine1'];
		$arrDefine['InvoiceDetails']	['AddressLine4']	['Value']	= $arrCustomerData['AddressLine2'];

		$arrDefine['InvoiceDetails']	['Suburb']			['Value']	= $arrCustomerData['Suburb'];
		$arrDefine['InvoiceDetails']	['State']			['Value']	= $arrCustomerData['State'];
		$arrDefine['InvoiceDetails']	['Postcode']		['Value']	= $arrCustomerData['Postcode'];
		$arrDefine['InvoiceDetails']	['PaymentDueDate']	['Value']	= date("j M Y", strtotime($arrInvoiceDetails['DueOn']));
		
		$this->_arrFileData[] = $arrDefine['InvoiceDetails'];
		
		// MONTHLY COMPARISON BAR GRAPH
		// build output
		// FIXME... different graphs??
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
		$arrDefine['GraphData']		['Title']			['Value']	= date("M y", strtotime("-1 month", strtotime($arrInvoiceDetails['CreatedOn'])));
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
			$arrDefine['ChargeTotal']	['ChargeName']		['Value']	= $arrTotal['RecordType'];
			$arrDefine['ChargeTotal']	['ChargeTotal']		['Value']	= $arrTotal['Charge'];
			$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		}
		
		if (($intCount = $this->_selChargeTotal->Execute($arrServiceTypeTotalVars)) === FALSE)
		{
			Debug($this->_selChargeTotal->Error());
			return FALSE;
		}
		$fltAccountCharge = 0.0;
		if ($intCount)
		{
			$arrChargeTotals = $this->_selChargeTotal->FetchAll();
			
			$fltTotal = 0.0;
			foreach ($arrChargeTotals as $arrChargeTotal)
			{
				// Account for credits
				if ($arrChargeTotal['Nature'] == 'CR')
				{
					$fltTotal -= $arrChargeTotal['Charge'];
				}
				else
				{
					$fltTotal += $arrChargeTotal['Charge'];
				}
				$fltChargeName = $arrChargeTotal['RecordType'];
			}
			
			// add the total
			$fltAccountCharge = $fltTotal;
			$arrDefine['ChargeTotal']	['ChargeName']		['Value']	= $fltChargeName;
			$arrDefine['ChargeTotal']	['ChargeTotal']		['Value']	= $fltTotal;
			$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		}
		
		// Plan Charges & Credits
		$this->_selServicePlanCharges->Execute($arrInvoiceDetails);
		$arrServicePlanCharges = $this->_selServicePlanCharges->Fetch();
		if (round($arrServicePlanCharges['GrandPlanCharge'], 2) != 0.0)
		{
			$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Plan Charges";
			$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= (float)$arrServicePlanCharges['GrandPlanCharge'];
			$this->_arrFileData[] = $arrDefine['ChargeTotal'];
			
			$fltPlanCredit = ((float)$arrServicePlanCharges['GrandTotalCharge'] - $arrServicePlanCharges['GrandRatedCharge']) - $arrServicePlanCharges['GrandPlanCharge'];
			$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Plan Credits";
			$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= $fltPlanCredit;
			$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		}
		/*$arrData = Array();
		$arrData['Account']		= $arrInvoiceDetails['Account'];
		$arrData['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
		if ($this->_selInvoiceServiceTotal->Execute($arrData) === FALSE)
		{
			Debug($this->_selInvoiceServiceTotal->Error());
			return FALSE;
		}
		$arrInvoiceServiceTotal = $this->_selInvoiceServiceTotal->Fetch();
		
		$fltPlanCharges = $arrInvoiceDetails['Total'] - $arrInvoiceServiceTotal['GrandTotal'] - $fltAccountCharge;
		if (round($fltPlanCharges, 2) != 0.0)
		{
			$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "Plan Charges & Credits";
			$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= $fltPlanCharges;
			$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		}*/
		
		
		$arrDefine['ChargeTotal']		['ChargeName']		['Value']	= "GST Total";
		$arrDefine['ChargeTotal']		['ChargeTotal']		['Value']	= $arrInvoiceDetails['Tax'];
		$this->_arrFileData[] = $arrDefine['ChargeTotal'];
		$arrDefine['ChargeTotalsFooter']['BillTotal']		['Value']	= $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax'];
		$this->_arrFileData[] = $arrDefine['ChargeTotalsFooter'];
		
		// PAYMENT DETAILS
		// build output
		$arrDefine['PaymentData']		['BillExpRef']		['Value']	= $arrInvoiceDetails['Account'].MakeLuhn($arrInvoiceDetails['Account']);
		$arrDefine['PaymentData']		['BPayCustomerRef']	['Value']	= $arrInvoiceDetails['Account'].MakeLuhn($arrInvoiceDetails['Account']);
		$arrDefine['PaymentData']		['AccountNo']		['Value']	= $arrInvoiceDetails['Account'];
		$arrDefine['PaymentData']		['DateDue']			['Value']	= date("j M Y", strtotime($arrInvoiceDetails['DueOn']));
		$arrDefine['PaymentData']		['TotalOwing']		['Value']	= (float)$arrInvoiceDetails['TotalOwing'];
		$arrDefine['PaymentData']		['CustomerName']	['Value']	= $arrCustomerData['FirstName']." ".$arrCustomerData['LastName'];
		$arrDefine['PaymentData']		['AddressLine1']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine1']['Value'];
		$arrDefine['PaymentData']		['AddressLine2']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine2']['Value'];
		$arrDefine['PaymentData']		['AddressLine3']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine3']['Value'];
		$arrDefine['PaymentData']		['AddressLine4']	['Value']	= $arrDefine['InvoiceDetails']['AddressLine4']['Value'];
		$arrDefine['PaymentData']		['AddressLine5']	['Value']	= "{$arrDefine['InvoiceDetails']['Suburb']['Value']}   {$arrDefine['InvoiceDetails']['State']['Value']}   {$arrDefine['InvoiceDetails']['Postcode']['Value']}";
		$arrDefine['PaymentData']		['PaymentMethod']	['Value']	= $arrCustomerData['BillingType'];
		$arrDefine['PaymentData']		['SpecialOffer1']	['Value']	= $this->_arrConfig['SpecialOffer1'][$arrCustomerData['CustomerGroup']];
		$arrDefine['PaymentData']		['SpecialOffer2']	['Value']	= $this->_arrConfig['SpecialOffer2'][$arrCustomerData['CustomerGroup']];
		
		$this->_arrFileData[] = $arrDefine['PaymentData'];
		
		
		// get details from services
		if (($intCount = $this->_selServices->Execute(Array('Account' => $arrInvoiceDetails['Account']))) === FALSE)
		{
			Debug("Error on _selServices!");
			Debug($this->_selServices->Error());
		}
		$arrServices = $this->_selServices->FetchAll();
		
		//Debug($arrServices);
		
		// Get the Service Ids
		foreach ($arrServices as $intKey=>$arrService)
		{
			$arrWhere = Array();
			$arrWhere['Account']	= $arrInvoiceDetails['Account'];
			$arrWhere['FNN']		= $arrService['FNN'];
			$arrWhere['RangeStart']	= $arrService['RangeStart'];
			//Debug($arrWhere);
			if (!$this->_selServiceInstances->Execute($arrWhere))
			{
				Debug("Error on _selServiceInstances!");
				Debug($this->_selServiceInstances->Error());
			}
			else
			{
				$arrService['Id']	= Array();
				while ($arrId = $this->_selServiceInstances->Fetch())
				{
					$arrService['Id'][] = $arrId['Id'];
				}
			}
			$arrServices[$intKey] = $arrService;
		}
		
	
		// Only generate Service Summaries and Itemised calls if there are services to generate for
		if ($intCount >= 1)
		{
			//--------------------------------------------------------------------//
			// SERVICE SUMMARIES
			//--------------------------------------------------------------------//
			
			// build output
			$strCostCentre		= -1;
			$fltCostCentreTotal	= 0.0;
			$this->_arrFileData[] = $arrDefine['SvcSummaryHeader'];
			foreach($arrServices as $arrService)
			{
				//Debug($arrService);
				
				// Add cost centre records
				if ($strCostCentre !== $arrService['CostCentre'])
				{
					if ($strCostCentre !== -1)
					{
						// Was there any data in the last Cost Centre?
						$arrLast = end($this->_arrFileData);
						if ($arrLast['RecordType']['Value'] == '0060')
						{
							//Debug("Popping: ");
							//Debug(array_pop($this->_arrFileData));
							array_pop($this->_arrFileData);
						}
						else
						{
							// add cost centre footer
							$arrDefine['SvcSummCCFooter']	['Total']		['Value']	= $fltCostCentreTotal;
							$this->_arrFileData[] = $arrDefine['SvcSummCCFooter'];
						}
					}
					
					// add cost centre header
					$strCostCentre		= $arrService['CostCentre'];
					$fltCostCentreTotal	= 0.0;
					$arrDefine['SvcSummCCHeader']	['Name']	['Value']	= $strCostCentre;
					$this->_arrFileData[] = $arrDefine['SvcSummCCHeader'];
				}
				
				// Add the Service Summary
				if ($mixResponse = $this->GenerateServiceSummary($arrService))
				{
					$fltCostCentreTotal += $mixResponse;
				}
				//var_dump($mixResponse);
			}
			if ($strCostCentre !== -1)
			{
				// Was there any data in the last Cost Centre?
				$arrLast = end($this->_arrFileData);
				if ($arrLast['RecordType']['Value'] == '0060')
				{
					array_pop($this->_arrFileData);
					//Debug("Popping");
				}
				else
				{
					// add cost centre footer
					$arrDefine['SvcSummCCFooter']	['Total']		['Value']	= $fltCostCentreTotal;
					$this->_arrFileData[] = $arrDefine['SvcSummCCFooter'];
				}
			}
			// add service summary footer
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
				if (is_numeric($arrService['RangeStart']) && is_numeric($arrService['RangeEnd']))
				{
					//$strFNN	= $arrService['FNN']."(".$arrService['ExtensionName'].")";
					$strFNN = $arrService['ExtensionName'];
				}
				else
				{
					$strFNN	= $arrService['FNN'];
				}
				$arrDefine['ItemSvcHeader']	['FNN']				['Value']	= $strFNN;
				$this->_arrFileData[] = $arrDefine['ItemSvcHeader'];
 		
		 		// Set up Ranges
		 		if (!is_numeric($arrService['RangeStart']))
		 		{
		 			if ($arrService['Indial100'])
		 			{
		 				// Indial without ELB
			 			$arrService['RangeStart']	= substr($arrService['FNN'], 0, -2).'00';
			 			$arrService['RangeEnd']		= substr($arrService['FNN'], 0, -2).'99';
		 			}
		 			else
		 			{
			 			// Not an Indial, fake the Range
			 			$arrService['RangeStart']	= $arrService['FNN'];
			 			$arrService['RangeEnd']		= $arrService['FNN'];
		 			}
		 		}
		 		else
		 		{
		 			// Indial, fix the range
		 			$arrService['RangeStart']	= substr($arrService['FNN'], 0, -2).str_pad($arrService['RangeStart'], 2, '0', STR_PAD_LEFT);
		 			$arrService['RangeEnd']		= substr($arrService['FNN'], 0, -2).str_pad($arrService['RangeEnd'], 2, '0', STR_PAD_LEFT);
		 		}
		 		
				
			 	// Fetch the record type data
			 	$arrWhere = Array();
			 	$arrWhere['Service']	= $arrService['Id'];
			 	$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
			 	$arrWhere['RangeStart']	= $arrService['RangeStart'];
			 	$arrWhere['RangeEnd']	= $arrService['RangeEnd'];
				if (($arrItemisedRecordTypes = $this->_BillingFactory(BILL_FACTORY_ITEMISE_RECORD_TYPES, $arrService, $arrWhere)) === FALSE)
				{
					// ERROR
					return FALSE;
				}
			 	
			 	$arrCreditRecordType['Description']	= "Other Charges & Credits";
			 	$arrCreditRecordType['DisplayType']	= RECORD_DISPLAY_S_AND_E;
			 	$arrCreditRecordType['IsCharge']	= TRUE;
			 	$arrItemisedRecordTypes[] = $arrCreditRecordType;
			 	
			 	// Generate the Itemised Call list
			 	$intTotal = 0;
			 	foreach ($arrItemisedRecordTypes as $arrRecordType)
			 	{
				 	if (($mixResponse = $this->GenerateItemisedCalls($arrService, $arrRecordType)) === FALSE)
				 	{
				 		// ERROR
				 		return FALSE;
				 	}
				 	$intTotal += $mixResponse;
			 	}
				
				$arrLast = end($this->_arrFileData);
			 	if ($arrLast['RecordType']['Value'] != '0099')
			 	{
			 		// No itemised calls, remove the header
			 		array_pop($this->_arrFileData);
			 		continue;
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
	 	$arrWhere['Account']	= $arrInvoiceDetails['Account'];
	 	$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
	 	$arrWhere['Service']	= NULL;
		if (($intChargeCount = $this->_selItemisedAccountCharges->Execute($arrWhere)) === FALSE)
		{
			// ERROR
			Debug($this->_selItemisedAccountCharges->Error());
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
			$arrCharges = $this->_selItemisedAccountCharges->FetchAll();
			
			// build header records
			$arrDefine['ItemSvcHeader']		['FNN']				['Value']	= $arrInvoiceDetails['Account'];
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
		$arrDefine['InvoiceFooter']	['Delivery']	['Value']	= $arrInvoiceDetails['DeliveryMethod'];
		$this->_arrFileData[] = $arrDefine['InvoiceFooter'];
		
		// Process and implode the data so it can be inserted into the DB
		$strFileContents = $this->GenerateInvoiceData($this->_arrFileData);
		
		// Are we debugging?
		if ($bolDebug)
		{
			// Yes, return data array
			//return $this->_arrFileData;
			return $strFileContents;
		}
		else
		{
			// Insert into InvoiceOutput table
			$arrWhere['InvoiceRun']	= $arrInvoiceDetails['InvoiceRun'];
			$arrWhere['Account']	= $arrInvoiceDetails['Account'];
			$arrWhere['Data']		= $strFileContents;
			if ($this->_insInvoiceOutput->Execute($arrWhere) === FALSE)
			{
				// Error
				Debug($this->_insInvoiceOutput->Error());
				return FALSE;
			}
			return TRUE;
		}
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
	 * @param		array		$arrAccounts	Indexed array of valid account numbers
	 * 											which have invoices in the InvoiceTemp table
	 * 											Only used with BILL_REPRINT_TEMP
	 * 
	 * @return		string						filename
	 *
	 * @method
	 */
 	function BuildOutput($intOutputType = BILL_COMPLETE, $arrAccounts = NULL)
 	{
		$bolSample			= FALSE;
		$strAccountList		= NULL;
		
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
				break;
				
			case BILL_REPRINT:
				$strFilename		= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".zip";
				$strInvoiceTable	= 'Invoice';
				break;	
				
			case BILL_REPRINT_TEMP:
				$strFilename		= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH."reprint".date("Y-m-d").".zip";
				$strInvoiceTable	= 'InvoiceTemp';
				$strAccountList		= implode(', ', $arrAccounts);
				break;	
		}
		
		$selMetaData = new StatementSelect("InvoiceOutput", "MIN(Id) AS MinId, MAX(Id) AS MaxId, COUNT(Id) AS Invoices, InvoiceRun", "1", NULL, NULL, "InvoiceRun");
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
							"WHERE $strWhere \n";
		
		// Add account list for Sample reprints
		if ($strAccountList)
		{
			$strQuery .= " AND Account IN ($strAccountList) ";
		}
		
		// LIMIT sample runs			
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
	 * @param		array		$arrService		The service to generate a summary for
	 *
	 * @return		mixed						float: total charge
	 * 											FALSE: an error occurred
	 *
	 * @method
	 */
 	function GenerateServiceSummary($arrService)
 	{
		$arrDefine = $this->_arrDefine;
		
		// Check if this is an Indial Extension
		if (is_numeric($arrService['RangeStart']) && is_numeric($arrService['RangeEnd']))
		{
			//$strFNN	= $arrService['FNN']."(".$arrService['ExtensionName'].")";
			$strFNN	= $arrService['ExtensionName'];	// FIXME later on, when file definition is changed
			
			// Ranges
			$strRangeStart	= substr($arrService['FNN'], 0, -2).str_pad($arrService['RangeStart'], 2, '0', STR_PAD_LEFT);
			$strRangeEnd	= substr($arrService['FNN'], 0, -2).str_pad($arrService['RangeEnd'], 2, '0', STR_PAD_LEFT);
			
			// Is the primary FNN in this range?
			if ($arrService['FNN'] >= $strRangeStart && $arrService['FNN'] <= $strRangeEnd)
			{
				$bolPrimary = TRUE;
			}
			else
			{
				$bolPrimary = FALSE;
			}
		}
		elseif ($arrService['Indial100'])
		{
			// Indial without ELB, use full range
			$strFNN			= $arrService['FNN'];
			$strRangeStart	= substr($arrService['FNN'], 0, -2).'00';
			$strRangeEnd	= substr($arrService['FNN'], 0, -2).'99';
			$bolPrimary		= TRUE;
		}
		else
		{
			// Not an Indial, so manually set RangeStart and RangeEnd
			$strFNN			= $arrService['FNN'];
			$strRangeStart	= $arrService['FNN'];
			$strRangeEnd	= $arrService['FNN'];
			$bolPrimary		= NULL;
		}
		
  		// Get ServiceTypeTotals
 		$arrColumns = Array();
 		$arrColumns['Service']		= $arrService['Id'];
 		$arrColumns['RangeStart']	= $strRangeStart;
 		$arrColumns['RangeEnd']		= $strRangeEnd;
 		$arrColumns['InvoiceRun']	= $this->_strInvoiceRun; 		
 		
 		// Generate Query and Execute
 		if (($arrServiceSummaries = $this->_BillingFactory(BILL_FACTORY_SERVICE_SUMMARY, $arrService, $arrColumns)) === FALSE)
 		{
 			return FALSE;
 		}
 		
 		//Debug($arrServiceSummaries);
	 	
	 	// Add Service Charges
	 	if ($bolPrimary === TRUE || $bolPrimary === NULL)
		{
			// It's the primary number, or not an Indial

	 		// Get Charge Totals
	 		$arrColumns = Array();
	 		$arrColumns['Service']		= $arrService['Id'];
	 		$arrColumns['InvoiceRun']	= $this->_strInvoiceRun;
	 		if (($arrChargeSummaries = $this->_BillingFactory(BILL_FACTORY_SERVICE_CHARGES_TOTAL, $arrService, $arrColumns)) === FALSE)
	 		{
	 			// ERROR
	 			return FALSE;
	 		}
			
	 		$arrChargeSummary = Array();
	 		$arrChargeSummary['Charge']		= 0.0;
	 		$arrChargeSummary['Records']	= 0;
	 		$arrChargeSummary['RecordType']	= 'Other Charges & Credits';
	 		foreach ($arrChargeSummaries as $arrSummary)
	 		{
	 			if ($arrSummary['Nature'] == 'CR')
	 			{
	 				$arrChargeSummary['Total']		-= $arrSummary['Charge'];
	 				$arrChargeSummary['Records']	+= $arrSummary['Records'];
	 				//$arrChargeSummary['RecordType']	= $arrSummary['RecordType'];
	 			}
	 			else
	 			{
	 				$arrChargeSummary['Total']		+= $arrSummary['Charge'];
	 				$arrChargeSummary['Records']	+= $arrSummary['Records'];
	 				//$arrChargeSummary['RecordType']	= $arrSummary['RecordType'];
	 			}
	 		}
	 		
	 		if ($arrChargeSummary['Records'] > 0)
	 		{
	 			$arrServiceSummaries[] = $arrChargeSummary;
	 		}
		}
		
		$arrWhere = Array();
		$arrWhere['FNN']		= $arrService['FNN'];
		$arrWhere['Service']	= $arrService['Id'];
		$arrWhere['InvoiceRun']	= $this->_strInvoiceRun;
 		
 		// if we have anything to add to the invoice...
 		//$arrCols = Array();
 		//$arrCols['Service']		= $intService;
 		//$arrCols['InvoiceRun']	= $this->_strInvoiceRun;
 		//if ($this->_selDisplayServiceSummary->Execute($arrCols))
 		if ($arrServiceSummaries || $arrService['ForceInvoiceRender'] || $this->_selHasServiceTotal->Execute($arrWhere))
 		{
			// Get Plan Details
			$this->_selRatePlan->Execute(Array('Id' => end($arrService['Id']), 'InvoiceRun' => $this->_strInvoiceRun));
			$arrRatePlan	= $this->_selRatePlan->Fetch();
			
			// Service Header
			$arrDefine['SvcSummSvcHeader']		['FNN']				['Value']	= $strFNN;
			$arrDefine['SvcSummSvcHeader']		['Plan']			['Value']	= $arrRatePlan['Name'];
			//$arrDefine['SvcSummSvcHeader']		['CostCentre']		['Value']	= $strCostCentre;
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
			
			if ($bolPrimary === TRUE || $bolPrimary === FALSE)
			{
				// An indial, so use the uncapped total
				$arrDefine['SvcSummSvcTotal']		['TotalCapped']		['Value']	= $fltTotal;
			}
			else
			{
				// not an indial, so use the capped total
		 		$arrData = Array();
		 		$arrData['Service']		= $arrService['Id'];
		 		$arrData['InvoiceRun']	= $this->_strInvoiceRun;
		 		if (($arrServiceTotals = $this->_BillingFactory(BILL_FACTORY_SERVICE_TOTAL, $arrService, $arrData)) === FALSE)
		 		{
		 			// ERROR
		 			return FALSE;
		 		}
		 		
		 		// Sum up the Grand Total Capped Charge and Total Plan Charge
		 		$fltGrandTotalCapped		= 0;
		 		$fltGrandTotalPlanCharge	= 0;
		 		foreach ($arrServiceTotals as $arrServiceTotal)
		 		{
		 			$fltGrandTotalCapped		+= (float)$arrServiceTotal['TotalCharge'];
		 			$fltGrandTotalPlanCharge	+= (float)$arrServiceTotal['PlanCharge'];
		 		}
		 		
				$arrDefine['SvcSummSvcTotal']		['TotalCapped']		['Value']	= $fltGrandTotalCapped;
				
				// add in plan charge breakdown
				if ((float)$arrRatePlan['MinMonthly'])
				{
					//Debug("Entered!");
					
					// it must not be a shared plan (shared plans may be handled at a later date)
					if (!$arrRatePlan['Shared'])
					{
						// add in breakdown
						$fltPlanCredit = ($fltGrandTotalCapped - $fltTotal) - $fltGrandTotalPlanCharge;
						$arrDefine['SvcSummPlanSumm']		['PlanCharge']		['Value']	= $fltGrandTotalPlanCharge;
						$arrDefine['SvcSummPlanSumm']		['PlanCredit']		['Value']	= $fltPlanCredit;
						$this->_arrFileData[] = $arrDefine['SvcSummPlanSumm'];
					}
				}
				else
				{
					//Debug("Skipped!");
				}
			}
			
			$this->_arrFileData[] = $arrDefine['SvcSummSvcTotal'];
 			
 			return $fltTotal;
 		}
 		else
 		{
 			return NULL;
 		}
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
 		$arrItemisedCalls = Array();
 		
 		// Is this a Charge itemisation or Call itemisation?
 		if ($arrRecordGroup['IsCharge'] !== TRUE)
 		{
	 		// Get Service RecordGroup Calls
	 		$arrWhere = Array();
	 		$arrWhere['Service']		= $arrService['Id'];
			$arrWhere['RangeStart']		= $arrService['RangeStart'];
			$arrWhere['RangeEnd']		= $arrService['RangeEnd'];
	 		$arrWhere['RecordGroup']	= $arrRecordGroup['RecordType'];
		 	$arrWhere['InvoiceRun']		= $this->_arrInvoiceDetails['InvoiceRun'];
			if (($arrItemisedCalls = $this->_BillingFactory(BILL_FACTORY_ITEMISE_CALLS, $arrService, $arrWhere)) === FALSE)
			{
				// ERROR
				return FALSE;
			}
 		}
		elseif ($arrService['FNN'] >= $arrService['RangeStart'] && $arrService['FNN'] <= $arrService['RangeEnd'])
		{
			// Get Service's Charges
		 	$arrWhere = Array();
		 	$arrWhere['Account']	= $this->_arrInvoiceDetails['Account'];
		 	$arrWhere['InvoiceRun']	= $this->_arrInvoiceDetails['InvoiceRun'];
		 	$arrWhere['Service']	= $arrService['Id'];
			if (($arrCharges = $this->_BillingFactory(BILL_FACTORY_ITEMISE_CHARGES, $arrService, $arrWhere)) === FALSE)
			{
				// ERROR
				return FALSE;
			}
			
			foreach ($arrCharges as $arrCharge)
			{
				//echo "\t\t\t...Adding Itemised Charge...\n";
				// Make sure that the Credits appear as a -ve figure
				if ($arrCharge['Nature'] == NATURE_CR)
				{
					$arrCharge['Charge'] = 0 - $arrCharge['Charge'];
				}
				$arrCharge['Units']			= 1;
				$arrCharge['Description']	= $arrCharge['ChargeType']." - ".$arrCharge['Description'];
				$arrCharge['DisplayType']	= RECORD_DISPLAY_S_AND_E;
				
				// Add to itemised calls array
				$arrItemisedCalls[] = $arrCharge;
			}
		}
		
		if (count($arrItemisedCalls))
		{
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
		}
		return count($arrItemisedCalls);
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
 	
 	
 	
  	//------------------------------------------------------------------------//
	// _BillingFactory()
	//------------------------------------------------------------------------//
	/**
	 * _BillingFactory()
	 *
	 * Creates and executes a Bill Printing Query, summing values for all of the services
	 * passed in
	 *
	 * Creates and executes a Bill Printing Query, summing values for all of the services
	 * passed in
	 * 
	 * @param	integer	$intType		The type of query to run
	 * @param	array	$arrService		MySQL resultset from _selService with additional 'Id' array
	 * @param	array	$arrParams		WHERE parameters
	 *
	 * @return	mixed					string	: invoice data
	 * 									FALSE	: invalid input
	 *
	 * @method
	 */
 	protected function _BillingFactory($intType, $arrService, $arrParams)
 	{
 		$intCount = count($arrService['Id']);
 		
 		// Is there a Statement for this many Service Ids and Type?
 		if (!$this->_arrFactoryQueries[$intType][$intCount])
 		{
	 		$arrWhere = Array();
	 		foreach ($arrService['Id'] as $intKey=>$intId)
	 		{
	 			$arrWhere[] = "Service = <Service$intKey>";
	 		}
	 		$strWhereService = "(".implode(' OR ', $arrWhere).")";
	 		
	 		switch ($intType)
	 		{
	 			case BILL_FACTORY_SERVICE_SUMMARY:
	 				$arrColumns = Array();
			 		$arrColumns['RecordType']	= "GroupType.Description";
			 		$arrColumns['Total']		= "SUM(ServiceTypeTotal.Charge)";
			 		$arrColumns['Records']		= "SUM(Records)";
 					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
 						(
							"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, RecordType AS GroupType",
							$arrColumns,
		 					"$strWhereService AND FNN BETWEEN <RangeStart> AND <RangeEnd> AND InvoiceRun = <InvoiceRun> AND GroupType.Id = RecordType.GroupId",
		 					"ServiceTypeTotal.FNN, GroupType.Description",
		 					NULL,
		 					"GroupType.Description DESC"
	 					);
	 				break;
	 				
	 			case BILL_FACTORY_ITEMISE_RECORD_TYPES:
	 				$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
						(	
							"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id, RecordType AS RecordGroup",
							"RecordGroup.Id AS RecordType, RecordGroup.Description AS Description, RecordGroup.DisplayType AS DisplayType", 
							"$strWhereService AND " .
							"RecordGroup.Id = RecordType.GroupId AND " .
							"RecordGroup.Itemised = 1 AND " .
							"CDR.InvoiceRun = <InvoiceRun> AND " .
							"FNN BETWEEN <RangeStart> AND <RangeEnd>",
							"RecordGroup.Description",
							NULL,
							"RecordGroup.Id"
	 					);
	 				break;
	 				
	 			case BILL_FACTORY_ITEMISE_CALLS:
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
 					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
 					(	
						"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id" .
						", RecordType as RecordGroup",
						$arrColumns,
						"$strWhereService AND " .
						"RecordGroup.Id = RecordType.GroupId AND " .
						"RecordGroup.Id = <RecordGroup> AND " .
						"RecordGroup.Itemised = 1 AND " .
						"CDR.InvoiceRun = <InvoiceRun> AND " .
						"FNN BETWEEN <RangeStart> AND <RangeEnd>",
						"CDR.StartDatetime"
 					);
	 				break;
	 				
	 			case BILL_FACTORY_ITEMISE_CHARGES:
	 				$arrColumns['Charge']				= "Amount";
					$arrColumns['Description']			= "Description";
					$arrColumns['ChargeType']			= "ChargeType";
					$arrColumns['Nature']				= "Nature";
					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
					(	
						"Charge",
						$arrColumns,
						"$strWhereService AND InvoiceRun = <InvoiceRun>"
					);
	 				break;
	 				
	 			case BILL_FACTORY_SERVICE_TOTAL:
					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
					(
						"ServiceTotal",
						"SUM(TotalCharge + Debit - Credit) AS TotalCharge, PlanCharge",
						"$strWhereService AND InvoiceRun = <InvoiceRun>",
						NULL,
						NULL,
						"Service"
					);
	 				break;
	 				
	 			case BILL_FACTORY_SERVICE_CHARGES_TOTAL:
					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
					(
	 					"Charge",
						"SUM(Amount) AS Charge, 'Other Charges & Credits' AS RecordType, COUNT(Id) AS Records, Nature",
						"$strWhereService AND InvoiceRun = <InvoiceRun>",
						"Nature",
						2,
						"Nature"
					);
	 				break;
	 			
	 			default:
	 				// No such Type
	 				return FALSE;
	 		}
 		}
 		
 		// Prepare WHERE parameters
 		foreach ($arrService['Id'] as $intKey=>$intId)
 		{
 			$arrParams["Service$intKey"] = $intId;
 		}
 		
 		// Execute and return data
 		if ($this->_arrFactoryQueries[$intType][$intCount]->Execute($arrParams) === FALSE)
 		{
 			Debug($this->_arrFactoryQueries[$intType][$intCount]->Error());
 			Debug($this->_arrFactoryQueries[$intType][$intCount]->_strQuery);
 			return FALSE;
 		}
 		else
 		{
 			return $this->_arrFactoryQueries[$intType][$intCount]->FetchAll();
 		}
 	}
 }

?>
