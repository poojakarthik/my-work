<?php
//----------------------------------------------------------------------------//
// Documentation
//----------------------------------------------------------------------------//
/**
 * Documentation
 *
 * Models a record of the Documentation table
 *
 * Models a record of the Documentation table
 *
 * @class	Documentation
 */
class Documentation extends ORM_Cached
{	
	protected 			$_strTableName					= "Documentation";
	protected static	$_strStaticTableName			= "Documentation";
	
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
	
	protected static function addToCache($mObjects)
	{
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false)
	{
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false)
	{
		return parent::getAll($bForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function getForEntityAndField($sEntity, $sField)
	{
		$oByEntity	= self::_preparedStatement("selByEntityAndField");
		if ($oByEntity->Execute(Array('Entity' => $sEntity, 'Field' => $sField)))
		{
			return new Documentation($oByEntity->Fetch());
		}
		elseif ($oByEntity->Error())
		{
			throw new Exception_Database($oByEntity->Error());
		}
		else
		{
			return NULL;
		} 
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selByEntityAndField':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Entity = <Entity> AND Field = <Field>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}
?>