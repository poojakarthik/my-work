
/*
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
										$T.ul({class: 'reset payment-method-group'},
											$T.li(
												$T.input({type: 'radio', name: 'payment-method-group', class: 'account-payment-method-radio', value: Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE}),
												$T.span('Invoice')
											),
											$T.li(
												$T.input({type: 'radio', name: 'payment-method-group'}),
												$T.span('Direct Debit')
											)
										),
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
														$T.span({class: 'payment-method-title'},
															'Credit Cards'
														)
													),
													$T.td(
														$T.span({class: 'payment-method-title'},
															'Bank Accounts'
														)
													)											
												)
											),
											$T.tbody(
												$T.tr(
													$T.td({class: 'account-payment-methods-col-credit-cards'},
														$T.div(
															$T.button({class: 'icon-button'},
																$T.img({src: Popup_Account_Payment_Methods.ADD_IMAGE_SOURCE, alt: '', title: 'AddBank Account'}),
																$T.span('Add Credit Card')
															)
														),
														$T.ul({class: 'horizontal reset account-payment-method'})
													),
													$T.td({class: 'account-payment-methods-col-bank-accounts'},
														$T.div(
															$T.button({class: 'icon-button'},
																$T.img({src: Popup_Account_Payment_Methods.ADD_IMAGE_SOURCE, alt: '', title: 'Add Bank Account'}),
																$T.span('Add Bank Account')
															)
														),
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
				
				this.oContent	= oContent;
				
				// Add event handlers to payment group selectors
				var aPaymentGroupRadios			= oContent.select('ul.payment-method-group > li > input[type="radio"]');
				var oPaymentGroupInvoice		= aPaymentGroupRadios[0];
				var oPaymentGroupDirectDebit	= aPaymentGroupRadios[1];
				oPaymentGroupInvoice.observe('click', this._paymentGroupSelected.bind(this, false));
				oPaymentGroupDirectDebit.observe('click', this._paymentGroupSelected.bind(this, true));
				
				this._refresh(oResponse);
				
				// Add button(s) event handlers
				var oAddCreditCard	= oContent.select('td.account-payment-methods-col-credit-cards button' ).first();
				oAddCreditCard.observe('click', this._showAddCreditCardPopup.bind(this));
				
				var oAddBankAccount	= oContent.select('td.account-payment-methods-col-bank-accounts button' ).first();
				oAddBankAccount.observe('click', this._showAddBankAccountPopup.bind(this));
				
				// Set save & cancel event handlers
				var oSaveButton		= oContent.select('div.account-payment-methods-buttons > button.icon-button').first();
				oSaveButton.observe('click', this._save.bind(this));
				
				var oCancelButton	= oContent.select('button.icon-button').last();
				oCancelButton.observe('click', this.hide.bind(this));	
				
				// Attach click and mouseover events to all payment method containers
				var aPaymentMethods	= oContent.select('div.payment-method-item, ul.payment-method-item');
				var oPaymentMethod	= null;
				var oArchiveButton	= null;
				
				for (var i = 0; i < aPaymentMethods.length; i++)
				{
					oPaymentMethod	= aPaymentMethods[i];
					oPaymentMethod.observe('click', this._paymentMethodSelected.bind(this, oPaymentMethod));
					
					oArchiveButton	= oPaymentMethod.select('img.archive-payment-method').first();
					oArchiveButton.observe('click', this._archivePaymentMethod.bind(this, null));
				}
				
				// Display Popup
				this.setTitle("Change Payment Method");
				this.addCloseButton();
				this.setIcon("../admin/img/template/payment.png");
				this.setContent(oContent);
				this.display();
			}
			else
			{
				// AJAX Error
				this._ajaxError(oResponse, true);
			}
		}
	},
	
	_archivePaymentMethod	: function(bDoArchive, event)
	{
		if (bDoArchive === null)
		{
			// Event handler, show yes/no popup
			Reflex_Popup.yesNoCancel(
				'Archive this Payment Method?', 
				{
					iWidth	: 20,
					fnOnYes	: this._archivePaymentMethod.bind(this, true),
					fnOnNo	: this._archivePaymentMethod.bind(this, false)
				}
			);
			
			Event.stop(event);
		}
		else if (bDoArchive === true)
		{
			// Got response from yes/no, archive the payment method
			this._archivePaymentMethod	= jQuery.json.jsonFunction(this._archiveResponse.bind(this), this._ajaxError.bind(this), 'Account', 'archivePaymentMethod');
			//this._archivePaymentMethod();
		}
	},
	
	_archiveResponse	: function(oResponse)
	{
		if (oResponse.Success)
		{
			// All good, refresh
			this._refresh();
		}
		else
		{
			// AJAX Error
			this._ajaxError(oResponse);
		}
	},
	
	_paymentGroupSelected	: function(bShowDirectDebitTable)
	{
		var oDirectDebitTable	= this.oContent.select('table.reflex').first();
		if (bShowDirectDebitTable)
		{
			oDirectDebitTable.style.display	= 'inline-table';
		}
		else
		{
			oDirectDebitTable.style.display	= 'none';
		}
	},
	
	_showAddCreditCardPopup	: function()
	{
		var fnShow	= function()
		{
			new Popup_Account_Add_CreditCard(this.iAccountId, this._refresh.bind(this));
		}
		
		JsAutoLoader.loadScript('javascript/popup_account_add_creditcard.js', fnShow.bind(this));
	},
	
	_showAddBankAccountPopup	: function()
	{
		var fnShow	= function()
		{
			new Popup_Account_Add_DirectDebit(this.iAccountId, this._refresh.bind(this));
		}
		
		JsAutoLoader.loadScript('javascript/popup_account_add_directdebit.js', fnShow.bind(this));
	},
	
	_refresh	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// Make AJAX Request
			this._getPaymentMethods	= jQuery.json.jsonFunction(this._refresh.bind(this), this._refresh.bind(this), 'Account', 'getPaymentMethods');
			this._getPaymentMethods(this.iAccountId);
			return;
		}
		else if (oResponse.Success)
		{
			// Got response, populate the direct debit payment methods
			// Clear both lists
			var aPaymentMethodItems	= this.oContent.select('div.payment-method-item');
			
			for (var i = 0; i < aPaymentMethodItems.length; i++)
			{
				aPaymentMethodItems[i].remove();
			}
			
			// Add Bank Accounts to popup
			var oColBankAccounts	= this.oContent.select('td.account-payment-methods-col-bank-accounts > ul').first();
			var oBankAccounts		= jQuery.json.arrayAsObject(oResponse.aPaymentMethods.direct_debits);
			var oBankAccount		= null;
			var oRadioAttributes	= null;
			var sValue				= null;
			
			for (var i in oBankAccounts)
			{
				oBankAccount	= oBankAccounts[i];
				sValue			= Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT + '_' + oBankAccount.Id;

				// Determine if this is the current payment method
				oRadioAttributes	= {type: 'radio', name: 'account-payment-method', value: sValue, class: 'account-payment-method-radio'};
				
				if ((oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT) && (oResponse.iDirectDebit == oBankAccount.Id))
				{
					oRadioAttributes.checked	= true;
				}
				
				// Create a UL to show the payment method
				oColBankAccounts.appendChild(
					$T.div({class: 'payment-method-item'},
						$T.ul({class: 'horizontal reset account-payment-method'},
							$T.li(
								$T.input(oRadioAttributes)
							),
							$T.li({class: 'account-payment-method-item-details'},
								$T.div(
									$T.span({class: 'payment-method-label dd'},
										'Account Name: '
									),
									$T.span(oBankAccount.AccountName),
									$T.img({class: 'archive-payment-method', src: Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE, alt: '', title: 'Archive'})
								),
								$T.div(
									$T.span({class: 'payment-method-label dd'},
										'BSB #'
									),
									$T.span(oBankAccount.BSB)
								),
								$T.div(
									$T.span({class: 'payment-method-label dd'},
										'Account #'
									),
									$T.span(oBankAccount.AccountNumber)
								),
								$T.div(
									$T.span({class: 'payment-method-label dd'},
										'Bank Name: '
									),
									$T.span(oBankAccount.BankName)
								),
								$T.div(
									$T.span({class: 'payment-method-label dd'},
										'Created: '
									),
									$T.span(oBankAccount.created_on)
								)
							)
						)
					)
				);
			}
			
			// Add Credit Cards to popup
			var oColCreditCards	= this.oContent.select('td.account-payment-methods-col-credit-cards > ul').first();
			var oCreditCards	= jQuery.json.arrayAsObject(oResponse.aPaymentMethods.credit_cards);
			var oCreditCard		= null;
			
			for (var i in oCreditCards)
			{
				oCreditCard		= oCreditCards[i];
				sValue			= Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD + '_' + oCreditCard.Id;
				
				// Determine if this is the current payment method
				oRadioAttributes	= {type: 'radio', name: 'account-payment-method', value: sValue, class: 'account-payment-method-radio'};
				
				if ((oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD) && (oResponse.iCreditCard == oCreditCard.Id))
				{
					oRadioAttributes.checked = true;
				}
				
				// Create a UL to show the payment method
				oColCreditCards.appendChild(
					$T.div({class: 'payment-method-item'},
						$T.ul({class: 'horizontal reset account-payment-method'},
							$T.li(
								$T.input(oRadioAttributes)
							),
							$T.li({class: 'account-payment-method-item-details'},
								$T.div(
									$T.span({class: 'payment-method-label cc'},
										'Name: '
									),
									$T.span(oCreditCard.Name),
									$T.img({class: 'archive-payment-method', src: Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE, alt: '', title: 'Archive'})
								),
								$T.div(
									$T.span({class: 'payment-method-label cc'},
										'Type: '
									),
									$T.span(oCreditCard.card_type_name)
								),
								$T.div(
									$T.span({class: 'payment-method-label cc'},
										'Number: '
									),
									$T.span(oCreditCard.card_number)
								),
								$T.div(
									$T.span({class: 'payment-method-label cc'},
										'CVV: '
									),
									$T.span(oCreditCard.cvv)
								),
								$T.div(
									$T.span({class: 'payment-method-label cc'},
										'Expires: '
									),
									$T.span({class: (oCreditCard.bExpired ? 'expired' : 'valid')},
										oCreditCard.expiry
									)
								)
							)
						)
					)
				);
			}
			
			// Select the current payment group
			var aPaymentGroupRadios			= this.oContent.select('ul.payment-method-group > li > input[type="radio"]');
			var oPaymentGroupInvoice		= aPaymentGroupRadios[0];
			var oPaymentGroupDirectDebit	= aPaymentGroupRadios[1];
			
			if (oResponse.iBillingType == Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE)
			{
				oPaymentGroupInvoice.checked 		= true;
				oPaymentGroupDirectDebit.checked 	= false;
				this._paymentGroupSelected(false);
			}
			else
			{
				oPaymentGroupInvoice.checked 		= false;
				oPaymentGroupDirectDebit.checked 	= true;
				this._paymentGroupSelected(true);
			}
		}
		else
		{
			// AJAX Error
			this._ajaxError(oResponse);
		}
	},
	
	_paymentMethodSelected	: function(oPaymentMethod)
	{
		var oRadio		= oPaymentMethod.select('input[type="radio"]').first();
		oRadio.checked 	= true;
	},
	
	_save	: function()
	{
		// Check all radio buttons, find the checked one and save it's value
		var aRadios			= this.oContent.select('input[type="radio"].account-payment-method-radio');
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
		else
		{
			Reflex_Popup.alert('Please select a payment method before saving.');
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
*/

