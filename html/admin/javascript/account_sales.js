var AccountSales = {
	POPUP_ID : "AccountSales",
	
	showSales : function(intAccountId)
	{
		// Make the ajax request to get the config record, and dealers
		jsonFunc = jQuery.json.jsonFunction(this.showSalesReturnHandler.bind(this), null, "Account_Sales", "buildAccountSalesPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1000);
		jsonFunc(intAccountId);
	},
	
	showSalesReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.success && response.success == true)
		{
			var strPopupTitle = "Sales for "+ response.accountId +" - "+ response.accountName;
			
			Vixen.Popup.Create(this.POPUP_ID, response.popupContent, "ExtraLarge", "centre", "modal", strPopupTitle);
		}
		else
		{
			$Alert("Loading the Account Sales popup failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
		}
	}
};
