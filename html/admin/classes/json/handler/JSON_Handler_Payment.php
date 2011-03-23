<?php

class JSON_Handler_Payment extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
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
	
	public function reversePayment($iPaymentId, $iReasonId, $oReplacementPaymentDetails=null)
	{
		$bUserIsGod	 = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oDataAccess	= DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception_Database("Failed to start db transaction");
			}
			
			try
			{
				// Reverse payment
				$oPayment = new Logic_Payment(Payment::getForId($iPaymentId));
				$oPayment->reverse($iReasonId);
				
				// Create replacement payment
				$mPaymentId = null;
				if ($oReplacementPaymentDetails !== null)
				{
					$aErrors = self::_validatePaymentDetails($oReplacementPaymentDetails);
					if (count($aErrors) > 0)
					{
						return array('bSuccess' => false, 'aErrors' => $aErrors);
					}
					
					// Add credit card surcharge if need be
					$fAmount			= $oReplacementPaymentDetails->amount;
					$bChargeSurcharge	= ($oReplacementPaymentDetails->credit_card_type_id !== null);
					$aTransactionData	= array();
					if ($bChargeSurcharge)
					{
						Log::getLog()->log("Surcharge to be applied");
						$oCardType 	= Credit_Card_Type::getForId($oReplacementPaymentDetails->credit_card_type_id);
						$fAmount	= $fAmount + $oCardType->calculateSurcharge($fAmount);
						
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
									array(
										'aTransactionData' => $aTransactionData
									)
								);
					
					if ($bChargeSurcharge)
					{
						// Apply credit card surcharge
						Log::getLog()->log("Applying surcharge");
						$oPayment->applyCreditCardSurcharge($oReplacementPaymentDetails->credit_card_type_id);
					}
					
					$mPaymentId = $oPayment->id;
				}
			}
			catch (Exception $e)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception_Database("Failed to rollback db transaction");
				}
				throw $e;
			}
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception_Database("Failed to commit db transaction");
			}
			
			return array('bSuccess' => true, 'iPaymentId' => $mPaymentId, 'sDebug' => ($bUserIsGod ? $this->_JSONDebug : ''));
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'),
						'sDebug' 	=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
		}
	}
	
	public function createPayment($oDetails)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aErrors = self::_validatePaymentDetails($oDetails);
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}
			
			$fAmount			= $oDetails->amount;
			$bChargeSurcharge	= ($oDetails->credit_card_type_id !== null);
			$aTransactionData	= array();
			if ($bChargeSurcharge)
			{
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
							date('Y-m-d', DataAccess::getDataAccess()->getNow(true)),
							array(
								'aTransactionData' => $aTransactionData
							)
						);
			
			if ($bChargeSurcharge)
			{
				// Apply credit card surcharge
				$oPayment->applyCreditCardSurcharge($oDetails->credit_card_type_id);
			}
			
			return array('bSuccess' => true, 'iPaymentId' => $oPayment->id);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
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