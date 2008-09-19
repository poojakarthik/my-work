var FlexCustomerVerification = {
	POPUP_ID : "CustomerVerification",

	elmVerificationStatus		: null,
	elmAccount					: null,
	elmContact					: null,
	elmAccountDetailsContainer	: null,
	elmContactDetailsContainer	: null,
	elmAccountBusinessName		: null,
	elmAccountTradingName		: null,
	elmAccountBusinessNameContainer		: null,
	elmAccountTradingNameContainer		: null,
	elmAccountAddress			: null,
	elmAccountAddressVerified	: null,
	elmAccountABNContainer		: null,
	elmAccountABN				: null,
	
	elmContactDetailsContainer	: null,
	elmContactPassword			: null,
	elmContactDOBContainer		: null,
	elmContactDOB				: null,
	elmContactEmailContainer	: null,
	elmContactEmail				: null,
	elmAccountButton			: null,
	elmContactButton			: null,
	intAccountId	: null,
	intContactId	: null,
	bolFixedContact	: null,
	bolFixedAccount	: null,
	objCustomer		: null,
	intValidationLevel	: 0,

	// Loads the popup, for the customer declared by intContact and intAccount
	load : function(intContact, intAccount)
	{
		jsonFunc = jQuery.json.jsonFunction(this.loadReturnHandler.bind(this), null, "Customer_Verification", "buildPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1500);
		jsonFunc(intContact, intAccount);
	},
	
	loadReturnHandler : function(response)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			// Display the popup
			Vixen.Popup.Create(this.POPUP_ID, response.PopupContent, "medium", "centre", "modal", "Customer Verification");
			
			// Initialise the popup
			this.initialisePopup(response.Customer);
		}
		else
		{
			$Alert("Loading the Customer Verification Popup failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	initialisePopup : function(objCustomer)
	{
		// Grab references to all important items on the form
		this.elmVerificationStatus		= $ID('CustomerVerificationPopup_VerificationStatus');
		this.elmAccount					= $ID('CustomerVerificationPopup_Account');
		this.elmContact					= $ID('CustomerVerificationPopup_Contact');
		this.elmAccountDetailsContainer	= $ID('CustomerVerificationPopup_AccountDetailsContainer');
		this.elmContactDetailsContainer	= $ID('CustomerVerificationPopup_ContactDetailsContainer');
		this.elmAccountBusinessName		= $ID('CustomerVerificationPopup_AccountBusinessName');
		this.elmAccountTradingName		= $ID('CustomerVerificationPopup_AccountTradingName');
		this.elmAccountBusinessNameContainer	= $ID('CustomerVerificationPopup_AccountBusinessNameContainer');
		this.elmAccountTradingNameContainer		= $ID('CustomerVerificationPopup_AccountTradingNameContainer');
		
		this.elmAccountAddress			= $ID('CustomerVerificationPopup_AccountAddress');
		this.elmAccountAddressVerified	= $ID('CustomerVerificationPopup_AccountAddressVerified');
		this.elmAccountABNContainer		= $ID('CustomerVerificationPopup_AccountABNContainer');
		this.elmAccountABN				= $ID('CustomerVerificationPopup_AccountABN');
		this.elmContactDetailsContainer	= $ID('CustomerVerificationPopup_ContactDetailsContainer');
		this.elmContactPassword			= $ID('CustomerVerificationPopup_ContactPassword');
		this.elmContactDOBContainer		= $ID('CustomerVerificationPopup_ContactDOBContainer');
		this.elmContactDOB				= $ID('CustomerVerificationPopup_ContactDOB');
		this.elmContactEmailContainer	= $ID('CustomerVerificationPopup_ContactEmailContainer');
		this.elmContactEmail			= $ID('CustomerVerificationPopup_ContactEmail');
		this.elmAccountButton			= $ID('CustomerVerificationPopup_AccountButton');
		this.elmContactButton			= $ID('CustomerVerificationPopup_ContactButton');

		this.bolFixedContact	= (objCustomer.FixedContact)? true : false;
		this.bolFixedAccount	= (objCustomer.FixedAccount)? true : false;
		this.objCustomer		= objCustomer;
		this.intContactId		= (objCustomer.SelectedContact == null)? 0 : objCustomer.SelectedContact;
		this.intAccountId		= (objCustomer.SelectedAccount == null)? 0 : objCustomer.SelectedAccount;

		if (this.bolFixedContact)
		{
			// The contact is fixed, which implies that the Account isn't fixed, even if there is only 1 account
			// if intAccountId has not been specified (== 0) and there is only 1 account, then use it
			if (this.elmAccount.options.length == 1 && this.intAccountId == 0)
			{
				this.intAccountId = this.elmAccount.options[1].value;
			}
			this.elmAccount.value = this.intAccountId;
			
			// Register listeners for the Account combobox
			Event.startObserving(this.elmAccount, "keypress", this.accountComboOnChange.bind(this), true);
			Event.startObserving(this.elmAccount, "click", this.accountComboOnChange.bind(this), true);
			
			this.elmAccount.focus();
		}

		if (this.bolFixedAccount)
		{
			// The account is fixed, which implies that the contact isn't fixed, even if there is only 1 contact
			// if intContactId has not been specified (== 0) and there is only 1 contact, then use it
			if (this.elmContact.options.length == 1 && this.intContactId == 0)
			{
				this.intContactId = this.elmContact.options[1].value;
			}
			this.elmContact.value = this.intContactId;
			
			// Rigister listeners for the Contact combobox
			Event.startObserving(this.elmContact, "keypress", this.contactComboOnChange.bind(this), true);
			Event.startObserving(this.elmContact, "click", this.contactComboOnChange.bind(this), true);
			
			this.elmContact.focus();
		}

		// Register all other event listeners
		//TODO!
		
		
		this.setupAccountDetails();
		this.setupContactDetails();
	},
	
	accountComboOnChange : function(objEvent)
	{
		// Check if the Account has actually been changed
		if (this.intAccountId != this.elmAccount.value)
		{
			// It has changed
			this.intAccountId = parseInt(this.elmAccount.value);
			this.setupAccountDetails();
		}
	},
	
	setupAccountDetails : function()
	{
		if (this.intAccountId == 0)
		{
			// No Account is specified
			this.elmVerificationStatus.innerHTML = "No Account Selected";
			
			// Hide Account details
			this.elmAccountDetailsContainer.style.display = "none";
		}
		else
		{
			// An account has been specified
			var objAccount = this.objCustomer.Accounts[this.intAccountId];

			// Set up the Account Details form
			this.elmAccountBusinessName.innerHTML	= objAccount.BusinessName;
			this.elmAccountBusinessNameContainer.style.display = (objAccount.BusinessName != null)? "table-row" : "none";
			
			this.elmAccountTradingName.innerHTML	= objAccount.TradingName;
			this.elmAccountTradingNameContainer.style.display = (objAccount.TradingName != null)? "table-row" : "none";
			
			this.elmAccountAddress.innerHTML		= objAccount.Address;
			
			
			
			this.elmAccountDetailsContainer.style.display = "block";
			
			Vixen.Popup.Centre(this.POPUP_ID);
		}
	
	},
	
	contactComboOnChange : function(objEvent)
	{
		// Check if the Contact has actually been changed
		if (this.intContactId != this.elmContact.value)
		{
			// It has changed
			this.intContactId = parseInt(this.elmContact.value);
			this.setupContactDetails();
		}
	},
	
	setupContactDetails : function()
	{
		if (this.intContactId == 0)
		{
			// No Contact is specified
			this.elmVerificationStatus.innerHTML = "No Contact Selected";
			
			// Hide Contact details
			this.elmContactDetailsContainer.style.display = "none";
		}
		else
		{
			// An Contact has been specified
			this.elmVerificationStatus.innerHTML = this.objCustomer.Contacts[this.intContactId].Name;
			
			// Set up the Contact Details form
			//TODO
			
			this.elmContactDetailsContainer.style.display = "block";

			Vixen.Popup.Centre(this.POPUP_ID);
		}
	}
	
};
