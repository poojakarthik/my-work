<?php
class Carrier_Translation_Context extends ORM_Cached {
	protected $_strTableName = "carrier_translation_context";
	protected static $_strStaticTableName = "carrier_translation_context";

	protected $_aJSONCached;

	protected $_aMemoisedTranslations = array();
	protected $_aMemoisedJSONTranslations = array();

	public function translate($mInValue) {
		$sInValue = (string)$mInValue;
		$mResult = Query::run("
			SELECT		ct.*
			FROM		carrier_translation ct
			WHERE		ct.carrier_translation_context_id = <carrier_translation_context_id>
						AND ct.in_value = <in_value>
			ORDER BY	ct.id DESC
			LIMIT		1;
		", array(
			'carrier_translation_context_id' => $this->id,
			'in_value' => $sInValue
		));
		if ($aRecord = $mResult->fetch_assoc()) {
			return ($this->_aMemoisedTranslations[$sInValue] = new Carrier_Translation($aRecord));
		} else {
			throw new Exception("No Translation found for Translation Context {$this->id} and in-value ".var_export($mInValue, true));
		}
	}

	public function translateJSON($mJSON, $aPrecedence=array()) {
		$oJSON = $mJSON;
		if (is_array($mJSON)) {
			$oJSON = (object)$mJSON;
		} elseif (is_string($mJSON)) {
			Flex::assert(null !== ($oJSON = json_decode($mJSON)),
				"Supplied in-value ".var_export($mJSON, true)." cannot be JSON-decoded",
				$mJSON
			);
		}
		$sJSON = json_encode($oJSON);

		if (!isset($this->_aMemoisedJSONTranslations[$sJSON])) {
			// Find the best match
			if (!isset($this->_aJSONCached)) {
				// Fetch and cache all translations in this Context
				$mResult = Query::run("
					SELECT		ct.*
					FROM		carrier_translation ct
					WHERE		ct.carrier_translation_context_id = <carrier_translation_context_id>
					ORDER BY	id ASC;
				", array(
					'carrier_translation_context_id' => $this->id
				));
				while ($aRecord = $mResult->fetch_assoc()) {
					$this->_aJSONCached[$aRecord['id']] = new Carrier_Translation($aRecord);
				}
			}
			if (!count($this->_aJSONCached)) {
				throw new Exception("No Translation found for Translation Context {$this->id} and in-value ".var_export($mInValue, true));
			}

			// Resolve
			$aMatches = array();
			$aPrecedence = array_reverse($aPrecedence);
			foreach ($this->_aJSONCached as $iTranslationId=>$oTranslation) {
				$aTranslationMatches = $oTranslation->testJSON($oJSON);
				// Only considered if it matches (exact or *) every property
				if (!self::_arrayHasFalse($aTranslationMatches)) {
					$aMatches[$iTranslationId] = (object)array(
						'oTranslation' => $oTranslation,
						'aMatches' => $aTranslationMatches,
						'aPrecedence' => &$aPrecedence
					);
				}
			}

			if (count($aMatches)) {
				// Sort results
				uasort($aMatches, 'Carrier_Translation_Context::compareJSONMatches');

				$oTopMatch = array_shift($aMatches);
				$this->_aMemoisedJSONTranslations[$sJSON] = $this->_aJSONCached[$oTopMatch->oTranslation->id];
			} else {
				Flex::assert(false, "Unable to translate in-value ".var_export($sJSON, true)." for Translation Context #{$this->id}");
			}
		}
		return $this->_aMemoisedJSONTranslations[$sJSON];
	}

	const DEBUG_JSON_COMPARISON = false;
	// NOTE: Designed for exlusive use by `Carrier_Translation_Context#translateJSON`
	public static function compareJSONMatches(stdClass $oA, stdClass $oB) {
		Log::get()->logIf(self::DEBUG_JSON_COMPARISON, "Comparing #{$oA->oTranslation->id} (".var_export($oA->aMatches, true).") against #{$oB->oTranslation->id} (".var_export($oB->aMatches, true).")");
		$aPrecedence = $oA->aPrecedence; // Should be a reference to the same array in both objects

		$iScoreDifference = self::_calculateMatchScore($oA->aMatches) - self::_calculateMatchScore($oB->aMatches);
		if ($iScoreDifference) {
			// One has better matches than the other
			Log::get()->logIf(self::DEBUG_JSON_COMPARISON, "Exiting: #".(($iScoreDifference > 0) ? $oA->oTranslation->id : $oB->oTranslation->id)." has better matches");
			return $iScoreDifference;
		}

		// Same number of matches, check precedence
		Log::get()->logIf(self::DEBUG_JSON_COMPARISON, "Comparing precedence:");
		foreach ($aPrecedence as $sProperty) {
			// If one has the match, and the other doesn't, it is considered "greater"
			$bInA = in_array($sProperty, $oA->aMatches);
			$bInB = in_array($sProperty, $oB->aMatches);
			if ($bInA && !$bInB) {
				Log::get()->logIf(self::DEBUG_JSON_COMPARISON, "Exiting: #{$oA->oTranslation->id} has {$sProperty} (#{$oB->oTranslation->id} doesn't)");
				return 1;
			} elseif (!$bInA && $bInB) {
				Log::get()->logIf(self::DEBUG_JSON_COMPARISON, "Exiting: #{$oB->oTranslation->id} has {$sProperty} (#{$oA->oTranslation->id} doesn't)");
				return -1;
			}
		}

		Flex::assert(false,
			"Carrier Translations #{$oA->oTranslation->id} and #{$oB->oTranslation->id} have identical precedence",
			array(
				"#{$oA->oTranslation->id}" => $oA,
				"#{$oB->oTranslation->id}" => $oB
			)
		);
		/*
		// Same precedence, check carrier_translation.id
		$iIdDifference = $oA->oTranslation->id - $oB->oTranslation->id;
		Log::get()->logIf(self::DEBUG_JSON_COMPARISON, "Exiting: #".(($iIdDifference > 0) ? $oA->oTranslation->id : $oB->oTranslation->id)." has a lower Id");
		return $iIdDifference;
		*/
	}

	private static function _calculateMatchScore($aMatches) {
		$iScore = 0;
		foreach ($aMatches as $sProperty=>$bMatch) {
			if ($bMatch === true) {
				$iScore++;
			} elseif ($bMatch === false) {
				$iScore--;
			}
			// Null (*-match) comparison doesn't modify score
		}
		return $iScore;
	}

	private static function _arrayHasFalse($aArray) {
		foreach ($aArray as $bValue) {
			if ($bValue === false) {
				return true;
			}
		}
		return false;
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