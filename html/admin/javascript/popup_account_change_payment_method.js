

var Popup_Account_Change_Payment_Method	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		if (!Popup_Account_Change_Payment_Method.HAS_CONSTANTS)
		{
			return;
		}

		$super(48);

		this.iAccountId	= iAccountId;

		this.hMethods	= {};
		this.hMethods[$CONSTANT.PAYMENT_METHOD_ACCOUNT]			= {};
		this.hMethods[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT]	= {};
		this.hMethods[$CONSTANT.PAYMENT_METHOD_REBILL]			= {};

		var oConstantGroups	= Flex.Constant.arrConstantGroups;

		// Account sub types (none), but add an oCache reference so that the description will show
		this.hMethods[$CONSTANT.PAYMENT_METHOD_ACCOUNT][null]			= this._createMethodObject();
		this.hMethods[$CONSTANT.PAYMENT_METHOD_ACCOUNT][null].oCache	= {};

		// Direct debit sub types
		for (var iId in oConstantGroups.direct_debit_type)
		{
			this.hMethods[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][iId]	= this._createMethodObject();
		}

		// Rebill sub types
		for (iId in oConstantGroups.rebill_type)
		{
			this.hMethods[$CONSTANT.PAYMENT_METHOD_REBILL][iId]	= this._createMethodObject();
		}

		// Set the expiry check flag for credit card and motorpass
		this.hMethods[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD].fnCheckExpiry	= Popup_Account_Change_Payment_Method._checkCreditCardExpiry;
		this.hMethods[$CONSTANT.PAYMENT_METHOD_REBILL][$CONSTANT.REBILL_TYPE_MOTORPASS].fnCheckExpiry				= Popup_Account_Change_Payment_Method._checkMotorpassExpiry;

		this.iSelectedMethod			= null;
		this.iSelectedSubType			= null;
		this._bInitialLoadComplete		= false;
		this._bShowExpiryNotification	= false;

		this.oLoading	= new Reflex_Popup.Loading('Getting Payment Methods...');
		this.oLoading.display();
		this._buildUI();
	},

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the current payment and information on other methods
			this._getCurrentPaymentMethod	= 	jQuery.json.jsonFunction(
													this._buildUI.bind(this),
													this._buildUI.bind(this),
													'Account',
													'getCurrentPaymentMethod'
												);
			this._getCurrentPaymentMethod(this.iAccountId);
		}
		else if (oResponse.Success)
		{
			// Generate the payment method options and details elements
			this.oContent	= 	$T.div({class: 'payment-methods'},
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Payment Methods')
											)
										),
										$T.div({class: 'section-content'},
											$T.ul({class: 'reset horizontal'},
												$T.li({class: 'payment-method-billing-types'},
													//$T.div()
													$T.ul({class: 'reset'})
												),
												$T.li({class: 'payment-method-details'},
													$T.div(
														$T.div({class: 'payment-method-details-description'}),
														$T.div({class: 'payment-method-details-info'}),
														$T.button({class: 'icon-button'},
															$T.img({src: Popup_Account_Change_Payment_Method.EDIT_IMAGE_SOURCE, alt: '', title: 'Change'}),
															$T.span('Change')
														)
													)
												)
											)
										)
									),
									$T.div({class: 'payment-methods-buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Change_Payment_Method.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
											$T.span('Save')
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Change_Payment_Method.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										)
									)
								);

			// Set save & cancel event handlers
			var oSaveButton		= this.oContent.select('div.payment-methods-buttons > button.icon-button').first();
			oSaveButton.observe('click', this._save.bind(this));

			var oCancelButton	= this.oContent.select('div.payment-methods-buttons > button.icon-button').last();
			oCancelButton.observe('click', this.hide.bind(this));

			// Create the 'Change' button for the selected billing type & bind events
			this.oChangeButton	= this.oContent.select('li.payment-method-details > div > button.icon-button').first();
			this.oChangeButton.observe('click', this._changePaymentMethodForBillingType.bind(this));

			// Add a radio option for each billing type (if the payment method is available)
			if (oResponse.aPaymentMethods[$CONSTANT.PAYMENT_METHOD_ACCOUNT])
			{
				this._addBillingType($CONSTANT.PAYMENT_METHOD_ACCOUNT, null);
			}

			if (oResponse.aPaymentMethods[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT])
			{
				for (var iSubType in Popup_Account_Change_Payment_Method.DISPLAY_DETAILS[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT])
				{
					this._addBillingType($CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT, iSubType);
				}
			}

			if (oResponse.aPaymentMethods[$CONSTANT.PAYMENT_METHOD_REBILL])
			{
				for (var iSubType in Popup_Account_Change_Payment_Method.DISPLAY_DETAILS[$CONSTANT.PAYMENT_METHOD_REBILL])
				{
					this._addBillingType($CONSTANT.PAYMENT_METHOD_REBILL, iSubType);
				}
			}

			// Select the current payment method
			//this._selectBillingType(oResponse.iPaymentMethod, oResponse.iPaymentMethodSubType);
			this._updateCurrentMethod(oResponse.iPaymentMethod, oResponse.iPaymentMethodSubType, oResponse);

			// Display Popup
			this.setTitle("Change Payment Method");
			this.addCloseButton();
			this.setIcon(Popup_Account_Change_Payment_Method.PAYMENT_METHOD_IMAGE_SOURCE);
			this.setContent(this.oContent);
			this.display();

			if (this._bShowExpiryNotification)
			{
				this._showExpiryNotification(
					oResponse.iPaymentMethod,
					oResponse.iPaymentMethodSubType,
					oResponse.oPaymentMethod
				);
			}
		}
		else
		{
			// AJAX Error
			this._ajaxError(oResponse, true);
		}
	},

	_updateCurrentMethod	: function(iMethodToSelect, iSubTypeToSelect, oResponse)
	{
		if (oResponse.Success)
		{
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;

			var iMethod		= oResponse.iPaymentMethod;
			var iSubType	= oResponse.iPaymentMethodSubType;
			var oMethod		= oResponse.oPaymentMethod;
			this.aHasMethod	= oResponse.aHasPaymentMethod;

			// No payment method returned, if Invoice is the current
			if (oMethod)
			{
				// Check for method expiry
				if (this.hMethods[iMethod][iSubType].fnCheckExpiry)
				{
					if (oMethod.oDetails.card_card_expiry_date!=null && oMethod.oDetails.card_card_expiry_date!='0000-00-00')
					{
						this.hMethods[iMethod][iSubType].fnCheckExpiry(oMethod);
					}


					if (!this._bInitialLoadComplete && oMethod.bExpired)
					{
						this._bShowExpiryNotification	= true;
					}
				}

				// Update payment method cache
				this.hMethods[iMethod][iSubType].oCache	= oMethod;
			}
			else if (oResponse.iBillingDetail && this.hMethods[iMethod][iSubType])
			{
				// Check if the current payment method (which is invalid) is cached, if so remove the method
				var oCached	= null;

				for (var i in this.hMethods)
				{
					if (i == $CONSTANT.PAYMENT_METHOD_ACCOUNT)
					{
						continue;
					}

					for (var j in this.hMethods[i])
					{
						oCached	= this.hMethods[i][j].oCache;

						if (oCached && (!this.aHasMethod[i][j] || (this.aHasMethod[i][j].indexOf(oCached.Id) === null)))
						{
							this.hMethods[i][j].oCache	= null;
							iMethodToSelect				= $CONSTANT.PAYMENT_METHOD_ACCOUNT;
							iSubTypeToSelect			= null;
						}
					}
				}
			}

			// Select billing type if one is specified
			if (iMethodToSelect)
			{
				this._selectBillingType(iMethodToSelect, iSubTypeToSelect);
			}

			this._bInitialLoadComplete	= true;
		}
		else
		{
			// AJAX error
			this._ajaxError(oResponse);
		}
	},

	_addBillingType	: function(iMethod, iSubType)
	{
		var oDetails	= Popup_Account_Change_Payment_Method.DISPLAY_DETAILS[iMethod][iSubType];
		var oDom		= 	$T.li({class: 'billing-type'},
								$T.input({type: 'radio', name: 'billing-type-option', value: this._toRadioValue(iMethod, iSubType)}),
								$T.img({src: oDetails.sImage, alt: oDetails.sName, title: oDetails.sName}),
								$T.span(oDetails.sName)
							);
		// Events
		var oRadio	= oDom.select('input[type="radio"]').first();
		oRadio.observe('click', this._billingTypeSelected.bind(this, oRadio));
		oDom.observe('click', this._selectBillingType.bind(this, iMethod, iSubType));

		// Attach element
		var oSectionContent	= this.oContent.select('li.payment-method-billing-types > ul.reset').first();
		oSectionContent.appendChild(oDom);

		// Cache the radio
		this.hMethods[iMethod][iSubType].oRadio				= oRadio;
		this.hMethods[iMethod][iSubType].oSummaryElement	= oDom;
	},

	_billingTypeSelected	: function(oRadio)
	{
		var oValue		= this._fromRadioValue(oRadio.value);
		var iMethod 	= oValue.iMethod;
		var iSubType	= oValue.iSubType;

		// Clear the details
		this._clearDetails();

		if (this.hMethods[iMethod][iSubType].oCache &&
			(!this.hMethods[iMethod][iSubType].oCache.bExpired || !this._bInitialLoadComplete))
		{
			// Got payment method details cached for the billing type, Show the details
			this._updateDetails(iMethod, iSubType, this.hMethods[iMethod][iSubType].oCache);
		}
		else
		{
			// For the billing type (except invoice, it's always available) see if a 'new payment method' or
			// 'select payment method' popup is required
			if (this.aHasMethod[iMethod][iSubType])
			{
				// There are payment methods for the billing type. Show the select payment method popup
				this._showPaymentMethodChangePopup(iMethod, iSubType, $CONSTANT.PAYMENT_METHOD_ACCOUNT, null);
			}
			else
			{
				// Show 'add' payment method popup
				this._showAddPaymentMethodPopup(iMethod, iSubType);
			}
		}

		if (iMethod == $CONSTANT.PAYMENT_METHOD_ACCOUNT || iMethod == $CONSTANT.PAYMENT_METHOD_REBILL)
		{
			// Remove it, not needed for 'Invoice'
			this.oChangeButton.hide();
		}
		else
		{
			// Show the change button
			this.oChangeButton.show();
		}

		// De-select previously selected option
		if (this.iSelectedMethod)
		{
			this.hMethods[this.iSelectedMethod][this.iSelectedSubType].oSummaryElement.removeClassName('billing-type-selected');
		}

		// Select the new option
		this.hMethods[iMethod][iSubType].oSummaryElement.addClassName('billing-type-selected');

		// Record selected billing type
		this.iSelectedMethod	= iMethod;
		this.iSelectedSubType	= iSubType;
	},

	_selectBillingType	: function(iMethod, iSubType)
	{
		var oRadio	= this.hMethods[iMethod][iSubType].oRadio;

		if (oRadio)
		{
			oRadio.checked = true;
			this._billingTypeSelected(oRadio);
		}
	},

	_updateDetails	: function(iMethod, iSubType)
	{
		var oMethod	= this.hMethods[iMethod][iSubType].oCache;

		// Update the details, description
		var oDescDiv		= this.oContent.select('li.payment-method-details > div > div.payment-method-details-description').first();
		oDescDiv.innerHTML	= Popup_Account_Change_Payment_Method.DISPLAY_DETAILS[iMethod][iSubType].sDescription;

		// Update the details, info
		var oDom	= false;
		switch (iMethod)
		{
			case $CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT:
				//
				// DIRECT DEBIT
				//
				if (typeof oMethod.Id === 'undefined')
				{
					break;
				}

				switch (iSubType)
				{
					case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
						oDom	= 	$T.div(
										$T.div(
											$T.span({class: 'label dd'},
												'Account Name: '
											),
											$T.span({class: 'value'},
												oMethod.AccountName
											)
										),
										$T.div(
											$T.span({class: 'label dd'},
												'BSB #'
											),
											$T.span({class: 'value'},
												oMethod.BSB
											)
										),
										$T.div(
											$T.span({class: 'label dd'},
												'Account #'
											),
											$T.span({class: 'value'},
												oMethod.AccountNumber
											)
										),
										$T.div(
											$T.span({class: 'label dd'},
												'Bank Name: '
											),
											$T.span({class: 'value'},
												oMethod.BankName
											)
										),
										$T.div(
											$T.span({class: 'label dd'},
												'Added: '
											),
											$T.span({class: 'value'},
												Popup_Account_Change_Payment_Method._formatDate(oMethod.created_on)
											)
										)
									);
						break;
					case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
						oDom	= 	$T.div(
										$T.div(
											$T.span({class: 'label cc'},
												'Name: '
											),
											$T.span({class: 'value'},
												oMethod.Name
											)
										),
										$T.div(
											$T.span({class: 'label cc'},
												'Type: '
											),
											$T.span({class: 'value'},
												oMethod.card_type_name
											)
										),
										$T.div(
											$T.span({class: 'label cc'},
												'Number: '
											),
											$T.span({class: 'value'},
												oMethod.card_number
											)
										),
										$T.div(
											$T.span({class: 'label cc'},
												'CVV: '
											),
											$T.span({class: 'value'},
												oMethod.cvv
											)
										),
										$T.div(
											$T.span({class: 'label cc'},
												'Expires: '
											),
											$T.span({class: 'payment-method-' + (oMethod.bExpired ? 'expired' : 'valid') + ' value'},
												oMethod.expiry
											)
										),
										$T.div(
											$T.span({class: 'label cc'},
												'Added: '
											),
											$T.span({class: 'value'},
												Popup_Account_Change_Payment_Method._formatDate(oMethod.created_on)
											)
										)
									);
						break;
				}

				break;
			case $CONSTANT.PAYMENT_METHOD_REBILL:
				//
				// REBILL
				//
				if (typeof oMethod.id === 'undefined')
				{
					break;
				}

				switch (iSubType)
				{
					case $CONSTANT.REBILL_TYPE_MOTORPASS:
						var sAccountClass = 'value';
						if (oMethod.oDetails.account_account_number == null || oMethod.oDetails.account_account_number == 0)
						{
							oMethod.oDetails.account_account_number = 'Not supplied';
							sAccountClass += ' empty';

						}

						var sExpiryClass = 'value';
						if (oMethod.oDetails.card_card_expiry_date==null || oMethod.oDetails.card_card_expiry_date=='0000-00-00')
						{
							oMethod.oDetails.card_card_expiry_date = 'Not supplied';
							sExpiryClass += ' empty';
						}



						oDom	= 	$T.div(
										$T.div(
											$T.span({class: 'label motorpass'},
												'Account Number: '
											),
											$T.span({class: sAccountClass},
												oMethod.oDetails.account_account_number
											)
										),

										$T.div(
											$T.span({class: 'label motorpass'},
												'Card Expiry: '
											),
											$T.span({class: sExpiryClass},
												oMethod.oDetails.card_card_expiry_date
											)
										)
									);
					break;
				}

				break;

			default:
				// Account
				if (iMethod == $CONSTANT.PAYMENT_METHOD_ACCOUNT)
				{
					// Nothing to do...
				}
		}

		if (oDom)
		{
			var oInfoDiv	= this.oContent.select('li.payment-method-details > div > div.payment-method-details-info').first();
			oInfoDiv.appendChild(oDom);
		}
	},

	_clearDetails	: function()
	{
		// Clear the details, description
		var oDescDiv		= this.oContent.select('li.payment-method-details > div > div.payment-method-details-description').first();
		oDescDiv.innerHTML	= '';

		// Clear the details, info
		var oInfoDiv		= this.oContent.select('li.payment-method-details > div > div.payment-method-details-info').first();
		oInfoDiv.innerHTML	= '';
	},

	_save	: function()
	{
		if (this.iSelectedMethod !== null)
		{
			var oMethod	= this.hMethods[this.iSelectedMethod][this.iSelectedSubType].oCache;
			var iId		= null;

			if (oMethod)
			{
				if (oMethod.Id)
				{
					iId	= oMethod.Id;
				}
				else if (oMethod.id)
				{
					iId	= oMethod.id;
				}
			}
			else
			{
				// Somehow, nothing was selected... shouldn't happen
				Reflex_Popup.alert('Please select a Payment Method.');
				return;
			}

			// Ensure the method hasn't expired
			if (oMethod.bExpired)
			{
				Reflex_Popup.alert('The payment method you have chosen has expired, please choose another one.');
				return;
			}

			// Make ajax request
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();

			var fnSetPaymentMethod	= 	jQuery.json.jsonFunction(
											this._saveResponse.bind(this),
											this._ajaxError.bind(this),
											'Account',
											'setPaymentMethod'
										);
			fnSetPaymentMethod(this.iAccountId, this.iSelectedMethod, this.iSelectedSubType, iId);
		}
		else
		{
			Reflex_Popup.alert('Please select a payment method before saving.');
		}
	},

	_saveResponse	: function(oResponse)
	{
		if (oResponse.Success)
		{
			window.location	= window.location;
		}
		else
		{
			this._ajaxError(oResponse);
		}
	},

	_showPaymentMethodChangePopup	: function(iMethod, iSubType, iMethodToSelect, iSubTypeToSelect)
	{
		iMethodToSelect		= (typeof iMethodToSelect != 'undefined' ? iMethodToSelect : iMethod);
		iSubTypeToSelect	= (typeof iSubTypeToSelect != 'undefined' ? iSubTypeToSelect : iSubType);

		var fnOnSelect		= 	this._paymentMethodSelected.bind(this, iMethod, iSubType);
		var fnOnCancel		= 	this._paymentMethodSelectCancelled.bind(
									this,
									iMethod,
									iSubType,
									iMethodToSelect,
									iSubTypeToSelect
								);
		switch (iMethod)
		{
			case $CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT:
				// Show payment method select popup
				var fnShow	= function(iSubType, fnOnSelect, fnOnCancel)
				{
					// Create the popup, giving it payment method details as well as cancel and selection callbacks
					new Popup_Account_Select_Payment_Method(
						this.iAccountId,
						iSubType,
						(this.hMethods[iMethod][iSubType].oCache ? this.hMethods[iMethod][iSubType].oCache.Id : null),
						fnOnSelect,
						fnOnCancel
					);
				}

				// Load js file
				JsAutoLoader.loadScript(
					'javascript/popup_account_select_payment_method.js',
					fnShow.bind(this, iSubType, fnOnSelect, fnOnCancel)
				);

				break;
			case $CONSTANT.PAYMENT_METHOD_REBILL:
				// Show the rebill edit popup
				//this._showRebillPopup(iSubType, fnOnSelect, fnOnCancel);
				fnGetRebill = jQuery.json.jsonFunction(
						fnOnSelect.bind(this),
						this._ajaxError.bind(this, true),
						'Account',
						'getRebill'
					);
				fnGetRebill(this.iAccountId);
				break;
		}
	},

	_getRebillCallback : function (iMethod, iSubType, oRebill)
	{
		if (oRebill==null)
		{
			Reflex_Popup.alert('There is no Rebill via Motorpass option available for this account.', {fnClose: this._selectBillingType.bind(this, $CONSTANT.PAYMENT_METHOD_ACCOUNT, null)});
		}
		else
		{
			this._paymentMethodSelected(iMethod, iSubType,oRebill);
		}

	},

	_showAddPaymentMethodPopup	: function(iMethod, iSubType)
	{
		// Check payment method
		switch (iMethod)
		{
			case $CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT:
				// Direct debit, check if credit or bank transfer
				switch (iSubType)
				{
					case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
						// Add credit card popup
						var fnShowCC	= function(iMethod, iSubType, iMethodToSelect, iSubTypeToSelect)
						{
							new Popup_Account_Add_CreditCard(
								this.iAccountId,
								this._paymentMethodSelected.bind(this, iMethod, iSubType),
								this._paymentMethodSelectCancelled.bind(
									this,
									iMethod,
									iSubType,
									iMethodToSelect,
									iSubTypeToSelect
								)
							);
						}

						JsAutoLoader.loadScript(
							'javascript/popup_account_add_creditcard.js',
							fnShowCC.bind(this, iMethod, iSubType, $CONSTANT.PAYMENT_METHOD_ACCOUNT, null)
						);

						break;
					case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
						// Add bank account popup
						var fnShowDD	= function(iMethod, iSubType, iMethodToSelect, iSubTypeToSelect)
						{
							new Popup_Account_Add_DirectDebit(
								this.iAccountId,
								this._paymentMethodSelected.bind(this, iMethod, iSubType),
								this._paymentMethodSelectCancelled.bind(
									this,
									iMethod,
									iSubType,
									iMethodToSelect,
									iSubTypeToSelect
								)
							);
						}

						JsAutoLoader.loadScript(
							'javascript/popup_account_add_directdebit.js',
							fnShowDD.bind(this, iMethod, iSubType, $CONSTANT.PAYMENT_METHOD_ACCOUNT, null)
						);

						break;
				}

				break;

			case $CONSTANT.PAYMENT_METHOD_REBILL:
				// Rebill, Show the rebill edit popup
				Reflex_Popup.alert('There is no Rebill via Motorpass option available for this account.', {fnClose: this._selectBillingType.bind(this, $CONSTANT.PAYMENT_METHOD_ACCOUNT, null)});

				/*this._showRebillPopup(
					iSubType,
					this._paymentMethodSelected.bind(this, iMethod, iSubType),
					this._paymentMethodSelectCancelled.bind(
						this,
						iMethod,
						iSubType,
						$CONSTANT.PAYMENT_METHOD_ACCOUNT,
						null
					)
				);*/
				break;
		}
	},

	_paymentMethodSelected	: function(iMethod, iSubType, oMethod)
	{
		// Check for method expiry
		if (this.hMethods[iMethod][iSubType].fnCheckExpiry)
		{
			this.hMethods[iMethod][iSubType].fnCheckExpiry(oMethod);
		}

		// Cache the payment method
		this.hMethods[iMethod][iSubType].oCache	= oMethod;

		// Update aHasMethod
		this.aHasMethod[iMethod][iSubType]	= true;

		// Select the added/selected
		this._selectBillingType(iMethod, iSubType);
	},

	_paymentMethodSelectCancelled	: function(iMethod, iSubType, iMethodToSelect, iSubTypeToSelect)
	{
		if ((iMethod != iMethodToSelect) || (iSubType && iSubTypeToSelect))
		{
			this._refresh(iMethodToSelect, iSubTypeToSelect);
		}
		else
		{
			this._refresh(iMethod, iSubType);
		}
	},

	_changePaymentMethodForBillingType	: function()
	{
		// Check if there are any payment methods to change to, if not show add popup.
		if (this.aHasMethod[this.iSelectedMethod][this.iSelectedSubType])
		{
			// There are payment methods for the billing type. Show the select payment method popup
			var oMethod	= this.hMethods[this.iSelectedMethod][this.iSelectedSubType].oCache;

			if (oMethod && oMethod.bExpired)
			{
				// Fallback to account if the method has expired
				this._showPaymentMethodChangePopup(
					this.iSelectedMethod,
					this.iSelectedSubType,
					$CONSTANT.PAYMENT_METHOD_ACCOUNT,
					null
				);
			}
			else
			{
				// Fallback to itself if the method is valid, or is not the current
				this._showPaymentMethodChangePopup(this.iSelectedMethod, this.iSelectedSubType);
			}
		}
		else
		{
			// Show 'add' payment method popup
			this._showAddPaymentMethodPopup(this.iSelectedMethod, this.iSelectedSubType);
		}
	},

	_refresh	: function(iMethodToSelect, iSubTypeToSelect)
	{
		this.oLoading	= new Reflex_Popup.Loading('Please Wait...');
		this.oLoading.display();

		// Refresh the current of payment method situation
		this._refreshCurrentPaymentMethod	= 	jQuery.json.jsonFunction(
													this._updateCurrentMethod.bind(
														this,
														iMethodToSelect,
														iSubTypeToSelect
													),
													this._updateCurrentMethod.bind(
														this,
														iMethodToSelect,
														iSubTypeToSelect
													),
													'Account',
													'getCurrentPaymentMethod'
												);

		this._refreshCurrentPaymentMethod(this.iAccountId);
	},

	_showRebillPopup	: function(iSubType, fnOnSelect, fnOnCancel)
	{
		var fnLoad	= function()
		{
			new Popup_Account_Edit_Rebill(this.iAccountId, iSubType, fnOnSelect, fnOnCancel);
		};

		var fnConstantLoad	= function()
		{
			JsAutoLoader.loadScript('javascript/popup_account_edit_rebill.js', fnLoad.bind(this));
		};

		Flex.Constant.loadConstantGroup(
			['motorpass_business_structure', 'motorpass_card_type'],
			fnConstantLoad.bind(this)
		);
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

	_toRadioValue	: function(iMethod, iSubType)
	{
		return iMethod + ((iSubType !== null) ? ',' + iSubType : '');
	},

	_fromRadioValue	: function(sRadioValue)
	{
		var aSplit	= sRadioValue.split(',');
		return	{
			iMethod		: parseInt(aSplit[0]),
			iSubType	: (aSplit[1] ? parseInt(aSplit[1]) : null),
		};
	},

	_createMethodObject	: function(bCheckExpiry)
	{
		return	{
			oCache			: null,
			oRadio			: null,
			oSummaryElement	: null,
			fnCheckExpiry	: null
		};
	},

	_showExpiryNotification	: function(iMethod, iSubType, oMethod)
	{
		var sNoLabel	= null;
		var oMsg		= 	$T.div(
								$T.p('There is a problem with the current payment method:'),
								$T.p('The ' + hDisplay[iMethod][iSubType].sName + ' has expired (on ' + oMethod.expiry + ').')
							);
		switch (iMethod)
		{
			case $CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT:
				switch (iSubType)
				{
					case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
						sNoLabel	= 'Choose another Credit Card';
					break;
				}
				break;
			case $CONSTANT.PAYMENT_METHOD_REBILL:
				switch (iSubType)
				{
					case $CONSTANT.REBILL_TYPE_MOTORPASS:
						sNoLabel	= 'Update Motorpass Details';
					break;
				}
				break;
		}

		Reflex_Popup.yesNoCancel(
			oMsg,
			{
				fnOnYes				: null,
				fnOnNo				: 	this._showPaymentMethodChangePopup.bind(
											this,
											iMethod,
											iSubType,
											$CONSTANT.PAYMENT_METHOD_ACCOUNT,
											null
										),
				fnOnCancel			: this.hide.bind(this),
				bShowCancel			: true,
				sYesIconSource		: Popup_Account_Change_Payment_Method.PAYMENT_METHOD_IMAGE_SOURCE,
				sYesLabel			: 'Choose another Method',
				sNoIconSource		: hDisplay[iMethod][iSubType].sImage,
				sNoLabel			: sNoLabel,
				sCancelIconSource	: Popup_Account_Change_Payment_Method.CANCEL_IMAGE_SOURCE,
				sCancelLabel		: 'Cancel'
			}
		);
	}
});

