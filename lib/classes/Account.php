<?php

class Account
{
	private $id = NULL;
	private $businessName = NULL;
	private $tradingName = NULL;
	private $abn = NULL;
	private $acn = NULL;
	private $address1 = NULL;
	private $address2 = NULL;
	private $suburb = NULL;
	private $postcode = NULL;
	private $state = NULL;
	private $country = NULL;
	private $billingType = NULL;
	private $primaryContact = NULL;
	private $customerGroup = NULL;
	private $creditCard = NULL;
	private $directDebit = NULL;
	private $accountGroup = NULL;
	private $lastBilled = NULL;
	private $billingDate = NULL;
	private $billingFreq = NULL;
	private $billingFreqType = NULL;
	private $billingMethod = NULL;
	private $paymentTerms = NULL;
	private $createdBy = NULL;
	private $createdOn = NULL;
	private $disableDDR = NULL;
	private $disableLatePayment = NULL;
	private $disableLateNotices = NULL;
	private $latePaymentAmnesty = NULL;
	private $sample = NULL;
	private $archived = NULL;
	private $creditControlStatus = NULL;
	private $lastAutomaticInvoiceAction = NULL;
	private $lastAutomaticInvoiceActionDatetime = NULL;
	private $automaticBarringStatus = NULL;
	private $automaticBarringDatetime = NULL;
	private $tioReferenceNumber = NULL;

	protected static $cache = array();

	private function __construct($arrProperties=NULL, $bolPropertiesIncludeEmployeeDetails=FALSE)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
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
		$arrValues = $this->getValuesToSave();

		// No id means that this must be a new record
		if (!$this->id)
		{
			$statement = new StatementInsert('Account', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['Id'] = $this->id;
			$statement = new StatementUpdateById('Account', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save account details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
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

	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{$name} = $value;
		}
		
	}

	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}

	protected function __set($strName, $mxdValue)
	{
		if ($strName[0] === '_') return; // It is read only!
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} !== $mxdValue)
			{
				$this->{$strName} = $mxdValue;
				$this->_saved = FALSE;
			}
		}
	}

	private function tidyName($name)
	{
		if (preg_match("/^[A-Z]+$/", $name)) $name = strtolower($name);
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
}

?>
