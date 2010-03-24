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

			$aResult		= array();
			
			/*
			 *
			 * TODO: 
			 * Need to retrieve CreditCards and DirectDebits
			 * Merge into single array and return back to javascript
			 * 
			 */
			 
			$oAccountGroup  	= Account_Group::getForAccountId($iAccountId);
			if(!$oAccountGroup)
			{
				throw new Exception('Invalid Account Id');
			}
			
			
			$qryQuery		= new Query();
			$resCreditCards	= $qryQuery->Execute("
			SELECT *
			FROM CreditCard
			WHERE AccountGroup={$oAccountGroup->id} AND Archived = 0;");
			
			while ($arrCreditCard = $resCreditCards->fetch_assoc())
			{
				$aResult[]	= $arrCreditCard;

				/*
				$aResult['credit_cards'][][]	= "";
				*/
			}
			
			return array(
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : '',
							"arrPaymentMethods"	=> $aResult,
						);
		}
		catch (Exception $e)
		{
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
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
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : '',
							"aCostCentres"		=> $aStdObjects
						);
		}
		catch (Exception $e)
		{
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? 'ERROR: '.$e->getMessage() : false,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
}
?>