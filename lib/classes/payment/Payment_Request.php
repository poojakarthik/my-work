<?php
/**
 * Payment_Request
 *
 * @class	Payment_Request
 */
class Payment_Request extends ORM_Cached
{
	protected 			$_strTableName			= "payment_request";
	protected static	$_strStaticTableName	= "payment_request";
	
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
	
	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function generatePending($iAccount, $iPaymentType, $fAmount, $iInvoiceRun=null, $iEmployeeId=null, $iPaymentId=null)
	{
		if ($iEmployeeId === null)
		{
			$iEmployeeId	= Flex::getUserId();
			$iEmployeeId	= ($iEmployeeId === NULL ? Employee::SYSTEM_EMPLOYEE_ID : $iEmployeeId);
		}
		
		// Create new payment_request
		$oPaymentRequest							= new Payment_Request();
		$oPaymentRequest->account_id				= $iAccount;
		$oPaymentRequest->amount					= $fAmount;
		$oPaymentRequest->payment_type_id			= $iPaymentType;
		$oPaymentRequest->payment_request_status_id	= PAYMENT_REQUEST_STATUS_PENDING;
		$oPaymentRequest->invoice_run_id			= $iInvoiceRun;
		$oPaymentRequest->payment_id				= $iPaymentId;
		$oPaymentRequest->created_datetime			= date('Y-m-d H:i:s');
		$oPaymentRequest->created_employee_id		= $iEmployeeId;
		$oPaymentRequest->save();
		
		return $oPaymentRequest;
	}

	public static function getForStatus($iStatus)
	{
		// Get data
		$oStmt		= self::_preparedStatment('selByStatus');
		$mResult	= $oStmt->Execute(array('payment_request_status_id' => $iStatus));
		if ($mResult === false)
		{
			throw new Exception("Failed to get Payment Requests for status '{$iStatus}'. ".$oStmt->Error());
		}
		
		// Convert to ORM objects (Direct_Debit_Request)
		$aResults	= array();
		while ($aRow = $oStmt->Fetch())
		{
			$oORM					= new self($aRow);
			$aResults[$oORM->id]	= $oORM;
		}
		return $aResults;
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
				case 'selByStatus':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "payment_request_status_id = <payment_request_status_id>", "id ASC");
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