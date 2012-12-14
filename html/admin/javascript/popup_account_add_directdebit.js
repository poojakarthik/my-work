
var Popup_Account_Add_DirectDebit	= Class.create(Reflex_Popup,
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
			Popup_Account_Add_DirectDebit._getAccountPaymentInfo(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		else if (!bConstantsLoaded)
		{
			// Load db consants
			Flex.Constant.loadConstantGroup(['payment_method', 'direct_debit_type', 'rebill_type'], this._buildUI.bind(this, oAccountInfo, true));
			return;
		}
		
		this._oAccountInfo	= oAccountInfo;
		
		var oBankAccountSection	= new Section(true);
		oBankAccountSection.setTitleText('Bank Account Details');
		oBankAccountSection.setContent(
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th({class: 'label'},
							'Bank Name :'
						),
						$T.td(
							this._getField(Popup_Account_Add_DirectDebit.FIELD_BANK_NAME).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label'},
							'BSB # :'
						),
						$T.td(
							this._getField(Popup_Account_Add_DirectDebit.FIELD_BSB).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label'},
							'Account # :'
						),
						$T.td(
							this._getField(Popup_Account_Add_DirectDebit.FIELD_ACCOUNT_NUMBER).getElement()
						)
					),
					$T.tr(
						$T.th({class: 'label'},
							'Account Name :'
						),
						$T.td(
							this._getField(Popup_Account_Add_DirectDebit.FIELD_ACCOUNT_NAME).getElement()
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
						this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE).getElement(),
						$T.span({class: 'popup-add-direct-debit-paymentlabel'},
							'Account Balance:'
						).observe('click', this._selectPaymentAmountRadio.bind(this, Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE)),
						$T.span({class: 'popup-add-direct-debit-paymentamountbalance'},
							$T.span({class: 'popup-add-direct-debit-currencysymbol'},
								'$'
							),
							this._oAccountInfo.fBalance.toFixed(2)
						)
					),
					$T.li(
						this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OVERDUE).getElement(),
						$T.span({class: 'popup-add-direct-debit-paymentlabel'},
							'Overdue Balance:'
						).observe('click', this._selectPaymentAmountRadio.bind(this, Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OVERDUE)),
						$T.span({class: 'popup-add-direct-debit-paymentamountoverdue'},
							$T.span({class: 'popup-add-direct-debit-currencysymbol'},
								'$'
							),
							this._oAccountInfo.fOverdueBalance.toFixed(2)
						)
					),
					$T.li(
						this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OTHER).getElement(),
						$T.span({class: 'popup-add-direct-debit-paymentlabel'},
							'Other:'
						).observe('click', this._selectPaymentAmountRadio.bind(this, Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OTHER)),
						$T.span({class: 'popup-add-direct-debit-currencysymbol'},
							'$'
						),
						this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT).getElement()
					)
				)
			)
		);
		
		// Extra work on amount field
		var oPaymentAmount	= this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT);
		oPaymentAmount.getElement().addClassName('popup-add-direct-debit-amounttopay');
		
		// Change callback for submit checkbox
		this._getField(Popup_Account_Add_DirectDebit.FIELD_SUBMIT_PAYMENT).addOnChangeCallback(this._submitPaymentChange.bind(this));
		
		// Payment radio buttons
		var fnPaymentTypeChange	= this._paymentAmountChange.bind(this);
		this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE).addOnChangeCallback(fnPaymentTypeChange.curry(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE));
		this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OVERDUE).addOnChangeCallback(fnPaymentTypeChange.curry(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OVERDUE));
		this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OTHER).addOnChangeCallback(fnPaymentTypeChange.curry(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OTHER));
		this._selectPaymentAmountRadio(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE);
		
		var oContent	=	$T.div({class: 'popup-add-direct-debit'},
								oBankAccountSection.getElement(),
								this._oPaymentSection.getElement(),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Account_Add_DirectDebit.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									).observe('click', this._save.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Account_Add_DirectDebit.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									).observe('click', this._cancel.bind(this))
								)
							);
		
		// Display Popup
		this.setTitle("Add Bank Account Details");
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
						this._getField(Popup_Account_Add_DirectDebit.FIELD_SUBMIT_PAYMENT).getElement(),
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
		var oSubmitPaymentCheckbox	= this._getField(Popup_Account_Add_DirectDebit.FIELD_SUBMIT_PAYMENT);
		var oPaymentAmount			= this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT);
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
			Popup_Account_Add_DirectDebit._showValidationErrorPopup(aErrors);
			return;
		}
		
		// Round the amount to 2 decimal places before showing the confirmation popup
		this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT]	=
			parseFloat(this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT]).toFixed(2);
		
		// Sanitise BSB
		this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_BSB]	= 
			this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_BSB].replace(/-/, '');
		
		if (this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_SUBMIT_PAYMENT])
		{
			var sBank		= this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_BANK_NAME];
			var sBSB		= this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_BSB];
			var sAccount	= this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_ACCOUNT_NUMBER];
			var sAccName	= this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_ACCOUNT_NAME];
			
			// Confirm payment
			Reflex_Popup.yesNoCancel(
				$T.div({class: 'popup-add-direct-debit-confirmcontent'},
					$T.p(
						$T.span('An amount of '),
						$T.span({class: 'popup-add-direct-debit-highlight'},
							'$' + this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT]
						),
						$T.span(' will be charged to the given Bank Account.')
					),
					$T.p(
						$T.div(
							$T.span('The Payment Method will be set to '),
							$T.span({class: 'popup-add-direct-debit-highlight'},
								'Direct Debit via Bank Transfer'
							), 
							$T.span(' using the given Bank Account:')
						),
						$T.ul({class: 'reset popup-add-direct-debit-preview'},
							$T.li(
								$T.span({class: 'popup-add-direct-debit-preview-label'},
									'Bank:'
								),
								$T.div({class: 'popup-add-direct-debit-preview-value'},
									$T.div(sBank),
									$T.div({class: 'popup-add-direct-debit-preview-value-small'},
										sBSB
									)
								)
							),
							$T.li(
								$T.span({class: 'popup-add-direct-debit-preview-label'},
									'Account:'
								),
								$T.div({class: 'popup-add-direct-debit-preview-value'},
									$T.div(sAccount),
									$T.div({class: 'popup-add-direct-debit-preview-value-small'},
										sAccName
									)
								)
							)
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
				'Do you want to use this Bank Account as the Payment Method for the Flex Account ' + this._iAccountId + ' (' + this._oAccountInfo.sBusinessName + ')?',
				{fnOnYes: this._doSave.bind(this, true)}
			);
		}
	},
	
	_doSave	: function(bChangePaymentMethod)
	{
		if (bChangePaymentMethod)
		{
			// Record that payment method is to be changed
			this._hFieldValues[Popup_Account_Add_DirectDebit.FIELD_SET_PAYMENT_METHOD]	= true;
		}
		
		// Create a Popup to show 'saving...' close it when save complete
		this._oLoading = new Reflex_Popup.Loading('Saving...');
		this._oLoading.display();
		
		var fnAddDirectDebit	= 	jQuery.json.jsonFunction(
										this._saveResponse.bind(this, bChangePaymentMethod), 
										this._ajaxError.bind(this), 
										'Account', 
										'addDirectDebit'
									);
		fnAddDirectDebit(this._iAccountId, this._hFieldValues);
	},
	
	_saveResponse : function(bChangePaymentMethod, oResponse) {
		this._oLoading.hide();
		delete this._oLoading;
		
		if (oResponse.Success) {
			this.hide();
			
			if (oResponse.oPaymentReceipt !== null) {
				// A payment has been submitted, ask who to email the receipt to
				JsAutoLoader.loadScript('popup_account_direct_debit_receipt_email', 
					function() {
						new Popup_Account_Direct_Debit_Receipt_Email(this._iAccountId, oResponse.oPaymentReceipt, this._receiptEmailSent.bind(this, bChangePaymentMethod, oResponse));
					}.bind(this),
					true
				);
			} else if (bChangePaymentMethod && this._fnOnPaymentMethodChange) {
				this._fnOnPaymentMethodChange(oResponse.oDirectDebit);
			} else if (this._fnOnSave) {
				this._fnOnSave(oResponse.oDirectDebit);
			}
		} else if (oResponse.sExceptionClass == 'JSON_Handler_Account_Exception') {
			// Special exception message (e.g. insufficient permission), must be shown to user
			Reflex_Popup.alert(oResponse.Message);
		} else if (oResponse.aValidationErrors) {
			// Validation errors
			Popup_Account_Add_DirectDebit._showValidationErrorPopup(oResponse.aValidationErrors);
		} else {
			// Any other kind of failure, show ajax error popup
			this._ajaxError(oResponse);
		}
	},
	
	_receiptEmailSent	: function(bChangePaymentMethod, oResponse)
	{
		if (bChangePaymentMethod && this._fnOnPaymentMethodChange)
		{
			this._fnOnPaymentMethodChange(oResponse.oDirectDebit);
		}
		else if (this._fnOnSave)
		{
			this._fnOnSave(oResponse.oDirectDebit);
		}
	},
	
	_ajaxError	: function(oResponse) {
		if (this._oLoading) {
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		if (oResponse.Success == false) {
			jQuery.json.errorPopup(oResponse);
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
		
		var oConfig	= Popup_Account_Add_DirectDebit.FIELDS[sFieldName];
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
		var oAmount	= this._getField(Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT);
		var oBalanceSpan	= this._oPaymentSection.getElement().select('.popup-add-direct-debit-paymentamountbalance').first();
		var oOverdueSpan	= this._oPaymentSection.getElement().select('.popup-add-direct-debit-paymentamountoverdue').first();
		var oOtherSymbol	= this._oPaymentSection.getElement().select('.popup-add-direct-debit-currencysymbol').last();
		oBalanceSpan.removeClassName('popup-add-direct-debit-paymentamountdisabled');
		oOverdueSpan.removeClassName('popup-add-direct-debit-paymentamountdisabled');
		oOtherSymbol.removeClassName('popup-add-direct-debit-paymentamountdisabled');
		
		switch (sField)
		{
			case Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE:
				oOverdueSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oOtherSymbol.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oAmount.disableInput();
				oAmount.setMandatory(false);
				break;
			case Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OVERDUE:
				oBalanceSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oOtherSymbol.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oAmount.disableInput();
				oAmount.setMandatory(false);
				break;
			case Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OTHER:
				oOverdueSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oBalanceSpan.addClassName('popup-add-direct-debit-paymentamountdisabled');
				oAmount.enableInput();
				oAmount.setMandatory(true);
				break;
		}
	},
	
	_selectPaymentCheckbox	: function()
	{
		var oSubmitPayment	= this._getField(Popup_Account_Add_DirectDebit.FIELD_SUBMIT_PAYMENT);
		oSubmitPayment.setValue(!oSubmitPayment.getValue());
		this._submitPaymentChange();
	}
});

