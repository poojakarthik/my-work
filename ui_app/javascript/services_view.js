//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// services_view.js
//----------------------------------------------------------------------------//
/**
 * services_view
 *
 * javascript required of the "View Services" popup webpage
 *
 * javascript required of the "View Services" popup webpage
 * 
 *
 * @file		rate_group_add.js
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
	this.ViewServicesPopupOnClose = function(objarrServiceDetails)
	{
		document.getElementById(objarrServiceDetails.Id).innerHTML= objarrServiceDetails.Name;
	}
}

// instanciate the objects
if (Vixen.ServicesView == undefined)
{
	Vixen.ServicesView = new VixenServicesViewClass;
}
