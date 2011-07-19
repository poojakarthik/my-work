<?php

class JSON_Handler_Collections extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAccounts($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aFilter	= get_object_vars($oFilter);
			$aSort		= get_object_vars($oSort);
			if ($bCountOnly)
			{
				return	array(
							'bSuccess'		=> true,
							'iRecordCount'	=> Logic_Account::countForCollectionsLedger($aFilter)
						);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aResult 	= Logic_Account::searchAndCountForCollectionsLedger($iLimit, $iOffset, $aSort, $aFilter);
			$aResults	= array();
			$i			= $iOffset;
			foreach ($aResult['aData'] as $aRecord)
			{
				$aResults[$i] = $aRecord;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $aResult['iCount']
					);
		}
		catch (JSON_Handler_Collections_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage(),
						'Message'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
					);
		}
	}
	
	public function generateAccountLedgerFile($oSort=null, $oFilter=null, $sFileType)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aColumns =	array(
							'account_id' 							=> 'Account|int',
							'account_name' 							=> 'Business Name',
							'customer_group_name' 					=> 'Customer Group',
							'balance'				 				=> 'Balance|currency',
							'overdue_balance'		 				=> 'Overdue Balance|currency',
							'overdue_amount_from_1_30'				=> 'Debt (1-30 days old)|currency',
							'overdue_amount_from_30_60' 			=> 'Debt (30-60 days old)|currency',
							'overdue_amount_from_60_90'				=> 'Debt (60-90 days old)|currency',
							'overdue_amount_from_90_on'				=> 'Debt (90+ days old)|currency',
							'last_payment_paid_date'				=> 'Last Payment Date',
							'last_payment_amount' 					=> 'Last Payment Amount|currency',
							'collection_status'						=> 'Collections Status',
							'previous_promise_instalment_due_date' 	=> 'Previous Promise Instalment Due',
							'next_promise_instalment_due_date'		=> 'Next Promise Instalment Due',
							'collection_event_name'					=> 'Current Event'
						);
			$aRecords = Logic_Account::searchForCollectionsLedger(null, null, get_object_vars($oSort), get_object_vars($oFilter));
			
			// Build list of lines for the file
			$aLines	= array();
			foreach ($aRecords as $aRecord)
			{
				$aLine = array();
				foreach ($aColumns as $sField => $sTitle)
				{
					$mValue	= $aRecord[$sField];
					if ($sField == 'collection_status')
					{
						$aSplit 	= explode('_', $mValue);
						$aNewValue	= array();
						foreach ($aSplit as $sPiece)
						{
							$aNewValue[] = substr($sPiece, 0, 1).strtolower(substr($sPiece, 1));
						}
						$mValue = implode(' ', $aNewValue);
					}
					$aLine[$sTitle] = $mValue;
				}
				$aLines[] = $aLine;
			}
			
			switch ($sFileType)
			{
				case 'CSV':
					$sFileExtension = 'csv';
					$sMIME			= 'text/csv';
					break;
				case 'Excel2007':
					$sFileExtension = 'xlsx';
					$sMIME			= 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					break;
			}
			
			$sFilename	= "collections_account_ledger_".date('YmdHis').".{$sFileExtension}";
			$sFilePath	= FILES_BASE_PATH."/temp/{$sFilename}";
			
			$oSpreadsheet = new Logic_Spreadsheet(array_keys($aLines[0]), $aLines, $sFileType);
            $oSpreadsheet->saveAs($sFilePath, $sFileType);
			
			return array('bSuccess' => true, 'sFilename' => $sFilename, 'sMIME' => $sMIME);
		}
		catch (Exception $oException)
		{
			$sMessage = $bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function getOCAReferralLedgerDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Account_OCA_Referral::getLedger(true, null, null, null, $oFilter);
			if ($bCountOnly)
			{
				return	array(
							'bSuccess'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= ($iLimit === 0 ? null : $iLimit);
			$aData	 	= Account_OCA_Referral::getLedger(false, $iLimit, $iOffset, $oSort, $oFilter);
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
		catch (JSON_Handler_Collections_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage(),
						'Message'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
					);
		}
	}
	
	public function actionOCAReferrals($aAccountOCAReferralIds)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			return array('bSuccess' => false, 'sMessage' => 'Failed to start db Transaction. Please contact YBS.');
		}
		
		try
		{
			// Create & Commit invoice runs
			$aAccountOCAReferralsByAccountId	= array();
			$aAccountsByCustomerGroupId 		= array();
			foreach ($aAccountOCAReferralIds as $iAccountOCAReferralId)
			{
				$oAccountOCAReferral 												= Account_OCA_Referral::getForId($iAccountOCAReferralId);
				$aAccountOCAReferralsByAccountId[$oAccountOCAReferral->account_id]	= $oAccountOCAReferral;
				$oAccount															= Account::getForId($oAccountOCAReferral->account_id);
				
				// Check last invoice run for the account, is it committed and interim? if so this account fails referral, otherwise
				$aInvoice = Invoice::getForAccount($oAccount->Id);
				if (count($aInvoice) > 0)
				{
				    $oLastInvoice 				= array_pop($aInvoice);
				    $oLastInvoiceRun 			= Invoice_Run::getForId($oLastInvoice->invoice_run_id);
				    $aInterimInvoiceRunTypes 	= array(INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM_FIRST);
					if ($oLastInvoiceRun && ($oLastInvoiceRun->invoice_run_status_id == INVOICE_RUN_STATUS_COMMITTED) && in_array($oLastInvoiceRun->invoice_run_type_id, $aInterimInvoiceRunTypes))
					{
						$sInvoiceRunType = Constant_Group::getConstantGroup('invoice_run_type')->getConstantName($oLastInvoiceRun->invoice_run_type_id);
						throw new JSON_Handler_Collections_Exception("Failed to commit Final Invoice for Account {$oAccountOCAReferral->account_id} it already has a commited Invoice of type: '{$sInvoiceRunType}'.");
					}
				}
								
				$iCustomerGroupId = $oAccount->CustomerGroup;
				if (!$aAccountsByCustomerGroupId[$iCustomerGroupId])
				{
					$aAccountsByCustomerGroupId[$iCustomerGroupId] = array();
				}
				$aAccountsByCustomerGroupId[$iCustomerGroupId][] = $oAccount;
			}
			
			Log::getLog()->log("Sorted accounts by customer group");
			
			$iDeliveryMethodIdOverride = Collections_Config::get()->oca_final_invoice_delivery_method_id;
			foreach ($aAccountsByCustomerGroupId as $iCustomerGroupId => $aAccounts)
			{
				try
				{
					// Try to generate the invoice run for the accounts
					$oInvoiceRun = new Invoice_Run();
					$oInvoiceRun->generateForAccounts($iCustomerGroupId, $aAccounts, INVOICE_RUN_TYPE_FINAL);
					
					Log::getLog()->log("Invoice Run generated");
					
					// Override delivery method of each invoice, if set in collections_config
					if ($iDeliveryMethodIdOverride !== null)
					{
						$aInvoices = Invoice::getForInvoiceRunId($oInvoiceRun->Id);
						foreach ($aInvoices as $oInvoice)
						{
							$oInvoice->DeliveryMethod = $iDeliveryMethodIdOverride;
							$oInvoice->save();
						}
						
						// Re-export the invoices
						$oInvoiceRun->export();
					}
					
					$oInvoiceRun->commit();
					
					// Clear cache so that Invoice objects inherit their new status
					Invoice::clearCache();
					
					$oInvoiceRun->deliver();
										
					// Update the invoice_run_id for each account_oca_referral (saved, when they are actioned, below)
					foreach ($aAccounts as $oAccount)
					{
						$aAccountOCAReferralsByAccountId[$oAccount->Id]->invoice_run_id = $oInvoiceRun->Id;
					}
					
					Log::getLog()->log("Generated, Commited & Deliverd Invoice run for customer group {$iCustomerGroupId}");
				}
				catch (Exception $oException)
	            {
	            	Log::getLog()->log("Error, attempting to revoke now: ".$oException->getMessage());
	            	
	                // Perform a Revoke on the Temporary Invoice Run
	                if ($oInvoiceRun->Id)
	                {
	                	try 
	                	{
	                		$oInvoiceRun->revoke();
	                	}
	                	catch (Exception $oEx)
	                	{
	                		// Ignore errors in this process, the transaction is to be rolled back anyway when the exception is thrown below
	                	}
	                }
	                
	                throw new JSON_Handler_Collections_Exception("Failed to commit Final Invoice for customer group ".Customer_Group::getForId($iCustomerGroupId)->internal_name.". Details: ".$oException->getMessage());
	            }
			}
			
			// Build referral file
			$oResourceTypeHandler = Resource_Type_File_Export_OCA_Referral::exportOCAReferrals($aAccountOCAReferralIds);
			
			// Mark referrals as actioned
			foreach ($aAccountOCAReferralsByAccountId as $iAccountId => $oAccountOCAReferral)
			{
				$oAccountOCAReferral->action();
				Log::getLog()->log("Actioned Account {$iAccountId}");
			}
			
			// Deliver the referral file
			$oResourceTypeHandler->deliver();
			
			Log::getLog()->log("Delivered Referral file");
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new JSON_Handler_Collections_Exception("Successful but failed to commit db transaction.");
			}
			
			return array('bSuccess' => true);
		}
		catch (JSON_Handler_Collections_Exception $oEx)
		{
			$sMessage = $oEx->getMessage();
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " Failed to rollback db transaction.";
			}
			
			return array('bSuccess' => false,'sMessage' => $sMessage);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " Failed to rollback db transaction.";
			}
			
			return array('bSuccess' => false,'sMessage' => $sMessage);
		}
	}
	
	public function getFinalInvoiceForOCAReferral($iAccountOCAReferralId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Get the account_oca_referral record, and clear it's current invoice_run_id
			$oAccountOCAReferral = Account_OCA_Referral::getForId($iAccountOCAReferralId);
			
			// If the final invoice has been commited already (i.e. the account_oca_referral record has been completed) just return the appropriate invoice object
			if ($oAccountOCAReferral->invoice_run_id !== null)
			{
				$oExistingInvoiceRun = Invoice_Run::getForId($oAccountOCAReferral->invoice_run_id);
				if ($oExistingInvoiceRun->invoice_run_status_id == INVOICE_RUN_STATUS_COMMITTED)
				{
					$oInvoice = Invoice::getForInvoiceRunAndAccount($oAccountOCAReferral->invoice_run_id, $oAccountOCAReferral->account_id);
					return array('bSuccess' => true, 'oInvoice' => $oInvoice->toStdClass());
				}
				else
				{
					throw new JSON_Handler_Collections_Exception('The Final Invoice has not been commited.');
				}
			}
			else
			{
				throw new JSON_Handler_Collections_Exception('No Final Invoice has been generated.');
			}
		}
		catch (JSON_Handler_Collections_Exception $oEx)
		{
			return array('bSuccess' => false, 'sMessage' => $oEx->getMessage());
		}
		catch (Exception $oException)
		{
			$sMessage = ($bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for more assistance.');
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function generateFinalInvoiceForOCAReferral($iAccountOCAReferralId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Get the account_oca_referral record, and clear it's current invoice_run_id
			$oAccountOCAReferral = Account_OCA_Referral::getForId($iAccountOCAReferralId);
			
			// If the final invoice has been commited already (i.e. the account_oca_referral record has been completed) just return the appropriate invoice object
			if ($oAccountOCAReferral->invoice_run_id !== null)
			{
				$oExistingInvoiceRun = Invoice_Run::getForId($oAccountOCAReferral->invoice_run_id);
				if ($oExistingInvoiceRun->invoice_run_status_id == INVOICE_RUN_STATUS_COMMITTED)
				{
					throw new JSON_Handler_Collections_Exception("Cannot generate an invoice, there is already a committed Final Invoice.");
				}
			}
			
			try
            {
            	// Generate the invoice (run)
                $oInvoiceRun = new Invoice_Run();
                $oInvoiceRun->generateSingle(
               		$oAccount->CustomerGroup, 
               		INVOICE_RUN_TYPE_FINAL, 
               		strtotime(date('Y-m-d', strtotime('+1 day'))), 
               		$oAccountOCAReferral->account_id
               	);
            }
            catch (Exception $oException)
            {
                // Perform a Revoke on the Temporary Invoice Run
                if ($oInvoiceRun->Id)
                {
                	$oInvoiceRun->revoke();
                }
                throw $oException;
            }
            
            // Update the account_oca_referral record
            $oAccountOCAReferral->invoice_run_id = $oInvoiceRun->Id;
            $oAccountOCAReferral->save();
            
            // Get the invoice that was created
            $oInvoice = Invoice::getForInvoiceRunAndAccount($oInvoiceRun->Id, $oAccountOCAReferral->account_id);
            
            return array('bSuccess' => true, 'oInvoice' => $oInvoice->toStdClass());
		}
		catch (JSON_Handler_Collections_Exception $oException)
		{
			return array('bSuccess' => false, 'sMessage' => $oException->getMessage());
		}
		catch (Exception $oException)
		{
			$sMessage = ($bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for more assistance.');
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function generateOCAReferralLedgerFile($oSort, $oFilter, $sFileType)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aColumns =	array(
							'account_id' 						=> 'Account|int',
							'account_name'						=> 'Business Name',
							'customer_group_name'				=> 'Customer Group',
							'actioned_datetime' 				=> 'Actioned On',
							'actioned_employee_name'			=> 'Actioned By',
							'invoice_id' 						=> 'Final Invoice|int',
							'file_export_filename'				=> 'Referral File',
							'account_oca_referral_status_name'	=> 'Status'
						);
			$aRecords = Account_OCA_Referral::getLedger(false, null, null, $oSort, $oFilter);

			// Build list of lines for the file
			$aLines	= array();
			foreach ($aRecords as $aRecord)
			{
				$aLine = array();
				foreach ($aColumns as $sField => $sTitle)
				{
					$aLine[$sTitle] = $aRecord[$sField];
				}
				$aLines[] = $aLine;
			}
			
			switch ($sFileType)
			{
				case 'CSV':
					$sFileExtension = 'csv';
					$sMIME			= 'text/csv';
					break;
				case 'Excel2007':
					$sFileExtension = 'xlsx';
					$sMIME			= 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					break;
			}
			
			$sFilename	= "collections_oca_referral_ledger_".date('YmdHis').".{$sFileExtension}";
			$sFilePath	= FILES_BASE_PATH."/temp/{$sFilename}";
			
			$oSpreadsheet = new Logic_Spreadsheet(array_keys($aLines[0]), $aLines, $sFileType);
            $oSpreadsheet->saveAs($sFilePath, $sFileType);
			
			return array('bSuccess' => true, 'sFilename' => $sFilename, 'sMIME' => $sMIME);
		}
		catch (Exception $oException)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function getExtendedPromiseDetailsForAccount($iAccountId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aPromise		= null;
			$oORMPromise 	= Collection_Promise::getCurrentForAccountId($iAccountId);
			if ($oORMPromise)
			{
				$oPromise 			= new Logic_Collection_Promise($oORMPromise);
				$aPromise			= $oPromise->toArray();
				$oAccount			= Account::getForId($oPromise->account_id);
				$oPaymentMethod		= $oAccount->getPaymentMethod();
				$aReason			= Collection_Promise_Reason::getForId($oPromise->collection_promise_reason_id)->toArray();
				$aInstalments		= $oPromise->getInstalments();
				$aArrayInstalments	= array();
				
				foreach ($aInstalments as $oInstalment)
				{
					$aInstalment 			= $oInstalment->toArray();
					$aInstalment['balance']	= $oInstalment->getBalance();
					$aArrayInstalments[] 	= $aInstalment;
				}
				
				$aPromise['account'] 						= $oAccount->toArray();
				$aPromise['collection_promise_reason'] 		= $aReason;
				$aPromise['created_employee_name']			= Employee::getForId($oPromise->created_employee_id)->getName();
				$aPromise['collection_promise_instalments']	= $aArrayInstalments;
				$aPromise['use_direct_debit_actual']		= ($oPaymentMethod->id == PAYMENT_METHOD_DIRECT_DEBIT) && $oPromise->use_direct_debit;
			}
			
			return	array(
						'bSuccess'	=> true,
						'oPromise'	=> $aPromise
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getEventSummaryForAccount($iAccountId, $iStartMilliseconds, $iEndMilliseconds)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod;
		try
		{
		    $sStartDate			= date('Y-m-d H:i:s', floor($iStartMilliseconds / 1000));
		    $sEndDate			= date('Y-m-d H:i:s', floor($iEndMilliseconds / 1000));
		    $sBetween			= "'{$sStartDate}' AND '{$sEndDate}'";
		    $oQuery 			= new Query();
		    $aUnsortedEvents	= array();

		    // Promise Instalments
		    $mPromiseInstalmentResult = $oQuery->Execute("	SELECT 	cpi.*
															FROM 	collection_promise_instalment cpi
															JOIN 	collection_promise cp ON (cp.id = cpi.collection_promise_id)
															WHERE 	cpi.due_date BETWEEN {$sBetween}
															AND 	cp.account_id = {$iAccountId}
															AND		(cp.collection_promise_completion_id IS NULL
																	OR cp.collection_promise_completion_id = ".COLLECTION_PROMISE_COMPLETION_KEPT.");");
		    if ($mPromiseInstalmentResult === false)
		    {
			    throw new Exception("Failed to get promise instalment summary. ".$oQuery->Error());
		    }

		    // Create unsorted event items for each row
		    while ($aRow = $mPromiseInstalmentResult->fetch_assoc())
		    {
			    self::_createAccountEventSummaryItems($aRow, 'collection_promise_instalment', $aUnsortedEvents);
		    }

		    // Event instances
		    $mEventInstanceResult = $oQuery->Execute("	SELECT 	aceh.*, ce.name AS collection_event_name
														FROM 	account_collection_event_history aceh
														JOIN	collection_event ce ON (ce.id = aceh.collection_event_id)
														WHERE 	(
																	aceh.scheduled_datetime BETWEEN {$sBetween}
																	OR aceh.completed_datetime BETWEEN {$sBetween}
																)
														AND 	aceh.account_id = {$iAccountId};");
		    if ($mEventInstanceResult === false)
		    {
			    throw new Exception("Failed to get completed event instance summary. ".$oQuery->Error());
		    }

		    // Create unsorted event items for each row (one for completed_datetime, one for scheduled_datetime if different)
		    while ($aRow = $mEventInstanceResult->fetch_assoc())
		    {
			    self::_createAccountEventSummaryItems($aRow, 'account_collection_event_history', $aUnsortedEvents);
		    }
		    
			$aPreviousEvent = NULL;
			while ($aNextEvent = $this->getNextEventDetails($iAccountId, $aPreviousEvent))
			{

				$aScenarioEvent 									= array();
				$aScenarioEvent['collection_event_invocation_id']	= $aNextEvent['invocation'];
				$aScenarioEvent['collection_event_name'] 			= "Next Collections Event: ".$aNextEvent['event_name'];
				$aScenarioEvent['id'] 								= $aNextEvent['event_id'];
				$aScenarioEvent['isExit']							= $aNextEvent['is_exit'];
				$aScenarioEvent['isNextEvent']						= TRUE;
				if (!isset($aUnsortedEvents[$aNextEvent['event_date']]))
				{
					$aUnsortedEvents[$aNextEvent['event_date']] = array();
				}
				$aUnsortedEvents[$aNextEvent['event_date']]['collection_scenario_collection_event'][] = $aScenarioEvent;


				$aPreviousEvent = $aNextEvent;
			}
			
			
		    // Suspensions
		    $mSuspensionResult = $oQuery->Execute("	SELECT 	cs.*
												    FROM 	collection_suspension cs
												    WHERE 	(
															    cs.proposed_end_datetime BETWEEN {$sBetween}
															    OR cs.start_datetime BETWEEN {$sBetween}
															    OR cs.effective_end_datetime BETWEEN {$sBetween}
														    )
												    AND 	cs.account_id = {$iAccountId};");
		    if ($mSuspensionResult === false)
		    {
			    throw new Exception("Failed to get suspension summary. ".$oQuery->Error());
		    }

		    // Create unsorted event items for each row (one for start_datetime, one for proposed_datetime if not finished, otherwise effective_end_datetime)
		    while ($aRow = $mSuspensionResult->fetch_assoc())
		    {
			    self::_createAccountEventSummaryItems($aRow, 'collection_suspension', $aUnsortedEvents);
		    }

		    // Sort the dates
		    $aDates = array_keys($aUnsortedEvents);
		    sort($aDates);

		    // Create new sorted event hash
		    $aSortedEvents = array();
		    foreach ($aDates as $sDate)
		    {
			    $aSortedEvents[$sDate] = $aUnsortedEvents[$sDate];
		    }

		    return	array(
					    'bSuccess' 	=> true,
					    'aEvents'	=> $aSortedEvents
				    );
		}
		catch (Exception $oException)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}

	/**
	 * Gets the next collections event.
	 * If  $aPreviousEvent === NULL, the next event is calculated relative to the most recent collection event instance for the account.
	 * If  $aPreviousEvent !== NULL, the next event is calculated relative to  $aPreviousEvent
	 *
	 * IF $aPreviousEvent !== NULL a next event is only returned if it would be scheduled on the same day as the previous event, ie day_offset === 0
	 *
	 * @param <type> $iAccountId
	 * @param <array> $aPreviousEvent - array('invocation'=>$iInvocationId, 'event_name'=>$sEventName, 'event_id'=>$iEventId, 'is_exit' => $bIsExit, 'event_date'=>$sNextEventDate, 'event_object'=>$oEvent )
	 * @return <array> array('invocation'=>$iInvocationId, 'event_name'=>$sEventName, 'event_id'=>$iEventId, 'is_exit' => $bIsExit, 'event_date'=>$sNextEventDate, 'event_object'=>$oEvent )
	 */
	private function getNextEventDetails($iAccountId, $aPreviousEvent = NULL)
	{
		$oAccount 				= Logic_Account::getInstance($iAccountId);
		if ($oAccount->isInsuspension())
			return FALSE;
		$oScenario  			= $oAccount->getCurrentScenarioInstance()->getScenario();
		$oLastScheduledEvent 	= $oAccount->getMostRecentCollectionEventInstance();
		$bIsIncollections 		= $oAccount->isCurrentlyInCollections();
		$bShouldBeInCollections = $oAccount->shouldCurrentlyBeInCollections();
		$oEvent;
		if ($aPreviousEvent === NULL)
		{
			$oEvent = $bIsIncollections && ! $bShouldBeInCollections ? new Logic_Collection_Event_ExitCollections() :$oAccount->getNextCollectionScenarioEvent(TRUE);		

			$iInvocationId;
			$sEventName;
			$iEventId;
			$sNextEventDate;
			$bIsExit;

			if ($oEvent instanceof Logic_Collection_Event_ExitCollections)
			{
				$iInvocationId	= COLLECTION_EVENT_INVOCATION_AUTOMATIC;
				$sEventName		= $oEvent->name;
				$iEventId		= $oEvent->id;
				$sNextEventDate = date("Y-m-d", DataAccess::getDataAccess()->getNow(TRUE));
				$bIsExit		= TRUE;
			}
			else if ($oEvent !== NULL)
			{
				if ($bIsIncollections && $oScenario->id == $oLastScheduledEvent->getScenario()->id)
				{
					$sDateToOffset = $oLastScheduledEvent->completed_datetime != NULL ? $oLastScheduledEvent->completed_datetime : DataAccess::getDataAccess()->getNow();
				}
				else
				{
					$sDateToOffset = $oAccount->getCollectionsStartDate();
				}

				$iDateToOffset 				= strtotime($sDateToOffset);
				$iNextEventDate 			= max (strtotime("+$oEvent->day_offset day", $iDateToOffset), time());
				$sNextEventDate 			= date ("Y-m-d", $iNextEventDate);
				$bOverdueOnNextEventDate	= $oScenario->evaluateThresholdCriterion($oAccount->getOverDueCollectableAmount($sNextEventDate),$oAccount->getOverdueBalance($sNextEventDate));

				if ($bOverdueOnNextEventDate)
				{
					$iInvocationId	= $oEvent->getInvocationId();
					$sEventName		= $oEvent->getEventName();
					$iEventId		= $oEvent->id;
					$bIsExit		= FALSE;

				}
			}
		}
		else if ($aPreviousEvent['invocation'] === COLLECTION_EVENT_INVOCATION_AUTOMATIC )
		{
			$oPreviousEvent = $aPreviousEvent['event_object'];
			if ($oPreviousEvent instanceof Logic_Collection_Event_ExitCollections)
			{
				$iOffset = $oScenario->day_offset;
				$iToday = strtotime("+$iOffset day", time());
				$sToday = date ("Y-m-d", $iToday);
				$bOverdue	= $oScenario->evaluateThresholdCriterion($oAccount->getOverDueCollectableAmount($sToday),$oAccount->getOverdueBalance($sToday));
				if ($bOverdue)
				{
					//get the correct day offset by determining the due date of what will be the source collectable after exit collections
					$oCollectable = $oAccount->getOldestOverDueCollectableRelativeToDate($sToday);
					$iOverDueDate = strtotime("+1 day", strtotime($oCollectable->due_date));
					$iStartDate = strtotime("-$iOffset day", $iOverDueDate);
					$sStartDate = date ("Y-m-d", $iStartDate);
					$iOffset = Flex_Date::difference( $sStartDate,  Data_Source_Time::currentDate(), 'd');
					$oEvent = $oScenario->getInitialScenarioEvent($iOffset, FALSE);
				}
				if ($oEvent === NULL)
					return FALSE;

			}
			else
			{
				$oEvent = $oPreviousEvent->getNext();
				if ($oEvent === NULL || $oEvent->day_offset > 0)
					return FALSE;
			}

			$iInvocationId	= $oEvent->getInvocationId();
			$sEventName		= $oEvent->getEventName();
			$iEventId		= $oEvent->id;
			$bIsExit		= FALSE;
			$sNextEventDate	= $aPreviousEvent['event_date'];

		}

		if ($iInvocationId !== NULL && $sEventName!== NULL && $iEventId !== NULL && $sNextEventDate !== NULL && $bIsExit !== NULL)
		{
			return array('invocation'=>$iInvocationId, 'event_name'=>$sEventName, 'event_id'=>$iEventId, 'is_exit' => $bIsExit, 'event_date'=>$sNextEventDate, 'event_object'=>$oEvent );
			
		}

		return FALSE;

	}
	
	public function getTodaysEventsForAccount($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$sToday			= date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
			$sStartToday	= "{$sToday} 00:00:00";
			$sEndToday		= "{$sToday} 23:59:59";
			
			$oQuery = new Query();
			$sQuery	= "	SELECT		aceh.id 	AS account_collection_event_history_id, 
									ce.name 	AS collection_event_name, 
									cet.name 	AS collection_event_type_name, 
									(
										CASE
											WHEN	ceti.enforced_collection_event_invocation_id IS NOT NULL	THEN	ceti.enforced_collection_event_invocation_id
											WHEN	cet.collection_event_invocation_id IS NOT NULL				THEN	cet.collection_event_invocation_id
											WHEN	csce.collection_event_invocation_id IS NOT NULL				THEN	csce.collection_event_invocation_id
											WHEN	ce.collection_event_invocation_id IS NOT NULL				THEN	ce.collection_event_invocation_id
											ELSE	NULL
										END
									) AS collection_event_invocation_id,
									aces.id		AS account_collection_event_status_id, 
									aces.name 	AS account_collection_event_status_name
						FROM		account_collection_event_history aceh
                        JOIN 		collection_event ce ON (aceh.collection_event_id = ce.id)
                        JOIN 		collection_event_type cet ON (cet.id = ce.collection_event_type_id)
						JOIN 		collection_event_type_implementation ceti ON (ceti.id = cet.collection_event_type_implementation_id)
                        JOIN 		account_collection_event_status aces ON (aceh.account_collection_event_status_id = aces.id)
						LEFT JOIN 	collection_scenario_collection_event csce ON (aceh.collection_scenario_collection_event_id = csce.id)
                        LEFT JOIN 	collection_scenario cs ON (cs.id = csce.collection_scenario_id)
						WHERE		aceh.account_id = {$iAccountId}
						AND			(
										(aceh.scheduled_datetime BETWEEN '{$sStartToday}' AND '{$sEndToday}') 
										OR 
										(aceh.completed_datetime > '{$sStartToday}') 
										OR
										(aceh.completed_datetime IS NULL)
									);";
								
			$mResult = $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				throw new Exception_Database("Failed to get todays events for account {$iAccountId}. ".$oQuery->Error());
			}
			
			$aEvents = array();
			while ($aRow = $mResult->fetch_assoc())
			{
				if (!isset($aEvents[$aRow['account_collection_event_status_id']]))
				{
					$aEvents[$aRow['account_collection_event_status_id']] = array();
				}
				$aEvents[$aRow['account_collection_event_status_id']][] = $aRow;
			}
			
			Log::getLog()->log("Query: $sQuery");
			
			return array('bSuccess' => true, 'aEvents' => $aEvents);
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
	
	private static function _createAccountEventSummaryItems($aRow, $sType, &$aUnsortedEvents)
	{
		$aItemDates = array();
		switch ($sType)
		{
			case 'collection_promise_instalment':
				// One item for the due date
				$aItemDates['due_date'] = $aRow['due_date'];
				break;
			case 'account_collection_event_history':
				switch ($aRow['account_collection_event_status_id'])
				{
					case ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED:
						$aItemDates['scheduled_datetime'] = date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
						break;
					case ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED:
						$aItemDates['completed_datetime'] = $aRow['completed_datetime'];
							
						// Get the events invocation
						$oLogicEventInstance 					= new Logic_Collection_Event_Instance($aRow['id']);
						$aRow['collection_event_invocation_id']	= $oLogicEventInstance->getInvocationId();
						break;
				}
				
				break;
			case 'collection_suspension':
				// One item for the start_datetime
				$aItemDates['start_datetime'] = $aRow['start_datetime'];
				
				// One item for either effective or proposed datetime (use effective if set) 
				if ($aRow['effective_end_datetime'] !== null)
				{
					$aItemDates['effective_end_datetime'] = $aRow['effective_end_datetime'];
				}
				else
				{
					$aItemDates['proposed_end_datetime'] = $aRow['proposed_end_datetime'];
				}
				break;
		}
		
		foreach ($aItemDates as $sSubType => $sDate)
		{
			$sDate = date('Y-m-d', strtotime($sDate));
			if (!isset($aUnsortedEvents[$sDate]))
			{
				$aUnsortedEvents[$sDate] = array();
			}
			
			if (!isset($aUnsortedEvents[$sDate][$sType]))
			{
				$aUnsortedEvents[$sDate][$sType] = array();
			}
			
			$aRowCopy 							= $aRow;
			$aRowCopy['sub_type']	 			= $sSubType;
			$aUnsortedEvents[$sDate][$sType][]	= $aRowCopy;
		}
	} 
}

class JSON_Handler_Collections_Exception extends Exception
{
	// No changes
}

?>