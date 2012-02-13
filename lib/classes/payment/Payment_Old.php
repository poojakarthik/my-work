<?php
/**
 * Payment
 *
 * Represents a Record in the Payment table
 *
 * @class	Payment
 */
class Payment extends ORM_Cached
{
	protected 			$_strTableName			= "Payment";
	protected static	$_strStaticTableName	= "Payment";

	const DEBUG_LOGGING = true;
	
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
			Log::getLog()->logIf(self::DEBUG_LOGGING, "Reseting Payment Balance for Account #{$iAccountId}");
            $oQuery = new Query();
            $sSql = "   UPDATE payment p
                        LEFT JOIN payment p2 ON ( p2.reversed_payment_id = p.id)
                        SET p.balance = IF (p2.id is not null || p.payment_nature_id = 2 , 0, p.amount)
                        WHERE p.account_id = $iAccountId";

            $sSql = "UPDATE payment SET balance = amount WHERE account_id = $iAccountId";
            $oQuery->Execute($sSql);
            $sSql = "UPDATE payment p
                    JOIN payment p2 ON ( p2.reversed_payment_id = p.id AND p.account_id = $iAccountId)
                     SET p2.balance = 0, p.balance = 0 ";
        }

        public static function getForAccountId($iAccountId, $iPaymentNature = null)
        {

            $sPaymentTypeWhereClause = $iPaymentNature != null ? "AND pn.id = $iPaymentNature" : "";
            $sSQL = "   SELECT a.*
                        FROM payment p
                        JOIN payment_nature pn ON (p.payment_nature_id = pn.id)
                        WHERE p.account_id = $iAccountId
                         $sPaymentTypeWhereClause
                        ";
            $oQuery = new Query();
            $mResult = $oQuery->Execute($sSQL);
            $aResult = array();
            if ($mResult)
            {
                while ($aRecord = $mResult->fetch_assoc())
                {
                    $aResult[] = new self($aRecord);
                }
            }

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
	
	public function applySurcharges()
	{
		// Get Payment Merchant details
		if ($oCarrierPaymentType = Carrier_Payment_Type::getForCarrierAndPaymentType($this->carrier, $this->PaymentType))
		{
			// Calculate Surcharge
			$fSurcharge	= $oCarrierPaymentType->calculateSurcharge($this->Amount);
			
			// Apply Charge
			$oCharge	= null;
			if ($fSurcharge > 0.0)
			{
				$oChargeType	= Charge_Type::getByCode('PMF');
				
				$oCharge					= new Charge();
				
				$oCharge->AccountGroup		= $this->AccountGroup;
				$oCharge->Account			= $this->Account;
				$oCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
				$oCharge->CreatedOn			= date('Y-m-d');
				$oCharge->ApprovedBy		= Employee::SYSTEM_EMPLOYEE_ID;
				$oCharge->ChargeType		= $oChargeType->ChargeType;
				$oCharge->charge_type_id	= $oChargeType->Id;
				$oCharge->Description		= $oCarrierPaymentType->description
											.' Surcharge for Payment on '.date('d/m/Y', strtotime($this->PaidOn))
											.' of $'.(number_format($this->Amount, 2, '.', ''))
											.' @ '.round($oCarrierPaymentType->surcharge_percent * 100, 2).'%';
				$oCharge->ChargedOn			= $this->PaidOn;
				$oCharge->Nature			= 'DR';
				$oCharge->Amount			= round($fSurcharge, 2);
				$oCharge->LinkType			= CHARGE_LINK_PAYMENT;
				$oCharge->LinkId			= $this->Id;
				$oCharge->Status			= CHARGE_APPROVED;
				$oCharge->Notes				= '';
				$oCharge->global_tax_exempt	= 0;
				
				$oCharge->save();
			}
			
			return $oCharge;
		}
		else
		{
			return null;
		}
	}
	
	public function getSurcharges()
	{
		$oGetSurcharges	= self::_preparedStatement('selSurcharges');
		if (false === $oGetSurcharges->Execute(array('payment_id'=>$this->Id)))
		{
			throw new Exception_Database($oGetSurcharges->Error());
		}
		$aRecords	= array();
		while ($aRecord = $oGetSurcharges->Fetch())
		{
			$aRecords[$aRecord['Id']]	= new Charge($aRecord);
		}
		return $aRecords;
	}
	
	public function reverse($iReversedBy=null)
	{
		// Find all InvoicePayment Records
		$aInvoicePayments	= Invoice_Payment::getForPayment($this);
		foreach ($aInvoicePayments as $oInvoicePayment)
		{
			// Add back to Invoice Balance
			$oInvoice			= $oInvoicePayment->getInvoice();
			$oInvoice->Balance	+= $oInvoicePayment->Amount;
			$oInvoice->save();
			
			// Remove the Invoice Payment Record
			$oInvoicePayment->delete();
		}
		unset($aInvoicePayments);
		
		// Set Payment.Balance to Payment.Amount and Payment.Status to PAYMENT_REVERSED
		$this->Balance	= $this->Amount;
		$this->Status	= PAYMENT_REVERSED;
		
		// Remove or Credit any Surcharges
		$aSurchargeActions	= array();
		$aSurcharges		= $this->getSurcharges();
		foreach ($aSurcharges as $oCharge)
		{
			switch ($oCharge->Status)
			{
				case CHARGE_INVOICED:
				case CHARGE_TEMP_INVOICE:
					if (Invoice_Run::getForId($oCharge->invoice_run_id)->isProductionRun())
					{
						// Production Invoices
						// Add a negating Credit
						$oSurchargeCredit	= clone $oCharge;
						
						$oSurchargeCredit->CreatedOn		= date("Y-m-d");
						$oSurchargeCredit->ChargedOn		= date("Y-m-d");
						$oSurchargeCredit->CreatedBy		= $iReversedBy;
						$oSurchargeCredit->ApprovedBy		= null;
						$oSurchargeCredit->Nature			= 'CR';
						$oSurchargeCredit->Description		= "Payment Reversal: ".$oCharge->Description;
						$oSurchargeCredit->Status			= CHARGE_APPROVED;
						$oSurchargeCredit->invoice_run_id	= null;
						$oSurchargeCredit->charge_model_id	= CHARGE_MODEL_CHARGE;
						
						$oSurchargeCredit->save();
						
						$aSurchargeActions[$oCharge->Id] = "A new charge has been created to credit the Account: {$oCharge->Account} for the invoiced payment surcharge of \$". number_format(AddGST($oCharge->Amount), 2, ".", "");
						break;
					}
					// If we're a non-Production Invoice Run, then fall through to CHARGE_APPROVED clause
					
				case CHARGE_APPROVED:
					// Mark as Deleted
					$oCharge->Status	= CHARGE_DELETED;
					$oCharge->save();
					
					$aSurchargeActions[$oCharge->Id]	= "The yet-to-be-invoiced surcharge charge of \$". number_format(AddGST($oCharge->Amount), 2, ".", "") ." has been deleted from Account: {$oCharge->Account}";
					break;
			}
		}
		
		// Add a Note
		// Do we have an employee?
		if ($iReversedBy)
		{
			$oEmployee		= Employee::getForId($iReversedBy);
			$sEmployeeName	= "{$oEmployee->FirstName} {$oEmployee->LastName}";
		}
		else
		{
			$sEmployeeName	= "Administrators";
		}
		
		$sDate = date("d/m/Y", strtotime($this->PaidOn));
		
		// Work out if the payment was applied to an AccountGroup, or a specific Account
		if ($this->Account != NULL)
		{
			// The payment has been made to a specific account
			$sAccountClause	= "a Payment";
		}
		else
		{
			// The payment has been applied to an AccountGroup
			$sAccountClause	= "an AccountGroup Payment";
		}
		
		// Build the Reversed Charges clause
		$sReversedChargesClause	= (!count($aSurchargeActions)) ? '' : "\nThe following associated actions have also taken place:\n" . implode("\n", $aSurchargeActions);
		
		// Add the note
		$oNote	= new Note();
		$oNote->Note			= "{$sEmployeeName} Reversed {$sAccountClause} made on {$sDate} for \$". number_format($this->Amount, 2, ".", "") . $sReversedChargesClause;
		$oNote->AccountGroup	= $this->AccountGroup;
		$oNote->Account			= $this->Account;
		$oNote->Datetime		= Data_Source_Time::currentTimestamp();
		$oNote->NoteType		= Note::SYSTEM_NOTE_TYPE_ID;
		$oNote->save();
	}
	
	public function applyPaymentResponses()
	{
		/*	There are essentially only two results from this:
			
				1: No changes to Payment
				2: Reverse Payment
				
			Reversed Payments can't be unreversed (or reversed again), so just return out
		*/
		if (!in_array($this->Status, PAYMENT_WAITING, PAYMENT_PAYING, PAYMENT_FINISHED))
		{
			return;
		}
		elseif ($oLatestPaymentResponse	= Payment_Response::getLatestForPayment($this))
		{
			switch ($oLatestPaymentResponse->payment_response_type_id)
			{
				case PAYMENT_RESPONSE_TYPE_CONFIRMATION:
					// Nothing really to do, as you can't un-reverse a payment
					break;
					
				case PAYMENT_RESPONSE_TYPE_REJECTION:
					// Reverse the Payment
					$this->reverse();
					break;
			}
		}
	}
	
	public function process()
	{
		if (!in_array($this->Status, array(PAYMENT_WAITING, PAYMENT_PAYING)))
		{
			throw new Exception("Only WAITING or PAYING Payments can be processed");
		}
		
		$oStopwatch	= new Stopwatch();
		$oStopwatch->start();
		
		// Mark as Paying
		$this->Status	= PAYMENT_PAYING;
		
		// Get all related Invoices with Balances
		Log::getLog()->log("Getting payable Invoices");
		$oGetPayableInvoices	= self::_preparedStatement('selPayableInvoices');
		if (false === $oGetPayableInvoices->Execute(array('account_id'=>$this->Account,'accoun_group_id'=>$this->AccountGroup)))
		{
			throw new Exception_Database($oGetPayableInvoices->Error());
		}
		$iTotalInvoices	= $oGetPayableInvoices->Count();
		$iCount			= 0;
		while ($aInvoice = $oGetPayableInvoices->Fetch())
		{
			$iCount++;
			Log::getLog()->log("({$iCount}/{$iTotalInvoices}) Invoice #{$aInvoice['Id']}");
			if ($this->Balance > 0)
			{
				// Pay out the Invoice as much as possible
				$oInvoice	= new Invoice($aInvoice);
				
				// Determine Payable Amount
				$fInvoicePreBalance		= (float)$oInvoice->Balance;
				$fInvoicePostBalance	= max(0, $fInvoicePreBalance - $this->Balance);
				$fPayableAmount			= ($fInvoicePreBalance - $fInvoicePostBalance);
				
				Log::getLog()->log("[+] Paying \${$fInvoicePreBalance} with \${$this->Balance}, leaving \${$fInvoicePostBalance} remaining");
				
				// Add an InvoicePayment Record
				$oInvoicePayment	= new Invoice_Payment();
				$oInvoicePayment->invoice_run_id	= $oInvoice->invoice_run_id;
				$oInvoicePayment->Account			= $oInvoice->Account;
				$oInvoicePayment->AccountGroup		= $oInvoice->AccountGroup;
				$oInvoicePayment->Payment			= $this->Id;
				$oInvoicePayment->Amount			= $fPayableAmount;
				$oInvoicePayment->save();
				
				// Save the Invoice
				$oInvoice->Balance	-= $fPayableAmount;
				$oInvoice->Status	= ($oInvoice->Balance > 0) ? $oInvoice->Statue : INVOICE_SETTLED;
				$oInvoice->save();
				
				// Update our Balance
				$this->Balance	-= $fPayableAmount;
			}
			else
			{
				// Skip this Invoice -- no Balance to distribute
				Log::getLog()->log("[~] Skipping \${$fInvoicePreBalance} as there is no Payment Balance remaining");
			}
		}
		
		// Assign appropriate Status
		if ($this->Balance > 0)
		{
			Log::getLog()->log("[+] Marking Payment as Finished");
			$this->Status	= PAYMENT_FINISHED;
		}
		
		$this->save();
		
		Log::getLog()->log("Paid {$iTotalInvoices} Invoice in ".round($oStopwatch->lap(), 1).'s');
	}
	
	public static function processAll()
	{
		$oStopwatch	= new Stopwatch();
		$oStopwatch->start();
		
		Log::getLog()->log("Getting a list of payable Payments");
		$oGetPayablePayments	= self::_preparedStatement('selPayablePayments');
		if (false === $oGetPayablePayments->Execute())
		{
			throw new Exception_Database($oGetPayablePayments->Error());
		}
		$iTotalPayments	= $oGetPayablePayments->Count();
		$iCount			= 0;
		while ($aPayment = $oGetPayablePayments->Fetch())
		{
			$iCount++;
			Log::getLog()->log("({$iCount}/{$iTotalPayments}) Payment #{$aPayment['Id']}");
			$oPayment	= new Payment($aPayment);
			
			// Encase each Payment in a Transaction
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception_Database(DataAccess::getDataAccess()->Error());
			}
			
			try
			{
				// Process the Payment
				$aPayment->process();
			}
			catch (Exception $oException)
			{
				// Rollback and pass through
				DataAccess::getDataAccess()->TransactionRollback();
				throw $oException;
			}
			
			// Commit
			DataAccess::getDataAccess()->TransactionCommit();
		}
		
		Log::getLog()->log("Processed {$iTotalPayments} Payments in ".round($oStopwatch->lap(), 1).'s');
	}
	
	// Override
	public function save()
	{
		if ($this->id == NULL)
		{
			// New payment, set the created_datetime value
			$this->created_datetime	= date('Y-m-d H:i:s');
			
			// New payment, auto-set Origin Type
			if (!$this->OriginType && $this->OriginId)
			{
				$this->OriginType	= $this->PaymentType;
			}
		}
		parent::save();
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
				case 'selSurcharges':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	'Charge',
																					'*',
																					"Nature = 'DR' AND LinkType = ".CHARGE_LINK_PAYMENT." AND LinkId = <payment_id>"
																				);
					break;
				case 'selPayableInvoices':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	'Invoice',
																					'*',
																					"Status = ".INVOICE_COMMITTED." AND (AccountGroup = <account_group_id> OR Account = <account_id> AND Balance > 0",
																					'DueOn ASC, CreatedOn ASC, Id ASC'
																				);
				case 'selPayablePayments':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Status IN (".PAYMENT_WAITING.", ".PAYMENT_PAYING.")");
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