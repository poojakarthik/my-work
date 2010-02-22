

Account_Create = Class.create
({
	initialize	: function(oForm)
	{
		this.oForm					= oForm;
		this.oForm.oAccountCreate	= this;
		
		this.oForm.getInputs('text','Account[BusinessName]').first().validate = function ()
		{
			if (this.value.length < 5)
			{
				return "Business name must be at least 5 characters long.";
			}
			return true;
		}

		this.oForm.getInputs('text','Account[ABN]').first().validate = function ()
		{
			if (!Reflex_Validation.abn(this.value))
			{
				return "Invalid ABN specified";
			}
			return true;
		}
		this.oForm.getInputs('text','Account[ACN]').first().validate = function ()
		{
			if (!Reflex_Validation.acn(this.value))
			{
				return "Invalid ABN specified";
			}
			return true;
		}

		// Add dynamic validation
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			// alert(aInputs[i].validate);
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