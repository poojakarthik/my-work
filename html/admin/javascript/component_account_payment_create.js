
var Component_Account_Payment_Create = Class.create(
{
	initialize : function(iAccountId, iSaveMode, fnOnComplete)
	{
		this._iAccountId	= iAccountId;
		this._iSaveMode		= (iSaveMode ? iSaveMode : Component_Account_Payment_Create.SAVE_MODE_SAVE);
		this._fnOnComplete	= fnOnComplete;
		this._aControls 	= [];
		this._oElement 		= $T.div({class: 'component-account-payment-create'});

		Flex.Constant.loadConstantGroup(Component_Account_Payment_Create.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},

	// Public

	getElement : function()
	{
		return this._oElement;
	},

	save : function()
	{
		this._save();
	},

	// Protected

	_buildUI : function(oAccount)
	{
		if (!oAccount)
		{
			Flex.Account.getForId(this._iAccountId, this._buildUI.bind(this));
			return;
		}

		// Create controls
		var oPaymentTypeControl =	Control_Field.factory(
										'select',
										{
											sLabel		: 'Payment Type',
											mMandatory	: true,
											mEditable	: true,
											fnPopulate	: function (fnCallback) {
												Flex.Constant.getConstantGroupOptions('payment_type', function (aOptions) {
													fnCallback(aOptions.filter(function (oOption) {
														return (Component_Account_Payment_Create.PERMITTED_PAYMENT_TYPES.indexOf(Flex.Constant.arrConstantGroups.payment_type[oOption.value].Constant) > -1);
													}));
												});
											}
										}
									);
		oPaymentTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oPaymentTypeControl.addOnChangeCallback(this._paymentTypeChange.bind(this, oPaymentTypeControl));
		this._aControls.push(oPaymentTypeControl);
		this._oPaymentTypeControl = oPaymentTypeControl;

		var oAmountControl =	Control_Field.factory(
									'number',
									{
										sLabel			: 'Amount',
										mMandatory		: true,
										mEditable		: true,
										iDecimalPlaces	: 2,
										fnValidate		: Component_Account_Payment_Create._validateAmount
									}
								);
		oAmountControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oAmountControl.addOnChangeCallback(this._amountChange.bind(this));
		this._aControls.push(oAmountControl);
		this._oAmountControl = oAmountControl;

		var oTXNReferenceControl =	Control_Field.factory(
										'text',
										{
											sLabel		: 'Transaction Reference',
											mMandatory	: true,
											mEditable	: true,
											fnValidate	: Reflex_Validation.stringOfLength.curry(0, 128)
										}
									);
		oTXNReferenceControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._aControls.push(oTXNReferenceControl);
		this._oTXNReferenceControl = oTXNReferenceControl;

		var oCreditCardSurchargeControl = 	Control_Field.factory(
												'checkbox',
												{
													sLabel		: 'Charge Surcharge',
													mMandatory	: false,
													mEditable	: true
												}
											);
		oCreditCardSurchargeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oCreditCardSurchargeControl.addOnChangeCallback(this._updateCreditCardSummary.bind(this));
		this._aControls.push(oCreditCardSurchargeControl);
		this._oCreditCardSurchargeControl = oCreditCardSurchargeControl;

		var oCreditCardTypeControl = 	Control_Field.factory(
											'select',
											{
												sLabel		: 'Charge Card Type',
												mMandatory	: this._isPaymentTypeCreditCard.bind(this),
												mEditable	: true,
												fnPopulate	: Component_Account_Payment_Create._getCreditCardTypeOptions
											}
										);
		oCreditCardTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oCreditCardTypeControl.addOnChangeCallback(this._creditCardTypeChange.bind(this));
		this._aControls.push(oCreditCardTypeControl);
		this._oCreditCardTypeControl = oCreditCardTypeControl;

		var oCreditCardNumberControl = 	Control_Field.factory(
											'text',
											{
												sLabel		: 'Credit Card Number',
												mMandatory	: this._isPaymentTypeCreditCard.bind(this),
												mEditable	: true,
												fnValidate	: this._validateCreditCardNumber.bind(this)
											}
										);
		oCreditCardNumberControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._aControls.push(oCreditCardNumberControl);
		this._oCreditCardNumberControl = oCreditCardNumberControl;

		var oCurrentDate = new Date;
		oCurrentDate.setHours(0);
		oCurrentDate.setMinutes(0);
		oCurrentDate.setSeconds(0);
		oCurrentDate.setMilliseconds(0);

		var oEarliestPayableDate = (new Date(oCurrentDate.getTime())).shift(0 - Component_Account_Payment_Create.MAKE_PAYMENT_MAXIMUM_AGE_DAYS, Date.DATE_INTERVAL_DAY);
		//var oPaidDateControl = Control_Field.factory('combo-date', {
		var oPaidDateControl = Control_Field.factory('date_picker', {
			sLabel: 'Paid Date',
			mMandatory: true,
			mEditable: true,
			sDateFormat: 'Y-m-d',
			iYearStart: oEarliestPayableDate.getFullYear(),
			iYearEnd: oCurrentDate.getFullYear()
		});
		oPaidDateControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oPaidDateControl.setValue(oCurrentDate.$format('Y-m-d'));
		oPaidDateControl.setValidateFunction(function (sDate) {
			var oDate = Date.$parseDate(sDate, 'Y-m-d');
			if (oDate < oEarliestPayableDate) {
				throw 'Payments must be no older than ' + Component_Account_Payment_Create.MAKE_PAYMENT_MAXIMUM_AGE_DAYS + ' days (' + oEarliestPayableDate.$format('j M Y') + ')';
			}
			if (oDate > oCurrentDate) {
				throw 'Payments must be no newer than today';
			}
			return true;
		});
		this._aControls.push(oPaidDateControl);
		this._oPaidDateControl = oPaidDateControl;

		// Build container
		this._oElement.appendChild(
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th('Account'),
						$T.td(oAccount.Id + ': ' + oAccount.BusinessName)
					),
					$T.tr(
						$T.th('Payment Type'),
						$T.td(oPaymentTypeControl.getElement())
					),
					$T.tr(
						$T.th('Amount ($)'),
						$T.td(oAmountControl.getElement())
					),
					$T.tr(
						$T.th('Transaction Reference'),
						$T.td(oTXNReferenceControl.getElement())
					),
					$T.tr(
						$T.th('Paid Date'),
						$T.td(oPaidDateControl.getElement())
					),
					$T.tr({class: 'component-account-payment-create-creditcard-element'},
						$T.th('Charge Surcharge'),
						$T.td(oCreditCardSurchargeControl.getElement())
					),
					$T.tr({class: 'component-account-payment-create-creditcard-element'},
						$T.th('Credit Card Type'),
						$T.td(oCreditCardTypeControl.getElement())
					),
					$T.tr({class: 'component-account-payment-create-creditcard-element'},
						$T.th('Credit Card Number'),
						$T.td(oCreditCardNumberControl.getElement())
					),
					$T.tr(
						$T.th(),
						$T.td(
							$T.div({class: 'component-account-payment-create-creditcard-element'},
								$T.table({class: 'component-account-payment-create-creditcard-summary-table'},
									$T.tbody(
										$T.tr(
											$T.td('Payment Amount'),
											$T.td({class: 'component-account-payment-create-creditcard-amount'}),
											$T.td()
										),
										$T.tr(
											$T.td(
												$T.span({class: 'component-account-payment-create-creditcard-surcharge-percentage'}),
												$T.span({class: 'component-account-payment-create-creditcard-card-type'}),
												$T.span(' Surcharge')
											),
											$T.td({class: 'component-account-payment-create-creditcard-surcharge'}),
											$T.td('+')
										),
										$T.tr(
											$T.td('Total Payment Amount'),
											$T.td({class: 'component-account-payment-create-creditcard-total'}),
											$T.td()
										)
									)
								),
								$T.div({class: 'component-account-payment-create-creditcard-instructions'})
							)
						)
					)
				)
			)
		);

		this._oCreditCardSummaryAmount 				= this._oElement.select('.component-account-payment-create-creditcard-amount').first();
		this._oCreditCardSummarySurchargePercentage	= this._oElement.select('.component-account-payment-create-creditcard-surcharge-percentage').first();
		this._oCreditCardSummaryCardType 			= this._oElement.select('.component-account-payment-create-creditcard-card-type').first();
		this._oCreditCardSummarySurcharge 			= this._oElement.select('.component-account-payment-create-creditcard-surcharge').first();
		this._oCreditCardSummaryTotalAmount 		= this._oElement.select('.component-account-payment-create-creditcard-total').first();
		this._oCreditCardSummaryInstructions		= this._oElement.select('.component-account-payment-create-creditcard-instructions').first();
	},

	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aControls.length; i++)
			{
				try
				{
					this._aControls[i].validate(false);
					this._aControls[i].save(true);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}

			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Component_Account_Payment_Create._validationError(aErrors);
				return;
			}

			// Build the details object
			var iPaymentType		= parseInt(this._oPaymentTypeControl.getValue());
			var bCreditCardPayment	= iPaymentType == $CONSTANT.PAYMENT_TYPE_CREDIT_CARD;
			var iCreditCardType		= (bCreditCardPayment ? this._oCreditCardTypeControl.getValue() : null);
			var fSurcharge 			= (bCreditCardPayment ? CreditCardType.cardTypeForId(iCreditCardType).surcharge : null);

			var oDetails =
			{
				account_id				: this._iAccountId,
				payment_type_id			: iPaymentType,
				amount					: this._oAmountControl.getValue(),
				transaction_reference	: this._oTXNReferenceControl.getValue(),
				paid_date				: this._oPaidDateControl.getValue(),
				charge_surcharge		: (bCreditCardPayment ? this._oCreditCardSurchargeControl.getValue() : null),
				credit_card_type_id		: (bCreditCardPayment ? this._oCreditCardTypeControl.getValue() : null),
				credit_card_number		: (bCreditCardPayment ? this._oCreditCardNumberControl.getValue() : null),
				credit_card_surcharge	: (bCreditCardPayment ? fSurcharge : null)
			};

			if (this._iSaveMode == Component_Account_Payment_Create.SAVE_MODE_CALLBACK_WITH_DETAILS)
			{
				if (this._fnOnComplete)
				{
					this._fnOnComplete(oDetails);
				}
				return;
			}

			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();

			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Payment', 'createPayment');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;

		if (!oResponse.bSuccess)
		{
			Component_Account_Payment_Create._ajaxError(oResponse, 'Could not create the new Payment');
			return;
		}

		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iPaymentId);
		}
	},

	_isPaymentTypeCreditCard : function()
	{
		var iPaymentType = parseInt(this._oPaymentTypeControl.getElementValue());
		return (iPaymentType == $CONSTANT.PAYMENT_TYPE_CREDIT_CARD);
	},

	_validateCreditCardNumber : function(mValue)
	{
		return Reflex_Validation_Credit_Card.validateCardNumber(mValue, this._oCreditCardTypeControl.getElementValue());
	},

	_amountChange : function()
	{
		this._updateCreditCardSummary();
	},

	_creditCardTypeChange : function(oControl)
	{
		this._oCreditCardNumberControl.validate();
		this._updateCreditCardSummary();
	},

	_updateCreditCardSummary : function()
	{
		var oCardType 		= CreditCardType.cardTypeForId(this._oCreditCardTypeControl.getElementValue());
		var bAddSurchage	= this._oCreditCardSurchargeControl.getElementValue();
		var fAmount			= parseFloat(this._oAmountControl.getElementValue());
		fAmount 		 	= (isNaN(fAmount) ? 0 : fAmount);
		var fSurcharge		= (bAddSurchage && oCardType ? oCardType.surcharge : 0);
		var fAmountPiece	= fSurcharge * fAmount;
		var fTotal			= fAmount + fAmountPiece;

		this._oCreditCardSummaryAmount.innerHTML 				= fAmount.toFixed(2);
		this._oCreditCardSummarySurchargePercentage.innerHTML 	= (fSurcharge * 100) + '% ';
		this._oCreditCardSummaryCardType.innerHTML 				= (oCardType ? oCardType.name : '?');
		this._oCreditCardSummarySurcharge.innerHTML 			= new Number(fAmountPiece).toFixed(2);
		this._oCreditCardSummaryTotalAmount.innerHTML 			= new Number(fTotal).toFixed(2);
		this._oCreditCardSummaryInstructions.innerHTML			= 'The amount to be entered into the EFTPOS machine is $' + new Number(fTotal).toFixed(2);
	},

	_paymentTypeChange : function(oControl)
	{
		var iPaymentType = parseInt(oControl.getElementValue());
		if (iPaymentType == $CONSTANT.PAYMENT_TYPE_CREDIT_CARD)
		{
			this._oElement.addClassName('component-account-payment-create-show-credit-card');
			this._oCreditCardTypeControl.setValue(CreditCardType.types[0].id);
			this._updateCreditCardSummary();
		}
		else
		{
			this._oElement.removeClassName('component-account-payment-create-show-credit-card');
		}
	}
});

