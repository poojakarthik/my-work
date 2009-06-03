<?php
//----------------------------------------------------------------------------//
// ORM_Enumerated
//----------------------------------------------------------------------------//
/**
 * ORM_Enumerated
 *
 * Models an enumerated type, based on the table of the data source that models the enumerated type.
 * The table needs to have columns: id and system_name however system_name can be nullable.
 * It only needs to be set for the enumeration values that are explicitly
 * referenced in the code base, much like you would reference a constant value.
 *
 * @class	ORM_Enumerated
 * @extends	ORM_Cached
 */
abstract class ORM_Enumerated extends ORM_Cached
{	
	// Used to refere to an object by its system_name (for those objects that have a system name)
	private static $_arrSystemNameObjectIdMapping = array();
	
	// The system name can only be set once, and only if the object already has an id set
	private $bolSystemNameAlreadySet = false;

	/**
	 * __construct
	 *
	 * constructor - I would discourage using this for instanciating objects of classes that extend ORM_Enumerated.
	 * If you don't loadById, then the system name will no be set, even if you specify one
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining a record of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the record from the database table with the passed Id
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	public function __construct($arrProperties=array(), $bolLoadById=false)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
		
		if ($bolLoadById)
		{
			// The object was loaded from the database, and is now in the cache

			// Refresh the SystemName - Object Id mapping
			self::refreshSystemNameObjectIdMapping(get_class($this));
		}
		else
		{
			// Unset the system_name
			$this->setSystemName(null);
		}
	}

	/**
	 * setSystemName
	 *
	 * Sets the system_name for the object
	 * system_name can only be set once using the standard __set method, and the object must already have an id
	 * The idea is that system name should only be set, if the object is loaded from the database
	 * So it will be set if you use, getForId(), getAll() or instanciate a new object with $bolLoadById == true.
	 * Note that this does not update the SystemName-ObjectId mapping
	 *
	 * @param	string	$strSystemName		The system_name for the object (can be null)
	 *
	 * @return	void
	 */
	private function setSystemName($strSystemName)
	{
		$this->_arrProperties['system_name']	= $strSystemName;
		$this->bolSystemNameAlreadySet			= true;
	}

	/**
	 * getMaxCacheSize
	 *
	 * Enumorated types shouldn't have a max cache size.  All values of the enumoration should be cached,
	 * so to be on the safe side, the max cache size is set to 1 billion, which should never be reached in practice.
	 *
	 * @return	int			the max cache size
	 */
	final protected static function getMaxCacheSize()
	{
		return 1000000000;
	}

	/**
	 * getSystemNameObjectIdMapping
	 *
	 * Returns a reference to the SystemName-ObjectId mapping for the Cache name passed
	 *
	 * @param	string	$strCacheName		The name of the cache, of which to return the SystemName-ObjectId mapping
	 *
	 * @return	reference to an array		reference to the SystemName - Object Id mapping
	 */
	private static function &getSystemNameObjectIdMapping($strCacheName)
	{
		if (!array_key_exists($strCacheName, self::$_arrSystemNameObjectIdMapping))
		{
			// The cache doesn't currently exist.  Create it
			self::$_arrSystemNameObjectIdMapping[$strCacheName] = array();
		}
		return self::$_arrSystemNameObjectIdMapping[$strCacheName];
	}
	
	/**
	 * refreshSystemNameObjectIdMapping
	 *
	 * Rebuilds the SystemName - Object Id mapping for the cache related to the class specified
	 *
	 * @param	string	$strClass			The name of the child class (This won't be needed for PHP 5.3)
	 *
	 * @return	void
	 */
	private static function refreshSystemNameObjectIdMapping($strClass)
	{
		// PHP 5.3 - $strCacheName = static::getCacheName();  and remove $strClass from the function parameters
		$strCacheName = call_user_func(array($strClass, 'getCacheName'));
		
		$arrSystemNameObjectIdMapping	= &self::getSystemNameObjectIdMapping($strCacheName);
		$arrSystemNameObjectIdMapping	= array();

		// PHP 5.3 - $strObjectCache = static::getCachedObjects();  and remove $strClass from the function parameters
		$arrObjectCache					= self::getCachedObjects($strClass);
		
		foreach ($arrObjectCache as $intObjectId=>$object)
		{
			if ($object->systemName !== NULL)
			{
				$arrSystemNameObjectIdMapping[$object->systemName] = $intObjectId;
			}
		}
	}
	
