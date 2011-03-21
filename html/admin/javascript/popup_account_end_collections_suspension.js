
var Popup_Account_End_Collections_Suspension = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAccountId, fnOnComplete)
	{
		$super(35);
		
		this._iAccountId 	= iAccountId;
		this._fnOnComplete	= fnOnComplete;
		
		this._buildUI();
	},
	
	_buildUI : function(oSuspension, oReason)
	{
		if (Object.isUndefined(oSuspension))
		{
			Popup_Account_End_Collections_Suspension._getSuspensionDetails(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		
		if (!oSuspension)
		{
			Reflex_Popup.alert('This Account has not been suspended from Collections.', {iWidth: 30});
			return;
		}
		
		if (oReason.system_name == 'TIO_COMPLAINT')
		{
			Reflex_Popup.yesNoCancel(
				'This Account is part of TIO Complaint. Would you like to view the details of the complaint?', 
				{fnOnYes: this._showTIOComplaint.bind(this)}
			);
			return;
		}
		
		this._oSuspension = oSuspension;
		
		var oReasonControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'End Reason',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: Popup_Account_End_Collections_Suspension._getEndReasonOptions.curry(oSuspension.id)
									}
								);
		oReasonControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonControl = oReasonControl;
		
		var sProposedEndDate	= 	Date.$parseDate(oSuspension.proposed_end_datetime, 'Y-m-d H:i:s').$format('l jS M, g:i A');
		var oContentDiv 		=	$T.div({class: 'popup-account-end-collections-suspension'},
										$T.div('This Account is suspended until: ' + sProposedEndDate),
										$T.div('Please choose a reason why the suspension is being stopped.'),
										oReasonControl.getElement(),
										$T.div({class: 'popup-account-end-collections-suspension-buttons'},
											$T.button('End Suspension').observe('click', this._endSuspension.bind(this, null)),
											$T.button('Cancel').observe('click', this.hide.bind(this))
										)
									);
		
		this.setTitle('End Collections Suspension for Account ' + oSuspension.account_id);
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_endSuspension : function(oResponse, oEvent)
	{
		if (!oResponse)
		{
			// Validate reason
			try
			{
				this._oReasonControl.validate(false);
				var iReasonId = this._oReasonControl.getValue(true);
			}
			catch (oEx)
			{
				Reflex_Popup.alert('Please choose an End Reason before continuing.', {sTitle: 'Error'});
				return;
			}
			
			this._oLoading = new Reflex_Popup.Loading('Ending Suspension...');
			this._oLoading.display();
			
			// Request
			var fnResp	= this._endSuspension.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'endSuspension');
			fnReq(this._oSuspension.id, iReasonId);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_End_Collections_Suspension._ajaxError(oResponse);
			return;
		}
		
		Reflex_Popup.alert('Suspension ended');
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
		
		if (Vixen && Vixen.AccountDetails)
		{
			Vixen.AccountDetails.CancelEdit();
		}
		
		if (Component_Account_Collections)
		{
			Component_Account_Collections.refreshInstances();
		}
	},
	
	_showTIOComplaint : function()
	{
		new Popup_Account_TIO_Complaint_View(this._iAccountId, this._tioComplaintEnded.bind(this)); 
	},
	
	_tioComplaintEnded : function()
	{
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	}
});

Object.extend(Popup_Account_End_Collections_Suspension, 
{
	_getSuspensionDetails : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_End_Collections_Suspension._getSuspensionDetails.curry(iAccountId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'getSuspensionAvailabilityInfo');
			fnReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_End_Collections_Suspension._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.oSuspension, oResponse.oReason);
		}
	},
	
	_getEndReasonOptions : function(iSuspensionId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_End_Collections_Suspension._getEndReasonOptions.curry(iSuspensionId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension_End_Reason', 'getAllForSuspension');
			fnReq(iSuspensionId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_End_Collections_Suspension._ajaxError(oResponse);
			return;
		}
		
		// Create options and callback
		var aOptions = [];
		for (var i in oResponse.aReasons)
		{
			aOptions.push(
				$T.option({value: i},
					oResponse.aReasons[i].name	
				)	
			);
		}
		
		fnCallback(aOptions);
	},
	
	_ajaxError : function()
	{
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error'}
		);
	}
});