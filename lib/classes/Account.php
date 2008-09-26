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
				// Do we want to Debug something?
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

	// This is a dirty hack. It returns an array of array('id'=>x, 'fnn'=>x)
	public function listServices()
	{
		$selServices = new StatementSelect('account_services', array('service_id' => 'service_id', 'fnn' => 'fnn'), 'account_id = <ACCOUNT_ID>');
		$arrWhere = array('ACCOUNT_ID' => $this->id);
		if (($outcome = $selServices->Execute($arrWhere)) === FALSE)
		{
			throw new Exception('Failed to load services for account: ' . $selServices->Error());
		}
		return $selServices->FetchAll();
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

	public function save()
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
			$this->_arrProperties[$strName]	= $mxdValue;
			
			if ($this->{$strName} !== $mxdValue)
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