// Check if $CONSTANT has billing_type constant group loaded, if not this class won't work
if (typeof Flex.Constant.arrConstantGroups.rebill_type == 'undefined' ||
	typeof Flex.Constant.arrConstantGroups.direct_debit_type == 'undefined' ||
	typeof Flex.Constant.arrConstantGroups.payment_method == 'undefined')
{
	Popup_Account_Change_Payment_Method.HAS_CONSTANTS	= false;
	throw ('Please load the "payment_method", "direct_debit_type" & "rebill_type" constant groups before including Popup_Account_Change_Payment_Method.js');
}
else
{
	Popup_Account_Change_Payment_Method.HAS_CONSTANTS	= true;
}

// Image paths
Popup_Account_Change_Payment_Method.CANCEL_IMAGE_SOURCE 		= '../admin/img/template/delete.png';
Popup_Account_Change_Payment_Method.SAVE_IMAGE_SOURCE 			= '../admin/img/template/tick.png';
Popup_Account_Change_Payment_Method.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Popup_Account_Change_Payment_Method.PAYMENT_METHOD_IMAGE_SOURCE	= "../admin/img/template/payment.png";

var hDisplay	= {};
hDisplay[$CONSTANT.PAYMENT_METHOD_ACCOUNT]	= {};

hDisplay[$CONSTANT.PAYMENT_METHOD_ACCOUNT][null] 				= {};
hDisplay[$CONSTANT.PAYMENT_METHOD_ACCOUNT][null].sName			= 'Account';
hDisplay[$CONSTANT.PAYMENT_METHOD_ACCOUNT][null].sDescription	= 'Money owed is to be paid upon recieving the invoice.';
hDisplay[$CONSTANT.PAYMENT_METHOD_ACCOUNT][null].sImage			= '../admin/img/template/money.png';

hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT]	= {};

hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT]				= {};
hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT].sName			= 'Direct Debit via Bank Transfer';
hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT].sDescription	= 'Money owed is automatically transferred from the following bank account.';
hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT].sImage		= '../admin/img/template/bank_transfer.png';

hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD]				= {};
hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD].sName			= 'Direct Debit via Credit Card';
hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD].sDescription	= 'Money owed is automatically charged to the following credit card.';
hDisplay[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT][$CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD].sImage			= '../admin/img/template/credit_card.png';

hDisplay[$CONSTANT.PAYMENT_METHOD_REBILL]	= {};

hDisplay[$CONSTANT.PAYMENT_METHOD_REBILL][$CONSTANT.REBILL_TYPE_MOTORPASS]				= {};
hDisplay[$CONSTANT.PAYMENT_METHOD_REBILL][$CONSTANT.REBILL_TYPE_MOTORPASS].sName		= 'Rebill via Motorpass';
hDisplay[$CONSTANT.PAYMENT_METHOD_REBILL][$CONSTANT.REBILL_TYPE_MOTORPASS].sDescription	= 'Invoice balance is charged to the Motorpass account.';
hDisplay[$CONSTANT.PAYMENT_METHOD_REBILL][$CONSTANT.REBILL_TYPE_MOTORPASS].sImage		= '../admin/img/template/rebill.png';

