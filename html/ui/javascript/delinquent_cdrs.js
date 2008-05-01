//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// delinquent_cdrs.js
//----------------------------------------------------------------------------//
/**
 * delinquent_cdrs
 *
 * javascript required of the move delinquent CDRs functionality
 *
 * javascript required of the move delinquent CDRs functionality
 * 
 *
 * @file		delinquent_cdrs.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDelinquentCDRsClass
//----------------------------------------------------------------------------//
/**
 * VixenDelinquentCDRsClass
 *
 * Encapsulates all event handling required of the Move Delinquent CDRs webpage
 *
 * Encapsulates all event handling required of the Move Delinquent CDRs webpage
 * 
 *
 * @package	ui_app
 * @class	VixenMoveDelinquentCDRsClass
 * 
 */
function VixenDelinquentCDRsClass()
{
	this.intMaxRowsToShow	= 10;
	this.intFirstRowShown	= null;

	this.elmStartDate	= null;
	this.elmEndDate		= null;
	
	this.strStartDate	= null;
	this.strEndDate		= null;
	
	// Points to the FNN Selector listbox
	this.elmFNNSelector	= null;
	
	// Stores all the possible services that CDRs of a particular FNN/ServiceType/Carrier could belong to
	this.objServices = null;
	
	this.elmFNNGroupContainer	= null;
	this.tableCDRs				= null;
	this.elmGenericTableRow		= null;
	this.objCDRTableCaption		= {};
	this.arrCDRs				= null;
	this.elmAllCDRsCheckbox		= null;
	
	// This stores the list of selected CDRs which a Service assignment will be applied to
	this.arrSelectedCDRs = null;

	// This is called when the page is loaded
	this.Initialise = function()
	{
		this.elmFNNSelector			= $ID("FNNSelector");
		this.elmFNNGroupContainer	= $ID("Container_FNNGroup");
		this.elmStartDate			= $ID("StartDate");
		this.elmEndDate				= $ID("EndDate");
		this.tableCDRs				= $ID("CDRTable");
		this.elmAllCDRsCheckbox		= $ID("CheckBoxSelectAllCDRs");
		
		// Store a copy of the first record of the table, so that it can be used to add more records
		this.elmGenericTableRow = this.tableCDRs.rows[1].cloneNode(true);
		
		this.objCDRTableCaption.elmTop		= $ID("CDRTableCaptionTop");
		this.objCDRTableCaption.elmBottom	= $ID("CDRTableCaptionBottom");
	}

	// This is used to find the Delinquent CDRs between the 2 date ranges
	this.GetFNNs = function()
	{
		if (Vixen.Ajax.strFormCurrentlyProcessing)
		{
			return;
		}
		this.elmStartDate.SetHighlight();
		this.elmEndDate.SetHighlight();
		
		// Validate the Dates
		var bolInvalid = false;
		if (!this.elmStartDate.Validate("ShortDate"))
		{
			bolInvalid = true;
			this.elmStartDate.SetHighlight(true);
		}
		if (!this.elmEndDate.Validate("ShortDate"))
		{
			bolInvalid = true;
			this.elmEndDate.SetHighlight(true);
		}
		if (bolInvalid)
		{
			$Alert("ERROR: Dates must be specified as dd/mm/yyyy");
			return;
		}
		
		// Store the date range (the user can't manipulate these variables)
		this.strStartDate	= this.elmStartDate.value;
		this.strEndDate		= this.elmEndDate.value;

		
		// Retrieve the FNN data for the given range
		var objData =	{	
							Delinquents :	{
												StartDate	: this.strStartDate,
												EndDate		: this.strEndDate
											}
						};
		
		// Disable the FNN Selector
		this.LockControls(true);
		this.elmFNNGroupContainer.style.display = "none";
		
		Vixen.Popup.ShowPageLoadingSplash("Finding all Delinquent CDRs");
		Vixen.Ajax.CallAppTemplate("Misc", "GetDelinquentFNNs", objData, null, true, true, this.GetFNNsReturnHandler.bind(this));
	}
	
	// This is used to lock the controls, usually for when a request to the server is made
	this.LockControls = function(bolLock)
	{
		this.elmFNNSelector.disabled = bolLock;
	}
	
	
	// This handles the reply from the server triggered by GetFNNs
	this.GetFNNsReturnHandler = function(objXMLHttpRequest)
	{
		var objResponse = JSON.parse(objXMLHttpRequest.responseText);
		
		// Remove the current contents of the FNN Selector listbox
		while (this.elmFNNSelector.hasChildNodes())
		{
			this.elmFNNSelector.removeChild(this.elmFNNSelector.firstChild);
		}
		
		// Add the new options to the listbox
		var elmOption;
		for (i in objResponse)
		{
			elmOption = document.createElement('option');
			elmOption.style.whiteSpace = "pre";
			elmOption.setAttribute("FNN", objResponse[i].FNN);
			elmOption.setAttribute("Carrier", objResponse[i].Carrier);
			elmOption.setAttribute("ServiceType", objResponse[i].ServiceType);
			
			elmOption.innerHTML = objResponse[i].Description;
			
			this.elmFNNSelector.appendChild(elmOption);
		}
		
		// Enable the FNN Selector
		this.LockControls(false);
	}
	
	// Retrieves all the CDRs once an item in the FNN Selector listbox is selected
	this.GetCDRs = function()
	{
		if (Vixen.Ajax.strFormCurrentlyProcessing)
		{
			return;
		}
	
		var intIndex		= this.elmFNNSelector.selectedIndex;
		var intCarrier		= parseInt(this.elmFNNSelector.options[intIndex].getAttribute("Carrier"));
		var intServiceType	= parseInt(this.elmFNNSelector.options[intIndex].getAttribute("ServiceType"));
		var strFNN			= this.elmFNNSelector.options[intIndex].getAttribute("FNN");

		// Retrieve the CDR data for the given FNN/Carrier/DateRange
		var objData =	{	
							Delinquents :	{
												StartDate	: this.strStartDate,
												EndDate		: this.strEndDate,
												FNN			: strFNN,
												ServiceType	: intServiceType,
												Carrier		: intCarrier
											}
						};
		
		// Disable the controls
		this.LockControls(true);
		
		Vixen.Popup.ShowPageLoadingSplash("Finding Delinquent CDRs");
		Vixen.Ajax.CallAppTemplate("Misc", "GetDelinquentCDRs", objData, null, true, true, this.GetCDRsReturnHandler.bind(this));
	}
	
	// This handles the reply from the server triggered by GetFNNs
	this.GetCDRsReturnHandler = function(objXMLHttpRequest)
	{
		var objResponse = JSON.parse(objXMLHttpRequest.responseText);
		
		// Store all the possible services that the CDRs could belong to
		this.objServices = {};
		this.objServices = objResponse.Services;
		
		this.strServicesPopupContent = objResponse.ServiceSelectorHtml;
		//Vixen.Popup.Close("DelinquentCDRsServiceSelector");
				
		// Build the table
		this.BuildTable(objResponse.CDRs);
				
		// Display the CDR table, if it is not already displayed
		this.elmFNNGroupContainer.style.display = "block";
		
		// Check if there aren't any potential services for these CDRs
		if (this.objServices.length == 0)
		{
			$Alert("WARNING: No potential owners were found for these CDRs");
		}
		
		// Enable the controls
		this.LockControls(false);
	}
	
	// Loads the table of CDRs and handles the pagination
	this.BuildTable = function(arrCDRs)
	{
		$ID("CheckBoxSelectAllCDRs").checked = false;
	
		// Remove the contents of the table (omitting the first row which is actually the header for the table)
		while (this.tableCDRs.rows.length > 1)
		{
			this.tableCDRs.tBodies[0].removeChild(this.tableCDRs.rows[1]);
		}
		
		// Initialise the member variable which will store a record of all the CDRs and their assigned services
		this.arrCDRs = new Array();
		
		// Add each CDR as a row
		var elmRow;
		var strClassName = "Even";
		var objCDR;
		for (i=0; i < arrCDRs.length; i++)
		{
			strClassName = (strClassName == "Even")? "Odd" : "Even";
			objCDR	= 	{
							Id					: arrCDRs[i].Id,
							Service				: arrCDRs[i].Service,
							Time				: arrCDRs[i].Time,
							Cost				: arrCDRs[i].Cost,
							DefaultClassName	: strClassName
						};
			
			// Create the table row element
			elmRow = this.elmGenericTableRow.cloneNode(true);
			if (i >= this.intMaxRowsToShow)
			{
				elmRow.style.display = "none";
			}
			
			elmRow.className = strClassName;
			elmRow.cells[1].innerHTML	= i + 1;
			elmRow.cells[2].innerHTML	= arrCDRs[i].Time;
			elmRow.cells[3].innerHTML	= arrCDRs[i].Cost;
			//elmRow.cells[4].innerHTML	= "this is for this.arrCDRs["+ i +"]";
			
			// Add the Row to the table
			elmRow = this.tableCDRs.tBodies[0].appendChild(elmRow);
			
			// Add a reference to the row, to the CDR object
			objCDR.Row					= elmRow;
			objCDR.elmCheckBox			= elmRow.cells[0].firstChild;
			objCDR.elmServiceCell		= elmRow.cells[4];
			objCDR.elmServiceDateRange	= elmRow.cells[5];
			objCDR.elmServiceIdCell		= elmRow.cells[6];
			
			// Register event listeners
			elmRow.onmouseover			= this.HighlightRow.bind(this, true, i);
			elmRow.onmouseout			= this.HighlightRow.bind(this, false, i);
			objCDR.elmServiceCell.onclick	= this.OpenDeclareServicePopup.bind(this, i);
			objCDR.elmCheckBox.onchange		= this.SelectCDR.bind(this, i);
			
			// Add the CDR's data to the data member
			this.arrCDRs.push(objCDR);
			this.UpdateDeclaredService(i);
		}
		
		// Update the Table's captions
		this.intFirstRowShown	= 1;
		var intLastRowShown		= Math.min(this.intMaxRowsToShow, this.arrCDRs.length);
		var strCaption			= "Showing "+ (this.intFirstRowShown) +" to "+ intLastRowShown +" of "+ this.arrCDRs.length;
		this.objCDRTableCaption.elmTop.innerHTML	= strCaption;
		this.objCDRTableCaption.elmBottom.innerHTML	= strCaption;
	}
	
	// Handles highlighting of a row of the CDR table
	this.HighlightRow = function(objThis, bolHighligh, intCDR)
	{
		this.arrCDRs[intCDR].Row.className = (bolHighligh)? "Hover" : this.arrCDRs[intCDR].DefaultClassName;
	}
	
	// Both of these are optional.  If they are not included then the Service will be applied to all selected CDRs
	this.OpenDeclareServicePopup = function(objThis, intCDRIndex)
	{
		if (Vixen.Ajax.strFormCurrentlyProcessing)
		{
			return;
		}
	
		// Work out which services this is being applied to
		this.arrSelectedCDRs = new Array();
		if (intCDRIndex != undefined)
		{
			// The service will be assigned to a single CDR
			this.arrSelectedCDRs.push(intCDRIndex);
			
			var strTitle = "Potential Services for CDR "+ (intCDRIndex + 1) +" with Start Time: " + this.arrCDRs[intCDRIndex].Row.cells[2].innerHTML;
		}
		else
		{
			// Must be a bulk assignment
			for (i in this.arrCDRs)
			{
				if (this.arrCDRs[i].elmCheckBox.checked)
				{
					this.arrSelectedCDRs.push(i);
				}
			}
			if (this.arrSelectedCDRs.length == 0)
			{
				// There were none selected
				$Alert("No CDRs have been selected");
				return;
			}
			
			var strTitle = "Potential Services for Selected CDRs";
		}
		
		// Create the popup
		Vixen.Popup.Create("DelinquentCDRsServiceSelector", this.strServicesPopupContent, "extralarge", "centre", "modal", strTitle);
		
		if (this.arrSelectedCDRs.length == 1)
		{
			// Highlight the currently selected option
			var elmServiceSelector		= $ID("ServiceSelectorControl");
			elmServiceSelector.value	= (this.arrCDRs[this.arrSelectedCDRs[0]].Service != null)? this.arrCDRs[this.arrSelectedCDRs[0]].Service : 0;
		}
	}
	
	// This is called when an item is selected from the list of Services in the ServiceSelector popup
	this.SetService = function(intServiceId)
	{
		Vixen.Popup.Close("DelinquentCDRsServiceSelector");
		
		intServiceId = (intServiceId == 0) ? null : intServiceId;
		
		for (i in this.arrSelectedCDRs)
		{
			this.arrCDRs[this.arrSelectedCDRs[i]].Service = intServiceId;
			this.UpdateDeclaredService(this.arrSelectedCDRs[i]);
		}
	}
	
	// This will update the Service field of a row in the table, based on the Service property of the 
	// record in this.arrCDRs 
	this.UpdateDeclaredService = function(intCDRIndex)
	{
		var objCDR = this.arrCDRs[intCDRIndex];
		if (objCDR.Service == null)
		{
			// No service is associated with this CDR
			objCDR.elmServiceCell.style.color		= "red";
			objCDR.elmServiceCell.innerHTML			= "[ No Service Specified ]"
			objCDR.elmServiceDateRange.innerHTML	= "";
			objCDR.elmServiceIdCell.innerHTML		= "";
		}
		else
		{
			objCDR.elmServiceCell.style.color		= "";
			objCDR.elmServiceCell.innerHTML 		= this.objServices[objCDR.Service].AccountDescription;
			objCDR.elmServiceDateRange.innerHTML	= this.objServices[objCDR.Service].DateRange;
			objCDR.elmServiceIdCell.innerHTML		= objCDR.Service;
		}
	}

	// This will make a request to the server to commit the owner allocation for all CDRs that have been allocated an owner
	this.Commit = function(bolConfirmed)
	{
		if (Vixen.Ajax.strFormCurrentlyProcessing)
		{
			return;
		}
	
		// Compile the data to be sent to the server
		var arrCDRsToCommit		= new Array();
		var objCDR;
		var objServiceCounts	= {};
		for (i=0; i < this.arrCDRs.length; i++)
		{
			if (this.arrCDRs[i].Service != null)
			{
				// A service has been allocated to this CDR
				objCDR =	{
								Id		: this.arrCDRs[i].Id,
								Service	: this.arrCDRs[i].Service,
								Record	: i + 1
							};

				// Update the running totals of how many CDRs have been allocated to each Service, for this commit
				if (objServiceCounts[objCDR.Service] == undefined)
				{
					objServiceCounts[objCDR.Service] = 1;
				}
				else
				{
					objServiceCounts[objCDR.Service] += 1;
				}
				
				arrCDRsToCommit.push(objCDR);
			}
		}
		
		if (arrCDRsToCommit.length == 0)
		{
			$Alert("ERORR: None of the CDRs have been allocated to a Service");
			return;
		}
		
		if (!bolConfirmed)
		{
			// Prompt the user to confirm the "commit" action
			var strSummary = "";
			for (i in objServiceCounts)
			{
				strSummary += "<br />" + objServiceCounts[i] + " CDR(s) being added to service: " + i;
			}
			var strMsg = "Are you sure you want to commit these CDRs?<br />" + strSummary + "<br /><br />Once committed, these CDRs cannot be reallocated to any other service";
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.DelinquentCDRs.Commit(true)});
			return;
		}
		
		var intIndex		= this.elmFNNSelector.selectedIndex;
		var intCarrier		= parseInt(this.elmFNNSelector.options[intIndex].getAttribute("Carrier"));
		var intServiceType	= parseInt(this.elmFNNSelector.options[intIndex].getAttribute("ServiceType"));
		var strFNN			= this.elmFNNSelector.options[intIndex].getAttribute("FNN");

		// Compile the data to send to the server
		var objData =	{	
							Delinquents :	{
												FNN			: strFNN,
												ServiceType	: intServiceType,
												Carrier		: intCarrier,
												CDRs		: arrCDRsToCommit
											}
						};
		
		// Lock the controls
		this.LockControls(true);
		
		Vixen.Popup.ShowPageLoadingSplash("Committing Service Allocation");
		Vixen.Ajax.CallAppTemplate("Misc", "AssignCDRsToServices", objData, null, true, true, this.CommitReturnHandler.bind(this));
	}
	
	// Return Handler for the Commit method
	this.CommitReturnHandler = function(objXMLHttpRequest)
	{
		var objResponse = JSON.parse(objXMLHttpRequest.responseText);

		if (objResponse.Success == true)
		{
			this.RemoveCDRs(objResponse.SuccessfulCDRs);
			var strMsgSuffix = "";
			
			// Check if there are any CDRs left for this FNN/Carrier combination
			if (this.arrCDRs.length == 0)
			{
				// There are none left.  Remove the item from the FNN Selector listbox
				this.elmFNNGroupContainer.style.display = "none";
				
				var intScrollTop = this.elmFNNSelector.scrollTop;
				this.elmFNNSelector.removeChild(this.elmFNNSelector.options[this.elmFNNSelector.selectedIndex]);
				this.elmFNNSelector.scrollTop = intScrollTop;

				strMsgSuffix = " and there are no more delinquent CDRs for this FNN / Carrier combination";
			}
			
			$Alert("The CDRs have been successfully owned" + strMsgSuffix);
		}
		else
		{
			$Alert(objResponse.ErrorMsg);
		}
		
		// Enable the controls
		this.LockControls(false);
	}
	
	// Removes the CDRs from the table, reorders the existing ones and if there are none left, it will notify the user
	this.RemoveCDRs = function(arrCDRsToRemove)
	{
		for (i in arrCDRsToRemove)
		{
			// Find the CDR in the array of all CDRs
			for (j = 0; j < this.arrCDRs.length; j++)
			{
				if (this.arrCDRs[j].Id == arrCDRsToRemove[i])
				{
					// We have found the CDR.  Remove it from the array
					this.arrCDRs.splice(j, 1);
					break;
				}
			}
		}

		var arrNewCDRs = new Array();
		var objNewCDR;
		for (i = 0; i < this.arrCDRs.length; i++)
		{
			objNewCDR =	{
							Id		: this.arrCDRs[i].Id,
							Service	: this.arrCDRs[i].Service,
							Time	: this.arrCDRs[i].Time,
							Cost	: this.arrCDRs[i].Cost,
						};
			arrNewCDRs.push(objNewCDR);
		}

		this.BuildTable(arrNewCDRs);
	}
	
	
	this.MoveNext = function()
	{
		var intCurrentFirstCDRShown = this.intFirstRowShown - 1;
		var intFirstCDRToShow = intCurrentFirstCDRShown + this.intMaxRowsToShow;
	
		if (!(intFirstCDRToShow < this.arrCDRs.length))
		{
			// The first CDR to show is not within the bounds of the CDR array
			return;
		}
		
		this.ShowFromCDR(intFirstCDRToShow);
	}

	this.MovePrevious = function()
	{
		var intCurrentFirstCDRShown = this.intFirstRowShown - 1;
		var intFirstCDRToShow = intCurrentFirstCDRShown - this.intMaxRowsToShow;
	
		if (intFirstCDRToShow < 0)
		{
			return;
		}
		
		this.ShowFromCDR(intFirstCDRToShow);
	}
	
	this.MoveFirst = function()
	{
		this.ShowFromCDR(0);
	}
	
	this.MoveLast = function()
	{
		var intCurrentFirstCDRShown = this.intFirstRowShown - 1;
		
		var intFirstCDRToShow = parseInt((this.arrCDRs.length - 1) / this.intMaxRowsToShow) * this.intMaxRowsToShow;
	
		if (!(intFirstCDRToShow < this.arrCDRs.length))
		{
			// The first CDR to show is not within the bounds of the CDR array
			return;
		}
		
		this.ShowFromCDR(intFirstCDRToShow);
	}
	
	// Displays the allowed number of records, starting with record intRecord
	this.ShowFromCDR = function(intFirstCDR)
	{
		// Hide all rows
		for (i in this.arrCDRs)
		{
			this.arrCDRs[i].Row.style.display = "none";
		}
	
		var intLastCDR = intFirstCDR + Math.min(this.intMaxRowsToShow, this.arrCDRs.length - intFirstCDR) - 1;
	
		for (i = intFirstCDR; i <= intLastCDR; i++)
		{
			this.arrCDRs[i].Row.style.display = "table-row";
		}
		
		this.intFirstRowShown = intFirstCDR + 1;
		
		// Update the captions
		var intLastRowShown = intLastCDR + 1;
		
		var strCaption = "Showing "+ (this.intFirstRowShown) +" to "+ intLastRowShown +" of "+ this.arrCDRs.length;
		this.objCDRTableCaption.elmTop.innerHTML	= strCaption;
		this.objCDRTableCaption.elmBottom.innerHTML	= strCaption;
	}
	
	// Checks/Unchecks all the checkboxes
	this.SelectAllCDRs = function(bolChecked)
	{
		for (i in this.arrCDRs)
		{
			this.arrCDRs[i].elmCheckBox.checked = bolChecked;
		}
	}
	
	// Event Listener for when the checkbox of a CDR row is selected
	// It updates the checkbox which signifies that they are all selected
	this.SelectCDR = function(objThis, intCDRIndex)
	{
		//this.arrCDRs[intCDRIndex].elmCheckBox.checked = (!this.arrCDRs[intCDRIndex].elmCheckBox.checked);
		var bolChecked = this.arrCDRs[intCDRIndex].elmCheckBox.checked;
		if (bolChecked == false)
		{
			this.elmAllCDRsCheckbox.checked = false;
		}
		else
		{
			var bolAllChecked = true;
			for (i in this.arrCDRs)
			{
				bolAllChecked = (bolAllChecked && this.arrCDRs[i].elmCheckBox.checked);
			}
			this.elmAllCDRsCheckbox.checked = bolAllChecked;
		}
	}

}

if (Vixen.DelinquentCDRs == undefined)
{
	Vixen.DelinquentCDRs = new VixenDelinquentCDRsClass;
}
