<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// invoice_base
//----------------------------------------------------------------------------//
/**
 * invoice_base
 *
 * Billing module for Invoice Printing Base Class
 *
 * Billing module for Invoice Printing Base Class
 *
 * @file		module_printing.php
 * @language	PHP
 * @package		billing
 * @author		Jared 'flame' Herbohn, Rich 'Waste' Davis
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// BillingModuleInvoice
//----------------------------------------------------------------------------//
/**
 * BillingModuleInvoice
 *
 * Billing module for Invoice Printing Base Class
 *
 * Billing module for Invoice Printing Base Class
 *
 * @prefix		bil
 *
 * @package		billing
 * @class		BillingModuleInvoice
 */
abstract class BillingModuleInvoice
{
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for BillingModuleInvoice
	 *
	 * Constructor method for BillingModuleInvoice
	 *
	 * @return		BillingModuleInvoice
	 *
	 * @method
	 */
 	function __construct($ptrThisDB, $arrConfig, $strCDRTable = 'CDR')
 	{
		// Set up the database reference
		$this->db = $ptrThisDB;
		
		// Init member variables
		$this->_strFilename		= NULL;
		$this->_strSampleFile	= NULL;
		
		//--------------------------------------------------------------------//
		// Statements
		//--------------------------------------------------------------------//
		
		// Service Details
		$arrService					= Array();
		$arrService['FNN']			= "Service.FNN";
		$arrService['CostCentre']	= "(CASE WHEN CostCentreExtension.Id IS NULL THEN CostCentre.Name ELSE CostCentreExtension.Name END)";
		$arrService['Indial100']	= "Service.Indial100";
		$arrService['Extension']	= "ServiceExtension.Name";
		$arrService['RangeStart']	= "ServiceExtension.RangeStart";
		$arrService['RangeEnd']		= "ServiceExtension.RangeEnd";
		$arrService['IsRendered']	= "(CASE WHEN ForceInvoiceRender = 1 THEN 1 WHEN ServiceTotal != 0.0 THEN 1 WHEN Status = ".SERVICE_ACTIVE." THEN 1 ELSE 0 END)";
		$arrService['ServiceTotal']	= "SUM(ServiceTotal.TotalCharge + ServiceTotal.Debit - ServiceTotal.Credit)";
		$arrService['RatePlan']		= "RatePlan.Name";
		$arrService['RatedTotal']	= "ServiceTotal.CappedCharge + ServiceTotal.UncappedCharge";
		$arrService['PlanCharge']	= "ServiceTotal.PlanCharge";
		$this->_selServiceDetails			= new StatementSelect(	"((((Service JOIN ServiceTotal ON ServiceTotal.Service = Service.Id) JOIN RatePlan ON ServiceTotal.RatePlan = RatePlan.Id) LEFT JOIN CostCentre ON CostCentre.Id = Service.CostCentre) LEFT JOIN ServiceExtension ON (ServiceExtension.Service = Service.Id AND ServiceExtension.Archived = 0)) LEFT JOIN CostCentre CostCentreExtension ON ServiceExtension.CostCentre = CostCentreExtension.Id",
																	$arrService,
																	"Service.Account = <Account> AND Status != ".SERVICE_ARCHIVED.")",
																	"Service.ServiceType, Service.FNN, ServiceExtension.Name",
																	NULL,
																	"Service.FNN, ServiceExtension.Name");
		
		$this->_selServiceInstances			= new StatementSelect(	"Service LEFT JOIN ServiceExtension ON (Service.Id = ServiceExtension.Service AND ServiceExtension.Archived = 0)", 
																	"Service.Id AS Id", 
																	"Service.Account = <Account> AND Service.FNN = <FNN> AND (ServiceExtension.Name <=> <Extension>)");
				
		$this->_selAccountSummary			= new StatementSelect(	"(ServiceTypeTotal STT JOIN RecordType RT ON STT.RecordType = RT.Id) JOIN RecordType RG ON RT.GroupId = RG.Id",
																	"RG.Description AS Description, SUM(STT.Charge) AS Total",
																	"Account = <Id> AND InvoiceRun = <InvoiceRun>",
																	"RG.Description",
																	NULL,
																	"RG.Id");
		
		$this->_selAccountSummaryCharges	= new StatementSelect(	"Charge",
																	"SUM(CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Total",
																	//"Account = <Id> AND InvoiceRun = <InvoiceRun> AND LinkType NOT IN (".CHARGE_LINK_PLAN_DEBIT.", ".CHARGE_LINK_PLAN_CREDIT.", ".CHARGE_LINK_PRORATA.")");
																	"Account = <Id> AND InvoiceRun = <InvoiceRun> AND ChargeType NOT LIKE 'PCP%' AND ChargeType NOT LIKE 'PCA%'");
		
		$this->_selPlanCharges				= new StatementSelect(	"Charge",
																	"SUM(CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE 0 END) AS PlanCredit, SUM(CASE WHEN Nature = 'DR' THEN Amount ELSE 0 END) AS PlanDebit",
																	//"Account = <Id> AND InvoiceRun = <InvoiceRun> AND LinkType IN (".CHARGE_LINK_PLAN_DEBIT.", ".CHARGE_LINK_PLAN_CREDIT.", ".CHARGE_LINK_PRORATA.")");
																	"Account = <Id> AND InvoiceRun = <InvoiceRun> AND (ChargeType LIKE 'PCP%' OR ChargeType LIKE 'PCA%')");
		
		$this->_selCustomerData				= new StatementSelect(	"Account",
																	"BusinessName, Address1, Address2, Suburb, Postcode, State, CustomerGroup",
																	"Id = <Account>");
		
		$this->_selPlanAdjustments			= new StatementSelect(	"Charge",
																	"SUM(CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Total",
																	"InvoiceRun = <InvoiceRun> AND Account = <Account>",
																	NULL,
																	NULL,
																	"Account");
		
		$this->_selPlanChargeTotals			= new StatementSelect(	"ServiceTotal",
																	"SUM(PlanCharge) AS PlanChargeTotal, SUM(UncappedCharge + CappedCharge) AS RatedTotal, SUM(TotalCharge) AS GrandServiceTotal",
																	"InvoiceRun = <InvoiceRun> AND Account = <Account>",
																	NULL,
																	NULL,
																	"Account");
		
		$this->_selAccountAdjustments		= new StatementSelect(	"Charge",
																	"ChargeType, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description",
																	"InvoiceRun = <InvoiceRun> AND Account = <Account> AND Service IS NULL AND ChargeType NOT LIKE 'PCP%' AND ChargeType NOT LIKE 'PCA%'");
		
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
 	abstract protected function AddInvoice($arrInvoiceDetails, $bolDebug = FALSE);
 	
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
 	abstract protected function BuildOutput($intOutputType = BILL_COMPLETE, $arrAccounts = NULL);
 	
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
 	abstract protected function SendOutput($bolSample);
 	
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
							"$strCDRTable USE INDEX (Service_3) JOIN RecordType ON $strCDRTable.RecordType = RecordType.Id, RecordType AS RecordGroup",
							"RecordGroup.Id AS RecordType, RecordGroup.Description AS Description, RecordGroup.DisplayType AS DisplayType", 
							"$strWhereService AND " .
							"RecordGroup.Id = RecordType.GroupId AND " .
							"RecordGroup.Itemised = 1 AND " .
							"$strCDRTable.InvoiceRun = <InvoiceRun> AND " .
							"FNN BETWEEN <RangeStart> AND <RangeEnd>",
							"RecordGroup.Description",
							NULL,
							"RecordGroup.Id"
	 					);
	 				break;
	 				
