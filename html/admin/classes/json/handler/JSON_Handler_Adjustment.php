<?php

class JSON_Handler_Adjustment extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getApprovedDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Setup default filtering
			
			
			$iRecordCount = Adjustment::searchForApproved(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Adjustment::searchForApproved(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			
			foreach ($aData as $aRecord)
			{
				$aRecord['extra_detail_enabled']	= $bUserIsGod;
				$aResults[$i] 						= $aRecord;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getPendingDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Adjustment::searchForPending(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Adjustment::searchForPending(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			
			foreach ($aData as $aRecord)
			{
				$aResults[$i] = $aRecord;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getReversalInformation($iAdjustmentId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return array('bSuccess' => true, 'oReversalData' => new StdClass());
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function reverseAdjustment($iAdjustmentId, $iReasonId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAdjustment = new Logic_Adjustment(Adjustment::getForId($iAdjustmentId));
			$oAdjustment->reverse($iReasonId);
			
			return array('bSuccess' => true);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function createAdjustment($oDetails)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			// Validation
			$aErrors = array();
			if ($oDetails->adjustment_type_id === null)
			{
				$aErrors[] = 'Adjustment Type not supplied';
			}
			
			if ($oDetails->amount === '')
			{
				$aErrors[] = 'Amount not supplied';
			}
			else if ($oDetails->amount === '')
			{
				$aErrors[] = 'Invalid Amount supplied';
			}
			
			if ($oDetails->account_id === null)
			{
				$aErrors[] = 'Account Id not supplied';
			}
			else if (Account::getForId($oDetails->account_id)->Archived == ACCOUNT_STATUS_PENDING_ACTIVATION)
			{
				$aErrors[] = 'The Account is pending activation. Adjustments cannot be requested at this time.';
			}
			
			if ($oDetails->service_id !== null)
			{
				$oService = Service::getForId($oDetails->service_id);
				if ($oService->Status == SERVICE_PENDING)
				{
					$aErrors[] = 'Cannot use the Service, it is pending activation. Adjustments can not be requested at this time.';
				}
				else if (!$oService->isCurrentlyActive())
				{
					$aErrors[] = 'This service is not currently active on this account. Adjustments can only be requested for active services.';
				}
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}
			
			if (!$oDataAccess->TransactionStart())
			{
				throw new Exception("Failed to start db transaction");
			}
			
			// Prepare details
			$oDetails->amount = Rate::roundToRatingStandard($oDetails->amount, 2);
			if ($oDetails->invoice_id == '')
			{
				$oDetails->invoice_id = null;
			}
			
			$iNow = DataAccess::getDataAccess()->getNow(true);
			
			// Create adjustment
			$oAdjustment 						= new Adjustment(get_object_vars($oDetails));
			$oAdjustment->balance				= $oAdjustment->amount;
			$oAdjustment->adjustment_nature_id 	= ADJUSTMENT_NATURE_ADJUSTMENT;
			$oAdjustment->adjustment_status_id 	= ADJUSTMENT_STATUS_PENDING;
			$oAdjustment->effective_date		= date('Y-m-d', $iNow);
			$oAdjustment->created_datetime		= date('Y-m-d H:i:s', $iNow);
			$oAdjustment->created_employee_id	= Flex::getUserId();
			$oAdjustment->calculateTaxComponent();
			$oAdjustment->save();
			
			// Create an action to record the requested adjustment
			if ($oAdjustment->service_id !== null)
			{
				// The adjustment is being applied to a specific service
				$iAccountId = NULL;
				$iServiceId = $oAdjustment->service_id;
			}
			else
			{
				// The adjustment is being applied to an account
				$iAccountId = $oAdjustment->account_id;
				$iServiceId = NULL;
			}
			
			$oAdjustmentType		= Adjustment_Type::getForId($oAdjustment->adjustment_type_id);
			$sNature				= Transaction_Nature::getForId($oAdjustmentType->transaction_nature_id)->code;
			$sAmount				= $oAdjustment->amount;
			$sChargeType			= "{$oAdjustmentType->code} - {$oAdjustmentType->description}";
			$sActionExtraDetails	= "	Type: {$sChargeType} ({$sNature})\n Amount (Inc GST): \${$sAmount} {$sNature}";
			
			Action::createAction("Adjustment Requested", $sActionExtraDetails, $iAccountId, $iServiceId, null, Flex::getUserId(), Employee::SYSTEM_EMPLOYEE_ID);
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new Exception("Failed to commit db transaction");
			}
			
			return array('bSuccess' => true, 'iAdjustmentId' => $oAdjustment->id);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getAdjustableInvoicesForAccount($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oQuery 	= new Query();
			$mResult	= $oQuery->Execute("	SELECT	*
												FROM 	Invoice
												WHERE	Account = {$iAccountId}
												AND 	invoice_run_id IN (
															SELECT 	id
															FROM 	InvoiceRun
															WHERE 	invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED."
															AND 	invoice_run_type_id IN (".INVOICE_RUN_TYPE_LIVE.", ".INVOICE_RUN_TYPE_INTERIM.", ".INVOICE_RUN_TYPE_INTERIM_FIRST.", ".INVOICE_RUN_TYPE_FINAL .")
														)
												ORDER BY 	CreatedOn DESC, Id DESC
												LIMIT 		6");
			if ($mResult === false)
			{
				throw new Exception_Database("Failed to get adjustable invoices for Account {$iAccountId}. ".$oQuery->Error());
			}

			$aInvoices = array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$oInvoice 					= new Invoice($aRow);
				$aInvoices[$oInvoice->Id]	= $oInvoice->toStdClass();
			}
			
			return array('bSuccess' => true, 'aInvoices' => $aInvoices);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function approveAdjustmentRequests($aAdjustmentIds)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			if (!$oDataAccess->TransactionStart())
			{
				throw new Exception_Database("Failed to start db transaction");
			}
			
			foreach ($aAdjustmentIds as $iAdjustmentId)
			{
				// Approve the adjustment
				$oAdjustment = Adjustment::getForId($iAdjustmentId);
				$oAdjustment->approve();
				
				// Process the adjustment
				$oLogicAccount = Logic_Account::getInstance($oAdjustment->account_id);
				//$oLogicAccount->processDistributable(new Logic_Adjustment($oAdjustment));
				//simply distributing the balance of the new adjustment is not enough
				//because there might be other distributables that could previously not distribute their balance
				//which after distributing this one can distribute theirs. 
				//Example: if this adjustemtn is a debit adjustment, and before distributing it the sum(collectable.balance) === 0, and there is a payment with remaining balance, that payment's balance must be distributed after distributing the current adjustment's balance.
				//@TODO: in order to optimise this, implement functionality that allows for just distributing balances of any distributables that currently have a balance remaining, instead of a full redistribution.
				//$oLogicAccount->redistributeBalances();
				$oLogicAdjustment	= new Logic_Adjustment($oAdjustment);
				$oLogicAdjustment->distribute();
			}
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new Exception_Database("Failed to commit db transaction");
			}
			
			return array('bSuccess' => true);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function declineAdjustmentRequests($aAdjustmentIds, $iAdjustmentReviewOutcomeId)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			if (!$oDataAccess->TransactionStart())
			{
				throw new Exception_Database("Failed to start db transaction");
			}
			
			foreach ($aAdjustmentIds as $iAdjustmentId)
			{
				Adjustment::getForId($iAdjustmentId)->decline($iAdjustmentReviewOutcomeId);
			}
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new Exception_Database("Failed to commit db transaction");
			}
			
			return array('bSuccess' => true);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getForIds($aAdjustmentIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aAdjustments = array();
			foreach ($aAdjustmentIds as $iAdjustmentId)
			{
				$oStdClass						= Adjustment::getForId($iAdjustmentId)->toStdClass();
				$oStdClass->adjustment_type		= Adjustment_Type::getForId($oStdClass->adjustment_type_id)->toStdClass();
				$aAdjustments[$iAdjustmentId]	= $oStdClass;
			}
			return array('bSuccess' => true, 'aAdjustments' => $aAdjustments);
		}
		catch (Exception $e)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
}

class JSON_Handler_Adjustment_Exception extends Exception
{
	// No changes
}

?>