<?php
/**
 * Service_Total
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Service_Total
 */
class Service_Total extends ORM_Cached
{
	protected 			$_strTableName			= "ServiceTotal";
	protected static	$_strStaticTableName	= "ServiceTotal";
	
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

	public function getServices()
	{
		$oStmt	= self::_preparedStatement('selServiceIds');
		if ($oStmt->Execute(array('service_total_id' => $this->Id)) === false)
		{
			throw new Exception_Database("Failed to get services for service total {$this->Id}. ".$oStmt->Error());
		}
		
		$aRows		= $oStmt->FetchAll();
		$aServices	= array();
		foreach ($aRows as $aRow)
		{
			$oService					= Service::getForId($aRow['service_id']);
			$aServices[$oService->Id]	= $oService;
		}
		
		return $aServices;
	}

	public static function getForInvoiceRunAndAccount($iInvoiceRunId, $iAccountId)
	{
		// Retrieve the objects from the database
		$oStatement = self::_preparedStatement('selByAccountAndInvoiceRunId');
		
		if ($oStatement->Execute(array('invoice_run_id' => $iInvoiceRunId, 'Account' => $iAccountId)) === false)
		{
			throw new Exception_Database(__METHOD__ ." - Failed to retrieve all ServiceTotal objects from the data source: ". $oStatement->Error());
		}
	
		$aServiceTotals 	= array();
		while ($aRecord = $oStatement->Fetch())
		{
			$oServiceTotal 						= new self($aRecord);
			$aServiceTotals[$oServiceTotal->Id] = $oServiceTotal;
		}
		
		return $aServiceTotals;
	}
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement	Name of the statement
	 * 
	 * @return	Statement					The requested Statement
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selByAccountAndInvoiceRunId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "invoice_run_id = <invoice_run_id> AND Account = <Account>");
					break;
				case 'selServiceIds':
					$arrPreparedStatements[$strStatement]	= 	new StatementSelect(	
																	"service_total_service",
																	"service_id",
																	"service_total_id = <service_total_id>"
																);
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