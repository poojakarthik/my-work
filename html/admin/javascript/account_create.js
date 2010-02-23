

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
			if (!Reflex_Validation.abn(this.value))
			{
				this.className = "invalid";
				return "Invalid ABN specified";
			}
			this.className = "valid";
			return true;
		}

		// Validate an ACN
		this.oForm.getInputs('text','Account[ACN]').first().validate = function ()
		{
			if (!Reflex_Validation.acn(this.value))
			{
				this.className = "invalid";
				return "Invalid ACN specified";
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

		// Some examples from rich. to be continued...
		// Get value from state <select>
		this.oForm.select('select[name="Account[State]"]').first();
		// Get value from Billing Type <radio>
		this.oForm.select('input[type=radio][name="Account[BillingType]"][checked]').first();
		
		
		// Validate State
		this.oForm.AccountState = $ID('Account[State]');
		alert(this.oForm.AccountState.getValue());
		
		this.oForm.AccountState.validate = function ()
		{
			alert('position state');
		}
		this.oForm.AccountState.observe('change', this.oForm.AccountState.validate.bind(this.oForm.AccountState));

		// Add dynamic validation
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			if (aInputs[i].validate)
			{
				aInputs[i].observe('keyup', aInputs[i].validate.bind(aInputs[i]));
				aInputs[i].observe('change', aInputs[i].validate.bind(aInputs[i]));
			}
		}

	},
	
	submit	: function()
	{
		var aErrors	= [];

		// Check if everything is valid
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


		if (this.oForm.elements ['Account[State]'].value == '')
		{
			aErrors.push('Invalid State');
			this.oForm.elements ['Account[State]'].className = "invalid";
		}
		if (this.oForm.elements ['Account[CustomerGroup]'].value == '')
		{
			aErrors.push('Invalid Customer Group');
			this.oForm.elements ['Account[CustomerGroup]'].className = "invalid";
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
			return false;
		}
		
		// No errors -- submit
		return true;
	}

	
});