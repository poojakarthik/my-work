<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Payment
 *
 * @author JanVanDerBreggen
 */
class Logic_Payment implements DataLogic, Logic_Distributable{

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
    
	public function reverse($iReversalReasonId)
	{
		$oDataAccess = DataAccess::getDataAccess();
		if ($oDataAccess->TransactionStart() === false)
		{
			throw new Exception("Failed to start transaction");
		}
		
		try
		{
			// Create the reversal payment
			$oReversal	= $this->oDO->reverse($iReversalReasonId);
			$oAccount	= Logic_Account::getInstance($this->oDO->account_id);
			$oAccount->processDistributable(new Logic_Payment($oReversal));
			
			// Check the reason type
			$oReason = Payment_Reversal_reason::getForId($iReversalReasonId);
			if ($oReason->payment_reversal_type_id == PAYMENT_REVERSAL_TYPE_DISHONOUR)
			{
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
		Logic_Account::getInstance($this->account_id)->processDistributable($this);
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
			if (false === DataAccess::getDataAccess()->TransactionStart()) {
				throw new Exception_Database(DataAccess::getDataAccess()->Error());
			}

			try {
				// Distribute the Payment
				$oPayment->distribute();

				// FIXME: Do we need to save here?
				$oPayment->save();
			} catch (Exception $oException) {
				// Rollback and rethrow
				if (false === DataAccess::getDataAccess()->TransactionRollback()) {
					throw new Exception_Database(DataAccess::getDataAccess()->Error());
				}
				throw $oException;
			}

			// Commit
			if (false === DataAccess::getDataAccess()->TransactionCommit()) {
				throw new Exception_Database(DataAccess::getDataAccess()->Error());
			}
		}

		Log::getLog()->log("Processed {$iTotalPayments} Payments in ".round($oStopwatch->lap(), 1).'s');
	}
    
    // factory: Creates a Logic_Payment object, as well as surcharge if credit card payment (and flagged to be created).
    // 			aConfig is an array of values that will be used if passed correctly
    //				- credit_card_type_id			: If the payment is a credit card payment, used when creating a surcharge (if flagged to do so)
    //				- charge_credit_card_surcharge	: If true, and the payment is a credit card payment, charge the surcharge determined by the credit card type			
	//				- credit_card_number			: If the payment is a credit card payment, used to store in the payment_transaction_data
    public static function factory($iAccountId, $iPaymentTypeId, $fAmount, $iPaymentNature=PAYMENT_NATURE_PAYMENT, $sTransactionReference='', $sPaidDate=null, $aConfig=array())
	{
		try
		{
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception_Database("Failed to start db transaction.");
			}
			
			$sPaidDate 	= ($sPaidDate === null ? date('Y-m-d') : $sPaidDate);
			$oAccount	= Account::getForId($iAccountId);
			
			$oCreditCardType		= null;
			$fCreditCardSurcharge 	= null;
			if (($iPaymentTypeId == PAYMENT_TYPE_CREDIT_CARD) && isset($aConfig['credit_card_type_id']))
			{
				// Calculate amount and surcharge (only applied if credit card payment)
				$oCreditCardType 		= Credit_Card_Type::getForId($aConfig['credit_card_type_id']);
				$fCreditCardSurcharge	= $fAmount * $oCreditCardType->surcharge;
				$fAmount 				= $fAmount + $fCreditCardSurcharge;
			}
			
			// Create payment
			$oPayment 							= new Payment();
			$oPayment->account_id 				= $iAccountId;
			$oPayment->created_datetime			= date('Y-m-d H:i:s');
			$oPayment->created_employee_id		= Flex::getUserId();
			$oPayment->paid_date				= $sPaidDate;
			$oPayment->payment_type_id			= $iPaymentTypeId;
			$oPayment->transaction_reference	= $sTransactionReference;
			$oPayment->payment_nature_id		= $iPaymentNature;
			$oPayment->amount 					= Rate::roundToRatingStandard($fAmount, 2);
			$oPayment->balance 					= $oPayment->amount;
			$oPayment->save();
			
			if ($iPaymentTypeId == PAYMENT_TYPE_CREDIT_CARD)
			{
				if ($aConfig['charge_credit_card_surcharge'])
				{
					// Create a charge for the transaction surcharge
					$oCharge					= new Charge();
					$oCharge->AccountGroup		= $oAccount->AccountGroup;
					$oCharge->Account			= $oAccount->Id;
					$oCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
					$oCharge->Amount			= Rate::roundToRatingStandard(RemoveGST($fCreditCardSurcharge), 2);
					$oCharge->CreatedOn			= date('Y-m-d H:i:s');
					$oCharge->ChargedOn			= date('Y-m-d H:i:s');
					$oCharge->Status			= CHARGE_APPROVED;
					$oCharge->LinkType			= CHARGE_LINK_PAYMENT;
					$oCharge->LinkId			= $oPayment->id;
					$oCharge->ChargeType		= 'CCS';
					$oCharge->Nature			= 'DR';
					$oCharge->global_tax_exempt	= 0;
					$oCharge->Description		= "{$oCreditCardType->name} Surcharge for Payment on ".date('d/m/Y')." ({$oPament->amount}) @ ".(round(floatval($oCreditCardType->surcharge) * 100, 2))."%";
					$oCharge->charge_model_id	= CHARGE_MODEL_CHARGE;
					$oCharge->Notes				= '';
					$oCharge->save();
					
					// Update the payments surcharge charge link
					$oPayment->surcharge_charge_id = $oCharge->Id;
					$oPayment->save();
				}
				
				// Create payment_transaction_data record
				$oTransactionData 				= new Payment_Transaction_Data();
				$oTransactionData->name			= Payment_Transaction_Data::CREDIT_CARD_NUMBER;
				$oTransactionData->value		= Credit_Card::getMaskedCardNumber($aConfig['credit_card_number']);
				$oTransactionData->data_type_id	= DATA_TYPE_STRING; 
				$oTransactionData->payment_id	= $oPayment->id;
				$oTransactionData->save();
			}
			
			// Process the payment
			$oNewLogic 		= new self($oPayment);
			$oLogicAccount 	= Logic_Account::getInstance($iAccountId);
			$oLogicAccount->processDistributable($oNewLogic);
			
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

	public static function create() {
		$aArguments	= func_get_args();
		return Reflectors::getClass(__CLASS__)->newInstanceArgs($aArguments);
	}
}
?>
