
var Popup_Account_Adjustment_Reverse = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAdjustmentId, fnOnComplete)
	{
		$super(35);
		
		this._iAdjustmentId = iAdjustmentId;
		this._fnOnComplete	= fnOnComplete;
		
		this._buildUI();
	},
	
	_buildUI : function(oReversalData)
	{
		if (Object.isUndefined(oReversalData))
		{
			Popup_Account_Adjustment_Reverse._getReversalInformation(this._iAdjustmentId, this._buildUI.bind(this));
			return;
		}
		
		var oReasonControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Reason',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: Popup_Account_Adjustment_Reverse._getReasonOptions
									}
								);
		oReasonControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonControl = oReasonControl;
		
		var oContentDiv 		=	$T.div({class: 'popup-account-adjustment-reverse'},
										$T.div('Please choose a reason why the adjustment is being reversed.'),
										oReasonControl.getElement(),
										$T.div({class: 'popup-account-adjustment-reverse-buttons'},
											$T.button('Reverse Adjustment').observe('click', this._reverseAdjustment.bind(this, null)),
											$T.button('Cancel').observe('click', this.hide.bind(this))
										)
									);
		
		this.setTitle('Reverse Adjustment');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_reverseAdjustment : function(oResponse, oEvent)
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
			
			this._oLoading = new Reflex_Popup.Loading('Reversing Adjustment...');
			this._oLoading.display();
			
			// Request
			var fnResp	= this._reverseAdjustment.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment', 'reverseAdjustment');
			fnReq(this._iAdjustmentId, iReasonId);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Adjustment_Reverse._ajaxError(oResponse);
			return;
		}
		
		Reflex_Popup.alert('Adjustment Reversed');
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	}
});

Object.extend(Popup_Account_Adjustment_Reverse, 
{
	_getReversalInformation : function(iAdjustmentId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_Adjustment_Reverse._getReversalInformation.curry(iAdjustmentId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment', 'getReversalInformation');
			fnReq(iAdjustmentId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Adjustment_Reverse._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.oReversalData);
		}
	},
	
	_getReasonOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_Adjustment_Reverse._getReasonOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment_Reversal_Reason', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Adjustment_Reverse._ajaxError(oResponse);
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