
var Popup_Account_Promise_Cancel = Class.create(Reflex_Popup,
{
	initialize : function($super, iAccountId, fnOnComplete)
	{
		$super(45);
		
		this._iAccountId	= iAccountId;
		this._fnOnComplete	= fnOnComplete;
		this._aControls 	= [];
		
		this._buildUI();
	},
	
	_buildUI : function()
	{
		var oContentDiv = 	$T.div({class: 'popup-account-promise-cancel'},
								$T.div(
									$T.span('Please confirm that you wish to cancel the Promise to Pay and move the Account to the '),
									$T.span({class: 'popup-account-promise-cancel-scenario-label'},
										'Broken Promise to Pay Collection Scenario'
									),
									$T.span('?')
								),
								$T.div({class: 'popup-account-promise-cancel-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Cancel & Change Scenario')
									).observe('click', this._doCancel.bind(this, true)),
									$T.button({class: 'icon-button'},
										$T.span("Cancel Only")
									).observe('click', this._doCancel.bind(this, false)),
									$T.button({class: 'icon-button'},
										$T.span('Replace')
									).observe('click', this._doReplace.bind(this))
								)
							);
		
		this.setTitle(this._iAccountId + ': Cancel Promise to Pay');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_doCancel : function(bChangeScenario)
	{
		this._cancel(bChangeScenario);
	},
	
	_doReplace : function()
	{
		this.hide();
		this._refreshRelatedComponents();
		new Popup_Account_Promise_Edit(this._iAccountId);
	},
	
	_cancel : function(bChangeScenario, oResponse)
	{
		if (!oResponse)
		{
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._cancel.bind(this, bChangeScenario);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Promise', 'cancelPromiseForAccount');
			fnReq(this._iAccountId, bChangeScenario);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Account_Promise_Cancel._ajaxError(oResponse);
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iRecordId);
		}
		this._refreshRelatedComponents();
	},
	
	_refreshRelatedComponents : function()
	{
		if (typeof Component_Account_Collections != 'undefined')
		{
			Component_Account_Collections.refreshInstances();
		}
		
		if (typeof Vixen.AccountDetails != 'undefined')
		{
			Vixen.AccountDetails.CancelEdit();
		}
	}
});

Object.extend(Popup_Account_Promise_Cancel, 
{	
	_ajaxError : function(oResponse, sMessage)
	{
		// Exception
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error', sDebugContent: oResponse.sDebug}
		);
	}
});