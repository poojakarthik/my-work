
var Popup_Account_Payment_Reverse = Class.create(Reflex_Popup, 
{
	initialize : function($super, iPaymentId, fnOnComplete)
	{
		$super(45);
		
		this._iPaymentId = iPaymentId;
		this._fnOnComplete	= fnOnComplete;
		
		this._buildUI();
	},
	
	_buildUI : function(oPayment)
	{
		if (Object.isUndefined(oPayment))
		{
			Popup_Account_Payment_Reverse._getPayment(this._iPaymentId, this._buildUI.bind(this));
			return;
		}
		
		var oReasonControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Reason',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: Popup_Account_Payment_Reverse._getReasonOptions
									}
								);
		oReasonControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oReasonControl.addOnChangeCallback(this._reasonChange.bind(this));
		this._oReasonControl = oReasonControl;
		
		var oReplacementControl = 	Control_Field.factory(
										'checkbox',
										{
											sLabel		: 'Replace Payment',
											mEditable	: true,
											sExtraClass	: 'popup-account-payment-reverse-replacement-payment'
										}
									);
		oReplacementControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oReplacementControl.addOnChangeCallback(this._replacementPaymentChange.bind(this));
		this._oReplacementControl = oReplacementControl;
		
		var oPaymentComponent =	new Component_Account_Payment_Create(
									oPayment.account_id, 
									Component_Account_Payment_Create.SAVE_MODE_CALLBACK_WITH_DETAILS,
									this._reversePayment.bind(this)
								);
		this._oPaymentComponent = oPaymentComponent;
		
		var oContentDiv =	$T.div({class: 'popup-account-payment-reverse'},
								$T.div('Please choose a reason why the payment is being reversed.'),
								oReasonControl.getElement(),
								$T.div({class: 'popup-account-payment-reverse-replacement-payment-container'},
									oReplacementControl.getElement(),
									$T.span('Create a replacement Payment'),
									oPaymentComponent.getElement()
								),
								$T.div({class: 'popup-account-payment-reverse-buttons'},
									$T.button('Reverse Payment').observe('click', this._doReverse.bind(this, null)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		
		this._oReplacementPaymentContainer = oContentDiv.select('.popup-account-payment-reverse-replacement-payment-container').first();
		this._oReplacementPaymentContainer.hide();
		
		this.setTitle('Reverse Payment');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_reasonChange : function()
	{
		var oReason = Popup_Account_Payment_Reverse._hReversalReasons[this._oReasonControl.getElementValue()];
		if (oReason && oReason.system_name == 'AGENT_REVERSAL')
		{
			this._oReplacementControl.setValue(false);
			this._replacementPaymentChange();
			this._oReplacementPaymentContainer.show();
		}
		else
		{
			this._oReplacementPaymentContainer.hide();
		}
	},
	
	_replacementPaymentChange : function()
	{
		if (this._oReplacementControl.getElementValue())
		{
			this._oPaymentComponent.getElement().show();
		}
		else
		{
			this._oPaymentComponent.getElement().hide();
		}
	},
	
	_doReverse : function()
	{
		if (this._oReplacementControl.getElementValue())
		{
			// Get the payment details
			this._oPaymentComponent.save();
		}
		else
		{
			// No replacement payment
			this._reversePayment(null);
		}
	},
	
	_reversePayment : function(oReplacementPaymentDetails, oResponse)
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
			
			this._oLoading = new Reflex_Popup.Loading('Reversing Payment...');
			this._oLoading.display();
			
			// Request
			var fnResp	= this._reversePayment.bind(this, oReplacementPaymentDetails);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Payment', 'reversePayment');
			fnReq(this._iPaymentId, iReasonId, oReplacementPaymentDetails);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Payment_Reverse._ajaxError(oResponse);
			return;
		}
		
		Reflex_Popup.alert('Payment Reversed');
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	},
	
	_fnPaymentSaved : function()
	{
		alert('TODO');
	}
});

Object.extend(Popup_Account_Payment_Reverse, 
{
	_hReversalReasons : {},
	
	_getPayment : function(iPaymentId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_Payment_Reverse._getPayment.curry(iPaymentId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Payment', 'getForId');
			fnReq(iPaymentId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Payment_Reverse._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.oPayment);
		}
	},
	
	_getReasonOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Account_Payment_Reverse._getReasonOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Payment_Reversal_Reason', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Payment_Reverse._ajaxError(oResponse);
			return;
		}
		
		Popup_Account_Payment_Reverse._hReversalReasons = {};
		
		// Create options and callback
		var aOptions = [];
		for (var i in oResponse.aReasons)
		{
			aOptions.push(
				$T.option({value: i},
					oResponse.aReasons[i].name	
				)	
			);
			
			Popup_Account_Payment_Reverse._hReversalReasons[i] = oResponse.aReasons[i];
		}
		
		fnCallback(aOptions);
	},
	
	_ajaxError : function(oResponse)
	{
		Reflex_Popup.alert(
			oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error'}
		);
	}
});