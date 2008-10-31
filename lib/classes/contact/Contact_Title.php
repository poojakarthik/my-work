<?php

//----------------------------------------------------------------------------//
// Contact_Title
//----------------------------------------------------------------------------//
/**
 * Contact_Title
 *
 * Models a record of the contact_title table, and encapsulates other contact_title related functionality
 *
 * Models a record of the contact_title table, and encapsulates other contact_title related functionality
 *
 * @class	Contact_Title
 */
class Contact_Title
{
	private $id					= NULL;
	private $name				= NULL;
	private $description		= NULL;
	
	private static $_arrCache	= NULL;

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
	 * Returns array of Contact_Title objects representing each contact_title record in Flex
	 * 
	 * Returns array of Contact_Title objects representing each contact_title record in Flex
	 * This is an associative array with the key being the id of contact_title record.
	 *
	 * @return		array of Contact_Title objects	
	 * @method
	 */
	public static function getAll($bolForceRefresh=FALSE, $strOrderByClause='name ASC')
	{
		if (self::$_arrCache === NULL || $bolForceRefresh)
		{
			$objDB = Data_Source::get();
			
			$strColumns = self::getColumns(TRUE);
			
			$strQuery = "SELECT $strColumns FROM contact_title ORDER BY $strOrderByClause";
			
			$mixResult = $objDB->queryAll($strQuery, NULL, MDB2_FETCHMODE_ASSOC);
			if (PEAR::isError($mixResult))
			{
				throw new Exception("Failed to retrieve contact_title records. ". $mixResult->getMessage());
			}
			
			self::$_arrCache = array();
			foreach ($mixResult as $arrRecord)
			{
				self::$_arrCache[$arrRecord['id']] = new self($arrRecord);
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
	 * Returns the Contact_Title object with the id specified
	 * 
	 * Returns the Contact_Title object with the id specified
	 *
	 * @param	int 				$intId			id of the contact_title record to return		
	 * @return	mixed 				Contact_Title	: if $intId is a valid contact_title id
	 * 								NULL			: if $intId is not a valid contact_title id	
	 * @method
	 */
	public static function getForId($intId)
	{
		$arrObjects = self::getAll();
		return array_key_exists($intId, $arrObjects)? $arrObjects[$intId] : NULL;
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

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Dealer_Status object
	 * 
	 * Initialises the Dealer_Status object
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
