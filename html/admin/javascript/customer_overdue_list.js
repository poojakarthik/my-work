var FlexCustomerOverdueList = {
	
	displayPopup : function()
	{
		remoteClass		= 'Customer_OverdueList';
		remoteMethod	= 'buildPopup';
		jsonFunc		= jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	},
	
	/* This will store all the important fields on the popup.  That being:
	 */
	popupControls : {},
	
	displayPopupReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			this.searchTypes = response.SearchTypes;
			
			Vixen.Popup.Create("CustomerOverdueList", response.PopupContent, "extralarge", "centre", "modal", "Overdue Customers");
			
			// Initialise the popup
			// TODO!
		}
		else
		{
			$Alert("Failed to open the 'Overdue Customers' popup" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	}
	
};
