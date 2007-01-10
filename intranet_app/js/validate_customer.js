// TRUE & FALSE
var TRUE = true;
var FALSE = false;

// class
function ValidateCustomerClass
{
	// internal input array
	this._objInput = {};
	
	// validate an input
	// returns TRUE if input is valid
	// returns FALSE if input is invalid
	this.ValidateInput = function(objObject)
	{
		objObject.ValidValue
		// set class
		//TODO
		
		// add to input object
		this._objInput[objObject.id] =
		{
			'Level':objObject.ValidLevel,
			'Valid':1
		}
	}
	
	// check if we have enough valid information to allow access
	// returns TRUE or FALSE, Pablo says 'work out for yourself what that's about'
	this.IsValidated = function()
	{
		var objInput;
		var bolReturn = FALSE;
		var objScore = {};
		
		// for each input
		for (objInput in this._objInput)
		{
				
		}
	}

}

// instanciate the object
ValidateCustomer = new ValidateCustomerClass;
