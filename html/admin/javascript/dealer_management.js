var DealerManagement = {
	config : null,
	
	showConfig : function()
	{
		this.config = {};
		
		// Make the ajax request to get the config record, and dealers
		jsonFunc = jQuery.json.jsonFunction(this.showConfigReturnHandler.bind(this), null, "Dealer", "getDealerConfigDetails");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1000);
		jsonFunc();
	},
	
	showConfigReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.success && response.success == true)
		{
			// Store details
			this.config.dealerConfig = response.config;
			this.config.dealers = response.dealers;
		
			// Display the popup
			this.showConfigPopup();
		}
		else
		{
			$Alert("Loading the History popup failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
		}
	},
	
	
	
	showConfigPopup: function()
	{
		var popup = this.config.popup = new Reflex_Popup(40);
		
		popup.addCloseButton();
		popup.setTitle('Dealer Config');
		
		// Define footer Buttons
		var btnCancel = document.createElement("input");
		btnCancel.type = "button";
		btnCancel.value = "Close";
		Event.observe(btnCancel, "click", this.closeConfigPopup.bind(this), true);
		
		var btnSave = document.createElement("input");
		btnSave.type = "button";
		btnSave.value = "Save";
		Event.observe(btnSave, "click", this.saveDealerConfig.bind(this), true);
		
		popup.setFooterButtons([btnSave, btnCancel]);
		
		
		// Define Content
		var content = document.createElement('div');
		content.className = "GroupedContent";
		
		var table = document.createElement('table');
		content.appendChild(table);
		table.className = "form-data";
		
		var row = document.createElement('tr');
		table.appendChild(row);
		
		var defaultEmployeeManagerLabelContainer = document.createElement('td');
		row.appendChild(defaultEmployeeManagerLabelContainer);
		defaultEmployeeManagerLabelContainer.className = "title";
		defaultEmployeeManagerLabelContainer.appendChild(document.createTextNode("Default Employee Manager"));
		
		var defaultEmployeeManagerContainer = document.createElement('td');
		row.appendChild(defaultEmployeeManagerContainer);
		
		var defaultEmployeeManagerCombo = document.createElement('select');
		defaultEmployeeManagerContainer.appendChild(defaultEmployeeManagerCombo);
		defaultEmployeeManagerCombo.style.width = "100%";
		defaultEmployeeManagerCombo.appendChild(new Option("[None]", 0, false, true));
		
		var bolSelected = false;
		var strName;
		for (var i in this.config.dealers)
		{
			bolSelected = (this.config.dealers[i].id == this.config.dealerConfig.defaultEmployeeManagerDealerId);
			strName = this.config.dealers[i].username + " ( "+ this.config.dealers[i].name +" )";
			defaultEmployeeManagerCombo.appendChild(new Option(strName, this.config.dealers[i].id, false, bolSelected));
		}
		
		popup.setContent(content);
		popup.display();
		
		this.config.defaultEmployeeManagerCombo = defaultEmployeeManagerCombo;
	},
	
	closeConfigPopup: function(objEvent)
	{
		if (this.config && this.config.popup && this.config.popup != null)
		{
			this.config.popup.hide();
		}
		this.config.popup = null;
	},
	
	saveDealerConfig: function(objEvent, bolConfirmed)
	{
		if (!bolConfirmed)
		{
			if (!this.validateDealerConfigForm())
			{
				return;
			}
			
			if (parseInt(this.config.defaultEmployeeManagerCombo.value) == this.config.dealerConfig.defaultEmployeeManagerDealerId)
			{
				$Alert("No changes have been made");
				return;
			}
			
			Vixen.Popup.Confirm("Are you sure you want to save changes?", function(){DealerManagement.saveDealerConfig(null, true)});
			return;
		}
		
		
		// Prepare data for server
		var objConfig = {};
		objConfig.defaultEmployeeManagerDealerId = (this.config.defaultEmployeeManagerCombo.value != 0)? parseInt(this.config.defaultEmployeeManagerCombo.value) : null;
		
		jsonFunc = jQuery.json.jsonFunction(this.saveDealerConfigReturnHandler.bind(this), null, "Dealer", "saveDealerConfig");
		Vixen.Popup.ShowPageLoadingSplash("Saving", null, null, null, 1000);
		jsonFunc(objConfig);
	},
	
	validateDealerConfigForm: function()
	{
		return true;
	},
	
	saveDealerConfigReturnHandler: function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.success && response.success == true)
		{
			$Alert("The dealer configuration was successfully saved");
			this.closeConfigPopup();
			
			// HACK! we should really have a configurable event listener for when an Alert popup closes
			setTimeout("window.location = window.location", 1500);
		}
		else
		{
			$Alert("Saving the dealer configuration failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
		}
	}
	
	
};
