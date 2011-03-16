
var Popup_Account_TIO_Complaint_Close = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAccountId, fnOnComplete)
	{
		$super(30);
		
		this._iAccountId 	= iAccountId;
		this._fnOnComplete	= fnOnComplete;
		
		this._buildUI();
	},
	
	_buildUI : function(oComplaint)
	{
		if (Object.isUndefined(oComplaint))
		{
			Popup_Account_TIO_Complaint_Close._getComplaintDetails(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		
		if (!oComplaint)
		{
			Reflex_Popup.alert('This Account has no active TIO Complaint.', {iWidth: 30});
			return;
		}
		
		this._oComplaint = oComplaint;
		
		var oReasonControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Closure Reason',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: Popup_Account_TIO_Complaint_Close._getEndReasonOptions.curry(this._oComplaint.collection_suspension.id)
									}
								);
		oReasonControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonControl = oReasonControl;
		
		var oContentDiv	=	$T.div({class: 'popup-account-tio-complaint-close'},
								$T.div('Please choose a reason why the TIO Complaint is being closed.'),
								oReasonControl.getElement(),
								$T.div({class: 'popup-account-tio-complaint-close-buttons'},
									$T.button('Close Complaint').observe('click', this._doEndComplaint.bind(this)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle(this._oComplaint.account_id + ': Close TIO Complaint');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_doEndComplaint : function()
	{
		Reflex_Popup.yesNoCancel(
			'After the complaint has been closed. What would you like to do?', 
			{
				sYesLabel		: 'Create a Promise to Pay', 
				sNoLabel		: 'Suspend Account from Collections', 
				sCancelLabel	: 'Nothing',
				fnOnYes			: this._endComplaint.bind(this, Popup_Account_TIO_Complaint_Close.CLOSE_ACTION_CREATE_PROMISE),
				fnOnNo			: this._endComplaint.bind(this, Popup_Account_TIO_Complaint_Close.CLOSE_ACTION_CREATE_SUSPENSION),
				fnOnCancel		: this._endComplaint.bind(this, Popup_Account_TIO_Complaint_Close.CLOSE_ACTION_NOTHING)
			}
		);
	},
	
	_endComplaint : function(iCloseAction, oResponse)
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
				Reflex_Popup.alert('Please choose a Reason before continuing.', {sTitle: 'Error'});
				return;
			}
			
			// Check if the request needs to be bundled up with another task
			switch (iCloseAction)
			{
				case Popup_Account_TIO_Complaint_Close.CLOSE_ACTION_CREATE_PROMISE:
					new Popup_Account_Promise_Edit(
						this._iAccountId,
						{
							oSuspension: 
							{
								id                                  : this._oComplaint.collection_suspension_id,
								collection_suspension_end_reason_id	: iReasonId
							}
						}
					);
					return;
				case Popup_Account_TIO_Complaint_Close.CLOSE_ACTION_CREATE_SUSPENSION:
					new Popup_Account_Suspend_From_Collections(
						this._iAccountId, 
						this._complete.bind(this), 
						{iTIOComplaintId: this._oComplaint.id, iEndReasonId: iReasonId}
					);
					return;
			}
			
			this._oLoading = new Reflex_Popup.Loading('Closing TIO Complaint...');
			this._oLoading.display();
			
			// Request
			var fnResp	= this._endComplaint.bind(this, iCloseAction);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account_TIO_Complaint', 'endComplaint');
			fnReq(this._oComplaint.id, iReasonId);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_TIO_Complaint_Close._ajaxError(oResponse);
			return;
		}
		
		this._complete();
	},
	
	_complete : function()
	{
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	}
});

Object.extend(Popup_Account_TIO_Complaint_Close, 
{
	CLOSE_ACTION_CREATE_PROMISE 	: 1,
	CLOSE_ACTION_CREATE_SUSPENSION 	: 2,
	CLOSE_ACTION_NOTHING			: 3,
	
	_getComplaintDetails : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_TIO_Complaint_Close._getComplaintDetails.curry(iAccountId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account_TIO_Complaint', 'getExtendedComplaintDetailsForAccount');
			fnReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_TIO_Complaint_Close._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.oComplaint);
		}
	},
	
	_getEndReasonOptions : function(iSuspensionId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_TIO_Complaint_Close._getEndReasonOptions.curry(iSuspensionId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension_End_Reason', 'getAllForSuspension');
			fnReq(iSuspensionId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_TIO_Complaint_Close._ajaxError(oResponse);
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