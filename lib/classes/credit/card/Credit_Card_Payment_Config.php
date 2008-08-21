<?php

class Credit_Card_Payment_Config
{
	private $id = null;
	private $merchantId = null;
	private $password = null;
	private $confirmationText = null;
	private $directDebitText = null;
	private $customerGroupId = null;

	private $customerGroup = null;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	protected function init($arrProperties)
	{
		foreach($arrProperties as $name => $property)
		{
			$this->{$name} = $property;
		}
		$this->_saved = TRUE;
	}

	protected static function getColumns()
	{
		return array(
			'id',
			'merchant_id',
			'password',
			'confirmation_text',
			'direct_debit_text',
			'customer_group_id',
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

	protected static function getTableName()
	{
		return strtolower(__CLASS__);
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
			$statement = new StatementInsert($this->getTableName(), $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['Id'] = $this->id;
			$statement = new StatementUpdateById($this->getTableName(), $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save ' . (str_replace('_', ' ', $this->getTableName())) . ' details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}

		$this->_saved = TRUE;

		return TRUE;
	}

	public static function getForCustomerGroup($mxdCustomerGroupOrId, $bolCreateIfNotExists=FALSE)
	{
		$intCustomerGroupId = 0;
		if ($mxdCustomerGroupOrId instanceof Customer_Group)
		{
			$intCustomerGroupId = $mxdCustomerGroupOrId->id;
		}
		else
		{
			$intCustomerGroupId  =intval($mxdCustomerGroupOrId);
		}
		if (!$intCustomerGroupId)
		{
			throw new Exception("No customer group specified for " . (str_replace('_', ' ', $this->getTableName())) . " retrieval.");
		}
		$objConfig = self::getFor('customer_group_id=<CustGroupId>', array('CustGroupId'=>$intCustomerGroupId));
		if (!$objConfig && $bolCreateIfNotExists)
		{
			$objConfig = new Credit_Card_Payment_Config();
			$objConfig->customerGroupId = $intCustomerGroupId;
			$objConfig->_saved = FALSE;
		}
		return $objConfig;
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selMatches = new StatementSelect(
			self::getTableName(), 
			self::getColumns(), 
			$strWhere,
			$strSort,
			$strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load " . (str_replace('_', ' ', $this->getTableName())) . ": " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Credit_Card_Payment_Config($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}

	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			$mxdValue = $this->{$strName};

			if ($strName == 'password')
			{
				$mxdValue = Decrypt($mxdValue);
			}

			return $mxdValue;
		}
		return NULL;
	}

	public function __set($strName, $mxdValue)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} !== $mxdValue)
			{
				if ($strName == 'customerGroupId')
				{
					$this->customerGroup = NULL;
				}
				else if ($strName == 'customerGroup')
				{
					$this->customerGroupId = $mxdValue ? $mxdValue->id : null;
				}

				if ($strName == 'password')
				{
					$mxdValue = Encrypt($mxdValue);
				}

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

	private function uglifyName($name)
	{
		$tidy = str_replace(' ', '_', strtolower(preg_replace("/([A-Z])/", " \${1}", $name)));
		return $tidy;
	}
}

?>
