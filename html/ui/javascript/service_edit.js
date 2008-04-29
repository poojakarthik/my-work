//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_edit.js
//----------------------------------------------------------------------------//
/**
 * service_edit
 *
 * javascript required of the "Edit Service" and "Add Service" webpages
 *
 * javascript required of the "Edit Service" and "Add Service" webpages
 * 
 *
 * @file		service_edit.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceEditClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceEditClass
 *
 * Encapsulates all event handling required of the "Add/Edit Service" webpages
 *
 * Encapsulates all event handling required of the "Add/Edit Service" webpages
 *
 * @package	ui_app
 * @class	VixenServiceEditClass
 * 
 */
function VixenServiceEditClass()
{
	// These constants should only be visible from within this class
	// They should reflect the ServiceType constants defined in framework/definitions.php
	var SERVICE_TYPE_ADSL		= 100;
	var SERVICE_TYPE_MOBILE		= 101;
	var SERVICE_TYPE_LAND_LINE	= 102;
	var SERVICE_TYPE_INBOUND	= 103;
	var SERVICE_TYPE_DIALUP		= 104;

	//------------------------------------------------------------------------//
	// InitialisePage
	//------------------------------------------------------------------------//
	/**
	 * InitialisePage
	 *
	 * Initialises the page
	 *
	 * Initialises the page
	 *
	 * @param	bool	bolAdd		optional, TRUE if the page is to be set up to Add a new service
	 *								FALSE or NULL if the page is to be set up to Edit an existing service
	 *
	 * @return	void
	 * @method
	 */
	this.InitialisePage = function(bolAdd)
	{
		// Initialise bolEdit to false, if it has not been declared
		bolAdd = (bolAdd) ? true : false;
		
		if (bolAdd)
		{
			// The page is to be set up for adding a new service
		}
		else
		{
			// The page is to be set up for editing a service
			document.getElementById("Service.ServiceType").disabled = true;
		}
		
	}
	
	//------------------------------------------------------------------------//
	// ServiceTypeOnChange
	//------------------------------------------------------------------------//
	/**
	 * ServiceTypeOnChange
	 *
	 * Event handler for when the Service Type is chosen from the Service Type Combobox
	 *  
	 * Event handler for when the Service Type is chosen from the Service Type Combobox
	 *
	 * @param	int		intServiceType		Id of the ServiceType selected
	 *
	 * @return	void
	 * @method
	 */
	this.ServiceTypeOnChange = function(intServiceType)
	{
		switch (parseInt(intServiceType))
		{
			case SERVICE_TYPE_MOBILE:
				// hide any details not required for a mobile and display the mobile details
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='none';
				document.getElementById('MobileDetailDiv').style.display='inline';
				break;
			case SERVICE_TYPE_INBOUND:
				// hide any details not required for inbound services and show the inbound services details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='inline';
				break;
			case SERVICE_TYPE_LAND_LINE:
				// hide any details not required for inbound services and show the inbound services details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='inline';
				break;
			default:
				// hide all extra details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='none';
				break;
		}
	}
}

// instanciate the objects
if (Vixen.ServiceEdit == undefined)
{
	Vixen.ServiceEdit = new VixenServiceEditClass;
}
