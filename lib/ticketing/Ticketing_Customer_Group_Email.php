<?php

/* 
 * Note: 	Active email addresses should be unique accross all the customer groups.
 * 			That is to say ticketing_customer_group_email.email must be unique for all ticketing_customer_group_email 
 * 			records where ticketing_customer_group_email.archived_on datetime IS NOT NULL 
 */

class Ticketing_Customer_Group_Email
{
	private $id = NULL;
	private $customerGroupId = NULL;
	private $email = NULL;
	private $name = NULL;
	private $autoReply = NULL;
	private $archivedOnDatetime = NULL;

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
			'id'					=> 'id',
			'customerGroupId'		=> 'customer_group_id',
			'email'					=> 'email',
			'name'					=> 'name',
			'autoReply'				=> 'auto_reply',
			'archivedOnDatetime'	=> 'archived_on_datetime'
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

		if (!$this->id)
		{
			// The record does not currently exist, as no id has been set
			
			// Check that this email address is unique if the record is active (this should probably be moved to a 'validate' function)
			if ($this->archivedOnDatetime === NULL)
			{
				// The object is considered active (archivedOnDatetime has not been set), so make sure the email address isn't currently being used
				$objTCGEmail = self::getForEmailAddress($this->email);
				if ($objTCGEmail != NULL)
				{
					// An active ticketing_customer_group_email record already exists for this email address
					throw new Exception('Email address is already in use');
				}
			}
			
			$statement = new StatementInsert(strtolower(__CLASS__), $arrValues);
		}
		else
		{
			// The record must already exist, as it has an id set
			
			// Check that this email address is unique if the record is active (this should probably be moved to a 'validate' function)
			if ($this->archivedOnDatetime === NULL)
			{
				// The object is considered active (archivedOnDatetime has not been set), so make sure the email address isn't currently being used
				$objTCGEmail = self::getForEmailAddress($this->email);
				if ($objTCGEmail != NULL && $objTCGEmail->id != $this->id)
				{
					// An active ticketing_customer_group_email record already exists for this email address, and it is not for the record that this object relates to
					throw new Exception('Email address is already in use');
				}
			}
			
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
		$selMatches = new StatementSelect(strtolower(__CLASS__), self::getColumns(), $where, $strSort, $strLimit);
		
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to retrieve customer group email: " . $selMatches->Error());
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

	public static function listForCustomerGroupId($customerGroupId, $bolIncludeArchived=false)
	{
		if (!$customerGroupId)
		{
			return array();
		}
		
		if ($bolIncludeArchived)
		{
			// Include archived records
			$strWhere = "customer_group_id = <CustomerGroupId>";
		}
		else
		{
			// Don't include the archived records
			$strWhere = "customer_group_id = <CustomerGroupId> AND archived_on_datetime IS NULL";
		}
		
		return self::getFor($strWhere, array("CustomerGroupId" => $customerGroupId), TRUE, "name ASC, email ASC");
	}

	public static function listForCustomerGroup(Customer_Group $customerGroup, $bolIncludeArchived=false)
	{
		if (!$customerGroup)
		{
			return array();
		}
		return self::listForCustomerGroupId($customerGroup->id, $bolIncludeArchived);
	}

	// This will only consider ticketing_customer_group_email records where archived_on_datetime is null
	// If $intCustomerGroupId has been specified, then it will only consider records with this customer group id
	public static function getForEmailAddress($strEmailAddress, $intCustomerGroupId=null)
	{
		$strWhere = "LOWER(email) = <Email> AND archived_on_datetime IS NULL";
		
		if ($intCustomerGroupId)
		{
			// Limit it to only considering records associated with $intCustomerGroupId
			$strWhere .= " AND customer_group_id = <CustomerGroupId>";
		}

		$arrWhere = array(	"Email"				=> strtolower($strEmailAddress),
							"CustomerGroupId"	=> $intCustomerGroupId);
		return self::getFor($strWhere, $arrWhere);
	}
	
	// Retrieves an object representing the active record that this object relates to (as this object might represent an archived record).
	// Note that this will always be a new instance of the object, even if it relates to the same ticketing_customer_group_email record as $this does.
	// It will return NULL if there isn't an active record relating to this object and customer group
	public function getActiveVersion()
	{
		// Get the active record with the same email and customer group as this one
		$strWhere = "LOWER(email) = <Email> AND customer_group_id = <CustomerGroupId> AND archived_on_datetime IS NULL";
		$arrWhere = array('Email'=> $this->email, 'CustomerGroupId'=> $this->customerGroupId);
		return self::getFor($strWhere, $arrWhere);
	}
	
	// Returns true if the object represents an archived version of an customer group email address
	public function isArchivedVersion()
	{
		return ($this->archivedOnDatetime !== null) ? true : false;
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
