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
	
	displayPopupReturnHandler : function(oResponse) {
		Vixen.Popup.ClosePageLoadingSplash();

		if (oResponse.Success) {
			this.searchTypes = oResponse.SearchTypes;
			
			Vixen.Popup.Create("CustomerOverdueList", oResponse.PopupContent, "extralarge", "centre", "modal", "Overdue Customers");
			
			// Initialise the popup
			// TODO!
		} else {
			jQuery.json.errorPopup(oResponse, "Failed to open the 'Overdue Customers' popup");
		}
	}
	
};
