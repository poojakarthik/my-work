//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_status_history.js
//----------------------------------------------------------------------------//
/**
 * customer_status_history
 *
 * javascript required of the customer status history functionality
 *
 * javascript required of the customer status history functionality
 * 
 *
 * @file		customer_status_history.js
 * @language	Javascript
 * @package		admin
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.09
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenCustomerStatusHistoryClass
//----------------------------------------------------------------------------//
/**
 * VixenCustomerStatusHistoryClass
 *
 * Encapsulates all event handling required of the customer status history component
 *
 * Encapsulates all event handling required of the customer status history component
 * 
 *
 * @package	admin
 * @class	VixenCustomerStatusHistoryClass
 * 
 */
function VixenCustomerStatusHistoryClass()
{
	this.arrHistoryDetails	= null;
	this.elmName			= null;
	this.elmDescription		= null;
	this.elmLastUpdated		= null;
	this.elmAction			= null;
	this.elmDetailsTable	= null;

	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise()
	 *
	 * Initialises the component
	 *
	 * Initialises the component
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(arrHistoryDetails)
	{
		this.arrHistoryDetails	= arrHistoryDetails;
		this.elmName			= $ID("CustomerStatusDetails.Name");
		this.elmDescription		= $ID("CustomerStatusDetails.Description");
		this.elmLastUpdated		= $ID("CustomerStatusDetails.LastUpdated");
		this.elmAction			= $ID("CustomerStatusDetails.Action");
		this.elmDetailsTable	= $ID("CustomerStatusDetails");
	
		// Register event listeners
		var elmHistoryTable = $ID("CustomerStatusHistory");

		var elmCell;
		for (var i=0; i < elmHistoryTable.rows[0].cells.length; i++)
		{
			elmCell = elmHistoryTable.rows[0].cells[i];
			if (elmCell.tagName == "TD")
			{
				// It's a cell
				elmCell.setAttribute("historyIndex", i);
				Event.startObserving(elmCell, "mouseover", this.ShowStatusDetails.bind(this), true);
			}
		}
		
		Event.startObserving(elmHistoryTable, "mouseout", this.ShowStatusDetails.bind(this), true);
		
	}

	this.ShowStatusDetails = function(objEvent)
	{
		var objDetails;
		if (objEvent.type == "mouseover")
		{
			objDetails = this.arrHistoryDetails[objEvent.target.getAttribute("historyIndex")];
		}
		else
		{
			// The event should be mouseout
			// Show the default, which is the most recent invocie run
			objDetails = this.arrHistoryDetails[0];
		}
		
		this.elmDetailsTable.className	= objDetails.cssClass;
		this.elmName.innerHTML			= objDetails.name;
		this.elmDescription.innerHTML	= objDetails.description;
		this.elmLastUpdated.innerHTML	= objDetails.lastUpdated;
		this.elmAction.innerHTML		= objDetails.action;
	}

}

if (Vixen.CustomerStatusHistory == undefined)
{
	Vixen.CustomerStatusHistory = new VixenCustomerStatusHistoryClass;
}
