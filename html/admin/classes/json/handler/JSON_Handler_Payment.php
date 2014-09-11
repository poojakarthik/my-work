<?php

class JSON_Handler_Payment extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			Log::getLog()->log("Getting payment dataset ".($bCountOnly ? '(Count Only)' : '')."...");
			$iRecordCount = Payment::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}

			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Payment::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;

			foreach ($aData as $aRecord)
			{
				$aRecord['extra_detail_enabled']	= $bUserIsGod;
				$aResults[$i] 						= $aRecord;
				$i++;
			}

			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}

	public function getForId($iPaymentId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oPayment = Payment::getForId($iPaymentId);
			return array('bSuccess' => true, 'oPayment' => ($oPayment ? $oPayment->toStdClass() : null));
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}

	public function reversePayment($iPaymentId, $iReasonId, $oReplacementPaymentDetails=null) {
		try {
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false) {
				throw new Exception_Database("Failed to start db transaction");
			}

			try {
				// Reverse payment
				$oPayment = new Logic_Payment(Payment::getForId($iPaymentId));
				$oPayment->reverse($iReasonId);

				// Create replacement payment
				$mPaymentId = null;
				if ($oReplacementPaymentDetails !== null) {
					$aErrors = self::_validatePaymentDetails($oReplacementPaymentDetails);
					if (count($aErrors) > 0) {
						return array('bSuccess' => false, 'aErrors' => $aErrors);
					}

					// Add credit card surcharge if need be
					$fAmount = $oReplacementPaymentDetails->amount;
					$bChargeSurcharge = ($oReplacementPaymentDetails->credit_card_type_id !== null);
					$aTransactionData = array();
					if ($bChargeSurcharge) {
						Log::getLog()->log("Surcharge to be applied");
						$oCardType = Credit_Card_Type::getForId($oReplacementPaymentDetails->credit_card_type_id);
						$fAmount = $fAmount + $oCardType->calculateSurcharge($fAmount);

						// Add transaction data for the card number
						$aTransactionData[Payment_Transaction_Data::CREDIT_CARD_NUMBER] = Credit_Card::getMaskedCardNumber($oReplacementPaymentDetails->credit_card_number);

						Log::getLog()->log("New amount {$fAmount}");
					}

					$oPayment =	Logic_Payment::factory(
						$oReplacementPaymentDetails->account_id,
						$oReplacementPaymentDetails->payment_type_id,
						$fAmount,
						PAYMENT_NATURE_PAYMENT,
						$oReplacementPaymentDetails->transaction_reference,
						date('Y-m-d', DataAccess::getDataAccess()->getNow(true)),
						array('aTransactionData' => $aTransactionData)
					);

					if ($bChargeSurcharge) {
						// Apply credit card surcharge
						Log::getLog()->log("Applying surcharge");
						$oPayment->applyCreditCardSurcharge($oReplacementPaymentDetails->credit_card_type_id);
					}

					$mPaymentId = $oPayment->id;
				}
			} catch (Exception $oEx) {
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception_Database("Failed to rollback db transaction");
				}

				throw $oEx;
			}

			if ($oDataAccess->TransactionCommit() === false) {
				throw new Exception_Database("Failed to commit db transaction");
			}

			return array('bSuccess' => true, 'iPaymentId' => $mPaymentId);
		} catch (Exception $oEx) {
			return array(
				'bSuccess' => false,
				'sMessage' => $oEx->getMessage(),
				'sExceptionClass' => get_class($oEx)
			);
		}
	}

	public function createPayment($oDetails) {
		try {
			$aErrors = self::_validatePaymentDetails($oDetails);
			if (count($aErrors) > 0) {
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}

			$fAmount = $oDetails->amount;
			$bChargeSurcharge = $oDetails->charge_surcharge && ($oDetails->credit_card_type_id !== null);
			$aTransactionData = array();
			if ($bChargeSurcharge) {
				Log::getLog()->log("Credit card surcharge to be applied");
				$oCardType 	= Credit_Card_Type::getForId($oDetails->credit_card_type_id);
				$fAmount	= $fAmount + $oCardType->calculateSurcharge($fAmount);

				// Add transaction data for the card number
				$aTransactionData[Payment_Transaction_Data::CREDIT_CARD_NUMBER] = Credit_Card::getMaskedCardNumber($oDetails->credit_card_number);

				Log::getLog()->log("New amount {$fAmount}");
			}

			$oPayment =	Logic_Payment::factory(
				$oDetails->account_id,
				$oDetails->payment_type_id,
				$fAmount,
				PAYMENT_NATURE_PAYMENT,
				$oDetails->transaction_reference,
				$oDetails->paid_date ? $oDetails->paid_date : date('Y-m-d', DataAccess::getDataAccess()->getNow(true)),
				array('aTransactionData' => $aTransactionData)
			);

			if ($bChargeSurcharge) {
				// Apply credit card surcharge
				$oPayment->applyCreditCardSurcharge($oDetails->credit_card_type_id);
			}

			return array('bSuccess' => true, 'iPaymentId' => $oPayment->id);
		} catch (Exception $oEx) {
			return array(
				'bSuccess' => false,
				'sMessage' => $oEx->getMessage(),
				'sExceptionClass' => get_class($oEx)
			);
		}
	}

	const MAKE_PAYMENT_MAXIMUM_AGE_DAYS = 30;
	private static function _validatePaymentDetails($oDetails)
	{
		// Validate input
		$aErrors = array();
		if ($oDetails->account_id === '')
		{
			$aErrors[] = 'No Account supplied.';
		}

		if ($oDetails->payment_type_id === '')
		{
			$aErrors[] = 'No Payment Type supplied.';
		}

		if ($oDetails->amount === '')
		{
			$aErrors[] = 'No Amount supplied.';
		}

		if ($oDetails->transaction_reference === '')
		{
			$aErrors[] = 'No Transaction Reference supplied.';
		}

		if ($oDetails->paid_date) {
			$sEarliestPayableDate = date('Y-m-d', strtotime('-' . self::MAKE_PAYMENT_MAXIMUM_AGE_DAYS . ' days', time()));
			$sCurrentDate = date('Y-m-d');

			$iPaidTimestamp = strtotime($oDetails->paid_date);
			$iEarliestPayableTimestamp = strtotime($sEarliestPayableDate);
			$iCurrentTimestamp = strtotime($sCurrentDate);
			if ($iPaidTimestamp < $iEarliestPayableTimestamp) {
				$aErrors[] = 'Payments must be no older than ' . self::MAKE_PAYMENT_MAXIMUM_AGE_DAYS . ' days (' . date('j M Y', $iEarliestPayableTimestamp) . ').';
			} elseif ($iPaidTimestamp > $iCurrentTimestamp) {
				$aErrors[] = 'Payments must be no newer than today.';
			}
		}

		if ($oDetails->payment_type_id == PAYMENT_TYPE_CREDIT_CARD)
		{
			if ($oDetails->credit_card_type_id === '')
			{
				$aErrors[] = 'No Credit Card Type supplied.';
			}

			if ($oDetails->credit_card_number === '')
			{
				$aErrors[] = 'No Credit Card Number Reference supplied.';
			}
			else if (!CheckLuhn($oDetails->credit_card_number))
			{
				$aErrors[]	= 'Invalid Credit Card Number';
			}
			else if (!CheckCC($oDetails->credit_card_number, $oDetails->credit_card_type_id))
			{
				$aErrors[]	= 'Invalid Credit Card Number for the Card Type';
			}
		}

		return $aErrors;
	}
}

class JSON_Handler_Payment_Exception extends Exception
{
	// No changes
}

?>