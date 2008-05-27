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
 * Encapsulates all event handling required of the "Edit Service" popup
 *
 * Encapsulates all event handling required of the "Edit Service" popup
 *
 * @package	ui_app
 * @class	VixenServiceEditClass
 * 
 */
function VixenServiceEditClass()
{
	var SERVICE_ACTIVE			= 400;
	var SERVICE_DISCONNECTED	= 402;
	var SERVICE_ARCHIVED		= 403;
	
	this.intCurrentStatus = null;

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
	this.Initialise = function(intCurrentStatus)
	{
		this.intCurrentStatus = intCurrentStatus;
	}
	
	this.ApplyChanges = function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			var elmNewStatus	= $ID("ServiceEditStatusCombo");
			var intNewStatus	= parseInt(elmNewStatus.value);
			var strMsg			= "Are you sure you want to make changes to this service?";
			if (intNewStatus != this.intCurrentStatus)
			{
				if (this.intCurrentStatus == SERVICE_ACTIVE)
				{

					switch (intNewStatus)
					{
						case SERVICE_DISCONNECTED:
							strMsg += 	"<br /><br />You have chosen to <strong>disconnect</strong> a currently <strong>active</strong> service." +
										"<br />Disconnected services still get invoiced, as long as the account gets invoiced.";
							break;
						case SERVICE_ARCHIVED:
							strMsg += 	"<br /><br />You have chosen to <strong>archive</strong> a currently <strong>active</strong> service." +
										"<br />This will prohibit all outstanding CDRs, adjustments and recurring adjustments from being invoiced.";
							break;
					}
				}
				else if (this.intCurrentStatus == SERVICE_DISCONNECTED)
				{
					switch (intNewStatus)
					{
						case SERVICE_ACTIVE:
							strMsg += 	"<br /><br />You have chosen to <strong>activate</strong> a currently <strong>disconnected</strong> service.";
							break;
						case SERVICE_ARCHIVED:
							strMsg += 	"<br /><br />You have chosen to <strong>archive</strong> a currently <strong>disconnected</strong> service." +
										"<br />This will prohibit all outstanding CDRs, adjustments and recurring adjustments from being invoiced.";
							break;
					}
				}
				else if (this.intCurrentStatus == SERVICE_ARCHIVED)
				{
					switch (intNewStatus)
					{
						case SERVICE_ACTIVE:
							strMsg += 	"<br /><br />You have chosen to <strong>activate</strong> a currently <strong>archived</strong> service." +
										"<br /><strong>WARNING:</strong> All outstanding unbilled CDRs, adjustments and recurring adjustments currently associated with this archived service will not be eligible for billing";
							break;
						case SERVICE_DISCONNECTED:
							strMsg += 	"<br /><br />You have chosen to upgrade the status of the service from <strong>archived</strong> to <strong>disconnected</strong>." +
										"<br /><strong>WARNING:</strong> All outstanding unbilled CDRs, adjustments and recurring adjustments currently associated with this archived service will not be eligible for billing";
							break;
					}
				}
				
			}
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ServiceEdit.ApplyChanges(true)});
			return;
		}
		
		var elmSubmitButton = $ID("ServiceEditSubmitButton");
		elmSubmitButton.click();
	}
}

// instanciate the objects
if (Vixen.ServiceEdit == undefined)
{
	Vixen.ServiceEdit = new VixenServiceEditClass;
}
