var FlexSalesReport = {
	REPORT_TYPE_COMMISSIONS			: "Commissions",
	REPORT_TYPE_OUTSTANDING_SALES	: "OutstandingSales",
	REPORT_TYPE_SALE_ITEM_SUMMARY	: "SaleItemSummary",
	REPORT_TYPE_SALE_ITEM_STATUS	: "SaleItemStatus",
	REPORT_TYPE_SALE_ITEM_HISTORY	: "SaleItemHistory",
	REPORT_TYPE_SALE_HISTORY		: "SaleHistory",
	
	strReportType		: null,
	strReportName		: null,
	dealers				: null,
	controls			: null,
	rootLevelDealers	: null, // This will list all dealers that do not have a manager
	
	// Sets up the report page
	initialise : function(strReportType, strReportName, objDealers, arrSortedDealerIds)
	{
		this.strReportType	= strReportType;
		this.strReportName	= strReportName;
		this.dealers		= objDealers;
	
		// Get a reference to each input element on the form
		var elmForm		= document.getElementById("FormReportVariables");
		this.controls	= {};
		for (var i=0, j=elmForm.elements.length; i<j; i++)
		{
			if (elmForm.elements[i].hasAttribute("name"))
			{
				this.controls[elmForm.elements[i].getAttribute("name")] = elmForm.elements[i];
			}
		}
		
		if (this.controls.dealers)
		{
			// The dealers control is on the form, so set it up
			Event.startObserving(this.controls.dealersToggleRootLevelDealers, "change", this.updateDealerListbox.bind(this), true);
			Event.startObserving(this.controls.dealers, "change", this.updateDealerListbox.bind(this), true);
			
			this.loadAllDealers(arrSortedDealerIds);
		}
		
		Event.startObserving(this.controls.buildReport, "click", this.submitReport.bind(this), true);
		
	},
	
	// It is assumed the dealer control is empty
	loadAllDealers : function(arrSortedDealerIds)
	{
		var i, j, id, elmOption;
		
		// Find all root level dealers (those that don't have a manager)
		this.rootLevelDealers = {};
		for (i in this.dealers)
		{
			if (this.dealers[i].upLineId == null)
			{
				// The dealer is a root level dealer
				this.rootLevelDealers[i] = this.dealers[i];
			}
		}
		
		// Process dealers who are managers
		for (i=0, j=arrSortedDealerIds.length; i<j; i++)
		{
			id = arrSortedDealerIds[i];
			
			if (this.dealers[id].isManager)
			{
				// The dealer is a manager
				// For future reference, build an array of all its immediate subordinates
				this.dealers[id].immediateSubordinates = this.getImmediateSubordinatesForManager(this.dealers[id]);

				elmOption = new Option(this.dealers[id].username + " (including subordinates)", id);
			
				this.controls.dealers.appendChild(elmOption);
			
				// Store a reference to this element in the dealer object
				this.dealers[id].elmOptionIncludingSubordinates = elmOption;
			}
		}
		
		// Add all dealers (as normal dealers)
		for (i=0, j=arrSortedDealerIds.length; i<j; i++)
		{
			id = arrSortedDealerIds[i];
			elmOption = new Option(this.dealers[id].username, id);
			
			this.controls.dealers.appendChild(elmOption);
			
			// Store a reference to this element in the dealer object
			this.dealers[id].elmOption = elmOption;
		}
		
	},
	
	getImmediateSubordinatesForManager : function(manager)
	{
		var subbies = {};
		var i, j;
		
		// Find the immediate subordinate dealers of this dealer
		for (i in this.dealers)
		{
			if (this.dealers[i].upLineId == manager.id)
			{
				subbies[i] = this.dealers[i];
			}
		}
		
		return subbies;
	},
	

	// Use recursion to go through the management Tree, and if any dealer is selected then remove their subordinates, and if they
	// aren't selected then add their subordinates, if they aren't already present
	// NO: iterate through the listbox, and if any of the (including subordinates) options are selected, then hide the subordinates
	// and if any of them are not selected then show the subordinates
	// Only unselect an option if you have to hide it
	updateDealerListbox : function()
	{
		var i, j;
		
		if (this.controls.dealersToggleRootLevelDealers.checked)
		{
			// The "show dealers with no upline manager" checkbox is ticked, hide all options except for the root level dealers
			for (i in this.rootLevelDealers)
			{
				for (j in this.rootLevelDealers[i].immediateSubordinates)
				{
					this.hideDealer(this.rootLevelDealers[i].immediateSubordinates[j], true);
				}
			}
		}
		
		for (i in this.rootLevelDealers)
		{
			this.updateOptionsForManager(this.rootLevelDealers[i])
		}
		
	},
	
	updateOptionsForManager : function(dealer)
	{
		var i;
		
		if (dealer.elmOptionIncludingSubordinates.selected)
		{
			// Hide all subordinates (cascading)
			for (i in dealer.immediateSubordinates)
			{
				this.hideDealer(dealer.immediateSubordinates[i], true);
			}
		
			// Hide this dealer's normal option
			dealer.elmOption.selected		= false;
			dealer.elmOption.style.display  = "none";
		}
		else
		{
			// The "including subordinates" option is not selected
			dealer.elmOption.style.display = "";
			
			// Show all immediate subordinates, if they aren't already shown and this.controls.dealersToggleRootLevelDealers.checked == false
			if (!this.controls.dealersToggleRootLevelDealers.checked)
			{
				for (i in dealer.immediateSubordinates)
				{
					this.showDealer(dealer.immediateSubordinates[i], false);
					if (dealer.immediateSubordinates[i].isManager)
					{
						// The Subordinate is also a manager, so handle it and its subordinates
						this.updateOptionsForManager(dealer.immediateSubordinates[i]);
					}
				}
			}
		}
	},

	showDealer : function(dealer, bolShowSubordinates)
	{
		var i;
		dealer.elmOption.style.display  = "";
		if (dealer.elmOptionIncludingSubordinates)
		{
			dealer.elmOptionIncludingSubordinates.style.display = "";
		}
		
		if (bolShowSubordinates && dealer.immediateSubordinates)
		{
			// Hide all the subordinates of this dealer
			for (i in dealer.immediateSubordinates)
			{
				// I know I could just go this.showDealer(dealer.immediateSubordinates[i], true), but this should be much faster
				if (dealer.immediateSubordinates[i].isManager)
				{
					// The subordinate is a manager, so hide it and its subbies
					this.showDealer(dealer.immediateSubordinates[i], true);
				}
				else
				{
					// Just hide the subbie
					dealer.immediateSubordinates[i].elmOption.style.display = "";
				}
			}
		}
	},

	hideDealer : function(dealer, bolHideSubordinates)
	{
		var i;
		dealer.elmOption.selected		= false;
		dealer.elmOption.style.display  = "none";
		if (dealer.elmOptionIncludingSubordinates)
		{
			dealer.elmOptionIncludingSubordinates.selected		= false;
			dealer.elmOptionIncludingSubordinates.style.display = "none";
		}
		
		if (bolHideSubordinates && dealer.immediateSubordinates)
		{
			// Hide all the subordinates of this dealer
			for (i in dealer.immediateSubordinates)
			{
				// I know I could just go this.hideDealer(dealer.immediateSubordinates[i], true), but this should be much faster
				if (dealer.immediateSubordinates[i].isManager)
				{
					// The subordinate is a manager, so hide it and its subbies
					this.hideDealer(dealer.immediateSubordinates[i], true);
				}
				else
				{
					// Just hide the subbie
					dealer.immediateSubordinates[i].elmOption.selected		= false;
					dealer.immediateSubordinates[i].elmOption.style.display = "none";
				}
			}
		}
	},
	
	submitReport : function()
	{
		var i, j;
		var objConstraints = {};
		var strTimestamp;
		var bolIncludeSubordinates;
		
		if (!this.validateConstraints())
		{
			return;
		}
		
		// Prepare the report constraints
		// Prepare time constraints
		if (this.controls.earliestTime)
		{
			if (this.controls.earliestTime.value != '')
			{
				var strTimestamp = this.controls.earliestTime.value;
				objConstraints.earliestTime = strTimestamp.substr(15, 4) +"-"+ strTimestamp.substr(12, 2) +"-"+ strTimestamp.substr(9, 2) +" "+ strTimestamp.substr(0, 8);
			}
			else
			{
				objConstraints.earliestTime = null;
			}
		}
		
		if (this.controls.latestTime)
		{
			if (this.controls.latestTime.value != '')
			{
				var strTimestamp = this.controls.latestTime.value;
				objConstraints.latestTime = strTimestamp.substr(15, 4) +"-"+ strTimestamp.substr(12, 2) +"-"+ strTimestamp.substr(9, 2) +" "+ strTimestamp.substr(0, 8);
			}
			else
			{
				objConstraints.latestTime = null;
			}
		}
		
		// Prepare the status constraints
		if (this.controls.statuses)
		{
			objConstraints.statusIds = new Array();
			
			for (i=0, j=this.controls.statuses.options.length; i<j; i++)
			{
				if (this.controls.statuses.options[i].selected)
				{
					objConstraints.statusIds.push(parseInt(this.controls.statuses.options[i].value));
				}
			}
		}
		
		// Prepare the dealer constraints
		if (this.controls.dealers)
		{
			objConstraints.dealers = new Array();
			
			for (i in this.dealers)
			{
				bolIncludeSubordinates = (this.dealers[i].elmOptionIncludingSubordinates && this.dealers[i].elmOptionIncludingSubordinates.selected) ? true : false;
				if (bolIncludeSubordinates || this.dealers[i].elmOption.selected)
				{
					// Include the dealer
					objConstraints.dealers.push(	{
														id					: this.dealers[i].id,
														includeSubordinates : bolIncludeSubordinates
													}
												);
				}
			}
		}
		
		// Prepare the columns to include in the report
		var arrColumns = new Array();
		for (i=0, j=this.controls.selectedColumns.options.length; i<j; i++)
		{
			if (this.controls.selectedColumns.options[i].selected)
			{
				arrColumns.push(this.controls.selectedColumns.options[i].value)
			}
		}
		
		// Prepare rendermode
		var renderMode = this.controls.renderMode.value;

		jsonFunc = jQuery.json.jsonFunction(this.submitReportReturnHandler.bind(this), null, "Sale", "buildReport");
		Vixen.Popup.ShowPageLoadingSplash("Generating Report", null, null, null, 100);
		jsonFunc(this.strReportType, objConstraints, arrColumns, renderMode);
	},


	submitReportReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success && response.Success == true)
		{
			// The report was successfully generated
			if (response.ReportLocation != undefined)
			{
				// Check if there were any records in the report
				if (response.RecordCount > 0)
				{
					// There were records
					// Retrieve the report from the server by relocating the page
					window.location = response.ReportLocation;
				}
				else
				{
					// There weren't any records
					Vixen.Popup.Confirm("The report was successfully generated, but there aren't any records in it.<br /><br />Do you still want to download the report?", 
										function(){alert("report location = "+ response.ReportLocation); window.location = response.ReportLocation;});
				}
			}
			else
			{
				$Alert("ERROR: No location was given for the report.  Please notify your system administrators");
			}
			
			
		}
		else
		{
			$Alert("Generating the report failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	// Returns true if valid, else returns an appropriate message describing why the dealer control is invalid
	validateDealerControl : function()
	{
		// Just check that at least one dealer has been selected
		var i, j;
		
		for (i=0, j=this.controls.dealers.options.length; i<j; i++)
		{   
			if (this.controls.dealers.options[i].selected)
			{
				// At least one dealer is selected
				return true;
			}
		}
		
		return "<br />At least one dealer must be selected";
	},
	
	validateStatusControl : function()
	{
		// Just check that at least one status has been selected
		var i, j;
		
		for (i=0, j=this.controls.statuses.options.length; i<j; i++)
		{   
			if (this.controls.statuses.options[i].selected)
			{
				// At least one status is selected
				return true;
			}
		}
		
		return "<br />At least one status must be selected";
	},
	
	validateTimeframeControls : function()
	{
		var strProblemsEncountered = "";
		
		// Check that both the earliest time and latest time are in the correct format
		if (!$Validate("DateTime", this.controls.earliestTime.value, false))
		{
			strProblemsEncountered += "<br />Earliest Time must be in the format hh:mm:ss dd/mm/yyyy";
		}
		
		if (!$Validate("DateTime", this.controls.latestTime.value, false))
		{
			strProblemsEncountered += "<br />Latest Time must be in the format hh:mm:ss dd/mm/yyyy";
		}
		
		return (strProblemsEncountered == "")? true : strProblemsEncountered;
	},
	
	// Theoretically I should allow them to create a report with no columns, but that would be stupid
	validateColumnsControl : function()
	{
		var i, j;
		
		// Check that at least 1 column has been selected, even though a report with just 1 column is pretty dumnb
		for (i=0, j=this.controls.selectedColumns.options.length; i<j; i++)
		{
			if (this.controls.selectedColumns.options[i].selected)
			{
				// At least one column is selected
				return true;
			}
		}
		
		return "<br />At least one Report Column must be selected";
	},
	
	validateConstraints : function()
	{
		var strProblemsEncountered = "";
		var mixValid;
		
		switch (this.strReportType)
		{
			case this.REPORT_TYPE_COMMISSIONS:
				mixValid = this.validateDealerControl();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				mixValid = this.validateTimeframeControls();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				break;

			case this.REPORT_TYPE_OUTSTANDING_SALES:
				mixValid = this.validateStatusControl();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				break;

			case this.REPORT_TYPE_SALE_HISTORY:
				mixValid = this.validateStatusControl();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				mixValid = this.validateTimeframeControls();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				break;

			case this.REPORT_TYPE_SALE_ITEM_HISTORY:
				mixValid = this.validateStatusControl();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				mixValid = this.validateTimeframeControls();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				break;

			case this.REPORT_TYPE_SALE_ITEM_STATUS:
				mixValid = this.validateStatusControl();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				mixValid = this.validateTimeframeControls();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				break;

			case this.REPORT_TYPE_SALE_ITEM_SUMMARY:
				mixValid = this.validateDealerControl();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				mixValid = this.validateTimeframeControls();
				if (mixValid !== true)
				{
					strProblemsEncountered += mixValid;
				}
				break;
		}
		
		mixValid = this.validateColumnsControl();
		if (mixValid !== true)
		{
			strProblemsEncountered += mixValid;
		}
	
		if (strProblemsEncountered != "")
		{
			$Alert("The following problems were encountered:"+ strProblemsEncountered);
			return false;
		}
		return true;
		
	}

};