var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		$super(60);
		
		this.iAccountId		= iAccountId;
		
		// Stores objects representing payment methods, hashed against the billing type
		this.hCachedMethods	= {};
		this.hCachedMethods[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]	= true;
		
		// Stores radio buttons & summary divs, hashed by the billing type they represent
		this.hBillingTypeRadios		= {};
		this.hBillingTypeSummaries	= {};
		
		this.iCurrentBillingType	= null;
		
		this.oLoading		= new Reflex_Popup.Loading('Please Wait');
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
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;
			
			// Update flags
			this.bHasCreditCard		= oResponse.bHasCreditCard;
			this.bHasBankAccount	= oResponse.bHasBankAccount;
			
			// No payment method returned, if Invoice is the current
			if (oResponse.oPaymentMethod)
			{
				// Update payment method cache
				this.hCachedMethods[oResponse.iBillingType]	= oResponse.oPaymentMethod; 	 
			}
			
			// Generate the payment method options and details elements
			this.oContent	= 	$T.div({class: 'payment-methods'},
									$T.div({class: 'section billing-types'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Payment Methods')
											)
										),
										$T.div({class: 'section-content'})
									),
									$T.div({class: 'section payment-method-details'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Details')
											)
										),
										$T.div({class: 'section-content'},
											$T.div({class: 'payment-method-details-description'}),
											$T.div({class: 'payment-method-details-info'})
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
			
			// Add a radio option for each billing type
			this._addBillingType(Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT);
			this._addBillingType(Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD);
			this._addBillingType(Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE);
			
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
		var oDom	= 	$T.div({class: 'billing-type'},
							$T.div({class: 'billing-type-option'},
								$T.input({type: 'radio', name: 'billing-type-option', value: iBillingType}),
								Popup_Account_Payment_Methods.BILLING_TYPE_NAME[iBillingType]
							),
							$T.div({class: 'billing-type-summary'}
								// .. Empty for now
							)
						);
		
		// Events
		var oRadio	= oDom.select('div.billing-type-option > input[type="radio"]').first();
		oRadio.observe('click', this._billingTypeSelected.bind(this, oRadio));
		
		// Attach element
		var oSectionContent	= this.oContent.select('div.section.billing-types > div.section-content').first();
		oSectionContent.appendChild(oDom);
		
		// Cache the radio
		this.hBillingTypeRadios[iBillingType]		= oRadio;
		this.hBillingTypeSummaries[iBillingType]	= oDom.select('div.billing-type-summary').first();;
	},
	
	_billingTypeSelected	: function(oRadio)
	{
		var iBillingType 	= parseInt(oRadio.value);
		
		// Clear the details
		this._clearDetails();
		
		// Show the billing type summary
		var oDiv	= null;
		
		for (var i in this.hBillingTypeSummaries)
		{
			oDiv	= this.hBillingTypeSummaries[i];
			
			if ((iBillingType == this.iCurrentBillingType) && (i == iBillingType))
			{
				oDiv.innerHTML		= Popup_Account_Payment_Methods._getPaymentMethodSummary(iBillingType, this.hCachedMethods[iBillingType]);
				oDiv.style.display	= 'block';
			}
			else
			{
				oDiv.style.display	= 'none';
			}
		}
		
		if (this.hCachedMethods[iBillingType])
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
						var fnShow	= function()
						{
							new Popup_Account_Select_Payment_Method(this.iAccountId, iBillingType, this._paymentMethodSelected.bind(this));
						}
						
						JsAutoLoader.loadScript('javascript/popup_account_select_payment_method.js', fnShow.bind(this));
					}
					else
					{
						// Show new bank account popup
						alert('new bank account');
					}
					break;
				case Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD:
					if (this.bHasCreditCard)
					{
						// Show select credit card popup
						var fnShow	= function()
						{
							new Popup_Account_Select_Payment_Method(this.iAccountId, iBillingType, this._paymentMethodSelected.bind(this));
						}
						
						JsAutoLoader.loadScript('javascript/popup_account_select_payment_method.js', fnShow.bind(this));
					}
					else
					{
						// Show new credit card popup
						alert('new credit card');
					}
					break;
			}
		}
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
	
	_paymentMethodSelected	: function(iBillingType, oPaymentMethod)
	{
		debugger;
	},
	
	_updateDetails	: function(iBillingType, oPaymentMethod)
	{
		// Update the details, description
		var oDescDiv		= this.oContent.select('div.payment-method-details > div.section-content > div.payment-method-details-description').first();
		oDescDiv.innerHTML	= Popup_Account_Payment_Methods.BILLING_TYPE_DESCRIPTION[iBillingType];
		
		// Update the details, info
		var oInfoDiv	= this.oContent.select('div.payment-method-details > div.section-content > div.payment-method-details-info').first();
		var oDom		= false;
		
		switch (iBillingType)
		{
			case Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT:
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
										'Created: '
									),
									$T.span({class: 'value'},
										oPaymentMethod.created_on
									)
								)
							);
				break;
			case Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD:
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
									$T.span({class: (oPaymentMethod.bExpired ? 'expired' : 'valid') + ' value'},
										oPaymentMethod.expiry
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
		var oDescDiv		= this.oContent.select('div.payment-method-details > div.section-content > div.payment-method-details-description').first();
		oDescDiv.innerHTML	= '';
		
		// Clear the details, info
		var oInfoDiv		= this.oContent.select('div.payment-method-details > div.section-content > div.payment-method-details-info').first();
		oInfoDiv.innerHTML	= '';
	},
	
	_save	: function()
	{
		
	}
});

