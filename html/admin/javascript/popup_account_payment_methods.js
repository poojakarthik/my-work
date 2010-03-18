var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	
	initialize	: function($super, iAccountId)
	{
		// Popup Settings
		$super(60);
		this.setTitle("Change Payment Method");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		
		// Set Content
		this.setContent('Test: '+iAccountId);
		
		// Setup Buttons
		this.domChangePaymentMethodButton				= document.createElement('button');
		this.domChangePaymentMethodButton.innerHTML		= "Change Payment Method";
		this.domCancelButton							= document.createElement('button');
		this.domCancelButton.innerHTML					= "Cancel";
		this.setFooterButtons([this.domChangePaymentMethodButton, this.domCancelButton], false);
		
		// Display Popup
		this.display();
		
		
		this._addEventListeners();
		
	},
	
	addPaymentMethod	: function()
	{
		
	},
	
});

