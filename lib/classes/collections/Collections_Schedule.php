<?php
class Collections_Schedule extends ORM_Cached {
	protected $_strTableName = "collections_schedule";
	protected static $_strStaticTableName = "collections_schedule";

	const ELIGIBILITY_MAP_DIRECTDEBIT = 'DIRECTDEBIT';
	private static $_aEventEligibilityCache = array();

	const DEBUG_LOGGING = false;
	
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
	
	public function isActive() {
		return $this->status_id == STATUS_ACTIVE;
	}
	
	public static function getEligibility($iForCollectionEventId=null, $bForDirectDebit=false, $sEffectiveDate=null) {
		$sEffectiveDate = date('Y-m-d', ($sEffectiveDate && strtotime($sEffectiveDate) !== false) ? strtotime($sEffectiveDate) : time());
		$mMapKey = ($bForDirectDebit) ? self::ELIGIBILITY_MAP_DIRECTDEBIT : $iForCollectionEventId;
		Log::get()->logIf(self::DEBUG_LOGGING, "Checking eligibility for #{$mMapKey} on {$sEffectiveDate}");
		
		if (!isset(self::$_aEventEligibilityCache[$sEffectiveDate])) {
			self::$_aEventEligibilityCache[$sEffectiveDate] = array();
		}
		
		if (!isset(self::$_aEventEligibilityCache[$sEffectiveDate][$mMapKey])) {
			Log::get()->logIf(self::DEBUG_LOGGING, "  Not cached...");
			$mResult = Query::run("
					SELECT		eligibility

					FROM		collections_schedule

					WHERE		/* Date */
								(day IS NULL OR day = DAYOFMONTH(<effective_date>))
								AND (month IS NULL OR month = MONTH(<effective_date>))
								AND (year IS NULL OR year = YEAR(<effective_date>))

								/* Day of Week */
								AND (
									(monday = 1 AND WEEKDAY(<effective_date>) = 0)
									OR (tuesday = 1 AND WEEKDAY(<effective_date>) = 1)
									OR (wednesday = 1 AND WEEKDAY(<effective_date>) = 2)
									OR (thursday = 1 AND WEEKDAY(<effective_date>) = 3)
									OR (friday = 1 AND WEEKDAY(<effective_date>) = 4)
									OR (saturday = 1 AND WEEKDAY(<effective_date>) = 5)
									OR (sunday = 1 AND WEEKDAY(<effective_date>) = 6)
								)

								/* Events/Direct Debit */
								AND (
									collection_event_id IS NULL
									OR (
										<collection_event_id> IS NOT NULL
										AND collection_event_id = <collection_event_id>
										AND (is_direct_debit IS NULL OR is_direct_debit = 0)
									)
								)
								AND (
									(is_direct_debit IS NULL OR is_direct_debit = 0)
									OR (
										<is_direct_debit> = 1
										AND is_direct_debit = 1
										AND collection_event_id IS NULL
									)
								)

								/* Status */
								AND status_id = ".STATUS_ACTIVE."

					ORDER BY	precedence DESC,
								eligibility ASC;	/* Ineligibility beats eligibility at the same precedence */
				", array(
					'collection_event_id'	=> $iForCollectionEventId,
					'is_direct_debit'		=> $bForDirectDebit,
					'effective_date'		=> $sEffectiveDate
				)
			);
			
			// Our query does the resolution of precedence/weighting for us, so we simply need to look at the first result
			$aRow = $mResult->fetch_assoc();
			
			// If there are no results, the default state is 'ineligible'
			Log::get()->logIf(self::DEBUG_LOGGING, " Setting cached value to ".var_export($aRow && $aRow['eligibility'] == 1, true));
			self::$_aEventEligibilityCache[$sEffectiveDate][$mMapKey] = ($aRow && $aRow['eligibility'] == 1);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, "#{$mMapKey} on {$sEffectiveDate} eligbility: ".var_export(self::$_aEventEligibilityCache[$sEffectiveDate][$mMapKey], true));
		return self::$_aEventEligibilityCache[$sEffectiveDate][$mMapKey];
	}

	public static function clearEligibilityCache() {
		self::$_aEventEligibilityCache = array();
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
		static	$arrPreparedStatements = array();
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