<?php

class Credit_Card_Type
{
	protected $id;
	protected $name;
	protected $descritpion;
	protected $constName;
	protected $surcharge;
	protected $validLengths;
	protected $validPrefixes;
	protected $cvvLength;
	protected $minimumAmount;
	protected $maximumAmount;

	protected function __construct($arrProperties=NULL)
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

	public function calculateSurcharge($fltAmount)
	{
		return round((($fltAmount * 100) * $this->surcharge)/100, 2);
		
		// Some day we'll move Credit Card Type surcharges to the generic Carrier/Payment Merchant concept
		// TODO
	}

	protected static function getColumns()
	{
		return array(
			'id',
			'name',
			'description',
			'const_name',
			'surcharge',
			'valid_lengths',
			'valid_prefixes',
			'cvv_length',
			'minimum_amount',
			'maximum_amount',
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
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById($this->getTableName(), $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception_Database('Failed to save ' . (str_replace('_', ' ', $this->getTableName())) . ' details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}

		$this->_saved = TRUE;

		return TRUE;
	}

	public static function listAll()
	{
		static $instances;
		if (!isset($instances))
		{
			$all = self::getFor('', NULL, TRUE);
			$instances = array();
			foreach ($all as $one)
			{
				$instances[$one->id] = $one;
			}
		}
		return $instances;
	}

	public function cardNumberIsValid($strCardNumber)
	{
		$strCardNumber = preg_replace("/[^0-9]+/", "", $strCardNumber);
		$lengths = $this->valid_lengths;
		if (array_search(strlen($strCardNumber), $lengths) === FALSE)
		{
			return FALSE;
		}
		$prefixes = $this->valid_prefixes;
		$found = FALSE;
		foreach($prefixes as $prefix)
		{
			if (strpos($strCardNumber, $prefix) === 0)
			{
				$found = TRUE;
				break;
			}
		}
		if (!$found)
		{
			return FALSE;
		}
		return CheckLuhn($strCardNumber) ? $strCardNumber : FALSE;
	}

	public function cvvIsValid($strCvv)
	{
		$strCvv = preg_replace("/[^0-9]+/", "", $strCvv);
		return (strlen($strCvv) === $this->cvvLength) ? $strCvv : FALSE;
	}

	public static function getForId($id)
	{
		$instances = self::listAll();
		return $instances[intval($id)];
	}

	function getForCardNumber($mNumber)
	{
		// Find Card Type
		$iDigits = (int)substr(trim($mNumber), 0, 2);
		switch ($iDigits)
		{
			// VISA
			case 40:
			case 41:
			case 42:
			case 43:
			case 44:
			case 45:
			case 46:
			case 47:
			case 48:
			case 49:
				$iTypeId = CREDIT_CARD_TYPE_VISA;
				break;
		
				// Mastercard
			case 51:
			case 52:
			case 53:
			case 54:
			case 55:
				$iTypeId = CREDIT_CARD_TYPE_MASTERCARD;
				break;
			
			/*	// Bankcard
			case 56:
				$iTypeId = CREDIT_CARD_TYPE_BANKCARD;
				break;*/
		
				// AMEX
			case 34:
			case 37:
				$iTypeId = CREDIT_CARD_TYPE_AMEX;
				break;
		
				// Diners
			case 30:
			case 36:
			case 38:
				$iTypeId = CREDIT_CARD_TYPE_DINERS;
				break;
		
			default:
				return null;
		}
	
		return self::getForId($iTypeId);
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		if (!$strSort)
		{
			$strSort = 'name ASC';
		}
		// Note: Email address should be unique, so only fetch the first record
		$selMatches = new StatementSelect(
			self::getTableName(), 
			self::getColumns(), 
			$strWhere,
			$strSort,
			$strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception_Database("Failed to load " . (str_replace('_', ' ', $this->getTableName())) . ": " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new self($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}
	
	public function toStdClass()
	{
		$aColumns	= self::getColumns();
		$oStdClass	= new stdClass();
		foreach ($aColumns as $sColumn)
		{
			$oStdClass->{$sColumn}	= $this->{$sColumn};
		}
		return $oStdClass;
	}

	public function __get($strName)
	{
		switch (strtolower($strName))
		{
			case 'value':
				return $this->id;
			case 'constant':
				return $this->constName;
			case 'cssclass':
				return str_replace('_', '-', strtolower($this->constName));
		}

		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			$mxdValue = $this->{$strName};

			if ($strName == 'validLengths' || $strName == 'validPrefixes')
			{
				$mxdValue = explode(',', $mxdValue);
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
				if (($strName == 'validLengths' || $strName == 'validPrefixes') && is_array($mxdValue))
				{
					throw new Exception('wtf');
					$mxdValue = implode(',', $mxdValue);
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
