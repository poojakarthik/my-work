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
		var arrStatusTypes	= new Array();
		var mixOwner		= null;
		var mixCategory		= null;
		var i;
		
		with (this.objInputs)
		{
			// Create a list of the selected owners
			for (i=0; i < Owners.options.length; i++)
			{
				if (Owners.options[i].selected == true)
				{
					if (Owners.options[i].value == "all")
					{
						mixOwner = "all";
					}
					else
					{
						mixOwner = parseInt(Owners.options[i].value);
					}
					arrOwners.push(mixOwner);
				}
			}
			
			// Create a list of the selected categories
			for (i=0; i < Categories.options.length; i++)
			{
				if (Categories.options[i].selected == true)
				{
					if (Categories.options[i].value == "all")
					{
						mixCategory = "all";
					}
					else
					{
						mixCategory = parseInt(Categories.options[i].value);
					}
					arrCategories.push(mixCategory);
				}
			}
			
			// Create a list of the selected Statuses and StatusTypes
			for (i=0; i < Statuses.options.length; i++)
			{
				if (Statuses.options[i].selected == true)
				{
					if (Statuses.options[i].hasAttribute("IsStatusType"))
					{
						arrStatusTypes.push(parseInt(Statuses.options[i].value));
					}
					else
					{
						arrStatuses.push(parseInt(Statuses.options[i].value));
					}
				}
			}
			

		}
		
		
		// Define return handlers
		funcSuccess = function(response)
		{
			Vixen.Popup.ClosePageLoadingSplash();
			
			if (response.Success)
			{
				// The report was successfully generated
				if (response.Report !== null)
				{
					// The report has been returned.  Stick it in the page
					var elmReportContainer = $ID("ReportResultsContainer");
					elmReportContainer.innerHTML = response.Report;
				}
				else
				{
					// Retrieve the report from the server by relocating the page
					if (response.ReportLocation != undefined)
					{
						window.location = response.ReportLocation;
					}
				}
			}
			else
			{
				$Alert("Generating the report failed<br />" + response.Message);
			}
			
		}

		remoteClass		= 'Ticketing';
		remoteMethod	= 'buildSummaryReport';
		jsonFunc		= jQuery.json.jsonFunction(funcSuccess, null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Generating Report");
		jsonFunc(arrOwners, arrCategories, arrStatusTypes, arrStatuses, this.objInputs.EarliestTime.value, this.objInputs.LatestTime.value, this.objInputs.RenderMode.value);

	}
	
	// Validates the form and alerts the user to anything which is invalid
	this.ValidateForm = function()
	{
		// Check that at least one owner has been selected
		if (this.objInputs.Owners.selectedIndex == -1)
		{
			// No owner has been selected
			$Alert("ERROR: Please specify at least one owner");
			return false;
		}
	
		// Check that at least one Category has been selected
		if (this.objInputs.Categories.selectedIndex == -1)
		{
			// No category has been selected
			$Alert("ERROR: Please specify at least one category");
			return false;
		}
		
		// Check that at least one Status has been selected
		if (this.objInputs.Statuses.selectedIndex == -1)
		{
			// No status has been selected
			$Alert("ERROR: Please specify at least one status");
			return false;
		}
		
		if (!$Validate("DateTime", this.objInputs.EarliestTime.value, true))
		{
			// The date is invalid
			$Alert("ERROR: Earliest Time is invalid");
			return false;
		}
		if (!$Validate("DateTime", this.objInputs.LatestTime.value, true))
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
