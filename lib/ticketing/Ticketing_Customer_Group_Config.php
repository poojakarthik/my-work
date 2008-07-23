<?php

class Ticketing_Customer_Group_Config
{
	private $id = NULL;
	private $customerGroupId = NULL;
	private $acknowledgeEmailReceipts = NULL;
	private $emailReceiptAcknowledgement = NULL;
	private $defaultEmailId = NULL;

	private $_saved = FALSE;

	private function __construct($arrProperties)
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

	private function getColumns()
	{
		return array(
			'id' => 'id',
			'customerGroupId' => 'customer_group_id',
			'acknowledgeEmailReceipts' => 'acknowledge_email_receipts',
			'emailReceiptAcknowledgement' => 'email_receipt_acknowledgement',
			'defaultEmailId' => 'default_email_id',
		);
	}

	private static function getFor($where, $arrWhere)
	{
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			$this->getColumns(), 
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
		return strtolower(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
	}
}

?>
