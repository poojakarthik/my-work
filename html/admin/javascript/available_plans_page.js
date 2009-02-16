//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// available_plans_page.js
//----------------------------------------------------------------------------//
/**
 * available_plans_page
 *
 * javascript required of the Available Plans page
 *
 * javascript required of the Available Plans page
 * 
 *
 * @file		available_plans_page.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenAvailablePlansPageClass
//----------------------------------------------------------------------------//
/**
 * VixenAvailablePlansPageClass
 *
 * Encapsulates all event handling required of the Available Plans webpage
 *
 * Encapsulates all event handling required of the Available Plans webpage
 *
 * @package	ui_app
 * @class	VixenAvailablePlansPageClass
 * 
 */
function VixenAvailablePlansPageClass()
{
	var RATE_STATUS_ACTIVE		= 0;
	var RATE_STATUS_ARCHIVED	= 1;
	var RATE_STATUS_DRAFT		= 2;
	
	this.objRatePlans = null;
	

	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the Popup
	 *
	 * Initialises the Popup
	 *
	 * @param	int		intCurrentStatus	The current status of the service
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(objRatePlans)
	{
		this.objRatePlans = objRatePlans;
	}
	
	this.TogglePlanStatus = function(intRatePlanId)
	{
		if (this.objRatePlans[intRatePlanId] == undefined)
		{
			$Alert("Could not find the RatePlan with id: "+ intRatePlanId);
			return;
		}
		
		if (this.objRatePlans[intRatePlanId].DealerCount == 0 || this.objRatePlans[intRatePlanId].Status != RATE_STATUS_ACTIVE)
		{
			// The simple prompt can be used
			this.PromptForTogglePlanStatus(intRatePlanId);
			return;
		}
		
		// The RatePlan is associated with dealers and is about to be archived
		// Notify the user and prompt them to select an alternate RatePlan for the dealers to use
		this.PromptForAlternatePlan(intRatePlanId);
	}
	
	this.PromptForAlternatePlan = function(intRatePlanId)
	{
		var objRatePlan = this.objRatePlans[intRatePlanId];
		
		var strAlternateRatePlans = "<option value='0' selected='selected'>No Alternate RatePlan</option>";
		
		for (var i in this.objRatePlans)
		{
			if (this.objRatePlans[i].CustomerGroup == objRatePlan.CustomerGroup && 
				this.objRatePlans[i].ServiceType == objRatePlan.ServiceType && 
				this.objRatePlans[i].Status == RATE_STATUS_ACTIVE &&
				i != intRatePlanId)
			{
				strAlternateRatePlans += "<option value='"+ i +"'>"+ this.objRatePlans[i].Name +"</option>";
			}
		}
		
		var strPopupContent = 	"<div id='PopupPageBody' style='padding:3px'>" +
								"	<div class='GroupedContent'>" +
								"		Are you sure you want to <strong>archive</strong> the "+ objRatePlan.CustomerGroup +", "+ objRatePlan.ServiceType +" plan, '"+ objRatePlan.Name +"'?"+
								"		<br /><br />Archived Plans cannot be assigned to services that aren't already using them." +
								"		<br /><br /><span class='warning'>WARNING: This plan is currently assigned to dealers, and can be sold by them.  Archiving the plan will prohibit them from being able to sell it.  You can specify an alternate plan for dealers to sell.</span>" +
								"		<table class='form-data'>" +
								"			<tr>" +
								"				<td class='title' style='width:30%'>Alternate Plan</td>" +
								"				<td><select id='alternatePlanId' name='alternatePlanId' style='width:100%'>"+ strAlternateRatePlans +"</select></td>" +
								"			</tr>" +
								"		</table>" +
								"	</div>" +
								"	<div style='padding-top:3px;height:auto:width:100%'>" +
								"		<div style='float:right'>" +
								"			<input type='button' value='Archive Plan' onclick='Vixen.AvailablePlansPage.PromptForTogglePlanStatus("+ intRatePlanId +", true, parseInt($ID(\"alternatePlanId\").value)); Vixen.Popup.Close(this);'></input>" +
								"			<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>" +
								"		</div>" +
								"		<div style='clear:both;float:none'></div>" +
								"	</div>" +
								"</div>";
		Vixen.Popup.Create("TogglePlanStatus", strPopupContent, "Medium", "centre", "modal", "Archive Plan");
	}
	
	// Triggers Status toggle between Active and Archived
	this.PromptForTogglePlanStatus = function(intRatePlan, bolConfirmed, intAlternateRatePlan)
	{
		if (!bolConfirmed)
		{
			var objRatePlan = this.objRatePlans[intRatePlan];
			
			if (objRatePlan == undefined)
			{
				return;
			}
			
			var intNewStatus;
			var strArchiveDescription = "";
			var strAction;
			if (objRatePlan.Status == RATE_STATUS_ACTIVE)
			{
				intNewStatus			= RATE_STATUS_ARCHIVED;
				strAction				= "archive";
				strArchiveDescription	= "  Archived Plans cannot be assigned to services that aren't already using them.";

				if (objRatePlan.DealerCount > 0)
				{
					strArchiveDescription += "<br /><span class='warning'>WARNING: This plan is currently assigned to dealers, and can be sold by them.  Archiving the plan will prohibit them from being able to sell it</span>";
				}

			}
			else if (objRatePlan.Status == RATE_STATUS_ARCHIVED)
			{
				intNewStatus	= RATE_STATUS_ACTIVE;
				strAction		= "activate";
			}
			else
			{
				// Don't do anything
				return FALSE;
			}
			
			var strMsg = "Are you sure you want to <strong>"+ strAction +"</strong> the "+ objRatePlan.CustomerGroup +", "+ objRatePlan.ServiceType +" plan, '"+ objRatePlan.Name +"'?" + strArchiveDescription;
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.AvailablePlansPage.PromptForTogglePlanStatus(intRatePlan, true)});
			return;
		}
		
		// Make the call to the server, to toggle the status of the RatePlan
		var objData =	{
							RatePlan :	{	
											Id : intRatePlan
										},
							AlternateRatePlan :	{
													Id : (intAlternateRatePlan == 0)? null : intAlternateRatePlan
												}
						};
		
		Vixen.Ajax.CallAppTemplate("Plan", "TogglePlanStatus", objData, null, true, true);
	}
	
	// Emails Plan Brochures
	this.emailSelectedBrochures	= function ()
	{
		// Determine the Plans that are Selected
		var arrNoBrochures		= new Array();
		var strNoBrochures		= '';
		var arrHaveBrochures	= new Array();
		var strHaveBrochures	= '';
		var arrRatePlanIds		= new Array();
		var objCustomerGroups	= {};
		
		var arrCheckboxes		= document.getElementsByName('RatePlan_Checkbox');
		if (arrCheckboxes.length)
		{
			for (var i = 0; i < arrCheckboxes.length; i++)
			{
				var elmCheckbox	= arrCheckboxes[i];
				
				if (elmCheckbox.checked)
				{
					var elmRatePlanId	= $ID('RatePlan_'+elmCheckbox.value+'_BrochureId');
					if (elmRatePlanId)
					{
						// Add to list
						arrRatePlanIds.push(elmCheckbox.value);
						arrHaveBrochures.push($ID('RatePlan_'+elmCheckbox.value+'_Name').value);
						strHaveBrochures	+= "&nbsp;&nbsp;&nbsp;&nbsp;+ "+$ID('RatePlan_'+elmCheckbox.value+'_Name').value+"<br />\n";
						
						var intCustomerGroup				= $ID('RatePlan_'+elmCheckbox.value+'_CustomerGroup').value;
						var strCustomerGroup				= $ID('RatePlan_'+elmCheckbox.value+'_CustomerGroup_Name').innerHTML;
						objCustomerGroups[intCustomerGroup]	= (objCustomerGroups[intCustomerGroup] == undefined) ? {arrPlans: new Array(), strName: strCustomerGroup} : objCustomerGroups[intCustomerGroup];
						objCustomerGroups[intCustomerGroup].arrPlans.push($ID('RatePlan_'+elmCheckbox.value+'_Name').value);
					}
					else
					{
						// No Brochure
						arrNoBrochures.push($ID('RatePlan_'+elmCheckbox.value+'_Name').value);
						strNoBrochures		+= "&nbsp;&nbsp;&nbsp;&nbsp;- "+$ID('RatePlan_'+elmCheckbox.value+'_Name').value+"<br />\n";
					}
				}
			}
		}
		else
		{
			// No checkboxes
			$Alert("There are no Rate Plans available to email");
			return false;
		}
		
		// Are there Plans from more than 1 Customer Group?
		if (objCustomerGroups.length > 1)
		{
			var strPopupHTML	= 'You have selected Plans from multiple Customer Groups:';
			for (var i = 0; i < objCustomerGroups.length; i++)
			{
				strPopupHTML	+= objCustomerGroups[i].strName+":<br />\n";
				for (var t = 0; t < objCustomerGroups[i].arrPlans.length; t++)
				{
					strPopupHTML	+= "&nbsp;&nbsp;&nbsp;&nbsp;- "+objCustomerGroups[i].arrPlans[t]+"<br />\n";
				}
				strPopupHTML	+= "<br />\n";
			}
			
			strPopupHTML	+= "Please ensure you have selected Plans from only one Customer Group.";
			$Alert(strPopupHTML);
			return false;
		}
		
		// Check we have Rate Plans selected
		if (arrRatePlanIds.length < 1)
		{
			if (arrNoBrochures.length < 1)
			{
				$Alert("There are no Plans selected.  Please select the Plans whose Brochures you wish to email.");
				return false;
			}
			else
			{
				$Alert("The Plans you have selected do not have associated Brochures.");
				return false;
			}
		}
		
		var fncResponseHandler	=	function(objResponse)
									{
										// Render the popup
										if (objResponse.Success)
										{
											return eval(objResponse.strEval);
										}
										else
										{
											$Alert(objResponse.Message);
											return false;
										}
									};
		
		var	fncJSONRequest		=	function(arrRatePlanIds)
									{
										// Get JS code to load the Popup
										var fncJsonFunc		= jQuery.json.jsonFunction(fncResponseHandler, null, 'Rate_Plan', 'generateEmailButtonOnClick');
										fncJsonFunc(arrRatePlanIds);
									};
		
		// Check if every selected Plan has a Brochure
		if (arrNoBrochures.length)
		{
			var strYesNoHTML	= "Some of the selected Plans do not have associated brochures.<br />\n<br />\n";			
			strYesNoHTML		+= "The following Plans have brochures:<br />\n"+strHaveBrochures+"<br />\n";
			strYesNoHTML		+= "The following Plans do not have brochures:<br />\n"+strNoBrochures+"<br />\n";
			strYesNoHTML		+= "Do you want to continue by ignoring the Plans without brochures?";
			
			var strPopupId	= "RatePlan_Email_YesNo";
			Vixen.Popup.YesNoCancel(strYesNoHTML, fncJSONRequest, function(){Vixen.Popup.Close}, null, null, strPopupId, "Plans without Brochures");
		}
		else
		{
			fncJSONRequest(arrRatePlanIds);
		}
	}
}

// instanciate the objects
if (Vixen.AvailablePlansPage == undefined)
{
	Vixen.AvailablePlansPage = new VixenAvailablePlansPageClass;
}
