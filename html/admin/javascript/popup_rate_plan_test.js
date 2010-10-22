
var Popup_Rate_Plan_Test	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, iRatePlanId)
	{
		$super(35);
		
		this._showLoading();
	
		this._iRatePlanId	= iRatePlanId;
		this._oRatePlan		= null;
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oRatePlan)
	{
		if (typeof oRatePlan == 'undefined')
		{
			Flex.Plan.getForId(this._iRatePlanId, this._buildUI.bind(this));
			return;
		}
		
		this._oRatePlan	= oRatePlan;
		this._hideLoading();
		
		// Create control fields
		var oAccountField	= Control_Field.factory('text_ajax', Popup_Rate_Plan_Test.ACCOUNT_FIELD);
		oAccountField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oAccountField.addOnSelectCallback(this._accountSelected.bind(this));
		this._oAccountField	= oAccountField;
		
		var oInvoicesField		= $T.select({size: Popup_Rate_Plan_Test.INVOICES_SIZE, class: 'popup-rate-plan-test-invoices'});
		this._oInvoicesField	= oInvoicesField;
		
		// Modify the filter fields of the Text_AJAX field so that the customer group is filtered
		var oFilter	= oAccountField.getFilter();
		oFilter.addFilter('customer_group', {iType: Filter.FILTER_TYPE_VALUE});
		oFilter.setFilterValue('customer_group', this._oRatePlan.customer_group);
		
		// Create the content div
		var	oContent	=	$T.div({class: 'popup-rate-plan-test'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Account'),
											$T.td(oAccountField.getElement())
										),
										$T.tr(
											$T.th('Choose Invoice'),
											$T.td(oInvoicesField)
										)
									)
								),
								$T.div({class: 'buttons'}, 
									$T.button({class: 'icon-button'}, 
										'Rerate Invoice'
									).observe('click', this._rerate.bind(this)),
									$T.button({class: 'icon-button'}, 
										'Cancel'
									).observe('click', this.hide.bind(this))
								)
							);
		
		// Configure popup & display
		this.setContent(oContent);
		this.setTitle('Test Rate Plan');
		this.addCloseButton();
		this.display();
	},
		
	_accountSelected	: function()
	{
		var oAccount	= this._oAccountField.getValue();
		this._doInvoiceSearch(oAccount.Id);
	},
	
	_rerate	: function()
	{
		var sInvoice	= this._oInvoicesField.value;
		if (!sInvoice || (sInvoice !== ''))
		{
			Reflex_Popup.alert('Please choose an invoice to rerate.', {iWidth: 25});
			return;
		}
		
		new Popup_Invoice_Rerate(parseInt(sInvoice), this._oRatePlan.Id);
	},
	
	_doInvoiceSearch	: function(sAccount, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get invoices
			if (sAccount === null)
			{
				return;
			}
			
			this._showLoading();
			var fnHandler	= this._doInvoiceSearch.bind(this, sAccount);
			var fnSearch	= jQuery.json.jsonFunction(fnHandler, fnHandler, 'Invoice', 'getReratableInvoicesForAccount');
			fnSearch(sAccount);
		}
		else
		{
			// Got response
			this._hideLoading();
			
			if (!oResponse.bSuccess)
			{
				// Error, show message
				Popup_Rate_Plan_Test._ajaxError(oResponse);
				return;
			}
			
			// Clear list
			while (this._oInvoicesField.options.length)
			{
				this._oInvoicesField.options[0].remove();
			}
			
			// Add results to list
			for (var i = 0; i < oResponse.aResults.length; i++)
			{
				var oResult	= 	oResponse.aResults[i];
				var oOption	=	$T.option({value: oResult.Id},
									oResult.Id + ' - ' + oResult.CreatedOn
								);
				
				this._oInvoicesField.add(oOption, null);
			}
		}
	},
	
	_showLoading	: function(sMessage)
	{
		if (!this._oLoading)
		{
			this._oLoading	= new Reflex_Popup.Loading(sMessage);
		}
		this._oLoading.display();
	},
	
	_hideLoading	: function()
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
	}
});

// Static

Object.extend(Popup_Rate_Plan_Test, 
{
	INVOICES_SIZE			: 5,
	ACCOUNT_SEARCH_LIMIT	: 10,
});

Object.extend(Popup_Rate_Plan_Test, 
{
	ACCOUNT_FIELD	: 
	{
		sLabel						: 'Account',
		mMandatory					: false,
		mEditable					: true,
		bVisible					: true,
		bDisableValidationStyling	: true,
		oDatasetAjax				: new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {sObject: 'Account', sMethod: 'searchCustomerGroup'}),
		sDisplayValueProperty		: 'BusinessName',
		iResultLimit				: 10,
		oColumnProperties 			:
		{
			BusinessName	: {sClass: 'popup-rate-plan-test-account-business-name'},
			Id				: {}
		}
	},
	
	_ajaxError	: function(oResponse)
	{
		var oConfig	= {sTitle: 'Error'};
		if (oResponse.sMessage)
		{
			// Exception/Error message
			Reflex_Popup.alert(oResponse.sMessage, oConfig);
		}
		else if (oResponse.ERROR)
		{
			// System error, not thrown by handler code
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}		
	},
});
