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
	
	public function getChargesAwaitingApproval($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		return self::getRecords($bCountOnly, $iLimit, $iOffset, $oFieldsToSort, CHARGE_MODEL_CHARGE);
	}
	
	public function getAdjustmentsAwaitingApproval($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		return self::getRecords($bCountOnly, $iLimit, $iOffset, $oFieldsToSort, CHARGE_MODEL_ADJUSTMENT);
	}
	
	public function getAllAwaitingApproval($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		return self::getRecords($bCountOnly, $iLimit, $iOffset, $oFieldsToSort);
	}
	
	public function getRecords($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $iChargeModel=false)
	{
		//
		//	NOTE: 	Sorting & Filtering is not supported by this (Dataset_Ajax) method. rmctainsh 20100527
		//
		
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
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> Charge::searchFor($aFilter, $aSort, null, null, true),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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
							"Success"		=> true,
							"aRecords"		=> $aChargesFormatted,
							"iRecordCount"	=> ($oPaginationDetails !== null)? $oPaginationDetails->totalRecordCount : count($aChargesFormatted),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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
	public function approveChargeRequests($aChargeIds, $iChargeModel=CHARGE_MODEL_CHARGE)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		TransactionStart();
		
		try
		{
			$sChargeModel	= Constant_Group::getConstantGroup('charge_model')->getConstantName($iChargeModel);
			
			if (count($aChargeIds) == 0)
			{
				throw new Exception("No {$sChargeModel} requests have been specified, to approve");
			}
			
			$aChargesApproved	= array();
			$iEmployeeId		= Flex::getUserId();

			foreach ($aChargeIds as $iChargeId)
			{
				$oCharge = null;
				try
				{
					$oCharge = Charge::getForId(intval($iChargeId));
					
					// Approve the charge (this will also log the action having taken place)
					$oCharge->setToApproved($iEmployeeId, true);

					// Add to the list of approved charges
					$aChargesApproved[] = $oCharge;
				}
				catch (Exception $e)
				{
					$sChargeIdentifier = "Charge.Id: $iChargeId";
					if ($oCharge != null)
					{
						$sChargeIdentifier = $oCharge->getIdentifyingDescription(true, true, false);
					}
					throw new Exception("Failed to approve {$sChargeModel} Request '$sChargeIdentifier'.  Reason: ". $e->getMessage());
				}
			}
			
			TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"success"			=> true,
							"intSuccessCount"	=> count($aChargesApproved),
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
	public function rejectChargeRequests($aChargeIds, $sReason, $iChargeModel=CHARGE_MODEL_CHARGE)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		TransactionStart();
		
		try
		{
			$sChargeModel	= Constant_Group::getConstantGroup('charge_model')->getConstantName($iChargeModel);
			
			if (count($aChargeIds) == 0)
			{
				throw new Exception("No {$sChargeModel} requests have been specified, to reject");
			}
			
			$sReason = trim($sReason);
			
			if ($sReason == '')
			{
				throw new Exception("No reason has been supplied as to why these {$sChargeModel}s are being rejected");
			}
			
			$aChargesRejected	= array();
			$iEmployeeId		= Flex::getUserId();
			
			foreach ($aChargeIds as $iChargeId)
			{
				$oCharge = null;
				try
				{
					$oCharge = Charge::getForId(intval($iChargeId));
					
					// Reject the charge (this will also log the action having taken place)
					$oCharge->setToDeclined($iEmployeeId, true, $sReason);
					
					// Add to the list of rejected charges
					$aChargesRejected[] = $oCharge;
				}
				catch (Exception $e)
				{
					$sChargeIdentifier = "Charge.Id: $iChargeId";
					if ($oCharge != null)
					{
						$sChargeIdentifier = $oCharge->getIdentifyingDescription(true, true, false);
					}
					throw new Exception("Failed to reject {$sChargeModel} Request '$sChargeIdentifier'.  Reason: ". $e->getMessage());
				}
			}

			TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"success"			=> true,
							"intSuccessCount"	=> count($aChargesRejected),
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
	
	public function getForAccount($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		try
		{
			if (!AuthenticatedUser()->userHasPerm(PERMISSION_OPERATOR_VIEW))
			{
				throw new JSON_Handler_Charge_Exception('You do not have permission to view charges'); 
			}
			
			// Permissions checks
			$bUserIsGod					= Employee::getForId(Flex::getUserId())->isGod();
			$bUserIsCreditManagement	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
			$bolUserCanDeleteCharges	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || $bUserIsCreditManagement;
			
			// Build the list of columns to use for the Charge DBL (as it is pulling this information from 2 tables)
			$aVisibleChargeTypes	= array(CHARGE_TYPE_VISIBILITY_VISIBLE);
			if ($bUserIsCreditManagement)
			{
				$aVisibleChargeTypes[]	= CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL;
			}
			
			if ($bUserIsGod)
			{
				$aVisibleChargeTypes[]	= CHARGE_TYPE_VISIBILITY_HIDDEN;
			}
			
			// Get Charge set
			$aCharges	=	Charge::searchFor(
								array(	// Search constraints
									array(
										'Type' 	=> Charge::SEARCH_CONSTRAINT_ACCOUNT_ID, 
										'Value' => $iAccountId
									),
									array(
										'Type'	=> Charge::SEARCH_CONSTRAINT_CHARGE_STATUS,
										'Value'	=> array(CHARGE_WAITING, CHARGE_APPROVED, CHARGE_TEMP_INVOICE, CHARGE_INVOICED)
									)
								), 
								array(	// Order by fields
									Charge::ORDER_BY_CHARGED_ON	=> false,
									Charge::ORDER_BY_ID			=> false
								)
							);
			
			$aStdClassCharges	= array();
			
			foreach ($aCharges as $oCharge)
			{
				$oStdClassCharge	= $oCharge->toStdClass();
				
				// Verify that the charge type is NULL or has the correct visibility
				$oChargeType	= Charge_Type::getForId($oCharge->charge_type_id);
				
				if (!$oChargeType)
				{
					$oChargeType	= Charge_Type::getByCode($oCharge->ChargeType);
				}
				
				if (!$oChargeType->Id || in_array($oChargeType->charge_type_visibility_id, $aVisibleChargeTypes))
				{
					// Get human readable versions of createdby, approvedby and status
					if ($oStdClassCharge->Status == CHARGE_APPROVED)
					{
						$oStdClassCharge->approved_by_label	= Employee::getForId($oCharge->ApprovedBy)->getName();
					}
					
					if (!$bUserIsGod)
					{
						$oStdClassCharge->service_id	= $oStdClassCharge->Service;
					}
					
					$oStdClassCharge->charge_on_label	= date('d-m-Y', strtotime($oCharge->ChargedOn));
					$oStdClassCharge->serviceFNN		= $oCharge->serviceFNN;
					$oStdClassCharge->created_by_label	= Employee::getForId($oCharge->CreatedBy)->getName();
					$oStdClassCharge->status_label		= Constant_Group::getConstantGroup('ChargeStatus')->getConstantName($oStdClassCharge->Status);
					$oStdClassCharge->amount_inc_gst	= number_format(AddGST($oStdClassCharge->Amount), 2, '.', '');
					$aStdClassCharges[]					= $oStdClassCharge;
				}
			}
			
			return 	array(
						"Success"		=> true,
						"aCharges"		=> $aStdClassCharges,
						"bCanDelete"	=> $bolUserCanDeleteCharges,
						"bUserIsGod"	=> $bUserIsGod
					);
		}
		catch (JSON_Handler_Charge_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting charges from the database')
					);
		}
	}
}

class JSON_Handler_Charge_Exception extends Exception
{
	// No changes
}

?>