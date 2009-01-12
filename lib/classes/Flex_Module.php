<?php

//----------------------------------------------------------------------------//
// Flex_Module
//----------------------------------------------------------------------------//
/**
 * Flex_Module
 *
 * Models a single flex_module record.  Also includes other 'flex module' functionality
 *
 * Models a single flex_module record.  Also includes other 'flex module' functionality
 *
 * @class	Flex_Module
 */
class Flex_Module
{
	private $id				= NULL;
	private $name			= NULL;
	private $description	= NULL;
	private $active			= NULL;
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
	// getFor
	//------------------------------------------------------------------------//
	/**
	 * getFor()
	 *
	 * Retrieves the Flex_Module object for the id given
	 * 
	 * Retrieves the Flex_Module object for the id given
	 *
	 * @param		int		$intId	id of the flex_module record defining whether or not the module is active
	 * 								this will be the same value as the flex_module constant
	 * @return		mixed	Flex_Module object	: the flex_module record could be found
	 * 						NULL				: the record could not be found
	 * @method
	 */
	public static function getFor($intId)
	{
		static $arrModules;
		if (!isset($arrModules))
		{
			// Retrieve the details of all the modules defined in the database
			$arrModules = array();
				
			$selFlexModules = new StatementSelect("flex_module", self::getColumns(), "TRUE", "id ASC");
			
			if (($outcome = $selFlexModules->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all flex module declarations: ". $selFlexModules->Error());
			}
	
			while ($arrModule = $selFlexModules->Fetch())
			{
				$arrModules[$arrModule['id']] = new self($arrModule);
			}
		}
		
		if (array_key_exists($intId, $arrModules))
		{
			return $arrModules[$intId];
		}
		
		// The module could not be found
		return NULL;
	}

	//------------------------------------------------------------------------//
	// isActive
	//------------------------------------------------------------------------//
	/**
	 * isActive()
	 *
	 * Used to test if a module is active or not
	 * 
	 * Used to test if a module is active or not
	 *
	 * @param		int		$intId	id of the flex_module record defining whether or not the module is active
	 * 								this will be the same value as the flex_module constant
	 * @return		boolean			TRUE if the flex_module record could be found AND is active.
	 * 								FALSE if the flex_module could not be found, OR if it could be found, but is not active
	 * @method
	 */
	public static function isActive($intId)
	{
		$objModule = self::getFor($intId);
		if ($objModule === NULL)
		{
			// The module could not be found
			return FALSE;
		}
		return $objModule->active;
	}

	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the flex_module table
	 * 
	 * Returns array defining the columns of the flex_module table
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
						"const_name",
						"active"
					);
	}


	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Flex_Module object
	 * 
	 * Initialises the Flex_Module object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of flex_module table
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
