<?php
class Payment_Request extends ORM_Cached {
	protected $_strTableName = "payment_request";
	protected static $_strStaticTableName = "payment_request";
	
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
	// START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
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
	// END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function generatePending($iAccount, $iPaymentType, $fAmount, $iInvoiceRun=null, $iEmployeeId=null, $iPaymentId=null) {
		if ($iEmployeeId === null) {
			$iEmployeeId = Flex::getUserId();
			$iEmployeeId = ($iEmployeeId === NULL ? Employee::SYSTEM_EMPLOYEE_ID : $iEmployeeId);
		}
		
		// Create new payment_request
		$oPaymentRequest = new Payment_Request();
		$oPaymentRequest->account_id = $iAccount;
		$oPaymentRequest->amount = $fAmount;
		$oPaymentRequest->payment_type_id = $iPaymentType;
		$oPaymentRequest->payment_request_status_id = PAYMENT_REQUEST_STATUS_PENDING;
		$oPaymentRequest->invoice_run_id = $iInvoiceRun;
		$oPaymentRequest->payment_id = $iPaymentId;
		$oPaymentRequest->created_datetime = DataAccess::getDataAccess()->getNow();
		$oPaymentRequest->created_employee_id = $iEmployeeId;
		$oPaymentRequest->save();
		
		return $oPaymentRequest;
	}

	public static function getForStatusAndCustomerGroupAndPaymentType($iStatus, $iCustomerGroup, $iPaymentType, $bIncludeReversedPayments=false) {
		// Get data
		$oStmt = self::_preparedStatement('selByStatusAndCustomerGroupAndPaymentType');
		$mResult = $oStmt->Execute(
			array(
				'customer_group_id' => $iCustomerGroup, 
				'payment_request_status_id' => $iStatus,
				'payment_type_id' => $iPaymentType,
				'include_reversed_payments' => (int)$bIncludeReversedPayments
			)
		);
		if ($mResult === false) {
			throw new Exception_Database("Failed to get Payment Requests for customer group '{$iCustomerGroup}, status '{$iStatus}' & payment type '{$iPaymentType}'. ".$oStmt->Error());
		}
		
		// Convert to ORM objects (Payment_Request)
		$aResults = array();
		while ($aRow = $oStmt->Fetch()) {
			$oORM = new self($aRow);
			$aResults[$oORM->id] = $oORM;
		}
		return $aResults;
	}

	public static function getForStatus($iStatus) {
		// Get data
		$oStmt = self::_preparedStatment('selByStatus');
		$mResult = $oStmt->Execute(array('payment_request_status_id' => $iStatus));
		if ($mResult === false) {
			throw new Exception_Database("Failed to get Payment Requests for status '{$iStatus}'. ".$oStmt->Error());
		}
		
		// Convert to ORM objects (Payment_Request)
		$aResults = array();
		while ($aRow = $oStmt->Fetch()) {
			$oORM = new self($aRow);
			$aResults[$oORM->id] = $oORM;
		}
		return $aResults;
	}

	public function generateTransactionReference() {
		return "{$this->account_id}R{$this->id}";
	}
	
	public static function getForInvoice($mInvoice, $bIncludeCancelled=false) {
		$mResult = Query::run("
			SELECT pr.*
			FROM payment_request pr
					JOIN payment_request_invoice pri ON (
						pri.payment_request_id = pr.id
						AND pri.invoice_id = <invoice_id>
					)
			WHERE (
						<include_cancelled> = 1
						OR payment_request_status_id != ".PAYMENT_REQUEST_STATUS_CANCELLED."
					)
		", array(
			'invoice_id' => (int)ORM::extractId($mInvoice),
			'include_cancelled' => !!$bIncludeCancelled
		));
		return ($mResult->num_rows) ? new self($mResult->fetch_assoc()) : null;
	}

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param string $strStatement Name of the statement
	 *
	 * @return Statement The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = Array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		}
		else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selByStatus':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "payment_request_status_id = <payment_request_status_id>", "id ASC");
					break;
				case 'selByStatusAndCustomerGroupAndPaymentType':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						' payment_request pr
							JOIN Account a ON (a.Id = pr.account_id)
							LEFT JOIN payment p ON (p.id = pr.payment_id)
							LEFT JOIN payment p_reversal ON (p_reversal.reversed_payment_id = p.id)',
						' pr.*', 
						' a.CustomerGroup = <customer_group_id>
							AND pr.payment_request_status_id = <payment_request_status_id>
							AND pr.payment_type_id = <payment_type_id>
							AND (
								pr.payment_id IS NULL
								OR <include_reversed_payments> = 1
								OR p_reversal.id IS NULL
							)',
						' pr.id ASC'
					);
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