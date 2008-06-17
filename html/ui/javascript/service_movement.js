//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_movement.js
//----------------------------------------------------------------------------//
/**
 * service_movement
 *
 * javascript required of the Service Movement popup
 *
 * javascript required of the Service Movement popup
 *
 *
 * @file		service_movement.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceMovementClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceMovementClass
 *
 * Encapsulates all event handling required of the Service Movement popup
 *
 * Encapsulates all event handling required of the Service Movement popup
 * 
 *
 * @package	ui_app
 * @class	VixenServiceMovementClass
 * 
 */
function VixenServiceMovementClass()
{
	// Current Service Id
	this.intServiceId		= null;
	
	this.intGainingAccount				= null;
	this.intGainingAccountCustomerGroup	= null;
	
	this.strFNN				= null;
	this.objCurrentAccount	= null;
	this.objPreviousAccount	= null;

	this.objInputs	= {};
	
	this.elmAccountIdInputContainerDiv	= null;
	this.elmPreviousOwnerContainerDiv	= null;
	this.elmNewOwnerContainerDiv		= null;
	this.elmNewAccountName				= null;
	this.elmNewAccountId				= null;
	this.elmNewAccountStatus			= null;
	this.elmNewAccountCustomerGroup		= null;
	
	this.strActionTypeLastSelected		= null;
	
	// Handles intialisation processes that should be carried out regardless of it if is for adding a new one, or editing an existing one, or view one
	this.Initialise = function(strFNN, intServiceId, objCurrentAccount, objPreviousOwner, objProbableAction)
	{
		this.intServiceId		= intServiceId;
		this.strFNN				= strFNN;
		this.objCurrentAccount	= objCurrentAccount;
		this.objPreviousAccount	= objPreviousOwner;
		
		// Get a reference to each input element on the form
		// Every input element on the form that has a name will be referenced in the this.objInputElements object
		/* The following properties should be set:
		 *		this.objInputs.AccountId
		 *						ActionType
		 *						EffectiveOnDate
		 *						EffectiveOnType
		 *						MoveCDRs
		 *						MovePlan
		 */
		var formServiceMovement = $ID("ServiceMovementForm");
		var strName;
		
		for (i=0; i < formServiceMovement.length; i++)
		{
			if (formServiceMovement[i].name)
			{
				strName = formServiceMovement[i].name;
				this.objInputs[strName] = formServiceMovement[i];
			}
		}
		
		// Grab other references
		this.elmAccountIdInputContainerDiv	= $ID("ServiceMovement.AccountIdInputContainer");
		this.elmPreviousOwnerContainerDiv	= $ID("ServiceMovement.PreviousOwnerContainer");
		this.elmNewOwnerContainerDiv		= $ID("ServiceMovement.NewOwnerContainer");
		this.elmNewAccountName				= $ID("ServiceMovement.NewAccountName");
		this.elmNewAccountId				= $ID("ServiceMovement.NewAccountId");
		this.elmNewAccountStatus			= $ID("ServiceMovement.NewAccountStatus");
		this.elmNewAccountCustomerGroup		= $ID("ServiceMovement.NewAccountCustomerGroup");
		
		// Register Listeners
		Event.startObserving(this.objInputs.ActionType, "keypress", this.ActionTypeComboOnChange.bind(this), true);
		Event.startObserving(this.objInputs.ActionType, "click", this.ActionTypeComboOnChange.bind(this), true);
		Event.startObserving(this.objInputs.EffectiveOnType, "keyup", this.EffectiveOnTypeComboOnChange.bind(this), true);
		Event.startObserving(this.objInputs.EffectiveOnType, "click", this.EffectiveOnTypeComboOnChange.bind(this), true);

		// Register InputMasks
		RegisterAllInputMasks();
		
		if (objProbableAction != null)
		{
			this.InitialiseProbableAction(objProbableAction);
		}
	}

	// Sets up the form to reflect the probable action
	this.InitialiseProbableAction = function(objProbableAction)
	{
		this.objInputs.ActionType.value = objProbableAction.Action;
		this.ActionTypeComboOnChange();
		
		this.SetNewOwner(objProbableAction.GainingAccount, objProbableAction.CustomerGroup, objProbableAction.AccountName, objProbableAction.StatusDesc, objProbableAction.CustomerGroupName, true);
		
		// Set the remaining inputs
		this.objInputs.EffectiveOnType.value = objProbableAction.EffectiveOnType;
		this.EffectiveOnTypeComboOnChange();
		this.objInputs.EffectiveOnDate.value = objProbableAction.EffectiveOnFormatted;
		this.objInputs.MoveCDRs.checked = objProbableAction.MoveCDRs;
		
		// Only set the MovePlan checkbox if it isn't currently disabled
		if (!this.objInputs.MovePlan.disabled)
		{
			this.objInputs.MovePlan.checked = objProbableAction.MovePlan;
		}
		
		// Display the NewOwner container
		this.elmNewOwnerContainerDiv.style.display = "block";
	}

	this.ActionTypeComboOnChange = function()
	{
		if (this.strActionTypeLastSelected == this.objInputs.ActionType.value)
		{
			// Don't do anything
			return true;
		}
		switch (this.objInputs.ActionType.value)
		{
			case "LesseeChange":
			case "AccountChange":
				this.elmPreviousOwnerContainerDiv.style.display		= "none";
				this.elmNewOwnerContainerDiv.style.display			= "none";
				this.elmAccountIdInputContainerDiv.style.display	= "block";
				break;
				
			case "ReverseLesseeChange":
			case "ReverseAccountChange":
				this.elmAccountIdInputContainerDiv.style.display	= "none";
				this.elmNewOwnerContainerDiv.style.display			= "none";
				this.elmPreviousOwnerContainerDiv.style.display		= "block";
				break;
				
			default:
				// Hide everything
				this.elmAccountIdInputContainerDiv.style.display	= "none";
				this.elmPreviousOwnerContainerDiv.style.display		= "none";
				this.elmNewOwnerContainerDiv.style.display			= "none";
				break;
		}
		this.strActionTypeLastSelected = this.objInputs.ActionType.value;
	}
	
	this.EffectiveOnTypeComboOnChange = function()
	{
		if (this.objInputs.EffectiveOnType.value == "Date")
		{
			this.objInputs.EffectiveOnDate.style.display = "inline";
		}
		else
		{
			this.objInputs.EffectiveOnDate.style.display = "none";
		}
	}

	// Retrieves from the server, the details of the proposed new owner account
	this.FindAccount = function()
	{
		// Validate the AccountId
		if (!this.objInputs.AccountId.Validate("PositiveInteger"))
		{
			$Alert("ERROR: The Account Id is invalid");
			return false;
		}
		
		// Compile data to be sent to the server
		var objData	= 	{
							Account			: {Id : this.objInputs.AccountId.value},
							CurrentAccount	: {Id : this.objCurrentAccount.Id}
						};
		
		Vixen.Popup.ShowPageLoadingSplash("Retrieving Account Details");
		Vixen.Ajax.CallAppTemplate("ServiceMovement", "GetAccountDetailsForServiceMove", objData, null, false, true, this.FindAccountReturnHandler.bind(this));
	}
	
	this.FindAccountReturnHandler = function(objXMLHttpRequest)
	{
		// If this function is run, then the account was successfully found
		var objAccount = JSON.parse(objXMLHttpRequest.responseText);
		
		// Check that the ActionType combo hasn't been changed to something other than "LesseeChange" or "AccountChange"
		if (this.objInputs.ActionType.value != "LesseeChange"  && this.objInputs.ActionType.value != "AccountChange")
		{
			// Don't bother showing the Account details
			return;
		}

		this.SetNewOwner(objAccount.Id, objAccount.CustomerGroup, objAccount.Name, objAccount.StatusDesc, objAccount.CustomerGroupName);
	}
	
	// Sets the details of the new owner and displays the container div
	this.SetNewOwner = function(intAccountId, intCustomerGroup, strAccountName, strAccountStatusDesc, strCustomerGroup, bolDontDisplay)
	{
		// Defaults to false
		bolDontDisplay = (bolDontDisplay != undefined)? bolDontDisplay : false;
	
		this.intGainingAccount						= intAccountId;
		this.intGainingAccountCustomerGroup			= intCustomerGroup;
		this.objInputs.AccountId.value				= intAccountId;
		this.elmNewAccountId.innerHTML				= "<a href='flex.php/Account/Overview/?Account.Id="+ intAccountId +"' title='View Account'>"+ intAccountId +"</a>";
		this.elmNewAccountName.innerHTML			= strAccountName;
		this.elmNewAccountStatus.innerHTML			= strAccountStatusDesc;
		this.elmNewAccountCustomerGroup.innerHTML	= strCustomerGroup;
		
		if (this.intGainingAccountCustomerGroup != this.objCurrentAccount.CustomerGroup)
		{
			// The CustomerGroups differ which means the Plan details will not be able to be copied across
			this.objInputs.MovePlan.disabled	= true;
			this.objInputs.MovePlan.checked		= false;
		}
		else
		{
			this.objInputs.MovePlan.disabled	= false;
		}
		
		this.elmNewOwnerContainerDiv.style.display = (bolDontDisplay)? "none":"block";
	}
	
	this.CommitServiceMove = function(bolConfirmed)
	{
		// Validate the EffectiveOn date if specified
		if (!this.ValidateEffectiveOn())
		{
			return false;
		}
		
		if (!bolConfirmed)
		{
			// Confirm with the user their actions
			var strAction		= (this.objInputs.ActionType.value == "LesseeChange")? "a Change of Lessee" : "a Change of Account";
			var strEffective	= (this.objInputs.EffectiveOnType.value == "Immediately")? "immediately" : "at the beginning of " + this.objInputs.EffectiveOnDate.value;
			
			var strPlanWarning	= "";
			if (this.intGainingAccountCustomerGroup != this.objCurrentAccount.CustomerGroup)
			{
				strPlanWarning = "<br /><span style='margin-top:10px;color:#FF0000'>WARNING: Plan details will not be copied across, due to the new owner being in a different Customer Group to the current owner.  You will have to manually set a plan for this Service once the move has been made.</span>";
			}
			
			var strMsg 	= "Are you sure you want to perform " + strAction + " on service "+ this.strFNN +" effective " + strEffective + "?<br /><br />";
			strMsg		+= "<table cellpadding='0' cellspacing='0'>";
			strMsg 		+= "<tr><td width='45%'>Current Account:</td><td width='55%'>"+ this.objCurrentAccount.Name +" ("+ this.objCurrentAccount.Id +")</td></tr>";
			strMsg		+= "<tr><td>New Account:</td><td>"+ this.elmNewAccountName.innerHTML +" ("+ this.intGainingAccount +")</td></tr>";
			strMsg		+= "<tr><td>Move Unbilled CDRs:</td><td>" + ((this.objInputs.MoveCDRs.checked)? "Yes (only CDRs from after the 'Time of Acquisition' will be moved)" : "No") + "</td></tr>";
			strMsg		+= "<tr><td>Copy Plan Details:</td><td>" + ((this.objInputs.MovePlan.checked)? "Yes" : "No") +"</td></tr>";
			strMsg		+= "</table>" + strPlanWarning;
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ServiceMovement.CommitServiceMove(true)});
			
			return false;
		}
		
		// Prepare data to be sent to the server
		var objData = 	{
							Movement :	{
											ServiceId			: this.intServiceId,
											CurrentAccount		: this.objCurrentAccount.Id,
											CurrentAccountName	: this.objCurrentAccount.Name,
											ActionType			: this.objInputs.ActionType.value,
											GainingAccount		: this.intGainingAccount,
											EffectiveOnType		: this.objInputs.EffectiveOnType.value,
											EffectiveOnDate		: this.objInputs.EffectiveOnDate.value,
											MoveCDRs			: this.objInputs.MoveCDRs.checked,
											MovePlan			: this.objInputs.MovePlan.checked,
											SameCustomerGroups	: (this.intGainingAccountCustomerGroup == this.objCurrentAccount.CustomerGroup)? true : false
										}
						};
		Vixen.Ajax.CallAppTemplate("ServiceMovement", "PerformServiceMove", objData, null, true, true);
	}
	
	this.ValidateEffectiveOn = function()
	{
		if (this.objInputs.EffectiveOnType.value == "Immediately")
		{
			return true;
		}

		if (!this.objInputs.EffectiveOnDate.Validate("ShortDate"))
		{
			// The date is invalid
			$Alert("ERROR: EffectiveOn date is invalid");
			return false;
		}
		return true;
	}
	
	this.CommitReverse = function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			// Confirm with the user their actions
			var strAction;
			switch (this.objInputs.ActionType.value)
			{
				case "ReverseLesseeChange":
					strAction = "Change of Lessee";
					break;
					
				case "ReverseAccountChange":
					strAction = "Change of Account";
					break;
					
				default:
					// Invalid action
					$Alert("ERROR: Invalid action ("+ this.objInputs.ActionType.value + ")");
					return false;
					break;
			}
			
			var strPlanWarning = "";
			if (this.objPreviousAccount.CustomerGroup != this.objCurrentAccount.CustomerGroup)
			{
				strPlanWarning = "<br /><span style='margin-top:10px;color:#FF0000'>WARNING: Plan details will not be copied across, due to the previous account being in a different Customer Group.  You will have to manually set a plan for this Service once you have reversed the move.</span>";
			}
			
			var strMsg 	= "Are you sure you want to reverse the " + strAction + " of service "+ this.strFNN +"?<br /><br />";
			strMsg		+= "<table cellpadding='0' cellspacing='0'>";			
			strMsg 		+= "<tr><td width='45%'>Current Account:</td><td width='55%'>"+ this.objCurrentAccount.Name +" ("+ this.objCurrentAccount.Id +")</td></tr>";
			strMsg		+= "<tr><td>Account to revert to:</td><td>"+ this.objPreviousAccount.AccountName +" ("+ this.objPreviousAccount.Id +")</td></tr>";
			strMsg		+= "</table>" + strPlanWarning;
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ServiceMovement.CommitReverse(true)});
			
			return false;
		}
		
		// Prepare data to be sent to the server
		var objData = 	{
							Movement :	{
											ServiceId	: this.intServiceId
										}
						};
		Vixen.Ajax.CallAppTemplate("ServiceMovement", "ReverseServiceMove", objData, null, true, true);
	}
}

if (Vixen.ServiceMovement == undefined)
{
	Vixen.ServiceMovement = new VixenServiceMovementClass;
}