	/**
	 * clearCache
	 *
	 * Clears the cache
	 *
	 * @param	string	$strClass			The name of the child class (This won't be needed for PHP 5.3)
	 *
	 * @return	void
	 */
	public static function clearCache($strClass=NULL)
	{
		parent::clearCache($strClass);
		self::refreshSystemNameObjectIdMapping($strClass);
	}
	
	/**
	 * addToCache
	 *
	 * Adds objects to the cache. (And updates the SystemName-ObjectId mapping)
	 * 
	 * @param	mixed	$mixObjects			object				: the object to add to the cache
	 * 										array of objects	: and array of objects to add to the cache
	 * @param	string	$strClass			The name of the child class/cache (This won't be needed for PHP 5.3)
	 *
	 * @return	void 
	 * @method
	 */
	protected static function addToCache($mixObjects, $strClass=NULL)
	{
		// PHP 5.3 - you can remove $strClass from this function
		parent::addToCache($mixObjects, $strClass);
		self::refreshSystemNameObjectIdMapping($strClass);
	}
	 
	/**
	 * getAll
	 *
	 * Retreives all the objects comprising the Enumerated Type
	 * 
	 * @param	bool	[ $bolForceReload ]		Defaults to false.  if true, then the cached objects will be reloaded from the database.
	 * 											if false, then they will only be retreived from the database, if the cache doesn't already exist
	 *
	 * @param	string	$strClass				The name of the child class/cache (This won't be needed for PHP 5.3)
	 *
	 * @return	array							All the objects comprising the Enumerated Type
	 * @method
	 */
	public static function getAll($bolForceReload=false, $strClass=null)
	{
		// PHP 5.3 - $strCacheName = static::getCacheName();
		$strCacheName = call_user_func(array($strClass, 'getCacheName'));
		
		if ($bolForceReload || !self::hasCache($strCacheName))
		{
			// Reload the object into the cache
			
			// Clear the cache
			call_user_func(array($strClass, 'clearCache'));
			
			// Retrieve the objects from the database
			$selAll = call_user_func(array($strClass, '_preparedStatement'), 'selAll');
			if ($selAll->Execute() === false)
			{
				throw new Exception(__METHOD__ ." - Failed to retrieve all $strClass objects from the data source: ". $selAll->Error());
			}
		
			$arrObjects = array();
			while ($arrRecord = $selAll->Fetch())
			{
				$object = new $strClass($arrRecord);
				
				// You have to set the system_name
				$object->setSystemName($arrRecord['system_name']);

				$arrObjects[] = $object;
			}
			
			// Add the objects to the cache as a bulk operation
			call_user_func(array($strClass, 'addToCache'), $arrObjects);
		} 
		
		return call_user_func(array($strClass, 'getCachedObjects'));
	} 

	/**
	 * __clone
	 *
	 * Clones an object
	 * This will nullify the id property if the original object had it set and the system_name property
	 *
	 * @return	object			The clone
	 *
	 * @method
	 */
	public function __clone()
	{
		parent::__clone();
		
		$this->_arrProperties['system_name'] = NULL;
	}

	/**
	 * getForSystemName
	 *
	 * Retrieves the object that is uniquely referenced by the system_name passed, in the context of $strClass
	 * If the object can't be found, then an Assertion Exception is thrown, because if we are referencing an object by its system name,
	 * then it should always exist
	 * 
	 * @param	string	$strSystemName		The name that uniquely identifies the enumeration value desired 
	 *
	 * @param	string	$strClass			The name of the child class/cache (This won't be needed for PHP 5.3)
	 *
	 * @return	object						The object modelling the enumerated value that is referenced by $strSystemName 
	 * @method
	 */
	public static function getForSystemName($strSystemName, $strClass=NULL)
	{
		// PHP 5.3 - static::getAll(); and remove $strClass from the parameters		
		$arrObjectCache = call_user_func(array($strClass, 'getAll'), false);

		// PHP 5.3 - $strCacheName = static::getCacheName();
		$strCacheName = call_user_func(array($strClass, 'getCacheName'));
		
		$arrSystemNameObjectIdMapping	= self::getSystemNameObjectIdMapping($strCacheName);
		
		if (array_key_exists($strSystemName, $arrSystemNameObjectIdMapping) && array_key_exists($arrSystemNameObjectIdMapping[$strSystemName], $arrObjectCache))
		{
			// Found it
			/* In theory we should call getForId to retrieve it, so that the object gets flagged as the most recently accessed object
			 * but that is only done so it won't get removed from the cache if the max cache size is exceeded, but Enumerations don't have a 
			 * max cache size, so we don't have to worry about the object being removed if the cache is maxxed out
			 */
			return $arrObjectCache[$arrSystemNameObjectIdMapping[$strSystemName]];
		}
		else
		{
			// Didn't find it.  This should never happen
			$strSystemNameObjectIdMapping	= print_r($arrSystemNameObjectIdMapping, true);
			$strObjectCache					= print_r(array_keys($arrObjectCache), true);
			
			throw new Exception_Assertion("Could not find $strClass object with system_name '$strSystemName'.  This should never happen", "SystemName - ObjectId Mapping:\n{$strSystemNameObjectIdMapping}\n\nIds of objects cached:\n{$strObjectCache}");
		}
	}

