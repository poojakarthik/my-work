<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// service_movement
//----------------------------------------------------------------------------//
/**
 * service_movement
 *
 * contains the AppTemplateServiceMovement class which encapsulates the "Service Movement" functionality
 *
 * contains the AppTemplateServiceMovement class which encapsulates the "Service Movement" functionality
 *
 * @file		service_movement.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateServiceMovement
//----------------------------------------------------------------------------//
/**
 * AppTemplateServiceMovement
 *
 * The AppTemplateServiceMovement class
 *
 * The AppTemplateServiceMovement class
 *
 *
 * @package	ui_app
 * @class	AppTemplateServiceMovement
 * @extends	ApplicationTemplate
 */
class AppTemplateServiceMovement extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// DisplayServiceMovementPopup
	//------------------------------------------------------------------------//
	/**
	 * DisplayServiceMovementPopup()
	 *
	 * Displays the ServiceMovement popup (Change of lessee)
	 * 
	 * Displays the ServiceMovement popup (Change of lessee)
	 * It expects the following objects to be set:
	 * 	DBO()->Service->Id		Id of the service to move or reverse move
	 *
	 * @return		void
	 * @method		DisplayServiceMovementPopup
	 */
	function DisplayServiceMovementPopup()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Create the Service Object
		$intService = DBO()->Service->Id->Value;
		$objService = ModuleService::GetServiceById($intService);
		
		if ($objService === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not instantiate the Service Object (Service Id: $intService).  Please notify your system administrators");
			return TRUE;
		}
		elseif ($objService === NULL)
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find the service (Id: $intService) in the database");
			return TRUE;
		}
		
		// Check that this is the newest owner
		$objNewestService = ModuleService::GetServiceByFNN($objService->GetFNN());
		
		if ($objNewestService->GetAccount() != $objService->GetAccount())
		{
			// The current account isn't the newest owner of the Service
			// Service movement cannot be performed from this account
			$strAccountLink					= Href()->AccountOverview($objNewestService->GetAccount());
			$strTimeOfAcquisition			= $objNewestService->GetTimeOfAcquisition();
			$strNow							= GetCurrentISODateTime();
			$strTimeOfAcquisitionFormatted	= OutputMask()->LongDateAndTime($strTimeOfAcquisition);
			$strMsg							= "Account: <a href='$strAccountLink' title='View'>{$objNewestService->GetAccount()}</a> ". (($strTimeOfAcquisition > $strNow)? "will be acquiring":"acquired") ." this service on $strTimeOfAcquisitionFormatted.  Any Service movement operations have to be performed from this account.";
			Ajax()->AddCommand("ModalAlert", $strMsg);
			return TRUE;
		}
		
		// Check that the service is active
		if ($objService->GetStatus() != SERVICE_ACTIVE)
		{
			Ajax()->AddCommand("Alert", "Only active services can be moved");
			return TRUE;
		}
		
		DBO()->ServiceMove->ServiceObject	= $objService;
		DBO()->Account->Id					= $objService->GetAccount();
		DBO()->Account->Load();

		// Lessee Changes cannot be made when a bill run is taking place
		if (Invoice_Run::checkTemporary(DBO()->Account->CustomerGroup->Value, DBO()->Account->Id->Value))
		{
			Ajax()->AddCommand("Alert", "This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			return TRUE;
		}

		// Check if there are Session details relating to this functionality
		DBO()->ServiceMove->ProbableActionDetails = NULL;
		if (isset($_SESSION['ServiceMove']['CurrentAccount']) && $_SESSION['ServiceMove']['CurrentAccount'] == $objService->GetAccount())
		{
			// A Service Movement action has recently been performed on a service belonging to this account
			// Retrieve the details of this action, as they will probably be the same for this new action
			$arrProbableAction = array(
										"GainingAccount"		=> $_SESSION['ServiceMove']['GainingAccount'],
										"Action"				=> $_SESSION['ServiceMove']['Action'],
										"EffectiveOnType"		=> $_SESSION['ServiceMove']['EffectiveOnType'],
										"EffectiveOn"			=> $_SESSION['ServiceMove']['EffectiveOn'],
										"EffectiveOnFormatted"	=> OutputMask()->ShortDate($_SESSION['ServiceMove']['EffectiveOn']),
										"MoveCDRs"				=> $_SESSION['ServiceMove']['MoveCDRs'],
										"MovePlan"				=> $_SESSION['ServiceMove']['MovePlan'],
									);
			$arrColumns = array("AccountName"	=> "CASE WHEN BusinessName != '' THEN BusinessName WHEN TradingName != '' THEN TradingName ELSE NULL END",
								"Status"		=> "Archived",
								"CustomerGroup"	=> "CustomerGroup"
								);
			$selGainingAccount = new StatementSelect("Account", $arrColumns, "Id = <Id>");
			if ($selGainingAccount->Execute(array("Id"=>$arrProbableAction['GainingAccount'])))
			{
				// The account could be found
				$arrRecord = $selGainingAccount->Fetch();
				$arrProbableAction['AccountName']		= $arrRecord['AccountName'];
				$arrProbableAction['Status']			= $arrRecord['Status'];
				$arrProbableAction['StatusDesc']		= GetConstantDescription($arrRecord['Status'], "account_status");
				$arrProbableAction['CustomerGroup']		= $arrRecord['CustomerGroup'];
				$arrProbableAction['CustomerGroupName']	= Customer_Group::getForId($arrRecord['CustomerGroup'])->externalName;
				
				DBO()->ServiceMove->ProbableActionDetails = $arrProbableAction;
			}
		}
		else
		{
			// We are dealing with a different account than the one that is cached
			// Reset the cached details
			$_SESSION['ServiceMove'] = NULL;
		}
		
		// If the Service can have its last move reversed, then retrieve the details of the previous owner account
		DBO()->ServiceMove->PreviousOwner = NULL;
		if ($objService->CanReverseMove())
		{
			// The Move can be reversed, get the previous owner
			$intPreviousOwner	= $objService->GetPreviousOwner();
			$arrAccount			= $this->_GetAccountDetails($intPreviousOwner);
			
			if ($arrAccount === FALSE)
			{
				return FALSE;
			}
			
			// The account could be found
			DBO()->ServiceMove->PreviousOwner = array(
														"Id"				=> $intPreviousOwner,
														"Action"			=> $objService->GetNatureOfAcquisition(),
														"AccountName"		=> $arrAccount['Name'],
														"Status"			=> $arrAccount['Status'],
														"StatusDesc"		=> GetConstantDescription($arrAccount['Status'], "account_status"),
														"CustomerGroup"		=> $arrAccount['CustomerGroup'],
														"CustomerGroupName"	=> Customer_Group::getForId($arrAccount['CustomerGroup'])->externalName,
													);
		}
		
		// Use the generic popup page template
		$this->LoadPage('generic_popup');
		$this->Page->SetName('Service Movement - '. $objService->GetFNN());
		$this->Page->AddObject('ServiceMovement', COLUMN_ONE);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// GetAccountDetailsForServiceMove
	//------------------------------------------------------------------------//
	/**
	 * GetAccountDetailsForServiceMove()
	 *
	 * Retrieves the Account Details for the potential service gaining account
	 * 
	 * Retrieves the Account Details for the potential service gaining account
	 * It expects the following objects to be set:
	 * 	DBO()->Account->Id			Id of the potential service gaining account
	 *  DBO()->CurrentAccount->Id	Id of the account that currently owns the service
	 *
	 * @return		void
	 * @method		GetAccountDetailsForServiceMove
	 */
	function GetAccountDetailsForServiceMove()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$intCurrentAccountId	= DBO()->CurrentAccount->Id->Value;
		$intAccountId			= (int)(DBO()->Account->Id->Value);
		
		if ($intAccountId === 0)
		{
			Ajax()->AddCommand("Alert", "ERROR: Account Id is invalid");
			return TRUE;
		}
		
		if ($intAccountId == $intCurrentAccountId)
		{
			Ajax()->AddCommand("Alert", "ERROR: This account already owns this service");
			return TRUE;
		}
		
		// Retrieve the required Account details
		if (($arrAccount = $this->_GetAccountDetails($intAccountId)) === FALSE)
		{
			// Error retrieving the account details (error reporting has been handled)
			return TRUE;
		}
		
		// Check that the status of the account is acceptable
		if ($arrAccount['Status'] == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "ERROR: Account $intAccountId is pending activation.  Services cannot be moved to it");
			return TRUE;
		}
		
		// Send the retrieved record
		$arrAccount['StatusDesc']			= GetConstantDescription($arrAccount['Status'], "account_status");
		$arrAccount['CustomerGroupName']	= Customer_Group::getForId($arrAccount['CustomerGroup'])->externalName;
		AjaxReply($arrAccount);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// PerformServiceMove
	//------------------------------------------------------------------------//
	/**
	 * PerformServiceMove()
	 *
	 * Performs the Service Move (Change of Lessee or Change of Account)
	 * 
	 * Performs the Service Move (Change of Lessee or Change of Account)
	 * It expects the following objects to be set:
	 * 	DBO()->Movement->ServiceId			Id of one of the service records modelling the service on the Account that is losing ownership (current Account)
	 *	DBO()->Movement->CurrentAccount		Account Id of the current owner of the service
	 *	DBO()->Movement->CurrentAccountName	Name of the current owner of the service
	 * 	DBO()->Movement->ActionType			either "LesseeChange" or "AccountChange"
	 * 	DBO()->Movement->GainingAccount		Id of the account that the service will be moved to 
	 * 	DBO()->Movement->EffectiveOnType	either "Immediately" or "Date"
	 * 	DBO()->Movement->EffectiveOnDate	date (dd/mm/yyy) on which the move will take place if not being moved immediately. 
	 * 										The TimeStamp will be set to 00:00:00 dd/mm/yyyy
	 * 	DBO()->Movement->MoveCDRs			boolean.  If TRUE then all unbilled cdrs will be moved to the new Account
	 * 	DBO()->Movement->MovePlan			boolean.  If TRUE then the service will retain its plan details when it moves
	 *
	 *  If successful, it will fire an OnNewNote event and an OnServiceUpdate event
	 *
	 * @return		void
	 * @method
	 */
	function PerformServiceMove()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$intGainingAccount	= DBO()->Movement->GainingAccount->Value;

		// Make the Service object
		$intServiceId		= DBO()->Movement->ServiceId->Value;
		$objService			= ModuleService::GetServiceById($intServiceId);
		
		if ($objService === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not instantiate the Service Object (Service Id: $intServiceId).  Please notify your system administrators");
			return TRUE;
		}
		elseif ($objService === NULL)
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find the service (Id: $intServiceId) in the database");
			return TRUE;
		}
		
		$intService = $objService->GetId();
		$intAccountId = DBO()->Movement->CurrentAccount->Value;
		
		// Check that the Service object is modelling the service on the current owner of the service
		
		$intNewestOwner = ModuleService::GetNewestOwner($objService->GetFNN());
		if ($intAccountId != $intNewestOwner)
		{
			// This account isn't the current owner
			Ajax()->AddCommand("Alert", "ERROR: Account: $intAccountId is not the current owner of this service.  Account: $intNewestOwner is.");
			return TRUE;
		}
		
		// Check that the Gaining Account is different to the current account
		if ($intAccountId == $intGainingAccount)
		{
			// The proposed new account is the same as the current current one
			Ajax()->AddCommand("Alert", "ERROR: The proposed new owner of the service (Account $intGainingAccount) is already the current owner of the service");
			return TRUE;
		}
		
		// Work out what sort of action is proposed
		$strActionType = DBO()->Movement->ActionType->Value;
		switch ($strActionType)
		{
			case "LesseeChange":
				$bolChangeOfLessee = TRUE;
				break;
				
			case "AccountChange":
				$bolChangeOfLessee = FALSE;
				break;
			default:
				// Unknown action
				Ajax()->AddCommand("Alert", "ERROR: Unknown type of action.  Action Type = '$strActionType'");
				return TRUE;
		}
		
		// Find out when the Gaining Account was created
		// You can't have the EffectiveOn timestamp to a time before this
		if (($arrGainingAccount = $this->_GetAccountDetails($intGainingAccount)) === FALSE)
		{
			// Could not retrieve the details of the gaining account (error reporting has been handled)
			return TRUE;
		}
		
		// Check that the status of the gaining account is acceptable
		if ($arrGainingAccount['Status'] == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "ERROR: Account $intGainingAccount is pending activation.  Services cannot be moved to it");
			return TRUE;
		}
		
		// Work out the proposed move time
		$strEffectiveOnType	= DBO()->Movement->EffectiveOnType->Value;
		$strEffectiveOnDate	= DBO()->Movement->EffectiveOnDate->Value; 
		switch ($strEffectiveOnType)
		{
			case "Immediately":
				$strEffectiveOn = GetCurrentISODateTime();
				break;
			case "Date":
				
				$intDate = strtotime(ConvertDateToISODate($strEffectiveOnDate));
				if ($intDate === FALSE)
				{
					// Invalid Date
					Ajax()->AddCommand("Alert", "ERROR: The effective on date is invalid");
					return TRUE;
				}
				$strEffectiveOn = date("Y-m-d", $intDate) . " 00:00:00";
				break;
			default:
				// Unknown type
				Ajax()->AddCommand("Alert", "ERROR: Unknown EffectiveOn type.  Type = '$strEffectiveOnType'");
				return TRUE;
		}
		
		// Work out when it should take place
		$strEarliestAllowableTime = $objService->GetEarliestAllowableMoveTime();
		if (!$objService->IsOk())
		{
			// Earliest allowable move time could not be calculated
			Ajax()->AddCommand("Alert", "ERROR: Could not establish the Earliest Allowable Move Time for this Service");
			return TRUE;
		}
		
		if ($strEffectiveOn < $strEarliestAllowableTime)
		{
			// The proposed Date of change is less than the earliest allowable move time
			$strEffectiveOn = $strEarliestAllowableTime;
		}
		
		// I think we now have all the information to make the change
		$bolMoveCDRs	= DBO()->Movement->MoveCDRs->Value;
		$bolMovePlan	= DBO()->Movement->MovePlan->Value;
		$intEmployee	= AuthenticatedUser()->_arrUser['Id'];
		
		// The move has to be performed in a transaction
		TransactionStart();

		$intNewServiceId = $objService->MoveToAccount($intGainingAccount, $bolChangeOfLessee, $strEffectiveOn, $bolMoveCDRs, $bolMovePlan, $intEmployee);
		if ($intNewServiceId === FALSE)
		{
			// An Error occurred
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: The move could not be completed<br />". $objService->GetErrorMsg());
			return TRUE;
		}
		
		// The service move was successful
		TransactionCommit();
		
		// Create System Note for the account that is losing ownership
		$strFNN						= $objService->GetFNN();
		$strAction					= ($bolChangeOfLessee)? "Change of Lessee" : "Change of Account";
		$strEffectiveOnFormatted	= OutputMask()->LongDateAndTime($strEffectiveOn);
		$strOldOwnerAccountMsg		= "$strAction has been performed.  Effective $strEffectiveOnFormatted, its new owner is Account $intGainingAccount ({$arrGainingAccount['Name']}).";
		SaveSystemNote($strOldOwnerAccountMsg, $objService->GetAccountGroup(), $intAccountId, NULL, $objService->GetId());
		
		// Create System Note for the account that is gaining ownership
		$strOldOwnerName			= DBO()->Movement->CurrentAccountName->Value;
		$strNewOwnerAccountMsg		= "This service has been acquired through a $strAction operation, effective $strEffectiveOnFormatted.  Its previous owner was Account $intAccountId ({$strOldOwnerName}).";
		if ($bolMoveCDRs && $strEffectiveOn < GetCurrentISODateTime())
		{
			// The service movement is retroactive
			$strNewOwnerAccountMsg	.= "\nUnbilled CDRs since the time of acquisition will be applied to this account";
		}
		if ($bolMovePlan && DBO()->Movement->SameCustomerGroups->Value == TRUE)
		{
			$strNewOwnerAccountMsg	.= "\nThe service has retained its Plan details";
		}
		SaveSystemNote($strNewOwnerAccountMsg, $arrGainingAccount['AccountGroup'], $intGainingAccount, NULL, $intNewServiceId);
		
		// Update the cached action details regarding the ServiceMovement popup
		$arrCachedActionDetails = array(
										"CurrentAccount"	=> $intAccountId,
										"GainingAccount"	=> $intGainingAccount,
										"Action"			=> $strActionType,
										"EffectiveOnType"	=> $strEffectiveOnType,
										"EffectiveOn"		=> $strEffectiveOn,
										"MoveCDRs"			=> $bolMoveCDRs,
										"MovePlan"			=> $bolMovePlan
										);
										
		$_SESSION['ServiceMove'] = $arrCachedActionDetails;
		
		// Close the popup
		Ajax()->AddCommand("ClosePopup", "MoveServicePopup");
		
		$strMsg = "$strAction has been successful.<br />Account $intGainingAccount ({$arrGainingAccount['Name']}) gains control of the service, effective from $strEffectiveOnFormatted";
		Ajax()->AddCommand("Alert", $strMsg);
		
		// Fire events
		// The contents of this object should be declared in the doc block of this method
		$arrEvent['Service']['Id'] = $intServiceId;
		Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
		
		// Fire the OnNewNote Event
		Ajax()->FireOnNewNoteEvent($intAccountId, $intServiceId);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ReverseServiceMove
	//------------------------------------------------------------------------//
	/**
	 * ReverseServiceMove()
	 *
	 * Performs the Reversal of a Service Move (Change of Lessee or Change of Account), if it can be reversed
	 * 
	 * Performs the Reversal of a Service Move (Change of Lessee or Change of Account), if it can be reversed
	 * It expects the following objects to be set:
	 * 	DBO()->Movement->ServiceId		Id of one of the service records modelling the service on the Account that is losing ownership (current Account)
	 *
	 *  If successful, it will fire an OnNewNote event and an OnServiceUpdate event
	 *
	 * @return		void
	 * @method
	 */
	function ReverseServiceMove()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Make the Service object
		$intServiceId		= DBO()->Movement->ServiceId->Value;
		$objService			= ModuleService::GetServiceById($intServiceId);
		
		if ($objService === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not instantiate the Service Object (Service Id: $intService).  Please notify your system administrators");
			return TRUE;
		}
		elseif ($objService === NULL)
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find the service (Id: $intService) in the database");
			return TRUE;
		}
		
		// Check that we are dealing with the newest owner of the service
		$intOutgoingOwner		= $objService->GetAccount();
		$intOutgoingServiceId	= $objService->GetId();
		$intNewestOwner			= ModuleService::GetNewestOwner($objService->GetFNN());
		
		if ($intOutgoingOwner != $intNewestOwner)
		{
			// The service object is not representing the service's usage on the newest owner
			Ajax()->AddCommand("Alert", "ERROR: This account is not the current owner of the service.  The current owner of this service is account: $intNewestOwner");
			return TRUE;
		}
		
		// Check that there is a previous owner
		$intIncomingOwner = $objService->GetPreviousOwner();
		
		if (!$intIncomingOwner)
		{
			// Cannot establish a previous owner
			Ajax()->AddCommand("Alert", "ERROR: Cannot establish who was the previous owner of this service");
			return TRUE;
		}
		
		$intNatureOfAcquisition = $objService->GetNatureOfAcquisition();
		
		if ($intNatureOfAcquisition != SERVICE_CREATION_LESSEE_CHANGED && $intNatureOfAcquisition != SERVICE_CREATION_ACCOUNT_CHANGED)
		{
			Ajax()->AddCommand("Alert", "ERROR: Cannot establish the nature of acquisition");
			return TRUE;
		}

		$strEarliestAllowableMoveTime	= $objService->GetEarliestAllowableMoveTime();
		$strTimeOfAcquisition			= $objService->GetTimeOfAcquisition();
		
		if (!is_string($strTimeOfAcquisition))
		{
			Ajax()->AddCommand("Alert", "ERROR: Cannot establish the time of acquisition");
			return TRUE;
		}
		
		if ($strEarliestAllowableMoveTime != $strTimeOfAcquisition)
		{
			Ajax()->AddCommand("Alert", "ERROR: This account has already been invoiced for this service.  Its acquisition cannot be reversed");
			return TRUE;
		}
		
		// Retrieve details about the outgoing owner
		if (($arrOutgoingOwner = $this->_GetAccountDetails($intOutgoingOwner)) === FALSE)
		{
			// Error (error reporting has been handled already)
			return TRUE; 
		}
		
		// Retrieve details about the previous owner
		if (($arrIncomingOwner = $this->_GetAccountDetails($intIncomingOwner)) === FALSE)
		{
			// Error (error reporting has been handled already)
			return TRUE;
		}
		
		// It should be fine to do the reverse
		TransactionStart();

		$intEmployee		= AuthenticatedUser()->_arrUser['Id'];
		$intNewServiceId	= $objService->ReverseMove($intEmployee);
		if ($intNewServiceId === FALSE)
		{
			// An Error occurred
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: The reversal could not be completed<br />". $objService->GetErrorMsg());
			return TRUE;
		}
		
		// The Reversal was successful
		TransactionCommit();
		
		// Create System Note for the account that is losing ownership
		$strFNN					= $objService->GetFNN();
		$strAction				= ($intNatureOfAcquisition == SERVICE_CREATION_ACCOUNT_CHANGED)? "Change of Account" : "Change of Lessee";
		$strOutgoingOwnerMsg	= "$strAction Reversal has been performed.  This service now belongs to account {$arrIncomingOwner['Id']} ({$arrIncomingOwner['Name']}).";
		SaveSystemNote($strOutgoingOwnerMsg, $arrOutgoingOwner['AccountGroup'], $intOutgoingOwner, NULL, $intOutgoingServiceId);
		
		// Create System Note for the account that is gaining ownership (previous owner)
		$strIncomingOwnerMsg	= "$strAction Reversal has been performed.  This account now owns this service again.  The $strAction was to account {$arrOutgoingOwner['Id']} ({$arrOutgoingOwner['Name']}).";
		SaveSystemNote($strIncomingOwnerMsg, $arrIncomingOwner['AccountGroup'], $intIncomingOwner, NULL, $intNewServiceId);
		
		// Close the popup
		Ajax()->AddCommand("ClosePopup", "MoveServicePopup");
		
		$strMsg = "$strAction Reversal has been successful.<br />Account {$arrIncomingOwner['Id']} ({$arrIncomingOwner['Name']}) once again has control of this service.";
		Ajax()->AddCommand("Alert", $strMsg);
		
		// Fire events
		// The contents of this object should be declared in the doc block of this method
		$arrEvent['Service']['Id'] = $intOutgoingServiceId;
		Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
		
		// Fire the OnNewNote Event
		Ajax()->FireOnNewNoteEvent($intOutgoingOwner, $intOutgoingServiceId);
		return TRUE;
		
		
	}
	
	// Usually when I want to get details about an account, I want the same information
	// If an error occurs, this will add an Alert command to the ajax response
	private function _GetAccountDetails($intAccount)
	{
		$arrColumns = array("Id"			=> "Id",
							"AccountGroup"	=> "AccountGroup",
							"CreatedOn"		=> "CreatedOn",
							"Name"			=> "CASE WHEN BusinessName != '' THEN BusinessName WHEN TradingName != '' THEN TradingName ELSE NULL END",
							"Status"		=> "Archived",
							"CustomerGroup" => "CustomerGroup"
							);
		$selAccount = new StatementSelect("Account", $arrColumns, "Id = <Id>");
		if ($selAccount->Execute(array("Id"=>$intAccount)) === FALSE)
		{
			// Database error
			Ajax()->AddCommand("Alert", "ERROR: Retrieving Account with Id: $intAccount, from the database failed, unexpectedly.  Please notify your system administrators");
			return FALSE;
		}
		if (($arrAccount = $selAccount->Fetch()) === FALSE)
		{
			// Could not find the account
			Ajax()->AddCommand("Alert", "ERROR: Could not find Account with Id: $intAccount");
			return FALSE;
		}
		return $arrAccount;
	}
	
	
}
?>