
var Popup_Account_Add_CreditCard	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, fnOnSave, fnOnCancel, fnOnPaymentMethodChange)
	{
		$super(35);
		
		this._iAccountId				= iAccountId;
		this._fnOnSave					= fnOnSave;
		this._fnOnCancel				= fnOnCancel;
		this._fnOnPaymentMethodChange	= fnOnPaymentMethodChange;
		
		this._hFields					= {};
		this._hFieldValues				= {};
		this._sSelectedPaymentAmount	= null;
		
		this._buildUI();
	},
	
	_buildUI	: function(oAccountInfo, bConstantsLoaded)
	{
		if (Object.isUndefined(oAccountInfo))
		{
			// Get account info
			Popup_Account_Add_CreditCard._getAccountPaymentInfo(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		else if (!bConstantsLoaded)
		{
			// Load db consants
			Flex.Constant.loadConstantGroup(['payment_method', 'direct_debit_type', 'rebill_type'], this._buildUI.bind(this, oAccountInfo, true));
			return;
		}
		
		this._oAccountInfo	= oAccountInfo;
		
		var oCardSection	= new Section(true);
		oCardSection.setTitleText('Credit Card Details');
		oCardSection.setContent(
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th({class: 'label'},
							'Card Type :'
						),
						$T.td(
							this._getField(Popup_Account_Add_CreditCard.FIELD_CARD_TYPE).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label'},
							'Card Holder Name :'
						),
						$T.td(
							this._getField(Popup_Account_Add_CreditCard.FIELD_CARD_NAME).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label'},
							'Card Number :'
						),
						$T.td(
							this._getField(Popup_Account_Add_CreditCard.FIELD_CARD_NUMBER).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label'},
							'Expiry Date :'
						),
						$T.td(
							this._getField(Popup_Account_Add_CreditCard.FIELD_EXPIRY_DATE).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label popup-add-credit-card-cvvlabel'},
							'CVV # :'
						),
						$T.td(
							this._getField(Popup_Account_Add_CreditCard.FIELD_CVV).getElement()
						)
					)
				)
			)
		);
		
		this._oPaymentSection	=	new Section_Expandable(
										false, 
										'popup-add-direct-debit-paymentsection', 
										false, 
										false
									);
		this._oPaymentSection.addToHeaderOptions(this._getPaymentSectionHeader());
		this._oPaymentSection.setContent(
			$T.div(
				$T.ul({class: 'reset'},
					$T.li(
						this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE).getElement(),
						$T.span({class: 'popup-add-direct-debit-paymentlabel'},
							'Account Balance:'
						).observe('click', this._selectPaymentAmountRadio.bind(this, Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE)),
						$T.span({class: 'popup-add-direct-debit-paymentamountbalance'},
							$T.span({class: 'popup-add-direct-debit-currencysymbol'},
								'$'
							),
							this._oAccountInfo.fBalance.toFixed(2)
						)
					),
					$T.li(
						this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OVERDUE).getElement(),
						$T.span({class: 'popup-add-direct-debit-paymentlabel'},
							'Overdue Balance:'
						).observe('click', this._selectPaymentAmountRadio.bind(this, Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OVERDUE)),
						$T.span({class: 'popup-add-direct-debit-paymentamountoverdue'},
							$T.span({class: 'popup-add-direct-debit-currencysymbol'},
								'$'
							),
							this._oAccountInfo.fOverdueBalance.toFixed(2)
						)
					),
					$T.li(
						this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OTHER).getElement(),
						$T.span({class: 'popup-add-direct-debit-paymentlabel'},
							'Other:'
						).observe('click', this._selectPaymentAmountRadio.bind(this, Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OTHER)),
						$T.span({class: 'popup-add-direct-debit-currencysymbol'},
							'$'
						),
						this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT).getElement()
					)
				)
			)
		);
		
		// CVV Tooltip
		this._oCVVTooltip	=	$T.div({class: 'cvv-image-tooltip'},
									$T.img({src: '../ui/img/template/cvv_visa.gif'}),
									$T.img({src: '../ui/img/template/cvv_amex.gif'})
								);
		var oTooltipElement	= oCardSection.getElement().select('.popup-add-credit-card-cvvlabel').first();
		oTooltipElement.observe('mouseover', this._showCVVTooltip.bind(this, true, oTooltipElement));
		oTooltipElement.observe('mouseout', this._showCVVTooltip.bind(this, false));
		
		// Cache frequently referenced fields
		this._oCardType		= this._getField(Popup_Account_Add_CreditCard.FIELD_CARD_TYPE);
		this._oCardCVV		= this._getField(Popup_Account_Add_CreditCard.FIELD_CVV);
		this._oCardNumber	= this._getField(Popup_Account_Add_CreditCard.FIELD_CARD_NUMBER);
		this._oCardName		= this._getField(Popup_Account_Add_CreditCard.FIELD_CARD_NAME);
		this._oCardExpiry	= this._getField(Popup_Account_Add_CreditCard.FIELD_EXPIRY_DATE);
		
		// Card type
		this._oCardType.addOnChangeCallback(this._cardTypeChanged.bind(this));
		
		// CVV validation
		this._oCardCVV.setValidateFunction(this._validateCVV.bind(this));
		
		// Card Number validation
		this._oCardNumber.setValidateFunction(this._validateCardNumber.bind(this));
		
		// Extra work on amount field
		var oPaymentAmount	= this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT);
		oPaymentAmount.getElement().addClassName('popup-add-direct-debit-amounttopay');
		oPaymentAmount.setMandatory(this._isPaymentAmountMandatory.bind(this));
		
		// Change callback for submit checkbox
		this._getField(Popup_Account_Add_CreditCard.FIELD_SUBMIT_PAYMENT).addOnChangeCallback(this._submitPaymentChange.bind(this));
		
		// Payment radio buttons
		var fnPaymentTypeChange	= this._paymentAmountChange.bind(this);
		this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE).addOnChangeCallback(fnPaymentTypeChange.curry(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE));
		this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OVERDUE).addOnChangeCallback(fnPaymentTypeChange.curry(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OVERDUE));
		this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OTHER).addOnChangeCallback(fnPaymentTypeChange.curry(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OTHER));
		this._selectPaymentAmountRadio(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE);
		
		var oContent	=	$T.div({class: 'popup-add-direct-debit popup-add-credit-card'},
								oCardSection.getElement(),
								this._oPaymentSection.getElement(),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Account_Add_CreditCard.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									).observe('click', this._save.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Account_Add_CreditCard.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									).observe('click', this._cancel.bind(this))
								)
							);
		
		// Display Popup
		this.setTitle("Add Credit Card Details");
		this.addCloseButton();
		this.setIcon("../admin/img/template/payment.png");
		this.setContent(oContent);
		this.display();
		
		this.oContent	= oContent;
		this._validate();
	},
	
	_getPaymentSectionHeader	: function()
	{
		if (this._oAccountInfo.fBalance <= 0)
		{
			// Credit or 0 balance, no payment needed
			return	$T.div({class: 'popup-add-direct-debit-nopaymentcheckbox'},
						$T.span(
							$T.span('This Account has a balance of '),
							$T.span({class: 'popup-add-direct-debit-highlight'},
								'$' + Math.abs(this._oAccountInfo.fBalance.toFixed(2)) + ' CR'
							),
							$T.span(', no payment is required.')
						)
					);
		}
		else
		{
			// Balance present, allow payment
			return	$T.div({class: 'popup-add-direct-debit-submitpaymentcheckbox'},
						this._getField(Popup_Account_Add_CreditCard.FIELD_SUBMIT_PAYMENT).getElement(),
						$T.span(
							$T.span('This Account has a balance of '),
							$T.span({class: 'popup-add-direct-debit-highlight'},
								'$' + this._oAccountInfo.fBalance.toFixed(2)
							),
							$T.span(', make an initial Payment.')
						).observe('click', this._selectPaymentCheckbox.bind(this))
					);
		}
	},
	
	_submitPaymentChange	: function()
	{
		var oSubmitPaymentCheckbox	= this._getField(Popup_Account_Add_CreditCard.FIELD_SUBMIT_PAYMENT);
		var oPaymentAmount		 	= this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT);
		if (oSubmitPaymentCheckbox.getElementValue())
		{
			this._oPaymentSection.setExpanded(true);
			oPaymentAmount.setValue(this._oAccountInfo.fBalance);
		}
		else
		{
			this._oPaymentSection.setExpanded(false);
			oPaymentAmount.clearValue();
		}
	},
	
	_isPaymentAmountMandatory	: function()
	{
		return (this._oPaymentAmountTR && this._oPaymentAmountTR.visible());
	},
	
	_validate	: function(bCacheValues)
	{
		// Build an array of error messages, after running all validation functions
		var aErrors	= [];
		var mError 	= null;
		var oField 	= null;
		for (var sName in this._hFields)
		{
			oField = this._hFields[sName];
			try
			{
				oField.validate(false);
				oField.save(true);
				
				if (bCacheValues)
				{
					this._hFieldValues[sName]	= oField.getValue();
				}
			}
			catch (oException)
			{
				aErrors.push(oException);
			}
		}
		
		return aErrors;
	},
	
	_save	: function()
	{
		var aErrors	= this._validate(true);
		if (aErrors.length)
		{
			Popup_Account_Add_CreditCard._showValidationErrorPopup(aErrors);
			return;
		}
		
		// Round the amount to 2 decimal places before showing the confirmation popup
		this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT]	=
			parseFloat(this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT]).toFixed(2);
		
		if (this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_SUBMIT_PAYMENT])
		{
			// Confirm payment
			Reflex_Popup.yesNoCancel(
				$T.div({class: 'popup-add-direct-debit-confirmcontent'},
					$T.p(
						$T.span('An amount of '),
						$T.span({class: 'popup-add-direct-debit-highlight'},
							'$' + this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT]
						),
						$T.span(' will be charged to the given Credit Card with NO SURCHARGE applied.')
					),
					$T.p(
						$T.div(
							$T.span('The Payment Method will be set to '),
							$T.span({class: 'popup-add-direct-debit-highlight'},
								'Direct Debit via Credit Card'
							), 
							$T.span(' using the given Credit Card:')
						),
						$T.div({class: 'popup-add-credit-card-preview'},
							$T.div(Popup_Account_Add_CreditCard._getCardType(this._oCardType.getElementValue()).name),
							$T.div(this._oCardNumber.getElementValue() + ' (' + this._oCardCVV.getElementValue() + ')'),
							$T.div(this._oCardName.getElementValue()),
							$T.div(Date.$parseDate(this._oCardExpiry.getElementValue(), 'Y-m').$format('m/Y'))
						)
					)
				),
				{
					sTitle			: 'Confirm Changes',
					fnOnYes			: this._doSave.bind(this, true),
					bOverrideStyle	: true,
					sYesLabel		: 'Confirm',
					sNoLabel		: 'Cancel'
				}
			);
		}
		else
		{
			// No payment, check if they want to use this account as the payment method
			Reflex_Popup.yesNoCancel(
				'Do you want to use this Credit Card as the Payment Method for the Flex Account ' + this._iAccountId + ' (' + this._oAccountInfo.sBusinessName + ')?',
				{fnOnYes: this._doSave.bind(this, true)}
			);
		}
	},
	
	_doSave	: function(bChangePaymentMethod)
	{
		if (bChangePaymentMethod)
		{
			// Record that payment method is to be changed
			this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_SET_PAYMENT_METHOD]	= true;
		}
		
		// Sanitise card type & cvv
		this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_CARD_TYPE]	= 
			parseInt(this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_CARD_TYPE]);
		
		var oExpiryDate	= Date.$parseDate(this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_EXPIRY_DATE], 'Y-m');
		this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_EXPIRY_MONTH]	= oExpiryDate.getMonth() + 1;
		this._hFieldValues[Popup_Account_Add_CreditCard.FIELD_EXPIRY_YEAR]	= oExpiryDate.getFullYear();
		
		// Create a Popup to show 'saving...' close it when save complete
		this._oLoading = new Reflex_Popup.Loading('Saving...');
		this._oLoading.display();
		
		var fnAddCreditCard	= 	jQuery.json.jsonFunction(
										this._saveResponse.bind(this, bChangePaymentMethod), 
										this._ajaxError.bind(this), 
										'Account', 
										'addCreditCard'
									);
		fnAddCreditCard(this._iAccountId, this._hFieldValues);
	},
	
	_saveResponse	: function(bChangePaymentMethod, oResponse)
	{
		this._oLoading.hide();
		delete this._oLoading;
		
		if (oResponse.Success)
		{
			if (oResponse.oTransactionDetails)
			{
				// A credit card payment was made & was successfull
				Reflex_Popup.alert('Your credit card payment was processed successfully!');
				
				// A "Payment Made" action will have been created.  Fire the event, if the ActionsAndNotes package is loaded
				if (window.ActionsAndNotes)
				{
					ActionsAndNotes.fireEvent('NewAction');
				}
			}
			
			this.hide();
			
			if (bChangePaymentMethod && this._fnOnPaymentMethodChange)
			{
				this._fnOnPaymentMethodChange(oResponse.oCreditCard);
			}
			else if (this._fnOnSave)
			{
				this._fnOnSave(oResponse.oCreditCard);
			}
		}
		else if (oResponse.aValidationErrors)
		{
			// Validation errors
			Popup_Account_Add_CreditCard._showValidationErrorPopup(oResponse.aValidationErrors);
		}
		else
		{
			// Credit card payment error
			if (oResponse.bPaymentError)
			{
				// Payment error
				Reflex_Popup.alert(
					'Your credit card payment could not be processed. ' + oResponse.Message, 
					{sTitle: 'Payment Failed'}
				);
			}
			else
			{
				// General ajax error
				this._ajaxError(oResponse);
			}
		}
	},
	
	_ajaxError	: function(oResponse, bHideOnClose)
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
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
	
	_cancel	: function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
		
		this.hide();
	},
	
	_getField	: function(sFieldName)
	{
		if (this._hFields[sFieldName])
		{
			return this._hFields[sFieldName];
		}
		
		var oConfig	= Popup_Account_Add_CreditCard.FIELDS[sFieldName];
		if (oConfig)
		{
			var oField	= Control_Field.factory(oConfig.sType, oConfig.oConfig);
			oField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._hFields[sFieldName]	= oField;
			return oField;
		}
		return null;
	},
	
	_getPaymentMethodName	: function()
	{
		var sMethod	= Flex.Constant.arrConstantGroups.payment_method[this._oAccountInfo.iPaymentMethod].Name;
		var sType	= '';
		switch (this._oAccountInfo.iPaymentMethod)
		{
			case $CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT:
				sType	= Flex.Constant.arrConstantGroups.direct_debit_type[this._oAccountInfo.iPaymentMethodSubType].Name;
				break;
			case $CONSTANT.PAYMENT_METHOD_REBILL:
				sType	= Flex.Constant.arrConstantGroups.rebill_type[this._oAccountInfo.iPaymentMethodSubType].Name;
				break;	
		}
		return sMethod + (sType !== '' ? ' via ' + sType : '');
	},
	
	_selectPaymentAmountRadio	: function(sField)
	{
		this._getField(sField).setValue(true);
		this._paymentAmountChange(sField);
	},
	
	_paymentAmountChange	: function(sField)
	{
		var oAmount	= this._getField(Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT);
		var oBalanceSpan	= this._oPaymentSection.getElement().select('.popup-add-direct-debit-paymentamountbalance').first();
		var oOverdueSpan	= this._oPaymentSection.getElement().select('.popup-add-direct-debit-paymentamountoverdue').first();
		var oOtherSymbol	= this._oPaymentSection.getElement().select('.popup-add-direct-debit-currencysymbol').last();
		oBalanceSpan.removeClassName('popup-add-direct-debit-paymentamountdisabled');
		oOverdueSpan.removeClassName('popup-add-direct-debit-paymentamountdisabled');
		oOtherSymbol.removeClassName('popup-add-direct-debit-paymentamountdisabled');
		
		switch (sField)
		{
			case Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE:
				oOverdueSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oOtherSymbol.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oAmount.disableInput();
				break;
			case Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OVERDUE:
				oBalanceSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oOtherSymbol.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oAmount.disableInput();
				break;
			case Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OTHER:
				oOverdueSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oBalanceSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oAmount.enableInput();
				break;
		}
	},
	
	_selectPaymentCheckbox	: function()
	{
		var oSubmitPayment	= this._getField(Popup_Account_Add_CreditCard.FIELD_SUBMIT_PAYMENT);
		oSubmitPayment.setValue(!oSubmitPayment.getValue());
		this._submitPaymentChange();
	},
	
	_validateCVV	: function(mValue)
	{
		var oCardType 			= Popup_Account_Add_CreditCard._getCardType(this._oCardType.getElementValue());
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
		
		this._oCardCVV.setValidationReason(sValidationReason);
		return bCVVIsValid;
	},
	
	_showCVVTooltip	: function(bShow, oRelativeTo)
	{
		if (bShow)
		{
			this._oCVVTooltip.show();
			if (oRelativeTo)
			{
				// An element to show next to
				var iValueT	= 0;
				var iValueL	= 0;
				var iWidth	= oRelativeTo.offsetWidth;
				var iHeight	= oRelativeTo.offsetHeight;
				do 
				{
					iValueT 	+= oRelativeTo.offsetTop || 0;
					iValueL 	+= oRelativeTo.offsetLeft || 0;
					oRelativeTo	= oRelativeTo.offsetParent;
				} 
				while (oRelativeTo);
				
				iValueL	+= iWidth + document.body.scrollLeft;
				iValueT += document.body.scrollTop;
				
				this._oCVVTooltip.style.left	= iValueL + 'px';
				this._oCVVTooltip.style.top		= iValueT + 'px';
				document.body.appendChild(this._oCVVTooltip);
			}
		}
		else
		{
			this._oCVVTooltip.remove();
		}
	},
	
	_validateCardNumber	: function(mValue)
	{
		var iCardType			= this._oCardType.getElementValue();
		var oCardType			= Popup_Account_Add_CreditCard._getCardType(iCardType);
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
				oValidateType = Popup_Account_Add_CreditCard._getCCType(sCardNumber);
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
						bCardNumberIsValid 	= Popup_Account_Add_CreditCard._checkCardNumber(sCardNumber, oValidateType['id']);
						if (!bCardNumberIsValid)
						{
							sValidationReason 	= 'The Card Number entered is invalid.';
						}
					}
				}
			}
			else
			{
				oValidateType	= (sRubbish == '') && Popup_Account_Add_CreditCard._getCCType(sCardNumber);
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
		
		this._oCardNumber.setValidationReason(sValidationReason);
		return bCardNumberIsValid;
	},
	
	_cardTypeChanged	: function()
	{
		this._oCardNumber.validate();
		this._oCardCVV.validate();
	}	
});

