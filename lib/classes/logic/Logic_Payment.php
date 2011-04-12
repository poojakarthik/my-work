<?php

/**
 * Description of Logic_Payment
 *
 * @author JanVanDerBreggen
 */
class Logic_Payment extends Logic_Distributable implements DataLogic{

	 protected $oDO;

	public function __construct($mDefinition)
	{
		$this->oDO = $mDefinition;

	}
	   public function getPaymentNature()
	{
	   return Payment_Nature::getForId($this->payment_nature_id);
	}

	public static function getForId($iId)
	{
		$oPayment = Payment::getForId($iId);
		return $oPayment!== null ? new self($oPayment) : null;
	}

	public static function getForAccount($oAccount, $iSignType, $bWithDistributableBalance = true)
	{
		$aORM = Payment::getForAccountId($oAccount->id, $iSignType, $bWithDistributableBalance);
		$aResult = array();
		foreach ($aORM as $oORM)
		{
			$aResult[] = new self($oORM);
		}
		return $aResult;

	}

	private function refreshData()
	{
		$this->oDO = Payment::getForId($this->id);
	}
	
	public function reverse($iReversalReasonId, $bDistribute=true)
	{
		$oDataAccess = DataAccess::getDataAccess();
		if ($oDataAccess->TransactionStart() === false)
		{
			throw new Exception("Failed to start transaction");
		}
		
		try
		{
			// Create the reversal payment
			$this->oDO->reverse($iReversalReasonId, $bDistribute);
			$oAccount	= Logic_Account::getInstance($this->oDO->account_id);
			$oPromise = Logic_Collection_Promise::getForAccount($oAccount);
			
			//rather than merely distributing the reversed payment, we need to do a full redistribution of balances at this point.
			//The reaon for this is that if the original payment had any distributable balance left, this would need to be applied after distributing the reversed payment's balance in full, or else the collectable balance will be wrong.
			if ($bDistribute || ($oPromise !== NULL && $oReason->payment_reversal_type_id == PAYMENT_REVERSAL_TYPE_DISHONOUR)) {
				$oAccount->redistributeBalances();
				$this->refreshData();				
			}

			

			// Check the reason type
			$oReason = Payment_Reversal_reason::getForId($iReversalReasonId);
			if ($oReason->payment_reversal_type_id == PAYMENT_REVERSAL_TYPE_DISHONOUR)
			{
				//the rule is that a dishonoured payment scenario takes precedence over a broken promise scenario, thats why we process any active promises at this point
				
				if ($oPromise !== NULL)
					$oPromise->process();

				// Change scenario for the account, dishonoured payment
				$oConfig = Collection_Scenario_System_Config::getForSystemScenario(COLLECTION_SCENARIO_SYSTEM_DISHONOURED_PAYMENT);
				if (!$oConfig)
				{
					throw new Exception("Failed to retrieve system scenario for dishonoured payment.");
				}
				$oAccount->setCurrentScenario($oConfig->collection_scenario_id, false);
			}
		}
		catch (Exception $oEx)
		{
			if ($oDataAccess->TransactionRollback() === false)
			{
				throw new Exception("Failed to rollback transaction");
			}
			throw $oEx;
		}
		
		if ($oDataAccess->TransactionCommit() === false)
		{
			throw new Exception("Failed to commit transaction");
		}
	}

	public function getReversal() {
		$oReversingPayment	= $this->oDO->getReversal();
		return $oReversingPayment ? new self($oReversingPayment) : null;
	}

	public function applyPaymentResponses($bDistribute=true) {
		/*	There are essentially only two results from this:
			
				1: No changes to Payment
				2: Reverse Payment
				
			Reversed Payments can't be unreversed (or reversed again), so just return out
		*/
		if ($this->getReversal() instanceof self) {
			// Already reversed
			Log::getLog()->log("Payment is reversed and no longer mutable by Payment Responses");
			return;
		} elseif ($oLatestPaymentResponse = Payment_Response::getForId($this->oDO->latest_payment_response_id)) {
			Log::getLog()->log($this->oDO->latest_payment_response_id." - ".$oLatestPaymentResponse->payment_response_type_id);
			switch ($oLatestPaymentResponse->payment_response_type_id) {
				case PAYMENT_RESPONSE_TYPE_CONFIRMATION:
					// Nothing really to do, as you can't un-reverse a payment
					Log::getLog()->log("Payment Confirmed");
					break;
					
				case PAYMENT_RESPONSE_TYPE_REJECTION:
					// Not yet reversed, Reverse the Payment
					$this->reverse(
						isset($oLatestPaymentResponse->payment_reversal_reason_id) ? $oLatestPaymentResponse->payment_reversal_reason_id : Payment_Reversal_Reason::getForSystemName('DISHONOUR_REVERSAL')->id,
						$bDistribute
					);
					Log::getLog()->log("Payment Reversed");
					break;
			}
		}
	}
	
