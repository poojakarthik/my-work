<?php
class Invoice_Export_XML {
	const DEFAULT_CUSTOMER_NAME = 'Customer';
	
	function __construct() {
		// Call Parent Constructor
		parent::__construct();
	}
	
	static public function export($arrInvoice, $bolDebug=false) {
		// Init Output Array
		$arrOutputData = array();
		
		// Get Customer Information
		$arrCustomer = Invoice_Export::getCustomerData($arrInvoice);
		
		// Init our XML Document
		$domDocument = new DOMDocument('1.0');
		$domDocument->formatOutput = true;
		
		//--------------------------------------------------------------------//
		// Service (Data retrieval only)
		//--------------------------------------------------------------------//
		$arrServices = Invoice_Export::getServices($arrInvoice);
		
		//--------------------------------------------------------------------//
		// Document Object
		//--------------------------------------------------------------------//
		$strCustomerGroupConstant = 'CUSTOMER_GROUP_'.strtoupper(str_replace(' ', '_', Customer_Group::getForId($arrCustomer['CustomerGroup'])->internal_name));
		$xmlDocument = self::_addElement($domDocument, 'Document');
		self::_addElement($xmlDocument, 'DocumentType', 'DOCUMENT_TYPE_INVOICE');
		self::_addElement($xmlDocument, 'CustomerGroup', $strCustomerGroupConstant);
		self::_addElement($xmlDocument, 'CreationDate', date('Y-m-d H:i:s', strtotime($arrInvoice['CreatedOn'])));
		self::_addElement($xmlDocument, 'DeliveryMethod', GetConstantName($arrInvoice['DeliveryMethod'], 'delivery_method'));
		self::_addAttribute($xmlDocument, 'DateIssued', date('j M y', strtotime($arrInvoice['CreatedOn'])));
		
		//--------------------------------------------------------------------//
		// Invoice Object
		//--------------------------------------------------------------------//
		$xmlInvoice = self::_addElement($xmlDocument, 'Invoice');
		self::_addAttribute($xmlInvoice, 'Id', $arrInvoice['Id']);
		$oInvoiceRun = Invoice_Run::getForId($arrInvoice['invoice_run_id']);
		self::_addAttribute($xmlInvoice, 'InvoiceRunType', Constant_Group::getConstantGroup('invoice_run_type')->getConstantAlias($oInvoiceRun->invoice_run_type_id));
		
		//--------------------------------------------------------------------//
		// Currency Symbol (at the moment, we always use AUD, so $)
		//--------------------------------------------------------------------//
		$xmlCurrency = self::_addElement($xmlInvoice, 'Currency');
		$xmlSymbol = self::_addElement($xmlCurrency, 'Symbol', '$');
		self::_addAttribute($xmlSymbol, 'Location', 'Prefix');
		$xmlNegative = self::_addElement($xmlCurrency, 'Negative', 'CR');
		self::_addAttribute($xmlNegative, 'Location', 'Suffix');
		
		//--------------------------------------------------------------------//
		// Rate Classes
		//--------------------------------------------------------------------//
		$aRateClasses = Invoice_Export::getRateClasses();
		
		$xmlRateClasses = self::_addElement($xmlInvoice, 'RateClasses');
		foreach ($aRateClasses as $oRateClass) {
			$xmlRateClass = self::_addElement($xmlRateClasses, 'RateClass');
			self::_addAttribute($xmlRateClass, 'Id', $oRateClass->id);
			self::_addElement($xmlRateClass, 'Name', $oRateClass->name);
			self::_addElement($xmlRateClass, 'Code', $oRateClass->invoice_code);
		}
		
		//--------------------------------------------------------------------//
		// Account Information
		//--------------------------------------------------------------------//
		$xmlAccount = self::_addElement($xmlInvoice, 'Account');
		self::_addAttribute($xmlAccount, 'Id', $arrInvoice['Account']);
		self::_addAttribute($xmlAccount, 'Name', $arrCustomer['BusinessName']);
		self::_addAttribute($xmlAccount, 'CustomerGroup', GetConstantName($arrCustomer['CustomerGroup'], 'CustomerGroup'));
		self::_addAttribute($xmlAccount, 'NewCustomer', ($arrCustomer['InvoiceCount'] > 0) ? 0 : 1);
		self::_addElement($xmlAccount, 'Addressee', $arrCustomer['BusinessName']);
		self::_addElement($xmlAccount, 'AddressLine1', $arrCustomer['Address1']);
		self::_addElement($xmlAccount, 'AddressLine2', $arrCustomer['Address2']);
		self::_addElement($xmlAccount, 'Suburb', strtoupper($arrCustomer['Suburb']));
		self::_addElement($xmlAccount, 'Postcode', strtoupper($arrCustomer['Postcode']));
		self::_addElement($xmlAccount, 'State', strtoupper($arrCustomer['State']));
		$oPrimaryContact = Contact::getForId(Account::getForId($arrInvoice['Account'])->PrimaryContact);
		$sContactPerson = ($oPrimaryContact) ? $oPrimaryContact->title." ".$oPrimaryContact->getName() : self::DEFAULT_CUSTOMER_NAME;
		self::_addElement($xmlAccount, 'ContactPerson', $sContactPerson);
		
		//--------------------------------------------------------------------//
		// Adjustments
		//--------------------------------------------------------------------//
		$aAdjustments = Invoice_Export::getAccountAdjustments($arrInvoice);
		$xmlAdjustments = self::_addElement($xmlInvoice, 'Adjustments');
		
		foreach ($aAdjustments['Itemisation'] as $aItem) {
			$xmlItem = self::_addElement($xmlAdjustments, 'Item');
			self::_addElement($xmlItem, 'Description', $aItem['Description']);
			self::_addElement($xmlItem, 'Items', $aItem['Units']);
			self::_addElement($xmlItem, 'Charge', number_format($aItem['Charge'], 2, '.', ''));
			
			$arrItem = self::_itemiseCDR($aItem, RECORD_DISPLAY_S_AND_E);
			foreach ($arrItem as $strField=>$mixValue) {
				self::_addElement($xmlItem, $strField, $mixValue);
			}
		}
		
		$xmlAdjustmentTax = self::_addElement($xmlAdjustments, 'Item');
		self::_addElement($xmlAdjustmentTax, 'Description', 'GST Total');
		self::_addElement($xmlAdjustmentTax, 'Charge', number_format($aAdjustments['TaxComponent'], 2, '.', ''));
		
		//--------------------------------------------------------------------//
		// Account Summary & Itemisation
		//--------------------------------------------------------------------//
		$arrAccountCategories = Invoice_Export::getAccountSummary($arrInvoice);
		$xmlItemisation = self::_addElement($xmlInvoice, 'Charges');
		
		// Charge Itemisation
		foreach ($arrAccountCategories as $strName=>$arrCategory) {
			$xmlItemisationType = self::_addElement($xmlItemisation, 'Category');
			self::_addAttribute($xmlItemisationType, 'Name', $strName);
			self::_addAttribute($xmlItemisationType, 'GrandTotal', number_format($arrCategory['TotalCharge'], 2, '.', ''));
			self::_addAttribute($xmlItemisationType, 'Records', @count($arrCategory['Itemisation']));
			self::_addAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrCategory['DisplayType'], 'DisplayType'));
			
