
var Popup_Account_Select_Payment_Method	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, iBillingType)
	{
		$super(40);
		
		this.iAccountId		= iAccountId;
		this.iBillingType	= iBillingType;
		
		this.oLoading		= new Reflex_Popup.Loading('Please Wait');
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
			
			// Generate the payment method options and details elements
			this.oContent	= 	$T.div({class: 'payment-methods'},
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Payment Methods')
											)
										),
										$T.div({class: 'section-content'},
											$T.ul({class: 'reset'})
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
			
			// Display Popup
			switch (this.iBillingType)
			{
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
					this.setTitle("Choose Bank Account");
					break;
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
					this.setTitle("Choose Credit Card");
					break;
			}
			
			for (var i = 0; i < oResponse.aPaymentMethods.length; i++)
			{
				this._addPaymentMethod(oResponse.aPaymentMethods[i]);
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
	
	_addPaymentMethod	: function(oPaymentMethod)
	{
		var oUL		= this.oContent.select('div.section-content > ul.reset').first();
		var oItem	= null;
		
		switch (this.iBillingType)
		{
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
				oItem	= 	$T.li(
								$T.ul({class: 'horizontal reset account-payment-method'},
									$T.li(
										$T.input({type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'account-payment-method-radio'})
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
				oItem	= 	$T.li(
								$T.ul({class: 'horizontal reset account-payment-method'},
									$T.li(
										$T.input({type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'account-payment-method-radio'})
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
	
	_save	: function()
	{
		
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

