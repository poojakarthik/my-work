
var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId)
	{
		this.iAccountId	= iAccountId;
		this.oLoading	= new Reflex_Popup.Loading('Please Wait');
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
				var oPaymentMethodsTemplate	=	$T.div({class: 'payment-methods reflex-popup-content'},
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
																	$T.input({type: 'radio', name: 'account-payment-method'}),
																	'Invoice'
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
													)
												);

				

				// Add Bank Accounts to popup
				var oColBankAccounts	= oPaymentMethodsTemplate.select('.account-payment-methods-col-bank-accounts > ul').first();
				var oBankAccounts		= jQuery.json.arrayAsObject(oResponse.aPaymentMethods.direct_debits);
				for (var i in oBankAccounts)
				{
					// oResponse.intSelectedPaymentMethod
					var oPaymentMethodDD	=	$T.ul({class: 'horizontal reset account-payment-method'},
													$T.li(
														$T.input(
															{type: 'radio', name: 'account-payment-method', value: String(oBankAccounts[i].Id)}
														)
													),
													$T.li(	
														$T.div('Account Name: ' + String(oBankAccounts[i].AccountName)),
														$T.div('BSB #' + String(oBankAccounts[i].BSB)+', Account #'+String(oBankAccounts[i].AccountNumber)),
														$T.div('Bank Name: ' + String(oBankAccounts[i].BankName)),
														$T.div('Created: ' + String(oBankAccounts[i].created_on))
													)
												);
					oColBankAccounts.appendChild(oPaymentMethodDD);
				}
				
				// Add Credit Cards to popup
				var oColCreditCards	= oPaymentMethodsTemplate.select('.account-payment-methods-col-credit-cards > ul').first();
				var oCreditCards	= jQuery.json.arrayAsObject(oResponse.aPaymentMethods.credit_cards);
				for (var i in oCreditCards)
				{
					// oResponse.intSelectedPaymentMethod
					var oPaymentMethodCC	=	$T.ul({class: 'horizontal reset account-payment-method'},
													$T.li(
														$T.input(
															{type: 'radio', name: 'account-payment-method', value: String(oCreditCards[i].Id)}
														)
													),
													$T.li(	
														$T.div('Name: ' + oCreditCards[i].Name),
														$T.div('Type: '+ oCreditCards[i].card_type_name),
														$T.div('Number: '+ oCreditCards[i].card_number),
														$T.div('CVV: '+ oCreditCards[i].cvv)
													)
												);
					oColCreditCards.appendChild(oPaymentMethodCC);
				}
								
				// Display Popup
				var oPopup = new Reflex_Popup(95);
				oPopup.setTitle("Change Payment Method");
				oPopup.addCloseButton();
				oPopup.setIcon("../admin/img/template/user_edit.png");
				oPopup.setContent(oPaymentMethodsTemplate);
				oPopup.domCloseButton = document.createElement('button');
				oPopup.domCloseButton.style.setProperty('padding-left', '20px', 'important');
				oPopup.domCloseButton.style.setProperty('padding-right', '20px', 'important');
				oPopup.domCloseButton.innerHTML = "OK";
				oPopup.domCloseButton.observe('click', oPopup.hide.bind(oPopup));
				oPopup.setFooterButtons([oPopup.domCloseButton], true);
				oPopup.display();
			}
			else
			{
				this._ajaxError(oResponse, true);
			}
		}
	},
	
	_addPaymentMethod	: function()
	{
		
	},
	
	_ajaxError	: function(oResponse, bHideOnClose)
	{
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

