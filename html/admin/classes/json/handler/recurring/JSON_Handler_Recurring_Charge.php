<?php

class JSON_Handler_Recurring_Charge extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	const MAX_LIMIT = 100;
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getRecurringChargesAwaitingApproval($bolCountOnly=false, $intLimit=0, $intOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		//
		//	NOTE: 	Sorting & Filtering is not supported by this (Dataset_Ajax) method. rmctainsh 20100527
		//
		
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Set the filter constraints (only retrieve the recurring adjusments that are awaiting approval)
			$arrFilter = array('recurringChargeStatus'	=> 	array(
																'Type'	=> Recurring_Charge::SEARCH_CONSTRAINT_RECURRING_CHARGE_STATUS_ID,
																'Value'	=> Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL')
															)
							);
			
			// Order by the createdOn timestamp ascending
			$arrSort = array(Recurring_Charge::ORDER_BY_CREATED_ON => true);
			
			if ($bolCountOnly)
			{
				// Count Only
				return array(
								"Success"		=> true,
								"iRecordCount"	=> Recurring_Charge::searchFor($arrFilter, $arrSort, null, null, true),
								"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$intLimit	= (max($intLimit, 0) == 0) ? self::MAX_LIMIT : (int)$intLimit;
				$intLimit	= ($intLimit > self::MAX_LIMIT)? self::MAX_LIMIT : $intLimit;
				
				$intOffset	= ($intLimit === null) ? 0 : max((int)$intOffset, 0);
				
				// Retrieve the recurring charges
				$arrRecCharges = Recurring_Charge::searchFor($arrFilter, $arrSort, $intLimit, $intOffset);
				
				$objPaginationDetails = Recurring_Charge::getLastSearchPaginationDetails();
				
				// Create a friendly/formatted version of each charge record, and its related details
				$arrEmployeeCache		= array();
				$arrRecChargesFormatted	= array();
				$intCount = 0;
				foreach ($arrRecCharges as $objRecCharge)
				{
					if (!array_key_exists($objRecCharge->createdBy, $arrEmployeeCache))
					{
						$arrEmployeeCache[$objRecCharge->createdBy] = Employee::getForId($objRecCharge->createdBy);
					}
					
					$arrRecCharge										= $objRecCharge->toArray(true);
					$arrRecCharge['minChargeIncGstFormatted']			= number_format(AddGST($objRecCharge->minCharge), 2, '.', '');
					$arrRecCharge['minChargeIncGst']					= AddGST($objRecCharge->minCharge);
					$arrRecCharge['recursionChargeIncGstFormatted']		= number_format(AddGST($objRecCharge->recursionCharge), 2, '.', '');
					$arrRecCharge['recursionChargeIncGstDescription']	= substr($objRecCharge->getRecursionChargeDescription(true), 1);  // The substr(, 1) is to remove the $ sign
					$arrRecCharge['timesToCharge']						= $objRecCharge->getTimesToCharge();
					$arrRecCharge['hasPartialFinalCharge']				= $objRecCharge->hasPartialFinalInstallmentCharge();
					$arrRecCharge['partialFinalChargeInGSTFormatted']	= number_format(AddGST($objRecCharge->calculatePartialFinalInstallmentCharge()), 2, '.', '');
					
					$arrRecCharge['chargedOnForFirstInstallmentFormatted']		= date('d-m-Y', strtotime($objRecCharge->getChargedOnDateForInstallment(1)));
					$arrRecCharge['chargedOnForFinalInstallmentFormatted']		= date('d-m-Y', strtotime($objRecCharge->getChargedOnDateForInstallment($arrRecCharge['timesToCharge'])));
					
					
					$arrRecCharge['natureFormatted']					= ($objRecCharge->nature == NATURE_DR)? 'Debit' : 'Credit';
					$arrRecCharge['inAdvanceFormatted']					= ($objRecCharge->inAdvance)? 'Advance' : 'Arrears';
					switch ($objRecCharge->recurringFreqType)
					{
						case BILLING_FREQ_DAY:
							$arrRecCharge['frequencyTypeFormatted'] .= 'Day';
							break;
						case BILLING_FREQ_MONTH:
							$arrRecCharge['frequencyTypeFormatted'] .= 'Month';
							break;
						case BILLING_FREQ_HALF_MONTH:
							$arrRecCharge['frequencyTypeFormatted'] .= 'Half-Month';
							break;
							
						default:
							$arrRecCharge['frequencyTypeFormatted'] .= '?';
							break;
					}
					if ($objRecCharge->recurringFreq != 1)
					{
						$arrRecCharge['frequencyTypeFormatted'] .= 's';
					}
					
					
					$arrRecCharge['accountViewHref']		= Href()->InvoicesAndPayments($objRecCharge->account);
					$arrRecCharge['accountName']			= $objRecCharge->accountName;
					$arrRecCharge['serviceViewHref']		= ($objRecCharge->service != null)? Href()->ViewService($objRecCharge->service) : null;
					$arrRecCharge['serviceFNN']				= $objRecCharge->serviceFNN;
					$arrRecCharge['createdByEmployeeName']	= $arrEmployeeCache[$objRecCharge->createdBy]->getName();
					$arrRecCharge['createdOnFormated']		= date('d-m-Y', strtotime($objRecCharge->createdOn));
					$arrRecCharge['startedOnFormated']		= date('d-m-Y', strtotime($objRecCharge->startedOn));
					
					$arrRecChargesFormatted[$intCount+$intOffset] = $arrRecCharge;
					$intCount++;
				}
				
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"		=> true,
								"aRecords"		=> $arrRecChargesFormatted,
								"iRecordCount"	=> ($objPaginationDetails !== null)? $objPaginationDetails->totalRecordCount : count($arrRecChargesFormatted),
								"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
							);
			}
		}
		catch (Exception $e)
		{
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	// If any of the recurring charges can't be approved, then none of them are
	// As soon as we implement Nested transactions, then we can fix this so it will approve the ones it can, and won't approve the others
	public function approveRecurringChargeRequests($arrRecChargeIds)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);

		try
		{
			TransactionStart();
			
			if (count($arrRecChargeIds) == 0)
			{
				throw new Exception('No recurring charge requests have been specified, to approve');
			}
			
			$arrRecChargesApproved	= array();
			$intEmployeeId			= Flex::getUserId();

			foreach ($arrRecChargeIds as $intRecChargeId)
			{
				$objRecCharge = null;
				try
				{
					$objRecCharge = Recurring_Charge::getForId(intval($intRecChargeId));
					
					// Approve the recurring charge (this will also log the action having taken place)
					$objRecCharge->setToApproved($intEmployeeId, true);

					// Add to the list of approved charges
					$arrRecChargesApproved[] = $objRecCharge;
				}
				catch (Exception $e)
				{
					$strRecChargeIdentifier = "RecurringCharge.Id: $intRecChargeId";
					if ($objRecCharge != null)
					{
						$strRecChargeIdentifier = $objRecCharge->getIdentifyingDescription(true, true, false, true, true);
					}
					throw new Exception("Failed to approve Recurring Charge Request '$strRecChargeIdentifier'.  Reason: ". $e->getMessage());
				}
			}
			
			TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"success"			=> true,
							"intSuccessCount"	=> count($arrRecChargesApproved),
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			
			return array(
							"success"		=> false,
							"errorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	// If any of the charges can't be approved, then none of them are
	// As soon as we implement Nested transactions, then we can fix this so it will approve the ones it can, and won't approve the others
	public function rejectRecurringChargeRequests($arrRecChargeIds, $strReason)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			TransactionStart();

			if (count($arrRecChargeIds) == 0)
			{
				throw new Exception('No recurring charge requests have been specified, to reject');
			}
			
			$strReason = trim($strReason);
			
			if ($strReason == '')
			{
				throw new Exception('No reason has been supplied as to why these recurring charges are being rejected');
			}
			
			$arrRecChargesRejected	= array();
			$intEmployeeId			= Flex::getUserId();
			
			foreach ($arrRecChargeIds as $intRecChargeId)
			{
				$objRecCharge = null;
				try
				{
					$objRecCharge = Recurring_Charge::getForId(intval($intRecChargeId));
					
					// Reject the recurring charge (this will also log the action having taken place)
					$objRecCharge->setToDeclined($intEmployeeId, true, $strReason);
					
					// Add to the list of rejected charges
					$arrRecChargesRejected[] = $objRecCharge;
				}
				catch (Exception $e)
				{
					$strRecChargeIdentifier = "RecurringCharge.Id: $intRecChargeId";
					if ($objRecCharge != null)
					{
						$strRecChargeIdentifier = $objRecCharge->getIdentifyingDescription(true, true, false, true, true);
					}
					throw new Exception("Failed to reject Recurring Charge Request '$strRecChargeIdentifier'.  Reason: ". $e->getMessage());
				}
			}

			TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"success"			=> true,
							"intSuccessCount"	=> count($arrRecChargesRejected),
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			
			return array(
							"success"		=> false,
							"errorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}	
}
?>