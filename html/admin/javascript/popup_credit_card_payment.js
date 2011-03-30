
var Popup_Credit_Card_Payment	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(40);
		this._iAccountId	= iAccountId;
		this._hFields		= {};
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oAccountInfo, bConstantsLoaded)
	{
		if (Object.isUndefined(oAccountInfo))
		{
			// Get account info
			Popup_Credit_Card_Payment._getAccountPaymentInfo(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		else if (!bConstantsLoaded)
		{
			// Load db consants
			Flex.Constant.loadConstantGroup(['payment_method', 'direct_debit_type', 'rebill_type'], this._buildUI.bind(this, oAccountInfo, true));
			return;
		}
		
		this._oAccountInfo				= oAccountInfo;
		this._bPaymentMethodCreditCard	= (this._oAccountInfo.iPaymentMethodSubType == $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD);
		
		this._oEmail	= this._getField(Popup_Credit_Card_Payment.ACCOUNT_EMAIL);
		this._oEmail.setValue(this._oAccountInfo.sContactEmail);
		
		var sPaymentMethodName	= this._getPaymentMethodName();
		
		var oAccountSection	= new Section(true);
		oAccountSection.setTitleText('Account');
		oAccountSection.setContent(
			$T.div(
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th({class: 'label'},
								'Account:'
							),
							$T.td(this._iAccountId)
						),
						$T.tr(
							$T.th({class: 'label'},
								'ABN:'
							),
							$T.td(this._oAccountInfo.sABN)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Company:'
							),
							$T.td(this._oAccountInfo.sBusinessName)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Current Payment Method:'
							),
							$T.td(sPaymentMethodName)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Email:'
							),
							$T.td(this._oEmail.getElement())
						)
					)
				)
			)
		);
		
		this._oCardType	= this._getField(Popup_Credit_Card_Payment.CARD_TYPE);
		this._oCardType.addOnChangeCallback(this._cardTypeChange.bind(this));
		
		this._oCardName	= this._getField(Popup_Credit_Card_Payment.CARD_NAME);
		
		this._oCardNumber	= this._getField(Popup_Credit_Card_Payment.CARD_NUMBER);
		this._oCardNumber.setValidateFunction(this._validateCardNumber.bind(this));
		this._oCardCVV		= this._getField(Popup_Credit_Card_Payment.CARD_CVV);
		this._oCardCVV.setValidateFunction(this._validateCVV.bind(this));
		this._oCardExpiry	= this._getField(Popup_Credit_Card_Payment.CARD_EXPIRY);
		this._oCardExpiry.getElement().addClassName('credit-card-payment-popup-cardexpiry');
		
		var sTidyAmountOwing	= this._tidyAmount(this._oAccountInfo.fBalance);
		
		this._oDirectDebit	= this._getField(Popup_Credit_Card_Payment.CARD_DD_UPDATE);
		this._oDirectDebit.addOnChangeCallback(this._directDebitChanged.bind(this));
		
		this._oDirectDebitInvalid	=	$T.span('-');
		
		this._oCardAmount	= this._getField(Popup_Credit_Card_Payment.CARD_AMOUNT);
		this._oCardAmount.getElement().addClassName('credit-card-payment-popup-amounttopay');
		this._oCardAmount.addOnChangeCallback(this._amountChanged.bind(this));
		this._oCardAmount.setValue(sTidyAmountOwing);
		this._oCardAmount.setValidateFunction(this._validateAmount.bind(this));
		
		this._oCardSurcharge	= this._getDisplayOnlyField();
		this._oCardSurcharge.setValue(this._tidyAmount(0.00));
		
		this._oCardTotalPayment	= this._getDisplayOnlyField();
		this._oCardTotalPayment.setValue(sTidyAmountOwing);
		
		this._oCardBalance	= this._getDisplayOnlyField();
		this._oCardBalance.setValue(sTidyAmountOwing);
		
		this._oCardBalanceAfterPayment	= this._getDisplayOnlyField();
		this._oCardBalanceAfterPayment.setValue(this._tidyAmount(0.00));
		
		this._oUseExistingCardDetails	= this._getField(Popup_Credit_Card_Payment.EXISTING_CARD_DETAILS);
		this._oUseExistingCardDetails.addOnChangeCallback(this._useExistingCardDetailsChange.bind(this));
		this._oUseExistingCardDetailsDiv	=	$T.div({class: 'credit-card-payment-popup-useexistingdetails'},
													this._oUseExistingCardDetails.getElement(),
													$T.span('Use the ' + sPaymentMethodName + ' details').observe('click', this._selectUseExistingCardDetails.bind(this))
												);
		this._oUseExistingCardDetailsDiv.hide();
		
		var oCardSection	= new Section(true);
		oCardSection.setTitleText('Card');
		oCardSection.addToHeaderOptions(this._oUseExistingCardDetailsDiv);
		oCardSection.setContent(
			$T.div(
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th({class: 'label'},
								'Credit Card Type:'
							),
							$T.td(this._oCardType.getElement())
						),
						$T.tr(
							$T.th({class: 'label'},
								'Name on Card:'
							),
							$T.td(this._oCardName.getElement())
						),
						$T.tr(
							$T.th({class: 'label'},
								'Card Number:'
							),
							$T.td(this._oCardNumber.getElement())
						),
						$T.tr(
							$T.th({class: 'label'},
								$T.span({class: 'credit-card-payment-popup-cvvlabel'},
									'CVV:'
								)
							),
							$T.td(this._oCardCVV.getElement())
						),
						$T.tr(
							$T.th({class: 'label'},
								'Expiry Date:'
							),
							$T.td(
								this._oCardExpiry.getElement(),
								$T.span({class: 'credit-card-payment-popup-cardexpiryformat'},
									'(MM/YYYY)'
								)
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Save as Payment Method:'
							),
							$T.td(
								this._oDirectDebit.getElement(),
								this._oDirectDebitInvalid
							)
						)
					)
				)
			)		
		);
		
		var oPaymentSection	= new Section(true);
		oPaymentSection.setTitleText('Payment');
		oPaymentSection.setContent(
			$T.div(
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th({class: 'label'},
								'Amount to Pay:'
							),
							$T.td(
								$T.span({class: 'credit-card-payment-popup-currencysymbol'},
									'$'
								),
								this._oCardAmount.getElement()
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Credit Card Surcharge:'
							),
							$T.td(
								$T.span({class: 'credit-card-payment-popup-currencysymbol'},
									'$'
								),
								this._oCardSurcharge.getElement()
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Total Payment:'
							),
							$T.td(
								$T.span({class: 'credit-card-payment-popup-currencysymbol'},
									'$'
								),
								this._oCardTotalPayment.getElement()
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Current Balance:'
							),
							$T.td(
								$T.span({class: 'credit-card-payment-popup-currencysymbol'},
									'$'
								),
								this._oCardBalance.getElement()
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Balance After Payment:'
							),
							$T.td(
								$T.span({class: 'credit-card-payment-popup-currencysymbol'},
									'$'
								),
								this._oCardBalanceAfterPayment.getElement()
							)
						)
					)
				)
			)		
		);
		
		if (this._bPaymentMethodCreditCard)
		{
			this._oUseExistingCardDetailsDiv.show();
		}
		this._useExistingCardDetailsChange();
		
		var oTooltipElement	= oCardSection.getElement().select('.credit-card-payment-popup-cvvlabel').first();
		oTooltipElement.observe('mouseover', this._showCVVTooltip.bind(this, true, oTooltipElement));
		oTooltipElement.observe('mouseout', this._showCVVTooltip.bind(this, false));
		this._oCVVTooltip	=	$T.div({class: 'cvv-image-tooltip'},
									$T.img({src: '../ui/img/template/cvv_visa.gif'}),
									$T.img({src: '../ui/img/template/cvv_amex.gif'})
								);
		
		this.setContent(
			$T.div({class: 'credit-card-payment-popup'},
				$T.div(
					oAccountSection.getElement(),
					oCardSection.getElement(),
					oPaymentSection.getElement()
				),
				$T.div({class: 'buttons'},
					$T.button({class: 'icon-button'},
						'Submit'
					).observe('click', this._submit.bind(this)),
					$T.button({class: 'icon-button'},
						'Cancel'
					).observe('click', this.hide.bind(this))
				)
			)
		);
		
		this.setTitle('Secure Credit Card Payment');
		this.addCloseButton();
		this.display();
	},
	
	_getField	: function(sFieldName)
	{
		var oConfig	= Popup_Credit_Card_Payment.FIELDS[sFieldName];
		if (oConfig)
		{
			var oField	= Control_Field.factory(oConfig.sType, oConfig.oConfig);
			oField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._hFields[sFieldName]	= oField;
			return oField;
		}
		else
		{
			return null;
		}
	},
	
	_getDisplayOnlyField	: function()
	{
		var oField	= this._getField(Popup_Credit_Card_Payment.CARD_DISPLAY_ONLY);
		oField.disableInput();
		oField.getElement().addClassName('credit-card-payment-popup-displayonly');
		return oField;
	},
	
	_directDebitChanged	: function()
	{
		this._updateSurcharges();
	},
	
	_amountChanged	: function()
	{
		this._updateSurcharges();
		this._updateBalances();
	},
	
	_updateBalances: function()
	{
		//var fAmount		= parseFloat(this._oCardAmount.getElementValue().replace(/[^0-9\.]+/g, ''));
		//fAmount 		= isNaN(fAmount) ? 0.00 : fAmount;
		var sBalance	= this._tidyAmount(this._oAccountInfo.fBalance - (isNaN(this._fTotalPayment) ? 0 : this._fTotalPayment));
		var fBal 		= parseFloat(sBalance);
		if (fBal < 0)
		{
			fBalance	= sBalance.replace(/\-/g, '');
			fBalance 	+= " CR";
		}
		
		this._oCardBalanceAfterPayment.setValue(sBalance);
	},

	_updateSurcharges: function()
	{
		this._oCardSurcharge.clearValue();
		this._oCardTotalPayment.clearValue();
		
		var iCardType 	= this._oCardType.getElementValue();
		var oCardType	= Popup_Credit_Card_Payment._getCardType(iCardType);
		var fAmount 	= parseFloat(this._oCardAmount.getElementValue().replace(/[^0-9\.]+/g, ''));
		fAmount 		= isNaN(fAmount) ? 0.00 : fAmount;
		var fSurcharge 	= oCardType ? this._calculateSurcharge(oCardType, fAmount) : 0.00;
		var sSurcharge 	= this._tidyAmount(fSurcharge);
		
		if (this._bPaymentMethodCreditCard || this._oDirectDebit.getElementValue())
		{
			// NO SURCHARGE APPILED: if the account is already on credit card direct debit OR is going to be
			fSurcharge	= 0;
			sSurcharge	= '[WAIVED]';
		}
		
		// Payments to Secure Pay must be to 2 decimal places 
		// this ensures that the total payment meets that criteria
		this._fTotalPayment	= (fAmount + fSurcharge).toFixed(2);
		this._fSurcharge	= fSurcharge;
		
		this._oCardSurcharge.setValue(sSurcharge);
		this._oCardTotalPayment.setValue(this._tidyAmount(fAmount + fSurcharge));
	},
	
	_tidyAmount: function(fAmount)
	{
		if (typeof fAmount == 'string') 
		{
			fAmount	= parseFloat(fAmount.replace(/[^0-9\.]+/g, ''));
		}
		
		if (isNaN(fAmount)) 
		{
			fAmount	= 0.00;
		}
		
		var sTidy	= "" + Math.round(fAmount * 100) / 100;
		if (!sTidy.match(/\./))
		{
			sTidy	+= ".00";
		}
		
		if (sTidy.match(/\.[0-9]{1,1}$/))
		{
			sTidy 	+= "0";
		}
		
		return sTidy;
	},

	_calculateSurcharge: function(oCardType, fAmount)
	{
		return Math.round((fAmount * 100) * oCardType['surcharge'])/100;
	},
	
	_cardTypeChange	: function()
	{
		if (this._bPaymentMethodCreditCard && this._oUseExistingCardDetails.getElementValue() && (this._oCardType.getValue() === ''))
		{
			// Just loaded, set the value to the accounts credit card type (from the current payment method)
			this._oCardType.setValue(this._oAccountInfo.oPaymentMethod.CardType);
		}
		else
		{
			// Revalidate amount, number & cvv
			this._amountChanged();
			this._oCardNumber.validate();
			this._oCardCVV.validate();
		}
	},
	
	_validateCardNumber	: function(mValue)
	{
		var sValidationReason 	= '';
		var bCardNumberIsValid	= true;
		try
		{
			Reflex_Validation_Credit_Card.validateCardNumber(mValue, this._oCardType.getElementValue());
		}
		catch (oException)
		{
			sValidationReason	= oException;
			bCardNumberIsValid	= false;
		}
		
		this._oCardNumber.setValidationReason(sValidationReason);
		return bCardNumberIsValid;
	},
	
	_validateAmount	: function(mValue)
	{
		var oCardType			= Popup_Credit_Card_Payment._getCardType(this._oCardType.getElementValue());
		var sValidationReason	= '';
		var sCleansed 			= mValue.replace(/[^0-9\.]+/g, '');
		var bAmountIsValid 		= mValue.match(/^ *[0-9]+[\, 0-9]*\.?(|[0-9]+[\, 0-9]*)$/) && !isNaN(parseFloat(sCleansed));
		var fAmount 			= parseFloat(mValue.replace(/[^0-9\.]+/g, ''));
		if (bAmountIsValid)
		{
			if (oCardType)
			{
				var fTotal 		= Math.floor((fAmount + this._calculateSurcharge(oCardType, fAmount)) * 100) / 100;
				bAmountIsValid 	= oCardType['minimum_amount'] <= fTotal && fTotal <= oCardType['maximum_amount'];
				if (!bAmountIsValid)
				{
					var fMinAmount 		= this._tidyAmount(oCardType['minimum_amount'] / (1 + oCardType['surcharge']));
					var fMaxAmount 		= this._tidyAmount(oCardType['maximum_amount'] / (1 + oCardType['surcharge']));
					sValidationReason	= 'Amount to Pay must be between $' + fMinAmount + ' and $' + fMaxAmount + ' for the selected Credit Card Type.';
				}
			}
			else
			{
				fAmount 		= Math.floor(fAmount);
				bAmountIsValid 	= fAmount > 0;
				if (!bAmountIsValid)
				{
					sValidationReason = 'Amount to Pay must be greater than 0 (zero).';
				}
			}
		}
		else
		{
			sValidationReason = 'It must must be number greater than 0 (zero).';
		}
		
		this._oCardAmount.setValidationReason(sValidationReason);
		return bAmountIsValid;
	},
	
	_submit	: function(oEvent, bConfirmed)
	{
		if (typeof bConfirmed != 'boolean')
		{
			bConfirmed = false;
		}
		
		// Validate & save each field value
		var oField	= this._hFields[sField];
		var aErrors	= [];
		for (var sField in this._hFields)
		{
			oField	= this._hFields[sField];
			try
			{
				oField.validate(false);
				oField.save(true);
			}
			catch (oException)
			{
				aErrors.push(oException);
			}
		}
		
		if (aErrors.length)
		{
			var oErrorList	= $T.ul();
			for (var i = 0; i < aErrors.length; i++)
			{
				oErrorList.appendChild($T.li(aErrors[i]));
			}
			
			new Reflex_Popup.alert(
				$T.div({class: 'credit-card-payment-popup-validationerrors'},
					$T.div('The payment form contains errors:'),
					oErrorList
				)
			);
			return;
		}
		
		// Confirm
		if (!bConfirmed)
		{
			new Reflex_Popup.yesNoCancel(
				this._getConfirmationSummaryElement(), 
				{
					sYesLabel	: 'Confirm',
					sNoLabel	: 'Cancel',
					fnOnYes		: this._submit.bind(this, null, true)
				}
			);
			return;
		}

		// Show loading popup
		this._showLoading(true, 'Processing Payment...');
		
		var fnSubmit =	jQuery.json.jsonFunction(
							this._submitComplete.bind(this), 
							null, 
							'Credit_Card_Payment', 
							'makePayment'
						);

		// Gather the validated values and submit to the server
		var oExpiryDate	= Date.$parseDate(this._oCardExpiry.getValue() + '-01', 'Y-m-d');
		fnSubmit(
			this._iAccountId,								// iAccountId
			this._oCardType.getValue(),						// iCardType
			this._oCardNumber.getValue(),					// sCardNumber
			this._oCardCVV.getValue(),						// iCVV
			oExpiryDate.getMonth() + 1,						// iMonth
			oExpiryDate.getFullYear(),						// iYear
			this._oCardName.getValue(),						// sName
			this._oCardAmount.getValue(),					// fAmount
			this._oEmail.getValue(),						// sEmail
			this._oDirectDebit.getValue(),					// bDirectDebit
			this._oUseExistingCardDetails.getElementValue()	// Use current payment method
		);
	},
	
	_getConfirmationSummaryElement	: function()
	{
		return	$T.div({class: 'credit-card-payment-popup-confirmation'},
					$T.div('Please review the information you have supplied before confirming the payment:'),
					$T.table({class: 'credit-card-payment-popup-confirmation-table'},
						$T.tbody(
							$T.tr(
								$T.td('Email:'),
								$T.td(this._oEmail.getElementValue())
							),
							$T.tr(
								$T.td('Payment Amount:'),
								$T.td('$' + this._fTotalPayment + (this._fSurcharge !== 0 ? ' (Includes $' + this._fSurcharge + ' Surcharge)' : ' (NO SURCHARGE)'))
							),
							$T.tr(
								$T.td('Credit Card:'),
								$T.td(
									$T.div(Popup_Credit_Card_Payment._getCardType(this._oCardType.getElementValue()).name),
									$T.div(this._oCardNumber.getElementValue() + ' (' + this._oCardCVV.getElementValue() + ')'),
									$T.div(this._oCardName.getElementValue()),
									$T.div(Date.$parseDate(this._oCardExpiry.getElementValue() + '-01', 'Y-m-d').$format('m/Y'))
								)
							),
							$T.tr(
								$T.td('Save as Payment Method:'),
								$T.td(this._oDirectDebit.getElementValue() ? 'YES' : 'No')
							)
						)
					)
				);
	},
	
	_showLoading	: function(bShow, sMessage)
	{
		if (bShow)
		{
			// Show with message
			this._oLoading	= new Reflex_Popup.Loading(sMessage);
			this._oLoading.display();
		}
		else if (this._oLoading)
		{
			// Hide if shown previously
			this._oLoading.hide();
			delete this._oLoading;
		}
	},
	
	_submitComplete	: function(oResponse)
	{
		this._showLoading(false);
		
		if (!oResponse.bSuccess)
		{
			// Check for specific exception, determine action from type
			var sTitle		= 'Server Error';
			var sMessage	= oResponse.sMessage;
			if (oResponse.bInformativeError)
			{
				sTitle		= 'Payment Failed';
				sMessage	= 'Your credit card payment could not be processed. ' + oResponse.sMessage;
			}
			
			var bHaveDebugText	= (oResponse.sDebug && (oResponse.sDebug != ''));
			
			// Show popup
			Reflex_Popup.alert(
				oResponse.sMessage, 
				{
					sTitle			: sTitle, 
					fnClose			: (bHaveDebugText ? Reflex_Popup.debug.bind(Reflex_Popup, oResponse.sDebug) : null),
					sButtonLabel	: (bHaveDebugText ? 'OK (View Log)' : null)
				}
			);
			return;
		}
		
		Reflex_Popup.alert('Your credit card payment was processed successfully!');
		
		// A "Payment Made" action will have been created.  Fire the event, if the ActionsAndNotes package is loaded
		if (window.ActionsAndNotes)
		{
			ActionsAndNotes.fireEvent('NewAction');
		}
		
		this.hide();
		
		return;
		
		/*
		// Check the 'OUTCOME' property of the response
		var sOutcome	= oResponse['OUTCOME'];
		switch (sOutcome)
		{
			// INVALID = problem with the submitted values
			case 'INVALID':
				// Need to display the confirmation message and change buttons to OK
				Reflex_Popup.alert(
					$T.div({class: 'alert-content'},
						$T.div('Your payment request could not be processed: '),
						$T.p(oResponse['MESSAGE']),
						$T.div('Please check your details and try again.')
					), 
					{sTitle: 'Payment Failure'}
				);
				return false;
			
			// UNAVAILABLE 	= The SecurePay servers could not be contacted
			// FAILED 		= A problem occurred communicating with the SecurePay servers
			case 'UNAVAILABLE':
			case 'FAILED':
				// Need to display the confirmation message and change buttons to OK
				Reflex_Popup.alert(
					$T.div({class: 'alert-content'},
						$T.div('Your payment request could not be processed: '),
						$T.p(oResponse['MESSAGE']),
						$T.div('Please try again later.')
					), 
					{sTitle: 'Payment Failure'}
				);
				return false;
			
			// FLEX_LOGGING_FAILURE = The Payment was made, but then it couldn't be logged as a payment in Flex.  A Payment Reversal would have been attempted, but might have failed
			case 'FLEX_LOGGING_FAILURE':
				Reflex_Popup.alert(
					$T.div({class: 'alert-content'},
						$T.div('Your payment request could not be completed:'),
						$T.p(),
						$T.div(oResponse['MESSAGE'])
					), {sTitle: 'Payment Failure'}
				);
				return false;
			
			// SUCCESS = The payment was made and DD details stored (if appropriate)
			case 'SUCCESS':		
				// Need to display the confirmation message and change buttons to OK
				Reflex_Popup.alert(oResponse['MESSAGE']);
				
				// A "Payment Made" action will have been created.  Fire the event, if the ActionsAndNotes package is loaded
				if (window.ActionsAndNotes)
				{
					ActionsAndNotes.fireEvent('NewAction');
				}
				return true;
			
			default:
				// Something failed, this shouldn't happen but is here as a fail safe
				Reflex_Popup.alert('There was an error accessing the database, please contact YBS for assistance.', {sTitle: 'Error'});
		}
		*/
	},
	
	_validateCVV	: function(mValue)
	{
		var sValidationReason 	= '';
		var bCVVIsValid			= true;
		try
		{
			Reflex_Validation_Credit_Card.validateCVV(mValue, this._oCardType.getElementValue());
		}
		catch (oException)
		{
			sValidationReason	= oException;
			bCVVIsValid			= false;
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
	
	_selectUseExistingCardDetails	: function()
	{
		this._oUseExistingCardDetails.setValue(!this._oUseExistingCardDetails.getElementValue());
		this._useExistingCardDetailsChange();
	},
	
	_useExistingCardDetailsChange	: function()
	{
		var bUseExistingDetails	= this._oUseExistingCardDetails.getElementValue();
		if (this._bPaymentMethodCreditCard && bUseExistingDetails)
		{
			this._oCardType.setRenderMode(Control_Field.RENDER_MODE_VIEW);
			this._oCardName.setRenderMode(Control_Field.RENDER_MODE_VIEW);
			this._oCardNumber.setRenderMode(Control_Field.RENDER_MODE_VIEW);
			this._oCardExpiry.setRenderMode(Control_Field.RENDER_MODE_VIEW);
			this._oCardCVV.setRenderMode(Control_Field.RENDER_MODE_VIEW);
			
			this._oDirectDebit.getElement().hide();
			this._oDirectDebitInvalid.show();
			
			// Use the current payment methods cc details
			var oCreditCard	= this._oAccountInfo.oPaymentMethod;
			this._oCardType.setValue(oCreditCard.CardType);
			this._oCardName.setValue(oCreditCard.Name);
			this._oCardNumber.setValue(oCreditCard.card_number);
			this._oCardCVV.setValue(oCreditCard.cvv);
			this._oCardExpiry.setValue(oCreditCard.ExpYear + '-' + oCreditCard.ExpMonth);
		}
		else
		{
			this._oCardType.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._oCardName.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._oCardNumber.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._oCardExpiry.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._oCardCVV.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			
			this._oDirectDebit.getElement().show();
			this._oDirectDebitInvalid.hide();
			
			// Use default details (i.e. no number/cvv)
			this._oCardType.clearValue();
			this._oCardName.setValue(this._oAccountInfo.sContactName);
			this._oCardNumber.clearValue();
			this._oCardExpiry.setValue(new Date().$format('Y-m'));
			this._oCardCVV.clearValue();
			this._oDirectDebit.setValue(false);
		}
		
		this._oCardType.validate();
		this._cardTypeChange();
	}
});

