<?php
/**
 * Payment_Response
 *
 * @class	Payment_Response
 */
class Payment_Response extends ORM_Cached
{
	protected 			$_strTableName			= "payment_response";
	protected static	$_strStaticTableName	= "payment_response";
	
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

	public static function getForStatus($iStatus)
	{
		// Get data
		$oStmt		= self::_preparedStatment('selByStatus');
		$mResult	= $oStmt->Execute(array('payment_response_status_id' => $iStatus));
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to get Payment Responses for status '{$iStatus}'. ".$oStmt->Error());
		}
		
		// Convert to ORM objects
		$aResults	= array();
		while ($aRow = $oStmt->Fetch())
		{
			$oORM					= new self($aRow);
			$aResults[$oORM->id]	= $oORM;
		}
		return $aResults;
	}
	
	public static function getLatestForPayment()
	{
		$oGetLatestForPayment	= self::_preparedStatement('selLatestForPayment');
		if ($oGetLatestForPayment->Execute() === false)
		{
			throw new Exception_Database($oGetLatestForPayment->Error());
		}
		if ($aLatestForPayment = $oGetLatestForPayment->Fetch())
		{
			return new self($aLatestForPayment);
		}
		else
		{
			return null;
		}
	}
	
	public function action()
	{
		if ($this->payment_response_status_id !== PAYMENT_RESPONSE_STATUS_PROCESSED)
		{
			throw new Exception('Only Processed Payment Requests can be actioned');
		}
		elseif (!$this->payment_id)
		{
			// Create a Payment if we haven't been associated with one
			// Any linking to existing Payments should be done at Normalisation, prior to this step
			$oPayment					= new Payment();
			
			$oPayment->AccountGroup		= $this->account_group_id;
			$oPayment->Account			= $this->account_id;
			$oPayment->PaidOn			= $this->effective_date;
			$oPayment->PaymentType		= $this->payment_type_id;
			$oPayment->Amount			= $this->amount;
			$oPayment->TXNReference		= $this->transaction_reference;
			$oPayment->OriginId			= $this->origin_id;
			$oPayment->EnteredBy		= Employee::SYSTEM_EMPLOYEE_ID;
			$oPayment->Balance			= $this->amount;
			$oPayment->Status			= PAYMENT_WAITING;
			$oPayment->created_datetime	= null;
			
			// FIXME: These will probably be removed sometime soon
			$oPayment->carrier			= (!$this->file_import_data_id) ? null : File_Import::getForId(File_Import_Data::getForId($this->file_import_data_id)->file_import_id)->Carrier;
			$oPayment->OriginType		= ($this->origin_id) ? $this->payment_type_id : $this->payment_type_id;
			$oPayment->Payment			= (!$this->file_import_data_id) ? null : File_Import_Data::getForId($this->file_import_data_id)->data;
			$oPayment->File				= (!$this->file_import_data_id) ? null : File_Import_Data::getForId($this->file_import_data_id)->file_import_id;
			$oPayment->SequenceNo		= (!$this->file_import_data_id) ? null : File_Import_Data::getForId($this->file_import_data_id)->sequence;
			
			$oPayment->save();
			
			// Get logic object for payment
			$oLogicPayment = new Logic_Payment($oPayment);
			
			// Surcharges
			$oLogicPayment->applySurcharges();
			
			// HACK: Old-style Credit Card Surcharges
			// This function can return FALSE for both critical and acceptable failures, so
			// we can't really handle it.
			AddCreditCardSurcharge($oPayment->id);
			
			$this->payment_id = $oPayment->id;
			$this->save();
			
			// Process the payment
			$oLogicPayment->distribute();
		}
		else
		{
			// Already associated with a Payment
			$oPayment		= Payment::getForId($this->payment_id);
			$oLogicPayment 	= new Logic_Payment($oPayment);
		}
		
		// Update the latest payment response of the payment
		$oLogicPayment->latest_payment_response_id = $this->id;
		$oLogicPayment->save();
		
		// Make sure that the Payment is up-to-date
		$oLogicPayment->applyPaymentResponses();
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
				case 'selLatestForPayment':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(
																					self::$_strStaticTableName,
																					"*",
																					"payment_id = <payment_id>",
																					"(payment_reponse_type_id = ".PAYMENT_RESPONSE_TYPE_REJECTION.") DESC, effective_date DESC",
																					1
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