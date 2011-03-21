<?php
/**
 * Data_Type
 *
 * This is an example of a class that extends ORM_Enumerated
 *
 * @class	ORM_Enumerated_Example
 */
class Data_Type extends ORM_Enumerated
{
	protected 			$_strTableName			= "data_type";
	protected static	$_strStaticTableName	= "data_type";

	public static function cast($mData, $iDataTypeId) {
		switch ((int)$iDataTypeId)
		{
			case DATA_TYPE_INTEGER:
				$mValue	= (int)$mValue;
				break;

			case DATA_TYPE_FLOAT:
				$mValue	= (float)$mValue;
				break;

			case DATA_TYPE_BOOLEAN:
				$mValue	= !!$mValue;
				break;

			case DATA_TYPE_SERIALISED:
			case DATA_TYPE_ARRAY:
				return $mValue;
				break;

			case DATA_TYPE_STRING:
			default:
				$mValue	= (string)$mValue;
				break;
		}
		return $mValue;
	}

	public static function encode($mData, $iDataTypeId) {
		switch ((int)$iDataTypeId)
		{
			case DATA_TYPE_INTEGER:
				$mValue	= (int)$mValue;
				break;

			case DATA_TYPE_FLOAT:
				$mValue	= (float)$mValue;
				break;

			case DATA_TYPE_BOOLEAN:
				$mValue	= (bool)$mValue;
				break;

			case DATA_TYPE_SERIALISED:
			case DATA_TYPE_ARRAY:
				$mValue	= serialize($mValue);
				break;

			case DATA_TYPE_STRING:
			default:
				$mValue	= (string)$mValue;
				break;
		}
		return (string)$mValue;
	}

	public static function decode($mData, $iDataTypeId) {
		switch ((int)$iDataTypeId)
		{
			case DATA_TYPE_INTEGER:
				$mValue	= (int)$mValue;
				break;

			case DATA_TYPE_FLOAT:
				$mValue	= (float)$mValue;
				break;

			case DATA_TYPE_BOOLEAN:
				$mValue	= (bool)$mValue;
				break;

			case DATA_TYPE_SERIALISED:
			case DATA_TYPE_ARRAY:
				$mValue	= unserialize($mValue);
				break;

			case DATA_TYPE_STRING:
			default:
				$mValue	= (string)$mValue;
				break;
		}
		return $mValue;
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


	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Enumerated UNTIL WE START USING PHP 5.3 - START
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

	public static function getForSystemName($strSystemName)
	{
		return parent::getForSystemName($strSystemName, __CLASS__);
	}

	public static function getIdForSystemName($strSystemName)
	{
		return parent::getIdForSystemName($strSystemName, __CLASS__);
	}

	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Enumerated UNTIL WE START USING PHP 5.3 - END
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