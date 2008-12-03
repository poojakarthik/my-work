<?php

//TODO! Convert this class so that it uses the original StatementSelect/Update/Insert database connection classes.  It currently uses MDB2
// This class currently uses the MDB2 database connection classes.  All code that interacts with the Flex database should be using the Orginal "StatementSelect" functionality, so that
// it can all be used within the one Transaction.  When this class starts getting used to add new Sale Types, it will have to be updated to use the StatementSelect/Update/Insert 
// classes

//----------------------------------------------------------------------------//
// Sale_Type
//----------------------------------------------------------------------------//
/**
 * Sale_Type
 *
 * Models a sale type
 *
 * Models a sale type
 *
 * @class	Sale_Type
 */
class Sale_Type
{
	// Constants (these should reflect the state of the Sale_Type table)
	const NEW_CUSTOMER		= 1;
	const EXISTING_CUSTOMER	= 2;
	const WIN_BACK			= 3;
	
	private $id				= NULL;
	private $name			= NULL;
	private $description	= NULL;
	
	private static $_arrCache		= NULL;
	private static $_arrConstMap	= NULL;

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
	 * @param		array	$arrProperties 	Optional.  Associative array with keys for each field of the associated table
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
	 * Returns array of Sale_Type objects representing each Sale Type in Flex
	 * 
	 * Returns array of Sale_Type objects representing each Sale Type in Flex
	 * This is an associative array with the key being the id of sale_type record.
	 * The array is ordered by sale_type.name
	 *
	 * @return		array of Sale_Type objects	
	 * @method
	 */
	public static function getAll($bolForceRefresh=FALSE)
	{
		if (self::$_arrCache === NULL || $bolForceRefresh)
		{
			$objDB = Data_Source::get();
			
			$strColumns = self::getColumns(TRUE);
			
			$strQuery = "SELECT $strColumns FROM sale_type ORDER BY name ASC";
			
			$mixResult = $objDB->queryAll($strQuery, self::getColumnDataTypes(), MDB2_FETCHMODE_ASSOC);
			if (PEAR::isError($mixResult))
			{
				throw new Exception("Failed to retrieve sale type records. ". $mixResult->getMessage());
			}
			
			self::$_arrCache	= array();
			self::$_arrConstMap	= array();
			foreach ($mixResult as $arrRecord)
			{
				self::$_arrCache[$arrRecord['id']] = new self($arrRecord);
				$strConstName = strtoupper(str_replace(' ', '_', $arrRecord['name']));
				self::$_arrConstMap[$strConstName] = $arrRecord['id'];
			}
		}
		
		return self::$_arrCache;
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the Sale_Type object with the id specified
	 * 
	 * Returns the Sale_Type object with the id specified
	 *
	 * @param	int 				$intId		id of the dealer_status record to return		
	 * @return	mixed 				Sale_Type	: if $intId is a valid sale_type id
	 * 								NULL		: if $intId is not a valid sale_type id	
	 * @method
	 */
	public static function getForId($intId)
	{
		$arrObjects = self::getAll();
		return array_key_exists($intId, $arrObjects)? $arrObjects[$intId] : NULL;
	}

	// This will be used to refere to the constants
	// $strConstName should reflect the name column of a record of the dealer_status table, in all upper case, and underscores for spaces
	// For example the record with name = "Active" will have a const name of "ACTIVE"
	// This will return the id of the corresponding record
	// THIS ISN'T A VERY NICE WAY OF DOING THIS
	public static function constValue($strConstName)
	{
		// Check that the passed name is a constant
		if (self::$_arrConstMap === NULL)
		{
			// Retrieve all the records from the database
			self::getAll();
		}
		if (array_key_exists($strConstName, self::$_arrConstMap))
		{
			return self::$_arrCache[self::$_arrConstMap[$strConstName]]->id;
		}
		else
		{
			// The record with name mapping to $strConstName, could not be found in the table
			throw new Exception("Class: ". __CLASS__ . " does not contain constant: '$strConstName'");
		}
	}
	
	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the table
	 * 
	 * Returns array defining the columns of the table
	 *
	 * @param	bool	$bolAsString	optional, defaults to FALSE.  If set to TRUE then the columns are returned as a string
	 * 									in the form, "Column1 AS 'Alias1' [, Column2 AS 'Alias2']". If FALSE then an array
	 * 									is returned with the key being aliases and values being the columns
	 *
	 * @return		array or string	
	 * @method
	 */
	protected static function getColumns($bolAsString=FALSE)
	{
		$arrColumns =  array(
						"id"			=> "id",
						"name"			=> "name",
						"description"	=> "description"
					);
		if ($bolAsString)
		{
			$arrColumnParts = array();
			foreach ($arrColumns as $strAlias=>$strColumn)
			{
				$arrColumnParts[] = "$strColumn AS '$strAlias'";
			}
			return implode(", ", $arrColumnParts);
		}
		
		return $arrColumns;
	}


	// The keys are the tidy names and the values are the actual table column names
	protected static function getColumnDataTypes()
	{
		return array(
			"id"					=> "integer",
			"name"					=> "text",
			"description"			=> "text"
		);
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the object
	 * 
	 * Initialises the object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of the associated table
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
