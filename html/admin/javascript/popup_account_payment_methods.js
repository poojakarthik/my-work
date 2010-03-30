var Popup_Account_Payment_Methods	= Class.create(Reflex_Popup,
{
	
	initialize	: function($super, iAccountId)
	{
	
		//----------------------------------------------------------------//
		// Retrieve Data required to build the page
		//----------------------------------------------------------------//
		
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 0);
	
		// (jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this), alert('fail'), 'Account', 'getPaymentMethods'))(iAccountId);
		this._getPaymentMethods			= jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this), null, 'Account', 'getPaymentMethods');
		this._getPaymentMethods(iAccountId);
		
	},
	
	
	displayPopupReturnHandler	: function(response)
	{

		//----------------------------------------------------------------//
		// Display Change Payment Methods
		//----------------------------------------------------------------//
		
		Vixen.Popup.ClosePageLoadingSplash();
		
		if (response.Success && response.Success == true)
		{	

			// Popup Template
			var oPaymentMethodsTemplate		=	$T.div(
													{class: 'payment-methods reflex-popup-content'},
													$T.table(
															{class: 'form-layout reflex'},
															$T.caption(
																$T.div(
																		{class: 'caption_bar', id: 'caption_bar'},
																		$T.div(
																				{class: 'caption_title', id: 'caption_title'},
																				'Payment Methods'
																		)
																)
															),
															$T.thead(),
															$T.tbody(
																$T.tr(
																		{class: 'account-payment-methods-titles'},
																		$T.td(''),
																		$T.td('Credit Cards'),
																		$T.td('Bank Accounts')
																),
																$T.tr(
																		$T.td({class: 'account-payment-methods-col-other'}),
																		$T.td({class: 'account-payment-methods-col-credit-cards'}),
																		$T.td({class: 'account-payment-methods-col-bank-accounts'})
																)
															),
															$T.tfoot()
													)
												);
				
			
			// Populate Popup Template with Payment Methods
			var oAccountPaymentMethodsColBankAccounts = oPaymentMethodsTemplate.select('.account-payment-methods-col-bank-accounts').first();
			var oBankAccounts	= jQuery.json.arrayAsObject(response.arrPaymentMethods.direct_debits);
			for(var i in oBankAccounts)
			{
				// response.intSelectedPaymentMethod
				var oPaymentMethod =	$T.table({class: 'account-payment-methods'},
											$T.tr(
													$T.td(
															$T.input(
																	{type: 'radio'}
															)
													),
													$T.td(
															
															$T.table(
																	$T.tr(
																			$T.td(		
																				$T.div('Account Name: '+String(oBankAccounts[i].AccountName)),
																				$T.div('BSB #'+String(oBankAccounts[i].BSB)+', Account #'+String(oBankAccounts[i].AccountNumber)),
																				$T.div('Bank Name: '+String(oBankAccounts[i].BankName)),
																				$T.div('Created: '+String(oBankAccounts[i].created_on))
																			)
																	)
															)
													)
												)
										)
				oAccountPaymentMethodsColBankAccounts.appendChild(oPaymentMethod);
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

		}

	},
	
	addPaymentMethod	: function()
	{
		
	},
	
});

