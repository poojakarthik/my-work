

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

		// Validate Billing Type, by default none have been selected.
		var intFoundCheckedBillingType = 0;
		
		// Iterate through the options
		for (var aSelect = this.oForm.select('input[type=radio][name="Account[BillingType]"]'), i = 0, j = aSelect.length; i < j; i++)
		{
			// Check if any are selected
			if (aSelect[i].checked)
			{
				// Found a selected item, there won't be an error in this case.
				intFoundCheckedBillingType = 1;
			}
		}
		// If no billing type is found, add it to the error array.
		if (intFoundCheckedBillingType == 0)
		{
			aErrors.push('Invalid Payment Method selected');
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