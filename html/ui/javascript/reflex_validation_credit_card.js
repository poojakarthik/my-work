
var Reflex_Validation_Credit_Card = 
{
	// Public
	
	validateCardNumber : function(mValue, iCardType)
	{
		var oCardType			= Reflex_Validation_Credit_Card._getCardTypeForId(iCardType);
		var bCardNumberIsValid	= true;
		var sValidationReason	= '';
		if (Object.isUndefined(oCardType))
		{
			bCardNumberIsValid	= false;
			sValidationReason	= 'The Card Number is invalid without a Card Type.';
		}
		
		if (bCardNumberIsValid)
		{
			// Check that the card number is a valid card number. If not, highlight as invalid.
			var sRubbish 			= mValue.replace(/[0-9 ]+/g, '');
			var sCardNumber 		= mValue.replace(/[^0-9]+/g, '');
			var bCardNumberIsValid	= (sRubbish == '') && (sCardNumber.length >= CreditCardType.minCardNumberLength && sCardNumber.length <= CreditCardType.maxCardNumberLength);
			if (bCardNumberIsValid)
			{
				oValidateType = Reflex_Validation_Credit_Card._getCardTypeForNumber(sCardNumber);
				if (!oValidateType)
				{
					bCardNumberIsValid = false;
					sValidationReason	= 'The Card Number entered does not match any of the accepted credit card types.';
				}
				else
				{
					if (oCardType && oValidateType['id'] != oCardType['id'])
					{
						bCardNumberIsValid	= false;
						sValidationReason	= 'The selected Credit Card Type does not match the Card Number entered.';
					}
					else
					{
						bCardNumberIsValid 	= Reflex_Validation_Credit_Card._validateCardNumber(sCardNumber, oValidateType['id']);
						if (!bCardNumberIsValid)
						{
							sValidationReason 	= 'The Card Number entered is invalid.';
						}
					}
				}
			}
			else
			{
				oValidateType	= (sRubbish == '') && Reflex_Validation_Credit_Card._getCardTypeForNumber(sCardNumber);
				if (oValidateType)
				{
					bCardTypeEntered	= bCardTypeIsValid = true;
					var sLens			= '';
					for (var i = 0, l = oValidateType['valid_lengths'].length; i < l; i++)
					{
						sLens	+= ((i == 0) ? '' : (i == (l-1) ? ' or ' : ', ')) + oValidateType['valid_lengths'][i];
					}
					sValidationReason	= 'Card numbers for the selected Credit Card Type are ' + sLens + ' digits long.';
				}
				else if (sRubbish != '')
				{
					sValidationReason	= 'The Card Number can only contain numbers and spaces.';
				}
				else if (sCardNumber.length >= CreditCardType.minCardNumberLength)
				{
					sValidationReason	= 'The Card Number entered does not match any of the accepted credit card types.';
				}
				else
				{
					sValidationReason	= 'The Card Number entered is not long enough.';
				}
			}
		}
		
		if (bCardNumberIsValid)
		{
			return true;
		}
		else
		{
			throw sValidationReason;
		}
	},
	
	validateCVV : function(mValue, iCardType)
	{
		var oCardType 			= Reflex_Validation_Credit_Card._getCardTypeForId(iCardType);
		var sValidationReason 	= '';
		var sCleansed 			= mValue.replace(/[^0-9]+/g, '');
		var bCVVIsValid 		= mValue.match(/^ *[0-9]+[ 0-9]*$/) && !isNaN(parseInt(sCleansed));
		if (bCVVIsValid)
		{
			if (oCardType) 
			{
				bCVVIsValid 		= sCleansed.length == oCardType['cvv_length'];
				sValidationReason 	= 'The CVV for the selected Credit Card Type must be ' + oCardType['cvv_length'] + ' digits long.';
			}
			else 
			{
				bCVVIsValid 		= 	sCleansed.length >= CreditCardType.minCvvLength && sCleansed.length <= CreditCardType.maxCvvLength;
				var iDiff 			= 	CreditCardType.maxCvvLength - CreditCardType.minCvvLength;
				sValidationReason 	= 	(iDiff == 0) ? 'Valid CVVs are between ' + CreditCardType.minCvvLength + ' digits long.'
									 		: ((iDiff == 1) ? 'Valid CVVs are ' + CreditCardType.minCvvLength + ' or ' + CreditCardType.maxCvvLength + ' digits long.'
											  	: 'Valid CVVs are between ' + CreditCardType.minCvvLength + ' and ' + CreditCardType.maxCvvLength + ' digits long.');
			}
		}
		else
		{
			if (oCardType) 
			{
				sValidationReason	= 'The CVV enetered is invalid. It must must be a ' + oCardType['cvv_length'] + ' digit number for the selected Credit Card Type.';
			}
			else
			{
				var iDiff 			= 	CreditCardType.maxCvvLength - CreditCardType.minCvvLength;
				sValidationReason	= 	(iDiff == 0) ? 'The CVV enetered is invalid. It must must be a ' + CreditCardType.minCvvLength + ' digit number.'
											: ((iDiff == 1) ? 'The CVV enetered is invalid. It must must be a ' + CreditCardType.minCvvLength + ' or ' + CreditCardType.maxCvvLength + ' digit number.'
												: 'The CVV enetered is invalid. It must must be a number between ' + CreditCardType.minCvvLength + ' and ' + CreditCardType.maxCvvLength + ' digits long.');
			}
		}
		
		if (bCVVIsValid)
		{
			return true;
		}
		else
		{
			throw sValidationReason;
		}
	},
	
	// Protected
	
	_validateCardNumber	: function(mNumber)
	{
		// Strip the string of all non-digits
		var sNumber = mNumber.replace(/[^0-9]+/g, '');

		// Get the CC type for the number (this matches on prefixe)
		var aCcType = CreditCardType.cardTypeForNumber(sNumber);
		if (!aCcType)
		{
			return false;
		}

		// Check the Length is ok for the type
		bLengthFound = false;
		for (var i = 0, l = aCcType['valid_lengths'].length; i < l; i++)
		{
			if (sNumber.length == aCcType['valid_lengths'][i])
			{
				bLengthFound = true;
				break;
			}
		}
		if (!bLengthFound)
		{
			return false;
		}

		// Check the LUHN of the Credit Card
		return Reflex_Validation_Credit_Card._checkLuhn(sNumber);
	},
	
	_getCardTypeForNumber: function(mNumber)
	{
		if (mNumber.length < CreditCardType.minPrefixLength)
		{
			return false;
		}
		return CreditCardType.cardTypeForNumber(mNumber);
	},
	
	_getCardTypeForId : function(mId)
	{
		return CreditCardType.cardTypeForId(mId);
	},
	
	_checkLuhn	: function(sNumber)
	{
		var iDigits	= sNumber.length;
		var sDigits	= ("00" + sNumber).split("").reverse();
		var iTotal	= 0;
		for (var i = 0; i < iDigits; i += 2)
		{
			var d1	= parseInt(sDigits[i]);
			var d2	= 2 * parseInt(sDigits[i + 1]);
			d2 = d2 > 9 ? (d2 - 9) : d2;
			iTotal	+= d1 + d2;
			iTotal	-= iTotal >= 20 ? 20 :(iTotal >= 10 ? 10 : 0);
		}
		
		// Check that the total is 0
		return iTotal == 0;
	}
};
