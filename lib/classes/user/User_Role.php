<?php

//----------------------------------------------------------------------------//
// User_Role
//----------------------------------------------------------------------------//
/**
 * User_Role
 *
 * Models a user role
 *
 * Models a user role
 *
 * @class	User_Role
 */
class User_Role
{
	private $id				= NULL;
	private $name			= NULL;
	private $description	= NULL;
	private $constName		= NULL;

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
	 * @param		array	$arrProperties 	Optional.  Associative array defining a user role with keys for each field of the user_role table
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
	 * Returns array of User_Role objects representing each user role in Flex
	 * 
	 * Returns array of User_Role objects representing each user role in Flex
	 * This is an associative array with the key being the id of user_role record.
	 * The array is ordered by User Role name
	 *
	 * @return		array of User_Role objects	
	 * @method
	 */
	public static function getAll()
	{
		static $arrUserRoles;
		if (!isset($arrUserRoles))
		{
			$arrUserRoles = array();
	
			$arrColumns = self::getColumns();
			
			$selUserRoles = new StatementSelect("user_role", $arrColumns, "", "name ASC");
			if (($outcome = $selUserRoles->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all User Roles: ". $selUserRoles->Error());
			}
	
			while ($arrUserRole = $selUserRoles->Fetch())
			{
				$arrUserRoles[$arrUserRole['id']] = new self($arrUserRole);
			}
		}
		
		return $arrUserRoles;
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the User_Role object with the id specified
	 * 
	 * Returns the User_Role object with the id specified
	 *
	 * @param	int 				$intId		id of the user role to return		
	 * @return	mixed 				User_Role	: if $intId is a valid user_role_id
	 * 								NULL		: if $intId is not a valid user_role_id	
	 * @method
	 */
	public static function getForId($intId)
	{
		$arrUserRoles = self::getAll();
		if (array_key_exists($intId, $arrUserRoles))
		{
			return $arrUserRoles[$intId];
		}
		else
		{
			// UserRole does not exist
			return NULL;
		}
		
		
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
						"const_name"
					);
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the User_Role object
	 * 
	 * Initialises the User_Role object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of user_role table
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
