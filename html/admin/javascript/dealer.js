var Dealer = {
	POPUP_ID		: "Dealer",

	objDealer : null,
	objCountryStates : null,
	controls : null,

	// Loads the "New Dealer" popup, which allows the user to create a new dealer, or base one on an Employee
	loadNewDealerPopup : function()
	{
		// Display the Popup to select the Dealer/Employee
		jsonFunc = jQuery.json.jsonFunction(this.loadNewDealerPopupReturnHandler.bind(this), null, "Dealer", "buildNewDealerSelectionPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1000);
		jsonFunc();
	},
	
	loadNewDealerPopupReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			// Check if there are any employees who can become dealers
			if (response.EmployeeCount > 0)
			{
				// Display the popup
				var objPopup = Vixen.Popup.Create(this.POPUP_ID, response.PopupContent, "Medium", "centre", "modal", "New Dealer");
				this.controls = {};
				this.controls.newDealerEmployeeIdCombo = $ID("NewDealerPopupEmployeeIdCombo");
			}
			else
			{
				// There are no eligible employees to base new dealers on, so just open the new dealer popup
				this.editDealer(null);
			}
		}
		else
		{
			$Alert("Loading the New Dealer Popup failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},

	newDealerPopupOkButtonOnClick : function()
	{
		// Check if an employee has been selected
		var intEmployeeId = parseInt(this.controls.newDealerEmployeeIdCombo.value);
		if (intEmployeeId == 0)
		{
			intEmployeeId = null;
		}
		this.editDealer(null, intEmployeeId);
	},

	// Loads the "Edit Dealer" popup
	editDealer : function(intDealerId, intEmployeeId)
	{
		intDealerId		= (intDealerId == undefined)? null : intDealerId;
		intEmployeeId	= (intEmployeeId == undefined)? null : intEmployeeId;
	
		jsonFunc = jQuery.json.jsonFunction(this.editDealerReturnHandler.bind(this), null, "Dealer", "buildEditDealerPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 100);
		jsonFunc(intDealerId, intEmployeeId);
	},
	
	editDealerReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			// Display the popup
			var objPopup = Vixen.Popup.Create(this.POPUP_ID, response.PopupContent, "Large", "centre", "modal", (response.Data.Dealer.id === null && response.Data.Dealer.employeeId === null)? "New Dealer" : "Dealer - " + response.Data.DealerName);
			
			// Initialise the popup
			this.initialiseEditPopup(response.Data, objPopup);
		}
		else
		{
			$Alert("Loading the EditDealer Popup failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	initialiseEditPopup : function(objData, objPopup)
	{
		Tabs.initialiseTabCollections(objPopup);
		
		Vixen.Popup.Centre(this.POPUP_ID);
		
		this.objDealer = objData.Dealer;
		this.objCountryStates = objData.CountryStates;
		
		// Store references to all input elements on the EditDealerPopupForm
		var elmForm = document.getElementById("EditDealerPopupForm");
		this.controls = {};
		for (var i=0, j=elmForm.elements.length; i<j; i++)
		{
			if (elmForm.elements[i].hasAttribute("name"))
			{
				this.controls[elmForm.elements[i].getAttribute("name")] = elmForm.elements[i];
			}
		}
		
		// Set up event listeners
		Event.startObserving(this.controls.countryId, "change", this.setUpStatesForCountry.bind(this, this.controls.countryId, this.controls.stateId), true);
		Event.startObserving(this.controls.postalCountryId, "change", this.setUpStatesForCountry.bind(this, this.controls.postalCountryId, this.controls.postalStateId), true);
		
		// Populate the controls
		this.controls.firstName.value		= this.objDealer.firstName;
		this.controls.lastName.value		= this.objDealer.lastName;
		this.controls.titleId.value			= this.objDealer.titleId;
		this.controls.username.value		= this.objDealer.username;
		this.controls.upLineId.value		= (this.objDealer.upLineId != null)? this.objDealer.upLineId : 0;
		this.controls.canVerify.checked		= (this.objDealer.canVerify == 1);
		this.controls.phone.value			= this.objDealer.phone;
		this.controls.mobile.value			= this.objDealer.mobile;
		this.controls.fax.value				= this.objDealer.fax;
		this.controls.email.value			= this.objDealer.email;
		this.controls.terminationDate.value	= this.objDealer.terminationDate;
		
		this.controls.dealerStatusId.value	= this.objDealer.dealerStatusId;
		
		this.controls.businessName.value	= this.objDealer.businessName;
		this.controls.tradingName.value		= this.objDealer.tradingName;
		this.controls.abn.value				= this.objDealer.abn;
		this.controls.abnRegistered.checked	= (this.objDealer.abnRegistered == 1);
		
		this.controls.commissionScale.value		= this.objDealer.commissionScale;
		this.controls.royaltyScale.value		= this.objDealer.royaltyScale;
		this.controls.bankAccountBsb.value		= this.objDealer.bankAccountBsb;
		this.controls.bankAccountNumber.value	= this.objDealer.bankAccountNumber;
		this.controls.bankAccountName.value		= this.objDealer.bankAccountName;
		this.controls.gstRegistered.value		= this.objDealer.gstRegistered;
		
		this.controls.addressLine1.value		= this.objDealer.addressLine1;
		this.controls.addressLine2.value		= this.objDealer.addressLine2;
		this.controls.suburb.value				= this.objDealer.suburb;
		this.controls.postcode.value			= this.objDealer.postcode;
		this.controls.countryId.value			= (this.objDealer.countryId != null)? this.objDealer.countryId : 0;
		this.setUpStatesForCountry(this.controls.countryId, this.controls.stateId);
		this.controls.stateId.value				= (this.objDealer.stateId != null)? this.objDealer.stateId : 0;
		
		this.controls.postalAddressLine1.value	= this.objDealer.postalAddressLine1;
		this.controls.postalAddressLine2.value	= this.objDealer.postalAddressLine2;
		this.controls.postalSuburb.value		= this.objDealer.postalSuburb;
		this.controls.postalPostcode.value		= this.objDealer.postalPostcode;
		this.controls.postalCountryId.value		= (this.objDealer.postalCountryId != null)? this.objDealer.postalCountryId : 0;
		this.setUpStatesForCountry(this.controls.postalCountryId, this.controls.postalStateId);
		this.controls.postalStateId.value		= (this.objDealer.postalStateId != null)? this.objDealer.postalStateId : 0;
		
		// Disable the fields that should not be changed if the dealer is an employee
		if (this.objDealer.employeeId !== null)
		{
			this.controls.firstName.disabled = true;
			this.controls.lastName.disabled = true;
			this.controls.username.disabled = true;
			this.controls.password.disabled = true;
			this.controls.password2.disabled = true;
			this.controls.phone.disabled = true;
			this.controls.mobile.disabled = true;
			this.controls.email.disabled = true;
		}
		
	},
	
	// Copies physical address details to the postal address details controls
	copyAddressDetails : function()
	{
		this.controls.postalAddressLine1.value	= this.controls.addressLine1.value;
		this.controls.postalAddressLine2.value	= this.controls.addressLine2.value;
		this.controls.postalSuburb.value		= this.controls.suburb.value;
		this.controls.postalPostcode.value		= this.controls.postcode.value;
		
		this.controls.postalCountryId.value		= this.controls.countryId.value;
		this.setUpStatesForCountry(this.controls.postalCountryId, this.controls.postalStateId);
		this.controls.postalStateId.value		= this.controls.stateId.value;
	},
	
	setUpStatesForCountry : function(elmCountry, elmState)
	{
		var intCountryId = elmCountry.value;

		// Load in the states for the chosen Country
		while (elmState.options.length != 0)
		{
			elmState.removeChild(elmState.options[0]);
		}
		
		// Add the blank option
		elmState.appendChild(new Option(" ", 0, true, true));
		
		if (this.objCountryStates[intCountryId] != undefined)
		{
			// Load in all the states
			for (var i in this.objCountryStates[intCountryId])
			{
				elmState.appendChild(new Option(this.objCountryStates[intCountryId][i], parseInt(i), false, false));
			}
		}
	},
	
	saveDealerDetails : function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			if (!this.validateDealerDetails())
			{
				return;
			}
			
			Vixen.Popup.Confirm("Are you sure you want to save changes?", function(){Dealer.saveDealerDetails(true)});
			return;
		}
		
		// Collect details to save
		var objDealer = {};
		objDealer.id					= this.objDealer.id;
		objDealer.employeeId			= this.objDealer.employeeId;
		
		objDealer.firstName				= this.controls.firstName.value;
		objDealer.lastName				= this.controls.lastName.value;
		objDealer.titleId				= (this.controls.titleId.value != 0)? parseInt(this.controls.titleId.value) : null;
		objDealer.username				= this.controls.username.value;
		objDealer.password				= this.controls.password.value;
		objDealer.upLineId				= (this.controls.upLineId.value != 0)? parseInt(this.controls.upLineId.value) : null;
		objDealer.canVerify				= this.controls.canVerify.checked;
		objDealer.phone					= this.controls.phone.value;
		objDealer.mobile				= this.controls.mobile.value;
		objDealer.fax					= this.controls.fax.value;
		objDealer.email					= this.controls.email.value;
		
		objDealer.dealerStatusId		= parseInt(this.controls.dealerStatusId.value);
		
		objDealer.businessName			= this.controls.businessName.value;
		objDealer.tradingName			= this.controls.tradingName.value;
		objDealer.abn					= this.controls.abn.value;
		objDealer.abnRegistered			= this.controls.abnRegistered.checked;
		
		objDealer.commissionScale		= (this.controls.commissionScale.value != '')? parseInt(this.controls.commissionScale.value) : null;
		objDealer.royaltyScale			= (this.controls.royaltyScale.value != '')? parseInt(this.controls.royaltyScale.value) : null;
		objDealer.bankAccountBsb		= this.controls.bankAccountBsb.value;
		objDealer.bankAccountNumber		= this.controls.bankAccountNumber.value;
		objDealer.bankAccountName		= this.controls.bankAccountName.value;
		objDealer.gstRegistered			= this.controls.gstRegistered.checked;
		
		objDealer.addressLine1			= this.controls.addressLine1.value;
		objDealer.addressLine2			= this.controls.addressLine2.value;
		objDealer.suburb				= this.controls.suburb.value;
		objDealer.postcode				= this.controls.postcode.value;
		objDealer.countryId				= (this.controls.countryId.value != 0)? parseInt(this.controls.countryId.value) : null;
		objDealer.stateId				= (this.controls.stateId.value != 0)? parseInt(this.controls.stateId.value) : null;
		
		objDealer.postalAddressLine1	= this.controls.postalAddressLine1.value;
		objDealer.postalAddressLine2	= this.controls.postalAddressLine2.value;
		objDealer.postalSuburb			= this.controls.postalSuburb.value;
		objDealer.postalPostcode		= this.controls.postalPostcode.value;
		objDealer.postalCountryId		= (this.controls.postalCountryId.value != 0)? parseInt(this.controls.postalCountryId.value) : null;
		objDealer.postalStateId			= (this.controls.postalStateId.value != 0)? parseInt(this.controls.postalStateId.value) : null;
		
		
		// Format the Termination Date
		if (this.controls.terminationDate.value != '')
		{
			var strDate = this.controls.terminationDate.value;
			objDealer.terminationDate = strDate.substr(6, 4) +"-"+ strDate.substr(3, 2) + "-" + strDate.substr(0, 2);
		}
		else
		{
			objDealer.terminationDate = null;
		}
		
		jsonFunc = jQuery.json.jsonFunction(this.saveDealerDetailsReturnHandler.bind(this), null, "Dealer", "saveDealerDetails");
		Vixen.Popup.ShowPageLoadingSplash("Saving", null, null, null, 100);
		jsonFunc(objDealer);
	},


	saveDealerDetailsReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			$Alert("The dealer was successfully saved");
			
			// Update dealer object
			this.objDealer = response.Dealer;
		}
		else
		{
			$Alert("Saving the dealer failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	validateDealerDetails : function()
	{
		var strProblemsEncountered = "";
		
		if (!$Validate("NotEmptyString", this.controls.firstName.value, false))
		{
			strProblemsEncountered += "<br />First Name must be specified";
		}
		if (!$Validate("NotEmptyString", this.controls.lastName.value, false))
		{
			strProblemsEncountered += "<br />Last Name must be specified";
		}
		if (!$Validate("NotEmptyString", this.controls.username.value, false))
		{
			strProblemsEncountered += "<br />Username must be specified";
		}
		if (this.objDealer.password == null && !$Validate("NotEmptyString", this.controls.password.value, false))
		{
			strProblemsEncountered += "<br />Password must be specified";
		}
		else if (this.controls.password.value != this.controls.password2.value)
		{
			strProblemsEncountered += "<br />Password fields do not match";
		}
		if (!$Validate("EmailAddress", this.controls.email.value, true))
		{
			strProblemsEncountered += "<br />Invalid Email Address";
		}
		if (!$Validate("ShortDate", this.controls.terminationDate.value, true))
		{
			strProblemsEncountered += "<br />Termination date must be blank or in the format dd/mm/yyyy";
		}
		if (!$Validate("ABN", this.controls.abn.value, true))
		{
			strProblemsEncountered += "<br />Invalid ABN";
		}
		if (!$Validate("PositiveInteger", this.controls.commissionScale.value, true))
		{
			strProblemsEncountered += "<br />Invalid Commission Scale";
		}
		if (!$Validate("PositiveInteger", this.controls.royaltyScale.value, true))
		{
			strProblemsEncountered += "<br />Invalid Commission Scale";
		}
		
		if (strProblemsEncountered != "")
		{
			$Alert("The following problems were encountered:"+ strProblemsEncountered);
			return false;
		}
		return true;
		
	}
};
