//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_group_details.js
//----------------------------------------------------------------------------//
/**
 * customer_group_details
 *
 * javascript required of the "CustomerGroup details" HtmlTemplate (handles both viewing and editing)
 *
 * javascript required of the "CustomerGroup details" HtmlTemplate (handles both viewing and editing)
 * 
 *
 * @file		customer_group_details.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenCustomerGroupDetailsClass
//----------------------------------------------------------------------------//
/**
 * VixenCustomerGroupDetailsClass
 *
 * Encapsulates all event handling required of the "Customer Group Details" HtmlTemplate
 *
 * Encapsulates all event handling required of the "Customer Group Details" HtmlTemplate
 * 
 *
 * @package	ui_app
 * @class	VixenCustomerGroupDetailsClass
 * 
 */
function VixenCustomerGroupDetailsClass()
{
	this.strContainerDivId	= null;
	this.intCustomerGroupId = null;
	
	//------------------------------------------------------------------------//
	// InitialiseView
	//------------------------------------------------------------------------//
	/**
	 * InitialiseView
	 *
	 * Initialises the object for when the CustomerGroupDetails HtmlTemplate is rendered with VIEW context
	 *  
	 * Initialises the object for when the CustomerGroupDetails HtmlTemplate is rendered with VIEW context
	 *
	 * @param	int		intCustomerGroupId			Id of the CustomerGroup
	 * @param 	string	strTableContainerDivId		Id of the div that stores the CustomerGroupDetails HtmlTemplate
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseView = function(intCustomerGroupId, strContainerDivId)
	{
		// Save the parameters
		this.intCustomerGroupId	= intCustomerGroupId;
		this.strContainerDivId	= strContainerDivId;
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnCustomerGroupDetailsUpdate", this.OnUpdate);
	}
	
	this.InitialiseEdit = function(intCustomerGroupId, strContainerDivId)
	{
		// Save the parameters
		this.intCustomerGroupId	= intCustomerGroupId;
		this.strContainerDivId	= strContainerDivId;
	}

	this.RenderDetailsForEditing = function()
	{
		// Organise the data to send
		var objData	=	{
							CustomerGroup	:	{	Id		:	this.intCustomerGroupId},
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	Edit	:	true}
						};
		
		// Call the AppTemplate method which renders just the CustomerGroupDetails HtmlTemplate
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "RenderHtmlTemplateCustomerGroupDetails", objData, null, true);
	}
	
	this.CancelEdit = function()
	{
		// Organise the data to send
		var objData	=	{
							CustomerGroup	:	{	Id		:	this.intCustomerGroupId},
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	View	:	true}
						};

		// Call the AppTemplate method which renders just the CustomerGroupDetails HtmlTemplate
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "RenderHtmlTemplateCustomerGroupDetails", objData, null, true);
	}

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when the CustomerGroup has details updated which would necessitate the CustomerGroupDetails HtmlTemplate being redrawn
	 *  
	 * Event handler for when the CustomerGroup has details updated which would necessitate the CustomerGroupDetails HtmlTemplate being redrawn
	 *
	 * @param	object	objEvent		objEvent.Data.CustomerGroup.Id should be set
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strContainerDivId	= Vixen.CustomerGroupDetails.strContainerDivId;
		var intCustomerGroupId	= Vixen.CustomerGroupDetails.intCustomerGroupId;
		
		if (intCustomerGroupId != objEvent.Data.CustomerGroup.Id)
		{
			// This CustomerGroup is not the one that was updated
			return;
		}
		
		// Organise the data to send
		var objData	=	{
							CustomerGroup	:	{	Id		:	intCustomerGroupId},
							Container		:	{	Id		:	strContainerDivId},
							Context			:	{	View	:	true}
						};

		// Call the AppTemplate method which renders just the CustomerGroupDetails HtmlTemplate
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "RenderHtmlTemplateCustomerGroupDetails", objData, null, true);
	}
}

// instanciate the object
if (Vixen.CustomerGroupDetails == undefined)
{
	Vixen.CustomerGroupDetails = new VixenCustomerGroupDetailsClass;
}
