<?php

class JSON_Handler_Collection_Promise extends JSON_Handler implements JSON_Handler_Loggable
{
	protected	$_iTimestamp;

	public function __construct()
	{
		$this->_iTimestamp	= time();
		$this->_sDatetime	= date('Y-m-d H:i:s', $this->_iTimestamp);
		$this->_sDate		= date('Y-m-d', $this->_iTimestamp);
		$this->_iDate		= strtotime(date('Y-m-d', $this->_iTimestamp));
	}

	public function getDetailsForAccount($iAccountId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			if (!($oAccount = Account::getForId($iAccountId, true))) {
				throw new Exception();
			}
			$oCustomerGroup	= Customer_Group::getForId($oAccount->CustomerGroup);
			$oBillingType	= Billing_Type::getForId($oAccount->BillingType);
			$oPaymentMethod	= Payment_Method::getForId($oBillingType->payment_method_id);

			// Collection Promise Reasons
			$aCollectionPromiseReasons		= Collection_Promise_Reason::getActive();
			foreach ($aCollectionPromiseReasons as &$oCollectionPromiseReason) {
				$oCollectionPromiseReason	= $oCollectionPromiseReason->toStdClass();
			}
			unset($oCollectionPromiseReason);

			// Outstanding Invoices
			$aInvoicesORM			= Invoice::getForAccount($oAccount->Id);
			$aOutstandingInvoices	= array();
			foreach ($aInvoicesORM as $oInvoiceORM) {
				// We want to grab all related Collectables (including Promised) in case we with to replace the existing Promise
				$aCollectables	= Collectable::getForInvoice($oInvoiceORM, true);
				$oInvoiceRun	= Invoice_Run::getForId($oInvoiceORM->invoice_run_id);

				// Collectables
				$fTotalAmount	= 0;
				$fTotalBalance	= 0;
				foreach ($aCollectables as $oCollectable) {
					$fTotalAmount	= Rate::floorToPrecision($fTotalAmount + (float)$oCollectable->amount);
					$fTotalBalance	= Rate::floorToPrecision($fTotalBalance + (float)$oCollectable->balance);
				}

				if ($fTotalBalance > 0) {
					// Invoice Result
					$oInvoice								= new stdClass();
					$oInvoice->id							= $oInvoiceORM->Id;
					$oInvoice->invoice_date					= $oInvoiceORM->CreatedOn;
					$oInvoice->due_date						= $oInvoiceORM->DueOn;
					$oInvoice->grand_total					= $oInvoiceORM->Total + $oInvoiceORM->Tax;
					$oInvoice->balance						= $fTotalBalance;
					$oInvoice->invoice_run_id				= $oInvoiceORM->invoice_run_id;
					$oInvoice->invoice_run_type_id			= $oInvoiceRun->invoice_run_type_id;
					$oInvoice->invoice_run_type_name		= Constant_Group::getConstantGroup('invoice_run_type')->getConstantName($oInvoiceRun->invoice_run_type_id);
					$oInvoice->invoice_run_type_constant	= Constant_Group::getConstantGroup('invoice_run_type')->getConstantAlias($oInvoiceRun->invoice_run_type_id);

					$aOutstandingInvoices[$oInvoice->id]	= $oInvoice;
				}
			}
			$aOutstandingInvoices	= array_reverse($aOutstandingInvoices, true);

			// Existing Promise
			if ($oExistingPromiseORM = $oAccount->getActivePromise()) {
				$oExistingPromise				= $oExistingPromiseORM->toStdClass();
				$oExistingPromise->aInstalments	= array();
				$aExistingPromiseInstalments	= $oExistingPromiseORM->getInstalments();
				foreach ($aExistingPromiseInstalments as $oExistingPromiseInstalmentORM) {
					$oExistingPromise->aInstalments[]	= $oExistingPromiseInstalmentORM->toStdClass();
				}
				
				$oExistingPromise->collection_promise_reason_name	= $oExistingPromiseORM->getReason()->name;
			}

			// Existing Suspension
			if ($oExistingSuspensionORM = $oAccount->getActiveSuspension()) {
				$oExistingSuspension	= $oExistingSuspensionORM->toStdClass();
				
				$oExistingSuspension->collection_suspension_reason_system	= $oExistingSuspensionORM->getReason()->system_name;
				$oExistingSuspension->collection_suspension_reason_name		= $oExistingSuspensionORM->getReason()->name;
			}

			// Permissions
			$aPermissions	= Collection_Permissions_Config::getOptimalConfigValuesForEmployee(Flex::getUserId());
			$aPermissions['suspension_can_replace']	= $this->_getSuspensionCanReplace();

			// Data
			$aData	= array(
				'account_id'					=> $oAccount->Id,
				'account_name'					=> $oAccount->BusinessName,
				'customer_group_id'				=> $oCustomerGroup->Id,
				'customer_group_name'			=> $oCustomerGroup->internal_name,
				'payment_method_id'				=> $oPaymentMethod->id,
				'payment_method_constant'		=> $oPaymentMethod->const_name,
				'billing_type_system_name'		=> $oBillingType->system_name,
				'permissions'					=> $aPermissions,
				'collection_promise_reason'		=> (object)$aCollectionPromiseReasons,	// Cast to object to force non-Array serialisation
				'outstanding_invoices'			=> (object)$aOutstandingInvoices,
				'existing_promise'				=> $oExistingPromiseORM ? $oExistingPromise : null,
				'existing_suspension'			=> $oExistingSuspensionORM ? $oExistingSuspension : null
			);

			return	array(
						'bSuccess'		=> true,
						'oData'			=> $aData
					);
		} catch (Exception $oException) {
			$sMessage	= $bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}