			$xmlItemisationItems = self::_addElement($xmlItemisationType, 'Items');
			if ($arrCategory['Itemisation']) {
				foreach ($arrCategory['Itemisation'] as $arrCDR) {
					$xmlItem = self::_addElement($xmlItemisationItems, 'Item');
					
					// Process the CDR
					$arrItem = self::_itemiseCDR($arrCDR, $arrCategory['DisplayType']);
					
					// Item Fields
					foreach ($arrItem as $strField=>$mixValue) {
						self::_addElement($xmlItem, $strField, $mixValue);
					}
				}
			}
		}
		
		//--------------------------------------------------------------------//
		// Payment Information
		//--------------------------------------------------------------------//
		$xmlPayment = self::_addElement($xmlInvoice, 'PaymentDetails');
		$xmlBPay = self::_addElement($xmlPayment, 'BPay');
		self::_addElement($xmlBPay, 'CustomerReference', $arrInvoice['Account'].MakeLuhn($arrInvoice['Account']));
		$xmlBillExpress = self::_addElement($xmlPayment, 'BillExpress');
		self::_addElement($xmlBillExpress, 'CustomerReference', $arrInvoice['Account'].MakeLuhn($arrInvoice['Account'])); // FIXME
		
		self::_addAttribute($xmlPayment, 'DirectDebit', (in_array($arrCustomer['BillingType'], array(BILLING_TYPE_CREDIT_CARD, BILLING_TYPE_DIRECT_DEBIT)) ? 1 : 0));
		
		$xmlPaymentMethod = self::_addElement($xmlPayment, 'PaymentMethod');
		self::_addAttribute($xmlPaymentMethod, 'Method', Payment_Method::getForId(Billing_Type::getForId((int)$arrCustomer['BillingType'])->payment_method_id)->const_name);
		
		$oAccount = Account::getForId($arrInvoice['Account']);
		$oPaymentMethodDetails = $oAccount->getPaymentMethodDetails();
		if (is_object($oPaymentMethodDetails)) {
			$xmlPaymentMethodDetails = self::_addElement($xmlPaymentMethod, 'Details');
			
			$sClassName = get_class((method_exists($oPaymentMethodDetails, 'getDetails')) ? $oPaymentMethodDetails->getDetails() : $oPaymentMethodDetails);
			self::_addAttribute($xmlPaymentMethodDetails, 'Type', $sClassName);
		}
		
		//--------------------------------------------------------------------//
		// Statement
		//--------------------------------------------------------------------//
		$intBillingDate = strtotime($arrInvoice['CreatedOn']);
		$strBillingPeriodStart = date("j M y", strtotime($arrInvoice['billing_period_start_datetime']));
		$strBillingPeriodEnd = date("j M y", strtotime($arrInvoice['billing_period_end_datetime']));
		
		// Add to XML schema
		$arrLastInvoice = Invoice_Export::getOldInvoice($arrInvoice, 1);
		$xmlStatement = self::_addElement($xmlInvoice, 'Statement');
		self::_addElement($xmlStatement, 'OpeningBalance', number_format(Invoice::roundOut($arrLastInvoice['TotalOwing'], 2), 2, '.', ''));
		self::_addElement($xmlStatement, 'Payments', number_format(max(Invoice::roundOut($arrLastInvoice['TotalOwing'], 2) - Invoice::roundOut($arrInvoice['AccountBalance'], 2), 0.0), 2, '.', ''));
		self::_addElement($xmlStatement, 'Adjustments', number_format($arrInvoice['adjustment_total'] + $arrInvoice['adjustment_tax'], 2, '.', ''));
		self::_addElement($xmlStatement, 'OutstandingBalance', number_format($arrInvoice['AccountBalance'] + $arrInvoice['adjustment_total'] + $arrInvoice['adjustment_tax'], 2, '.', ''));
		self::_addElement($xmlStatement, 'OverdueBalance', number_format($arrCustomer['OverdueBalance'] + $arrInvoice['adjustment_total'] + $arrInvoice['adjustment_tax'], 2, '.', ''));
		self::_addElement($xmlStatement, 'NewCharges', number_format($arrInvoice['charge_total'] + $arrInvoice['charge_tax'], 2, '.', ''));
		self::_addElement($xmlStatement, 'InvoiceTotal', number_format($arrInvoice['Total'] + $arrInvoice['Tax'], 2, '.', ''));
		self::_addElement($xmlStatement, 'TotalOwing', number_format(Invoice::roundOut($arrInvoice['TotalOwing'], 2), 2, '.', ''));
		self::_addElement($xmlStatement, 'BillingPeriodStart', $strBillingPeriodStart);
		self::_addElement($xmlStatement, 'BillingPeriodEnd', $strBillingPeriodEnd);
		self::_addElement($xmlStatement, 'DueDate', date("j M y", strtotime($arrInvoice['DueOn'])));
		
		//--------------------------------------------------------------------//
		// Calculated Statement Info
		//--------------------------------------------------------------------//
		$oAccount = Account::getForId($arrInvoice['Account']);
		$fOpeningBalance = Rate::roundToCurrencyStandard(Invoice_Export::getOpeningBalance($arrInvoice));
		$fPaymentTotal = Rate::roundToCurrencyStandard(Invoice_Export::getPaymentTotal($arrInvoice));
		$fAdjustmentTotal = Rate::roundToCurrencyStandard($aAdjustments['TotalCharge'] + $aAdjustments['TaxComponent']);
		$fNewCharges = Rate::roundToCurrencyStandard($arrInvoice['charge_total'] + $arrInvoice['charge_tax']);
		$fTotalOwing = Rate::roundToCurrencyStandard($fOpeningBalance + $fPaymentTotal + $fAdjustmentTotal + $fNewCharges);
		$fTotalOverdue = Rate::roundToCurrencyStandard(Invoice_Export::getTotalOverdue($arrInvoice));
		$fBalanceBroughtForward = Rate::roundToCurrencyStandard($fOpeningBalance + $fPaymentTotal + $fAdjustmentTotal);
		
		$xmlCalculatedStatement = self::_addElement($xmlInvoice, 'CalculatedStatement');
		self::_addElement($xmlCalculatedStatement, 'OpeningBalance', number_format($fOpeningBalance, 2, '.', ''));
		self::_addElement($xmlCalculatedStatement, 'Payments', number_format($fPaymentTotal, 2, '.', ''));
		self::_addElement($xmlCalculatedStatement, 'Adjustments', number_format($fAdjustmentTotal, 2, '.', ''));
		self::_addElement($xmlCalculatedStatement, 'NewCharges', number_format($fNewCharges, 2, '.', ''));
		self::_addElement($xmlCalculatedStatement, 'TotalOwing', number_format($fTotalOwing, 2, '.', ''));
		self::_addElement($xmlCalculatedStatement, 'TotalOverdue', number_format($fTotalOverdue, 2, '.', ''));
		self::_addElement($xmlCalculatedStatement, 'BalanceBroughtForward', number_format($fBalanceBroughtForward, 2, '.', ''));
		
		//--------------------------------------------------------------------//
		// Cost Centre Summary
		//--------------------------------------------------------------------//
		$arrCostCentres = array();
		foreach ($arrServices as $arrService) {
			// Is this Service in a Cost Centre?
			if ($arrService['CostCentre'] && $arrService['IsRendered']) {
				$arrCostCentres[$arrService['CostCentre']]['Services'][$arrService['Extension']] = $arrService['ServiceTotal'];
				$arrCostCentres[$arrService['CostCentre']]['GrandTotal'] += (float)$arrService['ServiceTotal'];
			}
		}
		
		$xmlCostCentreSummary = self::_addElement($xmlInvoice, 'CostCentreSummary');
		foreach ($arrCostCentres as $strName=>$arrCostCentre) {
			// Get Cost Centre Services
			$xmlCostCentre = self::_addElement($xmlCostCentreSummary, 'CostCentre');
			self::_addAttribute($xmlCostCentre, 'Name', $strName);
			self::_addAttribute($xmlCostCentre, 'Total', number_format($arrCostCentre['GrandTotal'], 2, '.', ''));
			
			foreach ($arrCostCentre['Services'] as $strFNN=>$fltServiceTotal) {
				$xmlService = self::_addElement($xmlCostCentre, 'Service', number_format($fltServiceTotal, 2, '.', ''));
				self::_addAttribute($xmlService, 'FNN', $strFNN);
			}
		}
		
		//--------------------------------------------------------------------//
		// Services XML
		//--------------------------------------------------------------------//
		$arrDebugCallTypes = array();
		$xmlServices = self::_addElement($xmlInvoice, 'Services');
		foreach ($arrServices as $arrService) {
			//Cli_App_Billing::debug("XML for {$arrService['FNN']}::{$arrService['Extension']}: ", false);
			
			// Only Render if there is data or ForceInvoiceRender is set
			if (!$arrService['IsRendered']) {
				//Cli_App_Billing::debug("NOT RENDERING!");
				//Cli_App_Billing::debug($arrService);
				continue;
			}
			//Cli_App_Billing::debug("Rendering...");
			
			$xmlService = self::_addElement($xmlServices, 'Service');
			self::_addAttribute($xmlService, 'FNN', ($arrService['Extension']) ? $arrService['Extension'] : $arrService['FNN']);
			self::_addAttribute($xmlService, 'CostCentre', $arrService['CostCentre']);
			self::_addAttribute($xmlService, 'Plan', $arrService['RatePlan']);
			self::_addAttribute($xmlService, 'GrandTotal', number_format($arrService['ServiceTotal'], 2, '.', ''));
			
			// Service Itemisation
			$xmlItemisation = self::_addElement($xmlService, 'Itemisation');
			foreach ($arrService['RecordTypes'] as $strName=>$arrChargeType) {
				//Debug($arrService['RecordTypes']);
				
				$xmlItemisationType = self::_addElement($xmlItemisation, 'Category');
				self::_addAttribute($xmlItemisationType, 'Name', $strName);
				self::_addAttribute($xmlItemisationType, 'GrandTotal', number_format($arrChargeType['TotalCharge'], 2, '.', ''));
				self::_addAttribute($xmlItemisationType, 'Records', count($arrChargeType['Itemisation']));
				self::_addAttribute($xmlItemisationType, 'RenderType', GetConstantName($arrChargeType['DisplayType'], 'DisplayType'));
				self::_addAttribute($xmlItemisationType, 'UnitsTotal', $arrChargeType['UnitsTotal']);
				
				// Charge Itemisation
				$xmlItemisationItems = self::_addElement($xmlItemisationType, 'Items');
				foreach ($arrChargeType['Itemisation'] as $arrCDR) {
					$xmlItem = self::_addElement($xmlItemisationItems, 'Item');
					
					// Process the CDR
					$arrItem = self::_itemiseCDR($arrCDR, $arrChargeType['DisplayType']);
					
					// Item Fields
					foreach ($arrItem as $strField=>$mixValue) {
						self::_addElement($xmlItem, $strField, $mixValue);
					}
					
					// Debug SUM of CDR values
					$arrDebugCallTypes[$strName][$arrService['FNN']] += (float)$arrItem['Charge'];
					$arrDebugCallTypes[$strName]['**Total'] += (float)$arrItem['Charge'];
				}
			}
		}
		//Cli_App_Billing::debug("Call Types:");
		//Cli_App_Billing::debug($arrDebugCallTypes);
		
		// Determine Output/Return data
		$strXMLOutput = $domDocument->saveXML();
		if (!$bolDebug) {
			// Save to file, return success
			$intAccount = $arrInvoice['Account'];
			$intCustomerGroup = $arrCustomer['CustomerGroup'];
			$strFullDirectory = FILES_BASE_PATH.'invoices/xml/'.$arrInvoice['invoice_run_id'];
			
			if (!file_exists($strFullDirectory)) {
				mkdir($strFullDirectory, 0777, true);
			}
			
			// Write the xml file using bzip2 compression
			$strFilename = "{$strFullDirectory}/{$intAccount}.xml.bz2";
			$mixReturn = (bool)file_put_contents("compress.bzip2://{$strFilename}", $strXMLOutput);

			if ($mixReturn) {
				Cli_App_Billing::debug("Successully exported to '{$strFilename}'");
			} else {
				Cli_App_Billing::debug("Unsuccessfully attempted to export to '{$strFilename}'");
			}
		} else {
			// Return XML Data
			$mixReturn = $strXMLOutput;
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
	 * @param boolean bolSample optional This is a sample billing file
	 *
	 * @return boolean
	 *
	 * @method
	 */
	static public function deliver($strInvoiceRun, $arrModes = null, $bolGeneratePDFs = true) {
		throw new Exception(__CLASS__."::deliver() has not been implemented yet!");
		
		// Get list of CustomerGroups
		$selCustomerGroups = new StatementSelect("CustomerGroup", "internal_name", "1");
		$selCustomerGroups->Execute();
		$arrCustomerGroups = $selCustomerGroups->FetchAll();
		
		// Define Output Modes
		$arrOutputModes = array();
		$arrSupportedModes = array();
		
		$arrSupportedModes['PRINT']['Archive'] = true;
		$arrSupportedModes['PRINT']['Delivery'] = 'SFTP';
		
		$arrSupportedModes['EMAIL']['Archive'] = false;
		$arrSupportedModes['EMAIL']['Delivery'] = 'EmailAttachment';
		
		if (is_array($arrModes)) {
			foreach ($arrModes as $strMode) {
				$arrOutputModes[$strMode] = $arrSupportedModes[$strMode];
			}
		}
		
		CliEcho("Delivering for InvoiceRun '$strInvoiceRun'...");
		
		// Generate the PDFs
		$strCommandDir = FLEX_BASE_PATH."cli/";
		$strXMLPath = INVOICE_XML_PATH.$strInvoiceRun.'/';
		$strPDFPath = FILES_BASE_PATH."invoices/pdf/".$strInvoiceRun.'/';
		$intRunning = 0;
		if ($bolGeneratePDFs) {
			foreach ($arrOutputModes as $strMode=>&$arrOptions) {
				foreach ($arrCustomerGroups as $strName=>&$arrCustomerGroup) {
					$strCustomerGroup = str_replace(' ', '_', strtoupper($arrCustomerGroup['internal_name']));
					$strTARName = str_replace(' ', '', strtolower($arrCustomerGroup['internal_name']))."-invoice-{$strInvoiceRun}.tar";
					$strTARPath = ($arrOptions['Archive']) ? "-f ".$strXMLPath.$strTARName : "";
					$strCommand = "cd {$strCommandDir}; php pdf.php -c $strCustomerGroup -x $strXMLPath {$strTARPath} -m $strMode";
					$arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['FilePath'] = $strTARPath;
					
					// Start the PDF generation process
					$arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Pipes'] = array();
					$arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Descriptor'][0] = array('pipe', 'r');
					$arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Descriptor'][1] = array('pipe', 'w');
					$arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Descriptor'][2] = array('pipe', 'w');
					if (!($arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Process'] = proc_open($strCommand, $arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Descriptor'], $arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Pipes']))) {
						// There was an error starting the child process
						CliEcho("Unable to start child process ('$strCommand') for $strName::$strMode!");
					} else {
						$arrCustomerGroup['StartDatetime'] = time();
						$arrStatus = proc_get_status($arrCustomerGroup['Process']);
						CliEcho("{$arrCustomerGroup['internal_name']}::$strMode process started successfully with PID {$arrStatus['pid']}!");
						stream_set_blocking($arrOptions['CustomerGroup'][$arrCustomerGroup['internal_name']]['Pipes'][1], 0);
						$intRunning++;
					}
				}
			}
			
			// Monitor PDF Generation Processes
			while ($intRunning) {
				foreach ($arrOutputModes as $strMode=>&$arrOptions) {
					foreach ($arrCustomerGroups as $strName=>$arrCustomerGroup) {
						// Is this Process still running?
						if ($arrCustomerGroup['Process']) {
							$arrStatus = proc_get_status($arrCustomerGroup['Process']);
							if (!$arrStatus['running']) {
								// Close the process
								$intTotalTime = time() - $arrCustomerGroup['StartDatetime'];
								CliEcho("{$arrCustomerGroup['internal_name']}::$strMode process has completed in $intTotalTime seconds");
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
		foreach ($arrOutputModes as $strMode=>&$arrOptions) {
			switch ($arrOptions['Delivery']) {
				case 'SFTP':
				case 'FTP':
					// Connect to the FTP Server
					if ($arrOptions['Delivery'] == 'SFTP') {
						$ptrConnection = ftp_ssl_connect('ftp.salmat.com.au');
					} elseif ($arrOptions['Delivery'] == 'FTP') {
						$ptrConnection = ftp_connect('ftp.salmat.com.au');
					} else {
						// WTF? This shouldn't happen
						CliEcho("Unexpected Delivery Mode '{$arrOptions['Delivery']}'!");
						break;
					}
					
					if ($ptrConnection) {
						// Log in to the FTP Server
						if (ftp_login($ptrConnection, 'yellowbilling', '9uA8;mGL')) {
							// Upload all necessary files
							foreach ($arrCustomerGroups as $strName=>$arrCustomerGroup) {
								if (ftp_put($ptrConnection, basename($arrCustomerGroup['FilePath']), $arrCustomerGroup['FilePath'], FTP_BINARY)) {
									// Successfully Uploaded
									CliEcho("Archive was successfully uploaded to the Salmat Server");
								} else {
									// Could not upload the file
									CliEcho("Unable to upload the Archive to the Salmat Server");
								}
							}
						} else {
							// Unable to log in
							CliEcho("Unable to log in to the Salmat Server with provided credentials");
						}
					} else {
						// Unable to connect to SFTP server
						CliEcho("Unable to establish a connection with the Salmat Server");
					}
					break;
				
				case 'EmailAttachment':
					// Get list of PDFs
					$arrPDFs = glob($strPDFPath."*.pdf");
					
					// Email Content Template
		 			$strContentTemplate = "Please find attached your most recent Invoice from <CustomerGroup>\n\n" .
											"Regards\n" .
											"The Team at <CustomerGroup>";
					
					// Get Billing Period
					$strBillingPeriod = date("F Y", strtotime("-1 month", strtotime($strInvoiceRun)));
					
					foreach ($arrPDFs as $strPDF) {
						// Get Account Number from the filename
						$arrPDFSplit = explode('.', basename($strPDF));
						$intAccount = (int)$arrPDFSplit[0];
						//CliEcho(basename($strPDF)." >> ".$intAccount);
						
						// Is this Invoice set to be Emailed?
						$selAccountEmail = new StatementSelect(
							"((Invoice JOIN Account ON Invoice.Account = Account.Id) JOIN Contact ON Contact.Account = Account.Id) JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id",
							"Invoice.Id AS InvoiceNumber, Invoice.Account, CustomerGroup.external_name, CustomerGroup.outbound_email, Email, FirstName",
							"Email != '' AND Contact.Archived = 0 AND InvoiceRun = <InvoiceRun> AND Invoice.Account = <Account> AND Invoice.DeliveryMethod = ".DELIVERY_METHOD_EMAIL
						);
						$updDeliveryMethod = new StatementUpdate("Invoice", "InvoiceRun = <InvoiceRun> AND Account = <Account>", array('DeliveryMethod' => null));
						
						if ($selAccountEmail->Execute(array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun)) === false) {
			 				Debug($selAccountEmail->Error());
			 				return false;
			 			}
			 			if (!$arrDetails = $selAccountEmail->FetchAll()) {
			 				// Bad Account Number or Non-Email Account
			 				continue;
			 			}
			 			
				 		CliEcho("\n\t+ Emailing Invoice(s) for Account #$intAccount...");
			 			
			 			// for each email-able contact
			 			foreach ($arrDetails as $arrDetail) {
				 			// Set email headers
				 			$arrHeaders = array(
								'From' => $arrDetail['outbound_email'],
								'Subject' => "Telephone Billing for $strBillingPeriod"
							);
		 					$strContent = str_replace('<CustomerGroup>', $arrDetail['external_name'], $strContentTemplate);
					 		
					 		// Does the customer have a first name?
					 		if (trim($arrDetail['FirstName'])) {
					 			$strContent = "Dear ".$arrDetail['FirstName']."\n\n" . $strContent;
					 		}
					 		
				 			// Account for , separated email addresses
				 			$arrEmails = explode(',', $arrDetail['Email']);
				 			foreach ($arrEmails as $strEmail) {
					 			$strEmail = trim($strEmail);
					 			
					 			CliEcho(str_pad("\t\tAddress: '$strEmail'...", 70, " ", STR_PAD_RIGHT), false);
					 			
					 			// Validate email address
					 			if (!preg_match('/^([[:alnum:]]([+-_.]?[[:alnum:]])*)@([[:alnum:]]([.]?[-[:alnum:]])*[[:alnum:]])\.([[:alpha:]]){2,25}$/', $strEmail)) {
					 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Email address is invalid");
					 				continue;
					 			}
					 			
					 			$mimMime = new Mail_mime("\n");
					 			$mimMime->setTXTBody($strContent);
					 			$mimMime->addAttachment(file_get_contents($strPDF), 'application/pdf', "{$intAccount}_{$arrDetail['InvoiceNumber']}.pdf", false);
								$strBody = $mimMime->get();
								$strHeaders = $mimMime->headers($arrHeaders);
					 			$emlMail = &Mail::factory('mail');
					 			
					 			// Uncomment this to Debug
					 			//$strEmail = 'rdavis@ybs.net.au, turdminator@hotmail.com, rj.davis@student.qut.edu.au';
					 			
					 			// Send the email
					 			if (!$emlMail->send($strEmail, $strHeaders, $strBody)) {
					 				CliEcho("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
					 				continue;
					 			} else {
						 			// Uncomment this to Debug
						 			//die;
					 			
									// Update DeliveryMethod
									$arrUpdateData = array();
									$arrUpdateData['DeliveryMethod'] = DELIVERY_METHOD_EMAIL_SENT;
									$arrWhere = array();
									$arrWhere['InvoiceRun'] = $strInvoiceRun;
									$arrWhere['Account'] = $arrDetail['Account'];
									if ($updDeliveryMethod->Execute($arrUpdateData, $arrWhere)) {
										//Debug("Success!");
									} else {
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
		
		return true;
	}
	
	protected static function _addElement(&$xmlParent, $strName, $mixValue = null) {
		if ($xmlParent instanceof DOMNode) {
			// Valid Parent
			$mixReturn = $xmlParent->appendChild(new DOMElement($strName, EscapeXML($mixValue)));
		} else {
			// $xmlParent is not a valid Parent Node
			$mixReturn = false;
		}
		
		return $mixReturn;
	}
	
	protected static function _addAttribute(&$xmlParent, $strName, $mixValue = null) {
		if ($xmlParent instanceof DOMNode) {
			// Valid Parent
			return (bool)$xmlParent->setAttributeNode(new DOMAttr($strName, $mixValue));
		} else {
			// $xmlParent is not a valid Parent Node
			return false;
		}
	}
	
	// Converts a CDR Record to an Itemised Item, based on it's DisplayType
	protected static function _itemiseCDR($arrCDR, $intDisplayType) {
		$arrItem = array();
		switch ($intDisplayType) {
			case RECORD_DISPLAY_S_AND_E:
				//$arrItem['Date'] = date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Description'] = $arrCDR['Description'];
				$arrItem['Items'] = (int)$arrCDR['Units'];
				$arrItem['Charge'] = number_format($arrCDR['Charge'], 2, '.', '');
				break;
			
			case RECORD_DISPLAY_DATA:
				$arrItem['Date'] = date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Time'] = date("H:i:s", strtotime($arrCDR['StartDatetime']));
				$arrItem['CalledParty'] = $arrCDR['Destination'];
				$arrItem['Description'] = $arrCDR['Description'];
				$arrItem['Data'] = (int)$arrCDR['Units'];
				$arrItem['Charge'] = number_format($arrCDR['Charge'], 2, '.', '');
				break;
			
			case RECORD_DISPLAY_SMS:
				$arrItem['Date'] = date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Time'] = date("H:i:s", strtotime($arrCDR['StartDatetime']));
				$arrItem['CalledParty'] = $arrCDR['Destination'];
				$arrItem['Items'] = (int)$arrCDR['Units'];
				$arrItem['Description'] = $arrCDR['Description'];
				$arrItem['Charge'] = number_format($arrCDR['Charge'], 2, '.', '');
				break;
			
			case RECORD_DISPLAY_CALL:
			default:
				$intHours = floor((int)$arrCDR['Units'] / 3600);
				$strDuration = "$intHours:".date("i:s", (int)$arrCDR['Units']);
				
				$arrItem['Date'] = date("j M y", strtotime($arrCDR['StartDatetime']));
				$arrItem['Time'] = date("H:i:s", strtotime($arrCDR['StartDatetime']));
				$arrItem['CalledParty'] = $arrCDR['Destination'];
				$arrItem['Description'] = $arrCDR['Description'];
				$arrItem['Duration'] = $strDuration;
				$arrItem['Charge'] = number_format($arrCDR['Charge'], 2, '.', '');
				break;
		}
		
		$arrItem['TaxExempt'] = $arrCDR['TaxExempt'];
		$arrItem['RateClass'] = $arrCDR['RateClass'];
		return $arrItem;
	}
}
