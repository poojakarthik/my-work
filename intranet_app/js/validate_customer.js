// class
function ValidateCustomerClass ()
{
	// internal input array
	this._objInput = {};
	
	// validate an input
	// returns true if input is valid
	// returns false if input is invalid
	this.ValidateInput = function(objObject)
	{
		if (objObject.type == 'checkbox')
		{
			this._objInput[objObject.id] =
			{
				'Level': objObject.getAttribute ("ValidLevel"),
				'Valid': (objObject.checked == true)
			}
		}
		else if (objObject.id == 'DOB-year' || objObject.id == 'DOB-month' || objObject.id == 'DOB-day')
		{
			var dobyearcorrect = parseInt (document.getElementById ('DOB-year').getAttribute ('ValidValue'));
			var dobmonthcorrect = parseInt (document.getElementById ('DOB-month').getAttribute ('ValidValue'));
			var dobdaycorrect = parseInt (document.getElementById ('DOB-day').getAttribute ('ValidValue'));
			
			var dobyeartest = parseInt (document.getElementById ('DOB-year').options [document.getElementById ('DOB-year').selectedIndex].value);
			var dobmonthtest = parseInt (document.getElementById ('DOB-month').options [document.getElementById ('DOB-month').selectedIndex].value);
			var dobdaytest = parseInt (document.getElementById ('DOB-day').options [document.getElementById ('DOB-day').selectedIndex].value);
			
			var Valid = (dobyearcorrect == dobyeartest && dobmonthcorrect == dobmonthtest && dobdaycorrect == dobdaytest);
			
			this._objInput[objObject.id] =
			{
				'Level': objObject.getAttribute ("ValidLevel"),
				'Valid': Valid
			}
			
			document.getElementById ('DOB-year').className = ((Valid) ? 'select-valid' : '');
			document.getElementById ('DOB-month').className = ((Valid) ? 'select-valid' : '');
			document.getElementById ('DOB-day').className = ((Valid) ? 'select-valid' : '');
		}
		else if (objObject.id == 'ABN' || objObject.id == 'ACN')
		{
			var testvalue = objObject.value.replace (/[\s]/g, '');
			var rightvalue = objObject.getAttribute ("ValidValue").replace (/[\s]/g, '');
			
			if (rightvalue == "")
			{
				objObject.disabled = true;
				return;
			}
			
			this._objInput[objObject.id] =
			{
				'Level': objObject.getAttribute ("ValidLevel"),
				'Valid': (testvalue == rightvalue)
			}
			
			objObject.className = ((testvalue == rightvalue) ? "input-string-valid" : "input-string");
		}
		else if (objObject.type == 'text')
		{
			if (objObject.getAttribute ("ValidValue") == "")
			{
				objObject.disabled = true;
				return;
			}
			
			this._objInput[objObject.id] =
			{
				'Level': objObject.getAttribute ("ValidLevel"),
				'Valid': (objObject.value == objObject.getAttribute ("ValidValue"))
			}
			
			objObject.className = ((objObject.value == objObject.getAttribute ("ValidValue")) ? "input-string-valid" : "input-string");
		}
		
		if (this.IsValidated ())
		{
			document.getElementById ("Confirm").disabled = false;
			document.getElementById ("Confirm").className = "input-submit";
		}
		else
		{
			document.getElementById ("Confirm").disabled = true;
			document.getElementById ("Confirm").className = "input-submit-disabled";
		}
	}
	
	// check if we have enough valid information to allow access
	// returns true or false, Pablo says 'work out for yourself what that's about'
	this.IsValidated = function()
	{
		var objInput;
		var arrScore = new Array ();
		
		for (i=1; i <= 5; ++i)
		{
			arrScore[i] = 0;
		}
		
		// for each input
		for (objInput in this._objInput)
		{
			var objItem = this._objInput [objInput];
			// add to score object
			
			arrScore[parseInt(objItem.Level)] += ((objItem.Valid == true) ? 1 : 0);
		}
		
		// see if we have a high enough score to be valid
		
		// 2 items from level 1
		if (arrScore[1] >= 2)
		{
			return true;
		}
		
		// 1 item from level 1 and 2 items from level 2
		if (arrScore[1] >= 1 && arrScore[2] >= 2)
		{
			return true;
		}
		
		// return false by default
		return false;
	}

}

// instanciate the object
ValidateCustomer = new ValidateCustomerClass;

window.addEventListener (
	'load',
	function ()
	{
		ValidateCustomer.ValidateInput (document.getElementById ('Account'));
		ValidateCustomer.ValidateInput (document.getElementById ('ABN'));
		ValidateCustomer.ValidateInput (document.getElementById ('ACN'));
		ValidateCustomer.ValidateInput (document.getElementById ('Invoice'));
	},
	true
);
