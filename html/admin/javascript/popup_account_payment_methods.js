var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	
	initialize	: function($super, iAccountId)
	{
		// Explicitly include the JS file "Reflex_Template.js"
	
		//----------------------------------------------------------------//
		// Retrieve Data required to build the page
		//----------------------------------------------------------------//
		
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 0);
	
		// (jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this), alert('fail'), 'Account', 'getPaymentMethods'))(iAccountId);
		this._getPaymentMethods			= jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this), null, 'Account', 'getPaymentMethods');
		this._getPaymentMethods(iAccountId);
		
	},
	
	displayPopupReturnHandler	: function(response)
	{
				
		Vixen.Popup.ClosePageLoadingSplash();
		
		if (response.success && response.success == true)
		{


		}

		else
		{

		}
		
	},
	
	addPaymentMethod	: function()
	{
		
	},
	
});

