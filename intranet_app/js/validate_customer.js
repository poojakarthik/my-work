// TRUE & FALSE
var TRUE = true;
var FALSE = false;

// class
function ValidateCustomerClass
{
	// internal input array
	this._objInput = {};
	
	// do we have a contact
	this.HasContact = FALSE;
	
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
		var objScore = [];
		
		// for each input
		for (objInput in this._objInput)
		{
			// add to score object
			arrScore[Number(objInput.Level)] += Number(objInput.Valid);
		}
		
		// see if we have a high enough score to be valid
		if (this.HasContact == TRUE)
		{
			// we have a contact... play nice
			
			// 1 item from level 1
			if (objScore[1] + objScore[2] > 0)
			{
				return TRUE;
			}
			
			// 2 items from level 3-5
			if (objScore[3] + objScore[4] + objScore[5] > 1)
			{
				return TRUE;
			}
		}
		else
		{
			// no contact, be harsh... be very harsh
			
			// 1 item from level 1
			// 1 item from level 2
			// 1 item from level 3-4
			if (objScore[1] > 0 && objScore[2] > 0 && objScore[2] + objScore[4] > 0)
			{
				return TRUE;
			}
			
			// 1 item from level 1-2
			// 2 item from level 3-4
			if (objScore[1] + objScore[1] > 0 && objScore[3] + objScore[4] > 1)
			{
				return TRUE;
			}
			
			// 1 item from level 3
			// 3 item from level 3-4 (4 total)
			if (objScore[3] > 0 && objScore[3] + objScore[4] > 3)
			{
				return TRUE;
			}
		}
		
		// return false by default
		return FALSE;
	}

}

// instanciate the object
ValidateCustomer = new ValidateCustomerClass;
