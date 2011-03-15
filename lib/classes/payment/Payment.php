<?php
/**
 * Payment_Method
 *
 * Represents a Record in the payment_method table
 *
 * @class	Payment_Method
 */
class Payment extends ORM_Cached
{
	protected		$_strTableName		= "payment";
	protected static	$_strStaticTableName	= "payment";

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


        public static function resetBalanceForAccount($iAccountId)
        {
            $oQuery = new Query();
            $sSql = "   UPDATE payment p
                        LEFT JOIN payment p2 ON ( p2.reversed_payment_id = p.id)
                        SET p.balance = IF (p2.id is not null || p.payment_nature_id = ".PAYMENT_NATURE_REVERSAL." , 0, p.amount)
                        WHERE p.account_id = $iAccountId";

            $oQuery->Execute($sSql);
        }



        public static function getForAccountId($iAccountId, $iPaymentNature = null, $bWithDistributableBalance = true)

        {


            $sWhereClause = $iPaymentNature != null ? "AND pn.id = $iPaymentNature" : "";
            $sWhereClause = $bWithDistributableBalance ?  $sWhereClause." AND p.balance > 0" : $sWhereClause;
            $sSQL = "   SELECT p.*
                        FROM payment p
                        JOIN payment_nature pn ON (p.payment_nature_id = pn.id)
                        WHERE p.account_id = $iAccountId
                         $sWhereClause
                        ";
            $oQuery = new Query();
            $mResult = $oQuery->Execute($sSQL);
            $aResult = array();
            if ($mResult)

            while ($aRecord = $mResult->fetch_assoc())

            {
                $aResult[] = new self($aRecord);
            }

        mysqli_free_result($mResult);

        return $aResult;
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
	
	public function reverse($iReasonId)
	{
		$oReason	= Payment_Reversal_Reason::getForId($iReasonId);
		$oReversal	= new Payment();
		
		// Copy fields from this payment
		$oReversal->account_id				= $this->account_id;
		$oReversal->carrier_id 				= $this->carrier_id;
		$oReversal->payment_type_id 		= $this->payment_type_id;
		$oReversal->transaction_reference 	= $this->transaction_reference;
		$oReversal->amount 					= $this->amount;
		$oReversal->balance 				= $this->balance;
		$oReversal->invoice_id 				= $this->invoice_id;
		$oReversal->invoice_run_id 			= $this->invoice_run_id;
		
		// Different fields
		$oReversal->paid_date 					= date('Y-m-d');
		$oReversal->created_employee_id 		= Flex::getUserId();
		$oReversal->created_datetime 			= date('Y-m-d H:i:s');
		$oReversal->surcharge_charge_id			= null;
		$oReversal->latest_payment_response_id	= null;
		
		// Reversal specific fields
		$oReversal->payment_nature_id 			= PAYMENT_NATURE_REVERSAL;
		$oReversal->reversed_payment_id 		= $this->id;
		$oReversal->payment_reversal_type_id 	= $oReason->payment_reversal_type_id;
		$oReversal->payment_reversal_reason_id 	= $iReasonId;
		
		$oReversal->save();
		
		// Deal with any surcharge related to the payment
		$mSurchargeAction = null;
		if ($this->surcharge_charge_id !== null)
		{
			$oCharge = Charge::getForId($this->surcharge_charge_id);
			switch ($oCharge->Status)
			{
				case CHARGE_INVOICED:
				case CHARGE_TEMP_INVOICE:
					if (Invoice_Run::getForId($oCharge->invoice_run_id)->isProductionRun())
					{
						// TODO: CR137 -- Charge or adjustment here?
						/*
						// Production Invoices
						// Add a negating Credit
						$oSurchargeCredit = clone $oCharge;
						
						$oSurchargeCredit->CreatedOn		= date("Y-m-d");
						$oSurchargeCredit->ChargedOn		= date("Y-m-d");
						$oSurchargeCredit->CreatedBy		= $oReversal->created_employee_id;
						$oSurchargeCredit->ApprovedBy		= null;
						$oSurchargeCredit->Nature			= 'CR';
						$oSurchargeCredit->Description		= "Payment Reversal: ".$oCharge->Description;
						$oSurchargeCredit->Status			= CHARGE_APPROVED;
						$oSurchargeCredit->invoice_run_id	= null;
						$oSurchargeCredit->charge_model_id	= CHARGE_MODEL_CHARGE;
						$oSurchargeCredit->save();
						
						$mSurchargeAction = "A new charge has been created to credit the Account: {$oCharge->Account} for the invoiced payment surcharge of \$". number_format(AddGST($oCharge->Amount), 2, ".", "");
						*/
						break;
					}
					// If we're a non-Production Invoice Run, then fall through to CHARGE_APPROVED clause
					
				case CHARGE_APPROVED:
					// Mark as Deleted
					$oCharge->Status = CHARGE_DELETED;
					$oCharge->save();
	
					$mSurchargeAction = "The yet-to-be-invoiced surcharge charge of \$". number_format(AddGST($oCharge->Amount), 2, ".", "") ." has been deleted from Account: {$oCharge->Account}";
					break;
			}
		}
		
		// Add a Note
		$sReversedChargesClause	= ($mSurchargeAction ? "{$mSurchargeAction}" : '');
		$sEmployeeName 			= ($oReversal->created_employee_id ? Employee::getForId($oReversal->created_employee_id)->getName() : 'Administrators');
		$sDate 					= date("d/m/Y", strtotime($this->paid_date));
		$oNote					= new Note();
		$oNote->Note			= "{$sEmployeeName} Reversed a Payment made on {$sDate} for \$". number_format($this->amount, 2, ".", "")."\nThe reason was '{$oReason->name} ({$oReason->description})'.\n$sReversedChargesClause";
		$oNote->AccountGroup	= Account::getForId($this->account_id)->AccountGroup;
		$oNote->Account			= $this->account_id;
		$oNote->Datetime		= date('Y-m-d H:i:s');
		$oNote->NoteType		= Note::SYSTEM_NOTE_TYPE_ID;
		$oNote->save();
		
		return $oReversal;		
	}
	
	public static function searchFor($bCountOnly, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases =	array(
						'payment_id' 		=> "p.id",
						'payment_type_name' => "pt.name",
						'paid_date'			=> "p.paid_date",
						'amount'			=> "p.amount",
						'account_id'		=> "p.account_id",
						'is_reversed'		=> "IF(p_reversed.id IS NULL, 0, 1)",
						'created_datetime'	=> "p.created_datetime"
					);
		
		$sFrom		= "				payment p
						JOIN		payment_type pt ON (pt.id = p.payment_type_id)
						LEFT JOIN	payment p_reversed ON (p_reversed.reversed_payment_id = p.id)";
		
		if ($bCountOnly)
		{
			$sSelect 	= "COUNT(p.id) AS count";
			$sOrderBy	= "";
			$sLimit		= "";
		}
		else
		{
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause)
			{
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect	= implode(', ', $aSelectLines);
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere	= $aWhere['sClause'];
		$sWhere	.= ($sWhere != '' ? " AND " : '')."p.reversed_payment_id IS NULL";	
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to get payment search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
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