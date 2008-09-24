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

	// Retrives the most recent $intMaxRecords from the EmployeeAccountAudit table for this employee
	// Returns indexed array of records, else empty array
	// The records will be sorted in descending order of EmployeeAccountAudit.RequestedOn
	public function getAccountHistory($intMaxRecords)
	{
		$arrColumns = array("Id",
							"Account",
							"Contact",
							"RequestedOn" 
							);
		$selEmployeeAccountAudit = new StatementSelect("EmployeeAccountAudit", $arrColumns, "Employee = <EmployeeId>", "RequestedOn DESC", "$intMaxRecords");
		
		if (($intRecCount = $selEmployeeAccountAudit->Execute(array("EmployeeId" => $this->id))) === FALSE)
		{
			throw new Exception("Failed to retrieve EmployeeAccountAudit records with Employee Id {$this->id}: ". $selEmployeeAccountAudit->Error());
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
			throw new Exception("Cannot log Customer in EmployeeAccountAudit when both AccountId and ContactId are NULL");
		}
		
		$arrData = array(	"Employee"		=> $this->id,
							"Account"		=> NULL,
							"Contact"		=> NULL,
							"RequestedOn"	=> new MySQLFunction("NOW()")
						);
		$insEmployeeAccountAudit = new StatementInsert("EmployeeAccountAudit", $arrData);
		
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
		
		$arrData['Account'] = $intAccountId;
		$arrData['Contact'] = $intContactId;
		
		if ($insEmployeeAccountAudit->Execute($arrData) === FALSE)
		{
			throw new Exception("Could not insert record into EmployeeAccountAudit table - ". $insEmployeeAccountAudit->Error());
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
	 * @param	int 				$intId		id of the employee to return		
	 * @return	mixed 				Employee	: if $intId is a valid Employee id
	 * 								NULL		: if $intId is not a valid Employee id	
	 * @method
	 */
	public static function getForId($intId)
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
			return NULL;
		}
		
		// The employee must exist
		return new Employee($selEmployee->Fetch());
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
	
}

?>
