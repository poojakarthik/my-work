

Account_Create = Class.create
({
	initialize	: function(oForm)
	{
		this.oForm					= oForm;
		this.oForm.oAccountCreate	= this;
		
		// Validate Business Name
		this.oForm.getInputs('text','Account[BusinessName]').first().validate = function ()
		{
			if (this.value.length < 5)
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
			if (this.value.length < 5)
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
			if (this.value.length < 4)
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
			if (this.value == '')
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
			if (this.value == '')
			{
				this.className = "invalid";
				return "Invalid Customer Group";	
			}
			this.className = "valid";
			return true;
		}
		
		/*
		this.oForm.select('input[type=radio][name="Account[BillingType]"][checked=checked]').first().validate = function ()
		{
			if(!this.checked)
			{
				this.className = "invalid";
				return "Invalid Payment Method";
			}
			this.className = "valid";
			return true;
		}
		*/
		
		// Add dynamic validation ( Event listeners, for Input Elements )
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			if (aInputs[i].validate)
			{
				aInputs[i].observe('keyup', aInputs[i].validate.bind(aInputs[i]));
				aInputs[i].observe('change', aInputs[i].validate.bind(aInputs[i]));
			}
		}
		// Event listeners for select Elements
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
		
		var aErrors	= [];

		// Check if <input> fields are valid.
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
		// Same as above except this handles the <select> elements.
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
		
		// By default no contact type has been selected
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

		// Validate new primary contact details
		if(intValidateExistingContact == 0)
		{
			if (isNaN($ID('Contact[Title]').value))
			{
				aErrors.push('Invalid Contact Title Selected');
			}
			if ($ID('Contact[FirstName]').value.length < 1)
			{
				aErrors.push('First Name must be at least 5 characters long');
			}
			if ($ID('Contact[LastName]').value.length < 1)
			{
				aErrors.push('Last Name must be at least 5 characters long');
			}
			if (isNaN($ID('Contact[DOB][day]').value))
			{
				aErrors.push('Invalid Date Of Birth Day');
			}
			if (isNaN($ID('Contact[DOB][month]').value))
			{
				aErrors.push('Invalid Date Of Birth Month');
			}
			if (isNaN($ID('Contact[DOB][year]').value))
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
			if ($ID('Contact[Password]').value.length < 8)
			{
				aErrors.push('Password must be at least 8 characters long');
			}
		}
		
		
		// By default no valid billing type has been selected
		var intFoundCheckedBillingType = 0;
		
		for (var aSelect = this.oForm.select('input[type=radio][name="Account[BillingType]"]'), i = 0, j = aSelect.length; i < j; i++)
		{
			if (aSelect[i].checked)
			{
				// Invoice
				if (aSelect[i].value == 3)
				{
					intFoundCheckedBillingType = 1;	
				}
				// Direct Debit
				if (aSelect[i].value == 1)
				{
					intFoundCheckedBillingType = 1;	
					
					if ($ID('DDR[BankName]').value == '')
					{
						aErrors.push('Payment Method Error: BankName');
					}
					if ($ID('DDR[BSB]').value == '')
					{ 
						aErrors.push('Payment Method Error: BSB');
					}
					if ($ID('DDR[AccountNumber]').value == '') 
					{ 
						aErrors.push('Payment Method Error: AccountNumber');
					}
					if ($ID('DDR[AccountName]').value == '') 
					{ 
						aErrors.push('Payment Method Error: AccountName');
					}

				}
				// Credit Card
				if (aSelect[i].value == 2)
				{
					intFoundCheckedBillingType = 1;	
					
					if ($ID('CC[CardType]').value == '') 
					{ 
						aErrors.push('Payment Method Error: CardType');
					}
					if ($ID('CC[Name]').value == '') 
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
		
		// If no billing type is found, add it to the error array.
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
		if(!Reflex_Validation.abn($ID('Account[ABN]').value) && !Reflex_Validation.acn($ID('Account[ACN]').value))
		{
			aErrors.push('A valid ABN or ACN is required.');
		}
		
		// Alert errors, then fail
		if (aErrors.length)
		{
			var sErrors	= 'Please check the following:\n\n';
			for (var i = 0, j = aErrors.length; i < j; i++)
			{
				sErrors	+= "- " + aErrors[i] + "\n";
			}
			alert(sErrors);
			// return false;
			return true;
		}
		
		// No errors -- submit
		return true;
	}

	
});