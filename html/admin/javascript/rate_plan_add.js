//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_plan_add.js
//----------------------------------------------------------------------------//
/**
 * rate_plan_add
 *
 * javascript required of the "Add Rate Plan" webpage
 *
 * javascript required of the "Add Rate Plan" webpage
 * 
 *
 * @file		rate_plan_add.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRatePlanAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRatePlanAddClass
 *
 * Encapsulates all event handling required of the "Add Rate Plan" webpage
 *
 * Encapsulates all event handling required of the "Add Rate Plan" webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRatePlanAddClass
 * 
 */
function VixenRatePlanAddClass()
{
	this.elmScalable					= null;
	this.elmScalableDetailsContainter	= null;
		
	// Initialises the form
	this.Initialise = function()
	{
		this.elmScalable					= $ID("ScalableCheckbox");
		this.elmScalableDetailsContainter	= $ID("Scalable_ExtraDetailsContainer");
		
		this.elmContractTerm				= $ID("RatePlan.ContractTerm");
		this.elmContactDetailsContainter	= $ID("Contract_ExtraDetailsContainer");
		
		// Add the Sections as Tab Pages
		this.oTabGroup				= {};
		this.oTabGroup.domElement	= document.createElement('div');
		this.oTabGroup.oControl		= new Control_Tab_Group(this.oTabGroup.domElement, true);
		$ID('VixenForm_AddPlan').appendChild(this.oTabGroup.domElement);
		
		this.oTabGroup.oControl.addTab('plan_details', new Control_Tab('Plan Details', $ID('RatePlanDetailsId'), '../admin/img/template/plan.png'));
		
		//alert("'RateGroupsDiv' does " + ($ID('RateGroupsDiv') ? '' : 'not ') + 'exist');
		//this.oTabGroup.oControl.addTab('rate_groups', new Control_Tab('Rate Groups', $ID('RateGroupsDiv'), '../admin/img/template/plan.png'));
		//this.oTabGroup.oControl.addTab('discounts', new Control_Tab('Discounts', document.createElement('div'), '../admin/img/template/money_dollar.png'));
		
		this.oTabGroup.oControl.switchToTab('plan_details');
	}
	
	// Shows/Hides the Extra details that are required if the plan is scalable
	this.ScalableOnChange = function()
	{
		if (this.elmScalable.checked)
		{
			// Show the Scalable extra details
			this.elmScalableDetailsContainter.style.display		= "block";
			this.elmScalableDetailsContainter.style.visibility	= "visible";
		}
		else
		{
			// Hide the Scalable extra details
			this.elmScalableDetailsContainter.style.display		= "none";
			this.elmScalableDetailsContainter.style.visibility	= "hidden";
		}
	}
	
	// Shows/Hides the Extra details that are required if it's a Contract Plan
	this.ContractTermOnChange = function()
	{
		if (this.elmContractTerm.value > 0.0)
		{
			// Show the Contract extra details
			this.elmContactDetailsContainter.style.display		= "block";
			this.elmContactDetailsContainter.style.visibility	= "visible";
		}
		else
		{
			// Hide the Contract extra details
			this.elmContactDetailsContainter.style.display		= "none";
			this.elmContactDetailsContainter.style.visibility	= "hidden";
		}
	}
	
	//------------------------------------------------------------------------//
	// ChangeServiceType
	//------------------------------------------------------------------------//
	/**
	 * ChangeServiceType
	 *
	 * Event handler for when the Service Type is chosen from the Service Type Combobox
	 *  
	 * Event handler for when the Service Type is chosen from the Service Type Combobox
	 *
	 * @param	int		intServiceType		Id of the ServiceType selected
	 * @param	bool	bolSetDefaults		optional, If true then the CarrierFullService Combobox
	 *										and the CarrierPreselection Combobox will be changed to
	 *										their default values
	 *
	 * @return	void
	 * @method
	 */
	this.ChangeServiceType = function(intServiceType, bolSetDefaults)
	{
		// Check if default values should be set, based on the ServiceType selected
		if (bolSetDefaults)
		{
			// Check if this ServiceType has default values for the CarrierFullService and CarrierPreselection combos
			var elmServiceTypeCombo 			= document.getElementById("ServiceTypeCombo");
			var intDefaultCarrierFullService	= elmServiceTypeCombo.options[elmServiceTypeCombo.selectedIndex].getAttribute('CarrierFullService');
			var intDefaultCarrierPreselection	= elmServiceTypeCombo.options[elmServiceTypeCombo.selectedIndex].getAttribute('CarrierPreselection');
			
			// Set the default value for the CarrierFullService combo
			var elmCarrierFullServiceCombo = document.getElementById("CarrierFullServiceCombo");
			elmCarrierFullServiceCombo.selectedIndex = 0;
			if (intDefaultCarrierFullService != null)
			{
				// A default CarrierFullService has been specified.  Update the CarrierFullService Combobox
				for (var i=0; i < elmCarrierFullServiceCombo.length; i++)
				{
					if (elmCarrierFullServiceCombo.options[i].value == intDefaultCarrierFullService)
					{
						// Found the option
						elmCarrierFullServiceCombo.selectedIndex = i;
						break;
					}
				}
			}
			
			// Set the default value for the CarrierPreselection combo
			var elmCarrierPreselectionCombo = document.getElementById("CarrierPreselectionCombo");
			elmCarrierPreselectionCombo.selectedIndex = 0;
			if (intDefaultCarrierPreselection != null)
			{
				// A default CarrierPreselection has been specified.  Update the CarrierPreselection Combobox
				for (var i=0; i < elmCarrierPreselectionCombo.length; i++)
				{
					if (elmCarrierPreselectionCombo.options[i].value == intDefaultCarrierPreselection)
					{
						// Found the option
						elmCarrierPreselectionCombo.selectedIndex = i;
						break;
					}
				}
			}
		}
		
		// Load the RateGroup table applicable to this ServiceType
		var elmRatePlanId = document.getElementById("RatePlan.Id");
		var elmBaseRatePlanId = document.getElementById("BaseRatePlan.Id");
		
		var objObjects = {};
		objObjects.RatePlan = {};
		objObjects.RatePlan.ServiceType = intServiceType;
		
		if (elmRatePlanId.value > 0)
		{
			// We are displaying a draft rate group, and want to show all its associated Rate Groups
			objObjects.RatePlan.Id = elmRatePlanId.value;
		}
		if (elmBaseRatePlanId != null)
		{
			// pass the BaseRatePlan.Id as well (Note you will only ever have elmRatePlanId.value > 0 or elmBaseRatePlanId.value > 0, never both)
			objObjects.BaseRatePlan = {};
			objObjects.BaseRatePlan.Id = elmBaseRatePlanId.value;
		}
		
		//Vixen.Ajax.CallAppTemplate('Plan', 'GetRateGroupsForm', objObjects);
		Vixen.Ajax.CallAppTemplate('Plan', 'GetRecordTypesForms', objObjects);
	}
	
	this.setRateGroupTabVisible	= function(bVisible)
	{
		if (bVisible)
		{
			// SHOW
			// Rate Groups
			if (!this.oTabGroup.oControl.getTab('rate_groups'))
			{
				this.oTabGroup.oControl.addTab('rate_groups', new Control_Tab('Rate Groups', $ID('RateGroupsDiv'), '../admin/img/template/plan.png'));
			}
			else
			{
				this.oTabGroup.oControl.getTab('rate_groups').getButton().show();
			}
		}
		else
		{
			// HIDE
			// Rate Groups
			//this.oTabGroup.oControl.removeTab('rate_groups');
			this.oTabGroup.oControl.getTab('rate_groups').getButton().hide();
		}
	}
	
	this.setDiscountTabVisible	= function(bVisible)
	{
		if (bVisible)
		{
			// SHOW
			// Discounts
			if (!this.oTabGroup.oControl.getTab('discounts'))
			{
				this.oTabGroup.oControl.addTab('discounts', new Control_Tab('Discounts', $ID('DiscountsDiv'), '../admin/img/template/money_dollar.png'));
			}
			else
			{
				this.oTabGroup.oControl.getTab('discounts').getButton().show();
			}
		}
		else
		{
			// HIDE
			// Discounts
			//this.oTabGroup.oControl.removeTab('discounts');
			this.oTabGroup.oControl.getTab('discounts').getButton().hide();
		}
	}
	
	//------------------------------------------------------------------------//
	// EditRateGroup
	//------------------------------------------------------------------------//
	/**
	 * EditRateGroup
	 *
	 * This is executed when the user wants to edit a draft Rate Group
	 *  
	 * This is executed when the user wants to edit a draft Rate Group
	 * Only draft RateGroups can be edited
	 *
	 * @param	int		intRecordType		RecordType Id of the rate group
	 * @param	bool	bolFleet			TRUE if the Rate Group to edit is is a fleet Rate Group
	 *
	 * @return	void
	 * @method
	 */
	this.EditRateGroup = function(intRecordType, bolFleet)
	{
		var elmRateGroupCombo;
		var strRateGroupCombo;
		// Find the Rate Group that the user wants to edit
		// Only draft Rate Groups can be editted
		if (bolFleet)
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".FleetRateGroupId";
		}
		else
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".RateGroupId";
		}

		elmRateGroupCombo = document.getElementById(strRateGroupCombo);
		
		if (elmRateGroupCombo.value == 0)
		{
			// A Rate Group has not been selected
			Vixen.Popup.Alert("No Rate Group has been selected");
			return;
		}
		
		var elmRateGroupOption = elmRateGroupCombo.options[elmRateGroupCombo.selectedIndex];
		
		// Only allow the user to edit the Rate Group if it is a draft rate group
		if (elmRateGroupOption.getAttribute('draft'))
		{
			// The Rate Group is a draft. Open the Edit Rate Group popup
			var objObjects						= {};
			objObjects.RateGroup				= {};
			objObjects.RateGroup.Id				= elmRateGroupOption.value;
			objObjects.CallingPage				= {};
			objObjects.CallingPage.AddRatePlan	= true;
			
			Vixen.Popup.ShowAjaxPopup("AddRateGroupPopup", "large", "Edit Rate Group", "RateGroup", "Add", objObjects);
		}
		else
		{
			Vixen.Popup.Alert("The currently selected Rate Group is not a draft");
		}
		
	}
	
	//------------------------------------------------------------------------//
	// AddRateGroup
	//------------------------------------------------------------------------//
	/**
	 * AddRateGroup
	 *
	 * This is executed when the user wants to add a Rate Group
	 *  
	 * This is executed when the user wants to add a Rate Group
	 *
	 * @param	int		intRecordType		RecordType Id that the new Rate Group will be associated with
	 * @param	bool	bolFleet			[optional, defaults to FALSE] TRUE if the new Rate Group is to be a fleet Rate Group
	 *
	 * @return	void
	 * @method
	 */
	this.AddRateGroup = function(intRecordType, bolFleet)
	{
		var objObjects = {};
		var elmRateGroupCombo;
		var strRateGroupCombo;
		
		// Set the default value for bolFleet, if it has not been specified
		bolFleet = (bolFleet == null) ? FALSE : bolFleet;

		// If a RateGroup is currently selected, then have the "Add Rate Group" page load it, so that the
		// new RateGroup will be based on the old one

		// Find if a Rate Group has been selected to base the new one on
		if (bolFleet)
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".FleetRateGroupId";
		}
		else
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".RateGroupId";
		}

		elmRateGroupCombo = document.getElementById(strRateGroupCombo);
		
		if (elmRateGroupCombo.value != 0)
		{
			// A RateGroup has been selected, so set up the objects to send to the AppTemplate
			objObjects.BaseRateGroup = {};
			objObjects.BaseRateGroup.Id = elmRateGroupCombo.value;
		}
		else
		{
			// A RateGroup has not been selected.  The "Add Rate Group" page will require the RecordType and Fleet flag of the new RateGroup
			objObjects.RecordType = {};
			objObjects.RecordType.Id = intRecordType;
			objObjects.RateGroup = {};
			objObjects.RateGroup.Fleet = bolFleet;
		}
		
		// Set up remaining data that needs to be sent to the "Add Rate Group" page
		objObjects.CallingPage = {};
		objObjects.CallingPage.AddRatePlan = true;
		
		// Call the "Add Rate Group" page
		Vixen.Popup.ShowAjaxPopup("AddRateGroupPopup", "large", "Add New Rate Group", "RateGroup", "Add", objObjects);
	}
	
	//------------------------------------------------------------------------//
	// ExportRateGroup
	//------------------------------------------------------------------------//
	/**
	 * ExportRateGroup
	 *
	 * This is executed when the user wants to export a Rate Group
	 *  
	 * This is executed when the user wants to export a Rate Group
	 *
	 * @param	int		intRecordType		RecordType Id of the Rate Group to export
	 * @param	bool	bolIsFleet			TRUE if the Rate Group to export is a fleet Rate Group. FALSE for normal RateGroups
	 *
	 * @return	void
	 * @method
	 */
	this.ExportRateGroup = function(intRecordType, bolIsFleet)
	{
		var elmRateGroupCombo	= null;;
		var strRateGroupCombo	= "";
		var strGetVariables		= "";
		
		// If a RateGroup is currently selected, then the user will export this RateGroup
		// If a RateGroup isn't selected, then the user will export a skeleton RateGroup CSV file specific to the RecordType declared

		// Find if a Rate Group has been selected
		if (bolIsFleet)
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".FleetRateGroupId";
		}
		else
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".RateGroupId";
		}

		elmRateGroupCombo = document.getElementById(strRateGroupCombo);
		
		if (elmRateGroupCombo.value != 0)
		{
			// A RateGroup has been selected, so set up the objects to send to the AppTemplate
			strGetVariables = "RateGroup.Id=" + elmRateGroupCombo.value;
		}
		else
		{
			// A RateGroup has not been selected.  A skeleton csv file for the given RecordType will be exported
			strGetVariables = "RecordType.Id=" + intRecordType + "&RateGroup.Fleet=" + ((bolIsFleet)? "1": "0");
		}
		
		// Call the Export RateGroup functionality
		window.location = "flex.php/RateGroup/Export/?" + strGetVariables;
	}

	//------------------------------------------------------------------------//
	// UpdateRateGroupCombo
	//------------------------------------------------------------------------//
	/**
	 * UpdateRateGroupCombo
	 *
	 * Updates the appropriate RateGroup combobox with the supplied RateGroup and selects it
	 *  
	 * Updates the appropriate RateGroup combobox with the supplied RateGroup and selects it
	 *
	 * @param	object	objRateGroup	Must contain the following RateGroup properties
	 *									Id, Name, Description, RecordType, Fleet, Archived
	 *
	 * @return	void
	 * @method
	 */
	this.UpdateRateGroupCombo = function(objRateGroup)
	{
		var strComboId;
		
		//alert("RateGroupId = " + objRateGroup.Id + " Name = " + objRateGroup.Name + " RecordType = " + objRateGroup.RecordType + " Fleet = " + objRateGroup.Fleet.toString());
		
		if (objRateGroup.Fleet)
		{
			// The rate group is a fleet rate group
			strComboId = "RateGroup" + objRateGroup.RecordType + ".FleetRateGroupId";
		}
		else
		{
			// The rate group is not a fleet rate group
			strComboId = "RateGroup" + objRateGroup.RecordType + ".RateGroupId";
		}
		
		// Get the combo box associated with this particular record type
		var elmRateGroupCombo = document.getElementById(strComboId);
		if (elmRateGroupCombo == undefined)
		{
			// The new rate group does not belong to any record types associated with this service type
			return;
		}
		
		// create a new option element
		var elmNewRateGroupOption		= document.createElement('option');
		elmNewRateGroupOption.value		= objRateGroup.Id;
		elmNewRateGroupOption.text		= objRateGroup.Name;
		elmNewRateGroupOption.selected	= TRUE;
		
		if (objRateGroup.Draft)
		{
			elmNewRateGroupOption.text = "DRAFT: " + elmNewRateGroupOption.text;
			elmNewRateGroupOption.setAttribute('draft', 'draft');
		}

		// Remove the old option from the combo box, if it exists
		for (var i=0; i < elmRateGroupCombo.options.length; i++)
		{
			if (elmRateGroupCombo.options[i].value == elmNewRateGroupOption.value)
			{
				// Destroy the old one
				elmRateGroupCombo.removeChild(elmRateGroupCombo.options[i]);
				break;
			}
		}
		
		// Stick it in the combo so that the alphabetical order of the options is preserved
		// i starts at 1 because we don't want to do a comparision between the new option, and the blank option
		for (var i=1; i < elmRateGroupCombo.options.length; i++)
		{
			if (elmNewRateGroupOption.text < elmRateGroupCombo.options[i].text)
			{
				// insert the new option just before the current one
				elmRateGroupCombo.insertBefore(elmNewRateGroupOption, elmRateGroupCombo.options[i]);
				elmRateGroupCombo.selectedIndex = elmNewRateGroupOption.index;
				elmRateGroupCombo.focus();
				return;
			}
		}
		
		// The option should either be the last in the list, or there are no other options in the list.  Append the option to the end of the list
		elmRateGroupCombo.appendChild(elmNewRateGroupOption);
		elmRateGroupCombo.selectedIndex = elmNewRateGroupOption.index;
		elmRateGroupCombo.focus();
	}


	//------------------------------------------------------------------------//
	// ReturnToCallingPage
	//------------------------------------------------------------------------//
	/**
	 * ReturnToCallingPage
	 *
	 * This is executed when the Cancel button is clicked and the user confirms the action
	 *  
	 * This is executed when the Cancel button is clicked and the user confirms the action
	 * If the hidden element CallingPage.Href stores am href then the browser is relocated to it.
	 * Else the browser steps back one page through its history
	 *
	 * @return	void
	 * @method
	 */
	this.ReturnToCallingPage = function()
	{
		// There should be a hidden input value called CallingPage.Href storing the href of the page that called this one
		var elmCallingPage = document.getElementById("CallingPage.Href");
		
		if (elmCallingPage.value == '')
		{
			// No calling page has been specified. Use the history object to go back one page
			window.history.go(-1);
		}
		else
		{
			// A calling page has been specified
			location.href = elmCallingPage.value;
		}
	}
	
	//------------------------------------------------------------------------//
	// SaveAsDraft
	//------------------------------------------------------------------------//
	/**
	 * SaveAsDraft
	 *
	 * This is executed when the SaveAsDraft button is clicked and the user confirms the action
	 *  
	 * This is executed when the SaveAsDraft button is clicked and the user confirms the action
	 * It posts the form's input elements via ajax, effectively saving the RatePlan as a Draft
	 *
	 *
	 * @return	void
	 * @method
	 */
	this.SaveAsDraft = function()
	{
		// Execute AppTemplatePlan->Add() and make sure all the input elements of the form are sent
		Vixen.Ajax.SendForm("VixenForm_AddPlan", "Save as Draft", "Plan", "Add");
	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit
	 *
	 * This is executed when the Commit button is clicked and the user confirms the action
	 *  
	 * This is executed when the Commit button is clicked and the user confirms the action
	 * It posts the form's input elements via ajax, effectively saving the RatePlan as a permanent, usable Rate Plan
	 *
	 *
	 * @return	void
	 * @method
	 */
	this.Commit = function()
	{
		// Execute AppTemplatePlan->Add() and make sure all the input elements of the form are sent
		Vixen.Ajax.SendForm("VixenForm_AddPlan", "Commit", "Plan", "Add");
	}
	
	this.aDiscounts		= [];
	this.iDiscountUID	= 0;
	
	this.addDiscount	= function()
	{
		this.iDiscountUID--;
		var oDiscount	=	{
								id				: this.iDiscountUID,
								name			: '',
								description		: '',
								charge_limit	: 0.00,
								unit_limit		: 0
							};
		this.aDiscounts.push(oDiscount);

		this._paintDiscount(oDiscount.id);
	};
	
	this.removeDiscount	= function(iDiscountId)
	{
		// Remove all instances of this Id
		$ID('rate_plan_discounts').select('tbody tr').each();
		for (var i = 0, j = this.aDiscounts.length; i < j; i++)
		{
			if (iDiscountId === this.aDiscounts[i].id)
			{
				this.aDiscounts.splice(i, 1);
			}
		}
		
		this._paintDiscount(iDiscountId);
	};
	
	this._paintDiscount	= function(iDiscountId)
	{
		// Get the Discount details
		var oDiscount;
		for (var i = 0, j = this.aDiscounts.length; i < j; i++)
		{
			if (iDiscountId === this.aDiscounts[i].id)
			{
				oDiscount	= this.aDiscounts[i];
			}
		}
		
		// Repaint Discount Combos
		for (var i = 0, aSelects = $ID('discount_record_types').select('tbody tr select '), j = aSelects.length; i < j; i++)
		{
			var domOption	= aSelects[i].select('option[value="'+iDiscountId+'"]').first();
			if (domOption)
			{
				// Option exists
				if (oDiscount === undefined)
				{
					// No Discount with this Id -- remove Option from DOM
					aSelectOptions[i].remove();
				}
				else
				{
					// Update the label
					aSelectOptions[i].innerHTML	= oDiscount.name;
				}
			}
			else if (oDiscount)
			{
				// Option doesn't exist -- create a new Option
				var domOption		= document.createElement('option');
				domOption.value		= oDiscount.id;
				domOption.innerHTML	= oDiscount.name;
				aSelects[i].appendChild(domOption);
			}
		}
	};
}

// Instantiate the objects
if (Vixen.RatePlanAdd == undefined)
{
	Vixen.RatePlanAdd = new VixenRatePlanAddClass;
}
