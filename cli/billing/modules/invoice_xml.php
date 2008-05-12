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
 class BillingModuleInvoiceXML extends BillingModuleInvoice
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
 	function __construct($ptrThisDB, $arrConfig, $strCDRTable = 'CDR')
 	{
		// Call Parent Constructor
		parent::__construct($ptrThisDB, $arrConfig, $strCDRTable);
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
	 * @param		array		$arrInvoice							Associative array of details for this Invoice
	 * @param		boolean		$bolDebug				[optional]	TRUE	: Doesn't write to file, returns XML data
	 * 																FALSE	: Writes to file, returns boolean (default)
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
		$arrCustomer	= $this->_GetCustomerData($arrInvoice);
		
		// Init our XML Document
		$this->_domDocument	= new DOMDocument('1.0'); 
		
		//--------------------------------------------------------------------//
		// Invoice Object
		//--------------------------------------------------------------------//
		$xmlInvoice	= $this->_AddElement($this->_domDocument, 'Invoice');
		$this->_AddAttribute($xmlInvoice, 'Id'				, 'SAMPLE');
		$this->_AddAttribute($xmlInvoice, 'DeliveryMethod'	, GetConstantName($arrInvoice['DeliveryMethod'], 'BillingMethod'));
		
		//--------------------------------------------------------------------//
		// Currency Symbol (at the moment, we always use AUD, so $)
		//--------------------------------------------------------------------//
		$xmlCurrency	= $this->_AddElement($xmlInvoice, 'Currency');
		$xmlSymbol		= $this->_AddElement($xmlCurrency, 'Symbol', '$');
		$this->_AddAttribute($xmlSymbol, 'Location', 'Prefix');
		$xmlNegative	= $this->_AddElement($xmlCurrency, 'Negative', 'CR');
		$this->_AddAttribute($xmlNegative, 'Location', 'Suffix');
		
		//--------------------------------------------------------------------//
		// Account Information
		//--------------------------------------------------------------------//
		$xmlAccount	= $this->_AddElement($xmlInvoice, 'Account');
		$this->_AddAttribute($xmlAccount, 'Id', $arrInvoice['Id']);
		$this->_AddAttribute($xmlAccount, 'Name', $arrInvoice['BusinessName']);
		$this->_AddAttribute($xmlAccount, 'CustomerGroup', GetConstantName($arrCustomer['CustomerGroup'], 'CustomerGroup'));
		$this->_AddElement($xmlAccount, 'Addressee', $arrInvoice['BusinessName']);
		$this->_AddElement($xmlAccount, 'AddressLine1', $arrInvoice['Address1']);
		$this->_AddElement($xmlAccount, 'AddressLine2', $arrInvoice['Address2']);
		$this->_AddElement($xmlAccount, 'Suburb', $arrInvoice['Suburb']);
		$this->_AddElement($xmlAccount, 'Postcode', $arrInvoice['Postcode']);
		$this->_AddElement($xmlAccount, 'State', $arrInvoice['State']);
		
		// Account Summary & Itemisation
		$arrAccountCharges	= $this->_GetAccountCharges($arrInvoice);
		$xmlItemisation	= $this->_AddElement($xmlAccount, 'Charges');
		
		$xmlItemisationType	= $this->_AddElement($xmlItemisation, 'Category');
		$this->_AddAttribute($xmlItemisationType, 'GrandTotal', $arrAccountCharges['TotalCharge']);
		$this->_AddAttribute($xmlItemisationType, 'Records', @count($arrAccountCharges['Itemisation']));
		$this->_AddAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrAccountCharges['DisplayType'], 'DisplayType'));
		
		// Charge Itemisation
		if (@count($arrAccountCharges['Itemisation']))
		{
			$xmlItemisationItems	= $this->_AddElement($xmlItemisationType, 'Items');
			foreach ($arrAccountCharges['Itemisation'] as $arrCDR)
			{
				$xmlItem	= $this->_AddElement($xmlItemisationItems, 'Item');
				
				// Item Fields
				foreach ($arrCDR as $strField=>$mixValue)
				{
					$this->_AddElement($xmlItem, $strField, $mixValue);
				}
			}
		}
		
		
		//--------------------------------------------------------------------//
		// Payment Information
		//--------------------------------------------------------------------//
		$xmlPayment		= $this->_AddElement($xmlInvoice, 'PaymentDetails');
		$xmlBPay		= $this->_AddElement($xmlPayment, 'BPay');
		$this->_AddElement($xmlBPay, 'CustomerReference', $arrInvoice['Account'].MakeLuhn($arrInvoice['Account']));
		$xmlBillExpress	= $this->_AddElement($xmlPayment, 'BillExpress');
		$this->_AddElement($xmlBillExpress, 'CustomerReference', $arrInvoice['Account'].MakeLuhn($arrInvoice['Account']));		// FIXME
		
		//--------------------------------------------------------------------//
		// Statement
		//--------------------------------------------------------------------//
		// HACKHACKHACK: These dates work off the "Bill every month on the 1st" premise
		$intBillingDate			= strtotime($arrInvoice['CreatedOn']);
		$strBillingPeriodStart	= date("j M y", strtotime("-1 month", strtotime(date("Y-m-01", $intBillingDate))));
		$strBillingPeriodEnd	= date("j M y", strtotime("-1 day", strtotime(date("Y-m-01", $intBillingDate))));
		
		// Add to XML schema
		$arrLastInvoice	= $this->_GetOldInvoice($arrInvoice['Account'], 1);
		$xmlStatement	= $this->_AddElement($xmlInvoice, 'Account');
		$this->_AddElement($xmlAccount, 'OpeningBalance', $arrLastInvoice['TotalOwing']);
		$this->_AddElement($xmlAccount, 'Payments', max($arrLastInvoice['TotalOwing'] - $arrInvoice['AccountBalance'], 0.0));
		$this->_AddElement($xmlAccount, 'Overdue', $arrInvoice['AccountBalance']);
		$this->_AddElement($xmlAccount, 'NewCharges', $arrInvoice['Total'] + $arrInvoice['Tax']);
		$this->_AddElement($xmlAccount, 'TotalOwing', $arrInvoice['TotalOwing']);
		$this->_AddElement($xmlAccount, 'BillingPeriodStart', $strBillingPeriodStart);
		$this->_AddElement($xmlAccount, 'BillingPeriodEnd', $strBillingPeriodEnd);
		$this->_AddElement($xmlAccount, 'DueDate', date("j M y", strtotime($arrInvoice['DueOn'])));
		
		//--------------------------------------------------------------------//
		// Service (Data retrieval only)
		//--------------------------------------------------------------------//
		$arrServices	= $this->_GetServices($arrInvoice);
		
		//--------------------------------------------------------------------//
		// Cost Centre Summary
		//--------------------------------------------------------------------//
		$arrCostCentres	= Array();
		foreach ($arrServices as $arrService)
		{
			// Is this Service in a Cost Centre?
			if ($arrService['CostCentre'] && $arrService['IsRendered'])
			{
				$arrCostCentres[$arrService['CostCentre']]['Services'][$arrService['FNN']]	= $arrService['ServiceTotal'];
				$arrCostCentres[$arrService['CostCentre']]['GrandTotal']					+= (float)$arrService['ServiceTotal'];
			}
		}
		
		$xmlCostCentreSummary	= $this->_AddElement($xmlInvoice, 'CostCentreSummary');
		foreach ($arrCostCentres as $arrCostCentre)
		{
			// Get Cost Centre Services
			$xmlCostCentre	= $this->_AddElement($xmlCostCentreSummary, 'CostCentre');
			$this->_AddAttribute($xmlCostCentre, 'Name', $arrCostCentre['Name']);
			$this->_AddAttribute($xmlCostCentre, 'Total', $arrCostCentre['GrandTotal']);
			
			foreach ($arrCostCentre['Services'] as $strFNN=>$fltServiceTotal)
			{
				$xmlService	= $this->_AddElement($xmlCostCentre, 'Service', round($fltServiceTotal, 2));
				$this->_AddAttribute($xmlService, 'FNN', $strFNN);
			}
		}
		
		//--------------------------------------------------------------------//
		// Services XML
		//--------------------------------------------------------------------//
		$xmlServices	= $this->_AddElement($xmlInvoice, 'Services');
		foreach ($arrServices as $arrService)
		{
			$xmlService	= $this->_AddElement($xmlService, 'Service');
			$this->_AddAttribute($xmlService, 'FNN', $arrService['FNN']);
			$this->_AddAttribute($xmlService, 'CostCentre', $arrService['CostCentre']);
			$this->_AddAttribute($xmlService, 'Plan', $arrService['Plan']);
			
			/*// Charge Summary
			$fltChargeTotal		= 0.0;
			$xmlChargeSummary	= $this->_AddElement($xmlService, 'ChargeSummary');
			foreach ($arrService['RecordTypes'] as $strName=>$arrRecordType)
			{
				$xmlChargeType	= $this->_AddElement($xmlChargeSummary, 'Category', $arrRecordType['TotalCharge']);
				$this->_AddAttribute($xmlChargeType, 'Description', $strName);
				
				$fltChargeTotal	+= (float)$arrRecordType['TotalCharge'];
			}
			$this->_AddAttribute($xmlChargeSummary, 'Total', $fltChargeTotal);*/
			
			// Service Itemisation
			$xmlItemisation	= $this->_AddElement($xmlService, 'Itemisation');
			foreach ($arrService['RecordTypes'] as $strName=>$arrChargeType)
			{
				$xmlItemisationType	= $this->_AddElement($xmlItemisation, 'Category');
				$this->_AddAttribute($xmlItemisationType, 'GrandTotal', $arrChargeType['TotalCharge']);
				$this->_AddAttribute($xmlItemisationType, 'Records', count($arrChargeType['Itemisation']));
				$this->_AddAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrChargeType['DisplayType'], 'DisplayType'));
				
				// Charge Itemisation
				$xmlItemisationItems	= $this->_AddElement($xmlItemisationType, 'Items');
				foreach ($arrChargeType['Itemisation'] as $arrCDR)
				{
					$xmlItem	= $this->_AddElement($xmlItemisationItems, 'Item');
					
					// Item Fields
					foreach ($arrCDR as $strField=>$mixValue)
					{
						$this->_AddElement($xmlItem, $strField, $mixValue);
					}
				}
			}
		}
		
		// Determine Output/Return data
		if ($bolDebug)
		{
			// Return XML Schema (plain text)
			$mixReturn	= $this->_domDocument->saveXML();
		}
		else
		{
			// Save to file, return success
			// TODO
			$mixReturn	= FALSE;
		}
		
		// Destroy XML object and return
		unset($this->_domDocument);
		return $mixReturn;
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
				$strFilename		= BILLING_LOCAL_PATH_SAMPLE."xml/sample".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH_SAMPLE."xml/sample".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH_SAMPLE."xml/sample".date("Y-m-d").".zip";
				$strInvoiceTable	= 'InvoiceTemp';
				$bolSample			= TRUE;
				break;
			
			case BILL_COMPLETE:
				$strFilename		= BILLING_LOCAL_PATH."xml/".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH."xml/".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH."xml/".date("Y-m-d").".zip";
				$strInvoiceTable	= 'Invoice';
				break;
				
			case BILL_REPRINT:
				$strFilename		= BILLING_LOCAL_PATH."xml/reprint".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH."xml/reprint".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH."xml/reprint".date("Y-m-d").".zip";
				$strInvoiceTable	= 'Invoice';
				break;	
				
			case BILL_REPRINT_TEMP:
				$strFilename		= BILLING_LOCAL_PATH."xml/reprint".date("Y-m-d").".vbf";
				$strMetaName		= BILLING_LOCAL_PATH."xml/reprint".date("Y-m-d").".vbm";
				$strZipName			= BILLING_LOCAL_PATH."xml/reprint".date("Y-m-d").".zip";
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
	// _AddElement()
	//------------------------------------------------------------------------//
	/**
	 * _AddElement()
	 *
	 * Adds an Element to the $this->_domDocument XML Schema
	 *
	 * Adds an Element to the $this->_domDocument XML Schema
	 * 
	 * @param	element	&$xmlParent					The Parent DOMNode for this Element
	 * @param	array	$strName					The name of this Element
	 * @param	array	$mixValue		[optional]	The value of this Element
	 *
	 * @return	mixed-+
	 *
	 * @method
	 */
 	protected function _AddElement(&$xmlParent, $strName, $mixValue = NULL)
 	{
 		if ($xmlParent instanceof DOMNode)
 		{
 			// Valid Parent
 			$mixReturn	= $xmlParent->appendChild(new DOMElement($strName, $mixValue));
 		}
 		else
 		{
 			// $xmlParent is not a valid Parent Node
 			$mixReturn	= FALSE;
 		}
 		
 		return $mixReturn;
 	}
 	
  	//------------------------------------------------------------------------//
	// _AddAttribute()
	//------------------------------------------------------------------------//
	/**
	 * _AddAttribute()
	 *
	 * Adds an Attribute to the specified XML Element
	 *
	 * Adds an Attribute to the specified XML Element
	 * 
	 * @param	element	&$xmlParent					The Parent DOMNode for this Attribute
	 * @param	array	$strName					The name of this Attribute
	 * @param	array	$mixValue					The value of this Attribute
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	protected function _AddAttribute(&$xmlParent, $strName, $mixValue = NULL)
 	{
 		if ($xmlParent instanceof DOMNode)
 		{
 			// Valid Parent
 			return (bool)$xmlParent->setAttributeNode(new DOMAttr($strName, $mixValue));
 		}
 		else
 		{
 			// $xmlParent is not a valid Parent Node
 			return FALSE;
 		}
 	}
 }

?>