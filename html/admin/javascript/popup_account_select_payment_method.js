
var Popup_Account_Select_Payment_Method	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, iBillingType, iPaymentMethodId, fnOnSelection, fnOnCancel)
	{
		$super(40);
		
		this.iAccountId			= iAccountId;
		this.iBillingType		= iBillingType;
		this.iPaymentMethodId	= iPaymentMethodId;
		this.fnOnSelection		= fnOnSelection;
		this.fnOnCancel			= fnOnCancel;
		this.hPaymentMethods	= {};
		this.oLoading			= new Reflex_Popup.Loading('Please Wait');
		this.oLoading.display();
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the current payment and information on other methods
			this._getPaymentMethods	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Account', 'getPaymentMethods');
			this._getPaymentMethods(this.iAccountId, this.iBillingType);
		}
		else if (oResponse.Success)
		{
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;
			
			// Billing type specific content
			var sAddText	= 'Add a ';
			
			switch (this.iBillingType)
			{
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
					this.setTitle("Choose Bank Account");
					sAddText	+= 'Bank Account';
					break;
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
					this.setTitle("Choose Credit Card");
					sAddText	+= 'Credit Card';
					break;
			}
			
			// Generate the payment method options and details elements
			this.oContent	= 	$T.div({class: 'payment-methods'},
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Payment Methods')
											),
											$T.div({class: 'section-header-options'},
												$T.button({class: 'icon-button'},
													$T.img({src: Popup_Account_Select_Payment_Method.ADD_IMAGE_SOURCE, alt: '', title: sAddText}),
													$T.span(sAddText)
												)
											)
										),
										$T.div({class: 'section-content'},
											$T.ul({class: 'reset'})
										)
									),
									$T.div({class: 'payment-methods-buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Payment_Methods.SAVE_IMAGE_SOURCE, alt: '', title: 'OK'}),
											$T.span('OK')
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										)
									)
								);
			
			// Set save, cancel & add event handlers
			var oOKButton		= this.oContent.select('div.payment-methods-buttons > button.icon-button').first();
			oOKButton.observe('click', this._paymentMethodSelected.bind(this));
			
			var oCancelButton	= this.oContent.select('div.payment-methods-buttons > button.icon-button').last();
			oCancelButton.observe('click', this._cancel.bind(this));
			
			var oAddButton	= this.oContent.select('div.section-header-options > button.icon-button').first();
			oAddButton.observe('click', this._addNewPaymentMethod.bind(this));
			
			for (var i = 0; i < oResponse.aPaymentMethods.length; i++)
			{
				this._createPaymentMethod(oResponse.aPaymentMethods[i]);
			}
			
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
	
	_createPaymentMethod	: function(oPaymentMethod)
	{
		var oUL		= this.oContent.select('div.section-content > ul.reset').first();
		var oItem	= null;
		
		switch (this.iBillingType)
		{
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
				var oRadioConfig	= {type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'account-payment-method-radio'};
				
				if (oPaymentMethod.Id == this.iPaymentMethodId)
				{
					oRadioConfig.checked	= true;
				}
				
				oItem	= 	$T.li(
								$T.ul({class: 'horizontal reset account-payment-method'},
									$T.li(
										$T.input(oRadioConfig)
									),
									$T.li({class: 'account-payment-method-item-details'},
										$T.div(
											$T.span({class: 'payment-method-label dd'},
												'Account Name: '
											),
											$T.span(oPaymentMethod.AccountName),
											$T.img({class: 'archive-payment-method', src: Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE, alt: '', title: 'Archive'})
										),
										$T.div(
											$T.span({class: 'payment-method-label dd'},
												'BSB #'
											),
											$T.span(oPaymentMethod.BSB)
										),
										$T.div(
											$T.span({class: 'payment-method-label dd'},
												'Account #'
											),
											$T.span(oPaymentMethod.AccountNumber)
										),
										$T.div(
											$T.span({class: 'payment-method-label dd'},
												'Bank Name: '
											),
											$T.span(oPaymentMethod.BankName)
										),
										$T.div(
											$T.span({class: 'payment-method-label dd'},
												'Created: '
											),
											$T.span(oPaymentMethod.created_on)
										)
									)
								)
							);
				break;
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
				var oRadioConfig	= {type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'account-payment-method-radio'}
				
				if (oPaymentMethod.Id == this.iPaymentMethodId)
				{
					oRadioConfig.checked	= true;
				}
				
				// Check the expiry date on the credit card
				Popup_Account_Select_Payment_Method._checkCreditCardExpiry(oPaymentMethod);
				
				if (oPaymentMethod.bExpired)
				{
					oRadioConfig.disabled	= true;
				}
				
				oItem	= 	$T.li(
								$T.ul({class: 'horizontal reset account-payment-method'},
									$T.li(
										$T.input(oRadioConfig)
									),
									$T.li({class: 'account-payment-method-item-details'},
										$T.div(
											$T.span({class: 'payment-method-label cc'},
												'Name: '
											),
											$T.span(oPaymentMethod.Name),
											$T.img({class: 'archive-payment-method', src: Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE, alt: '', title: 'Archive'})
										),
										$T.div(
											$T.span({class: 'payment-method-label cc'},
												'Type: '
											),
											$T.span(oPaymentMethod.card_type_name)
										),
										$T.div(
											$T.span({class: 'payment-method-label cc'},
												'Number: '
											),
											$T.span(oPaymentMethod.card_number)
										),
										$T.div(
											$T.span({class: 'payment-method-label cc'},
												'CVV: '
											),
											$T.span(oPaymentMethod.cvv)
										),
										$T.div(
											$T.span({class: 'payment-method-label cc'},
												'Expires: '
											),
											$T.span({class: (oPaymentMethod.bExpired ? 'expired' : 'valid')},
													oPaymentMethod.expiry
											)
										)
									)
								)
							); 
				break;
		}
		
		oUL.appendChild(oItem);
		this.hPaymentMethods[oPaymentMethod.Id]	= oPaymentMethod;
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
	
	_paymentMethodSelected	: function()
	{
		// Check all radio buttons, find the checked one and save it's value
		var aRadios			= this.oContent.select('input[type="radio"].account-payment-method-radio');
		var iBillingType	= null;
		var iBillingDetail	= null;
		
		for (var i = 0; i < aRadios.length; i++)
		{
			if (aRadios[i].checked)
			{
				// Got the billing detail
				iBillingDetail	= parseInt(aRadios[i].value);
				break;
			}
		}
		
		if (iBillingDetail !== null && this.hPaymentMethods[iBillingDetail])
		{
			// Selection callback
			if (typeof this.fnOnSelection !== 'undefined')
			{
				this.fnOnSelection(this.iBillingType, this.hPaymentMethods[iBillingDetail]);
			}
			
			this.hide();
		}
		else
		{
			Reflex_Popup.alert('Please select a payment method.');
		}
	},
	
	_cancel	: function()
	{
		// Cancel callback
		if (typeof this.fnOnCancel !== 'undefined')
		{
			this.fnOnCancel(this.iBillingType);
		}
		
		this.hide();
	},
	
	_addNewPaymentMethod	: function()
	{
		var fnShowDD	= function()
		{
			new Popup_Account_Add_DirectDebit(
				this.iAccountId, 
				this._paymentMethodAdded.bind(this)
			);
		}
		
		var fnShowCC	= function()
		{
			new Popup_Account_Add_CreditCard(
				this.iAccountId, 
				this._paymentMethodAdded.bind(this)
			);
		}
		
		switch (this.iBillingType)
		{
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_directdebit.js', 
					fnShowDD.bind(this)
				);
				break;
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_creditcard.js', 
					fnShowCC.bind(this)
				);
				break;
		}
	},
	
	_paymentMethodAdded	: function()
	{
		// Refresh the list of payment methods
		this.oLoading	= new Reflex_Popup.Loading('Please Wait');
		this.oLoading.display();
		this._getPaymentMethods(this.iAccountId, this.iBillingType);
	},
	
	_populateList	: function(oResponse)
	{
		// Clear existing list items
		var aLIs	= this.oContent.select('div.section-content > ul.reset > li');
		
		for (var i = 0; i < aLIs.length; i++)
		{
			aLIs[i].remove();
		}
		
		// Add the new data
		if (oResponse.aPaymentMethods)
		{
			for (var i = 0; i < oResponse.aPaymentMethods.length; i++)
			{
				this._createPaymentMethod(oResponse.aPaymentMethods[i]);
			}
		}
		
		this.oLoading.hide();
		delete this.oLoading; 
	}
});

// Billing types
Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT	= 1;
Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD	= 2;
Popup_Account_Select_Payment_Method.BILLING_TYPE_INVOICE		= 3;

// Image paths
Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE = '../admin/img/template/delete.png';
Popup_Account_Select_Payment_Method.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
Popup_Account_Select_Payment_Method.ADD_IMAGE_SOURCE	= '../admin/img/template/new.png';



Popup_Account_Select_Payment_Method._checkCreditCardExpiry	= function(oCreditCard)
{
	month 	= parseInt(oCreditCard.ExpMonth);
	year 	= parseInt(oCreditCard.ExpYear);
	
	var d 			= new Date();
	var curr_month 	= d.getMonth() + 1;
	var curr_year	= d.getFullYear();
	
	oCreditCard.expiry		= (month < 10 ? '0' + month : month) + '/' + year;
	oCreditCard.bExpired	= !(year > curr_year || (year == curr_year && month >= curr_month));
};
