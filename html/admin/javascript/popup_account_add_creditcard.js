
var Popup_Account_Add_CreditCard	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, fnOnSave, fnOnCancel)
	{
		$super(35);
		
		this.iAccountId				= iAccountId;
		this.fnOnSave				= fnOnSave;
		this.fnOnCancel				= fnOnCancel;
		this.hInputs				= {};
		this.aCreditCardNumberRegex	= {};
		this.aCreditCardCVVRegex	= {};
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make AJAX request for the credit card types
			this._getCardTypes = jQuery.json.jsonFunction(this._buildUI.bind(this), this._ajaxError.bind(this), 'Account', 'getCreditCardTypes');
			this._getCardTypes();
			return;
		}
		else if (oResponse.Success)
		{
			// Got the card types, Make the interface
			var oContent	=	$T.div({class: 'popup-add-credit-card'},
									$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar', id: 'caption_bar'},
													$T.div({class: 'caption_title', id: 'caption_title'},
														'Details'
													)
												)
											),
											$T.tbody(
												$T.tr(
													$T.th({class: 'label'},
														'Card Type :'
													),
													$T.td(
														$T.select({class: 'popup-add-credit-card-type', bRequired: true, sValidationFunction: 'nonEmptyDigits'}
															// ...
														)
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Card Holder Name :'
													),
													$T.td(
														$T.input({type: 'text', bRequired: true, sValidationFunction: 'nonEmptyString'})
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Credit Card Number :'
													),
													$T.td(
														$T.input({type: 'text', bRequired: true, sValidationFunction: 'nonEmptyDigits'})
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Expiration Date :'
													),
													$T.td(
														$T.select({class: 'popup-add-credit-card-expiry-month', sFieldNameExtra: '-MM', bRequired: true, sValidationFunction: 'nonEmptyDigits'}
															// ...
														),
														' / ',
														$T.select({class: 'popup-add-credit-card-expiry-year', sFieldNameExtra: '-YYYY', bRequired: true, sValidationFunction: 'nonEmptyDigits'}
															// ...
														)
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'CVV # :'
													),
													$T.td(
														$T.input({type: 'text', bRequired: true, sValidationFunction: 'nonEmptyDigits'})
													)
												)
											)
										),
									$T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Add_CreditCard.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
											$T.span('Save')
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Add_CreditCard.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										)
									)
								);
			
			// Set save & cancel event handlers
			var oSaveButton		= oContent.select('button.icon-button').first();
			oSaveButton.observe('click', this._save.bind(this));
			
			var oCancelButton	= oContent.select('button.icon-button').last();
			oCancelButton.observe('click', this._cancel.bind(this));	
			
			// Populate card type select & build card number & cvv regexes
			var oCardTypeSelect		= oContent.select('select.popup-add-credit-card-type').first();
			var aCreditCardTypes	= jQuery.json.arrayAsObject(oResponse.aTypes);
			var oCardType			= null;
			
			for (var iId in aCreditCardTypes)
			{
				oCardType	= aCreditCardTypes[iId];
				
				// Add card type to the select
				oCardTypeSelect.appendChild(
					$T.option({value: iId},
						oCardType.name
					)
				);
				
				// Add card number regex
				var iPrefixLength	= oCardType.valid_prefixes[0].length;
				var sPrefixRegex	= '(' + oCardType.valid_prefixes.join('|') + ')';
				var sLengths		= oCardType.valid_lengths.join().replace(
												/(\d+)/g, 
												function($1)
												{
													return '\\d{' + (parseInt($1) - iPrefixLength) + '}';
												}
											);
				var aLengths		= sLengths.split(',');
				var sLengthRegex	= '(' + aLengths.join('|') + ')';
				
				this.aCreditCardNumberRegex[iId]	= new RegExp('^' + sPrefixRegex + sLengthRegex + '$');
				
				// Add cvv regex
				this.aCreditCardCVVRegex[iId]	= new RegExp('^\\d{' + oCardType.cvv_length + '}$')
			}
			
			// Populate expiry date selects
			var oExpiryMonthSelect	= oContent.select('select.popup-add-credit-card-expiry-month').first();
			Popup_Account_Add_CreditCard._populateNumberSelect(oExpiryMonthSelect, 1, 12);
			
			var oExpiryYearSelect	= oContent.select('select.popup-add-credit-card-expiry-year').first();
			var iThisYear			= new Date().getFullYear();
			Popup_Account_Add_CreditCard._populateNumberSelect(oExpiryYearSelect, iThisYear, iThisYear + 10);
			
			// Setup input validate event handlers (selects first, then inputs)
			var aInputs		= oContent.select('select, input');
			var oInput		= null;
			
			for (var i = 0; i < aInputs.length; i++)
			{
				oInput				= aInputs[i];
				oInput.bRequired	= (oInput.getAttribute('bRequired') == 'true' ? true : false);
				
				if (oInput.getAttribute('sValidationFunction'))
				{
					oInput.validate	=	Popup_Account_Add_CreditCard._validateInput.bind(
											oInput, 		
											Reflex_Validation.Exception[oInput.getAttribute('sValidationFunction')]
										);
				}
				
				oInput.sFieldName	= 	oInput.parentNode.parentNode.select('th').first().innerHTML.replace(/^(.*)\s:$/, '$1')
										+ (oInput.getAttribute('sFieldNameExtra') ? oInput.getAttribute('sFieldNameExtra') : '');
				this.hInputs[oInput.sFieldName]	= oInput;
			}
			
			for (var sName in this.hInputs)
			{
				if (typeof this.hInputs[sName].validate !== 'undefined')
				{
					this.hInputs[sName].observe('keyup', this.hInputs[sName].validate);
					this.hInputs[sName].observe('change', this.hInputs[sName].validate);
				}
			}
			
			// Display Popup
			this.setTitle("Add Credit Card Details");
			this.addCloseButton();
			this.setIcon("../admin/img/template/payment.png");
			this.setContent(oContent);
			this.display();
			
			this.oContent	= oContent;
			this._isValid();
		}
		else
		{
			// AJAX Error
			this._ajaxError(oResponse, true);
		}
	},
	
	_isValid	: function()
	{
		// Build an array of error messages, after running all validation functions
		var aErrors	= [];
		var mError 	= null;
		var oInput 	= null;
		
		for (var sName in this.hInputs)
		{
			oInput = this.hInputs[sName];
			
			if (typeof oInput.validate !== 'undefined')
			{
				mError = oInput.validate();
				
				if (mError != null)
				{
					aErrors.push(mError);
				}
			}
		}
		
		// Check credit card number
		var iCardType	= this.hInputs['Card Type'].value;
		
		if (!this._validateCreditCardNumber(this.hInputs['Credit Card Number'].value, iCardType))
		{
			aErrors.push('Invalid Credit Card Number');
		}
		
		// Check cvv
		if (!this._validateCreditCardCVV(this.hInputs['CVV #'].value, iCardType))
		{
			aErrors.push('Incorrect CVV # Length');
		}		
		
		return aErrors;
	},
	
	_save	: function()
	{
		var aErrors = this._isValid();
		
		if (aErrors.length)
		{
			Popup_Account_Add_CreditCard._showValidationErrorPopup(aErrors);
			return;
		}
		
		// Build request data
		var oDetails	=	{
								iCardType		: parseInt(this.hInputs['Card Type'].value),
								sCardHolderName	: this.hInputs['Card Holder Name'].value,
								iCardNumber		: this.hInputs['Credit Card Number'].value,
								iExpiryMonth	: parseInt(this.hInputs['Expiration Date-MM'].value),
								iExpiryYear		: parseInt(this.hInputs['Expiration Date-YYYY'].value),
								iCVV			: this.hInputs['CVV #'].value
							};

		// Create a Popup to show 'saving...' close it when save complete
		this.oLoading = new Reflex_Popup.Loading('Saving...');
		this.oLoading.display();
		
		this._addCreditCard	= jQuery.json.jsonFunction(this._saveResponse.bind(this), this._ajaxError.bind(this), 'Account', 'addCreditCard');
		this._addCreditCard(this.iAccountId, oDetails);
	},
	
	_saveResponse	: function(oResponse)
	{
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success)
		{
			this.hide();
			
			if (this.fnOnSave)
			{
				this.fnOnSave(oResponse.oCreditCard);
			}
		}
		else if (oResponse.aValidationErrors)
		{
			// Validation errors
			Popup_Account_Add_CreditCard._showValidationErrorPopup(oResponse.aValidationErrors);
		}
		else
		{
			this._ajaxError(oResponse);
		}
	},
	
	_ajaxError	: function(oResponse, bHideOnClose)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.Success == false)
		{
			var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
			
			if (oResponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message, oConfig);
			}
			else if (oResponse.ERROR)
			{
				Reflex_Popup.alert(oResponse.ERROR, oConfig);
			}
		}
	},
	
	_validateCreditCardNumber	: function(sNumber, iCreditCardType)
	{
		sNumber	= String(sNumber).replace(/[\s\-]/g, '');
		
		if (iCreditCardType != undefined && this.aCreditCardNumberRegex[iCreditCardType] != undefined && this.aCreditCardNumberRegex[iCreditCardType].test(sNumber))
		{
			// Looks kinda valid, check with the Luhn Algorithm
			// Go through each digit in reverse order and Double every second number
			aDigits	= sNumber.toArray().reverse();
			for (i = 1; i < aDigits.length; i+=2)
			{
				sDouble		= String(parseInt(aDigits[i]) * 2);
				aDigits[i]	= sDouble;
			}
			
			// Add up each single digit (eg. 2,2,10,4 is 2+2+1+0+4)
			iSum	= 0;
			
			for (i = 0; i < aDigits.length; i++)
			{
				sSubDigits	= String(aDigits[i]);
				for (t = 0; t < sSubDigits.length; t++)
				{
					iSum	+= parseInt(sSubDigits.charAt(t));
				}
			}
			
			// If the Sum is a muliple of 10, then it's valid
			return (iSum % 10 === 0);
		}
		
		return false;
	},
	
	_validateCreditCardCVV	: function(sCVV, iCreditCardType)
	{
		sCVV	= String(sCVV).replace(/\s/g, '');
		return (iCreditCardType != undefined && this.aCreditCardCVVRegex[iCreditCardType] != undefined && this.aCreditCardCVVRegex[iCreditCardType].test(sCVV));
	},
	
	_cancel	: function()
	{
		if (typeof this.fnOnCancel !== 'undefined')
		{
			this.fnOnCancel();
		}
		
		this.hide();
	}
});

