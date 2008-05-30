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
 	function __construct($ptrThisDB, $arrConfig)
 	{
		// Call Parent Constructor
		parent::__construct($ptrThisDB, $arrConfig);
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
		// Redundant
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
		$this->_domDocument					= new DOMDocument('1.0'); 
		$this->_domDocument->formatOutput	= TRUE;
		
		//--------------------------------------------------------------------//
		// Service (Data retrieval only)
		//--------------------------------------------------------------------//
		$arrServices	= $this->_GetServices($arrInvoice);
		
		//--------------------------------------------------------------------//
		// Document Object
		//--------------------------------------------------------------------//
		$xmlDocument	= $this->_AddElement($this->_domDocument, 'Document');
		$this->_AddElement($xmlDocument, 'DocumentType', 'DOCUMENT_TYPE_INVOICE');
		$this->_AddElement($xmlDocument, 'CustomerGroup', GetConstantName($arrCustomer['CustomerGroup'], 'CustomerGroup'));
		$this->_AddElement($xmlDocument, 'CreationDate', date('Y-m-d H:i:s', strtotime($arrInvoice['CreatedOn'])));
		$this->_AddElement($xmlDocument, 'DeliveryMethod', GetConstantName($arrInvoice['DeliveryMethod'], 'DeliveryMethod'));
		
		//--------------------------------------------------------------------//
		// Invoice Object
		//--------------------------------------------------------------------//
		$xmlInvoice	= $this->_AddElement($xmlDocument, 'Invoice');
		$this->_AddAttribute($xmlInvoice, 'Id', ($this->_strInvoiceTable == 'Invoice') ? $arrInvoice['Id'] : 'SAMPLE');
		//$this->_AddAttribute($xmlInvoice, 'DeliveryMethod'	, GetConstantName($arrInvoice['DeliveryMethod'], 'BillingMethod'));
		
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
		$this->_AddAttribute($xmlAccount, 'Id', $arrInvoice['Account']);
		$this->_AddAttribute($xmlAccount, 'Name', $arrCustomer['BusinessName']);
		$this->_AddAttribute($xmlAccount, 'CustomerGroup', GetConstantName($arrCustomer['CustomerGroup'], 'CustomerGroup'));
		$this->_AddElement($xmlAccount, 'Addressee', $arrCustomer['BusinessName']);
		$this->_AddElement($xmlAccount, 'AddressLine1', $arrCustomer['Address1']);
		$this->_AddElement($xmlAccount, 'AddressLine2', $arrCustomer['Address2']);
		$this->_AddElement($xmlAccount, 'Suburb', $arrCustomer['Suburb']);
		$this->_AddElement($xmlAccount, 'Postcode', $arrCustomer['Postcode']);
		$this->_AddElement($xmlAccount, 'State', $arrCustomer['State']);
		
		//--------------------------------------------------------------------//
		// Account Summary & Itemisation
		//--------------------------------------------------------------------//
		$arrAccountCategories	= $this->_GetAccountCharges($arrInvoice);
		$xmlItemisation	= $this->_AddElement($xmlInvoice, 'Charges');
		
		// Charge Itemisation
		foreach ($arrAccountCategories as $strName=>$arrCategory)
		{
			$xmlItemisationType	= $this->_AddElement($xmlItemisation, 'Category');
			$this->_AddAttribute($xmlItemisationType, 'Name', $strName);
			$this->_AddAttribute($xmlItemisationType, 'GrandTotal', number_format($arrCategory['TotalCharge'], 2, '.', ''));
			$this->_AddAttribute($xmlItemisationType, 'Records', @count($arrCategory['Itemisation']));
			$this->_AddAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrCategory['DisplayType'], 'DisplayType'));
			
			$xmlItemisationItems	= $this->_AddElement($xmlItemisationType, 'Items');
			
			if ($arrCategory['Itemisation'])
			{
				foreach ($arrCategory['Itemisation'] as $arrCDR)
				{
					$xmlItem	= $this->_AddElement($xmlItemisationItems, 'Item');
					
					// Process the CDR
					$arrItem	= $this->_CDR2Itemise($arrCDR, $arrCategory['DisplayType']);
					
					// Item Fields
					foreach ($arrItem as $strField=>$mixValue)
					{
						$this->_AddElement($xmlItem, $strField, $mixValue);
					}
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
		$arrLastInvoice	= $this->_GetOldInvoice($arrInvoice, 1);
		$xmlStatement	= $this->_AddElement($xmlInvoice, 'Statement');
		$this->_AddElement($xmlStatement, 'OpeningBalance', number_format($arrLastInvoice['TotalOwing'], 2, '.', ''));
		$this->_AddElement($xmlStatement, 'Payments', number_format(max($arrLastInvoice['TotalOwing'] - $arrInvoice['AccountBalance'], 0.0), 2, '.', ''));
		$this->_AddElement($xmlStatement, 'Overdue', number_format($arrInvoice['AccountBalance'], 2, '.', ''));
		$this->_AddElement($xmlStatement, 'NewCharges', number_format($arrInvoice['Total'] + $arrInvoice['Tax'], 2, '.', ''));
		$this->_AddElement($xmlStatement, 'TotalOwing', number_format($arrInvoice['TotalOwing'], 2, '.', ''));
		$this->_AddElement($xmlStatement, 'BillingPeriodStart', $strBillingPeriodStart);
		$this->_AddElement($xmlStatement, 'BillingPeriodEnd', $strBillingPeriodEnd);
		$this->_AddElement($xmlStatement, 'DueDate', date("j M y", strtotime($arrInvoice['DueOn'])));
		
		//--------------------------------------------------------------------//
		// Cost Centre Summary
		//--------------------------------------------------------------------//
		$arrCostCentres	= Array();
		foreach ($arrServices as $arrService)
		{
			// Is this Service in a Cost Centre?
			if ($arrService['CostCentre'] && $arrService['IsRendered'])
			{
				$arrCostCentres[$arrService['CostCentre']]['Services'][$arrService['Extension']]	= $arrService['ServiceTotal'];
				$arrCostCentres[$arrService['CostCentre']]['GrandTotal']							+= (float)$arrService['ServiceTotal'];
			}
		}
		
		$xmlCostCentreSummary	= $this->_AddElement($xmlInvoice, 'CostCentreSummary');
		foreach ($arrCostCentres as $strName=>$arrCostCentre)
		{
			// Get Cost Centre Services
			$xmlCostCentre	= $this->_AddElement($xmlCostCentreSummary, 'CostCentre');
			$this->_AddAttribute($xmlCostCentre, 'Name', $strName);
			$this->_AddAttribute($xmlCostCentre, 'Total', number_format($arrCostCentre['GrandTotal'], 2, '.', ''));
			
			foreach ($arrCostCentre['Services'] as $strFNN=>$fltServiceTotal)
			{
				$xmlService	= $this->_AddElement($xmlCostCentre, 'Service', number_format($fltServiceTotal, 2, '.', ''));
				$this->_AddAttribute($xmlService, 'FNN', $strFNN);
			}
		}
		
		//--------------------------------------------------------------------//
		// Services XML
		//--------------------------------------------------------------------//
		$xmlServices	= $this->_AddElement($xmlInvoice, 'Services');
		foreach ($arrServices as $arrService)
		{
			// Only Render if there is data or ForceInvoiceRender is set
			if (!$arrService['IsRendered'])
			{
				continue;
			}
			
			$xmlService	= $this->_AddElement($xmlServices, 'Service');
			$this->_AddAttribute($xmlService, 'FNN', ($arrService['Extension']) ? $arrService['Extension'] : $arrService['FNN']);
			$this->_AddAttribute($xmlService, 'CostCentre', $arrService['CostCentre']);
			$this->_AddAttribute($xmlService, 'Plan', $arrService['RatePlan']);
			$this->_AddAttribute($xmlService, 'GrandTotal', number_format($arrService['ServiceTotal'], 2, '.', ''));
			
			// Service Itemisation
			$xmlItemisation	= $this->_AddElement($xmlService, 'Itemisation');
			foreach ($arrService['RecordTypes'] as $strName=>$arrChargeType)
			{
				//Debug($arrService['RecordTypes']);
				
				$xmlItemisationType	= $this->_AddElement($xmlItemisation, 'Category');
				$this->_AddAttribute($xmlItemisationType, 'Name', $strName);
				$this->_AddAttribute($xmlItemisationType, 'GrandTotal', number_format($arrChargeType['TotalCharge'], 2, '.', ''));
				$this->_AddAttribute($xmlItemisationType, 'Records', count($arrChargeType['Itemisation']));
				$this->_AddAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrChargeType['DisplayType'], 'DisplayType'));
				
				// Charge Itemisation
				$xmlItemisationItems	= $this->_AddElement($xmlItemisationType, 'Items');
				foreach ($arrChargeType['Itemisation'] as $arrCDR)
				{
					$xmlItem	= $this->_AddElement($xmlItemisationItems, 'Item');
					
					// Process the CDR
					$arrItem	= $this->_CDR2Itemise($arrCDR, $arrChargeType['DisplayType']);
					
					// Item Fields
					foreach ($arrItem as $strField=>$mixValue)
					{
						$this->_AddElement($xmlItem, $strField, $mixValue);
					}
				}
			}
		}
		
		// Determine Output/Return data
		$strXMLOutput	= $this->_domDocument->saveXML();
		if (!$bolDebug)
		{
			// Save to file, return success
			$intAccount			= $arrInvoice['Account'];
			$intCustomerGroup	= $arrCustomer['CustomerGroup'];
			$strFullDirectory	= INVOICE_XML_PATH.$arrInvoice['InvoiceRun'];
			
			@mkdir($strFullDirectory, 0777, TRUE);
			
			$strFilename	= "$strFullDirectory/$intAccount.xml";
			$mixReturn		= (bool)file_put_contents($strFilename, $strXMLOutput);
		}
		else
		{
			// Return XML Data
			$mixReturn	= $strXMLOutput;
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
	 * @param		array		$arrAccounts	Indexed array of valid account numbers
	 * 											which have invoices in the InvoiceTemp table
	 * 											Only used with BILL_REPRINT_TEMP
	 * 
	 * @return		string						filename
	 *
	 * @method
	 */
 	function BuildOutput($strInvoiceRun, $arrAccounts = Array())
 	{
		// Get Invoice Detail
		$arrInvoices	= Array();
		if (!count($arrAccounts))
		{
			// Grab full list of Accounts
			$selAccounts	= new StatementSelect($this->_strInvoiceTable, "*", "InvoiceRun = <InvoiceRun>");
			$selAccounts->Execute();
			$arrInvoices	= $selAccounts->FetchAll();
		}
		else
		{
			// Grab specified Accounts
			$selAccountsById	= new StatementSelect($this->_strInvoiceTable, "*", "Account = <Account> AND InvoiceRun = <InvoiceRun>");
			foreach ($arrAccounts as $intAccount)
			{
				$selAccountsById->Execute(Array('Account' => $intAccount));
				if ($arrInvoice = $selAccountsById->Fetch())
				{
					$arrInvoices[]	= $arrInvoice;
				}
			}
		}		
		
		// Generate Output for each Account
		foreach ($arrInvoices as $arrInvoice)
		{
			$this->AddInvoice($arrInvoice);
		}
		
		// Return Pass/Fail
		return TRUE;
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
 	function SendOutput($strInvoiceRun)
 	{
		// Get list of CustomerGroups
		$selCustomerGroups	= new StatementSelect("CustomerGroup", "InternalName", "1");
		$selCustomerGroups->Execute();
		$arrCustomerGroups	= $selCustomerGroups->FetchAll();
		
		// Define Output Modes
		$arrOutputModes				= Array();
		$arrOutputModes['PRINT']['Archive']		= TRUE;
		$arrOutputModes['PRINT']['Delivery']	= 'SFTP';
		
		$arrOutputModes['EMAIL']['Archive']		= FALSE;
		$arrOutputModes['EMAIL']['Delivery']	= 'EmailAttachment';
		
		// Generate the PDFs
		$strCommandDir	= FLEX_BASE_PATH."lib/pdf/";
		$strXMLPath		= INVOICE_XML_PATH.$strInvoiceRun.'/';
		$intRunning	= 0;
		foreach ($arrOutputModes as $strMode=>&$arrOptions)
		{
			foreach ($arrCustomerGroups as $strName=>$arrCustomerGroup)
			{
				$strCustomerGroup	= str_replace(' ', '_', strtoupper($arrCustomerGroup['InternalName']));
				$strTARName			= str_replace(' ', '', strtolower($arrCustomerGroup['InternalName']))."-invoice-{$strInvoiceRun}.tar";
				$strTARPath			= ($arrOptions['Archive']) ? "-f ".$strXMLPath.$strTARName : "";
				$strCommand			= "cd {$strCommandDir}; php cli.php -c $strCustomerGroup -x $strXMLPath {$strTARPath} -m $strMode";
				$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['FilePath']			= $strTARPath;
				
				// Start the PDF generation process
				$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Pipes']			= Array();
				$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'][0]	= Array('pipe', 'r');
				$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'][1]	= Array('pipe', 'w');
				$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'][2]	= Array('pipe', 'w');
				if (!$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Process']	= proc_open($strCommand, $arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'], $arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Pipes']))
				{
					// There was an error starting the child process
					// TODO?
				}
				else
				{
					$intRunning++;
				}
			}
		}
		
		// Monitor PDF Generation Processes
		while ($intRunning)
		{
			foreach ($arrOutputModes as $strMode=>&$arrOptions)
			{
				foreach ($arrCustomerGroups as $strName=>$arrCustomerGroup)
				{
					// Is this Process still running?
					if ($arrCustomerGroup['Process'])
					{
						$arrStatus	= proc_get_status($arrCustomerGroup['Process']);
						if (!$arrStatus['running'])
						{
							// Close the process
							@pclose($arrCustomerGroup['Pipes'][0]);
							@pclose($arrCustomerGroup['Pipes'][1]);
							@pclose($arrCustomerGroup['Pipes'][2]);
							@proc_close($arrCustomerGroup['Process']);
							$intRunning--;
						}
					}
				}
			}
		}
		
		// Deliver the PDFs
		foreach ($arrOutputModes as $strMode=>&$arrOptions)
		{
			switch ($arrOptions['Delivery'])
			{
				case 'SFTP':
				case 'FTP':
					// Connect to the FTP Server
					if ($arrOptions['Delivery'] == 'SFTP')
					{
						$ptrConnection	= ftp_ssl_connect('ftp.salmat.com.au');
					}
					elseif ($arrOptions['Delivery'] == 'FTP')
					{
						$ptrConnection	= ftp_connect('ftp.salmat.com.au');
					}
					else
					{
						// WTF? This shouldn't happen
						// TODO
					}
					
					if ($ptrConnection)
					{
						// Log in to the FTP Server
						if (ftp_login($ptrConnection, 'yellowbilling', '9uA8;mGL'))
						{
							// Upload all necessary files
							foreach ($arrCustomerGroups as $strName=>$arrCustomerGroup)
							{
								if (ftp_put($ptrConnection, basename($arrCustomerGroup['FilePath']), $arrCustomerGroup['FilePath'], FTP_BINARY))
								{
									// Successfully Uploaded
									// TODO
								}
								else
								{
									// Could not upload the file
									// TODO
								}
							}
						}
						else
						{
							// Unable to log in
							// TODO
						}
					}
					else
					{
						// Unable to connect to SFTP server
						// TODO
					}
					break;
				
				case 'EmailAttachment':
					
					break;
			}
			
		}
		
		return TRUE;
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
 			$mixReturn	= $xmlParent->appendChild(new DOMElement($strName, EscapeXML($mixValue)));
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
 	
  	//------------------------------------------------------------------------//
	// _CDR2Itemise()
	//------------------------------------------------------------------------//
	/**
	 * _CDR2Itemise()
	 *
	 * Converts a CDR Record to an Itemised Item, based on it's DisplayType
	 *
	 * Converts a CDR Record to an Itemised Item, based on it's DisplayType
	 * 
	 * @param	element	&$xmlParent					The Parent DOMNode for this Attribute
	 * @param	array	$strName					The name of this Attribute
	 * @param	array	$mixValue					The value of this Attribute
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	protected function _CDR2Itemise($arrCDR, $intDisplayType)
 	{
 		$arrItem	= Array();
 		switch ($intDisplayType)
 		{
			case RECORD_DISPLAY_S_AND_E:
				//$arrItem['Date']			= date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Description']		= $arrCDR['Description'];
				$arrItem['Items']			= (int)$arrCDR['Units'];
				$arrItem['Charge']			= number_format($arrCDR['Charge'], 2, '.', '');
				break;
			
			case RECORD_DISPLAY_DATA:
				$arrItem['Date']			= date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Time']			= date("H:i:s", strtotime($arrCDR['StartDatetime']));
				$arrItem['CalledParty']		= $arrCDR['Destination'];
				$arrItem['Description']		= $arrCDR['Description'];
				$arrItem['KB']				= (int)$arrCDR['Units'];
				$arrItem['Charge']			= number_format($arrCDR['Charge'], 2, '.', '');
				break;
			
			case RECORD_DISPLAY_SMS:
				$arrItem['Date']			= date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Time']			= date("H:i:s", strtotime($arrCDR['StartDatetime']));
				$arrItem['CalledParty']		= $arrCDR['Destination'];
				$arrItem['Items']			= (int)$arrCDR['Units'];
				$arrItem['Description']		= $arrCDR['Description'];
				$arrItem['Charge']			= number_format($arrCDR['Charge'], 2, '.', '');
				break;
			
			case RECORD_DISPLAY_CALL:
			default:
				$intHours		= floor((int)$arrCDR['Units'] / 3600);
				$strDuration	= "$intHours:".date("i:s", (int)$arrCDR['Units']);
				
				$arrItem['Date']			= date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Time']			= date("H:i:s", strtotime($arrCDR['StartDatetime']));
				$arrItem['CalledParty']		= $arrCDR['Destination'];
				$arrItem['Description']		= $arrCDR['Description'];
				$arrItem['Duration']		= $strDuration;
				$arrItem['Charge']			= number_format($arrCDR['Charge'], 2, '.', '');
				break;
 		}
 		
 		return $arrItem;
 	}
 }

?>