<?php

class Ticketing_Customer_Group_Config
{
	private $id = NULL;
	private $customerGroupId = NULL;
	private $acknowledgeEmailReceipts = NULL;
	private $emailReceiptAcknowledgement = NULL;
	private $defaultEmailId = NULL;

	private $_saved = FALSE;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public function acknowledgeEmailReceipts()
	{
		return $this->acknowledgeEmailReceipts === ACTIVE_STATUS_ACTIVE;
	}

	public function setAcknowledgeEmailReceipts($bolSendAcknowledgements)
	{
		$this->_saved = $this->_saved && ($this->acknowledgeEmailReceipts() ===  $bolSendAcknowledgements);
		$this->acknowledgeEmailReceipts = $bolSendAcknowledgements ? ACTIVE_STATUS_ACTIVE : ACTIVE_STATUS_INACTIVE;
	}

	private static function getColumns()
	{
		return array(
			'id' => 'id',
			'customerGroupId' => 'customer_group_id',
			'acknowledgeEmailReceipts' => 'acknowledge_email_receipts',
			'emailReceiptAcknowledgement' => 'email_receipt_acknowledgement',
			'defaultEmailId' => 'default_email_id',
		);
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach ($arrColumns as $strColumn)
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
			$statement = new StatementInsert(strtolower(__CLASS__), $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['Id'] = $this->id;
			$statement = new StatementUpdateById(strtolower(__CLASS__), $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save customer group email details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;

		return TRUE;
	}

	public static function getForCustomerGroup($customerGroup)
	{
		if (!$customerGroup)
		{
			return NULL;
		}
		$instance = self::getForCustomerGroupId($customerGroup->id);
		if (!$instance)
		{
			$instance = new Ticketing_Customer_Group_Config();
			$instance->customerGroupId = $customerGroup->id;
			$instance->acknowledgeEmailReceipts = ACTIVE_STATUS_INACTIVE;
			$instance->_saved = FALSE;
		}
		return $instance;
	}

	private static function getFor($where, $arrWhere)
	{
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			self::getColumns(), 
			$where);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing customer group configuration: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return NULL;
		}
		return new Ticketing_Customer_Group_Config($selMatches->Fetch());
	}

	public static function getForId($id)
	{
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	public static function getForCustomerGroupId($id)
	{
		return self::getFor("customer_group_id = <CustomerGroupId>", array("CustomerGroupId" => $id));
	}

	public function getDefaultCustomerGroupEmail()
	{
		return Ticketing_Customer_Group_Email::getForId($this->defaultEmailId);
	}

	protected function init($arrProperties)
	{
		foreach($arrProperties as $name => $property)
		{
			$this->{$name} = $property;
		}
		$this->_saved = TRUE;
	}

	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}

	public function __set($strName, $mxdValue)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} != $mxdValue)
			{
				$this->{$strName} = $mxdValue;
				$this->_saved = FALSE;
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