// Image paths
Popup_Account_Add_CreditCard.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Add_CreditCard.SAVE_IMAGE_SOURCE 		= '../admin/img/template/tick.png';

//Credit Card types
Popup_Account_Add_CreditCard.CREDIT_CARD_TYPE_VISA			= 1;
Popup_Account_Add_CreditCard.CREDIT_CARD_TYPE_MASTERCARD	= 2;
Popup_Account_Add_CreditCard.CREDIT_CARD_TYPE_BANKCARD		= 3;
Popup_Account_Add_CreditCard.CREDIT_CARD_TYPE_AMEX			= 4;
Popup_Account_Add_CreditCard.CREDIT_CARD_TYPE_DINERS		= 5;

Popup_Account_Add_CreditCard._populateNumberSelect	= function(oSelect, iLowest, iHighest, sFirstItem)
{
	// Add optional first item
	if (sFirstItem)
	{
		oSelect.appendChild(
			$T.option({value: ''},
				sFirstItem
			)
		);
	}
	
	// Add numbers within bounds
	for (var iValue = iLowest; iValue <= iHighest; iValue++)
	{
		oSelect.appendChild(
			$T.option({value: iValue},
				(iValue < 10 ? '0' + iValue : iValue)
			)
		);
	}
};

Popup_Account_Add_CreditCard._showValidationErrorPopup	= function(aErrors)
{
	// Build UL of error messages
	var oValidationErrors = $T.ul();
	
	for (var i = 0; i < aErrors.length; i++)
	{
		oValidationErrors.appendChild(
							$T.li(aErrors[i])
						);
	}
	
	// Show a popup containing the list
	Reflex_Popup.alert(
					$T.div({style: 'margin: 0.5em'},
						'The following errors have occured: ',
						oValidationErrors
					),
					{
						iWidth	: 30,
						sTitle	: 'Validation Errors'
					}
				);
};

Popup_Account_Add_CreditCard._validateInput	= function(fnValidate)
{
	// This is to be bound to the scope of an input
	try
	{
		this.removeClassName('valid');
		this.removeClassName('invalid');
		
		// Check required validation first
		if (this.value == '' || this.value === null)
		{
			if (this.bRequired)
			{
				throw('Required field');
			}
			
			return null;
		}
		else
		{
			if (fnValidate(this.value))
			{
				this.addClassName('valid');
			}
			
			return null;
		}
	}
	catch (e)
	{
		this.addClassName('invalid');
		return this.sFieldName + ': ' + e; 
	}
};
