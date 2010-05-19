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
	
	public function getChargesAwaitingApproval($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		return self::getRecords($bCountOnly, $iLimit, $iOffset, CHARGE_MODEL_CHARGE);
	}
	
	public function getAdjustmentsAwaitingApproval($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		return self::getRecords($bCountOnly, $iLimit, $iOffset, CHARGE_MODEL_ADJUSTMENT);
	}
	
	public function getAllAwaitingApproval($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		return self::getRecords($bCountOnly, $iLimit, $iOffset);
	}
	
	public function getRecords($bCountOnly=false, $iLimit=0, $iOffset=0, $iChargeModel=false)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Set the filter constraints (only retrieve the adjusments that are awaiting approval)
			$aFilter = 	array(
							array(
								'Type'	=> Charge::SEARCH_CONSTRAINT_CHARGE_STATUS,
								'Value'	=> CHARGE_WAITING
							)
						);
			
			// Add charge model filter if necessary
			if ($iChargeModel !== false)
			{
				$aFilter[]	= array('Type' => Charge::SEARCH_CONSTRAINT_CHARGE_MODEL_ID, 'Value' => $iChargeModel);
			}
			
			// Order by the createdOn timestamp ascending
			$aSort = array(Charge::ORDER_BY_CREATED_ON => true);
			
			if ($bCountOnly)
			{
				// Count Only
				return array(
								"Success"			=> true,
								"intRecordCount"	=> Charge::searchFor($aFilter, $aSort, null, null, true),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) ? self::MAX_LIMIT : (int)$iLimit;
				$iLimit		= ($iLimit > self::MAX_LIMIT)? self::MAX_LIMIT : $iLimit;
				$iOffset	= ($iLimit === null) ? 0 : max((int)$iOffset, 0);
				
				// Retrieve the charges
				$aCharges	= Charge::searchFor($aFilter, $aSort, $iLimit, $iOffset);
				
				$oPaginationDetails	= Charge::getLastSearchPaginationDetails();
				
				// Create a friendly/formatted version of each charge record, and its related details
				$aEmployeeCache		= array();
				$aChargesFormatted	= array();
				$iCount 			= 0;
				foreach ($aCharges as $oCharge)
				{
					if (!array_key_exists($oCharge->createdBy, $aEmployeeCache))
					{
						$aEmployeeCache[$oCharge->createdBy] = Employee::getForId($oCharge->createdBy);
					}
					
					$aCharge							= $oCharge->toArray(true);
					$aCharge['amountIncGstFormatted']	= number_format(AddGST($oCharge->amount), 2, '.', '');
					$aCharge['amountIncGst']			= AddGST($oCharge->amount);
					$aCharge['natureFormatted']			= ($oCharge->nature == NATURE_DR)? 'Debit' : 'Credit';
					$aCharge['accountViewHref']			= Href()->InvoicesAndPayments($oCharge->account);
					$aCharge['accountName']				= $oCharge->accountName;
					$aCharge['serviceViewHref']			= ($oCharge->service != null)? Href()->ViewService($oCharge->service) : null;
					$aCharge['serviceFNN']				= $oCharge->serviceFNN;
					$aCharge['createdByEmployeeName']	= $aEmployeeCache[$oCharge->createdBy]->getName();
					$aCharge['createdOnFormated']		= date('d-m-Y', strtotime($oCharge->createdOn));
					
					$aChargesFormatted[$iCount+$iOffset] = $aCharge;
					$iCount++;
				}
				
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"			=> true,
							"arrRecords"		=> $aChargesFormatted,
							"intRecordCount"	=> ($oPaginationDetails !== null)? $oPaginationDetails->totalRecordCount : count($aChargesFormatted),
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (Exception $e)
		{
			return 	array(
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