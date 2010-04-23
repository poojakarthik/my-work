

var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(48);
		
		this.iAccountId		= iAccountId;
		
		// Stores objects representing payment methods, hashed against the billing type
		this.hCachedMethods	= {};
		this.hCachedMethods[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]	= {Id: null};
		
		// Stores radio buttons & summary divs, hashed by the billing type they represent
		this.hBillingTypeRadios		= {};
		this.hBillingTypeDom		= {};
		
		this.iCurrentBillingType	= null;
		this.iSelectedBillingType	= null;
		
		this.oLoading	= new Reflex_Popup.Loading('Please Wait');
		this.oLoading.display();
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the current payment and information on other methods
			this._getCurrentPaymentMethod	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Account', 'getCurrentPaymentMethod');
			this._getCurrentPaymentMethod(this.iAccountId);
		}
		else if (oResponse.Success)
		{
			this._updateCurrentPaymentMethod(false, oResponse);
			
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
			
			// Add a radio option for each billing type
			this._addBillingType(Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE);
			this._addBillingType(Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT);
			this._addBillingType(Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD);
			
			// Select the current payment method
			this.iCurrentBillingType	= oResponse.iBillingType;
			this._selectBillingType(oResponse.iBillingType);
			
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
	
	_addBillingType	: function(iBillingType)
	{
		// Using divs
		var sName	= Popup_Account_Payment_Methods.BILLING_TYPE_NAME[iBillingType];
		var oDom	= 	$T.li({class: 'billing-type'},
							$T.input({type: 'radio', name: 'billing-type-option', value: iBillingType}),
							$T.img({src: Popup_Account_Payment_Methods.BILLING_TYPE_IMAGE[iBillingType], alt: sName, title: sName}),
							$T.span(sName)
						);
		// Events
		var oRadio	= oDom.select('input[type="radio"]').first();
		oRadio.observe('click', this._billingTypeSelected.bind(this, oRadio));
		oDom.observe('click', this._selectBillingType.bind(this, iBillingType));
		
		// Attach element
		var oSectionContent	= this.oContent.select('li.payment-method-billing-types > ul.reset').first();
		oSectionContent.appendChild(oDom);
		
		// Cache the radio
		this.hBillingTypeRadios[iBillingType]	= oRadio;
		this.hBillingTypeDom[iBillingType]		= oDom;
	},
	
	_billingTypeSelected	: function(oRadio)
	{
		var iBillingType 	= parseInt(oRadio.value);
		
		// Clear the details
		this._clearDetails();
		
		// Check if it has expired, applies to credit cards only
		var bExpired	= false;
		
		if (this.hCachedMethods[iBillingType] && (iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD) && this.hCachedMethods[iBillingType].bExpired)
		{
			bExpired	= true;
		}
		
		if (this.hCachedMethods[iBillingType] && !bExpired)
		{
			// Got payment method details cached for the billing type, Show the details
			this._updateDetails(iBillingType, this.hCachedMethods[iBillingType]);
		}
		else 
		{
			// For the billing type (except invoice, it's always available) see if a 
			// 'new payment method' or 'select payment method' popup is required
			switch(iBillingType)
			{
				case Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT:
					if (this.bHasBankAccount)
					{
						// Show select bank account popup
						this._showPaymentMethodSelectPopup(iBillingType, Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE);
					}
					else
					{
						// Show the add new bank account popup
						this._showAddPaymentMethodPopup(iBillingType);
					}
					break;
				case Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD:
					if (this.bHasCreditCard)
					{
						// Show select credit card popup
						this._showPaymentMethodSelectPopup(iBillingType, Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE);
					}
					else
					{
						// Show the add new credit card popup
						this._showAddPaymentMethodPopup(iBillingType);
					}
					break;
			}
		}
		
		if (iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE)
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
			this.hBillingTypeDom[this.iSelectedBillingType].removeClassName('billing-type-selected');
		}
		
		// Select the new option
		this.hBillingTypeDom[iBillingType].addClassName('billing-type-selected');
		
		// Record selected billing type
		this.iSelectedBillingType	= iBillingType;
	},
	
	_selectBillingType	: function(iBillingType)
	{
		var oRadio	= this.hBillingTypeRadios[iBillingType];
		
		if (oRadio)
		{
			oRadio.checked = true;
			this._billingTypeSelected(oRadio);
		}
	},
	
	_updateDetails	: function(iBillingType, oPaymentMethod)
	{
		// Update the details, description
		var oDescDiv		= this.oContent.select('li.payment-method-details > div > div.payment-method-details-description').first();
		oDescDiv.innerHTML	= Popup_Account_Payment_Methods.BILLING_TYPE_DESCRIPTION[iBillingType];
		
		// Update the details, info
		var oInfoDiv	= this.oContent.select('li.payment-method-details > div > div.payment-method-details-info').first();
		var oDom		= false;
		
		switch (iBillingType)
		{
			case Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT:
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
			case Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD:
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
			case Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE:
				// Do nothing...
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
			
			this._setPaymentMethod	= jQuery.json.jsonFunction(this._saveResponse.bind(this), this._ajaxError.bind(this), 'Account', 'setPaymentMethod');
			this._setPaymentMethod(
				this.iAccountId, 
				this.iSelectedBillingType, 
				this.hCachedMethods[this.iSelectedBillingType].Id
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
	
	_showPaymentMethodSelectPopup	: function(iBillingType, iBillingTypeToSelect)
	{
		var fnShow	= function(iBillingType, iBillingTypeToSelect)
		{
			// Create the popup, giving it payment method details as well as cancel and selection callbacks
			new Popup_Account_Select_Payment_Method(
				this.iAccountId, 
				iBillingType, 
				(this.hCachedMethods[iBillingType] ? this.hCachedMethods[iBillingType].Id : null),
				this._paymentMethodSelected.bind(this),
				this._paymentMethodSelectCancelled.bind(this, iBillingTypeToSelect)
			);
		}
		
		// Load js file
		JsAutoLoader.loadScript(
			['popup_account_select_payment_method.js','reflex_date_format.js'], 
			fnShow.bind(this, iBillingType, iBillingTypeToSelect),
			true
		);
	},
	
	_paymentMethodSelected	: function(iBillingType, oPaymentMethod)
	{
		if ((iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD) && (typeof oPaymentMethod.expiry === 'undefined'))
		{
			// Check the credit cards expiry
			Popup_Account_Payment_Methods._checkCreditCardExpiry(oPaymentMethod);
		}
	
		this.hCachedMethods[iBillingType]	= oPaymentMethod;
		this._selectBillingType(iBillingType);
	},
	
	_paymentMethodSelectCancelled	: function(iBillingTypeToSelect, iBillingType)
	{
		if (iBillingType != iBillingTypeToSelect)
		{
			this._refresh(iBillingTypeToSelect);
		}
		else
		{
			this._refresh(iBillingType);
		}
	},
	
	_changePaymentMethodForBillingType	: function()
	{
		// Check if there are any payment methods to change to, if not show add popup.
		var	bCredit	= (this.iSelectedBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD) && this.bHasCreditCard;
		var	bDebit	= (this.iSelectedBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT) && this.bHasBankAccount;
		
		if (bCredit || bDebit)
		{
			// There are payment methods for the billing type. Show the select payment method popup
			this._showPaymentMethodSelectPopup(this.iSelectedBillingType, this.iSelectedBillingType);
		}
		else
		{
			// Show 'add' payment method popup
			this._showAddPaymentMethodPopup(this.iSelectedBillingType);
		}
	},
	
	_showAddPaymentMethodPopup	: function(iBillingType)
	{
		switch (iBillingType)
		{
			case Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD:
				// Add credit card popup
				var fnShowCC	= function(iBillingType, iBillingTypeToSelect)
				{
					new Popup_Account_Add_CreditCard(
						this.iAccountId, 
						this._paymentMethodSelected.bind(this, iBillingType),
						this._paymentMethodSelectCancelled.bind(this, iBillingTypeToSelect, iBillingType)
					);
				}
				
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_creditcard.js', 
					fnShowCC.bind(this, iBillingType, Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE)
				);
				break;
			case Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT:
				// Add bank account popup
				var fnShowDD	= function(iBillingType, iBillingTypeToSelect)
				{
					new Popup_Account_Add_DirectDebit(
						this.iAccountId, 
						this._paymentMethodSelected.bind(this, iBillingType),
						this._paymentMethodSelectCancelled.bind(this, iBillingTypeToSelect, iBillingType)
					);
				}
				
				JsAutoLoader.loadScript(
					'javascript/popup_account_add_directdebit.js', 
					fnShowDD.bind(this, iBillingType, Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE)
				);
				break;
		}
	},
	
	_refresh	: function(iBillingTypeToSelect)
	{
		// Refresh the current of payment method situation
		this.oLoading	= new Reflex_Popup.Loading('Please Wait...');
		this.oLoading.display();
		this._refreshCurrentPaymentMethod	= 	jQuery.json.jsonFunction(
													this._updateCurrentPaymentMethod.bind(this, iBillingTypeToSelect), 
													this._updateCurrentPaymentMethod.bind(this, iBillingTypeToSelect), 
													'Account', 
													'getCurrentPaymentMethod'
												);
		this._refreshCurrentPaymentMethod(this.iAccountId);
	},
	
	_updateCurrentPaymentMethod	: function(iBillingTypeToSelect, oResponse)
	{
		if (oResponse.Success)
		{
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;
			
			// Update flags
			this.bHasCreditCard		= oResponse.bHasCreditCard;
			this.bHasBankAccount	= oResponse.bHasBankAccount;
			
			// No payment method returned, if Invoice is the current
			if (oResponse.oPaymentMethod)
			{
				if (oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD)
				{
					// Check the expiry date on the credit card
					Popup_Account_Payment_Methods._checkCreditCardExpiry(oResponse.oPaymentMethod);
				}
				
				// Update payment method cache
				this.hCachedMethods[oResponse.iBillingType]	= oResponse.oPaymentMethod; 	 
			}
			else if (oResponse.iBillingDetail)
			{
				if (this.hCachedMethods[oResponse.iBillingType] && (this.hCachedMethods[oResponse.iBillingType].Id == oResponse.iBillingDetail))
				{
					// There is no payment method but there is still billing type & detail recorded, remove the invalid payment method from the hash
					this.hCachedMethods[oResponse.iBillingType]	= null;
				}
			}
			
			// Select billing type if one is specified
			if (iBillingTypeToSelect)
			{
				this._selectBillingType(iBillingTypeToSelect);
			}
		}
		else
		{
			// AJAX error
			this._ajaxError(oResponse);
		}
	}
});

// Image paths
Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Payment_Methods.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
Popup_Account_Payment_Methods.EDIT_IMAGE_SOURCE		= '../admin/img/template/pencil.png';

// Billing types
Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT	= 1;
Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD	= 2;
Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE		= 3;

// Billing type info
var hName	= {};
hName[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]		= 'Invoice';
hName[Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD]	= 'Direct Debit via Credit Card';
hName[Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT]	= 'Direct Debit via Bank Transfer';
Popup_Account_Payment_Methods.BILLING_TYPE_NAME					= hName;

var hDescription	= {};
hDescription[Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT]	= 'Money owed is automatically transferred from the following bank account.';
hDescription[Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD]	= 'Money owed is automatically charged to the following credit card.';
hDescription[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]		= 'Money owed is to be paid upon recieving the invoice.';
Popup_Account_Payment_Methods.BILLING_TYPE_DESCRIPTION					= hDescription;

var hImage	= {};
hImage[Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT]	= '../admin/img/template/bank_transfer.png';
hImage[Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD]	= '../admin/img/template/credit_card.png';
hImage[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]		= '../admin/img/template/money.png';
Popup_Account_Payment_Methods.BILLING_TYPE_IMAGE				= hImage;

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
