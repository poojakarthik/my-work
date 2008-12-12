var SaleCancellationPopup = {
	POPUP_ID : "SaleCancellationPopup",
	
	// This will store details of the sale items, to show what will also be cancelled if the sale is cancelled
	// (I was originally going to use the reflex_popup stuff, but it doesn't integrate well when the Vixen.Popup stuff is also being used)
	popup		: null,
	
	sale		: null,
	controls	: null,
	
	load : function(intSaleId)
	{
		// Make the ajax request
		jsonFunc = jQuery.json.jsonFunction(this.loadReturnHandler.bind(this), null, "Sale", "buildSaleCancellationPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1000);
		jsonFunc(intSaleId);
	},
	
	loadReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.success && response.success == true)
		{
			this.sale = response.sale;
			this.popup = Vixen.Popup.Create(this.POPUP_ID, response.popupContent, "ExtraLarge", "centre", "modal", 'Cancel Sale '+ this.sale.id);
		
			// Display the popup
			this.initialisePopup();
		}
		else
		{
			$Alert("Initialising Sale Cancellation failed. " + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
		}
	},
	
	initialisePopup : function()
	{
		// Store references to all input elements on the SaleCancellationForm
		var elmForm = document.getElementById("SaleCancellationForm");
		this.controls = {};
		for (var i=0, j=elmForm.elements.length; i<j; i++)
		{
			if (elmForm.elements[i].hasAttribute("name"))
			{
				this.controls[elmForm.elements[i].getAttribute("name")] = elmForm.elements[i];
			}
		}
		
		// Set up event listeners
		Event.startObserving(this.controls.cancelButton, "click", this.closePopup.bind(this), true);
		Event.startObserving(this.controls.okButton, "click", this.cancelSale.bind(this), true);
	},
	
	closePopup: function()
	{
		Vixen.Popup.Close(this.popup);
		this.popup = null;
	},
	
	cancelSale: function(objEvent, bolConfirmed)
	{
		if (!bolConfirmed)
		{
			if (!this.validateForm())
			{
				return;
			}
			
			Vixen.Popup.Confirm("Are you sure you want to cancel this sale?", function(){SaleCancellationPopup.cancelSale(null, true)});
			return;
		}
		
		jsonFunc = jQuery.json.jsonFunction(this.cancelSaleOkReturnHandler.bind(this), this.cancelSaleFailedReturnHandler.bind(this), "Sale", "cancelSale");
		Vixen.Popup.ShowPageLoadingSplash("Cancelling", null, null, null, 1000);
		jsonFunc(parseInt(this.sale.id), this.controls.reason.value);
	},
	
	validateForm : function()
	{
		var strProblemsEncountered = "";
		
		if (!$Validate("NotEmptyString", this.controls.reason.value, false))
		{
			strProblemsEncountered += "<br />A reason for cancelling the sale must be specified";
		}
		if (strProblemsEncountered != "")
		{
			$Alert("The following problems were encountered:"+ strProblemsEncountered);
			return false;
		}
		return true;
	},
	
	cancelSaleOkReturnHandler: function(strMessage)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		Vixen.Popup.Close(this.POPUP_ID);
		
		var strPrompt;
		
		if (strMessage)
		{
			strPrompt = "The sale has been successfully cancelled.<br />" + strMessage + "<br /><br />Elements of the page are now out of date.<br />Do you want to refresh the page?";
		}
		else
		{
			strPrompt = "The sale has been successfully cancelled.<br /><br />Elements of the page are now out of date.<br />Do you want to refresh the page?"
		}
		
		Vixen.Popup.Confirm(strPrompt, function(){window.location = window.location});
	},
	
	cancelSaleFailedReturnHandler: function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		$Alert(response.ERROR);
	},
	
	
	
};
