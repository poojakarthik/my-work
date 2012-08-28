
var Component_Collections_Suspension = Class.create( 
{
	initialize : function(iAccountId, oContainerDiv, fnOnComplete, fnOnCancel, oLoadingPopup, oTIOComplaintDetails, fnReadyToShow)
	{
		this._iAccountId			= iAccountId;
		this._oContainerDiv 		= oContainerDiv;
		this._fnOnComplete			= fnOnComplete;
		this._fnOnCancel			= fnOnCancel;
		this._oLoadingPopup			= oLoadingPopup;
		this._oTIOComplaintDetails	= (oTIOComplaintDetails ? oTIOComplaintDetails : null);
		this._fnReadyToShow			= fnReadyToShow;
		
		this._aControls	= [];
		
		this._buildUI();
	},
	
	_buildUI : function(oAccount, sStartDate, oConfig)
	{
		if (!oAccount)
		{
			Flex.Account.getForId(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		
		if (!sStartDate)
		{
			Component_Collections_Suspension._getEarliestSuspensionStartDate(this._iAccountId, this._buildUI.bind(this, oAccount));
			return;
		}
		
		if (!oConfig)
		{
			Component_Collections_Suspension._getSuspensionConfig(this._buildUI.bind(this, oAccount, sStartDate));
			return;
		}
		
		if (!oConfig.suspension_maximum_days)
		{
			Reflex_Popup.alert('You do not have permission to Suspend Accounts from Collections.');
			return;
		}
		
		this._iMaximumDays	= oConfig.suspension_maximum_days;
		this._iStartTime 	= Date.$parseDate(sStartDate, 'Y-m-d H:i:s').getTime();
		
		// Create control fields
		var oEndDateControl =	Control_Field.factory(
									'date-picker', 
									{
										sLabel				: 'Proposed End Date',
										mMandatory			: true,
										mEditable			: true,
										sDateFormat			: Component_Collections_Suspension.CONTROL_DATE_FORMAT,
										fnValidate			: this._validateEndDate.bind(this),
										sValidationReason	: 'Must be up to ' + this._iMaximumDays + ' days after the Start Date.'
									}
								);
		oEndDateControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oEndDateControl = oEndDateControl;
		this._aControls.push(oEndDateControl);

		var oReasonControl =	Control_Field.factory(
									'select', 
									{
										sLabel		: 'Reason',
										mMandatory	: true,
										mEditable	: true,
										fnPopulate	: Component_Collections_Suspension._getReasonOptions
									}
								);
		oReasonControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonControl = oReasonControl;
		this._aControls.push(oReasonControl);
		
		// Create ui content
		this._oContentDiv = $T.div({class: 'component-collections-suspension'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Account'),
											$T.td(
												$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oAccount.Id}, 
													oAccount.Id
												),
												$T.span(': ' + oAccount.BusinessName)
											)
										),
										$T.tr(
											$T.th('Start Date'),
											$T.td(
												Date.$parseDate(sStartDate, 'Y-m-d H:i:s').$format('d/m/Y')
											)
										),
										$T.tr(
											$T.th('Proposed End Date'),
											$T.td({class: 'component-collections-suspension-proposed-end-date'},
												$T.ul({class: 'reset horizontal'},
													$T.li(this._oEndDateControl.getElement()),
													$T.li('Maximum length of ' + this._iMaximumDays + ' days')
												)
											)
										),
										$T.tr(
											$T.th('Reason'),
											$T.td(this._oReasonControl.getElement())
										)
									)
								),
								$T.div({class: 'component-collections-suspension-buttons'},
									$T.button('Suspend Collections').observe('click', this._doSave.bind(this)),
									$T.button('Cancel').observe('click', this._cancel.bind(this))
								)
							);
		
		this._oTBody = this._oContentDiv.select('tbody').first();
		
		// Attach content
		this._oContainerDiv.appendChild(this._oContentDiv);
		
		if (this._fnReadyToShow)
		{
			this._fnReadyToShow();
		}
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		// Hide any saving-related popup
		if (this._oSavingPopup) {
			this._oSavingPopup.hide();
			delete this._oSavingPopup;
		}

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
			
			if (aErrors.length)
			{
				Component_Collections_Suspension._validationError(aErrors);
				return;
			}
			
			var oDetails = 	{
								account_id						: this._iAccountId,
								proposed_end_datetime			: this._oEndDateControl.getValue(),
								collection_suspension_reason_id	: this._oReasonControl.getValue()
							};

			this._oSavingPopup	= new Reflex_Popup.Loading('Creating Suspension...', true);

			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'createSuspension');
			fnReq(oDetails, Math.floor(this._iStartTime / 1000), this._oTIOComplaintDetails);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Suspension._ajaxError(oResponse, 'Could not create the suspension');
			return;
		}
		
		Reflex_Popup.alert('Suspension has been created');
		
		// Callback
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iSuspensionId);
		}
		
		// Update collections component, if defined
		if (typeof Component_Account_Collections != 'undefined')
		{
			Component_Account_Collections.refreshInstances();
		}
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
	},
	
	_validateEndDate : function(sDate)
	{
		var oEndDate 	= Date.$parseDate(sDate, Component_Collections_Suspension.CONTROL_DATE_FORMAT);
		var iEnd	= oEndDate.getTime();
		
		if (iEnd <= this._iStartTime)
		{
			throw 'Must end after the start of the suspension';
		}
		else if (iEnd > (this._iStartTime + (Component_Collections_Suspension.MS_IN_DAY * this._iMaximumDays)))
		{
			throw 'You do not have permission to create a suspension of that length';
		}
		
		return true;
	}
});

