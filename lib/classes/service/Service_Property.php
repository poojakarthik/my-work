<?php
class Service_Property extends ORM_Cached {
	protected $_strTableName = "service_property";
	protected static $_strStaticTableName = "service_property";

	public static function getForServiceAndName($serviceId, $name) {
		$propertyResult = DataAccess::get()->query('
			SELECT *
			FROM service_property
			WHERE service_id = <service_id>
				AND name = <name>
		', array(
			'service_id' => $serviceId,
			'name' => $name
		));
		if (!$propertyResult->num_rows) {
			return null;
		}
		return $property;
	}

	public static function getEffectiveForServiceAndName($serviceId, $name, $effectiveDatetime) {
		$propertyResult = DataAccess::get()->query('
			SELECT *
			FROM service_property_history sph
			WHERE service_id = <service_id>
				AND name = <name>
				AND modified_datetime <= <effective_datetime>
				AND id = (
					SELECT MAX(id)
					FROM service_property_history
					WHERE service_id = <service_id>
						AND name = <name>
						AND modified_datetime <= <effective_datetime>
				)
		', array(
			'service_id' => $serviceId,
			'name' => $name,
			'effective_datetime' => $effectiveDatetime
		));
		if (!$propertyResult->num_rows) {
			return null;
		}
		return $property;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//
	protected static function getCacheName() {
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName)) {
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize() {
		return 100;
	}

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects) {
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false) {
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false) {
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function importResult($aResultSet) {
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;

				case 'selAll':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement] = new StatementInsert(self::$_strStaticTableName);
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement] = new StatementUpdateById(self::$_strStaticTableName);
					break;

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}