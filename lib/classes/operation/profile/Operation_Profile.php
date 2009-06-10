<?php
/**
 * Operation_Profile
 *
 * Models the 'operation_profile' Table and handles related functions
 *
 * @class	Operation_Profile
 */
class Operation_Profile extends ORM_Cached
{	
	protected			$_strTableName 			= "operation_profile";
	protected static	$_strStaticTableName	= "operation_profile";
	
	protected			$_arrOperations;
	
	public function getOperations()
	{
		if (!isset($this->_arrOperations))
		{
			// Calculate a list of all atomic Operations this profile includes
			$this->_arrOperations	= array();
			
			// Get Sub-Profiles
			$selSubProfileIds	= self::_preparedStatement('selSubProfileIds');
			if ($selSubProfileIds->Execute($this->toArray()) === false)
			{
				throw new Exception($selSubProfileIds->Error());
			}
			while ($arrSubProfileId = $selSubProfileIds->Fetch())
			{
				// Get the Operations for this Sub-Profile & merge with current list
				$this->_arrOperations	= array_merge($this->_arrOperations, self::getForId($arrSubProfileId['child_operation_profile_id'])->getOperations());
			}
			
			// Get Direct Operations
			$selOperationIds	= self::_preparedStatement('selOperationIds');
			if ($selOperationIds->Execute($this->toArray()) === false)
			{
				throw new Exception($selOperationIds->Error());
			}
			while ($arrOperationId = $selOperationIds->Fetch())
			{
				// Add this Operation to the list
				$this->_arrOperations[$arrOperationId['operation_id']]	= Operation::getForId($arrOperationId['operation_id']);
			}
		}
		return $this->_arrOperations;
	}
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "name ASC");
					break;
				case 'selSubProfileIds':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("operation_profile_children", "*", "parent_operation_profile_id = <id>");
					break;
				case 'selOperationIds':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("operation_profile_operation", "*", "operation_profile_id = <id> AND status_id = ".STATUS_ACTIVE);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>