	 			case BILL_FACTORY_ITEMISE_CALLS:
					$arrColumns = Array();
					$arrColumns['Charge']			= "$strCDRTable.Charge";
					$arrColumns['Source']			= "$strCDRTable.Source";
					$arrColumns['Destination']		= "$strCDRTable.Destination";
					$arrColumns['StartDatetime']	= "$strCDRTable.StartDatetime";
					$arrColumns['EndDatetime']		= "$strCDRTable.EndDatetime";
					$arrColumns['Units']			= "$strCDRTable.Units";
					$arrColumns['Description']		= "$strCDRTable.Description";
					$arrColumns['DestinationCode']	= "$strCDRTable.DestinationCode";
					$arrColumns['DisplayType']		= "RecordGroup.DisplayType";
					$arrColumns['RecordGroup']		= "RecordGroup.Description";
 					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
 					(	
						"$strCDRTable USE INDEX (Service_3) JOIN RecordType ON $strCDRTable.RecordType = RecordType.Id" .
						", RecordType as RecordGroup",
						$arrColumns,
						"$strWhereService AND " .
						"RecordGroup.Id = RecordType.GroupId AND " .
						"RecordGroup.Id = <RecordGroup> AND " .
						/*"RecordGroup.Itemised = 1 AND " .*/
						"$strCDRTable.InvoiceRun = <InvoiceRun> AND " .
						"FNN BETWEEN <RangeStart> AND <RangeEnd>",
						"$strCDRTable.StartDatetime"
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
						"$strWhereService AND InvoiceRun = <InvoiceRun> AND ChargeType NOT LIKE 'PCP%' AND ChargeType NOT LIKE 'PCA%'"
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
	 				
	 			case BILL_FACTORY_RECORD_TYPES:
					$arrRecordType	= Array();
					$arrRecordType['RecordGroup']	= "RecordGroup.Description";
					$arrRecordType['GroupId']		= "RecordGroup.Id";
					/*$arrRecordType['Itemised']		= "RecordGroup.Itemised";*/
					$arrRecordType['DisplayType']	= "RecordGroup.DisplayType";
					$arrRecordType['TotalCharge']	= "ServiceTypeTotal.Charge";
					$arrRecordType['Records']		= "RecordGroup.DisplayType";
					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
					(
	 					"(ServiceTypeTotal JOIN RecordType ON RecordType.Id = ServiceTypeTotal.RecordType) JOIN RecordType RecordGroup ON RecordType.GroupId = RecordGroup.Id",
						$arrRecordType,
						"InvoiceRun = <InvoiceRun> AND $strWhereService AND FNN BETWEEN <RangeStart> AND <RangeEnd>"
					);
	 				break;
	 				
	 			case BILL_FACTORY_PLAN_ADJUSTMENTS:
	 				$arrColumns['Charge']				= "Amount";
					$arrColumns['Description']			= "Description";
					$arrColumns['ChargeType']			= "ChargeType";
					$arrColumns['Nature']				= "Nature";
					$this->_arrFactoryQueries[$intType][$intCount] = new StatementSelect
					(	
						"Charge",
						$arrColumns,
						"$strWhereService AND InvoiceRun = <InvoiceRun> AND (ChargeType LIKE 'PCP%' OR ChargeType LIKE 'PCA%')"
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
 	
  	//------------------------------------------------------------------------//
	// _GetOldInvoice()
	//------------------------------------------------------------------------//
	/**
	 * _GetOldInvoice()
	 *
	 * Returns the Invoice Data from the Xth last Invoice
	 *
	 * Returns the Invoice Data from the Xth last Invoice
	 * 
	 * @param	integer	$intAccount					The Account number to limit to
	 * @param	integer	$intPeriodsAgo				The number of billing periods ago to check (eg. 1 will return the last Invoice)
	 *
	 * @return	array								Old Invoice Data
	 *
	 * @method
	 */
	protected function _GetOldInvoice($intAccount, $intPeriodsAgo)
	{
		if ((int)$intPeriodsAgo < 1)
		{
			// Either not an integer, or an invalid number of periods ago
			return FALSE;
		}
		
		$intPeriodsAgo--;
		$selOldInvoice	= new StatementSelect("Invoice", "*", "Account = $intAccount", "CreatedOn", "$intPeriodsAgo, 1");
		if ($selOldInvoice->Execute === FALSE)
		{
			Debug($selOldInvoice->Error());
			return Array();
		}
		
		// Return data or empty array
		if ($arrInvoice = $selOldInvoice->Fetch())
		{
			return $arrInvoice;
		}
		else
		{
			return Array();
		}
	}
 	
  	//------------------------------------------------------------------------//
	// _GetCustomerData()
	//------------------------------------------------------------------------//
	/**
	 * _GetCustomerData()
	 *
	 * Returns the Account's Customer Data
	 *
	 * Returns the Account's Customer Data
	 * 
	 * @param	array	$arrInvoice					Invoice Details
	 *
	 * @return	array								Customer Data Array
	 *
	 * @method
	 */
	protected function _GetCustomerData($arrInvoice)
	{		
		// Retrieve the Customer Data
		if ($this->_selCustomerData->Execute === FALSE)
		{
			Debug($this->_selCustomerData->Error());
			return Array();
		}
		
		// Return data or empty array
		if ($arrCustomer = $this->_selCustomerData->Fetch())
		{
			return $arrCustomer;
		}
		else
		{
			return Array();
		}
	}
 	
  	//------------------------------------------------------------------------//
	// _GetServices()
	//------------------------------------------------------------------------//
	/**
	 * _GetServices()
	 *
	 * Gets a list of Services that have been Invoiced this run
	 *
	 * Gets a list of Services that have been Invoiced this run
	 * 
	 * @param	array	$arrInvoice						Invoice Details
	 * 
	 * @return	array									Account Summary Array
	 *
	 * @method
	 */
	protected function _GetServices($arrInvoice)
	{		
		// Get Service Details
		if ($this->_selServiceDetails->Execute($arrInvoice) === FALSE)
		{
			Debug($this->_selServiceDetails->Error());
			return Array();
		}
		
		$arrServices	= $this->_selServices->FetchAll();
		
		// Get List of Service IDs for each FNN
		foreach ($arrServices as $intKey=>$arrService)
		{
			$arrWhere = Array();
			$arrWhere['Account']	= $arrInvoice['Account'];
			$arrWhere['FNN']		= $arrService['FNN'];
			$arrWhere['Extension']	= $arrService['Extension'];
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
		
		foreach ($arrServices as &$arrService)
		{
			// Get Record Types
			$arrWhere	= Array();
			$arrWhere['InvoiceRun']	= $arrInvoice['InvoiceRun'];
			$arrWhere['RangeStart']	= $arrService['RangeStart'];
			$arrWhere['RangeEnd']	= $arrService['RangeEnd'];
			$arrRecordTypes	= $this->_BillingFactory(BILL_FACTORY_RECORD_TYPES, $arrService, $arrWhere);
			foreach ($arrRecordTypes as $arrRecordType)
			{
				// Get Call Itemisation
				$arrWhere['RecordGroup']		= $arrRecordType['GroupId'];
				$arrRecordType['Itemisation']	= $this->_BillingFactory(BILL_FACTORY_ITEMISE_CALLS, $arrService, $arrWhere);
				
				// Add Record Type to Service Array
				$arrService['RecordTypes'][$arrRecordType['Description']]	= $arrRecordType;
			}
			
			// Get Adjustments
			$arrItemised	= $this->_BillingFactory(BILL_FACTORY_ITEMISE_CHARGES, $arrService, $arrWhere);
			if (count($arrItemised))
			{
				$fltAdjustmentsTotal	= 0;
				
				// Convert each Adjustment to a CDR
				foreach ($arrItemised as $arrCharge)
				{
					$arrCDR	= Array();
					if ($arrCharge['Nature'] == NATURE_CR)
					{
						$arrCDR['Charge']	= 0 - $arrCharge['Charge'];
					}
					$fltAdjustmentsTotal	+= $arrCDR['Charge'];
					
					$arrCDR['Units']		= 1;
					$arrCDR['Description']	= $arrCharge['ChargeType']." - ".$arrCharge['Description'];
					
					$arrRecordTypes['Other Charges & Credits']['Itemisation'][]	= $arrCDR;
				}
				
				$arrRecordTypes['Other Charges & Credits']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
				$arrRecordTypes['Other Charges & Credits']['TotalCharge']	= $fltAdjustmentsTotal;
				$arrRecordTypes['Other Charges & Credits']['Records']		= count($arrItemised);
			}
			
			// Get Plan Charges & Credits
			$fltPlanChargeTotal			= 0;
			$arrPlanAdjustments			= $this->_BillingFactory(BILL_FACTORY_PLAN_ADJUSTMENTS, $arrService, $arrWhere);
			$arrPlanChargeItemisation	= Array();
			foreach ($arrPlanAdjustments as $arrAdjustment)
			{
				// Format Plan Adjustment as CDR
				$arrCDR	= Array();
				if ($arrCharge['Nature'] == NATURE_CR)
				{
					$arrCDR['Charge']		= 0 - $arrCharge['Charge'];
				}
				$fltPlanChargeTotal			+= $arrCDR['Charge'];
				
				$arrCDR['Units']			= 1;
				$arrCDR['Description']		= $arrCharge['ChargeType']." - ".$arrCharge['Description'];
				$arrPlanChargeItemisation[]	= $arrCDR;
			}
			
			// Add ServiceTotal.PlanCharge as a CDR
			if ((float)$arrService['PlanCharge'])
			{
				// Add an adjustment (Date is hack fixed to 1 month periods)
				$fltPlanChargeTotal			= $arrService['PlanCharge'];
				
				$arrCDR	= Array();
				$arrCDR['Charge']			= $arrService['PlanCharge'];				
				$arrCDR['Units']			= 1;
				$arrCDR['Description']		= "{$arrService['RatePlan']} Plan Charge from ".date("01/m/Y", strtotime("-1 month", strtotime(date($arrInvoice['CreatedOn']))))." to ".date("d/m/Y", strtotime("-1 day", date("01/m/Y", strtotime($arrInvoice['CreatedOn']))));
				$arrPlanChargeItemisation[]	= $arrCDR;
			
				// Check for ServiceTotal vs Rated Total, then add as CDR
				if ($arrService['ServiceTotal'] - $arrService['RatedTotal'])
				{
					$fltPlanChargeTotal			= $arrService['ServiceTotal'] - $arrService['RatedTotal'];
					
					$arrCDR	= Array();
					$arrCDR['Charge']			= $arrService['ServiceTotal'] - $arrService['RatedTotal'];
					$arrCDR['Units']			= 1;
					$arrCDR['Description']		= "{$arrService['RatePlan']} Plan Credit from ".date("01/m/Y", strtotime("-1 month", strtotime(date($arrInvoice['CreatedOn']))))." to ".date("d/m/Y", strtotime("-1 day", date("01/m/Y", strtotime($arrInvoice['CreatedOn']))));
					$arrPlanChargeItemisation[]	= $arrCDR;
				}
			}
			
			// Add to Service Array
			if (count($arrPlanChargeItemisation))
			{
				$arrRecordTypes['Plan Charges & Credits']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
				$arrRecordTypes['Plan Charges & Credits']['TotalCharge']	= $fltPlanChargeTotal;
				$arrRecordTypes['Plan Charges & Credits']['Records']		= count($arrPlanChargeItemisation);
				$arrRecordTypes['Plan Charges & Credits']['Itemisation']	= $arrPlanChargeItemisation;
			}
			
			$arrService['RecordTypes']	= $arrRecordTypes;
		}
		
		return $arrServices;
	}
 	
  	//------------------------------------------------------------------------//
	// _GetAccountAdjustments()
	//------------------------------------------------------------------------//
	/**
	 * _GetAccountAdjustments()
	 *
	 * Returns a CDR array of Account Adjustments
	 *
	 * Returns a CDR array of Account Adjustments
	 * 
	 * @param	array	$arrInvoice						Invoice Details
	 *
	 * @return	array									Account Adjustments Array
	 *
	 * @method
	 */
	protected function _GetAccountAdjustments($arrInvoice)
	{
		$arrAdjustments			= Array();
		$fltAccountChargeTotal	= 0.0;
		if ($this->_selAccountAdjustments->Execute($arrInvoice) === FALSE)
		{
			Debug($this->_selAccountAdjustments->Error());
		}
		else
		{
			while ($arrAdjustment = $this->_selAccountAdjustments-Fetch())
			{
				$arrCDR								= Array();
				$arrCDR['Description']				= $arrAdjustment['ChargeType'] . ' - ' . $arrAdjustment['Description'];
				$arrCDR['Units']					= 1;
				$arrCDR['Charge']					= $arrAdjustment['Amount'];
				$arrAdjustments['Itemisation'][]	= $arrCDR;
				$fltAccountChargeTotal				+= $arrCDR['Charge'];
			}
		}
		
		$arrAdjustments['DisplayType']	= RECORD_DISPLAY_S_AND_E;
		$arrAdjustments['TotalCharge']	= $fltAccountChargeTotal;
		$arrAdjustments['Records']		= count($arrAdjustments['Itemisation']);
		
		return $arrAdjustments;
	}
 	
  	//------------------------------------------------------------------------//
	// _GetAccountCharges()
	//------------------------------------------------------------------------//
	/**
	 * _GetAccountCharges()
	 *
	 * Returns the Account Summary and Itemisation as an associative array for a given Invoice
	 *
	 * Returns the Account Summary and Itemisation as an associative array for a given Invoice
	 * 
	 * @param	array	$arrInvoice						Invoice Details
	 * @param	boolean	$bolAdjustments		[optional]	TRUE	: Include 'Other Charges & Credits'
	 * 													FALSE	: Do not add Adjustments
	 * @param	boolean	$bolPlanAdjustments	[optional]	TRUE	: Include 'Plan Charges' and 'Plan Credits'
	 * 													FALSE	: Do not add Plan Adjustments
	 * @param	boolean	$bolGST				[optional]	TRUE	: Add GST Total as the final element (default)
	 * 													FALSE	: Do not add GST Total
	 *
	 * @return	array								Account Summary Array
	 *
	 * @method
	 */
	protected function _GetAccountCharges($arrInvoice, $bolAdjustments = TRUE, $bolPlanAdjustments = TRUE, $bolGST = TRUE)
	{
		$arrAccountSummary	= Array();
		
		// Get Account Summary
		if ($this->_selAccountSummary->Execute($arrInvoice) === FALSE)
		{
			Debug($this->_selAccountSummary->Error());
		}
		else
		{
			while ($arrSummary = $this->_selAccountSummary->Fetch())
			{
				$arrAccountSummary[$arrSummary['Description']]['TotalCharge']	= round($arrSummary['Total'], 2);
			}
		}
		
		// Add Other Charges and Credits
		if ($bolAdjustments)
		{
			if ($this->_selAccountSummaryCharges->Execute($arrInvoice) === FALSE)
			{
				Debug($this->_selAccountSummaryCharges->Error());
			}
			else
			{
				$arrAccountSummary['Other Charges & Credits']['TotalCharge']	= round($arrSummary['Total'], 2);
			}
		}
		
		// Account Charges and Credits
		$arrAccountSummary['Account Charges & Credits']	= $this->_GetAccountAdjustments($arrInvoice);
		
		// Add Plan Charges and Credits
		// TODO: This will work when Plan Charges are entirely Adjustment-based, and use the Charge Links
		/*if ($bolPlanAdjustments)
		{
			if ($this->_selPlanCharges->Execute($arrInvoice) === FALSE)
			{
				Debug($this->_selPlanCharges->Error());
			}
			else
			{
				$arrPlanCharges	= $this->_selPlanCharges->Fetch();
				
				// Add Plan Charges
				if ((float)$arrPlanCharges['PlanDebit'])
				{
					$arrAccountSummary['Plan Charges']	= round($arrPlanCharges['PlanDebit'], 2);
				}
				
				// Add Plan Credits
				if ((float)$arrPlanCharges['PlanCredit'])
				{
					$arrAccountSummary['Plan Credits']	= round($arrPlanCharges['PlanCredit'], 2);
				}
			}
		}*/
		
		if ($bolPlanAdjustments)
		{
			$fltGrandTotal	= 0.0;
			
			// Plan Adjustments
			if ($this->_selPlanAdjustments->Execute($arrInvoice) === FALSE)
			{
				Debug($this->_selPlanAdjustments->Error());
			}
			else
			{
				$arrPlanCharges	= $this->_selPlanAdjustments->Fetch();
				$fltGrandTotal	+= $arrPlanCharges['Total'];
			}
			
			// ServiceTotal Plan Charges
			if ($this->_selPlanChargeTotals->Execute($arrInvoice))
			{
				Debug($this->_selPlanChargeTotals->Error());
			}
			else
			{
				$arrPlanCharges	= $this->_selPlanChargeTotals->Fetch();
				$fltGrandTotal	+= ($arrPlanCharges['GrandServiceTotal'] - $arrPlanCharges['RatedTotal']);
			}
			
			$fltGrandTotal	= round($fltGrandTotal, 2);
			if ($fltGrandTotal)
			{
				$arrAccountSummary['Plan Charges & Credits']['TotalCharge']	 = $fltGrandTotal;
			}
		}
		
		// Add GST Element
		if ($bolGST)
		{
			$arrAccountSummary['GST Total']['TotalCharge']	= round($arrInvoice['Tax'], 2);
		}
		
		// Return Array
		return $arrAccountSummary;
	}
}

?>
