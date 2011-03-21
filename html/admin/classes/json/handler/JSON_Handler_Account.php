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
	
	public function getForId($iAccountId)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAccount	= Account::getForId($iAccountId);
			$aAccount	= $oAccount->toArray();
			$aAccount['customer_group']	= Customer_Group::getForId($oAccount->CustomerGroup)->toArray();
			
			return	array(
						'bSuccess'	=> true,
						'oAccount'	=> $aAccount,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $oException)
		{
			return	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bIsGod ? $oException->getMessage() : 'An error occured accessing the database'),
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
	}

	// searchCustomerGroup: Search for accounts within the given customer group (within filter data) using 
	//						the given search term (within filter data) which can be used to search the 
	//						fields: Id, BusinessName & TradingName
	public function searchCustomerGroup($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			//
			// NOTE: 	This is designed to be used by a Control_Field_Text_AJAX object on the client side
			//			As a result, the count only, offset and sorting data are ignored.
			//
			
			// Extract filter data
			$sSearchTerm		= $oFilter->search_term;
			$iCustomerGroupId	= $oFilter->customer_group;
			
			// Split the search term by spaces then use each part as a search term
			$sWhere	= '';	
			$aTerms	= split(' ', $sSearchTerm);
			foreach ($aTerms as $sTerm)
			{
				if ($sTerm == '')
				{
					continue;
				}
				
				// Create constraint
				// By default every term is checked against BusinessName & TradingName
				$sConstraint	= "(BusinessName LIKE '%{$sTerm}%') OR (TradingName LIKE '%{$sTerm}%')";
				if (is_numeric($sTerm))
				{
					// The term is numeric, so Id is checked also
					$sConstraint	.= " OR (Id LIKE '%{$sTerm}%')";
				}
				
				// Add constraint to where clause
				$sWhere	.= ($sWhere == '') ? "({$sConstraint})" : " AND ({$sConstraint})";
			}
			
			// Perform the search query
			$sQuery		= "	SELECT	*
							FROM	Account
							WHERE	CustomerGroup = {$iCustomerGroupId}
							".($sWhere !== '' ? "AND {$sWhere}" : '')."
							ORDER BY Id
							LIMIT {$iLimit}";
			Log::getLog()->log($sQuery);
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				throw new Exception_Database("Failed account customer group search. ".$oQuery->Error());
			}
			
			// Create array of results
			$aResults	= array();
			while($aRow = $mResult->fetch_assoc())
			{
				$aResults[]	= $aRow;
			}
			
			return	array(
						'bSuccess'		=> true,
						'Success'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> count($aResults),
						'sDebug'		=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $oException)
		{
			$sMessage	= ($bIsGod ? $oException->getMessage() : 'An error occured accessing the database');
			return	array(
						'bSuccess'	=> false,
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
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
				throw new Exception_Database($qryQuery->Error());
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
			$bhasCreditControlPermission	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);

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
			else
			{
				$aResult = array(1,2,3,4,5);
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
			$bhasCreditControlPermission	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);

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
				// Store the direct debits id in aHasPaymentMethods
				if (!isset($aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_BANK_ACCOUNT]))
				{
					$aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_BANK_ACCOUNT]	 = array();
				}

				$aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_BANK_ACCOUNT][]	= $oDirectDebit->Id;

				// Check if it is the current method
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
				// Store the credit cards id in aHasPaymentMethods
				if (!isset($aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_CREDIT_CARD]))
				{
					$aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_CREDIT_CARD]	 = array();
				}

				$aHasPaymentMethod[PAYMENT_METHOD_DIRECT_DEBIT][DIRECT_DEBIT_TYPE_CREDIT_CARD][]	= $oCreditCard->Id;

				// Check if it is the current method
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
			$oRebill	= $this->_getRebill($iAccountId);

			if ($oRebill)
			{
				$aHasPaymentMethod[PAYMENT_METHOD_REBILL][$oRebill->rebill_type_id]	 = true;

				if ($oAccount->BillingType == BILLING_TYPE_REBILL)
				{
					$oRebillDetails					= $oRebill->oDetails;
					$oPaymentMethod					= $oRebill;
					$oPaymentMethod->Id				= $oPaymentMethod->id;
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
		$bGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			self::_setPaymentMethod($iAccountId, $iPaymentMethodType, $iPaymentMethodSubType, $iBillingDetail);
			
			return array('Success' => true);
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			$oDataAccess->TransactionRollback();

			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> ($bGod ? $this->_JSONDebug : '')
					);
		}
		catch (Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			return 	array(
						"Success"	=> false,
						"Message"	=> ($bGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.'),
						"strDebug"	=> ($bGod ? $this->_JSONDebug : '')
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
		$bGod	= Employee::getForId(Flex::getUserId())->isGod();
		
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
				// Create orm object
				$oCreditCard	= new Credit_Card();
	
				// Set the account group
				$oAccountGroup				= Account_Group::getForAccountId($iAccountId);
				$oCreditCard->AccountGroup	= $oAccountGroup->Id;
	
				// Default values, that aren't supplied by interface
				$oCreditCard->Archived		= 0;
				$oCreditCard->employee_id	= Flex::getUserId();
				
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

				// Modify the payment method for the account
				if ($oDetails->bSetPaymentMethod)
				{
					try
					{
						self::_setPaymentMethod($iAccountId, PAYMENT_METHOD_DIRECT_DEBIT, DIRECT_DEBIT_TYPE_CREDIT_CARD, $oCreditCard->Id);
					}
					catch (JSON_Handler_Account_Exception $oException)
					{
						// Add more detail to the exception message
						throw new JSON_Handler_Account_Exception("Failed to modify the payment method for the Account. ".$oException->getMessage());
					}
				}
				
				// Make credit card payment if necessary 
				$oTransactionDetails	= null;
				if ($oDetails->bSubmitPayment)
				{
					// Payment to be made
					$oAccount	= Account::getForId($iAccountId);
					$fAmount	= null;
					if ($oDetails->bPaymentAmountBalance)
					{
						$fAmount	= $oAccount->getBalance();
					}
					else if ($oDetails->bPaymentAmountOverdueBalance)
					{
						$fAmount	= $oAccount->getOverdueBalance();
					}
					else if ($oDetails->bPaymentAmountOther)
					{
						$fAmount	= (float)$oDetails->sPaymentAmount;
					}
					
					if ($fAmount === null)
					{
						throw new Exception("Invalid amount supplied");
					}
					
					// Make the credit card transaction
					$oContact	= Contact::getForId($oAccount->PrimaryContact);
					if (!$oContact)
					{
						throw new Exception("Failed to load primary contact details for the account.");
					}
					
					$oTransactionDetails	=	Credit_Card_Payment::makeCreditCardPayment(
													$iAccountId, 
													$oContact->Id, 
													Flex::getUserId(), 
													$oDetails->iCardType, 
													$oDetails->iCardNumber,
													$oDetails->iCVV, 
													$oDetails->iExpiryMonth, 
													$oDetails->iExpiryYear, 
													$oDetails->sCardHolderName, 
													$fAmount, 
													$oContact->Email, 
													false	// Use details for direct debit
												);
				}

				// Get the card type name
				$oStdClassCreditCard					= $oCreditCard->toStdClass();
				$oStdClassCreditCard->card_type_name	= Constant_Group::getConstantGroup('credit_card_type')->getConstantName($oCreditCard->CardType);

				// Mask the card number and cvv
				$bhasCreditControlPermission	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
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
							"Success"				=> true,
							"oCreditCard"			=> $oStdClassCreditCard,
							'oTransactionDetails'	=> $oTransactionDetails,
							"strDebug"				=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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
		catch (Credit_Card_Payment_Communication_Response_Exception $oException)
		{
			// Credit card payment error
			$sMessage	= 'We were unable to read the response from SecurePay so we do not know whether the payment succeeded or failed. Please do not retry payment at this time.';
			if (Credit_Card_Payment::isTestMode())
			{
				$sMessage	.= ' '.$oException->getMessage();
			}
			
			self::_sendCreditCardPaymentErrorEmail(
				$iAccountId, 
				$oContact->Email, 
				$oDetails->iCardNumber, 
				$oDetails->sCardHolderName, 
				$fAmount, 
				$sMessage
			);
			
			return 	array(
						"Success"		=> false,
						"Message"		=> $sMessage,
						'bPaymentError'	=> true,
						"strDebug"		=> ($bGod ? $this->_JSONDebug : '')
					);
		}
		catch (Credit_Card_Payment_Communication_Exception $oException)
		{
			// Credit card payment error
			$sMessage	= 'We were unable to connect to SecurePay to process the payment.';
			if (Credit_Card_Payment::isTestMode())
			{
				$sMessage	.= ' '.$oException->getMessage();
			}
			
			self::_sendCreditCardPaymentErrorEmail(
				$iAccountId, 
				$oContact->Email, 
				$oDetails->iCardNumber, 
				$oDetails->sCardHolderName, 
				$fAmount, 
				$sMessage
			);
			
			return 	array(
						"Success"		=> false,
						"Message"		=> $sMessage,
						'bPaymentError'	=> true,
						"strDebug"		=> ($bGod ? $this->_JSONDebug : '')
					);
		}
		catch (Credit_Card_Payment_Remote_Processing_Error $oException)
		{
			// Credit card payment error
			$sMessage	= 'SecurePay was unable to process the payment request.';
			if (Credit_Card_Payment::isTestMode())
			{
				$sMessage	.= ' '.$oException->getMessage();
			}
			
			return 	array(
						"Success"		=> false,
						"Message"		=> $sMessage,
						'bPaymentError'	=> true,
						"strDebug"		=> ($bGod ? $this->_JSONDebug : '')
					);
		}
		catch (Credit_Card_Payment_Validation_Exception $oException)
		{
			// Credit card payment error
			$sMessage	= $oException->getMessage();
			if (Credit_Card_Payment::isTestMode())
			{
				$sMessage	.= ' '.$oException->getMessage();
			}
			
			return 	array(
						"Success"		=> false,
						"Message"		=> $sMessage,
						'bPaymentError'	=> true,
						"strDebug"		=> ($bGod ? $this->_JSONDebug : '')
					);
		}
		catch (Exception_Assertion $oException)
		{
			// Assertions should be handled at a much higher level than this
			throw $oException;
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

			$aPaymentReceipt	= null;

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
				// Create orm object
				$oDirectDebit	= new DirectDebit();
	
				// Set the account group
				$oAccountGroup				= Account_Group::getForAccountId($iAccountId);
				$oDirectDebit->AccountGroup	= $oAccountGroup->Id;
	
				// Default values, that aren't supplied by interface
				$oDirectDebit->Archived		= 0;
				$oDirectDebit->employee_id	= Flex::getUserId();
				
				// Update object & save
				$oDirectDebit->BankName			= $oDetails->sBankName;
				$oDirectDebit->BSB				= $oDetails->sBSB;
				$oDirectDebit->AccountNumber	= $oDetails->sAccountNumber;
				$oDirectDebit->AccountName		= $oDetails->sAccountName;
				$oDirectDebit->created_on		= date('Y-m-d H:i:s');
				$oDirectDebit->save();
				
				// Modify the payment method for the account
				if ($oDetails->bSetPaymentMethod)
				{
					try
					{
						self::_setPaymentMethod($iAccountId, PAYMENT_METHOD_DIRECT_DEBIT, DIRECT_DEBIT_TYPE_BANK_ACCOUNT, $oDirectDebit->Id);
					}
					catch (JSON_Handler_Account_Exception $oException)
					{
						// Add more detail to the exception message
						throw new JSON_Handler_Account_Exception("Failed to modify the payment method for the Account. ".$oException->getMessage());
					}
				}
				
				if ($oDetails->bSubmitPayment)
				{
					// Payment to be submitted
					// Determine the amount
					$fAmount	= null;
					if ($oDetails->bPaymentAmountBalance)
					{
						$fAmount	= Account::getForId($iAccountId)->getBalance();
					}
					else if ($oDetails->bPaymentAmountOverdueBalance)
					{
						$fAmount	= Account::getForId($iAccountId)->getOverdueBalance();
					}
					else if ($oDetails->bPaymentAmountOther)
					{
						$fAmount	= (float)$oDetails->sPaymentAmount;
					}
					
					if ($fAmount === null)
					{
						throw new Exception("Invalid amount supplied");
					}
					
					$iEmployeeId	= Flex::getUserId();
					
					// Create payment
					$oPayment =	Logic_Payment::factory(
									$iAccountId, 
									PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT, 
									$fAmount, 
									$iPaymentNature=PAYMENT_NATURE_PAYMENT, 
									'',
									date('Y-m-d'), 
									array(Payment_Transaction_Data::BANK_ACCOUNT_NUMBER => $oDirectDebit->AccountNumber)
								);
					
					// Create payment_request
					$oPaymentRequest	= 	Payment_Request::generatePending(
												$iAccountId, 						// Account id
												PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT, 	// Payment type
												round($fAmount, 2), 				// Amount
												null, 								// Invoice run id
												$iEmployeeId,						// Employee id
												$oPayment->id						// Payment id
											);
					
					// Update the payments transaction reference
					$oPayment->transaction_reference = Payment_Request::generateTransactionReference($oPaymentRequest);
					$oPayment->save();
											
					$aPaymentReceipt	= 	array(
												'sTransactionId'	=> $oPayment->transaction_reference,
												'iAccount'			=> $iAccountId,
												'sPaidOn'			=> $oPaymentRequest->created_datetime,
												'fAmount'			=> $oPaymentRequest->amount
											);
					
					// Create 'EFT One Time Payment' action
					$sAmount	= number_format($fAmount, 2);
					Action::createAction(
						'EFT One Time Payment', 
						"Amount: \${$sAmount}\n Receipt Number: {$oPayment->transaction_reference}", 
						$iAccountId, 
						NULL, 
						NULL, 
						$iEmployeeId, 
						Employee::SYSTEM_EMPLOYEE_ID
					);
				}
				
				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();
				
				// All good
				return 	array(
							"Success"			=> true,
							"oDirectDebit"		=> $oDirectDebit->toStdClass(),
							'oPaymentReceipt'	=> $aPaymentReceipt,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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

	public function getContactEmailAddresses($iAccountId)
	{
		$bGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAccount	= Account::getForId($iAccountId);
			$aContacts	= $oAccount->getContacts(true);
			$aResults	= array();
			foreach ($aContacts as $oContact)
			{
				$aResults[$oContact->Id]	=	array(
													'sName'			=> $oContact->getName(), 
													'sEmail' 		=> $oContact->Email,
													'bIsPrimary'	=> ($oContact->Id == $oAccount->PrimaryContact)
												);
			}

			return  array(
						'bSuccess'	=> true,
						'aContacts'	=> $aResults
					);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $bGod ? $e->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.'
					);
		}
	}

	public function getRebill($iAccountId )
	{
		try
		{
			$oRebill	= $this->_getRebill($iAccountId);
			return  array(
						"Success"	=> true,
						"oRebill"	=> $oRebill
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

	function starts_with($string, $search)
	{
	    return (strncmp($string, $search, strlen($search)) == 0);
	}

	public function addRebill($iAccountId, $iRebillTypeId , $oDetails)
	{
		// for debug purposes only
		//$oDetails = json_decode('{"account_id":32,"account_account_number":"1000174123","account_account_name":"sfsafdadf","account_business_commencement_date":"2010-08-01","account_motorpass_business_structure_id":"4","account_business_structure_description":"","account_motorpass_promotion_code_id":"1","card_motorpass_card_type_id":"2","card_card_type_description":"","card_card_expiry_date":"2011-2","card_shared":true,"card_holder_contact_title_id":null,"card_holder_first_name":"","card_holder_last_name":"","card_vehicle_model":"","card_vehicle_rego":"","card_vehicle_make":"","street_address_line_1":"asdfasdf","street_address_line_2":"","street_address_suburb":"asdfdsa","street_address_state_id":"2","street_address_postcode":"3333","postal_address_line_1":"","postal_address_line_2":"","postal_address_suburb":"","postal_address_state_id":null,"postal_address_postcode":"","contact_contact_title_id":null,"contact_first_name":"sdafdsfa","contact_last_name":"adsfsdaf","contact_dob":"2010-08-01","contact_drivers_license":"","contact_position":"asdffsda","contact_landline_number":"0765555555","reference1_company_name":"sdfgf","reference1_contact_person":"sdfgds","reference1_phone_number":"0765555555","reference2_company_name":"sghgh","reference2_contact_person":"hdh","reference2_phone_number":"0756555555"}');
		
		$oDataAccess	= DataAccess::getDataAccess();
		$bIsGod			= Employee::getForId(Flex::getUserId())->isGod();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> ($bIsGod ? 'Could not start database transaction.' : 'Database Error, please ask YBS for assistance.')
					);
		}

		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to add a rebill');
			}

			$aErrors	= array();

			// Set the extra rebill type specific information
			switch ($iRebillTypeId)
			{
				case REBILL_TYPE_MOTORPASS:
					// Validate card expiry date & convert it to the proper format so it can be compared to the
					// existing card_expiry_date field (if need be)
					$iTime						= strtotime($oDetails->card_expiry_date);
					$sLastDayInMonth			= date('t', $iTime);
					$iExpiryDate				= strtotime($oDetails->card_expiry_date.'-'.$sLastDayInMonth);
					$oDetails->card_expiry_date = date('Y-m-d', $iExpiryDate);

					//
					// NOTE : 	This has been commented out so that the old way of editing rebill payment methods could
					//			work temporarily. See below this commented section for the replacement code. -- rmctainsh
					//
					//create the logic object
					/*$oMotorpassAccount = new Motorpass_Logic_Account(Motorpass_Logic_Account::makeNestedObject($oDetails));
					$mResult = $oMotorpassAccount->save();

					if (is_array($mResult))
					{
						// Validation errors found, rollback transaction and return the errors
						$oDataAccess->TransactionRollback();

						return 	array(
									"Success"			=> false,
									"aValidationErrors"	=> $aErrors
								);
					}*/
					
					//
					// NOTE : 	This has been added so that the old way of editing rebill payment methods could
					//			work temporarily. It is to be removed and replaced with the commented section above
					//			once the SPMP site is up and there is a need to edit/create rebill motorpass data
					// 			from the Flex interface. -- rmctainsh
					//
					// Create a new rebill & rebill_motorpass
					$oRebill						= new Rebill();
					$oRebill->account_id			= $iAccountId;
					$oRebill->rebill_type_id		= $iRebillTypeId;
					$oRebill->created_employee_id	= Flex::getUserId();
					$oRebill->created_timestamp		= date('Y-m-d H:i:s');
					$oRebill->save();
					
					$oRebillMotorpass					= new Rebill_Motorpass();
					$oRebillMotorpass->rebill_id		= $oRebill->id;
					$oRebillMotorpass->account_number	= $oDetails->account_number;
					$oRebillMotorpass->account_name		= $oDetails->account_name;
					$oRebillMotorpass->card_expiry_date	= $oDetails->card_expiry_date;
					
					$oRebillMotorpass->save();
					
					// Return the created rebill
					$oCurrent						= Rebill::getForAccountId($iAccountId);
					$oCurrentStdClass				= $oCurrent->toStdClass();
					$oCurrentStdClass->oDetails 	= $oCurrent->getDetails()->toStdClass();
					
					$oDataAccess->TransactionCommit();
					
					return 	array(
								"Success"	=> true,
								"oRebill"	=> $oCurrentStdClass
							);
			}

		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			// Exception thrown & caught, rollback db transaction
			$oDataAccess->TransactionRollback();

			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			// Exception caught, rollback db transaction
			$oDataAccess->TransactionRollback();

			return 	array(
						"Success"	=> false,
						"Message"	=> ($bIsGod ? $e->getMessage() : 'There was an error accessing the database')
					);
		}
	}
	
	public function getDeliveredCorrespondence($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Require proper admin priviledges when the account has not been limited (i.e. is from a system wide search)
			$bUserIsProperAdmin	= AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN));
			if (!isset($oFilter->account_id) && !$bUserIsProperAdmin)
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to view Correspdondence.');
			}
			
			$aFilter		= get_object_vars($oFilter);
			$aSort			= get_object_vars($oSort);
			$iRecordCount	= Correspondence::getLedgerInformation(true, null, null, $aFilter, $aSort, true);
			if ($bCountOnly)
			{
				return	array(
							'Success'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= is_null($iLimit) ? 0 : $iLimit;
			$iOffset	= is_null($iOffset) ? 0 : $iOffset;
			$aItems		= Correspondence::getLedgerInformation(false, $iLimit, $iOffset, $aFilter, $aSort, true);
			$i			= 0;
			$aResults	= array();
			foreach ($aItems as $aItem)
			{
				$aItem['bViewRun']			= $bUserIsProperAdmin;
				$aResults[$iOffset + $i]	= $aItem;
				$i++;
			}
			
			return	array(
						'Success'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (JSON_Handler_Account_Exception $oException)
		{
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getPaymentInfo($iAccountId)
	{
		$bGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAccount		= Account::getForId($iAccountId);
			$oContact		= Contact::getForId($oAccount->PrimaryContact);
			$aPaymentMethod	= $this->getCurrentPaymentMethod($iAccountId);
			$oPaymentMethod	= $aPaymentMethod['oPaymentMethod'];
			
			$aInfo	= 	array(
							'sABN'					=> $oAccount->ABN,
							'sBusinessName'			=> $oAccount->BusinessName,
							'sContactName'			=> $oContact->getName(),
							'sContactEmail'			=> $oContact->Email,
							'fBalance'				=> $oAccount->getBalance(),
							'fOverdueBalance'		=> $oAccount->getOverdueBalance(),
							'iPaymentMethod'		=> $aPaymentMethod['iPaymentMethod'],
							'iPaymentMethodSubType'	=> $aPaymentMethod['iPaymentMethodSubType'],
							'oPaymentMethod'		=> $oPaymentMethod
						);
				
			return	array(
						'bSuccess'	=> true,
						'aInfo'		=> $aInfo
					);
		}
		catch (Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $bGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function sendDirectDebitReceiptEmail($oPaymentReceipt, $aEmails)
	{
		$bGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAccount		= Account::getForId($oPaymentReceipt->iAccount);
			$oCustomerGroup	= Customer_Group::getForId($oAccount->CustomerGroup);
			$sPaidOn		= date('d/m/Y', strtotime($oPaymentReceipt->sPaidOn));
			$sAmount		= number_format($oPaymentReceipt->fAmount, 2);
			
			$oEmail	= new Email_Flex();
			$oEmail->setSubject("Direct Debit Payment Receipt for {$oCustomerGroup->external_name} Account #{$oPaymentReceipt->iAccount}");
			$oEmail->setBodyText(
				"Dear Customer,\n\n".
				"A payment has been lodged for the {$oCustomerGroup->external_name} Account #{$oPaymentReceipt->iAccount} with the following details:\n\n".
				"\tReciept #:\t{$oPaymentReceipt->sTransactionId}\n".
				"\tAmount:\t\${$sAmount}\n".
				"\tPaid On:\t{$sPaidOn}\n\n".
				"Regards,\n".
				"The {$oCustomerGroup->external_name} Team"
			);
			$oEmail->setFrom("contact@{$oCustomerGroup->email_domain}");
			foreach ($aEmails as $sEmail)
			{
				$sEmail	= 'ybs-admin@ybs.net.au';	// TODO: CR135 -- remove this so that actual recipients receive emails
				$oEmail->addTo($sEmail);
			}
			$oEmail->send();
			
			return	array(
						'bSuccess'		=> true,
						'sRecipients'	=> implode(', ', $oEmail->getRecipients())
					);
		}
		catch (Exception $oException)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> $bGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function doesPrimaryContactHaveEmail($iAccountId)
	{
		try
		{
			$oAccount	= Account::getForId($iAccountId);
			$oContact	= Contact::getForId($oAccount->PrimaryContact);
			return array('bSuccess' => EmailAddressValid($oContact->Email));
		}
		catch (Exception $oException)
		{
			$bGod	= Employee::getForId(Flex::getUserId())->isGod();
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> $bGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function changeCollectionScenario($iAccountId, $iScenarioId, $bEndCurrentScenario=true)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAccount = Logic_Account::getInstance($iAccountId);
			$oAccount->setCurrentScenario($iScenarioId, $bEndCurrentScenario);
			return array('bSuccess' => true);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage,
						'sDebug'	=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
		}
	}
	
	public function hasAccountGotOCAReferral($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess' 					=> true, 
						'bAccountHasOCAReferral' 	=> Account_OCA_Referral::accountExists($iAccountId)
					);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'),
						'sDebug'	=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
		}
	}
	
	public function getAllowedInterimInvoiceType($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Get the allowed next interim invoice (run) type
			$oAccount 			= Account::getForId($iAccountId);
			$iInvoiceRunTypeId 	= $oAccount->getInterimInvoiceType();
			
			// Determine if the appropriate flex module is active
			$bInterimAllowed	= false;
			switch ($iInvoiceRunTypeId)
			{
				case INVOICE_RUN_TYPE_FINAL:
					$bInterimAllowed = Flex_Module::isActive(FLEX_MODULE_INVOICE_FINAL);
					break;
				case INVOICE_RUN_TYPE_INTERIM:
				case INVOICE_RUN_TYPE_INTERIM_FIRST:
					$bInterimAllowed = Flex_Module::isActive(FLEX_MODULE_INVOICE_INTERIM);
					break;
			}
			
			if (!$bInterimAllowed)
			{
				// Module is inactive, clear the result
				$iInvoiceRunTypeId = null;
			}
			
			return	array(
						'bSuccess'	 				=> true, 
						'iInterimInvoiceRunType'	=> $iInvoiceRunTypeId
					);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'),
						'sDebug'	=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
		}
	}
	
	private function _getRebill($iAccountId)
	{
		$oRebill	= Rebill::getForAccountId($iAccountId);
		$mResult	= null;
		if ($oRebill)
		{
			$mResult 			= $oRebill->toStdClass();
			$mResult->oDetails	= $oRebill->getDetails()->toStdClass();
		}
		return $mResult;
	}
	
	private static function _sendCreditCardPaymentErrorEmail($iAccountId, $sEmail, $sCardNumber, $sName, $fAmount, $sMessage)
	{
		$aCustomerDetails = array(
								"AccountId"			=> $iAccountId,
								"Email"				=> $sEmail,
								"CreditCardNumber"	=> substr($sCardNumber, 0, 3) ."***". substr($sCardNumber, -5),
								"Name"				=> $sName,
								"Amount"			=> $fAmount
							);
		$sCustomerDetails	= print_r($aCustomerDetails, TRUE);
		$sMessageSentToUser	= $sMessage;
		$sExceptionMessage	= $oException->getMessage();
		
		$sDetails	= "SecurePay Credit Card transaction failed via the Flex Customer Management System";
		$sDetails 	.= "Exception Message:\n";
		$sDetails 	.= "\t$sExceptionMessage\n\n";
		$sDetails 	.= "Message sent to User:\n";
		$sDetails 	.= "\t$sMessageSentToUser\n\n";
		$sDetails 	.= "CustomerDetails:\n";
		$sDetails 	.= "\t$sCustomerDetails\n\n";
		
		Flex::sendEmailNotificationAlert(
			(Credit_Card_Payment::isTestMode() ? '[TEST MODE] ' : '')."SecurePay Transaction Failure", 
			$sDetails, 
			FALSE, 
			TRUE, 
			TRUE
		);
	}
	
	private static function _setPaymentMethod($iAccountId, $iPaymentMethodType, $iPaymentMethodSubType, $iBillingDetail)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			throw new Exception('Failed to start transaction');
		}

		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to set the payment method');
			}

			// Update billing type
			$oAccount	= Account::getForId($iAccountId);

			/**
			 * ----------------------------------------------------------------------------------------
			 * VERY IMPORTANT!!!!
			 * ----------------------------------------------------------------------------------------
			 * IF YOU CHANGE THE CONTENTS OF THE NOTE THAT IS ADDED DURING THIS PROCESS, MAKE SURE THAT
			 * YOU UPDATE THE Motorpass Sales Portal IMPLEMENTATION OF IT AS WELL.
			 * 
			 * THE CODE TO DO SO IS LOCATED IN Cli_App_FlexSync.php WITHIN THE SPMP APPLICATION.			 * 
			 * ----------------------------------------------------------------------------------------
			 */
			 
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
								$sAccountNumber		= ($oOldRebillDetails->account_number 	? $oOldRebillDetails->account_number 	: 'Not supplied');
								$sAccountName		= ($oOldRebillDetails->account_name 	? $oOldRebillDetails->account_name 		: 'Not supplied');
								$sCCExpiry			= ($oOldRebillDetails->card_expiry_date	? $oOldRebillDetails->card_expiry_date 	: 'Not supplied');
								$sOldBillingType	= 	"Rebill via Motorpass\n" .
														"Account Number: {$sAccountNumber}\n" .
														"Account Name: {$sAccountName}\n" .
														"Card Expiry: {$sCCExpiry}";
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
													"Account Number: {$oRebillTypeDetails->account_number}\n" .
													"Account Name: {$oRebillTypeDetails->account_name}\n" .
													"Card Expiry: {$oRebillTypeDetails->card_expiry_date}";
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
		}
		catch (Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			throw $oException;
		}
	}
}

class JSON_Handler_Account_Exception extends Exception
{
	// No changes
}

?>