//
// Static
//

Object.extend(Popup_Account_Add_CreditCard, 
{
	CANCEL_IMAGE_SOURCE 	: '../admin/img/template/delete.png',
	SAVE_IMAGE_SOURCE 		: '../admin/img/template/tick.png',
	
	FIELD_CARD_TYPE					: 'iCardType',
	FIELD_CARD_NAME					: 'sCardHolderName',
	FIELD_CARD_NUMBER				: 'iCardNumber',
	FIELD_EXPIRY_DATE				: 'sExpiryDate',
	FIELD_EXPIRY_MONTH				: 'iExpiryMonth',
	FIELD_EXPIRY_YEAR				: 'iExpiryYear',
	FIELD_CVV						: 'iCVV',
	FIELD_SUBMIT_PAYMENT			: 'bSubmitPayment',
	FIELD_PAYMENT_AMOUNT			: 'sPaymentAmount',
	FIELD_PAYMENT_AMOUNT_BALANCE	: 'bPaymentAmountBalance',
	FIELD_PAYMENT_AMOUNT_OVERDUE	: 'bPaymentAmountOverdueBalance',
	FIELD_PAYMENT_AMOUNT_OTHER		: 'bPaymentAmountOther',
	FIELD_SET_PAYMENT_METHOD		: 'bSetPaymentMethod',
	
	FIELDS		: {},
	_aCardTypes	: {},
	
	_showValidationErrorPopup	: function(aErrors)
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
	},
	
	_getAccountPaymentInfo	: function(iAccountId, fnCallback, oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			var fnResp	= Popup_Account_Add_CreditCard._getAccountPaymentInfo.curry(iAccountId, fnCallback);
			var oReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'getPaymentInfo');
			oReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Reflex_Popup.alert(oResponse.sMessage, {sTitle: 'Error'});
			return;
		}
		
		fnCallback(oResponse.aInfo);
	},
	
	_getCardTypeOptions	: function(fnCallback, oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			var fnResp	= Popup_Account_Add_CreditCard._getCardTypeOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Credit_Card', 'getAllTypes');
			fnReq();
			return;
		}
		
		var aOptions	= [];
		for (var iId in oResponse.aCreditCardTypes)
		{
			if (!Object.isUndefined(oResponse.aCreditCardTypes[iId].id))
			{
				var oCardType	= oResponse.aCreditCardTypes[iId];
				
				// Cache type
				if (!Popup_Account_Add_CreditCard._aCardTypes)
				{
					Popup_Account_Add_CreditCard._aCardTypes	= {};
				}
				
				Popup_Account_Add_CreditCard._aCardTypes[oCardType.id]	= oCardType;
				
				// Add option
				aOptions.push(
					$T.option({value: oCardType.id},
						oCardType.name
					)
				);
			}
		}
		fnCallback(aOptions);
	},
	
	_getCardType	: function(iCardTypeId)
	{
		return Popup_Account_Add_CreditCard._aCardTypes[iCardTypeId];
	},
	
	_validateCardName	: function(mValue)
	{
		return (mValue.toString().replace(/[^a-zA-Z]+/g, '') != '');
	},
	
	_validateExpiry	: function(mValue)
	{
		var oDate	= Date.$parseDate(mValue, 'Y-m');
		if (!oDate)
		{
			return false;
		}
		
		var iMonth 			= oDate.getMonth() + 1;
		var iYear			= oDate.getFullYear();
		var oNow 			= new Date();
		var iCurrentMonth 	= oNow.getMonth() + 1;
		var iCurrentYear 	= oNow.getFullYear();
		return iYear > iCurrentYear || (iYear == iCurrentYear && iMonth >= iCurrentMonth);
	},
	
	_checkCardNumber	: function(mNumber)
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
		return Popup_Credit_Card_Payment._checkLuhn(sNumber);
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
	},
	
	_getCCType: function(mNumber)
	{
		if (mNumber.length < CreditCardType.minPrefixLength)
		{
			return false;
		}
		return CreditCardType.cardTypeForNumber(mNumber);
	}
});

