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
	this._intServiceId = null;
	
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
	this.Initialise = function(intServiceId)
	{
		this._intServiceId = intServiceId;
	}
	
	// Event handler for the "Change Plan" button
	// This prompts the user describing the consequences of the plan change
	this.ChangePlan = function()
	{
		var intStartNextBill = document.getElementById("StartTimeCombo").value;
		//TODO! finish this when you implement the confirm box
	}
	
	//------------------------------------------------------------------------//
	// ViewPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * ViewPlanDetails
	 *
	 * Event handler for when the View Plan Details button is triggered
	 *  
	 * Event handler for when the View Plan Details button is triggered
	 * Relocates the user to the "Rate Plan Details" page
	 *
	 * @return	void
	 * @method
	 */
	this.ViewPlanDetails = function()
	{
		var intPlanId = document.getElementById('SelectPlanCombo').value;
		window.location = 'rates_plan_summary.php?Id=' + intPlanId;
	}
}

// instanciate the objects
if (Vixen.PlanChange == undefined)
{
	Vixen.PlanChange = new VixenPlanChangeClass;
}
