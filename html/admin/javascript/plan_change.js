//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// plan_change.js
//----------------------------------------------------------------------------//
/**
 * plan_change
 *
 * javascript required of the "Plan Change" popup
 *
 * javascript required of the "Plan Change" popup
 * 
 *
 * @file		plan_change.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenPlanChangeClass
//----------------------------------------------------------------------------//
/**
 * VixenPlanChangeClass
 *
 * Encapsulates all event handling and dhtml required of the "Plan Change" popup
 *
 * Encapsulates all event handling and dhtml required of the "Plan Change" popup
 *
 * @package	ui_app
 * @class	VixenPlanChangeClass
 * 
 */
function VixenPlanChangeClass()
{
	this.intServiceId		= null;
	this.strPopupId			= null;
	this.elmViewPlanLink	= null;
	
	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the popup
	 *
	 * Initialises the popup
	 *
	 * @param	int		intServiceId	Id of the service which is having its plan changed
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(intServiceId, strPopupId)
	{
		this.intServiceId		= intServiceId;
		this.strPopupId			= strPopupId;
		this.elmViewPlanLink	= $ID("ChangePlan.ViewPlanDetails");
	}
	
	// Event handler for the "Change Plan" button
	// This prompts the user describing the consequences of the plan change
	this.ChangePlan = function(bolConfirmed)
	{
		var intStartTimeComboValue = $ID("Combo_NewPlan.StartTime").value;
		
		// Check that the Plan Change has been confirmed
		if (bolConfirmed == null)
		{
			if (intStartTimeComboValue == 0)
			{
				// They want to retroactivate a plan, which could mean updating CDRs in the CDR table which could take a while
				var strMsg = "Are you sure you want to change this service's plan, effective from the start of the current billing period?<br />WARNING: This process can take several minutes";
			}
			else
			{
				// The plan change comes into effect at the begining of the next billing period
				var strMsg = "Are you sure you want to change this service's plan, effective from the start of the next billing period?";
			}
			Vixen.Popup.Confirm(strMsg, function(){Vixen.PlanChange.ChangePlan(true);});
			return;
		}
		
		// Submit the form data
		Vixen.Ajax.SendForm("VixenForm_ChangePlan", "Change Plan", "Service", "ChangePlan", "", this.strPopupId);
	}
}

// instanciate the objects
if (Vixen.PlanChange == undefined)
{
	Vixen.PlanChange = new VixenPlanChangeClass;
}
