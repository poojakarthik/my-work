/*
 * THIS CLASS IS DEPRECATED, REPLACED BY popup_account_change_payment_method.js 
 */
var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		throw "Popup_Account_Payment_Methods is deprecated use Popup_Account_Change_Payment_Method instead";
		return;
	
		if (!Popup_Account_Payment_Methods.HAS_CONSTANTS)
		{
			return;
		}
		
		$super(48);
		
		this.iAccountId	= iAccountId;
		
		// Stores objects representing payment methods, hashed against the billing type
		this.hCachedMethods									= {};
		this.hCachedMethods[$CONSTANT.BILLING_TYPE_ACCOUNT]	= {0: {Id: null}};
		
		// Stores radio buttons & summary divs, hashed by the billing type they represent
		this.hBillingTypeRadios											= {};
		this.hBillingTypeRadios[$CONSTANT.BILLING_TYPE_DIRECT_DEBIT]	= {};
		this.hBillingTypeRadios[$CONSTANT.BILLING_TYPE_CREDIT_CARD]		= {};
		this.hBillingTypeRadios[$CONSTANT.BILLING_TYPE_ACCOUNT]			= {};
		this.hBillingTypeRadios[$CONSTANT.BILLING_TYPE_REBILL]			= {};
		
		this.hBillingTypeDom										= {};
		this.hBillingTypeDom[$CONSTANT.BILLING_TYPE_DIRECT_DEBIT]	= {};
		this.hBillingTypeDom[$CONSTANT.BILLING_TYPE_CREDIT_CARD]	= {};
		this.hBillingTypeDom[$CONSTANT.BILLING_TYPE_ACCOUNT]		= {};
		this.hBillingTypeDom[$CONSTANT.BILLING_TYPE_REBILL]			= {};
		
		this.iSelectedBillingType		= null;
		this.iSelectedSubBillingType	= null;
		
		this.oLoading	= new Reflex_Popup.Loading('Please Wait');
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
			this._updateCurrentPaymentMethod(false, false, oResponse);
			
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
															$T.img({src: Popup_Account_Payment_Methods.EDIT_IMAGE_SOURCE, alt: '', title: 'Change'}),
															$T.span('Change')
														)
													)
												)
											)
										)
									),
									$T.div({class: 'payment-methods-buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Payment_Methods.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
											$T.span('Save')
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
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
				this._addBillingType($CONSTANT.BILLING_TYPE_ACCOUNT);
			}
			
			if (oResponse.aPaymentMethods[$CONSTANT.PAYMENT_METHOD_DIRECT_DEBIT])
			{
				this._addBillingType($CONSTANT.BILLING_TYPE_DIRECT_DEBIT);
				this._addBillingType($CONSTANT.BILLING_TYPE_CREDIT_CARD);
			}
			
			if (oResponse.aPaymentMethods[$CONSTANT.PAYMENT_METHOD_REBILL])
			{
				// Rebill has sub billing types as well
				for (var iRebillTypeId in Popup_Account_Payment_Methods.BILLING_TYPE_NAME[$CONSTANT.BILLING_TYPE_REBILL])
				{
					this._addBillingType($CONSTANT.BILLING_TYPE_REBILL, iRebillTypeId);
				}
			}
			
			// Select the current payment method
			this._selectBillingType(oResponse.iBillingType, oResponse.iSubBillingType);
			
			// Display Popup
			this.setTitle("Change Payment Method");
			this.addCloseButton();
			this.setIcon("../admin/img/template/payment.png");
			this.setContent(this.oContent);
			this.display();
		}
		else
		{
			// AJAX Error
			this._ajaxError(oResponse, true);
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
	
	_addBillingType	: function(iBillingType, iSubBillingType)
	{
		// Default sub billing type is 0
		iSubBillingType	= (iSubBillingType ? iSubBillingType : 0);
		
		var sName	= Popup_Account_Payment_Methods.BILLING_TYPE_NAME[iBillingType][iSubBillingType];
		
		// Special naming convention for rebill
		if (iBillingType == $CONSTANT.BILLING_TYPE_REBILL)
		{
			sName	= 'Rebill via ' + sName; 
		}
		
		var oDom	= 	$T.li({class: 'billing-type'},
							$T.input({type: 'radio', name: 'billing-type-option', value: iBillingType + ',' + iSubBillingType}),
							$T.img({src: Popup_Account_Payment_Methods.BILLING_TYPE_IMAGE[iBillingType], alt: sName, title: sName}),
							$T.span(sName)
						);
		// Events
		var oRadio	= oDom.select('input[type="radio"]').first();
		oRadio.observe('click', this._billingTypeSelected.bind(this, oRadio));
		oDom.observe('click', this._selectBillingType.bind(this, iBillingType, iSubBillingType));
		
		// Attach element
		var oSectionContent	= this.oContent.select('li.payment-method-billing-types > ul.reset').first();
		oSectionContent.appendChild(oDom);
		
		// Cache the radio
		this.hBillingTypeRadios[iBillingType][iSubBillingType]	= oRadio;
		this.hBillingTypeDom[iBillingType][iSubBillingType]		= oDom;
	},
	
	_billingTypeSelected	: function(oRadio)
	{
		var aSplit			= oRadio.value.split(',');
		var iBillingType 	= parseInt(aSplit[0]);
		var iSubBillingType	= parseInt(aSplit[1]);
		
		// Clear the details
		this._clearDetails();
		
		// Check if it has expired, applies to credit cards only
		var bExpired	= false;
		
		if ((iBillingType == $CONSTANT.BILLING_TYPE_CREDIT_CARD) && 
			this.hCachedMethods[iBillingType] && 
			this.hCachedMethods[iBillingType][iSubBillingType] && 
			this.hCachedMethods[iBillingType][iSubBillingType].bExpired)
		{
			bExpired	= true;
		}
		
		if (this.hCachedMethods[iBillingType] && this.hCachedMethods[iBillingType][iSubBillingType] && !bExpired)
		{
			// Got payment method details cached for the billing type, Show the details
			this._updateDetails(iBillingType, iSubBillingType, this.hCachedMethods[iBillingType][iSubBillingType]);
		}
		else 
		{
			// For the billing type (except invoice, it's always available) see if a 
			// 'new payment method' or 'select payment method' popup is required
			var	bHasCreditCard	= (iBillingType == $CONSTANT.BILLING_TYPE_CREDIT_CARD) && this.bHasCreditCard;
			var	bHasBankAccount	= (iBillingType == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT) && this.bHasBankAccount;
			var bHasRebill		= (iBillingType == $CONSTANT.BILLING_TYPE_REBILL) && this.aHasRebill[iSubBillingType];
			
			if (bHasCreditCard || bHasBankAccount || bHasRebill)
			{
				// There are payment methods for the billing type. Show the select payment method popup
				this._showPaymentMethodChangePopup(iBillingType, iSubBillingType, $CONSTANT.BILLING_TYPE_ACCOUNT, 0);
			}
			else
			{
				// Show 'add' payment method popup
				this._showAddPaymentMethodPopup(iBillingType, iSubBillingType);
			}
		}
		
		if (iBillingType == $CONSTANT.BILLING_TYPE_ACCOUNT)
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
		if (this.iSelectedBillingType)
		{
			this.hBillingTypeDom[this.iSelectedBillingType][this.iSelectedSubBillingType].removeClassName('billing-type-selected');
		}
		
		// Select the new option
		this.hBillingTypeDom[iBillingType][iSubBillingType].addClassName('billing-type-selected');
		
		// Record selected billing type
		this.iSelectedBillingType		= iBillingType;
		this.iSelectedSubBillingType	= iSubBillingType;
	},
	
	_selectBillingType	: function(iBillingType, iSubBillingType)
	{
		var oRadio	= this.hBillingTypeRadios[iBillingType][iSubBillingType];
		
		if (oRadio)
		{
			oRadio.checked = true;
			this._billingTypeSelected(oRadio);
		}
	},
	
	_updateDetails	: function(iBillingType, iSubBillingType, oPaymentMethod)
	{
		// Update the details, description
		var oDescDiv		= this.oContent.select('li.payment-method-details > div > div.payment-method-details-description').first();
		oDescDiv.innerHTML	= Popup_Account_Payment_Methods.BILLING_TYPE_DESCRIPTION[iBillingType][iSubBillingType];
		
		// Update the details, info
		var oInfoDiv	= this.oContent.select('li.payment-method-details > div > div.payment-method-details-info').first();
		var oDom		= false;
		
		switch (iBillingType)
		{
			case $CONSTANT.BILLING_TYPE_DIRECT_DEBIT:
				if (typeof oPaymentMethod.Id === 'undefined')
				{
					break;
				}
				
				oDom	= 	$T.div(
								$T.div(
									$T.span({class: 'label dd'},
										'Account Name: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.AccountName
									)
								),
								$T.div(
									$T.span({class: 'label dd'},
										'BSB #'
									),
									$T.span({class: 'value'},
										oPaymentMethod.BSB
									)
								),
								$T.div(
									$T.span({class: 'label dd'},
										'Account #'
									),
									$T.span({class: 'value'},
										oPaymentMethod.AccountNumber
									)
								),
								$T.div(
									$T.span({class: 'label dd'},
										'Bank Name: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.BankName
									)
								),
								$T.div(
									$T.span({class: 'label dd'},
										'Added: '
									),
									$T.span({class: 'value'},
										Popup_Account_Payment_Methods._formatDate(oPaymentMethod.created_on)
									)
								)
							);
				break;
			case $CONSTANT.BILLING_TYPE_CREDIT_CARD:
				if (typeof oPaymentMethod.Id === 'undefined')
				{
					break;
				}
				
				oDom	= 	$T.div(
								$T.div(
									$T.span({class: 'label cc'},
										'Name: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.Name
									)
								),
								$T.div(
									$T.span({class: 'label cc'},
										'Type: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.card_type_name
									)
								),
								$T.div(
									$T.span({class: 'label cc'},
										'Number: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.card_number
									)
								),
								$T.div(
									$T.span({class: 'label cc'},
										'CVV: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.cvv
									)
								),
								$T.div(
									$T.span({class: 'label cc'},
										'Expires: '
									),
									$T.span({class: 'payment-method-credit-card-' + (oPaymentMethod.bExpired ? 'expired' : 'valid') + ' value'},
										oPaymentMethod.expiry
									)
								),
								$T.div(
									$T.span({class: 'label cc'},
										'Added: '
									),
									$T.span({class: 'value'},
										Popup_Account_Payment_Methods._formatDate(oPaymentMethod.created_on)
									)
								)
							);
				break;
			case $CONSTANT.BILLING_TYPE_ACCOUNT:
				// Do nothing...
				break;
			case $CONSTANT.BILLING_TYPE_REBILL:
				// ...
				break;
		}
		
		if (oDom)
		{
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
		if (this.iSelectedBillingType !== null)
		{
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			var fnSetPaymentMethod	= 	jQuery.json.jsonFunction(
											this._saveResponse.bind(this), 
											this._ajaxError.bind(this), 
											'Account', 
											'setPaymentMethod'
										);
			var oPaymentMethod		= this.hCachedMethods[this.iSelectedBillingType][this.iSelectedSubBillingType];
			fnSetPaymentMethod(
				this.iAccountId, 
				this.iSelectedBillingType, 
				(oPaymentMethod.Id ? oPaymentMethod.Id : oPaymentMethod.id)
			);
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
	
	_showPaymentMethodChangePopup	: function(iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
	{
		iBillingTypeToSelect	= (typeof iBillingTypeToSelect != 'undefined' ? iBillingTypeToSelect : iBillingType);
		iSubBillingTypeToSelect	= (typeof iSubBillingTypeToSelect != 'undefined' ? iSubBillingTypeToSelect : iSubBillingType);
		
		switch (iBillingType)
		{
			case $CONSTANT.BILLING_TYPE_CREDIT_CARD:
			case $CONSTANT.BILLING_TYPE_DIRECT_DEBIT:
				// Show payment method select popup
				var fnShow	= function(iBillingType, iBillingTypeToSelect)
				{
					// Create the popup, giving it payment method details as well as cancel and selection callbacks
					new Popup_Account_Select_Payment_Method(
						this.iAccountId, 
						iBillingType, 
						(this.hCachedMethods[iBillingType] ? this.hCachedMethods[iBillingType][iSubBillingType].Id : null),
						this._paymentMethodSelected.bind(this, iBillingType, iSubBillingType),
						this._paymentMethodSelectCancelled.bind(this, iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
					);
				}
				
				// Load js file
				JsAutoLoader.loadScript(
					'javascript/popup_account_select_payment_method.js', 
					fnShow.bind(this, iBillingType, iBillingTypeToSelect)
				);
				
				break;
			case $CONSTANT.BILLING_TYPE_REBILL:
				// Show the rebill edit popup
				this._showRebillPopup(
					iSubBillingType,
					this._paymentMethodSelected.bind(this, iBillingType, iSubBillingType),
					this._paymentMethodSelectCancelled.bind(this, iBillingType, iSubBillingType, $CONSTANT.BILLING_TYPE_ACCOUNT, 0)
				);
				break;
		}
	},
	
	_paymentMethodSelected	: function(iBillingType, iSubBillingType, oPaymentMethod)
	{
		if ((iBillingType == $CONSTANT.BILLING_TYPE_CREDIT_CARD) && (typeof oPaymentMethod.expiry === 'undefined'))
		{
			// Check the credit cards expiry
			Popup_Account_Payment_Methods._checkCreditCardExpiry(oPaymentMethod);
		}
	
		// Cache the payment method
		this.hCachedMethods[iBillingType]					= {};
		this.hCachedMethods[iBillingType][iSubBillingType]	= oPaymentMethod;
		
		// Update bHas{Billing Type}
		switch (iBillingType)
		{
			case $CONSTANT.BILLING_TYPE_CREDIT_CARD:
				this.bHasCreditCard		= true;
				break;
				
			case $CONSTANT.BILLING_TYPE_DIRECT_DEBIT:
				this.bHasBankAccount	= true;
				break;
				
			case $CONSTANT.BILLING_TYPE_REBILL:
				this.aHasRebill[iSubBillingType]	= true;
				break;
		}
		
		this._selectBillingType(iBillingType, iSubBillingType);
	},
	
	_paymentMethodSelectCancelled	: function(iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
	{
		if ((iBillingType != iBillingTypeToSelect) || (iSubBillingType && iSubBillingTypeToSelect))
		{
			this._refresh(iBillingTypeToSelect, iSubBillingTypeToSelect);
		}
		else
		{
			this._refresh(iBillingType, iSubBillingType);
		}
	},
	
	_changePaymentMethodForBillingType	: function()
	{
		// Check if there are any payment methods to change to, if not show add popup.
		var	bHasCreditCard	= (this.iSelectedBillingType == $CONSTANT.BILLING_TYPE_CREDIT_CARD) && this.bHasCreditCard;
		var	bHasBankAccount	= (this.iSelectedBillingType == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT) && this.bHasBankAccount;
		var bHasRebill		= (this.iSelectedBillingType == $CONSTANT.BILLING_TYPE_REBILL) && this.aHasRebill[this.iSelectedSubBillingType];
		
		if (bHasCreditCard || bHasBankAccount || bHasRebill)
		{
			// There are payment methods for the billing type. Show the select payment method popup
			this._showPaymentMethodChangePopup(this.iSelectedBillingType, this.iSelectedSubBillingType);
		}
		else
		{
			// Show 'add' payment method popup
			this._showAddPaymentMethodPopup(this.iSelectedBillingType, this.iSelectedSubBillingType);
		}
	},
	
	_showAddPaymentMethodPopup	: function(iBillingType, iSubBillingType)
	{
		switch (iBillingType)
		{
			case $CONSTANT.BILLING_TYPE_CREDIT_CARD:
				// Add credit card popup
				var fnShowCC	= function(iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
				{
					new Popup_Account_Add_CreditCard(
						this.iAccountId, 
						this._paymentMethodSelected.bind(this, iBillingType, iSubBillingType),
						this._paymentMethodSelectCancelled.bind(this, iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
					);
				}
				
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_creditcard.js', 
					fnShowCC.bind(this, iBillingType, iSubBillingType, $CONSTANT.BILLING_TYPE_ACCOUNT, 0)
				);
				break;
			case $CONSTANT.BILLING_TYPE_DIRECT_DEBIT:
				// Add bank account popup
				var fnShowDD	= function(iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
				{
					new Popup_Account_Add_DirectDebit(
						this.iAccountId, 
						this._paymentMethodSelected.bind(this, iBillingType, iSubBillingType),
						this._paymentMethodSelectCancelled.bind(this, iBillingType, iSubBillingType, iBillingTypeToSelect, iSubBillingTypeToSelect)
					);
				}
				
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_directdebit.js', 
					fnShowDD.bind(this, iBillingType, iSubBillingType, $CONSTANT.BILLING_TYPE_ACCOUNT, 0)
				);
				break;
			case $CONSTANT.BILLING_TYPE_REBILL:
				// Show the rebill edit popup
				this._showRebillPopup(
					iSubBillingType,
					this._paymentMethodSelected.bind(this, iBillingType, iSubBillingType),
					this._paymentMethodSelectCancelled.bind(this, iBillingType, iSubBillingType, $CONSTANT.BILLING_TYPE_ACCOUNT, 0)
				);
				break;
		}
	},
	
	_refresh	: function(iBillingTypeToSelect, iSubBillingTypeToSelect)
	{
		// Refresh the current of payment method situation
		this.oLoading	= new Reflex_Popup.Loading('Please Wait...');
		this.oLoading.display();
		this._refreshCurrentPaymentMethod	= 	jQuery.json.jsonFunction(
													this._updateCurrentPaymentMethod.bind(this, iBillingTypeToSelect, iSubBillingTypeToSelect), 
													this._updateCurrentPaymentMethod.bind(this, iBillingTypeToSelect, iSubBillingTypeToSelect), 
													'Account', 
													'getCurrentPaymentMethod'
												);
		this._refreshCurrentPaymentMethod(this.iAccountId);
	},
	
	_updateCurrentPaymentMethod	: function(iBillingTypeToSelect, iSubBillingTypeToSelect, oResponse)
	{
		if (oResponse.Success)
		{
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;
			
			// Update flags
			this.bHasCreditCard			= oResponse.bHasCreditCard;
			this.bHasBankAccount		= oResponse.bHasBankAccount;
			this.aHasRebill				= oResponse.aHasRebill;
			this.hAvailableBillingTypes	= oResponse.aBillingTypes;
			
			// Get sub billing type (only for rebill currently)
			oResponse.iSubBillingType	= 0;
			if (oResponse.iBillingType == $CONSTANT.BILLING_TYPE_REBILL)
			{
				oResponse.iSubBillingType	= oResponse.oPaymentMethod.rebill_type_id;
			}
			
			// No payment method returned, if Invoice is the current
			if (oResponse.oPaymentMethod)
			{
				if (oResponse.iBillingType == $CONSTANT.BILLING_TYPE_CREDIT_CARD)
				{
					// Check the expiry date on the credit card
					Popup_Account_Payment_Methods._checkCreditCardExpiry(oResponse.oPaymentMethod);
				}
				
				// Update payment method cache
				this.hCachedMethods[oResponse.iBillingType]								= {};
				this.hCachedMethods[oResponse.iBillingType][oResponse.iSubBillingType]	= oResponse.oPaymentMethod; 	 
			}
			else if (oResponse.iBillingDetail)
			{
				if (this.hCachedMethods[oResponse.iBillingType] && 
					this.hCachedMethods[oResponse.iBillingType][oResponse.iSubBillingType] && 
					(this.hCachedMethods[oResponse.iBillingType][oResponse.iSubBillingType].Id == oResponse.iBillingDetail))
				{
					// There is no payment method but there is still billing type & detail recorded, remove the invalid payment method from the hash
					this.hCachedMethods[oResponse.iBillingType]	= null;
				}
			}
			
			// Select billing type if one is specified
			if (iBillingTypeToSelect)
			{
				this._selectBillingType(iBillingTypeToSelect, iSubBillingTypeToSelect);
			}
		}
		else
		{
			// AJAX error
			this._ajaxError(oResponse);
		}
	},
	
	_showRebillPopup	: function(iSubBillingType, fnOnSelect, fnOnCancel)
	{
		var fnLoad	= function()
		{
			new Popup_Account_Edit_Rebill(this.iAccountId, iSubBillingType, fnOnSelect, fnOnCancel);
		};
		
		JsAutoLoader.loadScript('javascript/popup_account_edit_rebill.js', fnLoad.bind(this));
	}
});

// Check if $CONSTANT has billing_type constant group loaded, if not this class won't work
if (typeof Flex.Constant.arrConstantGroups.billing_type == 'undefined' || 
	typeof Flex.Constant.arrConstantGroups.rebill_type == 'undefined' || 
	typeof Flex.Constant.arrConstantGroups.payment_method == 'undefined')
{
	Popup_Account_Payment_Methods.HAS_CONSTANTS	= false;
	throw ('Please load the "billing_type" & "rebill_type" constant groups before including popup_account_payment_methods.js');
}
else
{
	Popup_Account_Payment_Methods.HAS_CONSTANTS	= true;
}

// Image paths
Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Payment_Methods.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
Popup_Account_Payment_Methods.EDIT_IMAGE_SOURCE		= '../admin/img/template/pencil.png';

// Billing type info
var hName										= {};
hName[$CONSTANT.BILLING_TYPE_DIRECT_DEBIT]		= {0: 'Direct Debit via Bank Transfer'};
hName[$CONSTANT.BILLING_TYPE_CREDIT_CARD]		= {0: 'Direct Debit via Credit Card'};
hName[$CONSTANT.BILLING_TYPE_ACCOUNT]			= {0: 'Account'};
hName[$CONSTANT.BILLING_TYPE_REBILL]			= {};

Popup_Account_Payment_Methods.BILLING_TYPE_NAME	= hName;

var hDescription										= {};
hDescription[$CONSTANT.BILLING_TYPE_DIRECT_DEBIT]		= {0: 'Money owed is automatically transferred from the following bank account.'};
hDescription[$CONSTANT.BILLING_TYPE_CREDIT_CARD]		= {0: 'Money owed is automatically charged to the following credit card.'};
hDescription[$CONSTANT.BILLING_TYPE_ACCOUNT]			= {0: 'Money owed is to be paid upon recieving the invoice.'};
hDescription[$CONSTANT.BILLING_TYPE_REBILL]				= {};

Popup_Account_Payment_Methods.BILLING_TYPE_DESCRIPTION	= hDescription;

// Add rebill_type constant values
for (var iId in Flex.Constant.arrConstantGroups.rebill_type)
{
	hName[$CONSTANT.BILLING_TYPE_REBILL][iId]			= Flex.Constant.arrConstantGroups.rebill_type[iId].Name;
	hDescription[$CONSTANT.BILLING_TYPE_REBILL][iId]	= Flex.Constant.arrConstantGroups.rebill_type[iId].Description;
}

var hImage											= {};
hImage[$CONSTANT.BILLING_TYPE_DIRECT_DEBIT]			= '../admin/img/template/bank_transfer.png';
hImage[$CONSTANT.BILLING_TYPE_CREDIT_CARD]			= '../admin/img/template/credit_card.png';
hImage[$CONSTANT.BILLING_TYPE_ACCOUNT]				= '../admin/img/template/money.png';
hImage[$CONSTANT.BILLING_TYPE_REBILL]				= '../admin/img/template/rebill.png';
Popup_Account_Payment_Methods.BILLING_TYPE_IMAGE	= hImage;

Popup_Account_Payment_Methods._checkCreditCardExpiry	= function(oCreditCard)
{
	month 	= parseInt(oCreditCard.ExpMonth);
	year 	= parseInt(oCreditCard.ExpYear);
	
	var d 			= new Date();
	var curr_month 	= d.getMonth() + 1;
	var curr_year	= d.getFullYear();
	
	oCreditCard.expiry		= (month < 10 ? '0' + month : month) + '/' + year;
	oCreditCard.bExpired	= !(year > curr_year || (year == curr_year && month >= curr_month));
};

Popup_Account_Payment_Methods._formatDate	= function(sDate)
{
	return Reflex_Date_Format.format('j/n/Y', Date.parse(sDate.replace(/-/g, '/')) / 1000);
}

