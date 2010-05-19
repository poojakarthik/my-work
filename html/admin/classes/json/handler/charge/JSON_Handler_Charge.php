<?php

class JSON_Handler_Charge extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	const MAX_LIMIT = 100;
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getChargesAwaitingApproval($bolCountOnly=false, $intLimit=0, $intOffset=0)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Set the filter constraints (only retrieve the adjusments that are awaiting approval)
			$arrFilter = array('chargeStatus'	=> array(	'Type'	=> Charge::SEARCH_CONSTRAINT_CHARGE_STATUS,
															'Value'	=> CHARGE_WAITING
														)
							);
			
			// Order by the createdOn timestamp ascending
			$arrSort = array(Charge::ORDER_BY_CREATED_ON => true);
			
			if ($bolCountOnly)
			{
				// Count Only
				return array(
								"Success"			=> true,
								"intRecordCount"	=> Charge::searchFor($arrFilter, $arrSort, null, null, true),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$intLimit	= (max($intLimit, 0) == 0) ? self::MAX_LIMIT : (int)$intLimit;
				$intLimit	= ($intLimit > self::MAX_LIMIT)? self::MAX_LIMIT : $intLimit;
				
				$intOffset	= ($intLimit === null) ? 0 : max((int)$intOffset, 0);
				
				// Retrieve the charges
				$arrCharges = Charge::searchFor($arrFilter, $arrSort, $intLimit, $intOffset);
				
				$objPaginationDetails = Charge::getLastSearchPaginationDetails();
				
				// Create a friendly/formatted version of each charge record, and its related details
				$arrEmployeeCache		= array();
				$arrChargesFormatted	= array();
				$intCount = 0;
				foreach ($arrCharges as $objCharge)
				{
					if (!array_key_exists($objCharge->createdBy, $arrEmployeeCache))
					{
						$arrEmployeeCache[$objCharge->createdBy] = Employee::getForId($objCharge->createdBy);
					}
					
					$arrCharge							= $objCharge->toArray(true);
					$arrCharge['amountIncGstFormatted']	= number_format(AddGST($objCharge->amount), 2, '.', '');
					$arrCharge['amountIncGst']			= AddGST($objCharge->amount);
					$arrCharge['natureFormatted']		= ($objCharge->nature == NATURE_DR)? 'Debit' : 'Credit';
					$arrCharge['accountViewHref']		= Href()->InvoicesAndPayments($objCharge->account);
					$arrCharge['accountName']			= $objCharge->accountName;
					$arrCharge['serviceViewHref']		= ($objCharge->service != null)? Href()->ViewService($objCharge->service) : null;
					$arrCharge['serviceFNN']			= $objCharge->serviceFNN;
					$arrCharge['createdByEmployeeName']	= $arrEmployeeCache[$objCharge->createdBy]->getName();
					$arrCharge['createdOnFormated']		= date('d-m-Y', strtotime($objCharge->createdOn));
					
					$arrChargesFormatted[$intCount+$intOffset] = $arrCharge;
					$intCount++;
				}
				
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"			=> true,
								"arrRecords"		=> $arrChargesFormatted,
								"intRecordCount"	=> ($objPaginationDetails !== null)? $objPaginationDetails->totalRecordCount : count($arrChargesFormatted),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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
	
	// If any of the charges can't be approved, then none of them are
	// As soon as we implement Nested transactions, then we can fix this so it will approve the ones it can, and won't approve the others
	public function approveChargeRequests($arrChargeIds)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);

		TransactionStart();
		try
		{
			if (count($arrChargeIds) == 0)
			{
				throw new Exception('No charge requests have been specified, to approve');
			}
			
			$arrChargesApproved	= array();
			$intEmployeeId		= Flex::getUserId();

			foreach ($arrChargeIds as $intChargeId)
			{
				$objCharge = null;
				try
				{
					$objCharge = Charge::getForId(intval($intChargeId));
					
					// Approve the charge (this will also log the action having taken place)
					$objCharge->setToApproved($intEmployeeId, true);

					// Add to the list of approved charges
					$arrChargesApproved[] = $objCharge;
				}
				catch (Exception $e)
				{
					$strChargeIdentifier = "Charge.Id: $intChargeId";
					if ($objCharge != null)
					{
						$strChargeIdentifier = $objCharge->getIdentifyingDescription(true, true, false);
					}
					throw new Exception("Failed to approve Charge Request '$strChargeIdentifier'.  Reason: ". $e->getMessage());
				}
			}
			
			TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"success"			=> true,
							"intSuccessCount"	=> count($arrChargesApproved),
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
	public function rejectChargeRequests($arrChargeIds, $strReason)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		TransactionStart();
		
		try
		{
			if (count($arrChargeIds) == 0)
			{
				throw new Exception('No charge requests have been specified, to reject');
			}
			
			$strReason = trim($strReason);
			
			if ($strReason == '')
			{
				throw new Exception('No reason has been supplied as to why these charges are being rejected');
			}
			
			$arrChargesRejected	= array();
			$intEmployeeId		= Flex::getUserId();
			
			foreach ($arrChargeIds as $intChargeId)
			{
				$objCharge = null;
				try
				{
					$objCharge = Charge::getForId(intval($intChargeId));
					
					// Reject the charge (this will also log the action having taken place)
					$objCharge->setToDeclined($intEmployeeId, true, $strReason);
					
					// Add to the list of rejected charges
					$arrChargesRejected[] = $objCharge;
				}
				catch (Exception $e)
				{
					$strChargeIdentifier = "Charge.Id: $intChargeId";
					if ($objCharge != null)
					{
						$strChargeIdentifier = $objCharge->getIdentifyingDescription(true, true, false);
					}
					throw new Exception("Failed to reject Charge Request '$strChargeIdentifier'.  Reason: ". $e->getMessage());
				}
			}

			TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"success"			=> true,
							"intSuccessCount"	=> count($arrChargesRejected),
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