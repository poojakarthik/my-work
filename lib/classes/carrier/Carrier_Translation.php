<?php
class Carrier_Translation extends ORM_Cached {
	protected $_strTableName = "carrier_translation";
	protected static $_strStaticTableName = "carrier_translation";
	
	// One of the few classes we allow a delete() for, so we can remove translation configurations
	public function delete() {
		$this->_delete();
	}
	
	public static function getForCarrierCode($mCarrierTranslationContext, $mInValue, $bSilentFail=false) {
		$mResult = Query::run("
			SELECT		*
			FROM		carrier_translation
			WHERE		carrier_translation_context_id = <carrier_translation_context_id>
						AND in_value = <in_value>
			ORDER BY	id DESC
			LIMIT		1;
		", array(
			'carrier_translation_context_id' => ORM::extractId($mCarrierTranslationContext),
			'in_value' => (string)$mInValue
		));
		if ($aRecord = $mResult->fetch_assoc()) {
			return new self($aRecord);
		} elseif (!$bSilentFail) {
			throw new Exception("No Translation found for Carrier {$iCarrier} and Code '{$mCarrierCode}'");
		} else {
			return false;
		}
	}
	
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
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

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