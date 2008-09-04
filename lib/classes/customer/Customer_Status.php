<?php

//----------------------------------------------------------------------------//
// Customer_Status
//----------------------------------------------------------------------------//
/**
 * Customer_Status
 *
 * Models a single customer status.  Also includes other customer status functionality
 *
 * Models a single customer status.  Also includes other customer status functionality
 *
 * @class	Customer_Status
 */
class Customer_Status
{
	const ACTION_DESCRIPTION_MAX_LENGTH = 1000;
	
	private $id							= NULL;
	private $name						= NULL;
	private $description				= NULL;
	private $defaultActionDescription	= NULL;
	private $precedence					= NULL;
	private $test						= NULL;
	private $cssClass					= NULL;
	private $_arrActionDescriptions		= NULL;
	
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
	 * @param		array	$arrProperties 	Optional.  Associative array defining a customer status with keys for each field of the customer_status table
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

	//------------------------------------------------------------------------//
	// getAll
	//------------------------------------------------------------------------//
	/**
	 * getAll()
	 *
	 * Returns array of Customer_Status objects representing each customer status in Flex
	 * 
	 * Returns array of Customer_Status objects representing each customer status in Flex
	 * This is an associative array with the key being the id of customer_status record.
	 * The objects have been added to the array in their order of precedence with the customer status
	 * with the highest precedence being append first, and so on
	 *
	 * @return		array of Customer_Status objects	
	 * @method
	 */
	public static function getAll()
	{
		static $arrCustomerStatuses;
		if (!isset($arrCustomerStatuses))
		{
			$arrCustomerStatuses = array();
	
			$arrColumns = self::getColumns();
			
			$selCustomerStatuses = new StatementSelect("customer_status", $arrColumns, "TRUE", "precedence ASC");
			if (($outcome = $selCustomerStatuses->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all Customer Statuses: ". $selCustomerStatuses->Error());
			}
	
			while ($arrCustomerStatus = $selCustomerStatuses->Fetch())
			{
				$arrCustomerStatuses[$arrCustomerStatus['id']] = new self($arrCustomerStatus);
			}
		}
		
		return $arrCustomerStatuses;
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the Customer_Status object with the id specified
	 * 
	 * Returns the Customer_Status object with the id specified
	 *
	 * @param	int 				$intId		id of the Customer Status to return		
	 * @return	Customer_Status	
	 * @method
	 */
	public static function getForId($intId)
	{
		$arrCustomerStatuses = self::getAll();
		return $arrCustomerStatuses[$intId];
	}
	
	//------------------------------------------------------------------------//
	// getAllOrderedByPrecedence
	//------------------------------------------------------------------------//
	/**
	 * getAllOrderedByPrecedence()
	 *
	 * Returns array of Customer_Status objects representing each customer status in Flex, ordered by precedence
	 * 
	 * Returns array of Customer_Status objects representing each customer status in Flex, ordered by precedence
	 * This is an associative array with the key being the id of customer_status record.
	 * The objects have been added to the array in their order of precedence with the customer status
	 * with the highest precedence being append first, and so on
	 *
	 * @return		array of Customer_Status objects	
	 * @method
	 */
	public static function getAllOrderedByPrecedence()
	{
		return self::getAll();
	}
	
	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the customer_status table
	 * 
	 * Returns array defining the columns of the customer_status table
	 *
	 * @return		array	
	 * @method
	 */
	protected static function getColumns()
	{
		return array(
						"id",
						"name",
						"description",
						"default_action_description",
						"precedence",
						"test"
					);
	}

	//------------------------------------------------------------------------//
	// getActionDescription
	//------------------------------------------------------------------------//
	/**
	 * getActionDescription()
	 *
	 * Returns the action description that the user should follow when dealing with a customer of this Customer Status
	 * 
	 * Returns the action description that the user should follow when dealing with a customer of this Customer Status
	 *
	 * @param		int		$intUserRole		Optional. user_role_id of the user dealing with the customer
	 * @return		string	
	 * @method
	 */
	public function getActionDescription($intUserRole=NULL)
	{
		if ($intUserRole == NULL)
		{
			// Return the default action description
			return $this->defaultActionDescription;
		}
		
		$this->_loadAllActionDescriptions();
		
		if (array_key_exists($intUserRole, $this->_arrActionDescriptions))
		{
			// Found it
			return $this->_arrActionDescriptions[$intUserRole];
		}
		else
		{
			// No Action Description could be found specific to this user
			return $this->defaultActionDescription;
		}
	}

	// Returns TRUE if a specific action description exists for $intUserRole, else FALSE
	public function hasActionDescription($intUserRole)
	{
		$this->_loadAllActionDescriptions();
		return array_key_exists($intUserRole, $this->_arrActionDescriptions);
	}
	
	// Returns the id of the customer_status_history record if it could be found, FALSE if it can't be found
	// If it could be found then it is added/updated in $this->_arrActionDescriptions
	// If it couldn't be found then it is removed from $this->_arrActionDescriptions
	private function _loadSingleActionDescription($intUserRole)
	{
		static $selAction;
		if (!is_array($this->_arrActionDescriptions))
		{
			$this->_loadAllActionDescriptions();
		}
		
		if (!isset($selAction))
		{
			$selAction = new StatementSelect("customer_status_action", array("id", "user_role_id", "description"), "customer_status_id = <CustomerStatusId> AND user_role_id = <UserRoleId>");
		}
		
		if (($intRecCount = $selAction->Execute(array("CustomerStatusId" => $this->id, "UserRoleId" => $intUserRole))) === FALSE)
		{
			throw new Exception("Failed to retrieve User Action for Customer Status {$this->name}, User Role Id: $intUserRole - ". $selAction->Error());
		}
		if ($intRecCount > 1)
		{
			// There are multiple records in the customer_status_action table refering to this customer_status_id/user_role_id combination
			throw new Exception("Retrieved multiple records from the customer_status_action table where customer status id = {$this->id} and user role id = $intUserRole");
		}
		if ($intRecCount == 0)
		{
			// No record was returned
			return FALSE;
		}
		
		// A single record was found
		$arrAction = $selAction->Fetch();
		
		$this->_arrActionDescriptions[$arrAction['user_role_id']] = $arrAction['description'];
		return $arrAction['id'];
	}
	
	// inserts/updates the appropraite customer_status_action record, and updates $this->_arrActionDescriptions
	// it assumes both $intUserRole and $strDescription are valid
	// if $intUserRole === NULL then the default ActionDescription is updated (customer_status table), and $this->defaultActionDescription is also updated 
	public function setActionDescription($intUserRole=NULL, $strDescription)
	{
		static $updDefaultAction;
		static $updAction;
		static $insAction;
		
		if ($intUserRole === NULL)
		{
			// Update the default action description
			$arrFields = array(	"id" => $this->id,
								"default_action_description" => $strDescription
							);
			if (!isset($updDefaultAction))
			{
				$updDefaultAction = new StatementUpdateById("customer_status", $arrFields);
			}
			
			// Make the update 
			if (($intRecordsAffected = $updDefaultAction->Execute($arrFields)) === FALSE)
			{
				// Update failed
				throw new Exception("Failed to update customer_status.default_action_description for where customer_status.id = {$this->id} - ". $updDefaultAction->Error());
			}
			
			// Update the object
			$this->defaultActionDescription = $strDescription;
			return;
		}
		
		// Check if the record already exists
		if (($intId = $this->_loadSingleActionDescription($intUserRole)) === FALSE)
		{
			// A record doesn't already exist, insert it
			$arrFields = array(	"id"					=> NULL,
								"customer_status_id"	=> $this->id,
								"user_role_id"			=> $intUserRole,
								"description"			=> $strDescription
							);
			if (!isset($insAction))
			{
				$insAction = new StatementInsert("customer_status_action", $arrFields);
			}
			
			// Make the insert
			if (($intNewId = $insAction->Execute($arrFields)) === FALSE)
			{
				throw new Exception("Failed to insert record into customer_status_action table for customer status id: {$this->id}, user role id: $intUserRole - ". $insAction->Error());
			}
		}
		else
		{
			// The record already exists, update it
			$arrFields = array(	"id"					=> $intId,
								"description"			=> $strDescription
							);
			if (!isset($updAction))
			{
				$updAction = new StatementUpdateById("customer_status_action", $arrFields);
			}
			
			// Make the update
			if (($intRecordsAffected = $updAction->Execute($arrFields)) === FALSE)
			{
				// Update failed
				throw new Exception("Failed to update record in customer_status_action table for customer status id: {$this->id}, user role id: $intUserRole - ". $updAction->Error());
			}
		}
		
		// Update $this->_arrActionDescriptions (it has already been initialised)
		$this->_arrActionDescriptions[$intUserRole] = $strDescription;
	}

	//returns TRUE/FALSE signifying validity. and if invalid, $arrErrors will have descriptions of the errors
	//appended to it as individual error messages.
	public static function isValidActionDescription($strDescription, &$arrErrors)
	{
		// A description is valid if it is a string which is not empty and does not exceed the length of Customer_Status::ACTION_DESCRIPTION_MAX_LENGTH
		if (!is_string($strDescription))
		{
			$arrErrors[] = "not a string";
			return FALSE;
		}
		$intLength = strlen($strDescription);
		if ($intLength == 0)
		{
			$arrErrors[] = "empty string";
			return FALSE;
		}
		if ($intLength > self::ACTION_DESCRIPTION_MAX_LENGTH)
		{
			$arrErrors[] = "exceeds maximum length of ". self::ACTION_DESCRIPTION_MAX_LENGTH ." characters";
			return FALSE;
		}
		return TRUE;
	} 
	
	
	private function _loadAllActionDescriptions()
	{
		// It is assumed that the defaultActionDescription is always up to date
		static $selActions;
		
		if (!is_array($this->_arrActionDescriptions))
		{
			if (!isset($selActions))
			{
				$selActions = new StatementSelect("customer_status_action", array("user_role_id", "description"), "customer_status_id = <CustomerStatusId>");
			}
			
			// Load the actions in from the database
			if (($outcome = $selActions->Execute(array("CustomerStatusId" => $this->id))) === FALSE)
			{
				throw new Exception("Failed to retrieve User Actions for Customer Status {$this->name} : ". $selActions->Error());
			}
			$this->_arrActionDescriptions = array();
			while ($arrAction = $selActions->Fetch())
			{
				$this->_arrActionDescriptions[$arrAction['user_role_id']] = $arrAction['description'];
			}
		}
	}
	
	// Removes the customer_status_action record where customer_status_id = $this->id and user_role_id = $intUserRole
	// returns void.  throws exception on error
	// Note: the default action description can not be deleted
	public function deleteActionDescription($intUserRole)
	{
		$qryDeleteAction = new Query();
		
		$strDeleteQuery = "DELETE FROM customer_status_action WHERE customer_status_id = {$this->id} AND user_role_id = $intUserRole";
		
		if ($qryDeleteAction->Execute($strDeleteQuery) === FALSE)
		{
			// Delete Failed
			throw new Exception("Failed to delete record in customer_status_action table for customer status id: {$this->id}, user role id: $intUserRole - ". $qryDeleteAction->Error());
		}
		
		// The delete worked, now remove it from $this->_arrActionDescriptions if it exists
		if (is_array($this->_arrActionDescriptions) && array_key_exists($intUserRole, $this->_arrActionDescriptions))
		{
			unset($this->_arrActionDescriptions[$intUserRole]);
		}
	}
	
	// returns $this->arrActionDescriptions (key = user_role_id, value = description)
	// This does not return the detault Action Description
	public function getAllActionDescriptions()
	{
		// It is assumed that the defaultActionDescription is always up to date
		$this->_loadAllActionDescriptions();
				
		return $this->_arrActionDescriptions;
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Customer_Status object
	 * 
	 * Initialises the Customer_Status object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of customer_status table
	 * @return		void	
	 * @method
	 */
	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{self::tidyName($name)} = $value;
		}
		
		// Save the css class name
		$this->cssClass = "customer-status-". str_replace('_', '-', strtolower($arrProperties['name']));
		
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
		if ($strName[0] === '_')
		{
			// Don't allow access to data attributes that start with '_'
			return NULL;
		}
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
