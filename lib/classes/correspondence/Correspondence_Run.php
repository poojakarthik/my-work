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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "correspondence_run_batch_id =  <correspondence_run_batch_id> ");
					break;
				case 'selByScheduleDateTime':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "scheduled_datetime <= <scheduled_datetime> AND delivered_datetime IS NULL");
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

	public static function getForScheduledDateTime($sScheduledDateTime)
	{

		$oSelect	= self::_preparedStatement('selByScheduleDateTime');
		$oSelect->Execute(array('scheduled_datetime' => $sScheduledDateTime));
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
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
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
		foreach ($aResults as $aResult)
		{
			$aObjects[]= new self($aResult);
		}
		return $aObjects;
	}

	public static function searchFor($bCountOnly=false, $iLimit=0, $iOffset=0, $aFilter=null, $aSort=null)
	{
		$sFrom			= '	correspondence_run c
							JOIN Employee e ON c.created_employee_id = e.id
							JOIN correspondence_template ct ON ct.id = c.correspondence_template_id';
		if ($bCountOnly)
		{
			$sSelect	= 'count(c.id) AS record_count';
		}
		else
		{
			$sSelect	= "	c.id, 
							c.correspondence_template_id, 
							c.processed_datetime, 
							c.scheduled_datetime, 
							COALESCE(c.delivered_datetime, '".Data_Source_Time::END_OF_TIME."') AS delivered_datetime, 
							c.created_employee_id, 
							c.created, 
							c.data_file_export_id, 
							c.preprinted, 
							c.pdf_file_export_id, 
							c.correspondence_run_batch_id, 
							CONCAT(e.FirstName,' ',e.LastName) AS created_employee_name, 
							ct.name AS correspondence_template_name";
		}
		
		$aWhereAlias	=	array(
								'processed_datetime' 	=> 'c.processed_datetime',
								'scheduled_datetime' 	=> 'c.scheduled_datetime',
								'created' 				=> 'c.created',
								'created_employee_id'	=> 'c.created_employee_id',
								'preprinted'			=> 'c.preprinted'
							);
		$aWhere			= 	StatementSelect::generateWhere($aWhereAlias, $aFilter);
		$aSortAlias		=	array(
								'processed_datetime' 			=> 'c.processed_datetime',
								'scheduled_datetime' 			=> 'c.scheduled_datetime',
								'created' 						=> 'c.created',
								'created_employee_name'			=> 'created_employee_name',
								'preprinted'					=> 'c.preprinted'
							);
		$sOrderByClause	= StatementSelect::generateOrderBy($aSortAlias, $aSort);
		$sLimitClause	= StatementSelect::generateLimit($iLimit, $iOffset);
		
		$oStmt	=	new StatementSelect(
						$sFrom, 
						$sSelect, 
						$aWhere['sClause'], 
						($bCountOnly ? '' : $sOrderByClause), 
						($bCountOnly ? '' : $sLimitClause)
					);
		
		if ($oStmt->Execute($aWhere['aValues']) === false)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ".$oStmt->Error());
		}
		
		if ($bCountOnly)
		{
			// Count only
			$aRow	= $oStmt->Fetch();
			return $aRow['record_count'];
		}
		else
		{
			// Results required
			$aResults	= array();
			while ($aRow = $oStmt->Fetch())
			{
				$oResult				= self::getForId($aRow['id']);
				$aResults[$oResult->id]	= $oResult;
			}
			return $aResults;
		}
	}
}
?>