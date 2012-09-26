
var Popup_Adjustment_Request = Class.create(Reflex_Popup,
{
	initialize : function($super, iAccountId, iServiceId, fOverrideAmount, iInvoiceId, iAdjustmentTypeId, fnOnComplete)
	{
		$super(35);
		
		// Parameters
		this._iAccountId 		= iAccountId;
		this._iServiceId 		= (Object.isUndefined(iServiceId) 			? null : iServiceId);
		this._fOverrideAmount	= (Object.isUndefined(fOverrideAmount) 		? null : fOverrideAmount);
		this._iInvoiceId		= (Object.isUndefined(iInvoiceId) 			? null : iInvoiceId);
		this._iAdjustmentTypeId	= (Object.isUndefined(iAdjustmentTypeId)	? null : iAdjustmentTypeId);
		this._fnOnComplete		= fnOnComplete;
		
		// State
		this._aControls 		= [];
		this._bAmountMandatory 	= true;
		this._oTaxType			= null;
		
		this._oLoading = new Reflex_Popup.Loading();
		this._oLoading.display();
		
		Flex.Constant.loadConstantGroup(Popup_Adjustment_Request.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function() {		
		var oContentDiv = $T.div({class: 'popup-adjustment-request'},
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th('Account Number'),
						$T.td({'class': 'popup-adjustment-request-accountid'})
					),
					$T.tr(
						$T.th('Business Name'),
						$T.td({'class': 'popup-adjustment-request-accountbusinessname'})
					),
					$T.tr(
						$T.th('Adjustment'),
						$T.td(
							this._createControl(
								'select', 
								'Adjustment', 
								true, 
								Popup_Adjustment_Request._getAdjustmentTypeOptions,
								this._iAdjustmentTypeId,
								this._adjustmentTypeChange.bind(this)
							).getElement()
						)
					),
					$T.tr(
						$T.th('Adjustment Type'),
						$T.td({class: 'popup-adjustment-request-adjustment-type-code'},
							'-'
						)
					),
					$T.tr(
						$T.th('Description'),
						$T.td({class: 'popup-adjustment-request-adjustment-type-description'},
							'-'
						)
					),
					$T.tr(
						$T.th('Nature'),
						$T.td({class: 'popup-adjustment-request-adjustment-type-nature'},
							'-'
						)
					),
					$T.tr(
						$T.th({'class': 'popup-adjustment-request-amountlabel'}),
						$T.td(
							this._createControl(
								'text', 
								'Amount', 
								this._isAmountRequired.bind(this), 
								Reflex_Validation.float,
								this._fOverrideAmount,
								this._amountChange.bind(this)
							).getElement()
						)
					),
					$T.tr(
						$T.th({'class': 'popup-adjustment-request-amount-exgstlabel'}),
						$T.td({class: 'popup-adjustment-request-amount-exgst'},
							'-'
						)
					),
					$T.tr(
						$T.th({class: 'popup-adjustment-request-amount-taxlabel'}),
						$T.td({class: 'popup-adjustment-request-amount-tax'},
							'-'
						)
					),
					$T.tr(
						$T.th('Invoice'),
						$T.td(
							this._createControl(
								'select', 
								'Invoice', 
								false, 
								Popup_Adjustment_Request._getInvoiceOptionsForAccount.curry(this._iAccountId),
								this._iInvoiceId,
								null
							).getElement())
					),
					$T.tr(
						$T.th('Note'),
						$T.td(
							new Control_Textarea({
								sExtraClass		: 'popup-adjustment-request-note',
								sName			: 'Note',
								iControlState	: Control.STATE_ENABLED,
								fnValidate		: function (oControl) {
									// Ensure that it doesn't exceed our storage capacity
									var	sValue	= String(oControl.getValue()),
										iLength	= sValue.length;
									if (iLength > 1000) {
										throw "Exceeded maximum length of 1000 characters (currently "+iLength+")";
									}
									return true;
								}
							})
						)
					)
				)
			),
			$T.div({class: 'popup-adjustment-request-buttons'},
				$T.button({class: 'icon-button'},
					$T.img({src: '../admin/img/template/approve.png'}),
					$T.span('Save')
				).observe('click', this._doSave.bind(this)),
				$T.button({class: 'icon-button'},
					$T.span('Cancel')
				).observe('click', this.hide.bind(this))
			)
		);
		
		this._oAdjustmentTypeCode = oContentDiv.select('.popup-adjustment-request-adjustment-type-code').first();
		this._oAdjustmentTypeDescription = oContentDiv.select('.popup-adjustment-request-adjustment-type-description').first();
		this._oAdjustmentTypeNature = oContentDiv.select('.popup-adjustment-request-adjustment-type-nature').first();
		this._oAmountExTax = oContentDiv.select('.popup-adjustment-request-amount-exgst').first();
		this._oAmountTax = oContentDiv.select('.popup-adjustment-request-amount-tax').first();		
		this._oNote	= oContentDiv.select('.popup-adjustment-request-note').first().oReflexComponent;

		this.setTitle('Request Adjustment');
		this.addCloseButton();
		this.setContent(oContentDiv);
		
		this._finaliseUI();

	},

	_finaliseUI : function(oAccount, oTaxType) {
		if (!oAccount) {
			Flex.Account.getForId(this._iAccountId, this._finaliseUI.bind(this));
			return;
		}
		
		if (typeof oTaxType == 'undefined') {
			Popup_Adjustment_Request._getGlobalTaxType(this._finaliseUI.bind(this, oAccount));
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;

		if (!oTaxType) {
			Reflex_Popup.alert("No Global Tax Type has been configured. Adjustments cannot be requested.");
			return;
		}

		this._oTaxType = oTaxType;

		this.contentPane.select('.popup-adjustment-request-amountlabel')[0].innerHTML = 'Amount ($ inc. ' + this._oTaxType.name + ')';
		this.contentPane.select('.popup-adjustment-request-amount-exgstlabel')[0].innerHTML = 'Amount ($ ex. ' + this._oTaxType.name + ')';
		this.contentPane.select('.popup-adjustment-request-amount-taxlabel')[0].innerHTML = this._oTaxType.name;

		this.contentPane.select('.popup-adjustment-request-accountid')[0].innerHTML = oAccount.Id;
		this.contentPane.select('.popup-adjustment-request-accountbusinessname')[0].innerHTML = oAccount.BusinessName;

		this.display();

		// Force clear the adjustment type select because of chromes auto-selecting the first element bug
		if (this._aControls[0].oControlOutput.oEdit.selectedIndex !== -1) {
			this._aControls[0].oControlOutput.oEdit.selectedIndex = -1;
		}
	},
	
	_createControl : function(sType, sLabel, mMandatory)
	{
		var aArgs = $A(arguments);
		aArgs.shift();
		aArgs.shift();
		aArgs.shift();
		
		var oDefinition = {sLabel: sLabel, mEditable: true, mMandatory: mMandatory};
		var fnOnChange	= null;
		var mValue		= null;
		switch (sType)
		{
			case 'text':
				oDefinition.fnValidate 	= aArgs[0];
				mValue					= aArgs[1];
				fnOnChange				= aArgs[2];
				break;
			case 'select':
				oDefinition.fnPopulate 	= aArgs[0];
				mValue					= aArgs[1];
				fnOnChange				= aArgs[2];
				break;
		}
		
		var oControl = Control_Field.factory(sType, oDefinition);
		oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		if (fnOnChange)
		{
			// Add on change callback, adding the control as a parameter
			oControl.addOnChangeCallback(fnOnChange.curry(oControl));
		}
		
		if (mValue !== null)
		{
			oControl.setValue(mValue);
		}
		
		this._aControls.push(oControl);
		return oControl;
	},
	
	_isAmountRequired : function()
	{
		return this._bAmountMandatory;
	},
	
	_adjustmentTypeChange : function(oControl)
	{
		var oType = Popup_Adjustment_Request._hAdjustmentTypes[oControl.getElementValue()];
		if (oType)
		{
			this._oAdjustmentTypeCode.innerHTML 		= oType.code;
			this._oAdjustmentTypeDescription.innerHTML 	= oType.description;
			this._oAdjustmentTypeNature.innerHTML 		= oType.transaction_nature_code;
			
			var oAmountControl = this._aControls[1];
			if (oType.is_amount_fixed)
			{
				oAmountControl.disableInput();
				this._bAmountMandatory = false;
			}
			else
			{
				oAmountControl.enableInput();
				this._bAmountMandatory = true;
			}
			
			oAmountControl.setValue(oType.amount);
			this._amountChange(oAmountControl);
		}
	},
	
	_amountChange : function(oControl)
	{
		var fAmount 		= parseFloat(oControl.getElementValue());
		var fTaxDivisor		= 1 + (this._oTaxType ? parseFloat(this._oTaxType.rate_percentage) : 0);
		var fTaxComponent	= fAmount - (fAmount / fTaxDivisor);
		fTaxComponent		= (isNaN(fTaxComponent) ? 0 : fTaxComponent);
		
		var fExTax = fAmount - fTaxComponent;
		if (isNaN(fExTax)) {
			fExTax = 0;
		}

		this._oAmountExTax.innerHTML = new Number(fExTax).toFixed(2);
		this._oAmountTax.innerHTML = new Number(fTaxComponent).toFixed(2);
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aControls.length; i++)
			{
				try
				{
					this._aControls[i].validate(false);
					this._aControls[i].save(true);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}

			// Also validate the Note (it validates differently, being a newer Control)
			var	mException;
			try {
				this._oNote.validate(false);
			} catch (mException) {
				aErrors.push(mException);
			}
			
			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Popup_Adjustment_Request._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				adjustment_type_id 	: parseInt(this._aControls[0].getValue()),
				amount				: parseFloat(this._aControls[1].getValue()),
				invoice_id			: parseInt(this._aControls[2].getValue()),
				account_id			: this._iAccountId, 
				service_id			: this._iServiceId,
				note				: this._oNote.getValue()
			};
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment', 'createAdjustment');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Adjustment_Request._ajaxError(oResponse, 'Could not save the Adjustment Request');
			return;
		}
		
		Reflex_Popup.alert('Adjustment Requested');
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iAdjustmentId);
		}
	}
});

