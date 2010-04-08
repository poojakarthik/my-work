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
	
	public function getPaymentMethods($iAccountId)
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
					$sCardNumber	= 	substr($sCardNumber, 0, 4).
										preg_replace('/\d/', 'X', substr($sCardNumber, 4, strlen($sCardNumber) - 8)).
										substr($sCardNumber, strlen($sCardNumber) - 4, 4);
					$sCVV	= ($sCVV == '' ? 'Not Supplied' : 'Supplied');
				}
				
				$oStdClassCreditCard->card_number	= $sCardNumber;
				$oStdClassCreditCard->cvv			= $sCVV;
				$aResult['credit_cards'][]			= $oStdClassCreditCard;
			}
			
			// Get all DirectDebit for the accountgroup
			$aDirectDebits	= DirectDebit::getForAccountGroup($oAccountGroup->Id);
			
			foreach ($aDirectDebits as $oDirectDebit)
			{
				$aResult['direct_debits'][]	= $oDirectDebit->toStdClass();
			}
			
			return 	array(
						"Success"					=> true,
						"strDebug"					=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : '',
						"aPaymentMethods"			=> $aResult,
						"iSelectedPaymentMethod"	=> $oAccount->BillingType,
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
	
	public function setPaymentMethod($iAccountId, $iBillingType, $mDetail)
	{
		// TODO: Update the account record, setting billing type and creditcard, directdebit depending on billing type
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