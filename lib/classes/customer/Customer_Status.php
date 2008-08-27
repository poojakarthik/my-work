<?php

	
//TODO! Add the test functions to this class as well as the calculate function
// The functions will get the account object passed to them



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
	private $id							= NULL;
	private $name						= NULL;
	private $description				= NULL;
	private $defaultActionDescription	= NULL;
	private $precedence					= NULL;
	private $test						= NULL;
	private $cssClass					= NULL;
	private $arrActionDescriptions		= NULL;
	
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
	 * @param		int		$intUserId		Optional. user_role_id of the user dealing with the customer
	 * @return		string	
	 * @method
	 */
	public function getActionDescription($intUserRole=NULL)
	{
		if ($intUserRole === NULL)
		{
			// Return the default action description
			return $this->defaultActionDescription;
		}
		if (!is_array($this->arrActionDescriptions))
		{
			// Load the actions in from the database
			$selActions = new StatementSelect("customer_status_action", array("role_id", "description"), "customer_status_id = <StatusId>");
			if (($outcome = $selActions->Execute(array("StatusId" => $this->id))) === FALSE)
			{
				throw new Exception("Failed to retrieve User Actions for Customer Status {$this->name} : ". $selActions->Error());
			}
			
			$this->arrActionDescriptions = array();
			while ($arrAction = $selActions->Fetch())
			{
				$this->arrActionDescriptions[$arrAction['role_id']] = $arrAction['description'];
			}
		}
		
		if (array_key_exists($intUserRole, $this->arrActionDescriptions))
		{
			// Found it
			return $this->arrActionDescriptions[$intUserRole];
		}
		else
		{
			// No Action Description could be found specific to this user
			return $this->defaultActionDescription;
		}
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
