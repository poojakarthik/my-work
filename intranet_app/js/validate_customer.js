// class
function ValidateCustomerClass ()
{
	// internal input array
	this._objInput = {};
	
	// do we have a contact
	this.HasContact = false;
	
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
		else if (objObject.id == 'ABN' || objObject.id == 'ACN')
		{
			var testvalue = objObject.value.replace (/[\s]/g, '');
			var rightvalue = objObject.getAttribute ("ValidValue").replace (/[\s]/g, '');
			
			if (rightvalue == "")
			{
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
			document.getElementById ("FormSubmit").disabled = false;
			document.getElementById ("FormSubmit").className = "input-submit";
		}
		else
		{
			document.getElementById ("FormSubmit").disabled = true;
			document.getElementById ("FormSubmit").className = "input-submit-disabled";
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
		if (this.HasContact == true)
		{
			// we have a contact... play nice
			
			// 1 item from level 1-2
			if (arrScore[1] + arrScore[2] > 0)
			{
				return true;
			}
			
			// 2 items from level 3-5
			if (arrScore[3] + arrScore[4] + arrScore[5] > 1)
			{
				return true;
			}
		}
		else
		{
			// no contact, be harsh... be very harsh
			
			// 1 item from level 1
			// 1 item from level 2
			// 1 item from level 3-4
			if (arrScore[1] > 0 && arrScore[2] > 0 && arrScore[3] + arrScore[4] > 0)
			{
				return true;
			}
			
			// 1 item from level 1-2
			// 2 item from level 3-4
			if (arrScore[1] + arrScore[1] > 0 && arrScore[3] + arrScore[4] > 1)
			{
				return true;
			}
			
			// 1 item from level 3
			// 3 item from level 3-4 (4 total)
			if (arrScore[3] > 0 && arrScore[3] + arrScore[4] > 3)
			{
				return true;
			}
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