	public function cancelPromiseForAccount($iAccountId, $bChangeScenario=false) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false) {
				throw new Exception_Database("Unable to start a Transaction");
			}
			
			try {
				// Cancel the existing promise to pay
				$oExistingPromise = Collection_Promise::getCurrentForAccountId($iAccountId);
				if ($oExistingPromise === null) {
					throw new Exception("Failed to cancel Promise to Pay for Account {$iAccountId}. There is no active promise.");
				}
				
				// Complete the promise
				$oLogicPromise = new Logic_Collection_Promise($oExistingPromise);
				$oLogicPromise->complete(COLLECTION_PROMISE_COMPLETION_CANCELLED);
				
				// Change scenario if necessary
				if ($bChangeScenario) {
					try {
						$oLogicAccount 				= $oLogicPromise->getAccount();
			            $iBrokenPromiseScenarioId	= $oLogicPromise->getScenarioId();
			            $oLogicAccount->setCurrentScenario($iBrokenPromiseScenarioId, false);
					} catch (Logic_Collection_Exception $oEx) {
						// Failed, most likely non configured broken promise scenario, pass out the error
						throw new JSON_Handler_Collection_Promise_Exception($oEx->getMessage());
					}
				}
			} catch (Exception $oEx) {
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception_Database("Unable to rollback db Transaction");
				}
				throw $oEx;
			}
			
			if ($oDataAccess->TransactionCommit() === false) {
				throw new Exception_Database("Unable to commit db Transaction");
			}
			
			return array('bSuccess' => true);
		} catch (JSON_Handler_Collection_Promise_Exception $oEx) {
			// Custom exception
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oEx->getMessage()
					);
		} catch (Exception $oEx) {
			// Standard exception
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}

	public function save($oData) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		try {
			// VALIDATE & SANITISE
			//----------------------------------------------------------------//
			Log::getLog()->log('Validate & Sanitise');
			$oData	= $this->_saveValidateAndSanitise($oData);

			// PERMISSIONS
			//----------------------------------------------------------------//
			Log::getLog()->log('Check Permissions');
			$this->_saveCheckPermissions($oData);

			// SAVE
			//----------------------------------------------------------------//
			Log::getLog()->log('Starting Transaction');
			if (DataAccess::getDataAccess()->TransactionStart() === false) {
				throw new Exception_Database("Unable to start a Transaction");
			}

			try {
				$oAccount					= Account::getForId($oData->iAccountId);
				$oCollectionPromiseReason	= Collection_Promise_Reason::getForId($oData->iCollectionPromiseReasonId);

				// Existing Promise
				if ($oActivePromise = $oAccount->getActivePromise()) {
					// Need to discontinue it
					Log::getLog()->log("Discontinuing Active Promise #{$oActivePromise->id}");
					$oActivePromise->completed_datetime					= $this->_sDatetime;
					$oActivePromise->collection_promise_completion_id	= COLLECTION_PROMISE_COMPLETION_CANCELLED;
					$oActivePromise->completed_employee_id				= Flex::getUserId();
					$oActivePromise->save();
					Log::getLog()->log(print_r($oActivePromise->toStdClass(), true));
				}

				// Existing Suspension
				if ($oActiveSuspension = $oAccount->getActiveSuspension()) {
					$oCollectionSuspensionEndReason	= Collection_Suspension_End_Reason::getForSystemName('CANCELLED');
					if ($oData->oExistingSuspension && $oData->oExistingSuspension->collection_suspension_end_reason_id) {
						$oCollectionSuspensionEndReason	= Collection_Suspension_End_Reason::getForId($oData->oExistingSuspension->collection_suspension_end_reason_id);
					}

					// Need to discontinue it
					Log::getLog()->log("Discontinuing Active Suspension #{$oActiveSuspension->id}");
					
					if ($oActiveSuspension->getReason()->system_name == 'TIO_COMPLAINT') {
						// TIO Complaint, end it
						Log::getLog()->log("Suspension is a TIO Complaint");
						$oTIOComplaint = Account_TIO_Complaint::getForCollectionSuspensionId($oActiveSuspension->id);
						if ($oTIOComplaint === null) {
							throw new Exception("Unable to find account_tio_complaint for suspension {$oActiveSuspension->id}");
						}
						$oTIOComplaint->end($oCollectionSuspensionEndReason->id);
						Log::getLog()->log("...complaint ended");
					} else {
						// Regular suspension, end it
						Log::getLog()->log("Regular suspension");
						$oActiveSuspension->end($oCollectionSuspensionEndReason->id);
						Log::getLog()->log("...suspension ended");
					}
					Log::getLog()->log(print_r($oActiveSuspension->toStdClass(), true));
				}

				// Promise
				Log::getLog()->log("Creating Promise for Account {$oAccount->Id} with reason: {$oCollectionPromiseReason->name}");
				$oPromise	= new Collection_Promise();
				
				$oPromise->account_id					= $oAccount->Id;
				$oPromise->collection_promise_reason_id	= $oCollectionPromiseReason->id;
				$oPromise->created_datetime				= $this->_sDatetime;
				$oPromise->created_employee_id			= Flex::getUserId();
				$oPromise->use_direct_debit				= (int)$oData->bUseDirectDebit;

				$oPromise->save();

				Log::getLog()->log(print_r($oPromise->toStdClass(), true));

				// Invoice
				Log::getLog()->log("Creating Transfers for ".count($oData->aInvoices).' invoices');
				foreach ($oData->aInvoices as $oInvoice) {
					Log::getLog()->log("Processing Invoice #{$oInvoice->iInvoiceId} with {$oInvoice->fPromisedAmount} Promised");
					$aInvoiceCollectables	= Invoice::getForId($oInvoice->iInvoiceId)->getCollectables(true);

					if (!($oInvoice->fPromisedAmount > 0)) {
						Log::getLog()->log("Skipping: Nothing promised; Nothing to Transfer");
						continue;
					}

					$fPromisedRemaining	= $oInvoice->fPromisedAmount;

					Log::getLog()->log("Creating Transfers for ".count($aInvoiceCollectables)." Collectables");
					foreach ($aInvoiceCollectables as $iInvoiceCollectableId=>$oInvoiceCollectable) {
						Log::getLog()->log("Processing Collectable #{$iInvoiceCollectableId} worth {$oInvoiceCollectable->amount} with {$oInvoiceCollectable->balance} remaining");
						if ($oInvoice->fPromisedAmount <= 0) {
							// Only continue if there is more value to transfer
							Log::getLog()->log("Breaking out: Promise value fulfilled");
							break;
						}

						$fTransferValue		= Rate::roundToRatingStandard(max(0, min($oInvoice->fPromisedAmount, $oInvoiceCollectable->balance)), 2);
						$fPromisedRemaining	= Rate::roundToRatingStandard($fPromisedRemaining - $fTransferValue);

						if ($fTransferValue <= 0.01) {
							Log::getLog()->log("Skipping: No value to Transfer");
							continue;
						}


						// Promise Collectable
						$oCollectable	= new Collectable();

						$oCollectable->account_id				= $oInvoiceCollectable->account_id;
						$oCollectable->amount					= 0;	// Temporarily
						$oCollectable->balance					= 0;	// Temporarily
						$oCollectable->created_datetime			= date('Y-m-d H:i:s', $this->_iTimestamp);
						$oCollectable->due_date					= $oInvoiceCollectable->due_date;
						$oCollectable->collection_promise_id	= $oPromise->id;
						$oCollectable->invoice_id				= $oInvoiceCollectable->invoice_id;

						$oCollectable->save();

						// Transfer
						$oTransfer	= Collectable_Transfer_Value::createForCollectables($oInvoiceCollectable, $oCollectable, $fTransferValue, Collectable_Transfer_Value::TRANSFER_MODE_BALANCE_ONLY);
						$oTransfer->created_datetime	= date('Y-m-d H:i:s', $this->_iTimestamp);
						$oTransfer->save();

						// Save the Collectables now they've been updated
						$oCollectable->save();
						$oInvoiceCollectable->save();

						Log::getLog()->log("Transferred {$fTransferValue} from Collectable #{$iInvoiceCollectableId} to Collectable #{$oCollectable->id}");
						Log::getLog()->log(print_r($oInvoiceCollectable->toStdClass(), true));
						Log::getLog()->log(print_r($oTransfer->toStdClass(), true));
						Log::getLog()->log(print_r($oCollectable->toStdClass(), true));
					}
				}

				// Instalments
				Log::getLog()->log("Scheduling ".count($oData->aInstalments).' instalments');
				foreach ($oData->aInstalments as $oInstalment) {
					$oPromiseInstalment	= new Collection_Promise_Instalment();

					$oPromiseInstalment->collection_promise_id	= $oPromise->id;
					$oPromiseInstalment->due_date				= $oInstalment->sDueDate;
					$oPromiseInstalment->amount					= $oInstalment->fAmount;
					$oPromiseInstalment->created_datetime		= date('Y-m-d H:i:s', $this->_iTimestamp);
					$oPromiseInstalment->created_employee_id	= Flex::getUserId();

					$oPromiseInstalment->save();

					Log::getLog()->log(print_r($oPromiseInstalment->toStdClass(), true));
				}

				Log::getLog()->log("Promise Created");
				//throw new Exception("Test Mode");

				// Re-distribute the Account
				$oLogicAccount	= Logic_Account::getInstance($oAccount);
				$oLogicAccount->redistributeBalances();

			} catch (Exception $oException) {
				// Rollback & re-Throw
				if (DataAccess::getDataAccess()->TransactionRollback() === false) {
					throw new Exception_Database("Unable to rollback active Transaction");
				}
				throw $oException;
			}

			// Commit
			if (DataAccess::getDataAccess()->TransactionCommit() === false) {
				throw new Exception_Database("Unable to commit active Transaction");
			}

			// Success!
			return 	array(
						'bSuccess'	=> true
					);

		} catch (Exception $oException) {
			$sMessage	= $bUserIsGod || $oException instanceof Exception_Validation ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}

	protected function _saveValidateAndSanitise($oData) {
		// $oData->iAccountId
		$oAccount	= Account::getForId($oData->iAccountId);

		// $oData->bUseDirectDebit
		if ($oData->bUseDirectDebit && Billing_Type::getForId($oAccount->BillingType)->payment_method_id !== PAYMENT_METHOD_DIRECT_DEBIT) {
			// Account isn't on Direct Debit
			throw new Exception_Validation("{$oData->iAccountId} is not on Direct Debit");
		}

		// $oData->iCollectionPromiseReasonId
		$oCollectionPromiseReason	= Collection_Promise_Reason::getForId($oData->iCollectionPromiseReasonId);

		// $oData->aInvoices
		$fTotalBalanceAvailable	= 0;
		$fTotalPromised			= 0;
		foreach ($oData->aInvoices as $oInvoice) {
			// $oData->aInvoices[]->iInvoiceId
			$oInvoice->oInvoice	= Invoice::getForId($oInvoice->iInvoiceId);

			// $oData->aInvoices[]->fPromisedAmount
			$oInvoice->fPromisedAmount	= Rate::roundToRatingStandard($oInvoice->fPromisedAmount, 2);
			$oInvoice->aCollectables	= $oInvoice->oInvoice->getCollectables(true);
			$fTotalAmount	= 0;
			$fTotalBalance	= 0;
			foreach ($oInvoice->aCollectables as $oCollectable) {
				$fTotalAmount	= Rate::roundToRatingStandard($fTotalAmount + (float)$oCollectable->amount, 2);
				$fTotalBalance	= Rate::roundToRatingStandard($fTotalAmount + (float)$oCollectable->balance, 2);
			}
			if ($fTotalBalance < $oInvoice->fPromisedAmount) {
				throw new Exception_Validation("Invoice {$oInvoice->iInvoiceId} has been promised {$oInvoice->fPromisedAmount} when there is only {$fTotalBalance} available");
			}

			$fTotalBalanceAvailable	= Rate::roundToRatingStandard($fTotalBalanceAvailable + max(0, $fTotalBalance));
			$fTotalPromised			= Rate::roundToRatingStandard($fTotalPromised + $oInvoice->fPromisedAmount);
		}

		// $oData->aInstalments
		$fTotalInstalmentAmount		= 0;
		$iEarliestInstalmentDate	= strtotime(date('Y-m-d', $this->_iTimestamp));
		$iLatestInstalmentDate		= null;
		foreach ($oData->aInstalments as $oInstalment) {
			// $oData->aInstalments[]->sDueDate
			$iDueDatetime	= strtotime($oInstalment->sDueDate);
			if ($iDueDatetime === false) {
				throw new Exception_Validation("'{$oInstalment->sDueDate}' is not a valid Instalment Due Date");
			}
			$iDueDate	= strtotime(date('Y-m-d', $iDueDatetime));
			if ($iDueDate <= $iEarliestInstalmentDate) {
				throw new Exception_Validation("'{$oInstalment->sDueDate}' is earlier than tomorrow");
			}
			if ($iLatestInstalmentDate !== null && $iDueDate <= $iLatestInstalmentDate) {
				throw new Exception_Validation("'{$oInstalment->sDueDate}' is earlier than or the same as a previous instalment");
			}
			$oInstalment->sDueDate	= date('Y-m-d', $iDueDate);

			$oInstalment->fAmount	= Rate::roundToRatingStandard($oInstalment->fAmount, 2);
			if ($oInstalment->fAmount <= 0) {
				throw new Exception_Validation("Instalment from {$oInstalment->sDueDate} does not have a promised amount");
			}

			$iLatestInstalmentDate	= $iDueDate;
			$fTotalInstalmentAmount	= Rate::roundToRatingStandard($fTotalInstalmentAmount + $oInstalment->fAmount, 2);
		}

		// $oData->oExistingPromise
		$oExistingPromise	= $oAccount->getActivePromise();
		if ($oExistingPromise && !$oData->oExistingPromise) {
			throw new Exception_Validation("There is already an active Promise to Pay.  Please try recreating your new Promise to Pay.");
		}
		if ($oExistingPromise->id !== $oData->oExistingPromise->id) {
			throw new Exception_Validation("The Promise to Pay you agreed to replace is no longer the current arrangement.  Please try recreating your new Promise to Pay.");
		}

		// $oData->oExistingSuspension
		$oExistingSuspension	= $oAccount->getActiveSuspension();
		if ($oExistingSuspension && !$oData->oExistingSuspension) {
			throw new Exception_Validation("There is already an active Suspension.  Please try recreating your new Promise to Pay.");
		}
		if ($oExistingSuspension->id !== $oData->oExistingSuspension->id) {
			throw new Exception_Validation("The Suspension you agreed to replace is no longer the current arrangement.  Please try recreating your new Promise to Pay.");
		}
		if (isset($oData->oExistingSuspension->collection_suspension_end_reason_id)) {
			$oData->oExistingSuspension->collection_suspension_end_reason_id	= Collection_Suspension_End_Reason::getForId($oData->oExistingSuspension->collection_suspension_end_reason_id)->id;
		}

		// Data Integrity
		if ($fTotalInstalmentAmount !== $fTotalPromised) {
			throw new Exception_Validation("Total Instalment Amount ({$fTotalInstalmentAmount}) is different to the Total Promised ({$fTotalPromised})");
		}
		if ($fTotalInstalmentAmount <= 0) {
			throw new Exception_Validation("No Instalments have been scheduled");
		}
		if ($fTotalPromised <= 0) {
			throw new Exception_Validation("No Invoices have been promised");
		}

		return $oData;
	}

	protected function _saveCheckPermissions($oData) {
		$aUserPermissions	= Collection_Permissions_Config::getOptimalConfigValuesForEmployee(Flex::getUserId());

		foreach ($aUserPermissions as $sName=>$mPermission) {
			if ($mPermission === null) {
				throw new Exception("You do not have the {$sName} permission");
			}
		}

		$oAccount	= Account::getForId($oData->iAccountId);

		// promise_start_delay_maximum_days
		$oFirstInstalment	= reset($oData->aInstalments);
		if ($oFirstInstalment->due_date > strtotime("+ {$aUserPermissions['promise_start_delay_maximum_days']} days", $this->_iDate)) {
			throw new Exception_Validation("First instalment ({$oFirstInstalment->due_date}) is more than {$aUserPermissions['promise_start_delay_maximum_days']} days after today ({$this->_sDate})");
		}
		
		// promise_maximum_days_between_due_and_end
		$fTotalPromised				= 0;
		$oLastInstalment			= end($oData->aInstalments);
		$iEarliestInvoiceDueDate	= null;
		foreach ($oData->aInvoices as $oInvoice) {
			if ($oInvoice->fPromisedAmount > 0) {
				$iEarliestInvoiceDueDate	= strtotime($oInvoice->sDueDate);
				$fTotalPromised				= Rate::roundToRatingStandard($fTotalPromised + $oInvoice->fPromisedAmount, 2);
			}
		}
		if ($oLastInstalment->due_date > strtotime("+ {$aUserPermissions['promise_maximum_days_between_due_and_end']} days", $iEarliestInvoiceDueDate)) {
			throw new Exception_Validation("Last instalment ({$oLastInstalment->due_date}) is more than {$aUserPermissions['promise_start_delay_maximum_days']} days after the earliest promised Invoice (".date('Y-m-d', $iEarliestInvoiceDueDate).")");
		}

		// promise_instalment_maximum_interval_days
		$oPreviousInstalment	= null;
		foreach ($oData->aInstalments as $oInstalment) {
			if ($oPreviousInstalment && Flex_Date::difference($oPreviousInstalment->sDueDate, $oInstalment->sDueDate, 'd') > $aUserPermissions['promise_instalment_maximum_interval_days']) {
				throw new Exception_Validation("Instalment from {$oInstalment->sDueDate} is more than {$aUserPermissions['promise_instalment_maximum_interval_days']} days after {$oPreviousInstalment->sDueDate}");
			}
			$oPreviousInstalment	= $oInstalment;
		}

		// promise_instalment_minimum_promised_percentage
		$fPromiseInstalmentMinimumPromisedPercentage	= (float)$aUserPermissions['promise_instalment_minimum_promised_percentage'] * 100;
		$oLastInstalment	= end($oData->aInstalments);
		foreach ($oData->aInstalments as $oInstalment) {
			$fPercentageOfTotalPromised	= ($oInstalment->fAmount / $fTotalPromised) * 100;
			if ($oLastInstalment !== $oInstalment && $fPercentageOfTotalPromised < $fPromiseInstalmentMinimumPromisedPercentage) {
				throw new Exception_Validation("Instalment from {$oInstalment->sDueDate} (\${$oInstalment->fAmount} : {$fPercentageOfTotalPromised}%) is less than {$fPromiseInstalmentMinimumPromisedPercentage}% of Total Promised ({$fTotalPromised})");
			}
		}

		// promise_can_replace
		if ($oAccount->getActivePromise() !== null && !$aUserPermissions['promise_can_replace']) {
			throw new Exception_Validation("You do not have the ability to replace existing Promise to Pay arrangements");
		}

		// suspension_can_replace
		if ($oAccount->getActiveSuspension() !== null && !$this->_getSuspensionCanReplace()) {
			throw new Exception_Validation("You do not have the ability to replace existing Suspensions with Promise to Pay arrangements");
		}

		// promise_create_maximum_severity_level
		$oSeverity	= Collection_Severity::getForId($oAccount->collection_severity_id);
		if ($oSeverity->severity_level > $aUserPermissions['promise_create_maximum_severity_level']) {
			throw new Exception_Validation("Account's Severity Level ({$oSeverity->name}) is greater than the highest allowed for creating Promises");
		}

		// promise_amount_maximum
		if ($fTotalPromised > (float)$aUserPermissions['promise_amount_maximum']) {
			throw new Exception_Validation("Total Promised ({$fTotalPromised}) exceeds maximum of {$aUserPermissions['promise_amount_maximum']}");
		}
	}
	
	public function getExtendedInstalmentDetailsForId($iPromiseInstalmentId) {
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try {
			// Instalment details
			$oInstalment					= Collection_Promise_Instalment::getForId($iPromiseInstalmentId);
			$oStdClass						= $oInstalment->toStdClass();
			$oStdClass->created_employee	= Employee::getForId($oInstalment->created_employee_id)->toStdClass();
			
			// Promise details
			$oPromise								= Collection_Promise::getForId($oInstalment->collection_promise_id)->toStdClass();
			$oPromise->account						= Account::getForId($oPromise->account_id)->toArray();
			$oPromise->collection_promise_reason	= Collection_Promise_Reason::getForId($oPromise->collection_promise_reason_id)->toStdClass();
			
			// Add promise to instalment
			$oStdClass->collection_promise = $oPromise;
			
			return	array(
						'bSuccess'				=> true,
						'oPromiseInstalment'	=> $oStdClass
					);
		} catch (Exception $oEx) {
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}

	protected function _getSuspensionCanReplace() {
		// This is the currently accepted business rule.  There are no restrictions on replacing a Suspension with a Promise, as a Promise is always preferred to Suspension
		return true;
	}
}

class JSON_Handler_Collection_Promise_Exception extends Exception
{
	// No changes
}

?>