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


// bolNotRequired defaults to null (signifying that it is required)
function $Validate(strRule, mixValue, bolNotRequired)
{
	return Vixen.Validation.Validate(strRule, mixValue, bolNotRequired);
}

// This doesn't work, if prototype.js is included before this file is
// This file should always be included before prototype.js
Element.prototype.Validate = function(strRule, bolNotRequired)
{
	var strValue = this.value.toString();
	return Vixen.Validation.Validate(strRule, strValue, bolNotRequired);
}

String.prototype.Validate = function(strRule, bolNotRequired)
{
	var strValue = this.toString();
	return Vixen.Validation.Validate(strRule, strValue, bolNotRequired);
}


// This Class encapsulates all the Validation Rules
function VixenValidationClass()
{
	this.regexShortDate					= /^(0[1-9]|[12][0-9]|3[01])[\/](0[1-9]|1[012])[\/](19|20)[0-9]{2}$/;
	this.regexABN						= /^\d{11}$/;
	this.regexPostCode					= /^\d{4}$/;
	this.regexPositiveInteger			= /^\d+$/;
	this.regexPositiveIntegerNonZero	= /^[1-9]\d*$/;
	this.regexLettersOnly				= /^[A-Za-z]+$/;
	this.regexTime24Hr					= /^(0[0-9]|[1][0-9]|2[0-3])(:(0[0-9]|[1-5][0-9])){2}$/;
	this.regexMonetaryValue				= /^\d+(\.(\d){0,2})?$/
	this.regexDateTime					= /^(0[0-9]|[1][0-9]|2[0-3])(:(0[0-9]|[1-5][0-9])){2} (0[1-9]|[12][0-9]|3[01])[\/](0[1-9]|1[012])[\/](19|20)[0-9]{2}$/;
	this.regexEmailAddress				= /^[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+(?:\.[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+)*@(?:[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?$/;
	this.regexPositiveFloat				= /^\d+(\.(\d)*)?$/;


	// Wrapper for the individual validation functions.  This is used
	// to manage bolRequired functionality.  If a value isn't required, and 
	// isn't specified, then the validation rule is not applied
	this.Validate = function(strRule, mixValue, bolNotRequired)
	{
		if (bolNotRequired == true)
		{
			// It is not required so only run the validation rule, if a value is specified
			if (mixValue == null || mixValue.toString() == "")
			{
				// The value has not been specified
				return true;
			}
			
			// The value has been specified.  Run the Validation rule
			return this[strRule](mixValue);
		}
		else
		{
			// It is required
			return this[strRule](mixValue);
		}
	}
	
	// Returns true if strDate is in the date format of dd/mm/yyyy
	this.ShortDate = function(mixValue)
	{
		return this.regexShortDate.test(mixValue);
	}
	
	this.DateTime = function(mixValue)
	{
		return this.regexDateTime.test(mixValue);
	}
	
	this.ShortDateInFuture = function(mixValue)
	{
		if (!this.ShortDate(mixValue))
		{
			return false;
		}
		
		// Convert the date into MM/DD/YYYY (american style) so it can be easily converted to a Date object
		var strDate		= mixValue.substr(3, 2) + "/" + mixValue.substr(0, 2) + "/" + mixValue.substr(6, 4);
		var objDate		= new Date(strDate);
		var objToday	= new Date();
		
		return (objDate > objToday);
	}
	
	
	this.PositiveInteger = function(mixValue)
	{
		return this.regexPositiveInteger.test(mixValue.toString());
	}
	
	this.PositiveIntegerNonZero = function(mixValue)
	{
		return this.regexPositiveIntegerNonZero.test(mixValue.toString());
	}
	
	this.LettersOnly = function(mixValue)
	{
		return this.regexLettersOnly.test(mixValue.toString());
	}
	
	this.ABN = function(mixValue)
	{
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
	
	this.NotEmptyString = function(strValue)
	{
		return (strValue.length > 0);
	}
	
	this.PostCode = function(strValue)
	{
		return this.regexPostCode.test(strValue);
	}

	this.Time24Hr = function(strValue)
	{
		return this.regexTime24Hr.test(strValue);
	}

	this.MonetaryValue = function(strValue)
	{
		return this.regexMonetaryValue.test(strValue);
	}
	
	this.EmailAddress = function(strValue)
	{
		return this.regexEmailAddress.test(strValue);
	}
	
	this.PositiveFloat = function(strValue)
	{
		return this.regexPositiveFloat.test(strValue);
	}

}


if (Vixen.Validation == undefined)
{
	Vixen.Validation = new VixenValidationClass;
}