// Image paths
Popup_Account_Payment_Methods.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Payment_Methods.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

// Billing types
Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT	= 1;
Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD	= 2;
Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE		= 3;

// Billing type info
var hName	= {};
hName[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]		= 'Invoice';
hName[Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD]	= 'Direct Debit via Credit Card';
hName[Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT]	= 'Direct Debit via Bank Transfer';
Popup_Account_Payment_Methods.BILLING_TYPE_NAME	= hName;

var hDescription	= {};
hDescription[Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT]	= 'Money owed is automatically transferred from the chosen bank account.';
hDescription[Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD]	= 'Money owed is automatically charge to the chosen credit card.';
hDescription[Popup_Account_Payment_Methods.BILLING_TYPE_INVOICE]		= 'Money owed is to be paid upon recieving the invoice.';
Popup_Account_Payment_Methods.BILLING_TYPE_DESCRIPTION	= hDescription;

Popup_Account_Payment_Methods._getPaymentMethodSummary	= function(iBillingType, oPaymentMethod)
{
	var sSummary	= '';
	
	switch (iBillingType)
	{
		case Popup_Account_Payment_Methods.BILLING_TYPE_DIRECT_DEBIT:
			sSummary	=	'Current: ' + 
							[
			        	  		oPaymentMethod.BankName, 
			        	  		oPaymentMethod.BSB + ' ' + oPaymentMethod.AccountNumber,
			        	  		oPaymentMethod.AccountName
			        	  	].join(' | ');
			break;
		case Popup_Account_Payment_Methods.BILLING_TYPE_CREDIT_CARD:
			sSummary	=	'Current: ' + 
							[
			        	  		oPaymentMethod.card_type_name, 
			        	  		oPaymentMethod.card_number,
			        	  		'Expires ' + oPaymentMethod.expiry
			        	  	].join(' | ');
			break;
	}
	
	return sSummary;
};