// Static

Object.extend(Component_Collections_Suspension, 
{
	CONTROL_DATE_FORMAT : 'Y-m-d',
	MS_IN_DAY			: 1000 * 60 * 60 * 24,
	
	_ajaxError : function(oResponse, sMessage)
   	{
   		if (oResponse.aErrors)
   		{
   			// Validation errors
   			Component_Collections_Suspension._validationError(oResponse.aErrors);
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
		
	_getReasonOptions : function(fnCallback, oResponse)
	{
   		if (!oResponse)
   		{
   			// Request
   			var fnResp	= Component_Collections_Suspension._getReasonOptions.curry(fnCallback);
   			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension_Reason', 'getAllForNewSuspension');
   			fnReq();
   			return;
   		}
   		
   		if (!oResponse.bSuccess)
   		{
   			// Error
   			Component_Collections_Suspension._ajaxError(oResponse);
   			return;
   		}
   		
		// Create options & callback
		var aData 		= oResponse.aReasons;
		var aOptions	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value: i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getSuspensionConfig : function(fnCallback, oResponse)
	{
		if (!oResponse)
   		{
   			// Request
   			var fnResp	= Component_Collections_Suspension._getSuspensionConfig.curry(fnCallback);
   			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'getSuspensionConfig');
   			fnReq();
   			return;
   		}
   		
   		if (!oResponse.bSuccess)
   		{
   			// Error
   			Component_Collections_Suspension._ajaxError(oResponse);
   			return;
   		}
   		
   		if (fnCallback)
   		{
   			fnCallback(oResponse.aConfig);
   		}
	},
	
	_getEarliestSuspensionStartDate : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
   		{
   			// Request
   			var fnResp	= Component_Collections_Suspension._getEarliestSuspensionStartDate.curry(iAccountId, fnCallback);
   			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'getEarliestSuspensionStartDate');
   			fnReq(iAccountId);
   			return;
   		}
   		
   		if (!oResponse.bSuccess)
   		{
   			// Error
   			Component_Collections_Suspension._ajaxError(oResponse);
   			return;
   		}
   		
   		if (fnCallback)
   		{
   			fnCallback(oResponse.sDate);
   		}
	}
});
