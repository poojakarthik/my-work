<?php
class JSON_Handler_Rate_Plan extends JSON_Handler implements JSON_Handler_Loggable {
	public function generateEmailButtonOnClick($intCustomerGroup, $arrRatePlanIds) {
		try {
			$strEval = Rate_Plan::generateEmailButtonOnClick($intCustomerGroup, $arrRatePlanIds);

			// If no exceptions were thrown, then everything worked
			return array(
				"Success" => true,
				"strEval" => $strEval,
				"strDebug" => (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_sJSONDebug : ''
			);
		} catch (Exception $e) {
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			return array(
				"Success" => false,
				"Message" => 'ERROR: '.$e->getMessage(),
				"strDebug" => (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_sJSONDebug : ''
			);
		}
	}

	public function renderAuthScript($intServiceId, $intNewPlanId, $bolStartNextMonth, $intContactId) {
		try {
			// Load the Plan, Service, Account, and Contact
			$objNewPlan = new Rate_Plan(array('Id'=>(int)$intNewPlanId), true);
			$objService = new Service(array('Id'=>(int)$intServiceId), true);
			$objAccount = new Account(array('Id'=>$objService->Account), false, true);
			$objContact = Contact::getForId($intContactId);
			$objServiceRatePlan = $objService->getCurrentServiceRatePlan();

			// Are we allowed to change plans?
			$objOldPlan = ($objServiceRatePlan !== null)? new Rate_Plan(array('id'=>$objServiceRatePlan->RatePlan), true) : null;
			if (($objOldPlan !== null) && $objOldPlan->locked && !AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN)) {
				// Not permitted -- use the Rejection Script
				$strHTML = $objNewPlan->parseRejectionScript($objAccount, $objContact, $objService, $objServiceRatePlan, $objOldPlan);
				$bolPermitted = false;
			} else {
				// Permitted -- use the Authorisation Script
				$strHTML = $objNewPlan->parseAuthenticationScript($objAccount, $objContact, $objService, $objServiceRatePlan, $objOldPlan);
				$bolPermitted = true;
			}

			// If no exceptions were thrown, then everything worked
			return array(
				"Success" => true,
				"bolPermitted" => $bolPermitted,
				"strHTML" => $strHTML,
				"strDebug" => (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_sJSONDebug : ''
			);
		} catch (Exception $e) {
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			return array(
				"Success" => false,
				"Message" => 'ERROR: '.$e->getMessage(),
				"strDebug" => (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug . $e->__toString() : ''
			);
		}
	}

	public function getForAccount($iAccountId, $bReturnArchived=false) {
		return $this->getForCustomerGroup(Account::getForId($iAccountId)->CustomerGroup, $bReturnArchived);
	}

	public function getForCustomerGroup($iCustomerGroup, $bReturnArchived=false) {
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();

		try {
			// Retrieve all RatePlans within the given customer group
			$sWhere	= "customer_group = <CustomerGroup>";
			$aWhere	= array("CustomerGroup"	=> $iCustomerGroup);

			if (!$bReturnArchived) {
				// Limit the status to ACTIVE
				$sWhere .= " AND Archived = <RatePlanActive>";
				$aWhere["RatePlanActive"]	= RATE_STATUS_ACTIVE;
			}

			$oStmt = new StatementSelect("RatePlan", "Id, ServiceType, Name", $sWhere, "Name ASC");
			$mResult = $oStmt->Execute($aWhere);
			$aRatePlans = array();
			if ($mResult) {
				$aRecordSet	= $oStmt->FetchAll();
				foreach ($aRecordSet as $aRecord) {
					$aRatePlans[$aRecord['ServiceType']][$aRecord['Id']] = $aRecord;
				}
			}

			return	array(
				'bSuccess' => true,
				'aRatePlans' => $aRatePlans,
				'aRecordSet' => $aRecordSet,
				'sDebug' => ($bIsGod ? $this->_sJSONDebug : false)
			);
		} catch (Exception $oEx) {
			return array(
				'bSuccess' => false,
				'sMessage' => ($bIsGod ? $oEx->getMessage() : ''),
				'sDebug' => ($bIsGod ? $this->_sJSONDebug : false)
			);
		}
	}

	public function getForId($iId) {
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			return array(
				'bSuccess' => true,
				'oRatePlan' => Rate_Plan::getForId($iId)->toStdClass(),
				'sDebug' => ($bIsGod ? $this->_JSONDebug : false)
			);
		} catch (Exception $oEx) {
			return array(
				'bSuccess' => false,
				'sMessage' => ($bIsGod ? $oEx->getMessage() : ''),
				'sDebug' => ($bIsGod ? $this->_JSONDebug : false)
			);
		}
	}

	public function getForCustomerGroupAndServiceType($customerGroupId, $serviceTypeId) {
		try {
			return array(
				'ratePlans' => array_map('ORM::mapToArray', Rate_Plan::getForCustomerGroupAndServiceType($customerGroupId, $serviceTypeId))
			);
		} catch (Exception $exception) {
			return self::_buildExceptionResponse($exception);
		}
	}
}