Object.extend(Popup_Adjustment_Request, 
{
	REQUIRED_CONSTANT_GROUPS : ['status', 'transaction_nature'],
	
	_hAdjustmentTypes : {},
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Popup_Adjustment_Request._validationError(oResponse.aErrors);
		}
		else
		{
			// Exception
			jQuery.json.errorPopup(oResponse, sMessage);
		}
	},
	
	_validationError : function(aErrors)
	{
		var oErrorElement = $T.ul();
		for (var i = 0; i < aErrors.length; i++)
		{
			oErrorElement.appendChild($T.li(aErrors[i]));
		}
		
		Reflex_Popup.alert(
			$T.div({class: 'alert-validation-error'},
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	},
	
	_getAdjustmentTypeOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Adjustment_Request._getAdjustmentTypeOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment_Type', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Adjustment_Request._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		if (typeof oResponse.aAdjustmentTypes.length == 'undefined') {
			for (var i in oResponse.aAdjustmentTypes)
			{
				var oType = oResponse.aAdjustmentTypes[i];
				aOptions.push(
					$T.option({value: i},
						oType.transaction_nature_code + ': ' + oType.description + ' (' + oType.code + ')'
					)
				);
				Popup_Adjustment_Request._hAdjustmentTypes[i] = oType;
			}
		}
		
		fnCallback(aOptions);
	},
	
	_getInvoiceOptionsForAccount : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Popup_Adjustment_Request._getInvoiceOptionsForAccount.curry(iAccountId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment', 'getAdjustableInvoicesForAccount');
			fnReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Adjustment_Request._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		
		// No association option
		aOptions.push($T.option('No Association'));
		
		for (var i in oResponse.aInvoices)
		{
			var oInvoice = oResponse.aInvoices[i];
			aOptions.push(
				$T.option({value: i},
					oInvoice.Id + ' (' + Date.$parseDate(oInvoice.CreatedOn, 'Y-m-d').$format('d/m/Y') + ')'
				)	
			);
		}
		
		fnCallback(aOptions);
	},
	
	_getGlobalTaxType : function(fnCallback, oResponse) {
		if (!oResponse) {
			// Make request
			var fnResp 	= Popup_Adjustment_Request._getGlobalTaxType.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Tax_Type', 'getGlobalTaxType');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess) {
			// Error
			Popup_Adjustment_Request._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback) {
			fnCallback(oResponse.oTaxType);
		}
	}
});