	/**
	 * getIdForSystemName
	 *
	 * Retrieves the id of the object that is uniquely referenced by the system_name passed, in the context of $strClass
	 * If the object can't be found, then an Assertion Exception is thrown, because if we are referencing an object by its system name,
	 * then it should always exist
	 * 
	 * @param	string	$strSystemName		The name that uniquely identifies the enumeration value desired 
	 *
	 * @param	string	$strClass			The name of the child class/cache (This won't be needed for PHP 5.3)
	 *
	 * @return	int							The id of the object modelling the enumerated value that is referenced by $strSystemName 
	 * @method
	 */
	public static function getIdForSystemName($strSystemName, $strClass)
	{
		$object = call_user_func(array($strClass, 'getForSystemName'), $strSystemName);
		
		return $object->id;
	}

	/**
	 * getForId
	 *
	 * Retrieves the object referenced by $intId, from the cache, or if it isn't already in the cache, it will retrieve it from the data source and add it to the cache
	 * The object will be flagged as being the most recently accessed object.  The oldest accessed objects are removed first, if the max cache size is ever exceeded,
	 * But this case should never happen when working with an enumerated type
	 *
	 * @param	int		$intId				Id of the object to retrieve
	 * @param	bool	[ $bolSilentFail ]	Defaults to false.  If true and the object can't be found, then the function will return null.  If false, then the function will
	 * 										throw an Exception_ORM_LoadById exception
	 * @param	string	$strClass			The name of the child class/cache (This won't be needed for PHP 5.3)
	 *
	 * @return	mixed				object	: If the object identified by $intId can be found in the cache or database
	 * 								null	: If the object can't be found and $bolSilentFail == true
	 * @method
	 */
	public static function getForId($intId, $bolSilentFail=false, $strClass=NULL)
	{
		// PHP 5.3 - static::getAll(); and remove $strClass from the parameters		
		call_user_func(array($strClass, 'getAll'), false);
		
		return parent::getForId($intId, $bolSilentFail, $strClass);
	}

	/**
	 * save
	 *
	 * Saves the object, and adds it to the cache, if it isn't already in the cache
	 *
	 * @return	void
	 * @method
	 */
	public function save()
	{
		// Don't allow saving of objects that don't currently have an id, but do have a system_name set
		if ($this->id === null && $this->systemName !== null)
		{
			throw new Exception("Cannot save an object with a system_name, but no id");
		}
		
		parent::save();
		
		// We don't have to refresh the SystemName - ObjectId mapping, because SystemNames cannot be modified via ORM objects.  They must be set via rollout scripts
	}
	
	/**
	 * __set
	 *
	 * Magic method for setting data members of the object.
	 * Prohibits the explicit setting of the system_name property, if it has already been set, or if the id of the object has not yet been established
	 *
	 * @return	void
	 * @method
	 */
	protected function __set($strName, $mxdValue)
	{
		$strName = array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		
		if ($strName == 'system_name')
		{
			if ($this->bolSystemNameAlreadySet || $this->id === null)
			{
				throw new Exception("The ". get_class($this) ." object with id: {$this->id} cannot have its system_name changed from '{$this->systemName}'.");
			}
			
			parent::__set($strName, $mxdValue);
			
			$this->bolSystemNameAlreadySet = true;
		}
		else
		{
			parent::__set($strName, $mxdValue);
		}
	}
}
?>