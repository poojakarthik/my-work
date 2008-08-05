//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// ticketing_summary_report.js
//----------------------------------------------------------------------------//
/**
 * ticketing_summary_report
 *
 * javascript required of the Ticketing Summary Report
 *
 * javascript required of the Ticketing Summary Report
 * 
 *
 * @file		ticketing_summary_report.js
 * @language	Javascript
 * @package		ui
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.08
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// FlexTicketingSummaryReportClass
//----------------------------------------------------------------------------//
/**
 * FlexTicketingSummaryReportClass
 *
 * Encapsulates all event handling required of the Ticketing Summary Report webpage
 *
 * Encapsulates all event handling required of the Ticketing Summary Report webpage
 * 
 *
 * @package	ui
 * @class	FlexTicketingSummaryReportClass
 * 
 */
function FlexTicketingSummaryReportClass()
{
	// Inputs
	this.objInputs = {};
	/* Deprecated
	this.elmOwners		= null;
	this.elmCategories	= null;
	this.elmStatuses	= null;
	this.elmStartDate	= null;
	this.elmEndDate		= null;
	this.elmRenderMode	= null;
	*/
	
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
	this.Initialise = function()
	{
		// Get a reference to each input element on the form
		// Every input element on the form that has a name will be referenced in the this.objInputs object
		/* The following properties should be set:
		 *		this.objInputs.Owners
		 *						Categories
		 *						Statuses
		 *						StartDate
		 *						EndDate
		 *						RenderMode
		 */
		var formReportVariables = $ID("FormReportVariables");
		var strName;
		
		for (i=0; i < formReportVariables.length; i++)
		{
			if (formReportVariables[i].name)
			{
				strName = formReportVariables[i].name;
				this.objInputs[strName] = formReportVariables[i];
			}
		}
	}

	// Makes a request to the server to run the report
	this.Run = function()
	{
		// Validate the boundary conditions
		if (!this.ValidateForm())
		{
			return false;
		}
	
		// Prepare boundary conditions for the server
		var arrOwners		= new Array();
		var arrCategories	= new Array();
		var arrStatuses		= new Array();
		
		with (this.objInputs)
		{
			// Create a list of the selected owners
			for (i in Owners.options)
			{
				if (Owners.options[i].selected == true)
				{
					arrOwners.push(parseInt(Owners.options[i].value));
				}
			}
			
			// Create a list of the selected categories
			for (i in Categories.options)
			{
				if (Categories.options[i].selected == true)
				{
					arrCategories.push(parseInt(Categories.options[i].value));
				}
			}
			
			// Create a list of the selected statuses
			for (i in Statuses.options)
			{
				if (Statuses.options[i].selected == true)
				{
					arrStatuses.push(parseInt(Statuses.options[i].value));
				}
			}
		}
		
		
		// Define return handlers
		funcSuccess = function(response)
		{
			if (typeof(response) == "string")
			{
				$Alert(response);
			}
			else
			{
				$Alert("Everything is A Ok");
			}
		}

		remoteClass		= 'Ticketing';
		remoteMethod	= 'buildSummaryReport';
		jsonFunc		= jQuery.json.jsonFunction(funcSuccess, null, remoteClass, remoteMethod);
		jsonFunc(arrOwners, arrCategories, arrStatuses, this.objInputs.EarliestTime.value, this.objInputs.LatestTime.value, this.objInputs.RenderMode.value);

	}
	
	// Validates the form and alerts the user to anything which is invalid
	this.ValidateForm = function()
	{
		//if (!this.objInputs.StartDate.Validate("ShortDate"))
		if (!$Validate("DateTime", this.objInputs.EarliestTime.value))
		{
			// The date is invalid
			$Alert("ERROR: Earliest Time is invalid");
			return false;
		}
		if (!$Validate("DateTime", this.objInputs.LatestTime.value))
		{
			// The date is invalid
			$Alert("ERROR: Latest Time is invalid");
			return false;
		}
		return true;
	}
	

}

if (Flex.TicketingSummaryReport == undefined)
{
	Flex.TicketingSummaryReport = new FlexTicketingSummaryReportClass;
}
