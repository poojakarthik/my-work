<?php

class Ticketing_User
{
	private $id = NULL;
	private $employeeId = NULL;
	private $permissionId = NULL;

	private $_saved = FALSE;
	private $_loadedEmployeeDetails = FALSE;
	private $_arrEmployeeDetails = NULL;

	protected static $cache = array();

	private function __construct($arrProperties=NULL, $bolPropertiesIncludeEmployeeDetails=FALSE)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
			$this->_loadedEmployeeDetails = $bolPropertiesIncludeEmployeeDetails;
		}
	}

	public static function listAll()
	{
		return self::getFor(array(), array(), TRUE);
	}

	public static function setPermissionForEmployeeId($employeeId, $permission=TICKETING_USER_PERMISSION_NONE)
	{
		$user = self::getForEmployeeId($employeeId);
		if ($user == NULL)
		{
			// If there is no user and we don't want the user to have access, there's no point creating a record. 
			if ($permission == TICKETING_USER_PERMISSION_NONE)
			{
				return TRUE;
			}
			$user = new Ticketing_User();
			$user->employeeId = $employeeId;
		}
		$user->permission_id = $permission;
		$user->save();
		return TRUE;
	}

	public static function getPermissionForEmployeeId($employeeId)
	{
		$user = self::getForEmployeeId($employeeId);
		return ($user == NULL) ? TICKETING_USER_PERMISSION_NONE : $user->permissionId;
	}

	public function getName()
	{
		$this->loadEmployeeDetails();
		if ($this->_arrEmployeeDetails === NULL)
		{
			return NULL;
		}
		$f = $this->_arrEmployeeDetails['FirstName'];
		$l = $this->_arrEmployeeDetails['LastName'];
		$n = ($f ? $f : '') . ($f && $f ? ' ' : '') . ($l ? $l : '');
		return $n ? $n : $this->_arrEmployeeDetails['UserName'];
	}

	private function loadEmployeeDetails()
	{
		if (!$this->_loadedEmployeeDetails)
		{
			$this->_loadedEmployeeDetails = TRUE;
			$arrWhere = array("Id" => $this->employeeId);
			$arrColumns = array(
				'FirstName',
				'LastName',
				'UserName',
				'Email',
				'Phone',
				'Mobile',
				'Extension',
				'Privileges',
				'Archived',
			);
			$selEmployee = new StatementSelect(
				"Employee", 
				$arrColumns, 
				$arrWhere);
			if (($outcome = $selEmployee->Execute($arrWhere)) === FALSE)
			{
				throw new Exception("Failed to check for existing user: " . $selEmployee->Error());
			}
			if (!$outcome)
			{
				throw new Exception("No employee record exists for ticketing system user " . $this->id);
			}
			$this->_arrEmployeeDetails  = $selEmployee->Fetch();
		}
	}

	public static function getCurrentUser()
	{
		return self::getForEmployeeId(Flex::getUserId());
	}

	public function isAdminUser()
	{
		return $this->permissionId === TICKETING_USER_PERMISSION_ADMIN;
	}

	public function isNormalUser()
	{
		return $this->permissionId === TICKETING_USER_PERMISSION_USER;
	}

	public function isUser()
	{
		return $this->permissionId !== TICKETING_USER_PERMISSION_NONE;
	}

	public static function currentUserIsTicketingUser()
	{
		return self::getCurrentUser()->isUser();
	}

	public static function currentUserIsTicketingAdminUser()
	{
		return self::getCurrentUser()->isAdminUser();
	}

	public static function getForEmployeeId($intEmployeeId)
	{
		$user = self::getFor("employee_id = <EmployeeId>", array("EmployeeId" => $intEmployeeId));
		if ($user == NULL)
		{
			$user = new Ticketing_User(array('employee_id' => $intEmployeeId, 'permission_id' => TICKETING_USER_PERMISSION_NONE));
			$user->_saved = FALSE;
		}
		return $user;
	}

	public static function getForCorrespondence(Ticketing_Correspondance $objCorrespondence)
	{
		return Ticketing_User::getForId($objCorrespondence->userId);
	}

	public static function getForTicket(Ticketing_Ticket $objTicket)
	{
		return Ticketing_User::getForId($objTicket->ownerId);
	}

	private static function getFor($where, $arrWhere, $bolAsArray=FALSE)
	{
		$selUsers = new StatementSelect(
			"ticketing_user", 
			self::getColumns(), 
			$where);
		if (($outcome = $selUsers->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to check for existing user: " . $selUsers->Error());
		}

		$records = array();
		while ($props = $selUsers->Fetch())
		{
			if (!array_key_exists($props['id'], self::$cache))
			{
				self::$cache[$props['id']] = new Ticketing_User($props);
			}
			$records[] = self::$cache[$props['id']];
			if (!$bolAsArray)
			{
				return $records[0];
			}
		}
		return $records;
	}

	public function autoReply()
	{
		return $this->autoReply === ACTIVE_STATUS_ACTIVE;
	}

	public static function getForId($id)
	{
		if (array_key_exists($id, self::$cache))
		{
			return self::$cache[$id];
		}
		return self::getFor("id = <Id>", array("Id" => $id));
	}

	protected static function getColumns()
	{
		return array(
			'id',
			'employee_id',
			'permission_id',
		);
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
			$statement = new StatementInsert('ticketing_user', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['Id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_user', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save user details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;
		return TRUE;
	}

	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{$name} = $value;
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
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
}

?>