Object.extend(Component_Account_Payment_Create,
{
	REQUIRED_CONSTANT_GROUPS : ['payment_type'],
	PERMITTED_PAYMENT_TYPES: [
		'PAYMENT_TYPE_CASH',
		'PAYMENT_TYPE_CHEQUE',
		'PAYMENT_TYPE_BILLEXPRESS', // Centrepay
		'PAYMENT_TYPE_EFT',
		'PAYMENT_TYPE_DEBTCOLLECTION_CREDITCOLLECT',
		'PAYMENT_TYPE_BPAY'
	],

	SAVE_MODE_SAVE 					: 1,
	SAVE_MODE_CALLBACK_WITH_DETAILS	: 2,

	MAKE_PAYMENT_MAXIMUM_AGE_DAYS: 30,

	_ajaxError : function(oResponse, sMessage) {
		if (oResponse.aErrors) {
			// Validation errors
			Component_Account_Payment_Create._validationError(oResponse.aErrors);
		} else {
			// Exception
			jQuery.json.errorPopup(oResponse, sMessage);
		}
	},

	_validationError : function(aErrors)
	{
		var oErrorElement = $T.ul();
		for (var i = 0; i < aErrors.length; i++)
		{
			oErrorElement.appendChild($T.li(aErrors[i]));
		}

		Reflex_Popup.alert(
			$T.div({class: 'alert-validation-error'},
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	},

	_getCreditCardTypeOptions	: function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Component_Account_Payment_Create._getCreditCardTypeOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Credit_Card', 'getAllTypes');
			fnReq();
			return;
		}

		var aOptions = [];
		for (var iId in oResponse.aCreditCardTypes)
		{
			if (!Object.isUndefined(oResponse.aCreditCardTypes[iId].id))
			{
				// Add option
				var oCardType = oResponse.aCreditCardTypes[iId];
				aOptions.push(
					$T.option({value: oCardType.id},
						oCardType.name
					)
				);
			}
		}

		fnCallback(aOptions);
	},

	_validateAmount : function(mValue)
	{
		if (Reflex_Validation.Exception.float(mValue))
		{
			if (parseFloat(mValue) > 0)
			{
				return true;
			}
			throw 'Amount must be greater than zero';
		}
	}
});