
var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(60);
		
		this.iAccountId					= iAccountId;
		this.oHighlightedPaymentMethod	= null;
		this.oLoading					= new Reflex_Popup.Loading('Please Wait');
		this.oLoading.display();
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// Make AJAX request for the payment methods
			this._getPaymentMethods	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Account', 'getPaymentMethods');
			this._getPaymentMethods(this.iAccountId);
			return;
		}
		else 
		{
			// Got a response, hide loading and check for success
			this.oLoading.hide();
			delete this.oLoading;
			
			if (oResponse.Success)
			{
				// Display payment methods
				var oContent	=	$T.div({class: 'payment-methods reflex-popup-content'},
										$T.table({class: 'form-layout reflex'},
											$T.caption(
												$T.div({class: 'caption_bar', id: 'caption_bar'},
													$T.div({class: 'caption_title', id: 'caption_title'},
														'Payment Methods'
													)
												)
											),
											$T.thead(
												$T.tr(
													$T.td(
														$T.div({class: 'payment-method-item'},
															$T.input({type: 'radio', name: 'account-payment-method', class: 'account-payment-method-invoice', value: Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE}),
															'Invoice'
														)
													),
													$T.td('Credit Cards'),
													$T.td('Bank Accounts')
												)
											),
											$T.tbody(
												$T.tr(
													$T.td({class: 'account-payment-methods-col-other'},
														$T.ul({class: 'horizontal reset account-payment-method'})
													),
													$T.td({class: 'account-payment-methods-col-credit-cards'},
															$T.ul({class: 'horizontal reset account-payment-method'})
													),
													$T.td({class: 'account-payment-methods-col-bank-accounts'},
														$T.ul({class: 'horizontal reset account-payment-method'})
													)		
												)
											),
											$T.tfoot()
										),
										$T.div({class: 'account-payment-methods-buttons'},
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
				
				// Add Bank Accounts to popup
				var oColBankAccounts	= oContent.select('.account-payment-methods-col-bank-accounts > ul').first();
				var oBankAccounts		= jQuery.json.arrayAsObject(oResponse.aPaymentMethods.direct_debits);
				var oBankAccount		= null;
				var oRadioAttributes	= null;
				var sValue				= null;
				
				for (var i in oBankAccounts)
				{
					oBankAccount	= oBankAccounts[i];
					sValue			= Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT + '_' + oBankAccount.Id;

					// Determine if this is the current payment method
					oRadioAttributes	= {type: 'radio', name: 'account-payment-method', value: sValue};
					
					if ((oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT) && (oResponse.iDirectDebit == oBankAccount.Id))
					{
						oRadioAttributes.checked = true;
					}
					
					// Create a UL to show the payment method
					oColBankAccounts.appendChild(
						$T.ul({class: 'horizontal reset account-payment-method payment-method-item'},
							$T.li(
								$T.input(oRadioAttributes)
							),
							$T.li(
								$T.div(
									$T.span({class: 'payment-method-label-dd'},
										'Account Name: '
									),
									$T.span(oBankAccount.AccountName)
								),
								$T.div(
									$T.span({class: 'payment-method-label-dd'},
										'BSB #'
									),
									$T.span(oBankAccount.BSB)
								),
								$T.div(
									$T.span({class: 'payment-method-label-dd'},
										'Account #'
									),
									$T.span(oBankAccount.AccountNumber)
								),
								$T.div(
									$T.span({class: 'payment-method-label-dd'},
										'Bank Name: '
									),
									$T.span(oBankAccount.BankName)
								),
								$T.div(
									$T.span({class: 'payment-method-label-dd'},
										'Created: '
									),
									$T.span(oBankAccount.created_on)
								)
							)
						)
					);
				}
				
				// Add Credit Cards to popup
				var oColCreditCards	= oContent.select('.account-payment-methods-col-credit-cards > ul').first();
				var oCreditCards	= jQuery.json.arrayAsObject(oResponse.aPaymentMethods.credit_cards);
				var oCreditCard		= null;
				
				for (var i in oCreditCards)
				{
					oCreditCard		= oCreditCards[i];
					sValue			= Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD + '_' + oCreditCard.Id;
					
					// Determine if this is the current payment method
					oRadioAttributes	= {type: 'radio', name: 'account-payment-method', value: sValue};
					
					if ((oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD) && (oResponse.iCreditCard == oCreditCard.Id))
					{
						oRadioAttributes.checked = true;
					}
					
					// Create a UL to show the payment method
					oColCreditCards.appendChild(
						$T.ul({class: 'horizontal reset account-payment-method payment-method-item'},
							$T.li(
								$T.input(oRadioAttributes)
							),
							$T.li(
								$T.div(
									$T.span({class: 'payment-method-label-cc'},
										'Name: '
									),
									$T.span(oCreditCard.Name)
								),
								$T.div(
									$T.span({class: 'payment-method-label-cc'},
										'Type: '
									),
									$T.span(oCreditCard.card_type_name)
								),
								$T.div(
									$T.span({class: 'payment-method-label-cc'},
										'Number: '
									),
									$T.span(oCreditCard.card_number)
								),
								$T.div(
									$T.span({class: 'payment-method-label-cc'},
										'CVV: '
									),
									$T.span(oCreditCard.cvv)
								),
								$T.div(
									$T.span({class: 'payment-method-label-cc'},
										'Expires: '
									),
									$T.span({class: (oCreditCard.bExpired ? 'expired' : 'valid')},
										oCreditCard.expiry
									)
								)
							)
						)
					);
				}
				
				// Pre-select Invoice if need be
				if (oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE)
				{
					oContent.select('input.account-payment-method-invoice').first().checked = true;
				}
				
				// Set save & cancel event handlers
				var oSaveButton		= oContent.select('button.icon-button').first();
				oSaveButton.observe('click', this._save.bind(this));
				
				var oCancelButton	= oContent.select('button.icon-button').last();
				oCancelButton.observe('click', this.hide.bind(this));	
				
				// Attach click and mouseover events to all payment method containers
				var aPaymentMethods	= oContent.select('div.payment-method-item, ul.payment-method-item');
				var oPaymentMethod	= null;
				
				for (var i = 0; i < aPaymentMethods.length; i++)
				{
					oPaymentMethod	= aPaymentMethods[i];
					oPaymentMethod.observe('click', 	this._paymentMethodSelected.bind(this, oPaymentMethod));
					oPaymentMethod.observe('mouseover', this._paymentMethodHover.bind(this, oPaymentMethod));
				}
				
				// Display Popup
				this.setTitle("Change Payment Method");
				this.addCloseButton();
				this.setIcon("../admin/img/template/payment.png");
				this.setContent(oContent);
				this.display();
				
				this.oContent	= oContent;
			}
			else
			{
				// AJAX Error
				this._ajaxError(oResponse, true);
			}
		}
	},
	
	_paymentMethodSelected	: function(oPaymentMethod)
	{
		var oRadio		= oPaymentMethod.select('input[type="radio"]').first();
		oRadio.checked 	= true;
	},
	
	_paymentMethodHover	: function(oPaymentMethod)
	{
		if (this.oHighlightedPaymentMethod)
		{
			this.oHighlightedPaymentMethod.removeClassName('payment-method-item-highlight');
		}
		
		oPaymentMethod.addClassName('payment-method-item-highlight');
		this.oHighlightedPaymentMethod = oPaymentMethod;
	},
	
	_save	: function()
	{
		// Check all radio buttons, find the checked one and save it's value
		var aRadios			= this.oContent.select('input[type="radio"]');
		var iBillingType	= null;
		var iBillingDetail	= null;
		
		for (var i = 0; i < aRadios.length; i++)
		{
			if (aRadios[i].checked)
			{
				// Got it, extract the billing type and CreditCard/DirectDebit value
				var aSplit	= aRadios[i].value.split('_');
				iBillingType	= parseInt(aSplit[0]);
				iBillingDetail	= null;
				
				if (aSplit[1])
				{
					iBillingDetail	= parseInt(aSplit[1]);
				}
				break;
			}
		}
		
		if (iBillingType !== null)
		{
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			this._setPaymentMethod	= jQuery.json.jsonFunction(this._saveResponse.bind(this), this._ajaxError.bind(this), 'Account', 'setPaymentMethod');
			this._setPaymentMethod(this.iAccountId, iBillingType, iBillingDetail);
		}
	},
	
	_saveResponse	: function(oResponse)
	{
		if (oResponse.Success)
		{
			// Success! Hide the loading and this popup
			this.oLoading.hide();
			delete this.oLoading;
			this.hide();
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
	}
});

// Image paths
Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Payment_Methods.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

// Billing types
Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT	= 1;
Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD	= 2;
Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE		= 3;

// Credit Card types
Popup_Account_Payment_Methods.CREDIT_CARD_TYPE_VISA			= 1;
Popup_Account_Payment_Methods.CREDIT_CARD_TYPE_MASTERCARD	= 2;
Popup_Account_Payment_Methods.CREDIT_CARD_TYPE_BANKCARD		= 3;
Popup_Account_Payment_Methods.CREDIT_CARD_TYPE_AMEX			= 4;
Popup_Account_Payment_Methods.CREDIT_CARD_TYPE_DINERS		= 5;
