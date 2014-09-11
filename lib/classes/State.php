<?php

//TODO! Convert this class so that it uses the original StatementSelect/Update/Insert database connection classes.  It currently uses MDB2
// This class currently uses the MDB2 database connection classes.  All code that interacts with the Flex database should be using the Orginal "StatementSelect" functionality, so that
// it can all be used within the one Transaction.  When this class starts getting used to add new Countries, it will have to be updated to use the StatementSelect/Update/Insert 
// classes


//----------------------------------------------------------------------------//
// State
//----------------------------------------------------------------------------//
/**
 * State
 *
 * Models a record of the state table, and encapsulates other state related functionality
 *
 * Models a record of the state table, and encapsulates other state related functionality
 *
 * @class	State
 */
class State
{
	private $id					= NULL;
	private $name				= NULL;
	private $countryId			= NULL;
	private $code				= NULL;
	
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
	 * Returns array of State objects representing each geographical state in Flex
	 * 
	 * Returns array of State objects representing each geographical state in Flex
	 * This is an associative array with the key being the id of state record.
	 *
	 * @return		array of State objects	
	 * @method
	 */
	public static function getAll($strOrderByClause='name ASC')
	{
		return self::getFor(NULL, $strOrderByClause);
	}

	// Always returns an array
	private static function getFor($strWhere=NULL, $strOrderBy=NULL)
	{
		$objDB		= Data_Source::get();
		$strColumns = self::getColumns(TRUE);
		$strWhere	= ($strWhere == NULL)? "" : "WHERE $strWhere";
		$strOrderBy	= ($strOrderBy == NULL)? "" : "ORDER BY $strOrderBy";
		$strQuery	= "SELECT $strColumns FROM state $strWhere $strOrderBy;";
		$mixResult	= $objDB->queryAll($strQuery, NULL, MDB2_FETCHMODE_ASSOC);
		if (MDB2::isError($mixResult))
		{
			throw new Exception("Failed to retrieve state records. Query: $strQuery, Message: ". $mixResult->getMessage());
		}
		
		$arrStates = array();
		foreach ($mixResult as $arrRecord)
		{
			$arrStates[$arrRecord['id']] = new self($arrRecord);
		}
		
		return $arrStates;
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns the object with the id specified
	 * 
	 * Returns the object with the id specified
	 *
	 * @param	int 				$intId	id of the associated table record to return		
	 * @return	mixed 				State	: if $intId was found
	 * 								NULL	: if $intId was not found	
	 * @method
	 */
	public static function getForId($intId)
	{
		$arrObjects = self::getFor("id = $intId");
		return (count($arrObjects) == 1)? $arrObjects[$intId] : NULL;
	}
	
	public static function getForCountry($intCountryId, $strOrderBy="name ASC")
	{
		return self::getFor("country_id = $intCountryId", $strOrderBy);
	}
	
	public static function getForCode($sCode)
	{
		$aStates	= self::getFor("code = '{$sCode}'");
		foreach ($aStates as $oState)
		{
			return $oState;
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
						"countryId"		=> "country_id",
						"code"			=> "code"
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
