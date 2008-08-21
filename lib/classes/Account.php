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

	public static function getForTicket(Ticketing_Ticket $objTicket)
	{
		return Account::getForId($objTicket->accountId);
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
			if (!array_key_exists($props['id'], self::$cache))
			{
				self::$cache[$props['id']] = new Account($props);
			}
			$records[] = self::$cache[$props['id']];
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
			'automatic_barring_datetime'
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
			if ($this->{$strName} != $mxdValue)
			{
				$this->{$strName} = $mxdValue;
			}
		}
	}

	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
}

?>
