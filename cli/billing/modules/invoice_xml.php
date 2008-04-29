<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// invoice_xml
//----------------------------------------------------------------------------//
/**
 * invoice_xml
 *
 * Billing Module for Invoice Export to XML
 *
 * Billing Module for Invoice Export to XML
 *
 * @file		invoice_xml.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste' Davis
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// BillingModuleInvoiceXML
//----------------------------------------------------------------------------//
/**
 * BillingModuleInvoiceXML
 *
 * Billing Module for Invoice Export to XML
 *
 * Billing Module for Invoice Export to XML
 *
 * @prefix		bil
 *
 * @package		billing
 * @class		BillingModuleInvoiceXML
 */
 class BillingModuleInvoiceXML
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for BillingModuleInvoiceXML
	 *
	 * Constructor method for BillingModuleInvoiceXML
	 *
	 * @return		BillingModuleInvoiceXML
	 *
	 * @method
	 */
 	function __construct($ptrThisDB, $arrConfig)
 	{
		// Call Parent Constructor
		parent::__construct();
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
	 * @param		array		$arrInvoice				Associative array of details for this Invoice
	 * @param		boolean		$bolDebug				optional TRUE	: Doesn't insert to database, returns data array
	 * 															 FALSE	: Inserts to database, returns boolean
	 *
	 * @return		mixed
	 *
	 * @method
	 */
 	function AddInvoice($arrInvoice, $bolDebug = FALSE)
 	{
		// Init Output Array
		$arrOutputData	= Array();
		
		// Get Customer Information
		// TODO
		
		// Init our XML Document
		$this->_domDocument	= new DOMDocument('1.0'); 
		 
		// Invoice Object
		$xmlInvoice	= $this->_AddElement($this->_domDocument, 'Invoice');
		$this->_AddAttribute($xmlInvoice, 'Id', 'SAMPLE');
		
		// Currency Symbol (at the moment, we always use AUD, so $)
		$this->_AddElement($xmlInvoice, 'CurrencySymbol', '$');
		
		// Account Information
		$xmlAccount	= $this->_AddElement($xmlInvoice, 'Account');
		$this->_AddAttribute($xmlAccount, 'Id', $arrInvoice['Id']);
		$this->_AddAttribute($xmlAccount, 'Name', $arrInvoice['BusinessName']);
		$this->_AddElement($xmlAccount, 'Addressee', $arrInvoice['BusinessName']);
		$this->_AddElement($xmlAccount, 'AddressLine1', $arrInvoice['Address1']);
		$this->_AddElement($xmlAccount, 'AddressLine2', $arrInvoice['Address2']);
		$this->_AddElement($xmlAccount, 'Suburb', $arrInvoice['Suburb']);
		$this->_AddElement($xmlAccount, 'Postcode', $arrInvoice['Postcode']);
		$this->_AddElement($xmlAccount, 'State', $arrInvoice['State']);
		$this->_AddElement($xmlAccount, 'CustomerReference', $arrInvoice['Id'].MakeLuhn($arrInvoice['Id']));
		
		// Statement
		$xmlStatement	= $this->_AddElement($xmlInvoice, 'Account');
		$this->_AddElement($xmlAccount, 'OpeningBalance', NULL);								// FIXME
		$this->_AddElement($xmlAccount, 'Payments', NULL);										// FIXME
		$this->_AddElement($xmlAccount, 'Overdue', $arrOutputData['AccountBalance']);
		$this->_AddElement($xmlAccount, 'NewCharges', $arrOutputData['Total'] + $arrOutputData['Tax']);
		$this->_AddElement($xmlAccount, 'TotalOwing', $arrOutputData['TotalOwing']);
		$this->_AddElement($xmlAccount, 'BillingPeriodStart', NULL);							// FIXME
		$this->_AddElement($xmlAccount, 'BillingPeriodEnd', NULL);								// FIXME
		
		// Account Summary
		$arrChargeTotal		= $this->_GetAccountSummary($arrInvoice);
		$xmlAccountSummary	= $this->_AddElement($xmlInvoice, 'AccountSummary');
		foreach ($arrChargeTotals as $arrChargeTotal)
		{
			$this->_AddElement($xmlAccountSummary, 'Category', $arrChargeTotal['Total']);
			$this->_AddAttribute($xmlAccountSummary, 'Description', $arrChargeTotal['Description']);
		}
		$this->_AddAttribute($xmlAccountSummary, 'GrandTotal', $arrInvoiceDetails['Total'] + $arrInvoiceDetails['Tax']);
		
		// Cost Centre Summary
		$xmlCostCentreSummary	= $this->_AddElement($xmlInvoice, 'CostCentreSummary');
		foreach ($arrCostCentres as $arrCostCentre)
		{
			// Get Cost Centre Services
			$xmlCostCentre	= $this->_AddElement($xmlCostCentreSummary, 'CostCentre');
			$this->_AddAttribute($xmlCostCentre, 'Name', $arrCostCentre['Name']);
			$this->_AddAttribute($xmlCostCentre, 'Total', $arrCostCentre['GrandTotal']);
			
			foreach ($arrCostCentreServices as $arrCostCentreService)
			{
				$xmlService	= $this->_AddElement($xmlCostCentre, 'Service', $arrCostCentreService['GrandTotal']);
				$this->_AddAttribute($xmlService, 'FNN', $arrCostCentreService['FNN']);
			}
		}
		
		// Services
		$xmlServices	= $this->_AddElement($xmlInvoice, 'Services');
		foreach ($arrServices as $arrService)
		{
			$xmlService	= $this->_AddElement($xmlService, 'Service');
			$this->_AddAttribute($xmlService, 'FNN', $arrService['FNN']);
			$this->_AddAttribute($xmlService, 'CostCentre', $arrService['CostCentre']);
			$this->_AddAttribute($xmlService, 'Plan', $arrService['Plan']);
			
			// Charge Summary
			$fltChargeTotal		= 0.0;
			$xmlChargeSummary	= $this->_AddElement($xmlService, 'ChargeSummary');
			foreach ($arrChargeSummaries as $arrChargeSummary)
			{
				$xmlChargeType	= $this->_AddElement($xmlChargeSummary, 'Category', $arrChargeSummary['GrandTotal']);
				$this->_AddAttribute($xmlChargeType, 'Description', $arrChargeSummary['Description']);
				
				$fltChargeTotal	+= (float)$arrChargeSummary['GrandTotal'];
			}
			$this->_AddAttribute($xmlChargeSummary, 'Total', $arrService['ServiceTotal']);
			
			// Service Itemisation
			$xmlItemisation	= $this->_AddElement($xmlService, 'Itemisation');
			foreach ($arrChargeTypes as $arrChargeType)
			{
				$xmlItemisationType	= $this->_AddElement($xmlItemisation, 'Category');
				$this->_AddAttribute($xmlItemisationType, 'GrandTotal', $arrChargeType['GrandTotal']);
				$this->_AddAttribute($xmlItemisationType, 'RenderType', NULL);								// FIXME
				
				// Charge Itemisation
				$xmlItemisationItems	= $this->_AddElement($xmlItemisationType, 'Items');
				foreach ($arrCDRs as $arrCDR)
				{
					$xmlItem	= $this->_AddElement($xmlItemisationItems, 'Item');
					
					// Item Fields
					foreach ($arrFields as $strField)
					{
						$this->_AddElement($xmlItem, 'Field', $arrCDR[$strField]);
					}
				}
			}
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
