<?php
class Carrier_Translation extends ORM_Cached {
	protected $_strTableName = "carrier_translation";
	protected static $_strStaticTableName = "carrier_translation";
	
	// One of the few classes we allow a delete() for, so we can remove translation configurations
	public function delete() {
		$this->_delete();
	}
	
	public static function getForCarrierCode($mCarrierTranslationContext, $mInValue, $bSilentFail=false) {
		try {
			Carrier_Translation::translate(ORM::extractId($mCarrierTranslationContext), $mInValue);
		} catch (Exception $oException) {
			if ($bSilentFail) {
				return false;
			}
			throw $oException;
		}
	}

	const DEBUG_JSON_TEST = false;
	private $_oInValueJSON;
	public function testJSON(stdClass $oJSON) {
		if (!isset($this->_oInValueJSON)) {
			Flex::assert(null !== ($this->_oInValueJSON = json_decode($this->in_value)),
				"Translation in-value ".var_export($this->in_value, true)." cannot be JSON-decoded",
				$this->toArray()
			);
		}

		Log::get()->logIf(self::DEBUG_JSON_TEST, "Testing ".var_export($oJSON, true)." against Translation #{$this->id} (".var_export($this->_oInValueJSON, true).")");
		$aMatches = array();
		foreach ($oJSON as $sProperty=>$mValue) {
			if (!isset($this->_oInValueJSON->$sProperty)) {
				Log::get()->logIf(self::DEBUG_JSON_TEST, "[*] Wildcard match on {$sProperty}");
				$aMatches[$sProperty] = null;
			} elseif ($this->_oInValueJSON->$sProperty === $oJSON->$sProperty) {
				Log::get()->logIf(self::DEBUG_JSON_TEST, "[+] Exact match on {$sProperty}");
				$aMatches[$sProperty] = true;
			} else {
				Log::get()->logIf(self::DEBUG_JSON_TEST, "[-] No match on {$sProperty}");
				$aMatches[$sProperty] = false;
			}
		}
		return $aMatches;
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