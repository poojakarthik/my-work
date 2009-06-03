<?php
//----------------------------------------------------------------------------//
// ORM_Cached
//----------------------------------------------------------------------------//
/**
 * ORM_Cached
 *
 * Adds caching funcationality to the ORM class
 * Note that any objects returned by the static functions will directly refer/point to an object in the cache, not a clone of the object.
 * Therefore any changes made to the object will change the object in the cache, as it should
 *
 * NOTE: An object is only implicitly cached if it is instanciated with the $bolLoadById==true, or retrieved using the 'getForId' method or saved using the 'save' method.
 * While the 'addToCache' function has been declared protected (so it can be extended by ORM_Enumerated), it should never be used or extended by a class that extends either ORM_Cached or ORM_Enurmerated
 * 
 * @class	ORM_Cached
 */
abstract class ORM_Cached extends ORM
{
	//NOTE: until PHP 5.3 all functions that aren't private should be internally called using call_user_func($strClass, [args]) except for &getReferenceToCache()

	// This array stores individual caches for each class that extends this class
	private static $_arrCache = array();

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
	public function __construct($arrProperties=array(), $bolLoadById=false)
	{
		parent::__construct($arrProperties, $bolLoadById);

		// Only cache the object if it was loaded from the database
		if ($bolLoadById)
		{
			// The object was loaded from the database
			$this->addToCache($this);
		}
	}

	/**
	 * &getReferenceToCache($strClass)
	 *
	 * Returns a reference to the cache of $strClass objects
	 *
	 * @param	string	$strCacheName	The name of the cache of objects to retrieve
	 *
	 * @return	&array					Reference to the actual array that is the cache specific to the cache name passed.  If a cache of these objects doesn't already exist then an empty one will be created
	 * @method
	 */
	private static function &getReferenceToCache($strCacheName)
	{
		// Because this is a private method, $strCacheName must be passed to it.  I'm pretty sure you can't do static::getCacheName(), because you can't trigger this
		
		if (!array_key_exists($strCacheName, self::$_arrCache))
		{
			// A cache doesn't currently exist with this class name.  Create it
			self::$_arrCache[$strCacheName] = array();
		}
		return self::$_arrCache[$strCacheName];
	}

	/**
	 * hasCache
	 *
	 * Returns true if the cache named $strCacheName has been created, else returns false
	 *
	 * @param	string	$strCacheName		Name of the cache in question
	 *
	 * @return	bool						Returns true if the cache named $strCacheName has been created, else returns false
	 * @method
	 */
	protected static function hasCache($strCacheName)
	{
		return array_key_exists($strCacheName, self::$_arrCache);
	}

	/**
	 * clearCache
	 *
	 * Clears the cache of $strClass objects
	 *
	 * @param	string	$strClass		The name of the child class (This won't be needed for PHP 5.3)
	 *
	 * @return	void
	 * @method
	 */
	public static function clearCache($strClass=null)
	{
		// PHP 5.3 - $strCacheName = static::getCacheName();  and remove $strClass from the function parameters
		$strCacheName = call_user_func(array($strClass, 'getCacheName'));
		
		$arrCache = &self::getReferenceToCache($strCacheName);
		$arrCache = array();
	}
	
