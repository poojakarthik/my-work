var FlexCustomerVerification = {
	POPUP_ID		: "CustomerVerification",
	PAGE_CONTACT	: "contact",
	PAGE_ACCOUNT	: "account",

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
	elmAccountACNContainer		: null,
	elmAccountACN				: null,
	
	elmContactPassword			: null,
	elmContactDOBContainer		: null,
	elmContactDOBDay			: null,
	elmContactDOBMonth			: null,
	elmContactDOBYear			: null,
	elmContactEmailContainer	: null,
	elmContactEmail				: null,
	elmAccountButton			: null,
	elmContactButton			: null,
	
	objAccount	: null,
	objContact	: null,
	bolFixedContact	: null,
	bolFixedAccount	: null,
	objCustomer		: null,
	intRequiredAccountScore : 0,
	intRequiredContactScore : 0,
	intPossibleAccountScore : null,
	intPossibleContactScore : null,
	objAccountScores		: null,
	objContactScores		: null,
	
	// Loads the popup, for the customer declared by intContact and intAccount
	load : function(intContact, intAccount)
	{
		jsonFunc = jQuery.json.jsonFunction(this.loadReturnHandler.bind(this), null, "Customer_Verification", "buildPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 100);
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
			this.initialisePopup(response.Customer, response.RequiredScoreToVerifyAccount, response.RequiredScoreToVerifyContact, response.ViewContactLink, response.ViewAccountLink);
		}
		else
		{
			$Alert("Loading the Customer Verification Popup failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	initialisePopup : function(objCustomer, intRequiredAccountScore, intRequiredContactScore, strContactLink, strAccountLink)
	{
		this.intRequiredAccountScore = intRequiredAccountScore;
		this.intRequiredContactScore = intRequiredContactScore;
		
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
		this.elmAccountACNContainer		= $ID('CustomerVerificationPopup_AccountACNContainer');
		this.elmAccountACN				= $ID('CustomerVerificationPopup_AccountACN');
		this.elmContactDetailsContainer	= $ID('CustomerVerificationPopup_ContactDetailsContainer');
		this.elmContactPassword			= $ID('CustomerVerificationPopup_ContactPassword');
		this.elmContactDOBContainer		= $ID('CustomerVerificationPopup_ContactDOBContainer');
		this.elmContactDOBDay			= $ID('CustomerVerificationPopup_ContactDOBDay');
		this.elmContactDOBMonth			= $ID('CustomerVerificationPopup_ContactDOBMonth');
		this.elmContactDOBYear			= $ID('CustomerVerificationPopup_ContactDOBYear');
		this.elmContactEmailContainer	= $ID('CustomerVerificationPopup_ContactEmailContainer');
		this.elmContactEmail			= $ID('CustomerVerificationPopup_ContactEmail');
		this.elmAccountButton			= $ID('CustomerVerificationPopup_AccountButton');
		this.elmContactButton			= $ID('CustomerVerificationPopup_ContactButton');

		this.bolFixedContact	= (objCustomer.FixedContact)? true : false;
		this.bolFixedAccount	= (objCustomer.FixedAccount)? true : false;
		this.objCustomer		= objCustomer;
		this.objContact			= (objCustomer.SelectedContact == null)? null : objCustomer.Contacts[objCustomer.SelectedContact];
		this.objAccount			= (objCustomer.SelectedAccount == null)? null : objCustomer.Accounts[objCustomer.SelectedAccount];

		if (this.bolFixedContact)
		{
			// The contact is fixed, which implies that the Account isn't fixed, even if there is only 1 account
			// if intAccountId has not been specified (== 0) and there is only 1 account, then use it
			if (this.elmAccount.options.length == 1 && this.intAccountId == 0)
			{
				this.objAccount = objCustomer.Accounts[this.elmAccount.options[1].value];
			}
			this.elmAccount.value = (this.objAccount == null)? 0 : this.objAccount.Id;
			
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
				this.objContact = objCustomer.Contacts[this.elmContact.options[1].value];
			}
			this.elmContact.value = (this.objContact == null)? 0 : this.objContact.Id;
			
			// Rigister listeners for the Contact combobox
			Event.startObserving(this.elmContact, "keypress", this.contactComboOnChange.bind(this), true);
			Event.startObserving(this.elmContact, "click", this.contactComboOnChange.bind(this), true);
			
			this.elmContact.focus();
		}

		// Register all other event listeners
		Event.startObserving(this.elmAccountABN, "keyup", this.verifyAccountABN.bind(this), true);
		Event.startObserving(this.elmAccountACN, "keyup", this.verifyAccountACN.bind(this), true);
		Event.startObserving(this.elmAccountAddressVerified, "change", this.verifyAccountAddress.bind(this), true);

		Event.startObserving(this.elmContactPassword, "keyup", this.verifyContactPassword.bind(this), true);
		
		Event.startObserving(this.elmContactDOBDay, "keyup", this.verifyContactDOB.bind(this), true);
		Event.startObserving(this.elmContactDOBMonth, "keyup", this.verifyContactDOB.bind(this), true);
		Event.startObserving(this.elmContactDOBYear, "keyup", this.verifyContactDOB.bind(this), true);
		Event.startObserving(this.elmContactDOBDay, "click", this.verifyContactDOB.bind(this), true);
		Event.startObserving(this.elmContactDOBMonth, "click", this.verifyContactDOB.bind(this), true);
		Event.startObserving(this.elmContactDOBYear, "click", this.verifyContactDOB.bind(this), true);

		Event.startObserving(this.elmContactEmail, "keyup", this.verifyContactEmail.bind(this), true);
		
		this.setupAccountDetails();
		this.setupContactDetails();
	},
	
	accountComboOnChange : function(objEvent)
	{
		// Check if the Account has actually been changed
		var intNewAccountId = parseInt(this.elmAccount.value);
		
		if ((this.objAccount == null && intNewAccountId != 0) || (this.objAccount != null && this.objAccount.Id != intNewAccountId))
		{
			// It has changed
			this.objAccount = (intNewAccountId != 0)? this.objCustomer.Accounts[intNewAccountId] : null;
			this.setupAccountDetails();
		}
	},
	
	contactComboOnChange : function(objEvent)
	{
		// Check if the Contact has actually been changed
		var intNewContactId = parseInt(this.elmContact.value);
		
		if ((this.objContact == null && intNewContactId != 0) || (this.objContact != null && this.objContact.Id != intNewContactId))
		{
			// It has changed
			this.objContact = (intNewContactId != 0)? this.objCustomer.Contacts[intNewContactId] : null;
			this.setupContactDetails();
		}
	},
	
	setupAccountDetails : function()
	{
		if (this.objAccount == null)
		{
			// No Account is specified
			this.elmVerificationStatus.innerHTML = "No Account Selected";
			
			// Hide Account details
			this.elmAccountDetailsContainer.style.display = "none";
			
			// Initialise scoring
			this.intPossibleAccountScore = null;
			this.objAccountScores = null;
		}
		else
		{
			// An account has been specified
			// Initialise scoring
			this.objAccountScores = {},
			
			// Work out the possible score that can be achieved
			this.intPossibleAccountScore = 0;
			for (var prop in this.objAccount.Verifiable)
			{
				if (this.objAccount.Verifiable[prop] != null)
				{
					this.intPossibleAccountScore += this.objAccount.Verifiable[prop].Weight;
					this.objAccountScores[prop] = null;
				}
			}

			// Set up the Account Details form
			this.elmAccountBusinessName.innerHTML = this.objAccount.BusinessName;
			this.elmAccountBusinessNameContainer.style.display = (this.objAccount.BusinessName != null)? "table-row" : "none";
			
			this.elmAccountTradingName.innerHTML = this.objAccount.TradingName;
			this.elmAccountTradingNameContainer.style.display = (this.objAccount.TradingName != null)? "table-row" : "none";
			
			this.elmAccountAddress.innerHTML = this.objAccount.Verifiable.Address.Value;
			this.verifyAccountAddress();
			
			if (this.objAccount.Verifiable.ABN != null)
			{
				this.verifyAccountABN();
				this.elmAccountABNContainer.style.display = "table-row";
			}
			else
			{
				this.elmAccountABNContainer.style.display = "none";
			}
			
			if (this.objAccount.Verifiable.ACN != null)
			{
				this.verifyAccountACN();
				this.elmAccountACNContainer.style.display = "table-row";
			}
			else
			{
				this.elmAccountACNContainer.style.display = "none";
			}
			
			// Show the Account Details
			this.elmAccountDetailsContainer.style.display = "block";
			

			Vixen.Popup.Centre(this.POPUP_ID);
		}
		this.verify();
	},
	
	setupContactDetails : function()
	{
		if (this.objContact == null)
		{
			// No Contact is specified
			this.elmVerificationStatus.innerHTML = "No Contact Selected";
			
			// Hide Contact details
			this.elmContactDetailsContainer.style.display = "none";

			// Initialise scoring
			this.intPossibleContactScore = null;
			this.objContactScores = null;
		}
		else
		{
			// Initialise scoring
			this.objContactScores = {},
			
			// Work out the possible score that can be achieved
			this.intPossibleContactScore = 0;
			for (var prop in this.objContact.Verifiable)
			{
				if (this.objContact.Verifiable[prop] != null)
				{
					this.intPossibleContactScore += this.objContact.Verifiable[prop].Weight;
					this.objContactScores[prop] = null;
				}
			}
			
			// Set up the Contact Details form
			this.verifyContactPassword();
			
			if (this.objContact.Verifiable.DOB != null)
			{
				this.verifyContactDOB();
				this.elmContactDOBContainer.style.display = "table-row";
			}
			else
			{
				this.elmContactDOBContainer.style.display = "none";
			}

			if (this.objContact.Verifiable.Email != null)
			{
				this.verifyContactEmail();
				this.elmContactEmailContainer.style.display = "table-row";
			}
			else
			{
				this.elmContactEmailContainer.style.display = "none";
			}
			
			this.elmContactDetailsContainer.style.display = "block";
			

			Vixen.Popup.Centre(this.POPUP_ID);
		}
		this.verify();
	},
	
	getScore : function(objScores)
	{
		var intScore = 0;
		for (var prop in objScores)
		{
			intScore += (objScores[prop] != null)? objScores[prop] : 0;
		}
		return intScore;
	},
	
	
	// Checks if either the contact or acount have been verified, and displays the appropriate buttons/message
	verify : function()
	{
		var bolContactVerified = false;
		var bolAccountVerified = false;
		var strMsg = "Customer is not verified";
		
		// Check if the contact can be verified
		if (this.objContact != null)
		{
			var intContactScore = this.getScore(this.objContactScores);
			// A contact has been selected
			if (this.intPossibleContactScore < this.intRequiredContactScore)
			{
				// There is no way the contact can be verified
				// Allow the user to continue
				strMsg = "This contact cannot possibly be verified with the current information stored for them.  Please set up their details";
				bolContactVerified = true;
			}
			else if (intContactScore >= this.intRequiredContactScore)
			{
				// The customer has been verified
				strMsg = "Customer is Verified";
				bolContactVerified = true;
			}
		}
		
		if (this.objAccount != null)
		{
			var intAccountScore = this.getScore(this.objAccountScores);
			// An Account has been selected
			// We are not concerned with whether or not it is possible to verify an account
			if (bolContactVerified)
			{
				// The contact has been verified, so let them view the account
				bolAccountVerified = true;
			}
			else if (intAccountScore >= this.intRequiredAccountScore)
			{
				bolAccountVerified = true;
				strMsg = "Customer is verified";
			}
		}
		
		this.elmContactButton.style.display = (bolContactVerified)? "inline" : "none";
		this.elmAccountButton.style.display = (bolAccountVerified)? "inline" : "none";
		this.elmVerificationStatus.innerHTML = strMsg;
	},
	
	verifyAccountAddress : function(objEvent)
	{
		var intScore = (this.elmAccountAddressVerified.checked)? this.objAccount.Verifiable.Address.Weight : 0;
		if (this.objAccountScores.Address != intScore)
		{
			// The score has changed
			this.objAccountScores.Address = intScore;
			this.verify();
		}
	},
	
	verifyAccountABN : function(objEvent)
	{
		var strSha1ABN		= hex_sha1(this.elmAccountABN.value);
		var intScore		= 0;
		var strClassName	= "";
		if (strSha1ABN == this.objAccount.Verifiable.ABN.Sha1)
		{
			// The property is valid
			intScore		= this.objAccount.Verifiable.ABN.Weight;
			strClassName	= "valid";
		}
		if (this.objAccountScores.ABN != intScore)
		{
			// The state of the property has changed
			this.objAccountScores.ABN		= intScore;
			this.elmAccountABN.className	= strClassName;
			this.verify();
		}
	},
	
	verifyAccountACN : function(objEvent)
	{
		var strSha1ACN		= hex_sha1(this.elmAccountACN.value);
		var intScore		= 0;
		var strClassName	= "";
		if (strSha1ACN == this.objAccount.Verifiable.ACN.Sha1)
		{
			// The property is valid
			intScore		= this.objAccount.Verifiable.ACN.Weight;
			strClassName	= "valid";
		}
		if (this.objAccountScores.ACN != intScore)
		{
			// The state of the property has changed
			this.objAccountScores.ACN		= intScore;
			this.elmAccountACN.className	= strClassName;
			this.verify();
		}
	},
	
	verifyContactDOB : function(objEvent)
	{
		var strSha1DOB = hex_sha1(this.elmContactDOBDay.value +"/"+ this.elmContactDOBMonth.value +"/"+ this.elmContactDOBYear.value)//;
		
		var intScore		= 0;
		var strClassName	= "";
		if (strSha1DOB == this.objContact.Verifiable.DOB.Sha1)
		{
			// The property is valid
			intScore		= this.objContact.Verifiable.DOB.Weight;
			strClassName	= "valid";
		}
		if (this.objContactScores.DOB != intScore)
		{
			// The state of the property has changed
			this.objContactScores.DOB			= intScore;
			this.elmContactDOBDay.className		= strClassName;
			this.elmContactDOBMonth.className	= strClassName;
			this.elmContactDOBYear.className	= strClassName;
			this.verify();
		}
	},

	verifyContactEmail : function(objEvent)
	{
		var strSha1Email	= hex_sha1(this.elmContactEmail.value.toLowerCase());
		var intScore		= 0;
		var strClassName	= "";
		if (strSha1Email == this.objContact.Verifiable.Email.Sha1)
		{
			// The property is valid
			intScore		= this.objContact.Verifiable.Email.Weight;
			strClassName	= "valid";
		}
		if (this.objContactScores.Email != intScore)
		{
			// The state of the property has changed
			this.objContactScores.Email		= intScore;
			this.elmContactEmail.className	= strClassName;
			this.verify();
		}
	},

	verifyContactPassword : function(objEvent)
	{
		var strSha1Password	= hex_sha1(this.elmContactPassword.value);
		var intScore		= 0;
		var strClassName	= "";
		if (strSha1Password == this.objContact.Verifiable.Password.Sha1)
		{
			// The property is valid
			intScore		= this.objContact.Verifiable.Password.Weight;
			strClassName	= "valid";
		}
		if (this.objContactScores.Password != intScore)
		{
			// The state of the property has changed
			this.objContactScores.Password		= intScore;
			this.elmContactPassword.className	= strClassName;
			this.verify();
		}
	},

	// Verifies the customer on the server and records the customer in the employee's Account history
	verifyOnServer : function(intContactId, intAccountId, objVerifiedContactProperties, objVerifiedAccountProperties, strRequestedPage, bolOverrideVerification)
	{
		jsonFunc = jQuery.json.jsonFunction(this.verifyOnServerReturnHandler.bind(this), null, "Customer_Verification", "verify");
		jsonFunc(intContactId, intAccountId, objVerifiedContactProperties, objVerifiedAccountProperties, strRequestedPage, bolOverrideVerification);
	},
	
	verifyOnServerReturnHandler : function(response)
	{
		if (response.Success)
		{
			if (response.PageToRelocateTo)
			{
				window.location = response.PageToRelocateTo;
			}
			else
			{
				$Alert("Recording the customer in your Recent Customers list worked, but no page has been specified to relocate to.  You should be able to find the customer in the Recent Customers popup");
			}
		}
		else
		{
			$Alert("Verifying the customer on the server failed" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	viewContact : function()
	{
		var objVerifiedContactProperties = this.buildVerifiedProperties(this.objContactScores);
		var objVerifiedAccountProperties = this.buildVerifiedProperties(this.objAccountScores);
		var intContactId = (this.objContact != null)? this.objContact.Id : null;
		var intAccountId = (this.objAccount != null)? this.objAccount.Id : null;
		
		this.verifyOnServer(intContactId, intAccountId, objVerifiedContactProperties, objVerifiedAccountProperties, this.PAGE_CONTACT, false);
	},
	
	viewAccount : function()
	{
		var objVerifiedContactProperties = this.buildVerifiedProperties(this.objContactScores);
		var objVerifiedAccountProperties = this.buildVerifiedProperties(this.objAccountScores);
		var intContactId = (this.objContact != null)? this.objContact.Id : null;
		var intAccountId = (this.objAccount != null)? this.objAccount.Id : null;
		
		this.verifyOnServer(intContactId, intAccountId, objVerifiedContactProperties, objVerifiedAccountProperties, this.PAGE_ACCOUNT, false);
	},
	
	buildVerifiedProperties : function(objScores)
	{
		var objProperties = {};
		var strProperty;
		if (objScores == null)
		{
			return null;
		}
		
		for (strProperty in objScores)
		{
			if (objScores[strProperty] != 0 && objScores[strProperty] != null)
			{
				// The property is correct
				objProperties[strProperty] = this.getUserInput(strProperty);
			}
		}
		return objProperties;
	},

	// This will retrieve the value that the user has entered, for the property requested
	getUserInput : function(strProperty)
	{
		switch (strProperty.toLowerCase())
		{
			case "address":
				return this.elmAccountAddressVerified.checked;
				break;
			case "abn":
				return this.elmAccountABN.value;
				break;
			case "acn":
				return this.elmAccountACN.value;
				break;
			case "dob":
				return this.elmContactDOBDay.value +"/"+ this.elmContactDOBMonth.value +"/"+ this.elmContactDOBYear.value;
				break;
			case "email":
				return this.elmContactEmail.value;
				break;
			case "password":
				return this.elmContactPassword.value;
				break;
		}
	}
	
	
};
