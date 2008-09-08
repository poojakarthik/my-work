//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_status_edit.js
//----------------------------------------------------------------------------//
/**
 * customer_status_edit
 *
 * javascript required of the Customer Status Edit page
 *
 * javascript required of the Customer Status Edit page
 * 
 *
 * @file		customer_status_edit.js
 * @language	Javascript
 * @package		ui
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.09
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// FlexCustomerStatusEditClass
//----------------------------------------------------------------------------//
/**
 * FlexCustomerStatusEditClass
 *
 * Encapsulates all event handling required of the Customer Status Edit webpage
 *
 * Encapsulates all event handling required of the Customer Status Edit webpage
 * 
 *
 * @package	ui
 * @class	FlexCustomerStatusEditClass
 * 
 */
function FlexCustomerStatusEditClass()
{
	// Inputs
	this.objActions	= {	
						UserRole : {},
						Default	: {}
						};
	this.intCustomerStatusId = null;
	
	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise()
	 *
	 * Initialises the page/functionality
	 *
	 * Initialises the page/functionality
	 * This is called when the page is loaded
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(intCustomerStatusId)
	{
		this.intCustomerStatusId = intCustomerStatusId;
		
		// Get a reference to each input element on the form
		
		var elmForm = $ID("FormCustomerStatus");
		var strName;
		var intRole;
		var refAction;
		
		for (i=0; i < elmForm.length; i++)
		{
			if (elmForm[i].name && (elmForm[i].name == "Action"))
			{
				// It is one of the "Action" textboxes
				if (elmForm[i].hasAttribute("UserRoleId"))
				{
					// A user role is associated with it
					intRole = parseInt(elmForm[i].getAttribute("UserRoleId"));
					if (!this.objActions.UserRole[intRole])
					{
						this.objActions.UserRole[intRole] = {};
					}
					refAction = this.objActions.UserRole[intRole];
				}
				else
				{
					// Must be one of the default ones
					refAction = this.objActions.Default;
				}
				
				if (elmForm[i].hasAttribute("Overdue"))
				{
					refAction.Overdue = elmForm[i];
				}
				else
				{
					refAction.Normal = elmForm[i];
				}
			}
		}
	}

	// Makes a request to the server to save the changes
	this.Save = function()
	{
		// Validate the boundary conditions
		if (!this.ValidateForm())
		{
			return false;
		}
	
		// Prepare data for the server
		var arrRoleSpecificActions	= new Array();
		
		with (this.objActions)
		{
			for (i in UserRole)
			{
				arrRoleSpecificActions.push({	UserRoleId	:	parseInt(i),
												Normal		:	UserRole[i].Normal.value,
												Overdue		:	UserRole[i].Overdue.value
											});
			}
		}
		
		
		// Define return handlers
		funcSuccess = function(response)
		{
			Vixen.Popup.ClosePageLoadingSplash();
			
			if (response.ERROR)
			{
				$Alert(response.ERROR);
				return;
			}
			
			if (response.Success)
			{
				$Alert("Customer Status has been successfully updated");
			}
			else
			{
				if (response.ValidationErrors != undefined)
				{
					$Alert("Failed validation:<br />" + response.ValidationErrors);
				}
				else
				{
					$Alert("Failed for some unknown reason");
				}
			}
			
		}

		remoteClass		= 'Customer_Status';
		remoteMethod	= 'modify';
		jsonFunc		= jQuery.json.jsonFunction(funcSuccess, null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Saving");
		jsonFunc(this.intCustomerStatusId, this.objActions.Default.Normal.value, this.objActions.Default.Overdue.value, arrRoleSpecificActions);
	}
	
	// Validates the form and alerts the user to anything which is invalid
	this.ValidateForm = function()
	{
		return true;
	}
	

}

if (Flex.CustomerStatusEdit == undefined)
{
	Flex.CustomerStatusEdit = new FlexCustomerStatusEditClass;
}
