<?php
//----------------------------------------------------------------------------//
// ORM
//----------------------------------------------------------------------------//
/**
 * ORM
 *
 * Models a record from any table, for use when a single record logically represents a single object
 *
 * Models a record from any table, for use when a single record logically represents a single object
 *
 * @class	ORM
 */
abstract class ORM
{
	protected	$_arrTidyNames	= array();
	protected	$_arrProperties	= array();
	
	protected	$_strIdField	= null;
	
	protected	$_strTableName	= NULL;
	
	protected	$_bolSaved		= FALSE;
	
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
	 * @param	array	$arrProperties 		[optional]	Associative array defining the data source record that this object will model
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the Record with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	protected function __construct($arrProperties=array(), $bolLoadById=FALSE)
	{
		// Get list of columns from Data Model
		$arrTableDefine		= DataAccess::getDataAccess()->FetchTableDefine($this->_strTableName);
		$this->_strIdField	= $arrTableDefine['Id'];
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$this->_arrProperties[$strName]					= NULL;
			$this->_arrTidyNames[self::tidyName($strName)]	= $strName;
		}
		$this->_arrProperties[$arrTableDefine['Id']]				= NULL;
		$this->_arrTidyNames[self::tidyName($arrTableDefine['Id'])]	= $arrTableDefine['Id'];
		
		if ($arrProperties instanceof ORM)
		{
			throw new Exception("\$arrProperties is an ORM object!");
		}
		
