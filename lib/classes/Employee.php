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
class Employee
{
	const SYSTEM_EMPLOYEE_ID = USER_ID;
	
	private $id				= NULL;
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
	private $isGod			= NULL;
	
	protected	$_arrOperations;

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
	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
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
			throw new Exception("Failed to retrieve employee_account_log records with Employee Id {$this->id}: ". $selEmployeeAccountAudit->Error());
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
		if ($intAccountId === NULL && $intContactId === NULL)
		{
			throw new Exception("Cannot log Customer in employee_account_log when both account_id and contact_id are NULL");
		}
		
		$arrData = array(	"employee_id"		=> $this->id,
							"account_id"		=> NULL,
							"contact_id"		=> NULL,
							"viewed_on"			=> new MySQLFunction("NOW()")
						);
		$insEmployeeAccountAudit = new StatementInsert("employee_account_log", $arrData);
		
		if ($intContactId !== NULL && $intAccountId === NULL)
		{
			// We have a contact, but don't have an account, use the contact's default account
			if (($objContact = Contact::getForId($intContactId)) === NULL)
			{
				// The contact cannot be found
				throw new Exception("Contact with id: $intContactId could not be found");
			}
			
			$intAccountId = $objContact->account;
		}
		
		$arrData['account_id'] = $intAccountId;
		$arrData['contact_id'] = $intContactId;
		
		if ($insEmployeeAccountAudit->Execute($arrData) === FALSE)
		{
			throw new Exception("Could not insert record into employee_account_log table - ". $insEmployeeAccountAudit->Error());
		}
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
	public static function getForId($intId, $bolSilentFail=false)
	{
		static $selEmployee;
		if (!isset($selEmployee))
		{
			$selEmployee = new StatementSelect("Employee", self::getColumns(), "Id = <EmployeeId>");
		}
		if (($intRecCount = $selEmployee->Execute(array("EmployeeId" => $intId))) === FALSE)
		{
			throw new Exception("Failed to retrieve Employee with id $intId: ". $selEmployee->Error());
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
	}
	
	// Returns all employees that have the permissions passed
	// Multiple permissions can be bitwise ORed together
	// This will always return an array, however the array can be empty
	public static function getAllWithPermissions($intPermissions)
	{
		$selEmployee = new StatementSelect("Employee", self::getColumns(), "Privileges & $intPermissions = $intPermissions");
		
		if ($selEmployee->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve Employees with permissions: $intPermissions - ". $selEmployee->Error());
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
				throw new Exception($selOperationProfileIds->Error());
			}
			while ($arrOperationProfileId = $selOperationProfileIds->Fetch())
			{
				// Get the Operations for this Sub-Profile & merge with current list
				$this->_arrOperations	= array_merge($this->_arrOperations, Operation_Profile::getForId($arrOperationProfileId['operation_profile_id'])->getOperations());
			}
			
			// Get Direct Operations
			$selOperationIds	= new StatementSelect("employee_operation", "operation_id", "'{$strEffectiveDatetime}' BETWEEN start_datetime AND end_datetime employee_id = <Id>");
			if ($selOperationIds->Execute($this->toArray()) === false)
			{
				throw new Exception($selOperationIds->Error());
			}
			while ($arrOperationId = $selOperationIds->Fetch())
			{
				// Add this Operation to the list
				$this->_arrOperations[$arrOperationId['operation_id']]	= Operation::getForId($arrOperationId['operation_id']);
			}
		}
		return $this->_arrOperations;
	}
	
	public function getOperations()
	{
		// Calculate a list of all atomic Operations this profile includes
		$strEffectiveDatetime	= Data_Source_Time::currentTimestamp();
		$arrOperations			= array();
		
		// Get Operations
		$selOperationIds	= new StatementSelect("employee_operation", "operation_id", "'{$strEffectiveDatetime}' BETWEEN start_datetime AND end_datetime AND employee_id = {$this->id}");
		if ($selOperationIds->Execute($this->toArray()) === false)
		{
			throw new Exception($selOperationIds->Error());
		}
		while ($arrOperationId = $selOperationIds->Fetch())
		{
			// Add this Operation to the list
			$arrOperations[$arrOperationId['operation_id']]	= Operation::getForId($arrOperationId['operation_id']);
		}
		
		return $arrOperations;
	}
	
	public function getOperationProfiles()
	{
		// Calculate a list of all atomic Operations this profile includes
		$strEffectiveDatetime	= Data_Source_Time::currentTimestamp();
		$arrOperationProfiles	= array();
		
		// Get Profiles
		$selOperationProfileIds	= new StatementSelect("employee_operation_profile", "operation_profile_id", "'{$strEffectiveDatetime}' BETWEEN start_datetime AND end_datetime AND employee_id = {$this->id}");
		if ($selOperationProfileIds->Execute($this->toArray()) === false)
		{
			throw new Exception($selOperationProfileIds->Error());
		}
		while ($arrOperationProfileId = $selOperationProfileIds->Fetch())
		{
			$arrOperationProfiles[$arrOperationProfileId['operation_profile_id']]	= Operation_Profile::getForId($arrOperationProfileId['operation_profile_id']);
		}
		
		return $arrOperationProfiles;
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
	protected static function getColumns()
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
	}

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
	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{self::tidyName($name)} = $value;
		}
	}

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
	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}
	
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
	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
	
	public function toArray()
	{
		$arrEmployee	= array();
		$arrColumns		= self::getColumns();
		foreach ($arrColumns as $strVariableName=>$strDBField)
		{
			$arrEmployee[$strDBField]	= $this->__get($strVariableName);
		}
		return $arrEmployee;
	}
	
}

?>
