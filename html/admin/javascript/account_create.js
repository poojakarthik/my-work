

Account_Create = Class.create
({
	
	initialize	: function(oForm)
	{

	
		//----------------------------------------------------------------//
		// Load Constants
		//----------------------------------------------------------------//
	
		// Flex.Constant.loadConstantGroup(['BillingType', 'account_status'], this._onConstantLoad.bind(this))
		Flex.Constant.loadConstantGroup('BillingType', this._onConstantLoad.bind(this))
		
		this.oForm					= oForm;
		this.oForm.oAccountCreate	= this;
	

		//----------------------------------------------------------------//
		// Event listeners To Validate Proposed Account
		//----------------------------------------------------------------//
		
		// Validate Business Name
		this.oForm.getInputs('text','Account[BusinessName]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Business name must be at least 5 characters long.";
			}
			this.className = "valid";
			return true;
		}

		// Validate an ABN
		this.oForm.getInputs('text','Account[ABN]').first().validate = function ()
		{
			if (this.value !== '' && !Reflex_Validation.abn(this.value))
			{
				this.className = "invalid";
				return "Invalid ABN specified";
			}
			if(this.value == '')
			{
				this.className = "";
				return true;
			}
			this.className = "valid";
			return true;
		}

		// Validate an ACN
		this.oForm.getInputs('text','Account[ACN]').first().validate = function ()
		{
			if (this.value !== '' && !Reflex_Validation.acn(this.value))
			{
				this.className = "invalid";
				return "Invalid ACN specified";
			}
			if(this.value == '')
			{
				this.className = "";
				return true;
			}
			this.className = "valid";
			return true;
		}

		// Validate Address line 1
		this.oForm.getInputs('text','Account[Address1]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Address (Line 1)";
			}
			this.className = "valid";
			return true;
		}

		// Validate Suburb
		this.oForm.getInputs('text','Account[Suburb]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Suburb";
			}
			this.className = "valid";
			return true;
		}

		// Validate Postcode
		this.oForm.getInputs('text','Account[Postcode]').first().validate = function ()
		{
			if (this.value.match (/^\d{4}$/) === null)
			{
				this.className = "invalid";
				return "Invalid Postcode";	
			}
			this.className = "valid";
			return true;
		}

		// Validate State
		this.oForm.select('select[name="Account[State]"]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid State";	
			}
			this.className = "valid";
			return true;
		}

		// Validate Customer Group
		this.oForm.select('select[name="Account[CustomerGroup]"]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Customer Group";	
			}
			this.className = "valid";
			return true;
		}
		// Event listeners for Input Elements
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			if (aInputs[i].validate)
			{
				aInputs[i].observe('keyup', aInputs[i].validate.bind(aInputs[i]));
				aInputs[i].observe('change', aInputs[i].validate.bind(aInputs[i]));
			}
		}
		// Event listeners for Select Elements
		for (var aSelects = this.oForm.select('select'), i = 0, j = aSelects.length; i < j; i++)
		{
			if (aSelects[i].validate)
			{
				aSelects[i].observe('keyup', aSelects[i].validate.bind(aSelects[i]));
				aSelects[i].observe('change', aSelects[i].validate.bind(aSelects[i]));
			}
		}

	},
	
	submit	: function()
	{

		
		//----------------------------------------------------------------//
		// Validate Proposed Account
		//----------------------------------------------------------------//
		
		var aErrors	= [];

		// Run Event listeners for Input Elements
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			if (aInputs[i].validate)
			{
				var mValid	= aInputs[i].validate();
				if (mValid !== true)
				{
					aErrors.push(mValid);
				}
			}
		}
		// Run Event listeners for Select Elements
		for (var aSelects = this.oForm.select('select'), i = 0, j = aSelects.length; i < j; i++)
		{
			if (aSelects[i].validate)
			{
				var mValid	= aSelects[i].validate();
				if (mValid !== true)
				{
					aErrors.push(mValid);
				}
			}
		}
		

		//----------------------------------------------------------------//
		// Validate Proposed Primary Contact
		//----------------------------------------------------------------//

		var intFoundCheckedContactType = 0;
		var intValidateExistingContact = null;
		
		if($ID('Contact[USE]'))
		{
			intValidateExistingContact = 0;
			intFoundCheckedContactType = 1;
		}
		
		for (var aSelect = this.oForm.select('input[type=radio][name="Contact[USE]"]'), i = 0, j = aSelect.length; i < j; i++)
		{
			if (aSelect[i].checked)
			{
				intFoundCheckedContactType = 1;
				// Select an existing contact
				if (aSelect[i].value == 1)
				{
					intValidateExistingContact = 1;
					if (isNaN($ID('Contact[Id]').value))
					{
						aErrors.push('Invalid Primary Contact Selected');
					}
				}
				if (aSelect[i].value == 0)
				{
					intValidateExistingContact = 0;
				}
			}
			
		}

		if(intValidateExistingContact == 0)
		{
			if ($ID('Contact[Title]').value.length == 0)
			{
				aErrors.push('Invalid Contact Title Selected');
			}
			if (!/\S/.test($ID('Contact[FirstName]').value))
			{
				aErrors.push('Invalid First Name');
			}
			if (!/\S/.test($ID('Contact[LastName]').value))
			{
				aErrors.push('Invalid Last Name');
			}
			if (isNaN($ID('Contact[DOB][Day]').value))
			{
				aErrors.push('Invalid Date Of Birth Day');
			}
			if (isNaN($ID('Contact[DOB][Month]').value))
			{
				aErrors.push('Invalid Date Of Birth Month');
			}
			if (isNaN($ID('Contact[DOB][Year]').value))
			{
				aErrors.push('Invalid Date Of Birth Year');
			}
			if (!_validate.email($ID('Contact[Email]').value))
			{
				aErrors.push('Invalid Email Address');
			}
			if (!_validate.fnnLandLine($ID('Contact[Phone]').value) && !_validate.fnnMobile($ID('Contact[Mobile]').value))
			{
				aErrors.push('A valid phone number OR Mobile must be provided');
			}
			if ($ID('Contact[Password]').value.length < Account_Create.DEFAULT_PASSWORD_LENGTH_REQUIREMENT)
			{
				aErrors.push('Password must be at least ' + Account_Create.DEFAULT_PASSWORD_LENGTH_REQUIREMENT + ' characters long');
			}
		}

		
		//----------------------------------------------------------------//
		// Validate Proposed Billing Details
		//----------------------------------------------------------------//

		var intFoundCheckedBillingType = 0;
		
		for (var aSelect = this.oForm.select('input[type=radio][name="Account[BillingType]"]'), i = 0, j = aSelect.length; i < j; i++)
		{
			if (aSelect[i].checked)
			{
				// Invoice
				if (aSelect[i].value == $CONSTANT.BILLING_TYPE_ACCOUNT)
				{
					intFoundCheckedBillingType = 1;	
				}
				// Direct Debit
				if (aSelect[i].value == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT)
				{
					intFoundCheckedBillingType = 1;	

					if (!/\S/.test($ID('DDR[BankName]').value))
					{
						aErrors.push('Payment Method Error: BankName');
					}
					if (!/\S/.test($ID('DDR[BSB]').value))
					{ 
						aErrors.push('Payment Method Error: BSB');
					}
					if (!/\S/.test($ID('DDR[AccountNumber]').value))
					{ 
						aErrors.push('Payment Method Error: AccountNumber');
					}
					if (!/\S/.test($ID('DDR[AccountName]').value))
					{ 
						aErrors.push('Payment Method Error: AccountName');
					}

				}
				// Credit Card
				if (aSelect[i].value == $CONSTANT.BILLING_TYPE_CREDIT_CARD)
				{
					intFoundCheckedBillingType = 1;	

					if (!/\S/.test($ID('DDR[CardType]').value))
					{ 
						aErrors.push('Payment Method Error: CardType');
					}
					if (!/\S/.test($ID('DDR[Name]').value))
					{ 
						aErrors.push('Payment Method Error: Name');
					}
					if ($ID('CC[CVV]').value.match (/^\d{3,4}$/) === null)
					{
						aErrors.push('Payment Method Error: CVV');
					}
					if (!_validate.creditCardNumber($ID('CC[CardNumber]').value, $ID('CC[CardType]').value))
					{
						aErrors.push('Payment Method Error: CardNumber');
					}
					if (!CreditCardPayment.checkExpiry($ID('CC[ExpMonth]').value, $ID('CC[ExpYear]').value))
					{
						aErrors.push('Payment Method Error: Expiry Date Mismatch');
					}
				}
			}
		}
		var intFoundCheckedDisableLatePayment = 0;
		for (var aSelect = this.oForm.select('input[type=radio][name="Account[DisableLatePayment]"]'), i = 0, j = aSelect.length; i < j; i++)
		{
			if (aSelect[i].checked)
			{
				intFoundCheckedDisableLatePayment = 1;
			}
		}
		
		// Check Billing Type
		if (intFoundCheckedBillingType == 0)
		{
			aErrors.push('Invalid Payment Method selected');
		}
		if (intFoundCheckedContactType == 0)
		{
			aErrors.push('Invalid Primary Contact Details');
		}
		if (intFoundCheckedDisableLatePayment == 0)
		{
			aErrors.push('No Late Payment option selected');
		}
		
		// ABN, ACN Checking
		if(!Reflex_Validation.abn($ID('Account[ABN]').value) && !Reflex_Validation.acn($ID('Account[ACN]').value))
		{
			aErrors.push('A valid ABN or ACN is required.');
		}
		

		//----------------------------------------------------------------//
		// Fail
		//----------------------------------------------------------------//

		// When we fail to create an account, load the error popup
		if (aErrors.length)
		{
			var sErrors	= 'Please check the following:\n\n';
			for (var i = 0, j = aErrors.length; i < j; i++)
			{
				sErrors	+= "- " + aErrors[i] + "\n";
			}
			alert(sErrors);
			return false;
		}
		

		//----------------------------------------------------------------//
		// Send Valid Response
		//----------------------------------------------------------------//
					
		return true;
	},
	

	_onConstantLoad : function ()
	{
		this.bConstantsLoaded = true;
	}

});


//----------------------------------------------------------------//
// Constants
//----------------------------------------------------------------//

Account_Create.DEFAULT_PASSWORD_LENGTH_REQUIREMENT	= 8;