	public function applySurcharges() {
		// Get Payment Merchant details
		if ($oCarrierPaymentType = Carrier_Payment_Type::getForCarrierAndPaymentType($this->oDO->carrier_id, $this->oDO->payment_type_id)) {
			// Calculate Surcharge
			$fSurcharge	= $oCarrierPaymentType->calculateSurcharge($this->oDO->amount);
			
			// Apply Charge
			$oCharge = null;
			if ($fSurcharge > 0.0) {
				$oChargeType = Charge_Type::getByCode('PMF');
				
				$oCharge					= new Charge();
				$oCharge->AccountGroup		= Account::getForId($this->oDO->account_id)->AccountGroup;
				$oCharge->Account			= $this->oDO->account_id;
				$oCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
				$oCharge->CreatedOn			= date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
				$oCharge->ApprovedBy		= Employee::SYSTEM_EMPLOYEE_ID;
				$oCharge->ChargeType		= $oChargeType->ChargeType;
				$oCharge->charge_type_id	= $oChargeType->Id;
				$oCharge->Description		= $oCarrierPaymentType->description
											.' Surcharge for Payment on '.date('d/m/Y', strtotime($this->paid_date))
											.' of $'.(number_format($this->amount, 2, '.', ''))
											.' @ '.round($oCarrierPaymentType->surcharge_percent * 100, 2).'%';
				$oCharge->ChargedOn			= $this->paid_date;
				$oCharge->Nature			= 'DR';
				$oCharge->Amount			= round($fSurcharge, 2);
				$oCharge->LinkType			= CHARGE_LINK_PAYMENT;
				$oCharge->LinkId			= $this->oDO->id;
				$oCharge->Status			= CHARGE_APPROVED;
				$oCharge->Notes				= '';
				$oCharge->global_tax_exempt	= 0;
				
				$oCharge->save();
				
				Log::getLog()->log("Surcharge applied {$oCharge->Id}");
			}
			
			return $oCharge;
		} else {
			return null;
		}
	}
	
	public function applyCreditCardSurcharge($iCreditCardType=null) {
		if ($this->oDO->payment_type_id != PAYMENT_TYPE_CREDIT_CARD) {
			// Payment doesn't qualify, no error, just don't do it.
			Log::getLog()->log("Payment is not a credit card payment, no surcharge (payment type id = {$this->oDO->payment_type_id}).");
			return null;
		}
		
		// Get the credit card type
		$oCreditCardType = null;
		if ($iCreditCardType === null) {
			// Get the credit card type from the number in the transaction data
			$aPaymentTransactionData = Payment_Transaction_Data::getForPayment($this->oDO->id, $this->oDO->latest_payment_response_id);
			foreach ($aPaymentTransactionData as $oData) {
				if ($oData->name == Payment_Transaction_Data::CREDIT_CARD_NUMBER) {
					$oCreditCardType = Credit_Card_Type::getForCardNumber($oData->value);
					break;
				}
			}
		} else {
			// Use the supplied credit card type id
			$oCreditCardType = Credit_Card_Type::getForId($iCreditCardType);
		}
		
		if ($oCreditCardType === null) {
			throw new Exception("Cannot create credit card surcharge, could not determine credit card type.");
		}
		
		$oAccount = Account::getForId($this->oDO->account_id);
		
		// Calculate surcharge, also remove global tax component
		$fSurcharge = $oCreditCardType->calculateSurcharge($this->oDO->amount);
		$oTaxType	= Tax_Type::getGlobalTaxType();
		if ($oTaxType !== null)
		{
			// Remove global tax component
			$fSurcharge	= $fSurcharge / (1 + $oTaxType->rate_percentage);
			Log::getLog()->log("Removed global tax: {$fSurcharge}");
		}
		
		$sNow = DataAccess::getDataAccess()->getNow();
		
		// Create a charge for the transaction surcharge
		$oCharge					= new Charge();
		$oCharge->AccountGroup		= $oAccount->AccountGroup;
		$oCharge->Account			= $oAccount->Id;
		$oCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
		$oCharge->Amount			= Rate::roundToCurrencyStandard($fSurcharge, 2);
		$oCharge->CreatedOn			= $sNow;
		$oCharge->ChargedOn			= $sNow;
		$oCharge->Status			= CHARGE_APPROVED;
		$oCharge->LinkType			= CHARGE_LINK_PAYMENT;
		$oCharge->LinkId			= $this->oDO->id;
		$oCharge->ChargeType		= 'CCS';
		$oCharge->Nature			= 'DR';
		$oCharge->global_tax_exempt	= 0;
		$oCharge->Description		= "{$oCreditCardType->name} Surcharge for Payment on ".date('d/m/Y', strtotime($this->oDO->paid_date))." ({$this->oDO->amount}) @ ".(round(floatval($oCreditCardType->surcharge) * 100, 2))."%";
		$oCharge->charge_model_id	= CHARGE_MODEL_CHARGE;
		$oCharge->Notes				= '';
		$oCharge->save();
		
		Log::getLog()->log("Credit Card surcharge created '{$oCharge->Description}'.");
		
		// Update the payments surcharge charge link
		$this->oDO->surcharge_charge_id = $oCharge->Id;
		$this->oDO->save();
		
		return $oCharge;
	}
	
