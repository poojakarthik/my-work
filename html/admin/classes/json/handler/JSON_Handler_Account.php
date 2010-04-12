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
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function getPaymentMethods($iAccountId, $iBillingType)
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
			if ($iBillingType == 1)
			{
				// Get all DirectDebit for the accountgroup
				$aDirectDebits	= DirectDebit::getForAccountGroup($oAccountGroup->Id);
				
				foreach ($aDirectDebits as $oDirectDebit)
				{
					$aResult[]	= $oDirectDebit->toStdClass();
				}
			}
			else if ($iBillingType == 2)
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
			
			$oPaymentMethod	= false;
			
			// Get all DirectDebit for the accountgroup to see if there is any
			$aDirectDebits	= DirectDebit::getForAccountGroup($oAccountGroup->Id);
			
			foreach ($aDirectDebits as $oDirectDebit)
			{
				$bHasBankAccount	= true;
				
				if ($oAccount->BillingType == 1)
				{
					if ($oAccount->DirectDebit == $oDirectDebit->Id)
					{
						$oPaymentMethod	= $oDirectDebit->toStdClass();
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
				$bHasCreditCard	= true;
								
				if ($oAccount->BillingType == 2)
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
					}
				}
				else
				{
					break;
				}
			}
			
			return 	array(
						"Success"					=> true,
						"strDebug"					=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : '',
						"iBillingType"				=> $oAccount->BillingType,
						"oPaymentMethod"			=> $oPaymentMethod,
						"bHasCreditCard" 			=> $bHasCreditCard,
						"bHasBankAccount" 			=> $bHasBankAccount
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
	
	public function setPaymentMethod($iAccountId, $iBillingType, $iBillingDetail)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_Account_Exception('You do not have permission to set the payment method');
			}
			
			// Update billing type
			$oAccount				= Account::getForId($iAccountId);
			$oAccount->BillingType	= $iBillingType;
			
			// Reset detail values first
			$oAccount->DirectDebit	= null;
			$oAccount->CreditCard	= null;
			
			// Update proper detail field
			switch ($iBillingType)
			{
				case 1:	// DirectDebit
					$oAccount->DirectDebit	= $iBillingDetail;
					break;
				case 2:	// CreditCard
					$oAccount->CreditCard	= $iBillingDetail;
					break;
				case 3:	// Invoice
					// Nothing
					break;
			}
			
			$oAccount->save();
			
			// All good
			return 	array(
						"Success"	=> true,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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
				return array(
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
}

class JSON_Handler_Account_Exception extends Exception
{
	// No changes
}

?>