//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_status_reports.js
//----------------------------------------------------------------------------//
/**
 * customer_status_reports
 *
 * javascript required of the Ticketing Summary Report
 *
 * javascript required of the Ticketing Summary Report
 * 
 *
 * @file		customer_status_reports.js
 * @language	Javascript
 * @package		ui
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.09
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// FlexCustomerStatusReportsClass
//----------------------------------------------------------------------------//
/**
 * FlexCustomerStatusReportsClass
 *
 * Encapsulates all event handling required of the Customer Status Summary Report webpage
 *
 * Encapsulates all event handling required of the Customer Status Report webpage
 * 
 *
 * @package	ui
 * @class	FlexCustomerStatusReportsClass
 * 
 */
function FlexCustomerStatusReportsClass()
{
	// Inputs
	this.objInputs = {};
	
	this.InitialiseSummaryReport = function()
	{
		this.Initialise();
	}
	
	this.InitialiseAccountReport = function()
	{
		this.Initialise();
	}
	
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
		 *		this.objInputs.CustomerGroups
		 *						CustomerStatuses
		 *						InvoiceRuns
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

	// Makes a request to the server to run the Summary report
	this.RunSummaryReport = function()
	{
		// Validate the boundary conditions
		if (!this.ValidateForm())
		{
			return false;
		}
	
		// Prepare boundary conditions for the server
		var arrCustomerGroups	= new Array();
		var arrCustomerStatuses	= new Array();
		var arrInvoiceRuns		= new Array();
		var i;
		with (this.objInputs)
		{
			// Create a list of the selected CustomerGroup options
			for (i=0; i < CustomerGroups.options.length; i++)
			{
				if (CustomerGroups.options[i].selected == true)
				{
					if (CustomerGroups.options[i].value == "any")
					{
						arrCustomerGroups.push("any");
					}
					else
					{
						arrCustomerGroups.push(parseInt(CustomerGroups.options[i].value));
					}
				}
			}
			
			// Create a list of the selected CustomerStatus options
			for (i=0; i < CustomerStatuses.options.length; i++)
			{
				if (CustomerStatuses.options[i].selected == true)
				{
					arrCustomerStatuses.push(parseInt(CustomerStatuses.options[i].value));
				}
			}
			
			// Create a list of the selected Invoice Runs
			for (i=0; i < InvoiceRuns.options.length; i++)
			{
				if (InvoiceRuns.options[i].selected == true)
				{
					arrInvoiceRuns.push(parseInt(InvoiceRuns.options[i].value));
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

		remoteClass		= 'Customer_Status';
		remoteMethod	= 'buildSummaryReport';
		jsonFunc		= jQuery.json.jsonFunction(funcSuccess, null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Generating Report");
		jsonFunc(arrCustomerGroups, arrCustomerStatuses, arrInvoiceRuns, this.objInputs.RenderMode.value);
	}
	
	// Makes a request to the server to run the Account report
	this.RunAccountReport = function(objBoundaryConditions)
	{
		var arrCustomerGroups	= new Array();
		var arrCustomerStatuses	= new Array();
		var arrInvoiceRuns		= new Array();
		var strRenderMode		= this.objInputs.RenderMode.value;
		
		if (objBoundaryConditions == undefined)
		{
			// Boundary conditions have not been passed, retrieve them from the form
			
			// Validate the boundary conditions
			if (!this.ValidateForm())
			{
				return false;
			}
		
			// Prepare boundary conditions for the server
			var i;
			with (this.objInputs)
			{
				// Create a list of the selected CustomerGroup options
				for (i=0; i < CustomerGroups.options.length; i++)
				{
					if (CustomerGroups.options[i].selected == true)
					{
						if (CustomerGroups.options[i].value == "any")
						{
							arrCustomerGroups.push("any");
						}
						else
						{
							arrCustomerGroups.push(parseInt(CustomerGroups.options[i].value));
						}
					}
				}
				
				// Create a list of the selected CustomerStatus options
				for (i=0; i < CustomerStatuses.options.length; i++)
				{
					if (CustomerStatuses.options[i].selected == true)
					{
						arrCustomerStatuses.push(parseInt(CustomerStatuses.options[i].value));
					}
				}
				
				// Create a list of the selected Invoice Runs
				for (i=0; i < InvoiceRuns.options.length; i++)
				{
					if (InvoiceRuns.options[i].selected == true)
					{
						arrInvoiceRuns.push(parseInt(InvoiceRuns.options[i].value));
					}
				}
			}
		}
		else
		{
			// Boundary conditions have been passed to the function
			arrCustomerGroups	= objBoundaryConditions.CustomerGroups;
			arrCustomerStatuses	= objBoundaryConditions.CustomerStatuses;
			arrInvoiceRuns.push(objBoundaryConditions.InvoiceRun);
			strRenderMode		= objBoundaryConditions.RenderMode;
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

		remoteClass		= 'Customer_Status';
		remoteMethod	= 'buildAccountReport';
		jsonFunc		= jQuery.json.jsonFunction(funcSuccess, null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Generating Report");
		jsonFunc(arrCustomerGroups, arrCustomerStatuses, arrInvoiceRuns, strRenderMode);
	}


	// Validates the form and alerts the user to anything which is invalid
	this.ValidateForm = function()
	{
		// Check that at least one CustomerGroup option has been selected
		if (this.objInputs.CustomerGroups.selectedIndex == -1)
		{
			$Alert("ERROR: Please specify at least one Customer Group");
			return false;
		}
	
		// Check that at least one Customer Status has been selected
		if (this.objInputs.CustomerStatuses.selectedIndex == -1)
		{
			$Alert("ERROR: Please specify at least one Customer Status");
			return false;
		}
		
		// Check that at least one InvoiceRun has been selected
		if (this.objInputs.InvoiceRuns.selectedIndex == -1)
		{
			$Alert("ERROR: Please specify an Invoice Run");
			return false;
		}
		
		return true;
	}
	

}

if (Flex.CustomerStatusReports == undefined)
{
	Flex.CustomerStatusReports = new FlexCustomerStatusReportsClass;
}