//
// Static
//

Object.extend(Popup_Credit_Card_Payment, 
{
	ACCOUNT_EMAIL		: 'email',
	CARD_TYPE			: 'card_type',
	CARD_NAME			: 'card_name',
	CARD_NUMBER			: 'card_number',
	CARD_CVV			: 'card_cvv',
	CARD_EXPIRY			: 'card_expiry',
	CARD_AMOUNT			: 'card_amount',
	CARD_DISPLAY_ONLY	: 'display_only',
	CARD_DD_UPDATE		: 'dd_update',
	FIELDS				: {},
	_aCardTypes			: {},
	
	_getCreditCardTypeOptions	: function(fnCallback, oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			var fnResp	= Popup_Credit_Card_Payment._getCreditCardTypeOptions.curry(fnCallback);
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
				if (!Popup_Credit_Card_Payment._aCardTypes)
				{
					Popup_Credit_Card_Payment._aCardTypes	= {};
				}
				
				Popup_Credit_Card_Payment._aCardTypes[oCardType.id]	= oCardType;
				
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
	
	_getAccountPaymentInfo	: function(iAccountId, fnCallback, oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			var fnResp	= Popup_Credit_Card_Payment._getAccountPaymentInfo.curry(iAccountId, fnCallback);
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
	
	_getCardType	: function(iCardTypeId)
	{
		return Popup_Credit_Card_Payment._aCardTypes[iCardTypeId];
	},
	
	_validateCardName	: function(mValue)
	{
		return (mValue.toString().replace(/[^a-zA-Z]+/g, '') != '');
	},
	
	_validateExpiry	: function(mValue)
	{
		var oDate			= Date.$parseDate(mValue + '-01', 'Y-m-d');
		var iMonth 			= oDate.getMonth() + 1;
		var iYear			= oDate.getFullYear();
		var oNow 			= new Date();
		var iCurrentMonth 	= oNow.getMonth() + 1;
		var iCurrentYear 	= oNow.getFullYear();
		return iYear > iCurrentYear || (iYear == iCurrentYear && iMonth >= iCurrentMonth);
	}
});

Popup_Credit_Card_Payment.FIELDS	= {};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.ACCOUNT_EMAIL]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Email',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Reflex_Validation.email
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_TYPE]	=
{
	sType	: 'select',
	oConfig	: 
	{
		sLabel		: 'Credit Card Type',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnPopulate	: Popup_Credit_Card_Payment._getCreditCardTypeOptions
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_NAME]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Name on Card',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Popup_Credit_Card_Payment._validateCardName
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_NUMBER]	=
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
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_CVV]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'CVV',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_EXPIRY]	=
{
	sType	: 'combo-date',
	oConfig	: 
	{
		sLabel				: 'Expiry Date',
		iMinYear			: new Date().getFullYear(),
		iMaxYear			: new Date().getFullYear() + 10,
		iFormat				: Control_Field_Combo_Date.FORMAT_M_Y,
		mMandatory			: true, 
		mEditable			: true,
		mVisible			: true,
		fnValidate			: Popup_Credit_Card_Payment._validateExpiry,
		mSeparatorElement	: '/',
		sValidationReason	: 'The Expiry Date must be a date in the future.'
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_AMOUNT]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Amount to Pay',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_DD_UPDATE]	=
{
	sType	: 'checkbox',
	oConfig	: 
	{
		sLabel		: 'Set Up/Modify Direct Debit',
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.CARD_DISPLAY_ONLY]	=
{
	sType	: 'text',
	oConfig	: 
	{
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Credit_Card_Payment.FIELDS[Popup_Credit_Card_Payment.EXISTING_CARD_DETAILS]	=
{
	sType	: 'checkbox',
	oConfig	: 
	{
		sLabel		: 'Use existing Credit Card Payment details',
		mEditable	: true,
		mVisible	: true
	}
};