	public function __get($sField) {
	   
	   switch($sField)
	   {
		   case 'balance':
		   case 'amount':
			   return Rate::roundToRatingStandard($this->oDO->$sField, 4);
			default:
				return $this->oDO->$sField;
	   }
	
	}

	public function __set($sField, $mValue) {

		switch($sField)
	   {
		   case 'balance':
		   case 'amount':
			   $this->oDO->$sField = Rate::roundToRatingStandard($mValue, 4);
			default:
			   $this->oDO->$sField = $mValue;
	   }
	}

	public function save() {
		$this->oDO->save();
	}

	public function toArray() {

	}

	public function isCredit() {
		return $this->payment_nature_id == PAYMENT_NATURE_PAYMENT;
	}

	public function isDebit() {
		return $this->payment_nature_id == PAYMENT_NATURE_REVERSAL;
	}

	public static function getAllDistributable($mDatasetType=__CLASS__) {
		$mResult	= Query::run("
			SELECT		*
			FROM		payment
			WHERE		balance > 0
		");
		$aResults	= array();
		while ($aPayment = $mResult->fetch_assoc()) {
			$aResults[$aPayment['id']]	= reset(Payment::importResult($aPayment))->toDatasetType($mDatasetType);
		}
		return $aResults;
	}



	public function distribute() {
		// We're temporarily always redistributing
		//Logic_Account::getInstance($this->account_id)->processDistributable($this);
		Logic_Account::getInstance($this->account_id)->redistributeBalances();
		//at this point, refresh the object, as the balance was most likely affected by the redistribution process
		$this->refreshData();
	}

	// Optionally accepts an array of Payments (array values can be Logic_Payment, Payment, or a Payment Id)
	public static function distributeAll(array $aPayments=null) {
		$oStopwatch	= new Stopwatch();
		$oStopwatch->start();

		// Get Dataset
		if (!$aPayments && !is_array($aPayments)) {
			// Get all distributable Payments as Logic_Payment instances
			Log::getLog()->log("Getting a list of payable Payments");
			$aPayments	= self::getAllDistributable();
		} else {
			Log::getLog()->log("Set of ".count($aPayments)." Payments supplied");
		}

		// Distribute!
		$iTotalPayments	= count($aPayments);
		$iProgress		= 0;
		foreach ($aPayments as $mPayment) {
			// Allow $mPayment to be a Logic_Payment, Payment, or Payment Id
			$oPayment	= ($mPayment instanceof self) ? $mPayment : new self(Payment::getForId($mPayment));

			$iProgress++;
			Log::getLog()->log("({$iProgress}/{$iTotalPayments}) Payment #{$$oPayment->id}");

			// Encase each Payment in a Transaction
			DataAccess::getDataAccess()->TransactionStart(false);

			try {
				// Distribute the Payment (auto-saved)
				$oPayment->distribute();
			} catch (Exception $oException) {
				// Rollback and rethrow
				DataAccess::getDataAccess()->TransactionRollback(false);
				throw $oException;
			}

			// Commit
			DataAccess::getDataAccess()->TransactionCommit(false);
		}

		Log::getLog()->log("Processed {$iTotalPayments} Payments in ".round($oStopwatch->split(), 4).'s');
	}

    
    // factory: Creates a Logic_Payment object, as well as surcharge if credit card payment (and flagged to be created).
    // 			aConfig is an array of values that will be used if passed:
    //				- aTransactionData 		: array of key => value items which will create payment_transaction_data records linked to the payment
    //				- iPaymentResponseId	: the payment response, responsible for the payment
    //				- iCarrierId			: the carrier_id of the record

	public static function factory($iAccountId, $iPaymentTypeId, $fAmount, $iPaymentNature=PAYMENT_NATURE_PAYMENT, $sTransactionReference='', $sPaidDate=null, $aConfig=array(), $bDistribute=true)
	{
		try
		{
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception_Database("Failed to start db transaction.");
			}
			
			$iNow		= DataAccess::getDataAccess()->getNow(true);
			$sPaidDate 	= ($sPaidDate === null ? date('Y-m-d', $iNow) : $sPaidDate);
			$oAccount	= Account::getForId($iAccountId);
			$oUser 		= Flex::getUser();
			
			// Create payment
			$oPayment 							= new Payment();
			$oPayment->account_id 				= $iAccountId;
			$oPayment->created_datetime			= date('Y-m-d H:i:s', $iNow);
			$oPayment->created_employee_id		= ($oUser instanceof Employee ? $oUser->Id : Employee::SYSTEM_EMPLOYEE_ID);
			$oPayment->paid_date				= $sPaidDate;
			$oPayment->payment_type_id			= $iPaymentTypeId;
			$oPayment->transaction_reference	= $sTransactionReference;
			$oPayment->payment_nature_id		= $iPaymentNature;
			$oPayment->amount 					= Rate::roundToRatingStandard($fAmount, 2);
			$oPayment->balance 					= $oPayment->amount;
			
			if (isset($aConfig['iPaymentResponseId']) && $aConfig['iPaymentResponseId'] !== null)
			{
				$oPayment->latest_payment_response_id = $aConfig['iPaymentResponseId'];
			}
			
			if (isset($aConfig['iCarrierId']) && $aConfig['iCarrierId'] !== null)
			{
				$oPayment->carrier_id = $aConfig['iCarrierId'];
			}
			
			$oPayment->save();
			
			if (isset($aConfig['aTransactionData']) && $aConfig['aTransactionData'] !== null)
			{
				foreach ($aConfig['aTransactionData'] as $sName => $mValue)
				{
					$mReferences = $oPayment;
					if (isset($aConfig['iPaymentResponseId']) && $aConfig['iPaymentResponseId'] !== null)
					{
						$mReferences = array('payment_id' => $oPayment->id, 'payment_response_id' => $aConfig['iPaymentResponseId']);
					}
					Log::getLog()->log("Adding payment_transaction_data: $sName => $mValue");
					//$oTransactionData = Payment_Transaction_Data::factory($sName, $mValue, $mReferences);
					//$oTransactionData->save();
				}
			}
			
			// Process the payment
			$oNewLogic 		= new self($oPayment);
			$oLogicAccount 	= Logic_Account::getInstance($iAccountId);
			
			// Simply distributing the balance of the new adjustment is not enough
			// because there might be other distributables that could previously not distribute their balance
			// which after distributing this one can distribute theirs.
			// 
			// Example	: if this adjustment is a debit adjustment, and before distributing it the sum(collectable.balance) === 0, and there is a payment with remaining balance, that payment's balance must be distributed after distributing the current adjustment's balance.
			// @TODO	: in order to optimise this, implement functionality that allows for just distributing balances of any distributables that currently have a balance remaining, instead of a full redistribution.
			//$oLogicAccount->redistributeBalances();
			if ($bDistribute) {
				$oNewLogic->distribute();
			}
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception_Database("Failed to commit db transaction.");
			}
			
			return $oNewLogic;
		}
		catch (Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			throw $oException;
		}
	}

	public function getAccount() {
		return Logic_Account::getInstance($this->account_id);
	}

	public static function create() {
		$aArguments	= func_get_args();
		return Reflectors::getClass(__CLASS__)->newInstanceArgs($aArguments);
	}
}
?>
