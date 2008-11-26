var SaleHistory = {
	POPUP_ID : "SaleHistory",

	// Loads the "Sale History" popup
	loadPopup : function(intSaleId)
	{
		// Display the Popup
		jsonFunc = jQuery.json.jsonFunction(this.loadPopupReturnHandler.bind(this), null, "Sale", "buildHistoryPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1000);
		jsonFunc(intSaleId);
	},
	
	loadPopupReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success && response.Success == true)
		{
			// Display the popup
			var objPopup = Vixen.Popup.Create(this.POPUP_ID, response.PopupContent, "ExtraLarge", "centre", "modal", "History of Sale: "+ response.saleId);
		}
		else
		{
			$Alert("Loading the History popup failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	}
};
