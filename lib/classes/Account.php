<?php

class Account
{
	protected static $cache = array();
	
	private	$_arrTidyNames	= array();
	private	$_arrProperties	= array();

	public function __construct($arrProperties=NULL, $bolPropertiesIncludeEmployeeDetails=FALSE, $bolLoadById=FALSE)
	{
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('Account');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$this->_arrProperties[$strName]					= NULL;
			$this->_arrTidyNames[self::tidyName($strName)]	= $strName;
		}
		$this->_arrProperties[$arrTableDefine['Id']]				= NULL;
		$this->_arrTidyNames[self::tidyName($arrTableDefine['Id'])]	= $arrTableDefine['Id'];
		
		// Automatically load the Invoice using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : (($arrProperties['id']) ? $arrProperties['id'] : NULL);
		if ($bolLoadById && $intId)
		{
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId)))
			{
				$arrProperties	= $selById->Fetch();
			}
			elseif ($selById->Error())
			{
				throw new Exception("DB ERROR: ".$selById->Error());
			}
			else
			{
				throw new Exception(__CLASS__." with Id {$intId} does not exist!");
			}
		}
		
		// Set Properties
		if (is_array($arrProperties))
		{
			foreach ($arrProperties as $strName=>$mixValue)
			{
				// Load from the Database
				$this->{$strName}	= $mixValue;
			}
		}
	}

	public function getBalance()
	{
		// TODO: Implement the account balance functionality here
		$framework = function_exists('Framework') ? Framework() : Flex::framework();
		return $framework->GetAccountBalance($this->id);
	}

	/**
	 * listServices
	 *
	 * returns array of Service objects defining the current service records associated with this account, for active services (based on the status)
	 *
	 * returns array of Service objects defining the current service records associated with this account, for active services (based on the status)
	 * 
	 * @return	array of Service objects
	 */
	public function listActiveServices()
	{
		return $this->listServices(array(SERVICE_ACTIVE));
	}

	/**
	 * listServices
	 *
	 * returns array of Service objects defining the current service records associated with this account
	 *
	 * returns array of Service objects defining the current service records associated with this account
	 * 
	 * @param	array	$arrStatuses	Defines the services to retrieve based on the statuses
	 * 									(Optional, defaults to NULL, in which services of all statuses will be retrieved)
	 *
	 * @return	array of Service objects
	 */
	public function listServices($arrStatuses=NULL)
	{
		if (is_array($arrStatuses) && count($arrStatuses))
		{
			$strStatusConstraint = "AND Status IN (". implode(", ", $arrStatuses) .")";
		}
		else
		{
			$strStatusConstraint = "";
		}
		
		$qryQuery = new Query();
		$strQuery = "
SELECT *
FROM Service
WHERE Id IN (
	/* Find the maximum id for each FNN associated with the account, as this record defines the current state of the service for this account */
	SELECT Max(Id)
	FROM Service
	WHERE Account = {$this->id}
	GROUP BY Account, FNN
)
AND Account = {$this->id}
AND (ClosedOn IS NULL OR ClosedOn >= CreatedOn)
$strStatusConstraint
ORDER BY FNN;";
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve services for Account: {$this->id} - " . $qryQuery->Error() ." - Query: $strQuery");
		}

		$arrServices = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrServices[$arrRecord['Id']] = new Service($arrRecord, FALSE);
		}

		return $arrServices;
	}


	public function getName()
	{
		return $this->businessName ? $this->businessName : ($this->tradingName ? $this->tradingName : '');
	}

	public function getCustomerGroup()
	{
		return Customer_Group::getForId($this->customerGroup);
	}
	
	// Returns a list of ContactIds or Contact objects, defining the contacts that can be associated with this account
	// In both cases, the key to the array will be the id of the contact
	// This will return an empty string if there are no Contacts for this account
	public function getContacts($bolAsObjects=FALSE)
	{
		$strQuery = "	SELECT c.Id AS ContactId
						FROM Account AS a INNER JOIN Contact AS c ON (c.CustomerContact = 1 AND a.AccountGroup = c.AccountGroup) OR (c.Account = a.Id) OR (c.Id = a.PrimaryContact)
						WHERE a.Id = {$this->id}";
		$qryQuery = new Query();
		
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve contacts for account: {$this->id} - " . $qryQuery->Error());
		}

		$arrContacts = array();

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrContacts[$arrRecord['ContactId']] = ($bolAsObjects)? Contact::getForId($arrRecord['ContactId']) : $arrRecord['ContactId'];
		}

		return $arrContacts;
	}

	public static function getForTicket(Ticketing_Ticket $objTicket)
	{
		return Account::getForId($objTicket->accountId);
	}

	/**
	 * Applies a payment to an account
	 * 
	 * *** THIS HAS ONLY BEEN TESTED FOR CREDIT CARD PAYMENTS ***
	 */
	public function applyPayment($intEmployeeId, $contact, $time, $totalAmount, $txnId, $strUniqueReference, $paymentType, $creditCardNumber=NULL, $creditCardType=NULL, $surcharge=NULL)
	{
		$arrCCH = array();
		$arrPayment = array();
		$arrCharge = array();

		$arrCCH['account_id'] = $arrPayment['Account'] = $arrCharge['Account'] = $this->id;
		$arrPayment['AccountGroup'] = $arrCharge['AccountGroup'] = $this->accountGroup;
		$arrCCH['employee_id'] = $arrPayment['EnteredBy'] = $arrCharge['CreatedBy'] = $intEmployeeId;
		$arrCCH['contact_id'] = $contact->id;
		$arrCCH['receipt_number'] = $strUniqueReference;
		$arrCCH['amount'] = $arrPayment['Amount'] = $arrPayment['Balance'] = $totalAmount;
		$arrCharge['Amount'] = RemoveGST($surcharge);
		$arrCCH['payment_datetime'] = date('Y-m-d H:i:s', $time);
		$arrPayment['PaidOn'] = $arrCharge['CreatedOn'] = $arrCharge['ChargedOn'] = date('Y-m-d', $time);
		$arrCCH['txn_id'] = $arrPayment['TXNReference'] = $txnId;
		$arrPayment['OriginId'] = ($paymentType == PAYMENT_TYPE_CREDIT_CARD) ? (substr($creditCardNumber, 0, 6) . '...' . substr($creditCardNumber, -3)) : '';
		$arrPayment['Status'] = $arrCharge['Status'] = CHARGE_APPROVED;
		$arrCharge['LinkType'] = CHARGE_LINK_PAYMENT;
		$arrCharge['ChargeType'] = "CCS";
		$arrCharge['Nature'] = "DR";
		$arrCharge['global_tax_exempt'] = 0;
		$arrCharge['Description'] = ($paymentType == PAYMENT_TYPE_CREDIT_CARD) ? ($creditCardType->name . ' Surcharge for Payment on ' . date('d/m/Y', $time) . ' (' . $totalAmount . ') @ ' . (round(floatval($creditCardType->surcharge)*100, 2)) . '%') : '';
		$arrPayment['Payment'] = $arrCharge['Notes'] = '';
		$arrPayment['PaymentType'] = $arrPayment['OriginType'] = $paymentType;

		$insPayment = new StatementInsert('Payment');
		if (($paymentId = $insPayment->Execute($arrPayment)) === FALSE)
		{
			// Eak!!
			throw new Exception('Failed to create payment record: ' . $insPayment->Error());
		}

		if ($paymentType == PAYMENT_TYPE_CREDIT_CARD)
		{
			$arrCCH['payment_id'] = $arrCharge['LinkId'] = $paymentId;

			$insCharge = new StatementInsert('Charge');
			if (($id = $insCharge->Execute($arrCharge)) === FALSE)
			{
				// Eak!!
				throw new Exception('Failed to create payment charge: ' . $insCharge->Error());
			}

			$insCreditCardHistory = new StatementInsert('credit_card_payment_history');
			if (($id = $insCreditCardHistory->Execute($arrCCH)) === FALSE)
			{
				// Eak!!
				throw new Exception('Failed to create credit card payment history: ' . $insCreditCardHistory->Error());
			}
		}
	}
	
	public function getInterimInvoiceType()
	{
		switch ($this->Archived)
		{
			case ACCOUNT_STATUS_ACTIVE:
				return INVOICE_RUN_TYPE_INTERIM;
				break;
				
			case ACCOUNT_STATUS_CLOSED:
			case ACCOUNT_STATUS_DEBT_COLLECTION:
				return INVOICE_RUN_TYPE_FINAL;
				break;
		}
		return null;
	}

	/**
	 * getBillingPeriodStart()
	 *
	 * Calculates the start of the current Billing Period for this Account
	 *
	 * @param	[string	$strEffectiveDate]				Only include Invoice Runs from before this date (defaults to Today)
	 *
	 * @return	string									Billing Period Start Date
	 *
	 * @method
	 */
	public function getBillingPeriodStart($strEffectiveDate=null)
	{
		$strEffectiveDate	= (strtotime($strEffectiveDate)) ? date("Y-m-d", strtotime($strEffectiveDate)) : date("Y-m-d");
		
		// Get the Account's last Invoice date
		$strAccountLastInvoiceDate			= $this->getLastInvoiceDate($strEffectiveDate);
		$intAccountLastInvoiceDate			= strtotime($strAccountLastInvoiceDate);
		
		// Get the CustomerGroup's last Invoice date (or predicted last Invoice date)
		$strCustomerGroupLastInvoiceDate	= Invoice_Run::getLastInvoiceDateByCustomerGroup($this->CustomerGroup, $strEffectiveDate);
		$intCustomerGroupLastInvoiceDate	= strtotime($strCustomerGroupLastInvoiceDate);
		
		return date("Y-m-d", max($intAccountLastInvoiceDate, $intCustomerGroupLastInvoiceDate));
	}

	/**
	 * getLastInvoiceDate()
	 *
	 * Retrieves (or calculates) the Last Invoice Date for this Account
	 *
	 * @param	[string	$strEffectiveDate]				Only include Invoice Runs from before this date (defaults to Today)
	 *
	 * @return	string									Date of the last Invoice Run
	 *
	 * @method
	 */
	public function getLastInvoiceDate($strEffectiveDate=null)
	{
		$strEffectiveDate	= strtotime($strEffectiveDate) ? date("Y-m-d", strtotime($strEffectiveDate)) : date("Y-m-d"); 
		
		$selPaymentTerms	= self::_preparedStatement('selPaymentTerms');

		$selInvoiceRun	= self::_preparedStatement('selLastInvoiceRun');
		if ($selInvoiceRun->Execute(Array('Account' => $this->Id)))
		{
			// We have an old InvoiceRun
			$arrLastInvoiceRun	= $selInvoiceRun->Fetch();
			return $arrLastInvoiceRun['BillingDate'] . ' 00:00:00';
		}
		elseif ($selInvoiceRun->Error())
		{
			throw new Exception("DB ERROR: ".$selInvoiceRun->Error());
		}
		elseif ($selPaymentTerms->Execute(Array('customer_group_id' => $this->CustomerGroup)))
		{
			$arrPaymentTerms	= $selPaymentTerms->Fetch();

			// No InvoiceRuns, so lets calculate when it should have been
			$intInvoiceDatetime	= strtotime(date("Y-m-{$strDay} 00:00:00", strtotime($strEffectiveDate)));
			if ((int)date("d", strtotime($strEffectiveDate)) < $arrPaymentTerms['invoice_day'])
			{
				// Billing Date is last Month
				$intInvoiceDatetime	= strtotime("-1 month", $intInvoiceDatetime);
			}
			return date("Y-m-d H:i:s", $intInvoiceDatetime);
		}
		elseif ($selPaymentTerms->Error())
		{
			throw new Exception("DB ERROR: ".$selPaymentTerms->Error());
		}
		else
		{
			throw new Exception("No Payment Terms specified for Customer Group {$intCustomerGroup}");
		}
	}

	private static function getFor($where, $arrWhere, $bolAsArray=FALSE)
	{
		$selUsers = new StatementSelect(
			"Account", 
			self::getColumns(), 
			$where);
		if (($outcome = $selUsers->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing account: " . $selUsers->Error());
		}
		if (!$outcome && !$bolAsArray)
		{
			return NULL;
		}

		$records = array();
		while ($props = $selUsers->Fetch())
		{
			if (!array_key_exists($props['Id'], self::$cache))
			{
				self::$cache[$props['Id']] = new Account($props);
			}
			$records[] = self::$cache[$props['Id']];
			if (!$bolAsArray)
			{
				return $records[0];
			}
		}
		return $records;
	}

	public static function getForId($id)
	{
		if (array_key_exists($id, self::$cache))
		{
			return self::$cache[$id];
		}
		$account = self::getFor("Id = <Id>", array("Id" => $id));
		return $account;
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach($arrColumns as $strColumn)
		{
			if ($strColumn == 'id') 
			{
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	} 

	// $intEmployeeId is used for the account_history record which records the state of the account
	// Setting this to NULL will make it use the system employee id (Account_History::SYSTEM_ACTION_EMPLOYEE_ID)
	public function save($intEmployeeId=NULL)
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		
		// Do we have an Id for this instance?
		if ($this->Id !== NULL)
		{
			// Update
			$ubiSelf	= self::_preparedStatement("ubiSelf");
			if ($ubiSelf->Execute($this->toArray()) === FALSE)
			{
				throw new Exception("DB ERROR: ".$ubiSelf->Error());
			}
		} 
		else
		{
			// Insert
			$insSelf	= self::_preparedStatement("insSelf");
			$mixResult	= $insSelf->Execute($this->toArray());
			if ($mixResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$insSelf->Error());
			}
			if (is_int($mixResult))
			{
				$this->Id	= $mixResult;
			}
			else
			{
				throw new Exception('Failed to save account details: ' . $statement->Error());
			}
		}
		
		// Record the new state of the account
		Account_History::recordCurrentState($this->Id, $intEmployeeId);
		
		$this->_saved = TRUE;
		return TRUE;
	}
	
	// Empties the cache
	public static function emptyCache()
	{
		self::$cache = array();
	}

	protected static function getColumns()
	{
		return array(
			'Id',
			'BusinessName',
			'TradingName',
			'ABN',
			'ACN',
			'Address1',
			'Address2',
			'Suburb',
			'Postcode',
			'State',
			'Country',
			'BillingType',
			'PrimaryContact',
			'CustomerGroup',
			'CreditCard',
			'DirectDebit',
			'AccountGroup',
			'LastBilled',
			'BillingDate',
			'BillingFreq',
			'BillingFreqType',
			'BillingMethod',
			'PaymentTerms',
			'CreatedBy',
			'CreatedOn',
			'DisableDDR',
			'DisableLatePayment',
			'DisableLateNotices',
			'LatePaymentAmnesty',
			'Sample',
			'Archived',
			'credit_control_status',
			'last_automatic_invoice_action',
			'last_automatic_invoice_action_datetime',
			'automatic_barring_status',
			'automatic_barring_datetime',
			'tio_reference_number',
			'vip'
		);
	}

	public function __get($strName)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	protected function __set($strName, $mxdValue)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		
		if (array_key_exists($strName, $this->_arrProperties))
		{
			$mixOldValue					= $this->_arrProperties[$strName];
			$this->_arrProperties[$strName]	= $mxdValue;
			
			if ($mixOldValue !== $mxdValue)
			{
				$this->_saved = FALSE;
			}
		}
		else
		{
			$this->{$strName} = $mxdValue;
		}
	}

	private function tidyName($name)
	{
		if (preg_match("/^[A-Z]+$/", $name)) $name = strtolower($name);
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
	
	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 * 
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray()
	{
		return $this->_arrProperties;
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	private static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Account", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selLastInvoiceRun':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("InvoiceRun JOIN Invoice ON Invoice.invoice_run_id = InvoiceRun.Id", "BillingDate", "Invoice.Account = <Account> AND invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED, "BillingDate DESC", 1);
					break;
				case 'selPaymentTerms':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("payment_terms", "*", "customer_group_id = <customer_group_id>", "id DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Account");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Account");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}

?>
