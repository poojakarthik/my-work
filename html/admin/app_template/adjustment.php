<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// adjustment.php
//----------------------------------------------------------------------------//
/**
 * adjustment
 *
 * contains all ApplicationTemplate extended classes relating to Adjustment functionality
 *
 * contains all ApplicationTemplate extended classes relating to Adjustment functionality
 *
 * @file		adjustment.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateAdjustment
//----------------------------------------------------------------------------//
/**
 * AppTemplateAdjustment
 *
 * The AppTemplateAdjustment class
 *
 * The AppTemplateAdjustment class.  This incorporates all logic for all pages
 * relating to Adjustments
 *
 *
 * @package	ui_app
 * @class	AppTemplateAdjustment
 * @extends	ApplicationTemplate
 */
class AppTemplateAdjustment extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Adjustment popup window (Used to request an adjustment)
	 * 
	 * Performs the logic for the Add Adjustment popup window (Used to request an adjustment)
	 * Note that regardless of whether or not the charge is a credit or debit adjustment, and regardless
	 * of the user's permission level, no manually requested adjustments (using this function) are automatically approved.
	 *
	 * @return		void
	 * @method
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasProperAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);

		//$bolCanCreateCreditAdjustments = ($bolUserHasProperAdminPerm || $bolHasCreditManagementPerm);
		$bolCanCreateCreditAdjustments = TRUE;

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}
		
		// Adjustments can not be added if the account is pending activation
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "The account is pending activation.  Adjustments cannot be requested at this time.");
			return TRUE;
		}
		
		// Check if the adjustment relates to a particular service
		if (DBO()->Service->Id->Value)
		{
			// A service has been specified.  Load it, to check that it actually exists
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("Alert", "ERROR: The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}
			
			// It is assumed that this is the newest most record modelling this service for the account
			// that the service belongs to.  Check that the service is currently active
			$objService = ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->RecordType->Value);
			if ($objService->GetStatus() == SERVICE_PENDING)
			{
				Ajax()->AddCommand("Alert", "This service is pending activation.  Adjustments cannot be requested at this time.");
				return TRUE;
			}
			elseif (!$objService->IsCurrentlyActive())
			{
				Ajax()->AddCommand("Alert", "This service is not currently active on this account.  Adjustments can only be requested for active services.");
				return TRUE;
			}
		}

		// Load all charge types that aren't archived and aren't flagged as automatic_only
		DBL()->ChargeTypesAvailable->Archived = 0;
		DBL()->ChargeTypesAvailable->automatic_only = 0;
		
		// Only proper admins and credit management can create credit adjustments
		if (!$bolCanCreateCreditAdjustments)
		{
			// The user can only create debit adjustments
			DBL()->ChargeTypesAvailable->Nature = 'DR';
		}
		DBL()->ChargeTypesAvailable->SetTable("ChargeType");
		DBL()->ChargeTypesAvailable->OrderBy("Nature DESC, Description ASC, ChargeType ASC");
		DBL()->ChargeTypesAvailable->Load();
		
		if (DBL()->ChargeTypesAvailable->RecordCount() == 0)
		{
			Ajax()->AddCommand("Alert", "There are currently no adjustment types defined");
			return TRUE;
		}

		// load the last 6 invoices with the most recent being first (Committed Live, Interim and Final invoices only)
		$arrWhere = array("AccountId" => DBO()->Account->Id->Value);
		$strWhere = "	Account = <AccountId>
						AND invoice_run_id IN (	SELECT id 
												FROM InvoiceRun
												WHERE invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED ." 
												AND invoice_run_type_id IN (". INVOICE_RUN_TYPE_LIVE .", ". INVOICE_RUN_TYPE_INTERIM .", ".INVOICE_RUN_TYPE_FINAL .")
											)";
		DBL()->AccountInvoices->SetTable("Invoice");
		DBL()->AccountInvoices->Where->Set($strWhere, $arrWhere);
		DBL()->AccountInvoices->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->AccountInvoices->SetLimit(6);
		DBL()->AccountInvoices->Load();


		// check if an adjustment is being submitted
		if (SubmittedForm('AddAdjustment', 'Add Adjustment'))
		{
			// Load the relating Account and ChargeType records
			DBO()->ChargeType->Load();

			// Define all the required properties for the Charge record
			if ((!DBO()->Account->IsInvalid()) && (!DBO()->Charge->IsInvalid()) && (!DBO()->ChargeType->IsInvalid()))
			{
				// if the charge amount has a leading dollar sign then strip it off
				DBO()->Charge->Amount = ltrim(trim(DBO()->Charge->Amount->Value), '$');
				
				// Check that the charge amount is not negative
				if (floatval(DBO()->Charge->Amount->Value < 0))
				{
					Ajax()->AddCommand("Alert", "ERROR: The Adjustment cannot be a negative value");
					return TRUE;
				}
				
				// Remove GST from this amount
				DBO()->Charge->Amount = RemoveGST(DBO()->Charge->Amount->Value);
				
				// Account details
				DBO()->Charge->Account		= DBO()->Account->Id->Value;
				DBO()->Charge->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// Service details
				if (DBO()->Service->Id->Value)
				{
					DBO()->Charge->Service	= DBO()->Service->Id->Value;
				}
				
				// User's details
				$dboUser 					= GetAuthenticatedUserDBObject();
				DBO()->Charge->CreatedBy	= $dboUser->Id->Value;
				
				// Date the adjustment was created (the current date)
				$strCurrentDate = GetCurrentDateForMySQL();
				DBO()->Charge->CreatedOn	= $strCurrentDate;
				DBO()->Charge->ChargedOn	= $strCurrentDate;
				
				// Details regarding the type of charge
				DBO()->Charge->ChargeType	= DBO()->ChargeType->ChargeType->Value;
				DBO()->Charge->Description	= DBO()->ChargeType->Description->Value;
				DBO()->Charge->Nature		= DBO()->ChargeType->Nature->Value;
				
				DBO()->Charge->Notes		= trim(DBO()->Charge->Notes->Value);
				
				// Check if the user has permission to create a credit adjustment, if the adjustment is a credit
				if (DBO()->Charge->Nature->Value == 'CR' && !$bolCanCreateCreditAdjustments)
				{
					// The user does not have the required permissions to create a credit adjustment
					Ajax()->AddCommand("Alert", "ERROR: You do not have permission to request credit adjustments");
					return TRUE;
				}
				
				// if DBO()->Charge->Invoice->Value == 0 then set it to NULL;
				if (!DBO()->Charge->Invoice->Value)
				{
					DBO()->Charge->Invoice = NULL;
				}
				
				// Set the status to CHARGE_WAITING (no adjustments are automatically approved)
				DBO()->Charge->Status = CHARGE_WAITING;

				$arrData = DBO()->Charge->AsArray();

				// Save the adjustment to the charge table of the vixen database
				TransactionStart();
				$intChargeId = Framework()->AddCharge($arrData);

				if ($intChargeId === FALSE)
				{
					// The adjustment did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Requesting the adjustment failed, unexpectedly");
					return TRUE;
				}
				else
				{
					// The adjustment was successfully saved
					
					// Log the 'Adjustment Request' action
					try
					{
						$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];
						if (DBO()->Service->Id->Value)
						{
							// The recurring adjustment is being applied to a specific service
							$intAccountId = NULL;
							$intServiceId = DBO()->Service->Id->Value;
						}
						else
						{
							// The recurring adjustment is being applied to an account
							$intAccountId = DBO()->Account->Id->Value;
							$intServiceId = NULL;
						}
						
						$strNature				= (DBO()->Charge->Nature->Value == 'CR')? "Credit" : "Debit";
						$strAmount				= number_format(AddGST(DBO()->Charge->Amount->Value), 2, '.', '');
						$strChargeType			= DBO()->Charge->ChargeType->Value ." - ". DBO()->Charge->Description->Value;
						$strActionExtraDetails	= 	"Type: {$strChargeType} ({$strNature})\n".
													"Amount (Inc GST): \${$strAmount} {$strNature}";
						
						// Log the action
						Action::createAction('Adjustment Requested', $strActionExtraDetails, $intAccountId, $intServiceId, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
					}
					catch (Exception $e)
					{
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Requesting the adjustment failed, while trying to log the action.");
						return TRUE;
					}
					
					

					TransactionCommit();
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The request for adjustment has been successfully logged.");
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				Ajax()->RenderHtmlTemplate("AdjustmentAdd", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				Ajax()->AddCommand("Alert", "ERROR: Adjustment details are incorrect. Invalid fields are highlighted");
				return TRUE;
			}
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('adjustment_add');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// AddRecurring
	//------------------------------------------------------------------------//
	/**
	 * AddRecurring()
	 *
	 * Performs the logic for the Add Recurring Adjustment popup window
	 * 
	 * Performs the logic for the Add Recurring Adjustment popup window
	 *
	 * @return		void
	 * @method
	 *
	 */
	function AddRecurring()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}

		// Adjustments can not be added if the account is pending activation
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "The account is pending activation.  Adjustments cannot be requested at this time.");
			return TRUE;
		}

		// Check if the adjustment relates to a particular service
		if (DBO()->Service->Id->Value)
		{
			// A service has been specified.  Load it, to check that it actually exists
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("Alert", "The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}
			// It is assumed that this is the newest most record modelling this service for the account
			// that the service belongs to.  Check that the service is currently active
			$objService = ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->RecordType->Value);
			if ($objService->GetStatus() == SERVICE_PENDING)
			{
				Ajax()->AddCommand("Alert", "This service is pending activation.  Adjustments cannot be requested at this time.");
				return TRUE;
			}
			elseif (!$objService->IsCurrentlyActive())
			{
				Ajax()->AddCommand("Alert", "This service is not currently active on this account.  Adjustments can only be requested for active services.");
				return TRUE;
			}
			
		}

		// Load all charge types that aren't archived
		DBL()->ChargeTypesAvailable->Archived = 0;
		DBL()->ChargeTypesAvailable->SetTable("RecurringChargeType");
		DBL()->ChargeTypesAvailable->OrderBy("Nature DESC, Description ASC, ChargeType ASC");
		DBL()->ChargeTypesAvailable->Load();

		if (DBL()->ChargeTypesAvailable->RecordCount() == 0)
		{
			Ajax()->AddCommand("Alert", "There are currently no recurring adjustment types defined");
			return TRUE;
		}

		// check if an adjustment is being submitted
		if (SubmittedForm('AddRecurringAdjustment', 'Add Adjustment'))
		{
			// Load the relating Account and ChargeType records
			DBO()->RecurringChargeType->Load();

			// Define all the required properties for the Charge record
			if ((!DBO()->Account->IsInvalid()) && (!DBO()->RecurringCharge->IsInvalid()) && (!DBO()->RecurringChargeType->IsInvalid()))
			{
				// if the money values have leading dollar signs then strip it off
				DBO()->RecurringCharge->MinCharge = ltrim(trim(DBO()->RecurringCharge->MinCharge->Value), '$');
				DBO()->RecurringCharge->RecursionCharge = ltrim(trim(DBO()->RecurringCharge->RecursionCharge->Value), '$');
			
				// Remove GST from Minimum Charge and Recursion Charge
				DBO()->RecurringCharge->MinCharge = RemoveGST(DBO()->RecurringCharge->MinCharge->Value);
				DBO()->RecurringCharge->RecursionCharge = RemoveGST(DBO()->RecurringCharge->RecursionCharge->Value);

				$arrErrors = array();
				if (DBO()->RecurringCharge->MinCharge->Value <= 0)
				{
					$arrErrors[] = "Minimum Charge is invalid";
				}
				if (DBO()->RecurringCharge->RecursionCharge->Value <= 0)
				{
					$arrErrors[] = "Recurring Charge is invalid";
				}
				
				if (count($arrErrors))
				{
					// Errors have been found
					Ajax()->AddCommand("Alert", "ERROR: ". implode(", ", $arrErrors));
					return TRUE;
				}
				
				// Account details
				DBO()->RecurringCharge->Account			= DBO()->Account->Id->Value;
				DBO()->RecurringCharge->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// Service details
				if (DBO()->Service->Id->Value)
				{
					DBO()->RecurringCharge->Service		= DBO()->Service->Id->Value;
				}

				// User's details
				DBO()->RecurringCharge->CreatedBy		= AuthenticatedUser()->_arrUser['Id'];
				
				// Approved By
				DBO()->RecurringCharge->ApprovedBy		= NULL;
				
				$strCurrentDate						= GetCurrentISODate();
				$intNow								= strtotime(GetCurrentISODateTime());
				$intCurrentDay						= intval(date("d", $intNow));
				$intCurrentMonth					= intval(date("m", $intNow));
				$intCurrentYear						= intval(date("Y", $intNow));
				DBO()->RecurringCharge->CreatedOn	= $strCurrentDate;

				// I'm pretty sure I should just leave this even though the adjustment will be pending approval
				// StartedOn, is the date that the recurring charge should have started on 
				if ($intCurrentDay >= 29 && $intCurrentDay <= 31)
				{
					// The StartedOn date has to snap to either the 28th or the 1st of next month
					switch (intval(DBO()->RecurringCharge->SnapToDayOfMonth->Value))
					{
						case 1:
								// Set the start date to the 1st of next month
								DBO()->RecurringCharge->StartedOn = date("Y-m-d", mktime(0, 0, 0, $intCurrentMonth + 1, 1, $intCurrentYear));
								break;
							
						case 28:
						default:
								// Set the start date to the 28th of the current month
								DBO()->RecurringCharge->StartedOn = date("Y-m-d", mktime(0, 0, 0, $intCurrentMonth, 28, $intCurrentYear));
								break;
					}
					
				}
				else
				{
					DBO()->RecurringCharge->StartedOn = $strCurrentDate;
				}
				
				if (DBO()->RecurringCharge->in_advance->Value == TRUE)
				{
					// Charging in advance
					DBO()->RecurringCharge->LastChargedOn = NULL;
				}
				else
				{
					// Charging in arrears
					DBO()->RecurringCharge->LastChargedOn = DBO()->RecurringCharge->StartedOn->Value;
				}
				
				// Details regarding the type of charge
				DBO()->RecurringCharge->ChargeType			= DBO()->RecurringChargeType->ChargeType->Value;
				DBO()->RecurringCharge->Description			= DBO()->RecurringChargeType->Description->Value;
				DBO()->RecurringCharge->Nature				= DBO()->RecurringChargeType->Nature->Value;
				
				DBO()->RecurringCharge->RecurringFreqType	= DBO()->RecurringChargeType->RecurringFreqType->Value;
				DBO()->RecurringCharge->RecurringFreq		= DBO()->RecurringChargeType->RecurringFreq->Value;
				
				$intRecurringFreqType	= DBO()->RecurringChargeType->RecurringFreqType->Value;
				$intRecurringFreq		= DBO()->RecurringChargeType->RecurringFreq->Value;
				
				switch ($intRecurringFreqType)
				{
					case BILLING_FREQ_DAY:
						$strFreqType = "day";
						break;
						
					case BILLING_FREQ_MONTH:
						$strFreqType = "month";
						break;
						
					case BILLING_FREQ_HALF_MONTH:
						$strFreqType = "half-month";
						break; 
				}
				if ($intRecurringFreq == 1)
				{
					$strFreq = '';
					$strFreqTypePluraliserSuffix = '';
				}
				else
				{
					$strFreq = "$intRecurringFreq ";
					$strFreqTypePluraliserSuffix = 's';
				}
				
				$strIndividualChargePeriod = "{$strFreq}{$strFreqType}{$strFreqTypePluraliserSuffix}";
				$strInAdvanceInArrears = (DBO()->RecurringCharge->in_advance->Value == TRUE)? "advance" : "arrears";
				$strStartDateFormatted = date('d-m-Y', strtotime(DBO()->RecurringCharge->StartedOn->Value));
				
				// These have already been set				
				//DBO()->RecurringCharge->MinCharge
				//DBO()->RecurringCharge->RecursionCharge
				
				DBO()->RecurringCharge->CancellationFee		= DBO()->RecurringChargeType->CancellationFee->Value;
				DBO()->RecurringCharge->Continuable			= DBO()->RecurringChargeType->Continuable->Value;
				DBO()->RecurringCharge->PlanCharge			= DBO()->RecurringChargeType->PlanCharge->Value;
				DBO()->RecurringCharge->UniqueCharge		= DBO()->RecurringChargeType->UniqueCharge->Value;
				DBO()->RecurringCharge->TotalCharged		= 0;
				DBO()->RecurringCharge->TotalRecursions		= 0;
				
				// Set the status to awaiting approval
				DBO()->RecurringCharge->recurring_charge_status_id = Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL');

				$strMinCharge		= OutputMask()->MoneyValue(addGST(DBO()->RecurringCharge->MinCharge->Value), 2, TRUE);
				$strRecursionCharge	= OutputMask()->MoneyValue(addGST(DBO()->RecurringCharge->RecursionCharge->Value), 2, TRUE);

				$strNote = "Type: " . DBO()->RecurringCharge->ChargeType->FormattedValue() ." - ". DBO()->RecurringCharge->Description->FormattedValue() . "\n";
				$strNote .= "Nature: " . DBO()->RecurringCharge->Nature->FormattedValue() . "\n";
				$strNote .= "Minimum Charge (inc GST): {$strMinCharge}\n";
				$strNote .= "Recurring Charge (inc GST): {$strRecursionCharge} charged in {$strInAdvanceInArrears}, every {$strIndividualChargePeriod}, starting $strStartDateFormatted"; 

				TransactionStart();
				
				// Save the recurring adjustment to the charge table
				if (!DBO()->RecurringCharge->Save())
				{
					// The recurring adjustment did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Submitting the Recurring Adjustment Request failed, unexpectedly.");
					return TRUE;
				}
				else
				{
					// The recurring adjustment was successfully saved
					
					// Log the 'Recurring Adjustment Request' action
					try
					{
						$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];
						if (DBO()->Service->Id->Value)
						{
							// The recurring adjustment is being applied to a specific service
							$intAccountId = NULL;
							$intServiceId = DBO()->Service->Id->Value;
						}
						else
						{
							// The recurring adjustment is being applied to an account
							$intAccountId = DBO()->Account->Id->Value;
							$intServiceId = NULL;
						}
						
						// Log the action
						Action::createAction('Recurring Adjustment Requested', $strNote, $intAccountId, $intServiceId, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
						
						// If the RecurringChargeType doesn't require approval, then flag it as being approved
						if (!DBO()->RecurringChargeType->approval_required->Value)
						{
							// The Recurring Adjustment can be automatically approved
							$objRecurringCharge = Recurring_Charge::getForId(DBO()->RecurringCharge->Id->Value);
							$objRecurringCharge->setToApproved(Employee::SYSTEM_EMPLOYEE_ID, true);
						}
						
					}
					catch (Exception $e)
					{
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Submitting the Recurring Adjustment Request failed, while trying to log the action.");
						return TRUE;
					}

					// Everything was successful
					TransactionCommit();
					
					
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The Recurring Adjustment Request has been successfully logged.");
					
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				Ajax()->RenderHtmlTemplate("RecurringAdjustmentAdd", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				Ajax()->AddCommand("Alert", "ERROR: The Recurring Adjustment Request could not be submitted.  Invalid fields have been reset and highlighted");
				return TRUE;
			}
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('recurring_adjustment_add');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// DeleteAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteAdjustment()
	 *
	 * Performs Delete Adjustment functionality
	 * 
	 * Performs Delete Adjustment functionality
	 *
	 * @return		void
	 * @method
	 *
	 */
	function DeleteAdjustment()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		
		$bolUserHasProperAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		
		$bolCanDeleteAdjustments	= ($bolUserHasProperAdminPerm || $bolHasCreditManagementPerm);
		
		if (!$bolCanDeleteAdjustments)
		{
			Ajax()->AddCommand("Alert", "You do not have the required permissions to delete an adjustment");
			return TRUE;
		}
		
		try
		{
			TransactionStart();
			
			if (!DBO()->Charge->Load())
			{
				throw new Exception("The adjustment with id: ". DBO()->Charge->Id->Value ." could not be found");
			}

			// Deleting Adjustments can not be done while a live invoice run is outstanding
			$objAccount = Account::getForId(DBO()->Charge->Account->Value);
			if (Invoice_Run::checkTemporary($objAccount->customerGroup, $objAccount->id))
			{
				throw new Exception("This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			}
			
			$intAdjustmentId			= DBO()->Charge->Id->Value;
			$intOriginalChargeStatus	= DBO()->Charge->Status->Value;
			
			switch ($intOriginalChargeStatus)
			{
				case CHARGE_WAITING:
					$strActionDescriptionPastTense		= "Cancelled request for adjusment";
					$strActionDescriptionPresentTense	= "Cancelling request for adjustment";
					$strActionDescriptionFutureTense	= "Cancel request for adjustment";
					break;
					
				case CHARGE_APPROVED:
				case CHARGE_TEMP_INVOICE:
					$strActionDescriptionPastTense		= "Deleted adjustment";
					$strActionDescriptionPresentTense	= "Deleting adjustment";
					$strActionDescriptionFutureTense	= "Delete adjustment";
					break;
				
				default:
					throw new Exception("The adjustment can not be deleted due to its status (charge status: {$intOriginalChargeStatus})");
					break;
			}
			
			// The charge can be deleted
			
			DBO()->Charge->Status = CHARGE_DELETED;
			
			// If an Invoice Run is associated with it (it is temp invoiced), set it to NULL, so it doesn't get reversed, if the invoice run gets reversed
			DBO()->Charge->invoice_run_id = NULL;

			// Update the charge record
			if (!DBO()->Charge->Save())
			{
				throw new Exception("Failed to {$strActionDescriptionFutureTense}");
			}
			
			// The Adjustment was successfully 'Deleted'
			
			// Record the system note (and include the user's note, if they defined one)
			$strUserNote		= trim(DBO()->Note->Note->Value);
			$strChargeType		= DBO()->Charge->ChargeType->Value ." - ". DBO()->Charge->Description->Value;
			$strAmount			= number_format(addGST(DBO()->Charge->Amount->Value), 2, '.', '');
			$strNature			= (DBO()->Charge->Nature->Value == 'CR')? "Credit" : "Debit";
			$strCreatedOn		= date('d-m-Y', strtotime(DBO()->Charge->CreatedOn->Value));
			$intEmployeeId		= AuthenticatedUser()->_arrUser['Id'];
			
			
			$strNote = 	"{$strActionDescriptionPastTense} (id: $intAdjustmentId)\n".
						"Type: {$strChargeType} ({$strNature})\n".
						"Amount (Inc GST): \${$strAmount} {$strNature}\n".
						"Created: $strCreatedOn";
			
			if ($strUserNote != '')
			{
				$strNote .= "\nUser Comments:\n{$strUserNote}";
			}
			
			Note::createSystemNote($strNote, $intEmployeeId, DBO()->Charge->Account->Value, DBO()->Charge->Service->Value);
			
			// All database modifications have been finalised
			TransactionCommit();
		}
		catch (Exception $e)
		{
			TransactionRollback();
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "ERROR: ". $e->getMessage());
			return TRUE;
		}
		
		// Success
		Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
		Ajax()->AddCommand("AlertReload", "Successfully {$strActionDescriptionPastTense}");
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// DeleteRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteRecurringAdjustment()
	 *
	 * Performs Delete Recurring Adjustment functionality
	 * 
	 * Performs Delete Recurring Adjustment functionality
	 *
	 * @return		void
	 * @method
	 *
	 */
	function DeleteRecurringAdjustment()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		
		$bolUserHasProperAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		
		$bolCanDeleteAdjustments	= ($bolUserHasProperAdminPerm || $bolHasCreditManagementPerm);
		
		if (!$bolCanDeleteAdjustments)
		{
			Ajax()->AddCommand("Alert", "You do not have the required permissions to cancel recurring adjustments");
			return TRUE;
		}

		TransactionStart();
		try
		{

			$objRecurringCharge = Recurring_Charge::getForId(DBO()->RecurringCharge->Id->Value);

			// Deleting Recurring Adjustments can not be done while billing is in progress
			$objAccount = Account::getForId($objRecurringCharge->account);
			if (Invoice_Run::checkTemporary($objAccount->customerGroup, $objAccount->id))
			{
				throw new Exception("This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			}

			$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];

			$strActionPassedTense	= ($objRecurringCharge->hasSatisfiedRequirementsForCompletion())? 'Discontinued' : 'Cancelled';
			$strSubjectOfTheAction	= ($objRecurringCharge->recurringChargeStatusId == Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL'))? 'Recurring Adjustment Request' : 'Recurring Adjustment';

			// Cancell the recurring adjustment
			$objRecurringCharge->setToCancelled($intEmployeeId, true, trim(DBO()->Note->Note->Value));
			
			
			TransactionCommit();
		}
		catch (Exception $e)
		{
			TransactionRollback();
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "ERROR: ". $e->getMessage());
			return TRUE;
		}
		
		// Success
		Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
		Ajax()->AddCommand("AlertReload", "Successfully $strActionPassedTense the $strSubjectOfTheAction");
		return TRUE;
	}

}
?>
