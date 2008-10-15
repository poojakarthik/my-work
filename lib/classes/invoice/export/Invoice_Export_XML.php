<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Invoice_Export_XML
//----------------------------------------------------------------------------//
/**
 * Invoice_Export_XML
 *
 * Billing Module for Invoice Export to XML
 *
 * Billing Module for Invoice Export to XML
 *
 * @file		Invoice_Export_XML.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste' Davis
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Invoice_Export_XML
//----------------------------------------------------------------------------//
/**
 * Invoice_Export_XML
 *
 * Billing Module for Invoice Export to XML
 *
 * Billing Module for Invoice Export to XML
 *
 * @prefix		bil
 *
 * @package		billing
 * @class		Invoice_Export_XML
 */
 class Invoice_Export_XML
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for Invoice_Export_XML
	 *
	 * Constructor method for Invoice_Export_XML
	 *
	 * @method
	 */
 	function __construct()
 	{
		// Call Parent Constructor
		parent::__construct();
 	}
 	
 	//------------------------------------------------------------------------//
	// export()
	//------------------------------------------------------------------------//
	/**
	 * export()
	 *
	 * Exports an Invoice to XML
	 *
	 * Exports an Invoice to XML
	 * 
	 * @param		array		$arrInvoice							Associative array of details for this Invoice
	 * @param		boolean		$bolDebug				[optional]	TRUE	: Doesn't write to file, returns XML data
	 * 																FALSE	: Writes to file, returns boolean (default)
	 *
	 * @return		mixed
	 *
	 * @method
	 */
 	static public function export($arrInvoice, $bolDebug = FALSE)
 	{
		// Init Output Array
		$arrOutputData	= Array();
		
		// Get Customer Information
		$arrCustomer	= Invoice_Export::getCustomerData($arrInvoice);
		
		// Init our XML Document
		$domDocument				= new DOMDocument('1.0'); 
		$domDocument->formatOutput	= TRUE;
		
		//--------------------------------------------------------------------//
		// Service (Data retrieval only)
		//--------------------------------------------------------------------//
		$arrServices	= Invoice_Export::getServices($arrInvoice);
		
		//--------------------------------------------------------------------//
		// Document Object
		//--------------------------------------------------------------------//
		$xmlDocument	= self::_addElement($domDocument, 'Document');
		self::_addElement($xmlDocument, 'DocumentType', 'DOCUMENT_TYPE_INVOICE');
		self::_addElement($xmlDocument, 'CustomerGroup', GetConstantName($arrCustomer['CustomerGroup'], 'CustomerGroup'));
		self::_addElement($xmlDocument, 'CreationDate', date('Y-m-d H:i:s', strtotime($arrInvoice['CreatedOn'])));
		self::_addElement($xmlDocument, 'DeliveryMethod', GetConstantName($arrInvoice['DeliveryMethod'], 'DeliveryMethod'));
		self::_addAttribute($xmlDocument, 'DateIssued', date('j M y', strtotime($arrInvoice['CreatedOn'])));
		
		//--------------------------------------------------------------------//
		// Invoice Object
		//--------------------------------------------------------------------//
		$xmlInvoice	= self::_addElement($xmlDocument, 'Invoice');
		self::_addAttribute($xmlInvoice, 'Id', $arrInvoice['Id']);
		//self::_addAttribute($xmlInvoice, 'DeliveryMethod'	, GetConstantName($arrInvoice['DeliveryMethod'], 'BillingMethod'));
		
		//--------------------------------------------------------------------//
		// Currency Symbol (at the moment, we always use AUD, so $)
		//--------------------------------------------------------------------//
		$xmlCurrency	= self::_addElement($xmlInvoice, 'Currency');
		$xmlSymbol		= self::_addElement($xmlCurrency, 'Symbol', '$');
		self::_addAttribute($xmlSymbol, 'Location', 'Prefix');
		$xmlNegative	= self::_addElement($xmlCurrency, 'Negative', 'CR');
		self::_addAttribute($xmlNegative, 'Location', 'Suffix');
		
		//--------------------------------------------------------------------//
		// Account Information
		//--------------------------------------------------------------------//
		$xmlAccount	= self::_addElement($xmlInvoice, 'Account');
		self::_addAttribute($xmlAccount, 'Id', $arrInvoice['Account']);
		self::_addAttribute($xmlAccount, 'Name', $arrCustomer['BusinessName']);
		self::_addAttribute($xmlAccount, 'CustomerGroup', GetConstantName($arrCustomer['CustomerGroup'], 'CustomerGroup'));
		self::_addAttribute($xmlAccount, 'NewCustomer', ($arrCustomer['InvoiceCount'] > 0) ? 0 : 1);
		self::_addElement($xmlAccount, 'Addressee', $arrCustomer['BusinessName']);
		self::_addElement($xmlAccount, 'AddressLine1', $arrCustomer['Address1']);
		self::_addElement($xmlAccount, 'AddressLine2', $arrCustomer['Address2']);
		self::_addElement($xmlAccount, 'Suburb', $arrCustomer['Suburb']);
		self::_addElement($xmlAccount, 'Postcode', $arrCustomer['Postcode']);
		self::_addElement($xmlAccount, 'State', $arrCustomer['State']);
		
		//--------------------------------------------------------------------//
		// Account Summary & Itemisation
		//--------------------------------------------------------------------//
		$arrAccountCategories	= Invoice_Export::getAccountCharges($arrInvoice);
		$xmlItemisation	= self::_addElement($xmlInvoice, 'Charges');
		
		// Charge Itemisation
		foreach ($arrAccountCategories as $strName=>$arrCategory)
		{
			$xmlItemisationType	= self::_addElement($xmlItemisation, 'Category');
			self::_addAttribute($xmlItemisationType, 'Name', $strName);
			self::_addAttribute($xmlItemisationType, 'GrandTotal', number_format($arrCategory['TotalCharge'], 2, '.', ''));
			self::_addAttribute($xmlItemisationType, 'Records', @count($arrCategory['Itemisation']));
			self::_addAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrCategory['DisplayType'], 'DisplayType'));
			
			$xmlItemisationItems	= self::_addElement($xmlItemisationType, 'Items');
			
			if ($arrCategory['Itemisation'])
			{
				foreach ($arrCategory['Itemisation'] as $arrCDR)
				{
					$xmlItem	= self::_addElement($xmlItemisationItems, 'Item');
					
					// Process the CDR
					$arrItem	= self::_itemiseCDR($arrCDR, $arrCategory['DisplayType']);
					
					// Item Fields
					foreach ($arrItem as $strField=>$mixValue)
					{
						self::_addElement($xmlItem, $strField, $mixValue);
					}
				}
			}
		}
		
		
		//--------------------------------------------------------------------//
		// Payment Information
		//--------------------------------------------------------------------//
		$xmlPayment		= self::_addElement($xmlInvoice, 'PaymentDetails');
		$xmlBPay		= self::_addElement($xmlPayment, 'BPay');
		self::_addElement($xmlBPay, 'CustomerReference', $arrInvoice['Account'].MakeLuhn($arrInvoice['Account']));
		$xmlBillExpress	= self::_addElement($xmlPayment, 'BillExpress');
		self::_addElement($xmlBillExpress, 'CustomerReference', $arrInvoice['Account'].MakeLuhn($arrInvoice['Account']));		// FIXME
		
		self::_addAttribute($xmlPayment, 'DirectDebit', (in_array($arrCustomer['BillingType'], Array(BILLING_TYPE_CREDIT_CARD, BILLING_TYPE_DIRECT_DEBIT)) ? 1 : 0));
		
		//--------------------------------------------------------------------//
		// Statement
		//--------------------------------------------------------------------//
		// HACKHACKHACK: These dates work off the "Bill every month on the 1st" premise
		$intBillingDate			= strtotime($arrInvoice['CreatedOn']);
		$strBillingPeriodStart	= date("j M y", strtotime("-1 month", $intBillingDate));
		$strBillingPeriodEnd	= date("j M y", strtotime("-1 day", $intBillingDate));
		
		// Add to XML schema
		$arrLastInvoice	= Invoice_Export::getOldInvoice($arrInvoice, 1);
		$xmlStatement	= self::_addElement($xmlInvoice, 'Statement');
		self::_addElement($xmlStatement, 'OpeningBalance', number_format($arrLastInvoice['TotalOwing'], 2, '.', ''));
		self::_addElement($xmlStatement, 'Payments', number_format(max($arrLastInvoice['TotalOwing'] - $arrInvoice['AccountBalance'], 0.0), 2, '.', ''));
		self::_addElement($xmlStatement, 'Overdue', number_format($arrInvoice['AccountBalance'], 2, '.', ''));
		self::_addElement($xmlStatement, 'NewCharges', number_format($arrInvoice['Total'] + $arrInvoice['Tax'], 2, '.', ''));
		self::_addElement($xmlStatement, 'TotalOwing', number_format($arrInvoice['TotalOwing'], 2, '.', ''));
		self::_addElement($xmlStatement, 'BillingPeriodStart', $strBillingPeriodStart);
		self::_addElement($xmlStatement, 'BillingPeriodEnd', $strBillingPeriodEnd);
		self::_addElement($xmlStatement, 'DueDate', date("j M y", strtotime($arrInvoice['DueOn'])));
		
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
		
		$xmlCostCentreSummary	= self::_addElement($xmlInvoice, 'CostCentreSummary');
		foreach ($arrCostCentres as $strName=>$arrCostCentre)
		{
			// Get Cost Centre Services
			$xmlCostCentre	= self::_addElement($xmlCostCentreSummary, 'CostCentre');
			self::_addAttribute($xmlCostCentre, 'Name', $strName);
			self::_addAttribute($xmlCostCentre, 'Total', number_format($arrCostCentre['GrandTotal'], 2, '.', ''));
			
			foreach ($arrCostCentre['Services'] as $strFNN=>$fltServiceTotal)
			{
				$xmlService	= self::_addElement($xmlCostCentre, 'Service', number_format($fltServiceTotal, 2, '.', ''));
				self::_addAttribute($xmlService, 'FNN', $strFNN);
			}
		}
		
		//--------------------------------------------------------------------//
		// Services XML
		//--------------------------------------------------------------------//
		$arrDebugCallTypes	= Array();
		$xmlServices	= self::_addElement($xmlInvoice, 'Services');
		foreach ($arrServices as $arrService)
		{
			//Cli_App_Billing::debug("XML for {$arrService['FNN']}::{$arrService['Extension']}: ", FALSE);
			
			// Only Render if there is data or ForceInvoiceRender is set
			if (!$arrService['IsRendered'])
			{
				//Cli_App_Billing::debug("NOT RENDERING!");
				//Cli_App_Billing::debug($arrService);
				continue;
			}
			//Cli_App_Billing::debug("Rendering...");
			
			$xmlService	= self::_addElement($xmlServices, 'Service');
			self::_addAttribute($xmlService, 'FNN', ($arrService['Extension']) ? $arrService['Extension'] : $arrService['FNN']);
			self::_addAttribute($xmlService, 'CostCentre', $arrService['CostCentre']);
			self::_addAttribute($xmlService, 'Plan', $arrService['RatePlan']);
			self::_addAttribute($xmlService, 'GrandTotal', number_format($arrService['ServiceTotal'], 2, '.', ''));
			
			// Service Itemisation
			$xmlItemisation	= self::_addElement($xmlService, 'Itemisation');
			foreach ($arrService['RecordTypes'] as $strName=>$arrChargeType)
			{
				//Debug($arrService['RecordTypes']);
				
				$xmlItemisationType	= self::_addElement($xmlItemisation, 'Category');
				self::_addAttribute($xmlItemisationType, 'Name', $strName);
				self::_addAttribute($xmlItemisationType, 'GrandTotal', number_format($arrChargeType['TotalCharge'], 2, '.', ''));
				self::_addAttribute($xmlItemisationType, 'Records', count($arrChargeType['Itemisation']));
				self::_addAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrChargeType['DisplayType'], 'DisplayType'));
				
				// Charge Itemisation
				$xmlItemisationItems	= self::_addElement($xmlItemisationType, 'Items');
				foreach ($arrChargeType['Itemisation'] as $arrCDR)
				{
					$xmlItem	= self::_addElement($xmlItemisationItems, 'Item');
					
					// Process the CDR
					$arrItem	= self::_itemiseCDR($arrCDR, $arrChargeType['DisplayType']);
					
					// Item Fields
					foreach ($arrItem as $strField=>$mixValue)
					{
						self::_addElement($xmlItem, $strField, $mixValue);
					}
					
					// Debug SUM of CDR values
					$arrDebugCallTypes[$strName][$arrService['FNN']]	+= (float)$arrItem['Charge'];
					$arrDebugCallTypes[$strName]['**Total']				+= (float)$arrItem['Charge'];
				}
			}
		}
		//Cli_App_Billing::debug("Call Types:");
		//Cli_App_Billing::debug($arrDebugCallTypes);
		
		// Determine Output/Return data
		$strXMLOutput	= $domDocument->saveXML();
		if (!$bolDebug)
		{
			// Save to file, return success
			$intAccount			= $arrInvoice['Account'];
			$intCustomerGroup	= $arrCustomer['CustomerGroup'];
			$strFullDirectory	= FILES_BASE_PATH.'invoices/xml/'.$arrInvoice['invoice_run_id'];
			
			if (!file_exists($strFullDirectory))
			{
				mkdir($strFullDirectory, 0777, TRUE);
			}
			
			$strFilename	= "{$strFullDirectory}/{$intAccount}.xml";
			$mixReturn		= (bool)file_put_contents($strFilename, $strXMLOutput);
			
			if ($mixReturn)
			{
				Cli_App_Billing::debug("Successully exported to '{$strFilename}'");
			}
			else
			{
				Cli_App_Billing::debug("Unsuccessfully attempted to export to '{$strFilename}'");
			}
		}
		else
		{
			// Return XML Data
			$mixReturn	= $strXMLOutput;
		}
		
		// Destroy XML object and return
		unset($domDocument);
		return $mixReturn;
	 }
 	
 	//------------------------------------------------------------------------//
	// deliver()
	//------------------------------------------------------------------------//
	/**
	 * deliver()
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
 	static public function deliver($strInvoiceRun, $arrModes = NULL, $bolGeneratePDFs = TRUE)
 	{
		throw new Exception(__CLASS__."::deliver() has not been implemented yet!");
		
		// Get list of CustomerGroups
		$selCustomerGroups	= new StatementSelect("CustomerGroup", "InternalName", "1");
		$selCustomerGroups->Execute();
		$arrCustomerGroups	= $selCustomerGroups->FetchAll();
		
		// Define Output Modes
		$arrOutputModes				= Array();
		$arrSupportedModes			= Array();
		
		$arrSupportedModes['PRINT']['Archive']	= TRUE;
		$arrSupportedModes['PRINT']['Delivery']	= 'SFTP';
		
		$arrSupportedModes['EMAIL']['Archive']	= FALSE;
		$arrSupportedModes['EMAIL']['Delivery']	= 'EmailAttachment';
		
		if (is_array($arrModes))
		{
			foreach ($arrModes as $strMode)
			{
				$arrOutputModes[$strMode]	= $arrSupportedModes[$strMode];
			}
		}
		
		CliEcho("Delivering for InvoiceRun '$strInvoiceRun'...");
		
		// Generate the PDFs
		$strCommandDir	= FLEX_BASE_PATH."cli/";
		$strXMLPath		= INVOICE_XML_PATH.$strInvoiceRun.'/';
		$strPDFPath		= FILES_BASE_PATH."invoices/pdf/".$strInvoiceRun.'/';
		$intRunning		= 0;
		if ($bolGeneratePDFs)
		{
			foreach ($arrOutputModes as $strMode=>&$arrOptions)
			{
				foreach ($arrCustomerGroups as $strName=>&$arrCustomerGroup)
				{
					$strCustomerGroup	= str_replace(' ', '_', strtoupper($arrCustomerGroup['InternalName']));
					$strTARName			= str_replace(' ', '', strtolower($arrCustomerGroup['InternalName']))."-invoice-{$strInvoiceRun}.tar";
					$strTARPath			= ($arrOptions['Archive']) ? "-f ".$strXMLPath.$strTARName : "";
					$strCommand			= "cd {$strCommandDir}; php pdf.php -c $strCustomerGroup -x $strXMLPath {$strTARPath} -m $strMode";
					$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['FilePath']			= $strTARPath;
					
					// Start the PDF generation process
					$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Pipes']			= Array();
					$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'][0]	= Array('pipe', 'r');
					$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'][1]	= Array('pipe', 'w');
					$arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'][2]	= Array('pipe', 'w');
					if (!($arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Process']	= proc_open($strCommand, $arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Descriptor'], $arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Pipes'])))
					{
						// There was an error starting the child process
						CliEcho("Unable to start child process ('$strCommand') for $strName::$strMode!");
					}
					else
					{
						$arrCustomerGroup['StartDatetime']	= time();
						$arrStatus							= proc_get_status($arrCustomerGroup['Process']);
						CliEcho("{$arrCustomerGroup['InternalName']}::$strMode process started successfully with PID {$arrStatus['pid']}!");
						stream_set_blocking($arrOptions['CustomerGroup'][$arrCustomerGroup['InternalName']]['Pipes'][1], 0);
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
								$intTotalTime	= time() - $arrCustomerGroup['StartDatetime'];
								CliEcho("{$arrCustomerGroup['InternalName']}::$strMode process has completed in $intTotalTime seconds");
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
						CliEcho("Unexpected Delivery Mode '{$arrOptions['Delivery']}'!");
						break;
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
									CliEcho("Archive was successfully uploaded to the Salmat Server");
								}
								else
								{
									// Could not upload the file
									CliEcho("Unable to upload the Archive to the Salmat Server");
								}
							}
						}
						else
						{
							// Unable to log in
							CliEcho("Unable to log in to the Salmat Server with provided credentials");
						}
					}
					else
					{
						// Unable to connect to SFTP server
						CliEcho("Unable to establish a connection with the Salmat Server");
					}
					break;
				
				case 'EmailAttachment':
					// Get list of PDFs
					$arrPDFs	= glob($strPDFPath."*.pdf");
					
					// Email Content Template
		 			$strContentTemplate	=	"Please find attached your most recent Invoice from <CustomerGroup>\n\n" .
 											"Regards\n" .
 											"The Team at <CustomerGroup>";
 					
 					// Get Billing Period
 					$strBillingPeriod	= date("F Y", strtotime("-1 month", strtotime($strInvoiceRun)));
					
					foreach ($arrPDFs as $strPDF)
					{
						// Get Account Number from the filename
						$arrPDFSplit	= explode('.', basename($strPDF));
						$intAccount		= (int)$arrPDFSplit[0];
						//CliEcho(basename($strPDF)." >> ".$intAccount);
						
						// Is this Invoice set to be Emailed?
						$selAccountEmail	= new StatementSelect(	"((Invoice JOIN Account ON Invoice.Account = Account.Id) JOIN Contact ON Contact.Account = Account.Id) JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id",
																	"Invoice.Id AS InvoiceNumber, Invoice.Account, CustomerGroup.ExternalName, CustomerGroup.OutboundEmail, Email, FirstName",
																	"Email != '' AND Contact.Archived = 0 AND InvoiceRun = <InvoiceRun> AND Invoice.Account = <Account> AND Invoice.DeliveryMethod = ".DELIVERY_METHOD_EMAIL);
						$updDeliveryMethod	= new StatementUpdate("Invoice", "InvoiceRun = <InvoiceRun> AND Account = <Account>", Array('DeliveryMethod' => NULL));
						
						if ($selAccountEmail->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun)) === FALSE)
			 			{
			 				Debug($selAccountEmail->Error());
			 				return FALSE;
			 			}
			 			if (!$arrDetails = $selAccountEmail->FetchAll())
			 			{
			 				// Bad Account Number or Non-Email Account
			 				continue;
			 			}
			 			
				 		CliEcho("\n\t+ Emailing Invoice(s) for Account #$intAccount...");
			 			
			 			// for each email-able contact
			 			foreach ($arrDetails as $arrDetail)
			 			{
				 			// Set email headers
				 			$arrHeaders = Array	(
				 									'From'		=> $arrDetail['OutboundEmail'],
				 									'Subject'	=> "Telephone Billing for $strBillingPeriod"
				 								);
		 					$strContent	=	str_replace('<CustomerGroup>', $arrDetail['ExternalName'], $strContentTemplate);
					 		
					 		// Does the customer have a first name?
					 		if (trim($arrDetail['FirstName']))
					 		{
					 			$strContent = "Dear ".$arrDetail['FirstName']."\n\n" . $strContent;
					 		}
					 		
				 			// Account for , separated email addresses
				 			$arrEmails = explode(',', $arrDetail['Email']);
				 			foreach ($arrEmails as $strEmail)
				 			{
					 			$strEmail = trim($strEmail);
					 			
					 			CliEcho(str_pad("\t\tAddress: '$strEmail'...", 70, " ", STR_PAD_RIGHT), FALSE);
					 			
					 			// Validate email address
					 			if (!preg_match('/^([[:alnum:]]([+-_.]?[[:alnum:]])*)@([[:alnum:]]([.]?[-[:alnum:]])*[[:alnum:]])\.([[:alpha:]]){2,25}$/', $strEmail))
					 			{
					 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Email address is invalid");
					 				continue;
					 			}
					 			
					 			$mimMime	= new Mail_mime("\n");
					 			$mimMime->setTXTBody($strContent);
					 			$mimMime->addAttachment(file_get_contents($strPDF), 'application/pdf', "{$intAccount}_{$arrDetail['InvoiceNumber']}.pdf", FALSE);
								$strBody	= $mimMime->get();
								$strHeaders	= $mimMime->headers($arrHeaders);
					 			$emlMail	= &Mail::factory('mail');
					 			
					 			// Uncomment this to Debug
					 			//$strEmail = 'rich@voiptelsystems.com.au, joel@voiptelsystems.com.au, turdminator@hotmail.com, rj.davis@student.qut.edu.au, jmdawkins@optusnet.com.au, n2333511@student.qut.edu.au, holiver@yellowbilling.com.au';
					 			
					 			// Send the email
					 			if (!$emlMail->send($strEmail, $strHeaders, $strBody))
					 			{
					 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
					 				continue;
					 			}
								else
								{
						 			// Uncomment this to Debug
						 			//die;
					 			
									// Update DeliveryMethod
									$arrUpdateData						= Array();
									$arrUpdateData['DeliveryMethod']	= BILLING_METHOD_EMAIL_SENT;
									$arrWhere				= Array();
									$arrWhere['InvoiceRun']	= $strInvoiceRun;
									$arrWhere['Account']	= $arrDetail['Account'];
									if ($updDeliveryMethod->Execute($arrUpdateData, $arrWhere))
									{
										//Debug("Success!");
									}
									else
									{
										//Debug("Failure!");
									}
									
				 					CliEcho("[   OK   ]");
								}
				 			}
			 			}
					}
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
	 * Adds an Element to the $domDocument XML Schema
	 *
	 * Adds an Element to the $domDocument XML Schema
	 * 
	 * @param	element	&$xmlParent					The Parent DOMNode for this Element
	 * @param	array	$strName					The name of this Element
	 * @param	array	$mixValue		[optional]	The value of this Element
	 *
	 * @return	mixed-+
	 *
	 * @method
	 */
 	protected static function _addElement(&$xmlParent, $strName, $mixValue = NULL)
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
 	protected static function _addAttribute(&$xmlParent, $strName, $mixValue = NULL)
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
	// _itemiseCDR()
	//------------------------------------------------------------------------//
	/**
	 * _itemiseCDR()
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
 	protected static function _itemiseCDR($arrCDR, $intDisplayType)
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
				$arrItem['Data']			= (int)$arrCDR['Units'];
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
 		
 		$arrItem['TaxExempt']	= $arrCDR['TaxExempt'];
 		return $arrItem;
 	}
 }

?>