	/**
	 * findInCache
	 *
	 * Retrieves an object from the cache
	 * This also flags the object as having been the most recently accessed.  The oldest accessed objects are removed first, if the max cache size is ever exceeded
	 *
	 * @param	int		$intId			id of the object
	 * @param	string	$strClassName	The name of the cache to look in
	 *
	 * @return	mixed					: the object if it can be found
	 * 									: null, if the object can't be found
	 * @method
	 */
	private static function findInCache($intId, $strCacheName)
	{
		$arrCache = &self::getReferenceToCache($strCacheName);
		
		if (array_key_exists($intId, $arrCache))
		{
			// Found it
			// Flag it as having been most recently accessed (by removing it from the cache, and then adding it again)
			$object = $arrCache[$intId];
			unset($arrCache[$intId]);
			$arrCache[$intId] = $object;
			return $object;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * getCachedObjects
	 *
	 * Returns a copy of the cache for the $strClass class
	 * Note that the objects in this copied array, will be references to the actual objects in the cache
	 * 
	 * This is only really used by the ORM_Enumerated class, so that it can build its cache of system names associated with the cache of objects.
	 * If it didn't need it, then I'd remove it
	 *
	 * @param	string	$strClass		The class of objects cached
	 *
	 * @return	array					a copy of the cache
	 * @method
	 */
	protected static function getCachedObjects($strClass=NULL)
	{
		// PHP 5.3 - $strCacheName = static::getCacheName();  and remove $strClass from the function parameters
		$strCacheName = call_user_func(array($strClass, 'getCacheName'));
		
		return self::getReferenceToCache($strCacheName);
	}

	/**
	 * addToCache
	 *
	 * Adds objects to the cache.
	 * If an object to add is already in the cache, it will be overwritten and be considered to be newly added.
	 * If the max cache size has been exceeded, then the oldest cached objects will be removed so long as they aren't one of the objects being added ($mixObjects).
	 * If the number of objects being added exceeds the max cache size, then the earlier ones in the array ($mixObjects) will not be cached.
	 * 
	 * This should be private, but ORM_Enumerated needs to be able to extend it
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
		// NOTE: There are times in this function where the cache size can potentially be exceed.  This is to minimize the processing overhead
		// If you are genuinely concerned about the memory use, then either don't pass this function an array of objects to add (instead do it one at a time)
		// or don't extend this class or rewrite this method
		
		// PHP 5.3 - $strCacheName = static::getCacheName();  and remove $strClass from the function parameters
		$strCacheName	= call_user_func(array($strClass, 'getCacheName'));
		$arrCache		= &self::getReferenceToCache($strCacheName);

		// PHP 5.3 - $intMaxCacheSize = static::getMaxCacheSize();
		$intMaxCacheSize = call_user_func(array($strClass, 'getMaxCacheSize'));

		$arrObjects = is_array($mixObjects)? $mixObjects : array($mixObjects);

		// Remove any of the objects that are already in the cache, so that they are considered to have been added more recently than other ones
		foreach ($arrObjects as $object)
		{
			if (array_key_exists($object->id, $arrCache))
			{
				// The object is already in the cache.  Remove it (temporarily)
				unset($arrCache[$object->id]);
			}
		}
		
		// Now add them all
		foreach ($arrObjects as $object)
		{
			$arrCache[$object->id] = $object;
		}

		// Truncate the cache if it has exceeded the maximum allowable size
		$intCacheSize = count($arrCache);
		if ($intCacheSize > $intMaxCacheSize)
		{
			$intObjectsToRemove	= $intCacheSize - $intMaxCacheSize;
			$arrKeys			= array_keys($arrCache);
			
			for ($i=0; $i<$intObjectsToRemove; $i++)
			{
				unset($arrCache[$arrKeys[$i]]);
			}
		}
	}

	/**
	 * getMaxCacheSize
	 *
	 * Returns the maximum size of the cache, specific to the class that extedns ORM_Cached
	 * If you don't want to specify a maximum size, then just set it to 1000000000
	 *
	 * @return	int		the maximum allowable size of the cache 
	 * @method
	 */
	abstract protected static function getMaxCacheSize();
	
	/**
	 * getCacheName
	 *
	 * Returns the name of the Cache for the specific class that extends this class
	 * It was originally using the derived class's name for the cache name, but that wouldn't allow for extending the class that should be cached
	 *
	 * @return	string		name of cache
	 * @method
	 */
	abstract protected static function getCacheName();
	
	/**
	 * getForId
	 *
	 * Retrieves the object referenced by $intId, from the cache, or if it isn't already in the cache, it will retrieve it from the data source and add it to the cache
	 * The object will be flagged as being the most recently accessed object.  The oldest accessed objects are removed first, if the max cache size is ever exceeded
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
		// For when PHP 5.3 is used
		// $strClass = get_called_class();  And remove $strClass from the parameters

		// PHP 5.3 - $strCacheName = static::getCacheName();
		$strCacheName = call_user_func(array($strClass, 'getCacheName'));

		$object = self::findInCache($intId, $strCacheName);

		if ($object !== NULL)
		{
			// The object is in the cache
			return $object;
		}
		
		// Try finding it in the database
		try
		{
			// This step will also cache the object, if it can be found
			$object = new $strClass(array('id'=>$intId), true);
		}
		catch (Exception_ORM_LoadById $e)
		{
			// Could not find the record
			if ($bolSilentFail)
			{
				return null;
			}
			else
			{
				throw $e;
			}
		}
		
		return $object;
	}
	
	/**
	 * save
	 *
	 * Saves the object, and adds it to the cache, if it isn't already in the cache.  It will also flag it as being the most recently accessed object in the cache
	 *
	 * @return	void
	 * @method
	 */
	public function save()
	{
		parent::save();
		if ($this->id !== NULL)
		{
			// Add the object to the cache
			$this->addToCache($this);
		}
	}
}
?>