<?php

class Ticketing_Customer_Group_Email
{
	private $id = NULL;
	private $customerGroupId = NULL;
	private $email = NULL;
	private $name = NULL;
	private $autoReply = NULL;

	private $_saved = FALSE;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public function autoReply()
	{
		return $this->autoReply === ACTIVE_STATUS_ACTIVE;
	}

	public function setAutoReply($autoReply)
	{
		$this->_saved = $this->_saved && ($this->autoReply == ($autoReply ? ACTIVE_STATUS_ACTIVE : ACTIVE_STATUS_INACTIVE));
		$this->autoReply = ($autoReply ? ACTIVE_STATUS_ACTIVE : ACTIVE_STATUS_INACTIVE);
	}

	private static function getColumns()
	{
		return array(
			'id' => 'id',
			'customerGroupId' => 'customer_group_id',
			'email' => 'email',
			'name' => 'name',
			'autoReply' => 'auto_reply',
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

	public function delete()
	{
		$delInstance = new Query();
		$strSQL = "DELETE FROM " . strtolower(__CLASS__) . " WHERE id = " . $this->id;
		if (($outcome = $delInstance->Execute($strSQL)) === FALSE)
		{
			throw new Exception('Failed to delete customer group email ' . $this->id . ' from customer group ' . $this->customerGroupId . ': ' . $delInstance->Error());
		}
		$this->id = NULL;
		$this->_saved = FALSE;
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
			$arrValues['id'] = $this->id;
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

	private static function getFor($where, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			self::getColumns(), 
			$where
		);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing customer group email: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Customer_Group_Email($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}

	public static function getForId($id)
	{
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	public static function createForDetails($customerGroupId, $email, $name, $autoReply)
	{
		$instance = new self();
		$instance->customerGroupId = $customerGroupId;
		$instance->email = $email;
		$instance->name = $name;
		$instance->setAutoReply($autoReply);
		$instance->_saved = FALSE;
		return $instance;
	}

	public static function listForCustomerGroupId($customerGroupId)
	{
		if (!$customerGroupId)
		{
			return array();
		}
		return self::getFor("customer_group_id = <CustomerGroupId>", array("CustomerGroupId" => $customerGroupId), TRUE);
	}

	public static function listForCustomerGroup(Customer_Group $customerGroup)
	{
		if (!$customerGroup)
		{
			return array();
		}
		return self::listForCustomerGroupId($customerGroup->id);
	}

	public static function getForEmailAddress($strEmailAddress)
	{
		return self::getFor("LOWER(email) = <Email>", array("Email" => $strEmailAddress));
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
