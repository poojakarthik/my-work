<?php
/**
 * Correspondence_Source
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Correspondence_Source
 */
class Correspondence_Run_Batch extends ORM_Cached
{
	protected 			$_strTableName			= "correspondence_run_batch";
	protected static	$_strStaticTableName	= "correspondence_run_batch";

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

	public function getForBatchDateTime($bCountOnly=false, $iLimit=null, $iOffset=null, $iMinDateTime=null, $iMaxDateTime=null, $sSortDirection='DESC')
	{
		// Build where clause
		if (!is_null($iMinDateTime) && !is_null($iMaxDateTime))
		{
			$sWhere	= "WHERE	batch_datetime BETWEEN '".date('Y-m-d H:i:s', $iMinDateTime)."' AND '".date('Y-m-d H:i:s', $iMaxDateTime)."'";
		}
		else if (!is_null($iMinDateTime))
		{
			$sWhere	= "WHERE	batch_datetime >= '".date('Y-m-d H:i:s', $iMinDateTime)."'";
		}
		else if (!is_null($iMaxDateTime))
		{
			$sWhere	= "WHERE	batch_datetime <= '".date('Y-m-d H:i:s', $iMaxDateTime)."'";
		}
		else
		{
			$sWhere	= "";
		}
		
		if ($bCountOnly)
		{
			// Count records only
			$oQuery	= new Query();
			$sQuery	= "	SELECT	count(*) as batch_count
						FROM	correspondence_run_batch
						{$sWhere}";
			$mResult	= $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				// Most likely a sql or connectivity error
				throw new Exception("Unable to count correspondence_run_batch records, SQL Error. ".$oQuery->Error());
			}
			$aRow	= $mResult->fetch_assoc();
			return $aRow['batch_count'];
		}
		else
		{
			// Return all records
			$sLimit	= StatementSelect::generateLimit($iLimit, $iOffset);
			$oQuery	= new Query();
			$sQuery	= "	SELECT	*
						FROM	correspondence_run_batch
						{$sWhere}
						ORDER BY batch_datetime {$sSortDirection}
						LIMIT {$sLimit}";
			$mResult	= $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				// Most likely a sql or connectivity error
				throw new Exception("Unable to retrieve correspondence_run_batch records, SQL Error. ".$oQuery->Error());
			}
			
			// Create ORM objects and return
			$aORMs	= array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$aORMs[]	= self::getForId($aRow['id']);
			}
			return $aORMs;
		}
	}

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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
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