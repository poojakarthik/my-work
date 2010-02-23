var Reflex_Validation	=
{
	email	: function(strTest)
	{
		return (strTest.search(/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i) != -1) ? true : false;
	},
	
	fnn		: function(strTest)
	{
		// Allows for FNNs in the following forms:
		//	* 0733534816	-- Fixed Line (Land Line)
		//	* 0406818784	-- Mobile MSN
		//	* 134585		-- Inbound 13
		//	* 1300154845	-- Inbound 1300
		//	* 1800648432	-- Inbound 1800
		return (Reflex_Validation.fnnFixedLine(strTest) || Reflex_Validation.fnnMobile(strTest) || Reflex_Validation.fnnInbound(strTest));
		//return (strTest.search(/^(13\d{4}|(0[123456789]\d{2}|1[38]00)\d{6})$/) != -1) ? true : false;
	},
	
	fnnFixedLine	: function(strTest)
	{
		return (strTest.search(/^0[12356789]\d{8}$/) != -1) ? true : false;
	},
	
	fnnMobile		: function(strTest)
	{
		return (strTest.search(/^04\d{8}$/) != -1) ? true : false;
	},
	
	fnnInbound		: function(strTest)
	{
		return (strTest.search(/^(13\d{4}|1[38]00\d{6})$/) != -1) ? true : false;
	},
	
	digits	: function(strTest)
	{
		return (strTest.search(/^\d*$/) != -1) ? true : false;
	},

	abn	: function(mixValue)
	{
		var strValue = mixValue.toString();
		var strValue = strValue.replace(/[^\d]/g, '');
		
		if (!/^[0-9]{11}$/.test(strValue))
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
	},
	
	// This method was ported from bash's original ACN.js
	acn : function(mixValue)
	{
		// Check that the item has only Numbers and Spaces
		if (mixValue.match (/[^\d\s]/g) !== null)
		{
			return false;
		}
		// Strip out everything except digits
		var intACN = mixValue.replace (/[^\d]/g, '');
		
		// Check there are 9 integers
		if (intACN.length !== 9)
		{
			return false;
		}
		
		// 1. Apply weighting to digits 1 to 8
		// 2. Sum the products
		// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
		// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
		// 5. Check the calculated check digit equals actual check digit
		
		var arrWeights = new Array (8);
		arrWeights [0] = 8;
		arrWeights [1] = 7;
		arrWeights [2] = 6;
		arrWeights [3] = 5;
		arrWeights [4] = 4;
		arrWeights [5] = 3;
		arrWeights [6] = 2;
		arrWeights [7] = 1;
		
		// 1. Apply weighting to digits 1 to 8
		// 2. Sum the products
		var NumberSum = 0;
		
		for (i=0; i < 8; ++i)
		{
			NumberSum += parseInt (intACN.charAt (i) * arrWeights [i]);
		}
		
		// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
		var Remainder = NumberSum % 10;
		
		// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
		var Complement = 10 - Remainder;
		
		if (Complement == 10)
		{
			Complement = 0;
		}
		
		// 5. Check the calculated check digit equals actual check digit
		if (intACN.charAt (8) != Complement)
		{
			return false;
		}
		
		return true;
	}
	
};