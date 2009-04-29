// Validation Class
Validate	= Class.create( 
{
	// fnn(): Checks whether an FNN is valid
	fnn		: function(strFNN, strServiceType)
	{
		var	expFNN	= /^((0[123478]\d{8}[i]?)|(13\d{4})|(1[389]00\d{6}))$/;
		
		// Is it a special type of fnn
		strServiceType.toLowerCase();
		strServiceType.replace(' ', '');
		switch (strServiceType)
		{
			case 'adsl':
			case 'landline':
				expFNN	= /^(0[12378]\d{8}[i]?)$/;
				break;
			
			case 'mobile':
				expFNN	= /^04\d{8}$/;
				break;
			
			case 'inbound':
				expFNN	= /^((13\d{4})|(1[389]00\d{6}))$/;
				break;
		}

		strFNN	= String(strFNN).replace(/\s/g, '');
		return expFNN.test(strFNN);
	},
	
	fnnLandLine	: function(strFNN)
	{
		return window._validate.fnn(strFNN, 'landline');
	},
	
	fnnADSL		: function(strFNN)
	{
		return window._validate.fnn(strFNN, 'adsl');
	},
	
	fnnMobile	: function(strFNN)
	{
		return window._validate.fnn(strFNN, 'mobile');
	},
	
	fnnInbound	: function(strFNN)
	{
		return window._validate.fnn(strFNN, 'adsl');
	},

	// email(): Checks whether an Email is valid
	email	: function(strEmail)
	{
		var expEmail	= /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)/i;
		//var expEmail	= /^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i;
		return expEmail.test(strEmail);
	},

	// date(): Checks whether a Date string is valid
	date	: function(strDate)
	{
		//alert("Validating Date '" + strDate + "'");
		strDate.strip();
		
		// First, validate using a regex
		var expDate	= /^(19|20)\d\d\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/;
		if (expDate.test(strDate))
		{
			// Correct number of days in the month?
			return window._validate.dayInMonthYear(parseInt("1"+strDate.substr(8, 2))-100, parseInt("1"+strDate.substr(5, 2))-100, strDate.substr(0, 4));
		}
		
		// Invalid Date
		return false;
	},

	// dayInMonthYear(): Checks whether a Day in a given Month/Year combination is valid
	dayInMonthYear	: function(mixDay, mixMonth, mixYear)
	{
		if (this._arrDaysInMonth == undefined)
		{
			this._arrDaysInMonth		= new Array();
			this._arrDaysInMonth[1]		= 31;
			this._arrDaysInMonth[2]		= 28;
			this._arrDaysInMonth[3]		= 31;
			this._arrDaysInMonth[4]		= 30;
			this._arrDaysInMonth[5]		= 31;
			this._arrDaysInMonth[6]		= 30;
			this._arrDaysInMonth[7]		= 31;
			this._arrDaysInMonth[8]		= 31;
			this._arrDaysInMonth[9]		= 30;
			this._arrDaysInMonth[10]	= 31;
			this._arrDaysInMonth[11]	= 30;
			this._arrDaysInMonth[12]	= 31;
		}
		
		intDay		= parseInt(mixDay);
		intMonth	= parseInt(mixMonth);
		intYear		= parseInt(mixYear);
		if (intDay > 0 && intDay <= this._arrDaysInMonth[intMonth])
		{
			// Valid Date in a normal year
			return true;
		}
		else if (intMonth == 2 && (intYear % 4) === 0 && intDay === 29)
		{
			// Valid Date for February in a Leap Year
			return true;
		}
		
		// Invalid Date
		return false;
	},
	
	// creditCardExpiry()
	creditCardExpiry	: function(strYearMonth)
	{
		intMonth	= parseInt(strYearMonth.substr(0, 2));
		intYear		= parseInt(strYearMonth.substr(String(intMonth).length+1, 4));
		dateNow		= new Date();
		if (intYear > dateNow.getFullYear() || (intYear == dateNow.getFullYear() && intMonth >= (dateNow.getMonth()+1)))
		{
			return true;
		}
		return false;
	},

	// australianBusinessNumber(): Checks whether an ABN is valid
	australianBusinessNumber	: function(mixABN)
	{
		// SEE: http://www.ato.gov.au/businesses/content.asp?doc=/content/13187.htm&pc=001/003/021/002/001&mnu=610&mfp=001/003&st=&cy=1
		// Define Digit Weights
		var arrWeights	= new Array(11);
		arrWeights[0]	= 10;
		arrWeights[1]	= 1;
		arrWeights[2]	= 3;
		arrWeights[3]	= 5;
		arrWeights[4]	= 7;
		arrWeights[5]	= 9;
		arrWeights[6]	= 11;
		arrWeights[7]	= 13;
		arrWeights[8]	= 15;
		arrWeights[9]	= 17;
		arrWeights[10]	= 19;
		
		// Ensure that the ABN is of decent length
		var strABN	= String(mixABN).replace(/\s/g, '');
		if (strABN.length != 11)
		{
			return false;
		}
		
		// Perform the calculation
		var arrDigits	= strABN.toArray();

		// 1. Subtract 1 from the first (left) digit to give a new eleven digit number
		arrDigits[0]	= parseInt(arrDigits[0]) - 1;
		
		// 2. Multiply each of the digits in this new number by its weighting factor
		// 3. Sum the resulting 11 products
		var intSum	= 0;
		for (i = 0; i < 11; i++)
		{
			arrDigits[i]	= parseInt(arrDigits[i]) * arrWeights[i];
			intSum			+= arrDigits[i];
		}
		
		// 4. Divide the total by 89, noting the remainder
		var intQuotient		= intSum / 89;
		var intRemainder	= intSum % 89;
		
		// 5. If the remainder is zero the number is valid
		return (intRemainder === 0) ? true : false;
	},

	// australianCompanyNumber(): Checks whether an ACN is valid
	australianCompanyNumber	: function(mixACN)
	{
		// SEE: http://www.asic.gov.au/asic/asic.nsf/byheadline/Australian+Company+Number+(ACN)+Check+Digit?opendocument
		// Define Digit Weights
		var arrWeights	= new Array(8);
		arrWeights[0]	= 8;
		arrWeights[1]	= 7;
		arrWeights[2]	= 6;
		arrWeights[3]	= 5;
		arrWeights[4]	= 4;
		arrWeights[5]	= 3;
		arrWeights[6]	= 2;
		arrWeights[7]	= 1;
		
		// Ensure that the ACN is of decent length
		var strACN	= String(mixACN).replace(/\s/g, '');
		if (strACN.length != 9)
		{
			return false;
		}
		
		// Perform the calculation
		var arrDigits	= strACN.toArray();
		
		// 1. Apply weighting to digits 1 to 8
		// 2. Sum the Products
		var intSum	= 0;
		for (i = 0; i < 8; i++)
		{
			arrDigits[i]	= parseInt(arrDigits[i]) * arrWeights[i];
			intSum			+= arrDigits[i];
		}
		
		// 3. Divide by 10 to obtain remainder (aka intSum mod 10)
		var intQuotient		= intSum / 10;
		var intRemainder	= intSum % 10;
		
		// 4. Subtract the remainder from 10
		var intDifference	= 10 - intRemainder;
		
		// 5. If the difference equals the check digit (digit 9), then it is valid
		return (intDifference == parseInt(arrDigits[8])) ? true : false;
	},
	
	// postcode()
	postcode	: function(strPostcode)
	{
		var	expPostcode	= /^\d{4}$/;
		return expPostcode.test(strPostcode);
	},
	
	// bsb()
	bsb	: function(strBSB)
	{
		strBSB		= String(strBSB).replace(/\s/g, '');
		var	expBSB	= /^\d{3}[-]?\d{3}$/;
		return expBSB.test(strBSB);
	},
	
	// string() - primarly used to check that a string does not exceed the maximum length
	// An empty string is a valid string
	string : function(strString, intMaxLength)
	{
		if (intMaxLength == undefined)
		{
			return true;
		}
		strString = String(strString);
		return (strString.length <= intMaxLength);
	},
	
	// Creates a function that can just be passed a string, and it will run the string validation with the appropriate length
	getStringLengthValidationFunc : function(intLength)
	{
		var strLength = (intLength == undefined)? "null" : String(intLength);

		var fncStringValidation;
		eval("fncStringValidation = function(strValue){return window._validate.string(strValue, "+ strLength +");};");
		return fncStringValidation;
	},
	
	// creditCardNumber()
	creditCardNumber	: function(strNumber, intCreditCardType)
	{
		//alert("Card Number: "+strNumber+"; Card Type: "+intCreditCardType);
		
		// Define Regexes
		if (this._arrCreditCardCVVLengths == undefined)
		{
			this._arrCreditCardNumberRegex		= new Array();
			this._arrCreditCardNumberRegex[1]	= /^[4](\d{12}|\d{15})$/;
			this._arrCreditCardNumberRegex[2]	= /^[5][1-5]\d{14}$/;
			this._arrCreditCardNumberRegex[4]	= /^[3][47]\d{13}$/;
			this._arrCreditCardNumberRegex[5]	= /^[3][068]\d{12}$/;
		}
		
		strNumber	= String(strNumber).replace(/[\s\-]/g, '');
		if (intCreditCardType != undefined && this._arrCreditCardNumberRegex[intCreditCardType] != undefined && this._arrCreditCardNumberRegex[intCreditCardType].test(strNumber))
		{
			// Looks kinda valid, check with the Luhn Algorithm
			// Go through each digit in reverse order and Double every second number
			arrDigits	= strNumber.toArray().reverse();
			for (i = 1; i < arrDigits.length; i+=2)
			{
				strDouble	= String(parseInt(arrDigits[i]) * 2);
				//alert(arrDigits.inspect() + "\nIndex: "+i+"\nDigit: "+arrDigits[i]+"\nDoubled: "+strDouble);
				arrDigits[i]	= strDouble;
			}
			
			// Add up each single digit (eg. 2,2,10,4 is 2+2+1+0+4)
			intSum	= 0;
			for (i = 0; i < arrDigits.length; i++)
			{
				strSubDigits	= String(arrDigits[i]);
				for (t = 0; t < strSubDigits.length; t++)
				{
					intSum	+= parseInt(strSubDigits.charAt(t));
					//alert(arrDigits.inspect() + "\nIndex: "+i+"\nDigit: "+arrDigits[i]+"\nSubDigit: "+strSubDigits.charAt(t));
				}
			}
			
			// If the Sum is a muliple of 10, then it's valid
			return (intSum % 10 === 0);
		}
	},
	
	// creditCardCVV()
	creditCardCVV	: function(strCVV, intCreditCardType)
	{
		//alert("CVV: "+strCVV+"; Card Type: "+intCreditCardType);
		
		// Define Valid Lengths
		if (this._arrCreditCardCVVRegex == undefined)
		{
			this._arrCreditCardCVVRegex		= new Array();
			this._arrCreditCardCVVRegex[1]	= /^\d{3}$/;
			this._arrCreditCardCVVRegex[2]	= /^\d{3}$/;
			this._arrCreditCardCVVRegex[4]	= /^\d{4}$/;
			this._arrCreditCardCVVRegex[5]	= /^\d{3}$/;
		}
		
		strCVV	= String(strCVV).replace(/\s/g, '');
		expCVV	= /^\d+$/;
		return (intCreditCardType != undefined && this._arrCreditCardCVVRegex[intCreditCardType] != undefined && this._arrCreditCardCVVRegex[intCreditCardType].test(strCVV));
	},
	
	// integer()
	integer			: function(mixValue)
	{
		expInteger	= /^\s*[\-]?(\d+)\s*$/;
		return expInteger.test(mixValue);
	},
	
	// integerPositive()
	integerPositive	: function(mixValue)
	{
		expPositiveInteger	= /^\s*(\d+)\s*$/;
		return expPositiveInteger.test(mixValue);
	}
});

if (window._validate == undefined)
{
	window._validate	= new Validate();
}