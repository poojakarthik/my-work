<?php
/**
 * Payment_Transaction_Data
 *
 * Represents a Record in the payment_method table
 *
 * @class	Payment_Transaction_Data
 */
class Payment_Transaction_Data extends ORM_Cached
{
	const	CREDIT_CARD_NUMBER		= 'credit_card_number';
	const	BANK_ACCOUNT_NUMBER		= 'bank_account_number';
	const	ORIGINAL_PAYMENT_TYPE	= 'original_payment_type_id';

	protected static	$aSchema	= array(
							self::CREDIT_CARD_NUMBER	=> array(
								'iDataType'	=> DATA_TYPE_STRING
							),
							self::BANK_ACCOUNT_NUMBER	=> array(
								'iDataType'	=> DATA_TYPE_STRING
							),
							self::ORIGINAL_PAYMENT_TYPE	=> array(
								'iDataType'	=> DATA_TYPE_INTEGER
							)
						);
	
	protected 			$_strTableName			= "payment_transaction_data";
	protected static	$_strStaticTableName	= "payment_transaction_data";
	
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

	public static function getForPayment($iPaymentId, $iPaymentResponseId=null) {
		$oSelect = self::_preparedStatement('selByPaymentOrPaymentResponse');
		if ($oSelect->Execute(array('payment_id' => $iPaymentId, 'payment_response_id' => $iPaymentResponseId)) === false)
		{
			throw new Exception_Database("Failed to get for payment {$iPaymentId}. ".$oSelect->Error());
		}
		
		$aData = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aData[$aRow['id']] = new self($aRow);
		}
		
		return $aData;
	}

	public static function factory($sName, $mValue, $mReferences=null, $aSchema=null) {
		if (!$aSchema) {
			if (!isset(self::$aSchema[$sName])) {
				throw new Exception("No schema defined or provided for data field '{$sName}'");
			}
			$aSchema	= self::$aSchema;
		}
		
		if (!isset($aSchema[$sName]['iDataType'])) {
			throw new Exception("Schema for '{$sName}' does not define a Data Type (iDataType)");
		}
		
		$iDataTypeId = $aSchema[$sName]['iDataType'];
		
		// Data
		$oTransactionData				= new self();
		$oTransactionData->name			= $sName;
		$oTransactionData->value		= Data_Type::encode($mValue, $iDataTypeId);
		$oTransactionData->data_type_id	= $iDataTypeId;
		
		// References
		// Payment, Logic_Payment, Payment_Response
		$aReferences	= array();
		if (is_object($mReferences) && ($mReferences instanceof ORM || $mReferences instanceof DataLogic)) {
			$aReferences	= array(get_class($mReferences)=>$mReferences);
		} elseif (is_array($mReferences)) {
			$aReferences	= $mReferences;
		} elseif ($mReferences !== null) {
			throw new Exception('References must be an ORM or DataLogic object or an array');
		}

		if (count($aReferences)) {
			foreach ($aReferences as $sType=>$mReference) {
				switch ((string)$sType) {
					case 'payment_id':
						$oTransactionData->payment_id = (int)$mReference;
						break;
					case 'payment_response_id':
						$oTransactionData->payment_response_id = (int)$mReference;
						break;
					case 'Payment':
					case 'Logic_Payment':
						if ($mReference instanceof $sType) {
							$oTransactionData->payment_id = (int)$mReference->id;
						}
						break;
					case 'Payment_Response':
						if ($mReference instanceof $sType) {
							$oTransactionData->payment_response_id = (int)$mReference->id;
						}
						break;

					default:
						throw new Exception("Unhandled reference type '{$sType}'");
						break;
				}
			}
		}

		return $oTransactionData;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selByPaymentOrPaymentResponse':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "payment_id = <payment_id> OR payment_response_id = <payment_response_id>");
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