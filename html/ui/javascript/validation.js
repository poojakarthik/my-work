//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// validation.js
//----------------------------------------------------------------------------//
/**
 * validation
 *
 * All data validation functions are stored here
 *
 * All data validation functions are stored here
 * 
 *
 * @file		validation.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// If bolRequired is omitted or set to null, then it will assume that it is required
// If it is set to false, then the function will return true, if mixValue is logically undefined (such as an empty string, or null)
function $Validate(strRule, mixValue, bolRequired)
{
	return Vixen.Validation.Validate(strRule, mixValue, bolRequired);
}

Element.prototype.Validate = function(strRule, bolRequired)
{
	return Vixen.Validation.Validate(strRule, this.value, bolRequired);
}

String.prototype.Validate = function(strRule, bolRequired)
{
	return Vixen.Validation.Validate(strRule, this, bolRequired);
}


// This Class encapsulates all the Validation Rules
function VixenValidationClass()
{
	this.regexShortDate = /^(0[1-9]|[12][0-9]|3[01])[\/](0[1-9]|1[012])[\/](19|20)[0-9]{2}$/;
	this.regexABN = /^\d{11}$/;
	this.regexPostCode = /^[1-9]\d{3}$/;
	this.regexPositiveInteger = /^\d+$/;
	this.regexPositiveIntegerNonZero = /^[1-9]\d*$/;
	this.regexLettersOnly = /^[A-Za-z]+$/;

	// Wrapper for the individual validation functions.  This is used
	// to manage bolRequired functionality.  If a value isn't required, and 
	// isn't specified, then the validation rule is not applied
	this.Validate = function(strRule, mixValue, bolRequired)
	{
		if (bolRequired == null)
		{
			// if it is not specified then have it default to being required
			bolRequired = true;
		}
		
		if (bolRequired == true)
		{
			// It is required
			return this[strRule](mixValue);
		}
		else
		{
/* This doesn't work as I have added stuff to String.prototype, so now strings are not considered strings 
			// It is not required so only run the validation rule, if a value is specified
			switch (typeof(mixValue))
			{
				case "string":
					if (mixValue.length > 0)
					{
						// The value has been specified.  Validate it
						return this[strRule](mixValue);
					}
					// The value has not been specified, so it passes validation by default
					return true;
					break;
				default:
					if (mixValue != null)
					{
						// A value has been specified
						return this[strRule](mixValue);
					}
					return true;
					break;
			}
*/
			if (mixValue.toString() != "")
			{
				return this[strRule](mixValue);
			}
			return true;
			
		}
	}
	
	// Returns true if strDate is in the date format of dd/mm/yyyy
	// bolRequired defaults to true meaning the value is required
	this.ShortDate = function(mixValue)
	{
		if (mixValue == null)
		{
			return false;
		}
		return this.regexShortDate.test(mixValue);
	}
	
	this.PositiveInteger = function(mixValue)
	{
		/*
		var intValue = parseInt(mixValue);
		if (isNaN(intValue))
		{
			return false;
		}
		
		if (intValue < 0)
		{
			return false;
		}
		
		var strValue = intValue.toString();
		
		if (strValue != mixValue)
		{
			return false;
		}
		return true;
		*/
		if (mixValue == null)
		{
			return false;
		}
		
		return this.regexPositiveInteger.test(mixValue.toString());
	}
	
	this.PositiveIntegerNonZero = function(mixValue)
	{
		if (mixValue == null)
		{
			return false;
		}
	
		return this.regexPositiveIntegerNonZero.test(mixValue.toString());
	}
	
	this.LettersOnly = function(mixValue)
	{
		if (mixValue == null)
		{
			return false;
		}
	
		return this.regexLettersOnly.test(mixValue.toString());
	}
	
	this.ABN = function(mixValue)
	{
		if (mixValue == null)
		{
			return false;
		}
	
		var strValue = mixValue.toString();
		var strValue = strValue.replace(/[^\d]/g, '');
		
		if (!this.regexABN.test(strValue))
		{
			return false;
		}
		
		// We know it is 11 chars long and they are all digits
		
		// Official ABN validation Step 1:
		// Subtract 1 from the first (left most) digit to give a new eleven digit number
		var strABNStep1 = "" + (parseInt(strValue.charAt(0)) - 1) + strValue.substr(1);
		
		var arrWeight = new Array(11);
		arrWeight[0]	= 10;
		arrWeight[1]	= 1;
		arrWeight[2]	= 3;
		arrWeight[3]	= 5;
		arrWeight[4]	= 7;
		arrWeight[5]	= 9;
		arrWeight[6]	= 11;
		arrWeight[7]	= 13;
		arrWeight[8]	= 15;
		arrWeight[9]	= 17;
		arrWeight[10]	= 19;
		
		// Steps 2 and 3:
		// Multiply each of the digits in this new number, by its weighting factor and sum the resulting 11 products
		var intABNStep3 = 0;
		
		for (i=0; i < 11; i++)
		{
			intABNStep3 += parseInt(strABNStep1.charAt(i)) * arrWeight[i];
		}
		
		// Steps 4 and 5:
		// Divide the total by 89.  If the remainder is zero then the number is valid
		if (intABNStep3 % 89 != 0)
		{
			return false;
		}
		
		// The number is a valid ABN
		return true;
	}
	
	this.NotEmptyString = function(mixValue)
	{
		if (mixValue == null)
		{
			return false;
		}
	
		return (mixValue.length > 0);
	}
	
	this.PostCode = function(mixValue)
	{
		if (mixValue == null)
		{
			return false;
		}
	
		return this.regexPostCode.test(mixValue);
	}
	
}


if (Vixen.Validation == undefined)
{
	Vixen.Validation = new VixenValidationClass;
}
