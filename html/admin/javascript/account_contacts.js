//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_contacts.js
//----------------------------------------------------------------------------//
/**
 * account_contacts
 *
 * javascript required of the "Account contacts" HtmlTemplates
 *
 * javascript required of the "Account contacts" HtmlTemplates
 * 
 *
 * @file		account_contacts.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenAccountContactsListClass
//----------------------------------------------------------------------------//
/**
 * VixenAccountContactsListClass
 *
 * Encapsulates all event handling required of the "Account Contacts list" HtmlTemplate
 *
 * Encapsulates all event handling required of the "Account Contacts list" HtmlTemplate
 * 
 *
 * @package	ui_app
 * @class	VixenAccountContactsListClass
 * 
 */
function VixenAccountContactsListClass()
{
	this.strContainerDivId = null;
	
	this.intAccountId = null;
	
	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the object for when the AccountContactsList HtmlTemplate is rendered
	 *  
	 * Initialises the object for when the AccountContactsList HtmlTemplate is rendered
	 *
	 * @param	int		intAccountId			Id of the account
	 * @param 	string	strTableContainerDivId	Id of the div that stores the table which lists all the services
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(intAccountId, strContainerDivId)
	{
		// Save the parameters
		this.intAccountId		= intAccountId;
		this.strContainerDivId	= strContainerDivId;
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnAccountPrimaryContactUpdate", this.OnUpdate);
	}
	
	this.SetPrimary = function(intPrimaryContactId)
	{
		// Organise the data to send
		var objObjects 							= {};
		objObjects.Objects 						= {};
		objObjects.Objects.Account 				= {};
		objObjects.Objects.Account.Id 			= this.intAccountId;
		objObjects.Objects.PrimaryContact		= {};
		objObjects.Objects.PrimaryContact.Id	= intPrimaryContactId;

		// Call the AppTemplate method which renders just the AccountServices table
		Vixen.Popup.ShowPageLoadingSplash("Setting Primary Contact");
		Vixen.Ajax.CallAppTemplate("Contact", "SetPrimaryForAccount", objObjects.Objects);
	}
	
	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when the Account Contact List has to be updated, for whatever reason
	 *  
	 * Event handler for when the Account Contact List has to be updated, for whatever reason
	 *
	 * @param	object	objEvent		objEvent.Data.Account.Id should be set.
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		// For now just reload the page
		window.location = window.location;
		return;
		
		//TODO! have this update the list of contacts only
		// I have not currently implemented this because I don't know how to accomodate for 
		// having this list both rendered in the page and as a popup at the same time
		// Perhaps I could store the names of the AppTemplate and method required to rerender the list
		// as one will just show the primary contact and the other will show the entire list of contacts for this account
		
	}
}
