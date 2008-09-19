var FlexSearch = {
	quickSearchOnEnter : function(event)
	{
		if (event && event.keyCode && event.keyCode == 13)
		{
			this.quickSearch();
		}
	},

	quickSearch : function()
	{
		// Trigger the search
		var mixSearchType	= $ID('quick_search_category').value;
		var strCriteria		= $ID('search_string').value;
		
		strCriteria = strCriteria.replace(new RegExp("^([\\s]+)|([\\s]+)$", "gm"), "");
		
		if (strCriteria == '')
		{
			$Alert("Please enter a search term");
			return;
		}
		
		if (mixSearchType == "tickets")
		{
			this.ticketSearch(strCriteria);
		}
		else
		{
			this.customerSearch(parseInt(mixSearchType),strCriteria, null, null, 0, true);
		}
	},
	
	ticketSearch : function(strCriteria)
	{
		document.location = "reflex.php/Ticketing/QuickSearch/?for=" + strCriteria;
	},
	
	customerSearch : function(intSearchType, strConstraint, intConstraintType, bolIncludeArchived, intOffset, bolForceRefresh)
	{
		intConstraintType	= (intConstraintType == undefined)? null : intConstraintType;
		bolIncludeArchived	= (bolIncludeArchived == undefined)? false : bolIncludeArchived;
		intOffset			= (intOffset == undefined)? 0 : intOffset;
		bolForceRefresh		= (bolForceRefresh == undefined)? true : bolForceRefresh;
	
		remoteClass		= 'Customer_Search';
		remoteMethod	= 'search';
		jsonFunc		= jQuery.json.jsonFunction(this.customerSearchReturnHandler.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Searching", null, null, null, 1500);
		jsonFunc(intSearchType, strConstraint, intConstraintType, bolIncludeArchived, intOffset, bolForceRefresh);
	},
	
	// Return handler for search
	customerSearchReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			if (response.RecordCount == 0)
			{
				$Alert("No results were found for the search criteria");
				return;
			}
			
			with (response)
			{
				this.displayResults(RecordCount, Results, SearchType, Constraint, ConstraintType, IncludeArchived);
			}
			
			return;
		}
		else
		{
			if (response.ErrorMessage != undefined)
			{
				$Alert("Search failed<br />" + response.ErrorMessage);
			}
			else
			{
				$Alert("Search failed");
			}
		}
	},
	
	// This will store the results, if the 
	cachedResults : null,
	
	displayResults : function(intRecCount, strResultsHtml, intSearchType, mixConstraint, intConstraintType, bolIncludeArchived)
	{
		this.cachedResults = {
								recordCount		: intRecCount,
								resultsHtml		: strResultsHtml,
								searchType		: intSearchType,
								constraint		: mixConstraint,
								constraintType	: intConstraintType,
								includeArchived	: bolIncludeArchived
							}

		// Check if the CustomerSearch popup is currently open
		var elmPopup = Vixen.Popup.GetPopupElement("CustomerSearch");
		if (elmPopup == null)
		{
			// The popup doesn't exist yet
			this.displayPopup(false);
		}
		else
		{
			// The popup is already displayed.
			// stick the results in it
			this.popupControls.ResultsContainer.innerHTML = strResultsHtml;
			
			// Update the controls to reflect the search
			this.popupControls.SearchType.value			= intSearchType;
			this.searchTypeComboOnChange();
			this.popupControls.ConstraintType.value		= (intConstraintType == null)? 0 : intConstraintType;
			this.popupControls.Constraint.value			= mixConstraint;
			this.popupControls.IncludeArchived.checked	= bolIncludeArchived;
			Vixen.Popup.Centre("CustomerSearch");
		}
	},
	
	displayPopup : function(bolFlushCachedResults)
	{
		// Default to flush cached results, if not specified
		bolFlushCachedResults = (bolFlushCachedResults == undefined)? true : bolFlushCachedResults;
		
		if (bolFlushCachedResults)
		{
			this.cachedResults = null;
		}
		remoteClass		= 'Customer_Search';
		remoteMethod	= 'buildCustomerSearchPopup';
		jsonFunc		= jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 1500);
		jsonFunc();
	},
	
	/* This will store all the important fields on the popup.  That being:
	 *	SearchType, Constraint, ConstraintType, ResultsContainer
	 */
	popupControls : {},
	
	// This stores the various search types, and their allowed constraint types
	searchTypes : {},
	
	displayPopupReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			this.searchTypes = response.SearchTypes;
			Vixen.Popup.Create("CustomerSearch", response.PopupContent, "extralarge", "centre", "modal", "Search");
			
			// Initialise the popup
			var elmSearchForm = $ID("CustomerSearchPopupForm");
			var elmControl;
			for (var i=0; i < elmSearchForm.elements.length; i++)
			{
				elmControl = elmSearchForm.elements[i];
				if (elmControl.hasAttribute("name"))
				{
					this.popupControls[elmControl.getAttribute("name")] = elmControl;
				}
			}
			
			this.popupControls.ResultsContainer = $ID("CustomerSearchPopupResultsContainer");
			
			// Register event listeners
			Event.startObserving(this.popupControls.SearchType, "keypress", this.searchTypeComboOnChange.bind(this), true);
			Event.startObserving(this.popupControls.SearchType, "click", this.searchTypeComboOnChange.bind(this), true);
			Event.startObserving(this.popupControls.Constraint, "keypress", this.constraintTextBoxOnEnter.bind(this), true);
			
			
			// Populate the SearchType combo
			for (var i in this.searchTypes)
			{
				this.popupControls.SearchType.appendChild(new Option(this.searchTypes[i].Name, parseInt(i), false, false));
			}
			
			if (this.cachedResults != null)
			{
				// There are results to be displayed
				this.popupControls.ResultsContainer.innerHTML	= this.cachedResults.resultsHtml;
				this.popupControls.SearchType.value				= this.cachedResults.searchType;
				this.searchTypeComboOnChange();
				this.popupControls.ConstraintType.value			= (this.cachedResults.constraintType == null)? 0 : this.cachedResults.constraintType;
				this.popupControls.Constraint.value				= this.cachedResults.constraint;
				this.popupControls.IncludeArchived.checked		= this.cachedResults.includeArchived;

				Vixen.Popup.Centre("CustomerSearch");
			}
			else
			{
				// There are no results to display, populate the ConstraintType combo based on the current value of the SearchType combo
				this.searchTypeComboOnChange();
			}
		}
		else
		{
			if (response.ErrorMessage != undefined)
			{
				$Alert("Failed to open Customer Search popup<br />" + response.ErrorMessage);
			}
			else
			{
				$Alert("Failed to open Customer Search popup");
			}
		}

	},
	
	
	constraintTextBoxOnEnter : function(event)
	{
		if (event && event.keyCode && event.keyCode == 13)
		{
			this.submitSearch();
		}
	},

	searchTypeComboOnChange : function(objEvent)
	{
		var intSearchType = this.popupControls.SearchType.value;
		var currentSearchType = this.popupControls.SearchType.getAttribute("currentSearchType");
		
		if (currentSearchType == intSearchType)
		{
			// Don't do anything
			return;
		}

		// Load in the allowable constraints for this search type
		//this.popupControls.ConstraintType.options.length = 0;
		while (this.popupControls.ConstraintType.options.length != 0)
		{
			this.popupControls.ConstraintType.removeChild(this.popupControls.ConstraintType.options[0]);
		}
		
		this.popupControls.ConstraintType.appendChild(new Option(" ", 0, true, true));
		for (var i in this.searchTypes[intSearchType].AllowableConstraintTypes)
		{
			this.popupControls.ConstraintType.appendChild(new Option(this.searchTypes[intSearchType].AllowableConstraintTypes[i].Name, parseInt(i), false, false));
		}
		
		// Update the current search type
		this.popupControls.SearchType.setAttribute("currentSearchType", intSearchType);
	},
	
	// This is used to retrieve a new page of results, when a result set is paginated
	getResults : function(intOffset)
	{
		if (this.cachedResults != null)
		{
			this.customerSearch(this.cachedResults.searchType, this.cachedResults.constraint, this.cachedResults.constraintType, this.cachedResults.includeArchived, intOffset, false);
		}
		else
		{
			// There are no cached results.  Trigger a fresh search based on the popup's input controls
			this.submitSearch();
		}
	},
	
	submitSearch : function()
	{
		var strConstraint = this.popupControls.Constraint.value.replace(new RegExp("^([\\s]+)|([\\s]+)$", "gm"), "");;
		
		if (strConstraint == '')
		{
			$Alert("Please enter a search term");
		}
		else
		{
			var intConstraintType = (this.popupControls.ConstraintType.value == 0)? null : parseInt(this.popupControls.ConstraintType.value);
			this.customerSearch(parseInt(this.popupControls.SearchType.value), strConstraint, intConstraintType, this.popupControls.IncludeArchived.checked, 0, true);
		}

	}
	
};
