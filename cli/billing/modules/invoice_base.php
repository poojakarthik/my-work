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
 	abstract function AddInvoice($arrInvoiceDetails, $bolDebug = FALSE)
 	{
		
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
 	abstract function BuildOutput($intOutputType = BILL_COMPLETE, $arrAccounts = NULL)
 	{
	
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
 	abstract function SendOutput($bolSample)
 	{
		
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
							"CDR USE INDEX (Service_3) JOIN RecordType ON CDR.RecordType = RecordType.Id, RecordType AS RecordGroup",
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
						"CDR USE INDEX (Service_3) JOIN RecordType ON CDR.RecordType = RecordType.Id" .
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
