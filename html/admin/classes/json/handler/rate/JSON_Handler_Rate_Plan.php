<?php

class JSON_Handler_Rate_Plan extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function generateEmailButtonOnClick($intCustomerGroup, $arrRatePlanIds)
	{
		try
		{
			$strEval	= Rate_Plan::generateEmailButtonOnClick($intCustomerGroup, $arrRatePlanIds);
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"strEval"		=> $strEval,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function renderAuthScript($intServiceId, $intNewPlanId, $bolStartNextMonth, $intContactId)
	{
		try
		{
			// Load the Plan, Service, Account, and Contact
			$objNewPlan			= new Rate_Plan(array('Id'=>(int)$intNewPlanId), true);
			$objService			= new Service(array('Id'=>(int)$intServiceId), true);
			$objAccount			= new Account(array('Id'=>$objService->Account), false, true);
			$objContact			= Contact::getForId($intContactId);
			$objServiceRatePlan	= $objService->getCurrentServiceRatePlan();
			
			// Are we allowed to change plans?
			$objOldPlan			= new Rate_Plan(array('id'=>$objServiceRatePlan->RatePlan), true);
			if ($objOldPlan->locked && !AuthenticatedUser()->UserHasPerm(PERMISSION_RATE_MANAGEMENT))
			{
				// Not permitted -- use the Rejection Script
				$strHTML		= $objNewPlan->parseRejectionScript($objAccount, $objContact, $objServiceRatePlan);
				$bolPermitted	= false;
			}
			else
			{
				// Permitted -- use the Authorisation Script
				$strHTML		= $objNewPlan->parseAuthenticationScript($objAccount, $objContact, $objServiceRatePlan);
				$bolPermitted	= true;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"bolPermitted"	=> $bolPermitted,
							"strHTML"		=> $strHTML,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			$this->_JSONDebug	.= $e->__toString();
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
}
?>