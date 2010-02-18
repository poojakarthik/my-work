

Account_Create = Class.create
({
	initialize	: function(oForm)
	{
		// alert('position construct');
		this.oForm					= oForm;
		this.oForm.oAccountCreate	= this;
		
		this.oForm.getInputs('text','Account[BusinessName]').first().validate = function ()
		{

			// alert('position getInputs1');
			if (this.value.length < 5)
			{
			 return "Business name must be at least 5 characters long.";
			}
			
			return true;
			
		}
		
		this.oForm.getInputs('text','Account[ABN]').first().validate = function ()
		{

			alert('position geInputs2');
			if (!Reflex_Validation.abn(this.value))
			{
				alert('Invalid ABN specified');
				return "Invalid ABN specified";
			}
			else
			{
				alert('correct abn');
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
		alert('position submit');
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
			alert(aErrors.length);
			var sErrors	= '';
			for (var i = 0, j = aErrors.length; i < j; i++)
			{
				sErrors	+= aErrors[i] + "\n";
			}
			alert(sErrors);
			return false;
		}
		
		// No errors -- submit
		return true;
	}

	
});