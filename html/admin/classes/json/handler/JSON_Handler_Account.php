<?php

class JSON_Handler_Account extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAccountsReferees()
	{
		try
		{
			$qryQuery	= new Query();
			
			// Get list of referees (everyone with PERMISSION_CREDIT_MANAGEMENT and without PERMISSION_GOD)
			$arrReferees	= array();
			$resReferees	= $qryQuery->Execute("SELECT * FROM Employee WHERE user_role_id = ".USER_ROLE_CREDIT_CONTROL_MANAGER." AND Archived = 0");
			if ($resReferees === false)
			{
				throw new Exception($qryQuery->Error());
			}
			while ($arrReferee = $resReferees->fetch_assoc())
			{
				$arrReferees[]	= $arrReferee;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : '',
							"arrReferees"	=> $arrReferees,
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : '',
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function getPaymentMethods($iAccountId, $iPaymentMethodSubType)
	{
		try
		{
			$aResult						= array();
			$oAccountGroup  				= Account_Group::getForAccountId($iAccountId);
			$oAccount						= Account::getForId($iAccountId);
			$bhasCreditControlPermission	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_CONTROL);
			
			if(!$oAccountGroup)
			{
				throw new JSON_Handler_Account_Exception('Invalid Account Id');
			}
			
			// Check billing type to see what to return
			if ($iPaymentMethodSubType == DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				// Get all DirectDebit for the accountgroup
				$aDirectDebits	= DirectDebit::getForAccountGroup($oAccountGroup->Id);
				
				foreach ($aDirectDebits as $oDirectDebit)
				{
					$aResult[]	= $oDirectDebit->toStdClass();
				}
			}
			else if ($iPaymentMethodSubType == DIRECT_DEBIT_TYPE_CREDIT_CARD)
			{
				// Get all Credit_Card for the accountgroup
				$aCreditCards	= Credit_Card::getForAccountGroup($oAccountGroup->Id);
				
				foreach ($aCreditCards as $oCreditCard)
				{
					$oStdClassCreditCard	= $oCreditCard->toStdClass();
					
					// Get the card type name
					$oStdClassCreditCard->card_type_name	= Constant_Group::getConstantGroup('credit_card_type')->getConstantName($oCreditCard->CardType);
					
					// Get the card number and cvv
					$sCardNumber	= Decrypt($oCreditCard->CardNumber).'';
					$sCVV			= (is_null($oCreditCard->CVV) ? '' : Decrypt($oCreditCard->CVV).'');
					
					// Hide card number and cvv if the user doesn't have sufficient priviledges
					if (!$bhasCreditControlPermission)
					{
						$sCardNumber	= $oCreditCard->getMaskedCardNumber($sCardNumber);
						$sCVV			= ($sCVV == '' ? 'Not Supplied' : 'Supplied');
					}
					
					$oStdClassCreditCard->card_number	= $sCardNumber;
					$oStdClassCreditCard->cvv			= $sCVV;
					$aResult[]							= $oStdClassCreditCard;
				}
			}
			
			return 	array(
						"Success"			=> true,
						"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : '',
						"aPaymentMethods"	=> $aResult,
					);
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getCurrentPaymentMethod($iAccountId)
	{
		try
		{
			$aResult						= array();
			$oAccountGroup  				= Account_Group::getForAccountId($iAccountId);
			$oAccount						= Account::getForId($iAccountId);
			$bhasCreditControlPermission	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_CONTROL);
			
			if(!$oAccountGroup)
			{
				throw new JSON_Handler_Account_Exception('Invalid Account Id');
			}
			
			$oPaymentMethod			= false;
			$iPaymentMethod			= null;
			$iPaymentMethodSubType	= null;
			$aHasPaymentMethod		= 	array(
											PAYMENT_METHOD_ACCOUNT		=> array(),
											PAYMENT_METHOD_DIRECT_DEBIT	=> array(),
											PAYMENT_METHOD_REBILL		=> array(),
										);
			
			switch ($oAccount->BillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
				case BILLING_TYPE_CREDIT_CARD:
					$iPaymentMethod	= PAYMENT_METHOD_DIRECT_DEBIT;
					break;
				case BILLING_TYPE_ACCOUNT:
					$iPaymentMethod	= PAYMENT_METHOD_ACCOUNT;
					break;
				case BILLING_TYPE_REBILL:
					$iPaymentMethod	= PAYMENT_METHOD_REBILL;
					break;
			}
			
			// Get all DirectDebit for the accountgroup to see if there is any
			$aDirectDebits	= DirectDebit::getForAccountGroup($oAccountGroup->Id);
			
			foreach ($aDirectDebits as $oDirectDebit)
			{
				$aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_BANK_ACCOUNT]	 = true;
				
				if ($oAccount->BillingType == BILLING_TYPE_DIRECT_DEBIT)
				{
					if ($oAccount->DirectDebit == $oDirectDebit->Id)
					{
						$iPaymentMethodSubType	= DIRECT_DEBIT_TYPE_BANK_ACCOUNT;
						$oPaymentMethod			= $oDirectDebit->toStdClass();
					}
				}
				else
				{
					break;
				}
			}
			
			// Get all Credit_Card for the accountgroup to see if there is any
			$aCreditCards	= Credit_Card::getForAccountGroup($oAccountGroup->Id);
			
			foreach ($aCreditCards as $oCreditCard)
			{
				$aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_CREDIT_CARD]	 = true;
				
				if ($oAccount->BillingType == BILLING_TYPE_CREDIT_CARD)
				{
					if ($oAccount->CreditCard == $oCreditCard->Id)
					{
						$oStdClassCreditCard	= $oCreditCard->toStdClass();
				
						// Get the card type name
						$oStdClassCreditCard->card_type_name	= Constant_Group::getConstantGroup('credit_card_type')->getConstantName($oCreditCard->CardType);
						
						// Get the card number and cvv
						$sCardNumber	= Decrypt($oCreditCard->CardNumber).'';
						$sCVV			= (is_null($oCreditCard->CVV) ? '' : Decrypt($oCreditCard->CVV).'');
						
						// Hide card number and cvv if the user doesn't have sufficient priviledges
						if (!$bhasCreditControlPermission)
						{
							$sCardNumber	= $oCreditCard->getMaskedCardNumber($sCardNumber);
							$sCVV			= ($sCVV == '' ? 'Not Supplied' : 'Supplied');
						}
						
						$oStdClassCreditCard->card_number	= $sCardNumber;
						$oStdClassCreditCard->cvv			= $sCVV;
						$oPaymentMethod						= $oStdClassCreditCard;
						$iPaymentMethodSubType				= DIRECT_DEBIT_TYPE_CREDIT_CARD;
					}
				}
				else
				{
					break;
				}
			}
			
			// Get the latest rebill for the account
			$oRebill	= $oAccount->getRebill();
			
			if ($oRebill)
			{
				$aHasPaymentMethod[PAYMENT_METHOD_REBILL][$oRebill->rebill_type_id]	 = true;
				
				if ($oAccount->BillingType == BILLING_TYPE_REBILL)
				{
					$oRebillDetails					= $oRebill->getDetails();
					$oPaymentMethod					= $oRebill->toStdClass();
					$oPaymentMethod->account_number	= $oRebillDetails->account_number;
					$iPaymentMethodSubType			= $oRebill->rebill_type_id;
				}
			}
			
			switch ($oAccount->BillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					$iBillingDetail	= $oAccount->DirectDebit;
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$iBillingDetail	= $oAccount->CreditCard;
					break;
				case BILLING_TYPE_REBILL:
					$iBillingDetail	= $oPaymentMethod->id;
					break;
				default:
					$iBillingDetail = false;
			}
			
			// Get the available billing types for the accounts customer group
			$aPaymentMethods	= $oAccount->getPaymentMethods();
			
			if (is_null($iPaymentMethod) || is_null($iPaymentMethodSubType))
			{
				// Could not retrieve correct payment method details, maybe the cc/account was deleted, send back 'ACCOUNT'
				$iPaymentMethod			= PAYMENT_METHOD_ACCOUNT;
				$iPaymentMethodSubType	= null;
			}
			
			return 	array(
						"Success"				=> true,
						"iPaymentMethod"		=> $iPaymentMethod,
						"iPaymentMethodSubType"	=> $iPaymentMethodSubType,
						"oPaymentMethod"		=> $oPaymentMethod,
						"iBillingDetail"		=> $iBillingDetail,
						"aPaymentMethods"		=> $aPaymentMethods,
						"aHasPaymentMethod"		=> $aHasPaymentMethod
					);
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function setPaymentMethod($iAccountId, $iPaymentMethodType, $iPaymentMethodSubType, $iBillingDetail)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'There was an error accessing the database' : '',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to set the payment method');
			}
			
			// Update billing type
			$oAccount	= Account::getForId($iAccountId);
			
			// Get the old billing type description
			$sOldBillingType	= '';
			
			switch ($oAccount->BillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					$sAccountName	= 'Unknown';
					$sAccountNumber	= 'Unknown';
					
					try
					{
						$oDirectDebit	= DirectDebit::getForId($oAccount->DirectDebit);
						$sAccountName	= $oDirectDebit->AccountName;
						$sAccountNumber	= $oDirectDebit->AccountNumber;
					}
					catch (Exception $e)
					{
						// No direct debit exists
					}
					
					$sOldBillingType	= 	"Direct Debit via Bank Account\n".
											"Account Name: {$sAccountName}\n".
											"Account Number: {$sAccountNumber}";
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$sCardName		= 'Unknown';
					$sCardNumber	= 'Unknown';
					
					try
					{
						$oCreditCard	= Credit_Card::getForId($oAccount->CreditCard);
						$sCardName		= $oCreditCard->Name;
						$sCardNumber	= "XXXXXXXXXXXX".substr(Decrypt($oCreditCard->CardNumber), -4);
					}
					catch (Exception $e)
					{
						// No credit card exists
					}
				
					$sOldBillingType	= 	"Direct Debit via Credit Card\n".
											"Card Name: {$sCardName}\n".
											"Card Number: {$sCardNumber}";
					break;
				case BILLING_TYPE_ACCOUNT:	// Invoice
					$sOldBillingType	= 'Invoice';
					break;
				
				case BILLING_TYPE_REBILL:
					$oOldRebill			= Rebill::getForAccountId($oAccount->Id, true);
					
					if ($oOldRebill)
					{
						$oOldRebillDetails	= $oOldRebill->getDetails();
					
						switch ($oOldRebill->rebill_type_id)
						{
							case REBILL_TYPE_MOTORPASS:
								$sOldBillingType	= 	"Rebill via Motorpass\n" .
														"Account Number: {$oOldRebillDetails->account_number}";
								break;
						}
					}
					else
					{
						// This will only happen if the old rebill doesn't exist, so it shouldn't happen
						$sOldBillingType	= 	"Rebill (unknown)";
					}
					break; 
			}
			
			// Determin the billing type (legacy concept) from the payment method and sub type
			$iBillingType	= BILLING_TYPE_ACCOUNT;
			
			switch ($iPaymentMethodType)
			{
				case PAYMENT_METHOD_ACCOUNT:
					$iBillingType	= BILLING_TYPE_ACCOUNT;
					break;
				case PAYMENT_METHOD_DIRECT_DEBIT:
					switch ($iPaymentMethodSubType)
					{
						case DIRECT_DEBIT_TYPE_CREDIT_CARD:
							$iBillingType	= BILLING_TYPE_CREDIT_CARD;
							break;
						case DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
							$iBillingType	= BILLING_TYPE_DIRECT_DEBIT;
							break;
					}
					break;
				case PAYMENT_METHOD_REBILL:
					$iBillingType	= BILLING_TYPE_REBILL;
					break;
			}
			
			$oAccount->BillingType	= $iBillingType;
			
			// Reset detail values first
			$oAccount->DirectDebit	= ($iBillingType == BILLING_TYPE_DIRECT_DEBIT ? $iBillingDetail : null);
			$oAccount->CreditCard	= ($iBillingType == BILLING_TYPE_CREDIT_CARD ? $iBillingDetail : null);
			
			// Update proper detail field
			$oDetails			= $oAccount->getPaymentMethodDetails();			 
			$sNewBillingType	= '';
			switch ($iBillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					$sNewBillingType	= 	"Direct Debit via Bank Account\n".
											"Account Name: {$oDetails->AccountName}\n".
											"Account Number: {$oDetails->AccountNumber}";
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$sNewBillingType	= 	"Direct Debit via Credit Card\n".
											"Card Name: {$oDetails->Name}\n".
											"Card Number: XXXXXXXXXXXX".substr(Decrypt($oDetails->CardNumber), -4);
					break;
				case BILLING_TYPE_ACCOUNT:
					$sNewBillingType	= 'Invoice';
					break;
				
				case BILLING_TYPE_REBILL:
					$oRebillTypeDetails	= $oDetails->getDetails();
					
					switch ($oDetails->rebill_type_id)
					{
						case REBILL_TYPE_MOTORPASS:
							$sNewBillingType	= 	"Rebill via Motorpass\n" .
													"Account Number: {$oRebillTypeDetails->account_number}";
							break;
					}
					break; 
			}
			
			$oAccount->save();
			
			// Add a note
			$sNote = "Payment method changed from:\n $sOldBillingType\n to $sNewBillingType";
			Note::createNote(SYSTEM_NOTE_TYPE, $sNote, Flex::getUserId(), $iAccountId);				
			
			// All good
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true);
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getCreditCardTypes()
	{
		try
		{
			$aTypes		= Credit_Card_Type::listAll();
			$aResult	= array();
			
			foreach ($aTypes as $iId => $oType)
			{
				$aResult[$iId]	=	array(
										'name' 				=> $oType->name,
										'valid_lengths' 	=> $oType->valid_lengths,
										'valid_prefixes' 	=> $oType->valid_prefixes,
										'cvv_length' 		=> $oType->cvv_length
									);
			}
			
			// All good
			return 	array(
						"Success"	=> true,
						"aTypes"	=> $aResult,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function addCreditCard($iAccountId, $oDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to add a credit card');
			}
			
			// Create orm object
			$oCreditCard	= new Credit_Card();
			
			// Set the account group
			$oAccountGroup				= Account_Group::getForAccountId($iAccountId);
			$oCreditCard->AccountGroup	= $oAccountGroup->Id;
			
			// Default values, that aren't supplied by interface
			$oCreditCard->Archived		= 0;
			$oCreditCard->employee_id	= Flex::getUserId();
			
			// Validate input
			$aErrors	= array();
			if (!is_numeric($oDetails->iCardType))
			{
				$aErrors[]	= 'Card Type missing';
			}
			
			if (!isset($oDetails->sCardHolderName) || $oDetails->sCardHolderName == '')
			{
				$aErrors[]	= 'Card Holder Name missing';
			}
			
			if (!is_numeric($oDetails->iCardNumber))
			{
				$aErrors[]	= 'Credit Card Number missing';
			}
			
			if (!CheckLuhn($oDetails->iCardNumber))
			{
				$aErrors[]	= 'Invalid Credit Card Number';
			}
			
			if (!CheckCC($oDetails->iCardNumber, $oDetails->iCardType))
			{
				$aErrors[]	= 'Invalid Credit Card Number for the Card Type';
			}
			
			if (!is_numeric($oDetails->iExpiryMonth))
			{
				$aErrors[]	= 'Expiration Month missing';
			}
			
			if (!is_numeric($oDetails->iExpiryYear))
			{
				$aErrors[]	= 'Expiration Year missing';
			}
			
			if (!is_numeric($oDetails->iCVV))
			{
				$aErrors[]	= 'CVV missing';
			}
			
			$oCardType	= Credit_Card_Type::getForId($oDetails->iCardType);
			if (!preg_match('/^\d{'.$oCardType->cvv_length.'}$/', "{$oDetails->iCVV}"))
			{
				$aErrors[]	= 'CVV is an incorrect length';
			}
			
			if (count($aErrors) > 0)
			{
				// Validation errors found, rollback transaction and return the errors
				$oDataAccess->TransactionRollback();
				
				return 	array(
							"Success"			=> false,
							"aValidationErrors"	=> $aErrors,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Update object & save
				$oCreditCard->CardType		= $oDetails->iCardType;
				$oCreditCard->Name			= $oDetails->sCardHolderName;
				$oCreditCard->CardNumber	= Encrypt($oDetails->iCardNumber);
				$oCreditCard->ExpMonth		= $oDetails->iExpiryMonth;
				$oCreditCard->ExpYear		= $oDetails->iExpiryYear;
				$oCreditCard->CVV			= Encrypt($oDetails->iCVV);
				$oCreditCard->created_on	= date('Y-m-d H:i:s');
				$oCreditCard->save();
				
				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();
				
				// Get the card type name
				$oStdClassCreditCard					= $oCreditCard->toStdClass();
				$oStdClassCreditCard->card_type_name	= Constant_Group::getConstantGroup('credit_card_type')->getConstantName($oCreditCard->CardType);
				
				// Mask the card number and cvv
				$bhasCreditControlPermission	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_CONTROL);
				$sCardNumber					= Decrypt($oCreditCard->CardNumber).'';
				$sCVV							= (is_null($oCreditCard->CVV) ? '' : Decrypt($oCreditCard->CVV).'');
				
				// Hide card number and cvv if the user doesn't have sufficient priviledges
				if (!$bhasCreditControlPermission)
				{
					$sCardNumber	= $oCreditCard->getMaskedCardNumber($sCardNumber);
					$sCVV			= ($sCVV == '' ? 'Not Supplied' : 'Supplied');
				}
				
				$oStdClassCreditCard->card_number	= $sCardNumber;
				$oStdClassCreditCard->cvv			= $sCVV;
				
				// All good
				return 	array(
							"Success"		=> true,
							"oCreditCard"	=> $oStdClassCreditCard,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function addDirectDebit($iAccountId, $oDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to add a direct debit');
			}
			
			// Create orm object
			$oDirectDebit	= new DirectDebit();
			
			// Set the account group
			$oAccountGroup				= Account_Group::getForAccountId($iAccountId);
			$oDirectDebit->AccountGroup	= $oAccountGroup->Id;
			
			// Default values, that aren't supplied by interface
			$oDirectDebit->Archived		= 0;
			$oDirectDebit->employee_id	= Flex::getUserId();
			
			// Validate input
			$aErrors	= array();
			
			if (!isset($oDetails->sBankName) || $oDetails->sBankName == '')
			{
				$aErrors[]	= 'Bank Name missing';
			}
			
			if (!isset($oDetails->sBSB) || $oDetails->sBSB == '')
			{
				$aErrors[]	= 'BSB missing';
			}
			
			if (!BSBValid($oDetails->sBSB))
			{
				$aErrors[]	= 'Invalid BSB';
			}
			
			if (!isset($oDetails->sAccountNumber) || $oDetails->sAccountNumber == '')
			{
				$aErrors[]	= 'Account Number missing';
			}
			
			if (!BankAccountValid($oDetails->sAccountNumber))
			{
				$aErrors[]	= 'Invalid Account Number';
			}
			
			if (!isset($oDetails->sAccountName) || $oDetails->sAccountName == '')
			{
				$aErrors[]	= 'Account Name missing';
			}
			
			if (count($aErrors) > 0)
			{
				// Validation errors found, rollback transaction and return the errors
				$oDataAccess->TransactionRollback();
				
				return 	array(
							"Success"			=> false,
							"aValidationErrors"	=> $aErrors,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Update object & save
				$oDirectDebit->BankName			= $oDetails->sBankName;
				$oDirectDebit->BSB				= $oDetails->sBSB;
				$oDirectDebit->AccountNumber	= $oDetails->sAccountNumber;
				$oDirectDebit->AccountName		= $oDetails->sAccountName;
				$oDirectDebit->created_on		= date('Y-m-d H:i:s');
				$oDirectDebit->save();
				
				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();
				
				// All good
				return 	array(
							"Success"		=> true,
							"oDirectDebit"	=> $oDirectDebit->toStdClass(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getCostCentres($iAccountId)
	{
		try 
		{
			$aStdObjects = array();
			$aCostCentres = Cost_Centre::getForAccountId($iAccountId);
			
			foreach ($aCostCentres as $iId => $oCostCentre)
			{
				$aStdObjects[$iId] = $oCostCentre->toStdClass();
			}
			
			return array(
							"Success"			=> true,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : '',
							"aCostCentres"		=> $aStdObjects
						);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function saveCostCentreChanges($iAccountId, $aChanges)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			$aValidationErrors = array();
			
			// Handles multiple changes
			foreach ($aChanges as $aCostCentre)
			{
				$iId 	= $aCostCentre->iId;
				$sName 	= $aCostCentre->sName;
				
				// If iId is given, get the cost centre for the id and update, otherwise create a new cost centre
				if (is_numeric($iId))
				{
					// Update existing cost centre
					$oCostCentre = Cost_Centre::getForId($iId);
				}
				else 
				{
					// New Cost centre required
					$oAccountGroup 				= Account_Group::getForAccountId($iAccountId);
					$oCostCentre 				= new Cost_Centre();
					$oCostCentre->AccountGroup	= $oAccountGroup->Id;
					$oCostCentre->Account 		= $iAccountId;
				}
				
				// Validate input
				$bValidInput = true;
				
				if (!isset($sName) || $sName == '')
				{
					$aValidationErrors[] = 'Cost Centre Name missing';
					$bValidInput = false;
				}
				
				if ($bValidInput)
				{
					// Validation passed, update the object and save
					$oCostCentre->Name = $sName;
					$oCostCentre->save();
				}
			}
			
			if (count($aValidationErrors) > 0)
			{
				// Validation errors found, rollback transaction and return errors
				$oDataAccess->TransactionRollback();
				
				return array(
							"Success"			=> false,
							"aValidationErrors"	=> $aValidationErrors,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();
				
				// Return successfully
				return 	array(
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : '',
							"iAccountId"	=> $iAccountId
						);
			}
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getRebill($iAccountId)
	{
		try
		{
			$oRebill	= Rebill::getForAccountId($iAccountId);
			$mResult	= ($oRebill ? $oRebill->toStdClass() : null);
			
			// Get the extra rebill type specific information
			switch ($oRebill->rebill_type_id)
			{
				case REBILL_TYPE_MOTORPASS:
					$oRebillMotorpass	= Rebill_Motorpass::getForRebillId($oRebill->id);
					
					// Add extra fields
					$mResult->account_number	= $oRebillMotorpass->account_number;
					break;
			}
			
			return 	array(
						"Success"	=> true,
						"oRebill"	=> $mResult
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database'
					);
		}
	}
	
	public function addRebill($iAccountId, $iRebillTypeId, $oDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
					);
		}
		
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to add a rebill');
			}
			
			$oCurrentRebill			= Rebill::getForAccountId($iAccountId);
			$oCurrentRebillDetails	= false;
			if ($oCurrentRebill)
			{
				$oCurrentRebillDetails	= $oCurrentRebill->getDetails();
			}
			
			
			// Create a new rebill
			$oRebill						= new Rebill();
			$oRebill->account_id			= $iAccountId;
			$oRebill->rebill_type_id		= $iRebillTypeId;
			$oRebill->created_employee_id	= Flex::getUserId();
			$oRebill->created_timestamp		= date('Y-m-d H:i:s');
			
			// Set the extra rebill type specific information
			switch ($iRebillTypeId)
			{
				case REBILL_TYPE_MOTORPASS:
					// Check if a save is required
					if ($oCurrentRebillDetails && ($oCurrentRebill->rebill_type_id == $iRebillTypeId) && ($oCurrentRebillDetails->account_number == $oDetails->account_number))
					{
						// No save required, the last rebill for the account is a motorpass with the same account number
						// Return the last one
						$oStdClassRebill					= $oCurrentRebill->toStdClass();
						$oStdClassRebill->account_number	= $oCurrentRebillDetails->account_number;
						
						return 	array(
									"Success"	=> true,
									"bNoChange"	=> true,
									"oRebill"	=> $oStdClassRebill
								);
					}
					
					// Save required, save the new rebill before creating the rebill_motorpass
					$oRebill->save();
					
					// Save a new Rebill_Motorpass
					$oRebillMotorpass					= new Rebill_Motorpass();
					$oRebillMotorpass->rebill_id		= $oRebill->id;
					$oRebillMotorpass->account_number	= $oDetails->account_number;
					$oRebillMotorpass->save();
					
					// Return new details
					$oStdClassRebill					= $oRebill->toStdClass();
					$oStdClassRebill->account_number	= $oRebillMotorpass->account_number;
					break;
			}
			
			// Everything looks OK -- Commit!
			$oDataAccess->TransactionCommit();
			
			return 	array(
						"Success"	=> true,
						"oRebill"	=> $oStdClassRebill
					);
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database'
					);
		}
	}
}

class JSON_Handler_Account_Exception extends Exception
{
	// No changes
}

?>