Popup_Account_Change_Payment_Method.DISPLAY_DETAILS	= hDisplay;

Popup_Account_Change_Payment_Method._checkCreditCardExpiry	= function(oCreditCard)
{
	iMonth 	= parseInt(oCreditCard.ExpMonth);
	iYear 	= parseInt(oCreditCard.ExpYear);
	Popup_Account_Change_Payment_Method._checkPaymentMethodExpiry(iMonth, iYear, oCreditCard);
};

Popup_Account_Change_Payment_Method._checkMotorpassExpiry	= function(oRebill)
{
	var aSplit	= oRebill.oDetails.card_card_expiry_date.split('-');
	iYear 		= parseInt(aSplit[0]);
	iMonth 		= parseInt(aSplit[1]);
	Popup_Account_Change_Payment_Method._checkPaymentMethodExpiry(iMonth, iYear, oRebill);
};

Popup_Account_Change_Payment_Method._checkPaymentMethodExpiry	= function(iMonth, iYear, oMethod)
{
	var d 			= new Date();
	var iCurrMonth 	= d.getMonth() + 1;
	var iCurrYear	= d.getFullYear();

	oMethod.expiry		= (iMonth < 10 ? '0' + iMonth : iMonth) + '/' + iYear;
	oMethod.bExpired	= !(iYear > iCurrYear || (iYear == iCurrYear && iMonth >= iCurrMonth));
}

Popup_Account_Change_Payment_Method._formatDate	= function(sDate)
{
	return Date.$format('j/n/Y', Date.parse(sDate.replace(/-/g, '/')) / 1000);
}

