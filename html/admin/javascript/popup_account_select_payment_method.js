
var Popup_Account_Select_Payment_Method	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, iMethodSubType, iPaymentMethodId, fnOnSelection, fnOnCancel)
	{
		if (!Popup_Account_Select_Payment_Method.HAS_CONSTANTS)
		{
			return;
		}
		
		$super(50);
		
		this.iAccountId			= iAccountId;
		this.iMethodSubType		= iMethodSubType;
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
			this._getPaymentMethods	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this), 
											this._buildUI.bind(this), 
											'Account', 
											'getPaymentMethods'
										);
			this._getPaymentMethods(this.iAccountId, this.iMethodSubType);
		}
		else if (oResponse.Success)
		{
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;
			
			// Billing type specific content
			var sAddText		= 'Add a ';
			var sSectionTitle	= '';
			
			switch (this.iMethodSubType)
			{
				case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
					this.setTitle("Choose Bank Account");
					sAddText		+= 'Bank Account';
					sSectionTitle	= 'Bank Accounts';
					break;
				case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
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
													// th's added later
												),
												$T.tbody({class: 'alternating'})
											)
										)
									),
									$T.div({class: 'payment-methods-list-buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Select_Payment_Method.SAVE_IMAGE_SOURCE, alt: '', title: 'OK'}),
											$T.span('OK')
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
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
			this.aHeadings	= [];
			
			switch (this.iMethodSubType)
			{
				case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
					this.aHeadings	= ['Account Name', 'BSB #', 'Account #', 'Bank Name', 'Added'];
					break;
				case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
					this.aHeadings	= ['Name', 'Type', 'Number', 'CVV', 'Expires', 'Added'];
					break;
			}
			
			// First column for radio button
			oTHead.appendChild($T.th());
			
			for (var i = 0; i < this.aHeadings.length; i++)
			{
				oTHead.appendChild($T.th(this.aHeadings[i]));
			}
			
			// One more for the archive image column
			oTHead.appendChild($T.th());
			
			// Add the payment methods from the ajax response
			if (oResponse.aPaymentMethods.length)
			{
				for (var i = 0; i < oResponse.aPaymentMethods.length; i++)
				{
					this._createPaymentMethod(oResponse.aPaymentMethods[i]);
				}
			}
			else
			{
				this._addNoRecordsRow(this.aHeadings.length);
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
	
	_addNoRecordsRow	: function(iColumnCount)
	{
		var oTBody	= this.oContent.select('div.section-content > table.reflex > tbody').first();
		var sText 	= '';
		
		switch (this.iMethodSubType)
		{
			case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
				sText	= 'There are no Bank Accounts to choose from';
				break;
			
			case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
				sText	= 'There are no Credit Cards to choose from';
				break;
		}
		
		oTBody.appendChild(
			$T.tr(        
				$T.td({class: 'payment-methods-list-no-records-row', colspan: iColumnCount + 2},
					sText
				)
			)
		);
	},
	
	_createPaymentMethod	: function(oPaymentMethod)
	{
		var oTBody	= this.oContent.select('div.section-content > table.reflex > tbody').first();
		var oItem	= null;
		
		switch (this.iMethodSubType)
		{
			case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
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
				
			case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
				var oRadioConfig	= {type: 'radio', name: 'account-payment-method', value: oPaymentMethod.Id, class: 'payment-methods-list-item-radio'}
				
				// Check the expiry date on the credit card
				Popup_Account_Select_Payment_Method._checkCreditCardExpiry(oPaymentMethod);
				
				if (oPaymentMethod.Id == this.iPaymentMethodId && !oPaymentMethod.bExpired)
				{
					oRadioConfig.checked	= true;
				} 
				else if (oPaymentMethod.bExpired)
				{
					oRadioConfig.style = 'display: none;';
				}
				
				oItem	= 	$T.tr(
								$T.td($T.input(oRadioConfig)),
								$T.td(oPaymentMethod.Name),
								$T.td(oPaymentMethod.card_type_name),
								$T.td(oPaymentMethod.card_number),
								$T.td(oPaymentMethod.cvv),
								$T.td({class: 'payment-method-credit-card-' + (oPaymentMethod.bExpired ? 'expired' : 'valid')},
									oPaymentMethod.expiry
								),
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
		var oRadio	= oItem.select('td > input[type="radio"]').first();
		
		if ((this.iMethodSubType != $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD) || !oPaymentMethod.bExpired)
		{
			// Either a bank account or a valid credit card
			// Add click event to the details
			var oRadio	= oItem.select('td > input[type="radio"]').first();
			oItem.observe('click', this._paymentMethodClick.bind(this, oRadio));
			oItem.style.cursor	= 'pointer';
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
		var iMethodSubType	= null;
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
				this.fnOnSelection(this.hPaymentMethods[iBillingDetail]);
			}
			
			this.hide();
		}
		else
		{
			// Get method name
			var sMethodName	= '';
			switch (this.iMethodSubType)
			{
				case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
					sMethodName	= 'Bank Account';
					break;
				case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
					sMethodName	= 'Credit Card';
					break;
			}
			
			Reflex_Popup.alert('Please select a ' + sMethodName + '.', {iWidth: 20});
		}
	},
	
	_cancel	: function()
	{
		// Cancel callback
		if (typeof this.fnOnCancel !== 'undefined')
		{
			this.fnOnCancel();
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
		
		switch (this.iMethodSubType)
		{
			case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_directdebit.js', 
					fnShowDD.bind(this)
				);
				break;
			case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
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
		this.oLoading	= new Reflex_Popup.Loading('Please Wait...');
		this.oLoading.display();
		this._getPaymentMethods	= jQuery.json.jsonFunction(this._populateList.bind(this), this._populateList.bind(this), 'Account', 'getPaymentMethods');
		this._getPaymentMethods(this.iAccountId, this.iMethodSubType);
	},
	
	_populateList	: function(oResponse)
	{
		if (oResponse.Success)
		{
			// Clear existing list items
			var aTRs	= this.oContent.select('div.section-content > table.reflex > tbody > tr');
			
			for (var i = 0; i < aTRs.length; i++)
			{
				aTRs[i].remove();
			}
			
			// Add the new data
			if (oResponse.aPaymentMethods && oResponse.aPaymentMethods.length)
			{
				for (var i = 0; i < oResponse.aPaymentMethods.length; i++)
				{
					this._createPaymentMethod(oResponse.aPaymentMethods[i]);
				}
			}
			else
			{
				this._addNoRecordsRow(this.aHeadings.length);
			}
			
			this.oLoading.hide();
			delete this.oLoading; 
		}
		else
		{
			// AJAX Error
			this._ajaxError(oResponse);
		}
	},
	
	_archive	: function(iId, bConfirmed)
	{
		if (bConfirmed)
		{
			// Archive Confirmed, make AJAX request
			switch (this.iMethodSubType)
			{
				case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
					this._archivePaymentMethod = jQuery.json.jsonFunction(this._archiveResponse.bind(this), this._ajaxError.bind(this), 'DirectDebit', 'archiveDirectDebit');
					break;
				case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
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
			var sPopupName	= '';
			
			switch (this.iMethodSubType)
			{
				case $CONSTANT.DIRECT_DEBIT_TYPE_BANK_ACCOUNT:
					sPopupName	= 'Bank Account';
					break;
				case $CONSTANT.DIRECT_DEBIT_TYPE_CREDIT_CARD:
					sPopupName	= 'Credit Card';
					break;
			}
			
			if (iId == this.iPaymentMethodId)
			{
				mPopupText	=	$T.div( 	
									$T.div('This ' + sPopupName + ' is the currently used by this account.'),
									$T.div('Are you sure you want to archive it?')
								);
			}
			else
			{
				mPopupText	= 'Do you wish to Archive this ' + sPopupName + '?';
			}
			
			// Show yes/no confirm Popup
			Reflex_Popup.yesNoCancel(mPopupText, {fnOnYes: this._archive.bind(this, iId, true), iWidth: 30});
		}
	},
	
	_archiveResponse	: function(oResponse)
	{
		// Hide loading popup
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success)
		{
			// Archive successful, refresh the list
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

//Check if $CONSTANT has correct constant groups loaded, if not this class won't work
if (typeof Flex.Constant.arrConstantGroups.payment_method == 'undefined' ||
	typeof Flex.Constant.arrConstantGroups.direct_debit_type == 'undefined')
{
		Popup_Account_Select_Payment_Method.HAS_CONSTANTS	= false;
	throw ('Please load the "payment_method" & "direct_debit_type" constant groups before using Popup_Account_Select_Payment_Method');
}
else
{
	Popup_Account_Select_Payment_Method.HAS_CONSTANTS	= true;
}

// Image paths
Popup_Account_Select_Payment_Method.CANCEL_IMAGE_SOURCE = '../admin/img/template/delete.png';
Popup_Account_Select_Payment_Method.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
Popup_Account_Select_Payment_Method.ADD_IMAGE_SOURCE	= '../admin/img/template/new.png';

