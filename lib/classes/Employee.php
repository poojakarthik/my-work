<?php

//----------------------------------------------------------------------------//
// Employee
//----------------------------------------------------------------------------//
/**
 * Employee
 *
 * Models an Employee
 *
 * Models an Employee
 *
 * @class	Employee
 */
class Employee extends ORM_Cached
{
	const SYSTEM_EMPLOYEE_ID = USER_ID;
	
	/*private $id				= NULL;
	private $firstName		= NULL;
	private $lastName		= NULL;
	private $username		= NULL;
	private $password		= NULL;
	private $phone			= NULL;
	private $mobile			= NULL;
	private $extension		= NULL;
	private $email			= NULL;
	private $dob			= NULL;
	private $sessionId		= NULL;
	private $sessionExpire	= NULL;
	private $session		= NULL;
	private $karma			= NULL;
	private $pabloSays		= NULL;
	private $privileges		= NULL;
	private $archived		= NULL;
	private $userRoleId		= NULL;
	private $isGod			= NULL;*/
	//protected			$_arrOperations;
	
	protected 			$_strTableName			= "Employee";
	protected static	$_strStaticTableName	= "Employee";

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @param		array	$arrProperties 	Optional.  Associative array defining a Employee with keys for each field of the Employee table
	 * @return		void
	 * @constructor
	 */
	/*private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}*/

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement	Name of the statement
	 * 
	 * @return	Statement					The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}

	// Retrives the most recent $intMaxRecords from the employee_account_log table for this employee
	// Returns indexed array of records, else empty array
	// The records will be sorted in descending order of employee_account_log.viewed_on
	public function getAccountHistory($intMaxRecords)
	{
		$arrColumns = array("id",
							"account_id",
							"contact_id",
							"viewed_on"
							);
		$selEmployeeAccountAudit = new StatementSelect("employee_account_log", $arrColumns, "employee_id = <EmployeeId>", "viewed_on DESC", "$intMaxRecords");
		
		if (($intRecCount = $selEmployeeAccountAudit->Execute(array("EmployeeId" => $this->id))) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve employee_account_log records with Employee Id {$this->id}: ". $selEmployeeAccountAudit->Error());
		}
		if ($intRecCount == 0)
		{
			// No records were returned
			return NULL;
		}
		return $selEmployeeAccountAudit->FetchAll();
	}
	
	// Either of these can be null, but they can't both be null
	// It assumes $intAccountId and $intContactId are both valid or NULL, but they can't both be NULL
	// throws exception on failure
	public function recordCustomerInAccountHistory($intAccountId, $intContactId)
	{
		Employee_Account_Log::createIfNotExistsForToday($this->id, $intAccountId, $intContactId);
	}
	
	// Will return their username, if they don't have a first and a last name
	// If they don't even have a user name, then it will return "Employee Id: <EmployeeId>"
	public function getName()
	{
		if ($this->firstName == "" && $this->lastName == "")
		{
			return ($this->username == "")? "Employee Id: {$this->id}" : $this->username;
		}
		else
		{
			return "{$this->firstName} {$this->lastName}";
		}
	}
	
	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the Employee object with the id specified
	 * 
	 * Returns the Employee object with the id specified
	 *
	 * @param	int 		$intId				id of the employee to return		
	 * @param	bool		[ $bolSilentFail ]	defaults to false.  If true then the function will return NULL if the employee cannot be found.
	 * 											If false then the function will throw an exception when the employee cannot be found
	 * 
	 * @return	mixed 		Employee	: if $intId is a valid Employee id
	 * 						NULL		: if $intId is not a valid Employee id, and $bolSilentFail == true	
	 * @method
	 */
	/*public static function getForId($intId, $bolSilentFail=false)
	{
		static $selEmployee;
		if (!isset($selEmployee))
		{
			$selEmployee = new StatementSelect("Employee", self::getColumns(), "Id = <EmployeeId>");
		}
		if (($intRecCount = $selEmployee->Execute(array("EmployeeId" => $intId))) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve Employee with id $intId: ". $selEmployee->Error());
		}
		if ($intRecCount == 0)
		{
			// The Employee doesn't exist
			if ($bolSilentFail)
			{
				return NULL;
			}
			else
			{
				throw new Exception("Could not find employee with id: $intId");
			}
		}
		
		// The employee must exist
		return new self($selEmployee->Fetch());
	}*/
	
	// Returns all employees that have the permissions passed
	// Multiple permissions can be bitwise ORed together
	// This will always return an array, however the array can be empty
	public static function getAllWithPermissions($intPermissions)
	{
		$selEmployee = new StatementSelect("Employee", self::getColumns(), "Privileges & $intPermissions = $intPermissions");
		
		if ($selEmployee->Execute() === FALSE)
		{
			throw new Exception_Database("Failed to retrieve Employees with permissions: $intPermissions - ". $selEmployee->Error());
		}
		
		$arrRecordSet	= $selEmployee->FetchAll();
		$arrEmployees	= array();

		foreach ($arrRecordSet as $arrRecord)
		{
			$arrEmployees[$arrRecord['id']] = new self($arrRecord);
		}
		return $arrEmployees;
	}
	
	public function getPermittedOperations()
	{
		if (!isset($this->_arrOperations))
		{
			// Calculate a list of all atomic Operations this profile includes
			$strEffectiveDatetime	= Data_Source_Time::currentTimestamp();
			$this->_arrOperations	= array();
			
			// Get Sub-Profiles
			$selOperationProfileIds	= new StatementSelect("employee_operation_profile", "operation_profile_id", "'{$strEffectiveDatetime}' BETWEEN start_datetime AND end_datetime AND employee_id = <Id>");
			if ($selOperationProfileIds->Execute($this->toArray()) === false)
			{
				throw new Exception_Database($selOperationProfileIds->Error());
			}
			
			while ($arrOperationProfileId = $selOperationProfileIds->Fetch())
			{
				// Get the Operations for this Sub-Profile & merge with current list
				$oOperationProfile	= Operation_Profile::getForId($arrOperationProfileId['operation_profile_id']);
				
				if ($oOperationProfile->isActive())
				{
					Log::getLog()->log("Employee({$this->id}): Adding operations from profile {$oOperationProfile->id}");
					$aOperations			= $oOperationProfile->getOperations();
					$this->_arrOperations	= $this->_arrOperations + $aOperations;
				}
			}
			
			// Get Direct Operations
			$selOperationIds	= new StatementSelect("employee_operation", "operation_id", "'{$strEffectiveDatetime}' BETWEEN start_datetime AND end_datetime AND employee_id = <Id>");
			if ($selOperationIds->Execute($this->toArray()) === false)
			{
				throw new Exception_Database($selOperationIds->Error());
			}
			
			while ($arrOperationId = $selOperationIds->Fetch())
			{
				$oOperation	= Operation::getForId($arrOperationId['operation_id']);
				
				if ($oOperation->isActive())
				{
					// Add this Operation to the list
					Log::getLog()->log("Employee({$this->id}): Adding operation {$oOperation->id}");
					$this->_arrOperations[$arrOperationId['operation_id']]	= $oOperation;
				}
			}
		}
		return $this->_arrOperations;
	}
	
	public function getOperations()
	{
		// Calculate a list of all atomic Operations this profile includes
		$sEffectiveDatetime	= Data_Source_Time::currentTimestamp();
		$aOperations		= array();
		
		// Get Operations
		$oOperationIds	= new StatementSelect("employee_operation", "operation_id", "'{$sEffectiveDatetime}' BETWEEN start_datetime AND end_datetime AND employee_id = {$this->id}");
		if ($oOperationIds->Execute($this->toArray()) === false)
		{
			throw new Exception_Database($oOperationIds->Error());
		}
		while ($aOperationId = $oOperationIds->Fetch())
		{
			$oOperation	= Operation::getForId($aOperationId['operation_id']);
			
			if ($oOperation->isActive())
			{
				// Add this Operation to the list
				$aOperations[$aOperationId['operation_id']]	= $oOperation;
			}
		}
		
		return $aOperations;
	}
	
	public function getOperationProfiles()
	{
		// Calculate a list of all atomic Operations this profile includes
		$sEffectiveDatetime	= Data_Source_Time::currentTimestamp();
		$aOperationProfiles	= array();
		
		// Get Profiles
		$oOperationProfileIds	= new StatementSelect("employee_operation_profile", "operation_profile_id", "'{$sEffectiveDatetime}' BETWEEN start_datetime AND end_datetime AND employee_id = {$this->id}");
		if ($oOperationProfileIds->Execute($this->toArray()) === false)
		{
			throw new Exception_Database($oOperationProfileIds->Error());
		}
		while ($aOperationProfileId = $oOperationProfileIds->Fetch())
		{
			$oOperationProfile	= Operation_Profile::getForId($aOperationProfileId['operation_profile_id']);
			
			if ($oOperationProfile->isActive())
			{
				$aOperationProfiles[$aOperationProfileId['operation_profile_id']]	= $oOperationProfile;
			}
		}
		
		return $aOperationProfiles;
	}
	
	public function getForUserName($sUserName)
	{
		$oSelect	= new StatementSelect('Employee', 'Id', 'UserName = <UserName>');
		$oSelect->Execute(array('UserName' => $sUserName));
		$iId	= $oSelect->Fetch();
		return $iId;
	}
	
	public function getDataReports()
	{
		return DataReport::getForEmployeeId($this->Id);
	}
	
	public function isGod()
	{
		return ($this->is_god == 1);
	}
	
	public function getOverdueFollowUpCount()
	{
		$oDueDateTimeConstraint			= new StdClass();
		$iNow							= time();
		$oDueDateTimeConstraint->mTo	= date('Y-m-d H:i:s', $iNow);
		
		return 	FollowUp::countFor(
					array(
						'assigned_employee_id'	=> $this->Id,				// Owner is the employee
						'followup_closure_id'	=> 'NULL',					// Not closed
						'due_datetime'			=> $oDueDateTimeConstraint	// Due date before now (overdue)
					)
				);
	}
	
	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the Employee table
	 * 
	 * Returns array defining the columns of the Employee table
	 *
	 * @return		array	
	 * @method
	 */
	/*protected static function getColumns()
	{
		return array(
						"id"			=> "Id",
						"firstName"		=> "FirstName",
						"lastName"		=> "LastName",
						"username"		=> "UserName",
						"password"		=> "PassWord",
						"phone"			=> "Phone",
						"mobile"		=> "Mobile",
						"extension"		=> "Extension",
						"email"			=> "Email",
						"dob"			=> "DOB",
						"sessionId"		=> "SessionId",
						"sessionExpire"	=> "SessionExpire",
						"session"		=> "Session",
						"karma"			=> "Karma",
						"pabloSays"		=> "PabloSays",
						"privileges"	=> "Privileges",
						"archived"		=> "Archived",
						"userRoleId"	=> "user_role_id",
						"isGod"			=> "is_god",
					);
	}*/

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Employee object
	 * 
	 * Initialises the Employee object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of Employee table
	 * @return		void	
	 * @method
	 */
	/*private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{self::tidyName($name)} = $value;
		}
	}*/

	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * accessor method
	 * 
	 * accessor method
	 *
	 * @param	string	$strName	name of property to get. in either of the formats xxxYyyZzz or xxx_yyy_zzz 
	 * @return	void
	 * @method
	 */
	/*public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}*/
	
	//------------------------------------------------------------------------//
	// tidyName
	//------------------------------------------------------------------------//
	/**
	 * tidyName()
	 *
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * 
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * If the string is already in the xxxYxxZzz format, then it will not be changed
	 *
	 * @param	string	$strName
	 * @return	string
	 * @method
	 */
	/*private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}*/
	
	/*public function toArray()
	{
		$arrEmployee	= array();
		$arrColumns		= self::getColumns();
		foreach ($arrColumns as $strVariableName=>$strDBField)
		{
			$arrEmployee[$strDBField]	= $this->__get($strVariableName);
		}
		return $arrEmployee;
	}*/
	
}

?>
