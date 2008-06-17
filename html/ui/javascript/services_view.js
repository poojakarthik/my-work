//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// services_view.js DEPRICATED (i think)
//----------------------------------------------------------------------------//
/**
 * services_view
 *
 * javascript required of the "View Services" popup webpage
 *
 * javascript required of the "View Services" popup webpage
 * 
 *
 * @file		services_view.js
 * @language	Javascript
 * @package		ui_app
 * @author		Ross 'He looks good in camouflage' Mullen
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServicesViewClass
//----------------------------------------------------------------------------//
/**
 * VixenServicesViewClass
 *
 * Encapsulates all event handling required of the "View Services" popup webpage
 *
 * Encapsulates all event handling required of the "View Services" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenServicesViewClass
 * 
 */
function VixenServicesViewClass()
{
	//------------------------------------------------------------------------//
	// ViewServicesPopupOnClose
	//------------------------------------------------------------------------//
	/**
	 * ViewServicesPopupOnClose
	 *
	 * Event handler for when the Plan Type is chosen from the Select Plan Combobox
	 *  
	 * Event handler for when the Plan Type is chosen from the Select Plan Combobox
	 *
	 * @param	object	objarrServiceDetails	Defines a new ServiceDetails.  It contains the properties:
	 *									Id (Service ID), Name (RatePlan)
	 * @return	void
	 * @method
	 */
	this.ViewServicesPopupOnClose = function(objEventData)
	{
		document.getElementById(objEventData.Service.Id).innerHTML = objEventData.NewRatePlan.Name;
		//document.getElementById(objarrServiceDetails.Id).innerHTML= objarrServiceDetails.Name;
	}
	
	// Initialisation (this should go in its own function which is called from within the HtmlTemplate)
	Vixen.EventHandler.AddListener("OnServicePlanChange", this.ViewServicesPopupOnClose);
}

// instanciate the objects
if (Vixen.ServicesView == undefined)
{
	Vixen.ServicesView = new VixenServicesViewClass;
}
