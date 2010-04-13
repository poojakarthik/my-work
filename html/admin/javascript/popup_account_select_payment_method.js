
var Popup_Account_Select_Payment_Method	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, iBillingType, iPaymentMethodId, fnOnSelection, fnOnCancel)
	{
		$super(50);
		
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
			var sAddText		= 'Add a ';
			var sSectionTitle	= '';
			
			switch (this.iBillingType)
			{
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
					this.setTitle("Choose Bank Account");
					sAddText		+= 'Bank Account';
					sSectionTitle	= 'Bank Accounts';
					break;
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
					this.setTitle("Choose Credit Card");
					sAddText		+= 'Credit Card';
					sSectionTitle	= 'Credit Cards';
					break;
			}
			
			// Generate the payment method options and details elements
			this.oContent	= 	$T.div({class: 'payment-methods-list'},
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span(sSectionTitle)
											),
											$T.div({class: 'section-header-options'},
												$T.button({class: 'icon-button'},
													$T.img({src: Popup_Account_Select_Payment_Method.ADD_IMAGE_SOURCE, alt: '', title: sAddText}),
													$T.span(sAddText)
												)
											)
										),
										$T.div({class: 'section-content section-content-fitted'},
											$T.table({class: 'reflex highlight-rows'},
												$T.thead(
													// TH's added later
												),
												$T.tbody({class: 'alternating'})
											)
										)
									),
									$T.div({class: 'payment-methods-list-buttons'},
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
			var oOKButton		= this.oContent.select('div.payment-methods-list-buttons > button.icon-button').first();
			oOKButton.observe('click', this._paymentMethodSelected.bind(this));
			
			var oCancelButton	= this.oContent.select('div.payment-methods-list-buttons > button.icon-button').last();
			oCancelButton.observe('click', this._cancel.bind(this));
			
			var oAddButton	= this.oContent.select('div.section-header-options > button.icon-button').first();
			oAddButton.observe('click', this._addNewPaymentMethod.bind(this));
			
			// Add the table headings
			var oTHead		= this.oContent.select('div.section-content > table.reflex > thead').first();
			var aHeadings	= [];
			
			switch (this.iBillingType)
			{
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
					aHeadings	= ['Account Name', 'BSB #', 'Account #', 'Bank Name', 'Added'];
					break;
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
					aHeadings	= ['Name', 'Type', 'Number', 'CVV', 'Expires', 'Added'];
					break;
			}
			
			// First column for radio button
			oTHead.appendChild($T.th());
			
			for (var i = 0; i < aHeadings.length; i++)
			{
				oTHead.appendChild($T.th(aHeadings[i]));
			}
			
			// One more for the archive image column
			oTHead.appendChild($T.th());
			
			// Add the payment methods from the ajax response
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
		var oTBody	= this.oContent.select('div.section-content > table.reflex > tbody').first();
		var oItem	= null;
		
		switch (this.iBillingType)
		{
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
				var oRadioConfig	= {type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'payment-methods-list-item-radio'};
				
				if (oPaymentMethod.Id == this.iPaymentMethodId)
				{
					oRadioConfig.checked	= true;
				}
				
				oItem		= 	$T.tr(
									$T.td($T.input(oRadioConfig)),
									$T.td(oPaymentMethod.AccountName),
									$T.td(oPaymentMethod.BSB),
									$T.td(oPaymentMethod.AccountNumber),
									$T.td(oPaymentMethod.BankName),
									$T.td(Popup_Account_Select_Payment_Method._formatDate(oPaymentMethod.created_on)),
									$T.td(
										$T.img({class: 'archive-payment', src: Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE, alt: '', title: 'Archive'})
									)
								);
				break;
			case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
				var oRadioConfig	= {type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'payment-methods-list-item-radio'}
				
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
				
				oItem	= 	$T.tr(
								$T.td($T.input(oRadioConfig)),
								$T.td(oPaymentMethod.Name),
								$T.td(oPaymentMethod.card_type_name),
								$T.td(oPaymentMethod.card_number),
								$T.td(oPaymentMethod.cvv),
								$T.td(oPaymentMethod.expiry),
								$T.td(Popup_Account_Select_Payment_Method._formatDate(oPaymentMethod.created_on)),
								$T.td(
									$T.img({class: 'archive-payment', src: Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE, alt: '', title: 'Archive'})
								)
							);
				break;
		}
		
		// Add click event to the archive image
		var oArchiveImage	= oItem.select('img.archive-payment').first();
		oArchiveImage.observe('click', this._archive.bind(this, oPaymentMethod.Id, false));
		
		if ((this.iBillingType != Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD) || !oPaymentMethod.bExpired)
		{
			// Either a bank account or a valid credit card
			// Add click event to the details
			var oRadio	= oItem.select('td > input[type="radio"]').first();
			oItem.observe('click', this._paymentMethodClick.bind(this, oRadio));
		}
		
		oTBody.appendChild(oItem);
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
		var aRadios			= this.oContent.select('input[type="radio"].payment-methods-list-item-radio');
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
			Reflex_Popup.alert('Please select a payment method.', {iWidth: 20});
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
				this._refresh.bind(this)
			);
		}
		
		var fnShowCC	= function()
		{
			new Popup_Account_Add_CreditCard(
				this.iAccountId, 
				this._refresh.bind(this)
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
	
	_refresh	: function()
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
	},
	
	_archive	: function(iId, bConfirmed)
	{
		if (bConfirmed)
		{
			// Archive Confirmed, make AJAX request
			switch (this.iBillingType)
			{
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
					this._archivePaymentMethod = jQuery.json.jsonFunction(this._archiveResponse.bind(this), this._ajaxError.bind(this), 'DirectDebit', 'archiveDirectDebit');
					break;
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
					this._archivePaymentMethod = jQuery.json.jsonFunction(this._archiveResponse.bind(this), this._ajaxError.bind(this), 'Credit_Card', 'archiveCreditCard');
					break;
			}
			
			// Show loading and make request
			this._archivePaymentMethod(iId);
			this.oLoading	= new Reflex_Popup.Loading('Archiving...');
			this.oLoading.display();
		}
		else
		{
			// Popup text
			var sPopupText	= '';
			
			switch (this.iBillingType)
			{
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_DIRECT_DEBIT:
					sPopupText	= 'Bank Account';
					break;
				case Popup_Account_Select_Payment_Method.BILLING_TYPE_CREDIT_CARD:
					sPopupText	= 'Credit Card';
					break;
			}
			
			// Show yes/no confirm Popup
			Reflex_Popup.yesNoCancel('Do you wish to Archive this ' + sPopupText + '?', {fnOnYes: this._archive.bind(this, iId, true)});
		}
	},
	
	_archiveResponse	: function(oResponse)
	{
		// Hide loading popup
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success)
		{
			// Archive successfull, refresh the list
			this._refresh();
		}
		else
		{
			// AJAX error
			this._ajaxError(oResponse);
		}
	},
	
	_paymentMethodClick	: function(oRadio)
	{
		oRadio.checked	= true;
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

Popup_Account_Select_Payment_Method._formatDate	= function(sDate)
{
	return Reflex_Date_Format.format('j/n/Y', Date.parse(sDate.replace(/-/g, '/')) / 1000);
}
