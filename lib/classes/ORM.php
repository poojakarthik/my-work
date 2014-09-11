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
	const	DATASET_TYPE_ID			= 1;
	const	DATASET_TYPE_ORM		= 2;
	const	DATASET_TYPE_ARRAY		= 3;
	const	DATASET_TYPE_STDCLASS	= 4;

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
		if (!($arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine($this->_strTableName))) {
			throw new Exception_ORM("Table '{$this->_strTableName}' cannot be found in the Data Model");
		}

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
			throw new Exception_ORM("\$arrProperties is an ORM object!");
		}

		// Automatically load the Record using the passed Id
		$intId	= isset($arrProperties['Id']) ? $arrProperties['Id'] : (isset($arrProperties['id']) ? $arrProperties['id'] : NULL);
		if ($bolLoadById && $intId !== NULL)
		{
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId)) === false)
			{
				throw new Exception_Database("DB ERROR: ".$selById->Error());
			}
			elseif (!($arrProperties = $selById->Fetch()))
			{
				// Do we want to Debug something?
				throw new Exception_ORM_LoadById($this->_strTableName, $intId, $selById->_strQuery);
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
			// Use ___set() instead of __set(), because we want to ignore any overridden __set() methods and set the raw data directly
			//$this->{$strName}	= $mixValue;
			$this->___set($strName, $mixValue);
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
				throw new Exception_Database("DB ERROR: ".$ubiSelf->Error());
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
			throw new Exception_Database("DB ERROR: ".$insSelf->Error());
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

	// By default this only has protected visibility, so we don't accidentally go deleting everything
	// A child class can reveal this to the outside world if it feels that it really needs to
	protected function _delete($bClearId=true)
	{
		static	$oQuery;
		$oQuery	= (isset($oQuery)) ? $oQuery : new Query();

		if (isset($this->_arrProperties[$this->_strIdField]))
		{
			$sDeleteSQL	= "	DELETE FROM	{$this->_strTableName}
							WHERE		id = {$this->_arrProperties[$this->_strIdField]}";

			if ($oQuery->Execute($sDeleteSQL) === false)
			{
				throw new Exception_Database("Unable to delete {$this->_strTableName} record where {$this->_strIdField} is '{$this->_arrProperties[$this->_strIdField]}'");
			}

			$this->setId(null);
			$this->_bolSaved	= false;
		}

		return true;
	}

	// This private function is used to set the id of the object, because this functionality is prohibited in the protected __set method (shouldn't the __set method be public?)
	private function setId($intId)
	{
		$this->_arrProperties[$this->_strIdField] = $intId;
	}

	public function __get($strName)
	{
		$strName	= $this->_getFieldName($strName);
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	public function __set($strName, $mxdValue)
	{
		$this->___set($strName, $mxdValue);
	}

	// ___set() is essentially a protected method that allows us to bypass any overridden __set() methods if needed
	final protected function ___set($strName, $mxdValue)
	{
		$strName	= $this->_getFieldName($strName);

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

	public function __isset($sName)
	{
		return isset($this->_arrProperties[$this->_getFieldName($sName)]);
	}

	public function __unset($sName)
	{
		unset($this->_arrProperties[$this->_getFieldName($sName)]);
	}

	protected function _getFieldName($sName)
	{
		return array_key_exists($sName, $this->_arrTidyNames) ? $this->_arrTidyNames[$sName] : $sName;
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

	public static function extractId($mORM)
	{
		if (is_object($mORM))
		{
			// Object (including ORM)
			if (isset($mORM->id))
			{
				return $mORM->id;
			}
			elseif (isset($mORM->Id))
			{
				return $mORM->Id;
			}
			elseif (isset($mORM->ID))
			{
				return $mORM->ID;
			}
			elseif (isset($mORM->iD))
			{
				return $mORM->iD;
			}
			else
			{
				return null;
			}
		}
		elseif (is_array($mORM))
		{
			// Array representing a DB row
			foreach ($mORM as $sKey=>$mValue)
			{
				if (strtolower($sKey) === 'id')
				{
					return $mValue;
				}
				return null;
			}
		}
		elseif (is_numeric($mORM))
		{
			// Numeric (cast it to an integer)
			return (int)$mORM;
		}
		else
		{
			// Unable to extract
			return null;
		}
	}

	public static function importResult($aResultSet, $sORMClass)
	{
		if (!is_subclass_of($sORMClass, 'ORM'))
		{
			throw new Exception_Database("Supplied Class '{$sORMClass}' does not inherit from ORM");
		}

		// If it is a single-dimensional array, wrap it in another array
		if (!is_array(reset($aResultSet)))
		{
			$aResultSet	= array($aResultSet);
		}

		$aInstances	= array();
		foreach ($aResultSet as $aResult)
		{
			$aInstances[ORM::extractId($aResult)]	= new $sORMClass($aResult);
		}

		return $aInstances;
	}

	public static function getORMSelect($sORMClass, $sAlias=null)
	{
		$sORMClass	= trim($sORMClass);
		$sAlias		= trim($sAlias);

		if (!class_exists($sORMClass) || !is_subclass_of($sORMClass, 'ORM'))
		{
			throw new Exception("{$sORMClass} is not an ORM Class (or doesn't exist)");
		}

		$aReflectedStaticProperties	= Reflectors::getClass($sORMClass)->getStaticProperties();
		$sTableName		= $aReflectedStaticProperties['_strStaticTableName'];

		$sAlias			= ($sAlias) ? $sAlias : $sTableName;
		$sColumnPrefix	= "[{$sORMClass}]{$sAlias}";

		// Get Variables
		$aExpandedColumns	= array();
		$aTableDefinition	= DataAccess::getDataAccess()->FetchTableDefine($sTableName);
		foreach ($aTableDefinition['Column'] as $sColumnName=>$aColumnDefinition)
		{
			$aExpandedColumns[]	= "{$sAlias}.{$sColumnName} AS '{$sColumnPrefix}.{$sColumnName}'";
		}
		$aExpandedColumns[]	= "{$sAlias}.{$aTableDefinition['Id']} AS '{$sColumnPrefix}.{$aTableDefinition['Id']}'";

		// Return as a String
		return implode(', ', $aExpandedColumns);
	}

	public static function parseORMResult($aResultRow)
	{
		// Parse the Result Row
		$aORMInstanceData	= array();
		$aExtraData			= array();
		foreach ($aResultRow as $sField=>$mData)
		{
			// Extract data from the column name
			$aTokens	= array();
			preg_match('/^(\[(?P<ORMClass>\w+)\])(?P<Alias>\w+)\.(?P<Column>\w+)$/i', $sField, $aTokens);

			CliEcho(print_r($aTokens, true));

			$sORMClass	= $aTokens['ORMClass'];
			$sAlias		= $aTokens['Alias'];
			$sColumn	= $aTokens['Column'];

			if ($sORMClass && $sAlias && $sColumn && is_subclass_of($sORMClass, 'ORM'))
			{
				// ORM Select
				$aORMInstanceData[$sORMClass][$sAlias][$sColumn]	= $mData;
			}
			else
			{
				// Other Data
				$aExtraData[$sField]	= $mData;
			}
		}

		// Build ORM Instances
		$aORMInstances	= array();
		foreach ($aORMInstanceData as $sORMClass=>$aAliases)
		{
			foreach ($aAliases as $sAlias=>$aColumns)
			{
				$aImportedORMs			= Callback::create('importResult', $sORMClass)->invoke($aColumns, $sORMClass);
				$aORMInstances[$sAlias]	= reset($aImportedORMs);
			}
		}

		return array_merge($aORMInstances, array($aExtraData));
	}

	public function toDatasetType($mDatasetType) {
		if (is_object($mDatasetType) && $mDatasetType instanceof Callback) {
			// If the DatasetType is actually a Callback instance, then invoke it with $this as the only parameter
			return $mDatasetType->invoke($this);
		} elseif (is_string($mDatasetType) && class_exists($mDatasetType)) {
			// If the DatasetType is actuall a Class name, then return a new instance of it with $this as the only parameter
			return new $mDatasetType($this);
		}

		// Standard DatasetTypes
		switch ((int)$mDatasetType) {
			case self::DATASET_TYPE_ID:
				return $this->id;
				break;
			case self::DATASET_TYPE_ORM:
				return $this;
				break;
			case self::DATASET_TYPE_ARRAY:
				return $this->toArray();
				break;
			case self::DATASET_TYPE_STDCLASS:
				return $this->toStdClass();
				break;

			default:
				throw new Exception_ORM("Unknown ORM Dataset Type: '{$mDatasetType}'");
		}
	}

	public static function mapToArray(ORM $orm) {
		return $orm->toArray();
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