		// Automatically load the Record using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : (($arrProperties['id']) ? $arrProperties['id'] : NULL);
		if ($bolLoadById && $intId)
		{
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId)))
			{
				$arrProperties	= $selById->Fetch();
			}
			elseif ($selById->Error())
			{
				throw new Exception("DB ERROR: ".$selById->Error());
			}
			else
			{
				// Do we want to Debug something?
				throw new Exception_ORM_LoadById($this->_strTableName, $intId);
			}
		}
		
		// Set Properties
		
		// First set the id field, if it has been specified
		if (array_key_exists('id', $arrProperties))
		{
			$this->setId($arrProperties['id']);
			
			// Remove it from the properties
			unset($arrProperties['id']);
		}
		elseif (array_key_exists('Id', $arrProperties))
		{
			$this->setId($arrProperties['Id']);
			
			// Remove it from the properties
			unset($arrProperties['Id']);
		}
		
		// Set all remaining fields
		foreach ($arrProperties as $strName=>$mixValue)
		{
			// Load from the Database
			$this->{$strName}	= $mixValue;
		}
	}
	
	/**
	 * __clone
	 *
	 * Clones an object
	 * This will nullify the id property if the original object had it set, and flag the object as not saved
	 *
	 * @return	object			The clone
	 *
	 * @method
	 */
	public function __clone()
	{
		// Nullify the id property
		$this->setId(null);
		$this->_bolSaved = false;
	}
	
	//------------------------------------------------------------------------//
	// save
	//------------------------------------------------------------------------//
	/**
	 * save()
	 *
	 * Inserts or Updates the Record for this instance
	 *
	 * Inserts or Updates the Record for this instance
	 * 
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
	public function save()
	{
		// Do we have an Id for this instance?
		if ($this->id !== NULL)
		{
			// Update
			$ubiSelf	= $this->_preparedStatement("ubiSelf");
			if ($ubiSelf->Execute($this->toArray()) === FALSE)
			{
				throw new Exception("DB ERROR: ".$ubiSelf->Error());
			}
			return TRUE;
		}
		else
		{
			$insSelf	= $this->_preparedStatement("insSelf");
		}
		
		// Insert
		$mixResult	= $insSelf->Execute($this->toArray());
		if ($mixResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$insSelf->Error());
		}
		if (is_int($mixResult))
		{
			// Set the id of the object (can't use the __set method as that prohibits explicit mutation of the id property)
			$this->setId($mixResult);
			return TRUE;
		}
		else
		{
			return $mixResult;
		}
	}

	// This private function is used to set the id of the object, because this functionality is prohibited in the protected __set method (shouldn't the __set method be public?)
	private function setId($intId)
	{
		$this->_arrProperties[$this->_strIdField] = $intId;
	}

	public function __get($strName)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	protected function __set($strName, $mxdValue)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		
		if (array_key_exists($strName, $this->_arrProperties))
		{
			if ($strName == $this->_strIdField)
			{
				// Cannot explicitly mutate the id
				throw new Exception_Assertion("Cannot explicitly set the id property of an ORM object", "Attempted to set the id to $mxdValue for the ". get_class($this) ." Object with internal state: \n". print_r($this, true), "ORM::__set() Violation");
			}
			
			$mixOldValue					= $this->_arrProperties[$strName];
			$this->_arrProperties[$strName]	= $mxdValue;
			
			if ($mixOldValue !== $mxdValue)
			{
				$this->_bolSaved	= FALSE;
			}
		}
		else
		{
			$this->{$strName}	= $mxdValue;
		}
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
	protected static function tidyName($strName)
	{
		$strTidy	= str_replace(' ', '', ucwords(str_replace('_', ' ', $strName)));
		$strTidy[0]	= strtolower($strTidy[0]);
		return $strTidy;
	}
	
	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 * 
	 * @param	bool	$bolUseTidyNames	Optional, defaults to FALSE.  If true then the keys of the array will be the tidy names.  If false, then the proper property names will be used
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray($bolUseTidyNames=FALSE)
	{
		if ($bolUseTidyNames)
		{
			$arrProps = array();
			
			foreach ($this->_arrTidyNames as $strTidyName=>$strPropName)
			{
				$arrProps[$strTidyName] = $this->_arrProperties[$strPropName];
			}
			return $arrProps;
		}
		else
		{
			return $this->_arrProperties;
		}
	}
	
	/**
	 * toStdClass()
	 *
	 * Returns a stdClass object modelling the Database Record
	 * 
	 * @param	[bool	$bolUseTidyNames				]	TRUE	: Use Tidied Names
	 * 														FALSE	: Use DB Names (default)
	 * 
	 * @return	stdClass
	 *
	 * @method
	 */
	public function toStdClass($bolUseTidyNames=FALSE)
	{
		$arrData	= array();
		if ($bolUseTidyNames)
		{
			foreach ($this->_arrTidyNames as $strTidyName=>$strPropName)
			{
				$arrData[$strTidyName] = $this->_arrProperties[$strPropName];
			}
		}
		else
		{
			$arrData	= $this->_arrProperties;
		}
		
		$objStdClass	= new stdClass();
		foreach ($arrData as $strField=>$mixValue)
		{
			$objStdClass->{$strField}	= $mixValue;
		}
		return $objStdClass;
	}
	
	/**
	 * mysqlToPostgresArray()
	 *
	 * Converts an associative array of MySQL fields/values to their Postgres equivalent
	 * 
	 * @param	array		$arrMySQL								MySQL version of the array
	 * @param	array		$bolReturnConversionArray	[optional]	TRUE: Return a conversion array instead of Field=>Value
	 * 
	 * @return	array												Postgres version of the array OR Array of Mysql Fields as keys, and Postgres Fields as the values
	 *
	 * @method
	 */
	public static function mysqlToPostgresArray($arrMySQL, $bolReturnConversionArray=false)
	{
		$arrPostgres	= array();
		
		foreach ($arrMySQL as $strMySQLField=>$mixValue)
		{
			$strPostgresField	= preg_replace('/(([A-Za-z])([A-Z0-9])([a-z]))+/'	, '${2}_${3}${4}'	, $strMySQLField);
			$strPostgresField	= preg_replace('/(([a-z])([A-Z0-9]))+/'				, '${2}_${3}'		, $strPostgresField);
			$strPostgresField	= preg_replace('/(([0-9])([A-Za-z]))+/'				, '${2}_${3}'		, $strPostgresField);
			
			if ($bolReturnConversionArray)
			{
				$arrPostgres[$strMySQLField]	= strtolower(trim($strPostgresField, '_'));
			}
			else
			{
				$arrPostgres[strtolower(trim($strPostgresField, '_'))]	= $mixValue;
			}
		}
		
		return $arrPostgres;
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by a class that extends this one
	 *
	 * Access a Static Cache of Prepared Statements used by a class that extends this one
	 * 
	 * @param	string		$strStatement		Name of the statement
	 * 											Each derived class must handle the values
	 * 											'selById' (SELECT by id),
	 * 											'ubiSelf' (UPDATE by id of object),
	 * 											'insSelf' (INSERT by id of object)
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	abstract protected static function _preparedStatement($strStatement);
	
}
?>