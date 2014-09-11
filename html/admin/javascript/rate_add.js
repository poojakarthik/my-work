//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_add.js
//----------------------------------------------------------------------------//
/**
 * rate_add
 *
 * javascript required of the "Add Rate" popup webpage
 *
 * javascript required of the "Add Rate" popup webpage
 * 
 *
 * @file		rate_add.js
 * @language	Javascript
 * @package		ui_app
 * @author		Ross Mullen
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRateAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRateAddClass
 *
 * Encapsulates all event handling required of the "Add Rate" popup webpage
 *
 * Encapsulates all event handling required of the "Add Rate" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRateAddClass
 * 
 */
function VixenRateAddClass()
{
	// Stores the Id of the popup.  This is required for the methods: SaveAsDraft, Commit and Close
	this.strPopupId = "";

	//------------------------------------------------------------------------//
	// InitialiseForm
	//------------------------------------------------------------------------//
	/**
	 * InitialiseForm
	 *
	 * Prepares the form when initially loaded
	 *
	 * Prepares the form when initially loaded
	 * Responsible for registering event listeners for the various controls in the popup, that need them
	 *
	 * @param	string	strPopupId		This is required for the methods SaveAsDraft, Commit and Close
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseForm = function(strPopupId)
	{
		this.strPopupId = strPopupId;
		
		// Override the width of the Name and Description fields
		document.getElementById("Rate.StartTime").disabled	= true;
		document.getElementById("Rate.EndTime").disabled	= true;
		document.getElementById("Rate.Duration").disabled	= true;
		document.getElementById("Rate.Fleet").disabled		= true;
		
		document.getElementById("Rate.StartTime").style.color	= "#000000";
		document.getElementById("Rate.EndTime").style.color		= "#000000";
		document.getElementById("Rate.Duration").style.color	= "#000000";
		document.getElementById("Rate.Fleet").style.color		= "#000000";

		var elmPassThroughCheckbox = document.getElementById("Rate.PassThrough");

		elmPassThroughCheckbox.addEventListener("change", PassThroughOnChange, false);
		
		// Initialise what is visible on the form
		PassThroughOnChange();
		
		// Register listeners for the various radio buttons
		// Standard Rate stuff
		document.getElementById("Radio_StdCharge").addEventListener("change", StdChargeTypeOnChange, false);
		document.getElementById("Radio_StdMarkup").addEventListener("change", StdChargeTypeOnChange, false);
		document.getElementById("Radio_StdPercentage").addEventListener("change", StdChargeTypeOnChange, false);
		
		// Capping type
		document.getElementById("Radio_NoCap").addEventListener("change", CapTypeOnChange, false);
		document.getElementById("Radio_CapUnits").addEventListener("change", CapTypeOnChange, false);
		document.getElementById("Radio_CapCost").addEventListener("change", CapTypeOnChange, false);

		// Cap limitting type
		document.getElementById("Radio_NoCapLimit").addEventListener("change", CapLimitTypeOnChange, false);
		document.getElementById("Radio_CapCostLimit").addEventListener("change", CapLimitTypeOnChange, false);
		document.getElementById("Radio_CapUsageLimit").addEventListener("change", CapLimitTypeOnChange, false);
		
		// Excess Rate stuff
		document.getElementById("Radio_ExsCharge").addEventListener("change", ExsChargeTypeOnChange, false);
		document.getElementById("Radio_ExsMarkup").addEventListener("change", ExsChargeTypeOnChange, false);
		document.getElementById("Radio_ExsPercentage").addEventListener("change", ExsChargeTypeOnChange, false);
		
		// Register a listener for the Rate.Untimed checkbox
		document.getElementById("Rate.Untimed").addEventListener("change", UntimedOnChange, false);
		
		// Register a listener for the Rate.StdUnits textbox
		document.getElementById("Rate.StdUnits").addEventListener("blur", StdUnitsOnChange, false);
		
		// Register a listener for the Rate.ExsUnits textbox
		document.getElementById("Rate.ExsUnits").addEventListener("blur", ExsUnitsOnChange, false);
		
		// Initialise what fields should be enabled and disabled
		StdChargeTypeOnChange();
		CapTypeOnChange();
		CapLimitTypeOnChange();
		ExsChargeTypeOnChange();
		UntimedOnChange();
		StdUnitsOnChange();
		ExsUnitsOnChange();
	}
	
	function StdUnitsOnChange()
	{
		var elmStdRatePerUnitSuffix	= document.getElementById("RateAdd_StdRatePerUnitSuffix");
		var elmStdMarkupSuffix 		= document.getElementById("RateAdd_StdMarkupSuffix");
		var intStdUnits				= parseInt(document.getElementById("Rate.StdUnits").value);
		
		//var strUnitSuffix = document.getElementById("RateAdd_StdUnitSuffix").innerHTML;
		var strUnitSuffix = document.getElementById("RateAdd_StdUnitSuffix").getAttribute("UnitSuffix");
		
		if (isNaN(intStdUnits))
		{
			// The user did not enter a valid integer as the Standard Billing Units
			elmStdRatePerUnitSuffix.innerHTML	= "Per X " + strUnitSuffix;
			elmStdMarkupSuffix.innerHTML		= "Per X " + strUnitSuffix;
		}
		else
		{
			// StdUnits is valid
			elmStdRatePerUnitSuffix.innerHTML	= "Per " + intStdUnits + " " + strUnitSuffix;
			elmStdMarkupSuffix.innerHTML		= "Per " + intStdUnits + " " + strUnitSuffix;
		}
	}

	function ExsUnitsOnChange()
	{
		var elmExsRatePerUnitSuffix	= document.getElementById("RateAdd_ExsRatePerUnitSuffix");
		var elmExsMarkupSuffix 		= document.getElementById("RateAdd_ExsMarkupSuffix");
		var intExsUnits				= parseInt(document.getElementById("Rate.ExsUnits").value);
		
		var strUnitSuffix = document.getElementById("RateAdd_StdUnitSuffix").getAttribute("UnitSuffix");
		
		if (isNaN(intExsUnits))
		{
			// The user did not enter a valid integer as the Excess Billing Units
			elmExsRatePerUnitSuffix.innerHTML	= "Per X " + strUnitSuffix + " beyond cap limit";
			elmExsMarkupSuffix.innerHTML		= "Per X " + strUnitSuffix + " beyond cap limit";
		}
		else
		{
			// ExsUnits is valid
			elmExsRatePerUnitSuffix.innerHTML	= "Per " + intExsUnits + " " + strUnitSuffix + " beyond cap limit";
			elmExsMarkupSuffix.innerHTML		= "Per " + intExsUnits + " " + strUnitSuffix + " beyond cap limit";
		}
	}


	function StdChargeTypeOnChange()
	{
		var bolDisableExsMarkup = false;
		
		if (document.getElementById('Radio_StdCharge').checked)
		{
			// Disable the Rate.StdMarkup and Rate.StdPercentage textboxes
			document.getElementById('Rate.StdMarkup').disabled		= true;
			document.getElementById('Rate.StdPercentage').disabled	= true;
			
			// Hide the RateAdd_StdMarkupSuffix label
			document.getElementById("RateAdd_StdMarkupSuffix").style.display = "none";
			
			// Enable the Rate.StdRatePerUnit textbox
			document.getElementById('Rate.StdRatePerUnit').disabled	= false;
			
			// Display the RateAdd_StdRatePerUnitSuffix label
			document.getElementById("RateAdd_StdRatePerUnitSuffix").style.display = "inline";
			
			// Enable the ExsMarkup and ExsPercentage radio buttons
			document.getElementById("Radio_ExsMarkup").disabled = false;
			document.getElementById("Radio_ExsPercentage").disabled = false;
			ExsChargeTypeOnChange();
		}
		else if (document.getElementById('Radio_StdMarkup').checked)
		{
			// Disable the Rate.StdRatePerUnit and Rate.StdPercentage textboxes
			document.getElementById('Rate.StdRatePerUnit').disabled	= true;
			document.getElementById('Rate.StdPercentage').disabled	= true;

			// Hide the RateAdd_StdRatePerUnitSuffix label
			document.getElementById("RateAdd_StdRatePerUnitSuffix").style.display = "none";

			// Enable the Rate.StdMarkup textbox
			document.getElementById('Rate.StdMarkup').disabled		= false;
			
			// Display the RateAdd_StdMarkupSuffix label
			document.getElementById("RateAdd_StdMarkupSuffix").style.display = "inline";
			
			// Flag that Exs Markup on Cost options must be disabled
			bolDisableExsMarkup = true;
		}
		else if (document.getElementById('Radio_StdPercentage').checked)
		{
			// Disable the Rate.StdRatePerUnit and Rate.StdMarkup textboxes
			document.getElementById('Rate.StdRatePerUnit').disabled	= true;
			document.getElementById('Rate.StdMarkup').disabled	= true;

			// Hide the RateAdd_StdRatePerUnitSuffix label
			document.getElementById("RateAdd_StdRatePerUnitSuffix").style.display = "none";
			// Hide the RateAdd_StdMarkupSuffix label
			document.getElementById("RateAdd_StdMarkupSuffix").style.display = "none";

			// Enable the Rate.StdPercentage textbox
			document.getElementById('Rate.StdPercentage').disabled	= false;
			
			// Flag that Exs Markup on Cost options must be disabled
			bolDisableExsMarkup = true;
		}
		if (bolDisableExsMarkup)
		{
			// Disable the ExsMarkup and ExsPercentage radio buttons and textboxes 
			// as you can't specify both a Standard markup on cost, and an Excess markup on cost
			var elmExsMarkupRadio		= document.getElementById("Radio_ExsMarkup");
			var elmExsPercentageRadio	= document.getElementById("Radio_ExsPercentage");
			var elmNoCapRadio			= document.getElementById("Radio_NoCap");
			var elmCapUsageLimitRadio	= document.getElementById("Radio_CapUsageLimit");
			
			// Check if either ExsMarkup or ExsPercentage had been selected and 
			// if so, warn the user that they cannot specify both a Standard 
			// markup on cost, and an Excess markup on cost
			if ((elmNoCapRadio.checked == false) && (elmCapUsageLimitRadio.checked == true) && (elmExsMarkupRadio.checked == true || elmExsPercentageRadio.checked == true))
			{
				Vixen.Popup.Alert("WARNING: You cannot specify both a Standard Markup on Cost and an Excess Markup on Cost as the original cost will be added to the charge twice");
			}
			
			document.getElementById("Radio_ExsCharge").checked = true;
			elmExsMarkupRadio.disabled = true;
			elmExsPercentageRadio.disabled = true;
			ExsChargeTypeOnChange();
		}
	}
	
	function CapTypeOnChange()
	{
		if (document.getElementById('Radio_NoCap').checked)
		{
			// Disable the Rate.CapUnits and Rate.CapCost textboxes
			document.getElementById('Rate.CapUnits').disabled	= true;
			document.getElementById('Rate.CapCost').disabled	= true;
			
			// Hide details that aren't required when there is no capping
			document.getElementById('CapDetailDiv').style.display = 'none';
			document.getElementById('ExcessDetailDiv').style.display = 'none';
		}
		else if (document.getElementById('Radio_CapUnits').checked)
		{
			// Enable the Rate.CapUnits textbox
			document.getElementById('Rate.CapUnits').disabled	= false;
			
			// Disable the Rate.CapCost textbox
			document.getElementById('Rate.CapCost').disabled	= true;

			// Show details required for a cap
			document.getElementById('CapDetailDiv').style.display = 'inline';
			
			// If the CapUsage radio button is currently selected, then you also have to expand the ExcessDetailDiv
			if (document.getElementById('Radio_CapUsageLimit').checked == true)
			{
				document.getElementById('ExcessDetailDiv').style.display = 'inline';
			}
		}
		else if (document.getElementById('Radio_CapCost').checked)
		{
			// Enable the Rate.CapCost textbox
			document.getElementById('Rate.CapCost').disabled	= false;
			
			// Disable the Rate.CapUnits textbox
			document.getElementById('Rate.CapUnits').disabled	= true;

			// Show details required for a cap
			document.getElementById('CapDetailDiv').style.display = 'inline';
			
			// If the CapUsage radio button is currently selected, then you also have to expand the ExcessDetailDiv
			if (document.getElementById('Radio_CapUsageLimit').checked == true)
			{
				// It is
				document.getElementById('ExcessDetailDiv').style.display = 'inline';
			}
		}
	}
	
	function CapLimitTypeOnChange()
	{
		if (document.getElementById('Radio_NoCapLimit').checked)
		{
			// Disable the Rate.CapLimit and Rate.CapUsage textboxes
			document.getElementById('Rate.CapLimit').disabled	= true;
			document.getElementById('Rate.CapUsage').disabled	= true;

			// Show/hide details required for when "No Cap Limits" is selected
			document.getElementById('ExcessDetailDiv').style.display = 'none';
			document.getElementById('ExsFlagfallDiv').style.display = 'none';
		}
		else if (document.getElementById('Radio_CapCostLimit').checked)
		{
			// Enable the Rate.CapLimit textbox
			document.getElementById('Rate.CapLimit').disabled	= false;
			
			// Disable the Rate.CapUsage textbox
			document.getElementById('Rate.CapUsage').disabled	= true;

			// Show/hide details required for when "Cap Limit" is selected
			document.getElementById('ExcessDetailDiv').style.display = 'none';
			document.getElementById('ExsFlagfallDiv').style.display = 'inline';
			
		}
		else if (document.getElementById('Radio_CapUsageLimit').checked)
		{
			// Enable the Rate.CapUsage textbox
			document.getElementById('Rate.CapUsage').disabled	= false;
			
			// Disable the Rate.CapLimit textbox
			document.getElementById('Rate.CapLimit').disabled	= true;

			// Show/hide details required for when "Cap Usage" is selected
			document.getElementById('ExcessDetailDiv').style.display = 'inline';
			document.getElementById('ExsFlagfallDiv').style.display = 'inline';
		}
	}
	
	function ExsChargeTypeOnChange()
	{
		if (document.getElementById('Radio_ExsCharge').checked)
		{
			// Disable the Rate.ExsMarkup and Rate.ExsPercentage textboxes
			document.getElementById('Rate.ExsMarkup').disabled		= true;
			document.getElementById('Rate.ExsPercentage').disabled	= true;

			// Hide the RateAdd_ExsMarkupSuffix label
			document.getElementById("RateAdd_ExsMarkupSuffix").style.display = "none";

			// Enable the Rate.ExsRatePerUnit textbox
			document.getElementById('Rate.ExsRatePerUnit').disabled	= false;

			// Display the RateAdd_ExsRatePerUnitSuffix label
			document.getElementById("RateAdd_ExsRatePerUnitSuffix").style.display = "inline";
		}
		else if (document.getElementById('Radio_ExsMarkup').checked)
		{
			// Disable the Rate.ExsRatePerUnit and Rate.ExsPercentage textboxes
			document.getElementById('Rate.ExsRatePerUnit').disabled	= true;
			document.getElementById('Rate.ExsPercentage').disabled	= true;

			// Hide the RateAdd_ExsRatePerUnitSuffix label
			document.getElementById("RateAdd_ExsRatePerUnitSuffix").style.display = "none";

			// Enable the Rate.ExsMarkup textbox
			document.getElementById('Rate.ExsMarkup').disabled		= false;

			// Display the RateAdd_ExsMarkupSuffix label
			document.getElementById("RateAdd_ExsMarkupSuffix").style.display = "inline";
		}
		else if (document.getElementById('Radio_ExsPercentage').checked)
		{
			// Disable the Rate.ExsRatePerUnit and Rate.ExsMarkup textboxes
			document.getElementById('Rate.ExsRatePerUnit').disabled	= true;
			document.getElementById('Rate.ExsMarkup').disabled		= true;

			// Hide the RateAdd_ExsRatePerUnitSuffix label
			document.getElementById("RateAdd_ExsRatePerUnitSuffix").style.display = "none";
			
			// Hide the RateAdd_ExsMarkupSuffix label
			document.getElementById("RateAdd_ExsMarkupSuffix").style.display = "none";

			// Enable the Rate.ExsPercentage textbox
			document.getElementById('Rate.ExsPercentage').disabled	= false;
		}
	}
	
	// This should only be visible from within this class, so we don't have to worry about its name conflicting with any
	// other javascript functions that are loaded in memory.  I would have made it a method of the class, but then
	// it wouldn't work properly as an event listener
	function PassThroughOnChange()
	{
		var elmPassThroughCheckbox	= document.getElementById("Rate.PassThrough");
		var elmRateUntimedContainer	= document.getElementById("ContainerDiv_RateUntimed");
		var elmRateUntimed			= document.getElementById("Rate.Untimed");
		
		if (elmPassThroughCheckbox.checked)
		{
			// The Rate is a passthrough rate
			// Hide Rate Charge and Capping detials
			document.getElementById('RateDetailDiv').style.display = 'none';
			document.getElementById('CapMainDetailDiv').style.display = 'none';
			
			// Hide the Rate.Untimed container div
			elmRateUntimedContainer.style.display = "none";
		}
		else
		{
			// The Rate is not a passthrough rate
			// Show Rate Charge and Capping details
			document.getElementById('RateDetailDiv').style.display = 'inline';
			document.getElementById('CapMainDetailDiv').style.display = 'inline';
			
			// Show the Rate.Untimed checkbox and fire its event listener
			elmRateUntimedContainer.style.display = "inline";
			UntimedOnChange();
		}
	}
	
	function UntimedOnChange()
	{
		if (document.getElementById("Rate.Untimed").checked)
		{
			// The rate is untimed
			document.getElementById('RateDetailDiv').style.display = 'none';
			document.getElementById('CapMainDetailDiv').style.display = 'none';
		}
		else
		{
			// The rate is timed
			document.getElementById('RateDetailDiv').style.display = 'inline';
			document.getElementById('CapMainDetailDiv').style.display = 'inline';
		}
	}
	
	//------------------------------------------------------------------------//
	// SaveAsDraft
	//------------------------------------------------------------------------//
	/**
	 * SaveAsDraft
	 *
	 * This is executed when the SaveAsDraft button is clicked and then the user confirms that they want to go through with it
	 *  
	 * This is executed when the SaveAsDraft button is clicked and then the user confirms that they want to go through with it
	 * It submits the form
	 *
	 * @return	void
	 * @method
	 */
	this.SaveAsDraft = function()
	{
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		//Vixen.Ajax.SendForm("VixenForm_AddRate", "Save as Draft", "Rate", "Add", "", document.getElementById("AddRatePopupId").value);
		Vixen.Ajax.SendForm("VixenForm_AddRate", "Save as Draft", "Rate", "Add", "", this.strPopupId);
	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit
	 *
	 * This is executed when the Commit button is clicked and then the user confirms that they want to go through with it
	 *  
	 * This is executed when the Commit button is clicked and then the user confirms that they want to go through with it
	 * It submits the form
	 *
	 * @return	void
	 * @method
	 */
	this.Commit = function()
	{
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		//TODO! I shouldn't have to hardcode the id of the form.  This isn't very elegant because the form's id and the 
		//AppTemplate and method are defined in more than one place
		Vixen.Ajax.SendForm("VixenForm_AddRate", "Commit", "Rate", "Add", "", this.strPopupId);
	}

	//------------------------------------------------------------------------//
	// Close
	//------------------------------------------------------------------------//
	/**
	 * Close
	 *
	 * This is executed when the Cancel button is clicked and then the user confirms that they want to go through with it
	 *  
	 * This is executed when the Cancel button is clicked and then the user confirms that they want to go through with it
	 *
	 * @return	void
	 * @method
	 */
	this.Close = function()
	{
		// The PopupId, containing this form, has been rendered as a hidden
		Vixen.Popup.Close(this.strPopupId);
	}
}


// instantiate the object
if (Vixen.RateAdd == undefined)
{
	Vixen.RateAdd = new VixenRateAddClass;
}