//
// Static
//

Object.extend(Popup_Account_Add_DirectDebit, 
{
	CANCEL_IMAGE_SOURCE 	: '../admin/img/template/delete.png',
	SAVE_IMAGE_SOURCE 		: '../admin/img/template/tick.png',
	
	FIELD_BANK_NAME					: 'sBankName',
	FIELD_BSB						: 'sBSB',
	FIELD_ACCOUNT_NUMBER			: 'sAccountNumber',
	FIELD_ACCOUNT_NAME				: 'sAccountName',
	FIELD_SUBMIT_PAYMENT			: 'bSubmitPayment',
	FIELD_PAYMENT_AMOUNT			: 'sPaymentAmount',
	FIELD_PAYMENT_AMOUNT_BALANCE	: 'bPaymentAmountBalance',
	FIELD_PAYMENT_AMOUNT_OVERDUE	: 'bPaymentAmountOverdueBalance',
	FIELD_PAYMENT_AMOUNT_OTHER		: 'bPaymentAmountOther',
	FIELD_SET_PAYMENT_METHOD		: 'bSetPaymentMethod',
	
	FIELDS					: {},
	
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
			var fnResp	= Popup_Account_Add_DirectDebit._getAccountPaymentInfo.curry(iAccountId, fnCallback);
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
	
	_validatePaymentAmount : function(mValue)
	{
		if (Reflex_Validation.Exception.float(mValue))
		{
			if (parseInt(mValue) > 0)
			{
				return true;
			}
			throw 'Amount must be greater than zero';
		}
	}
});

//
//	More Static
//

Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_BANK_NAME]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Bank Name',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_BSB]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'BSB #',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Reflex_Validation.bsb
	}
};
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_ACCOUNT_NAME]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Account Name',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_ACCOUNT_NUMBER]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Account #',
		mMandatory	: true, 
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Reflex_Validation.bankAccountNumber
	}
};
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_SUBMIT_PAYMENT]	=
{
	sType	: 'checkbox',
	oConfig	: 
	{
		sLabel		: 'Submit Payment',
		mEditable	: true,
		mVisible	: true
	}
};
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT]	=
{
	sType	: 'text',
	oConfig	: 
	{
		sLabel		: 'Amount To Pay',
		mEditable	: true,
		mVisible	: true,
		fnValidate	: Popup_Account_Add_DirectDebit._validatePaymentAmount
	}
};
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_BALANCE]	=
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
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OVERDUE]	=
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
Popup_Account_Add_DirectDebit.FIELDS[Popup_Account_Add_DirectDebit.FIELD_PAYMENT_AMOUNT_OTHER]	=
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