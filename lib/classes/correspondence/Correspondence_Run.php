<?php
/**
 * Correspondence_Source_SQL
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Correspondence_Source_SQL
 */
class Correspondence_Run extends ORM_Cached
{
	protected 			$_strTableName			= "correspondence_run";
	protected static	$_strStaticTableName	= "correspondence_run";

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

	public static function getForScheduledDateTime($sScheduledDateTime)
	{
		$oSelect	= self::_preparedStatement('selByScheduleDateTime');
		$oSelect->Execute(array('scheduled_datetime' => $sScheduledDateTime));
		$aResults	= $oSelect->FetchAll();
		$aObjects 	= array();
		foreach ($aResults as $aResult)
		{
			$aObjects[]= new self($aResult);
		}
		return $aObjects;
	}

	public static function getForBatchId($iBatchId)
	{
		$oSelect	= self::_preparedStatement('selByBatchId');
		$oSelect->Execute(array('correspondence_run_batch_id' => $iBatchId));
		$aResults 	= $oSelect->FetchAll();
		$aObjects 	= array();
		foreach ($aResults as $aResult)
		{
			$aObjects[]= new self($aResult);
		}
		return $aObjects;
	}

	public static function getLedgerInformation($bCountOnly=false, $iLimit=0, $iOffset=0, $aFilter=null, $aSort=null)
	{
		$sFrom		= "	correspondence_run cr
						JOIN Employee e ON cr.created_employee_id = e.id
						JOIN correspondence_template ct ON ct.id = cr.correspondence_template_id
						LEFT JOIN correspondence c ON (c.correspondence_run_id = cr.id)
						LEFT JOIN FileImport fi ON fi.id = cr.file_import_id";
		
		if ($bCountOnly)
		{
			$sSelect	= "	cr.id,
							count(c.id) as count_correspondence,
							(
								SELECT	count(*)
								FROM	correspondence_run_dispatch
								WHERE	correspondence_run_id = cr.id
							) as dispatch_count";
		}
		else
		{
			$sSelect	= "	cr.id,
							cr.correspondence_template_id,
							cr.processed_datetime,
							cr.scheduled_datetime,
							COALESCE(cr.delivered_datetime, '".Data_Source_Time::END_OF_TIME."') AS delivered_datetime,
							cr.created_employee_id,
							cr.created,
							cr.preprinted,
							CONCAT(e.FirstName,' ',e.LastName) AS created_employee_name,
							ct.name AS correspondence_template_name,
							ct.correspondence_source_id AS correspondence_template_source_id,
							cr.correspondence_run_error_id,
							fi.FileName as import_file_name,
							count(c.id) as count_correspondence,
							(
								SELECT	count(*)
								FROM	correspondence_run_dispatch
								WHERE	correspondence_run_id = cr.id
							) as dispatch_count";
		}
		
		$aHavingFilter	= null;
		if (isset($aFilter['dispatch_count']))
		{
			$aHavingFilter	= array('dispatch_count' => $aFilter['dispatch_count']);
			unset($aFilter['dispatch_count']);
		}

		$aWhereAlias	=	array(
								'processed_datetime' 			=> 'cr.processed_datetime',
								'scheduled_datetime' 			=> 'cr.scheduled_datetime',
								'created' 						=> 'cr.created',
								'created_employee_id'			=> 'cr.created_employee_id',
								'preprinted'					=> 'cr.preprinted',
								'correspondence_run_error_id'	=> 'cr.correspondence_run_error_id'
							);
		$aWhere			= 	StatementSelect::generateWhere($aWhereAlias, $aFilter);
		$aSortAlias		=	array(
								'processed_datetime' 			=> 'cr.processed_datetime',
								'scheduled_datetime' 			=> 'cr.scheduled_datetime',
								'created' 						=> 'cr.created',
								'created_employee_name'			=> 'created_employee_name',
								'preprinted'					=> 'cr.preprinted',
								'correspondence_run_error_id'	=> 'cr.correspondence_run_error_id'
							);
		$sOrderByClause	= 	StatementSelect::generateOrderBy($aSortAlias, $aSort);
		$sLimitClause	= 	StatementSelect::generateLimit($iLimit, $iOffset);
		$sWhereClause	= 	$aWhere['sClause'];
		
		if ($sWhereClause == "")
		{
			$sWhereClause	.= " 1";
		}
		
		$sWhereClause	.= " GROUP BY cr.id";
		
		if ($aHavingFilter)
		{
			$aHaving			= StatementSelect::generateWhere(null, $aHavingFilter);
			$sWhereClause		.= " HAVING ".$aHaving['sClause'];
			$aWhere['aValues']	= array_merge($aWhere['aValues'], $aHaving['aValues']);
		}
		
		$oStmt	= new StatementSelect($sFrom, $sSelect, $sWhereClause, ($bCountOnly ? '' : $sOrderByClause), ($bCountOnly ? '' : $sLimitClause));
		//Log::getLog()->log($oStmt->_strQuery);
		if ($oStmt->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ".$oStmt->Error());
		}
		
		if ($bCountOnly)
		{
			// Count only
			$aRows	= $oStmt->FetchAll();
			return count($aRows);
		}
		else
		{
			// Results required
			$aResults	= array();
			while ($aRow = $oStmt->Fetch())
			{
				$aResults[$aRow['id']]	= $aRow;
			}

			$aRunInfo	= array();
			foreach ($aResults as $iId => $aRow)
			{
				// Using the logic class is easy, but not the most efficient, as more data than needed is loaded into memory
				$oTmpRun							= new Correspondence_Logic_Run(self::getForId($iId), true);
				$aRow['status']		 				= $oTmpRun->generateDispatchStatusForReport();
				$aRow['file_export_breakdown'] 		= $oTmpRun->generateFileDeliveryReport(false);
				$aRow['delivery_method_breakdown'] 	= $oTmpRun->generateDeliveryMethodReport(false);
				$aRunInfo[$iId] 					= $aRow;
			}
			return $aRunInfo;
		}
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
				case 'selByBatchId':
					$arrPreparedStatements[$strStatement]	= 	new StatementSelect(
																	self::$_strStaticTableName." cr
																	JOIN correspondence_run_dispatch crd ON (crd.correspondence_run_id = cr.id)", 
																	"cr.*", 
																	"crd.correspondence_run_batch_id = <correspondence_run_batch_id>"
																);
					break;
				case 'selByScheduleDateTime':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "scheduled_datetime <= <scheduled_datetime> AND correspondence_run_error_id IS NULL AND delivered_datetime IS NULL");
					break;
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