//
//	More Static
//

Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_CARD_TYPE]	=
{
	sType	: 'select',
	oConfig	: 
	{
		sLabel		: 'Card Type',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnPopulate	: Popup_Account_Add_CreditCard._getCardTypeOptions
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_CARD_NAME]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Card Holder Name',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Popup_Account_Add_CreditCard._validateCardName
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_EXPIRY_DATE]	=
{
	sType	: 'combo_date',
	oConfig	: 
	{
		sLabel				: 'Expiry Date',
		mMandatory			: true, 
		mEditable			: true,
		mVisible			: true,
		iMinYear			: new Date().getFullYear(),
		iMaxYear			: new Date().getFullYear() + 10,
		iFormat				: Control_Field_Combo_Date.FORMAT_M_Y,
		fnValidate			: Popup_Account_Add_CreditCard._validateExpiry,
		sValidationReason	: 'The Expiry Date must be a date in the future.'
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_CVV]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'CVV #',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_CARD_NUMBER]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Card Number',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_SUBMIT_PAYMENT]	=
{
	sType	: 'checkbox',
	oConfig	: 
	{
		sLabel		: 'Submit Payment',
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Amount To Pay',
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Reflex_Validation.float
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_BALANCE]	=
{
	sType	: 'radiobutton',
	oConfig	: 
	{
		sLabel		: 'Account Balance',
		sFieldName	: 'payment_amount',
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OVERDUE]	=
{
	sType	: 'radiobutton',
	oConfig	: 
	{
		sLabel		: 'Overdue Balance',
		sFieldName	: 'payment_amount',
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_CreditCard.FIELDS[Popup_Account_Add_CreditCard.FIELD_PAYMENT_AMOUNT_OTHER]	=
{
	sType	: 'radiobutton',
	oConfig	: 
	{
		sLabel		: 'Other',
		sFieldName	: 'payment_amount',
		mEditable	: true,
		mVisible	: true
	}
};
