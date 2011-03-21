
var Popup_Account_TIO_Complaint = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAccountId, fnOnComplete)
	{
		$super(40);
		
		this._iAccountId	= iAccountId;
		this._fnOnComplete 	= fnOnComplete;
	
		this._aControls	= [];
		
		var oDate = new Date();
		oDate.setMinutes(0);
		oDate.setHours(0);
		oDate.setSeconds(0);
		oDate.setMilliseconds(0);
		this._iStartTime = oDate.getTime();
		
		this._checkForExistingTIOComplaint();
	},
	
	_checkForExistingTIOComplaint : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._checkForExistingTIOComplaint.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account_TIO_Complaint', 'getCurrentComplaintAndPromiseForAccount');
			fnReq(this._iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_TIO_Complaint._ajaxError(oResponse);
			return;
		}
		
		// Success
		if (oResponse.oComplaint)
		{
			Reflex_Popup.alert('There is already a TIO Complaint active for this Account. The reference number is: ' + oResponse.oComplaint.tio_reference_number)
			return;
		}
		else if (oResponse.oPromise)
		{
			Reflex_Popup.yesNoCancel(
				'There is an active Promise to Pay for the Account. Creating a TIO Complaint will cancel the Promise to Pay. Do you still want to continue?',
				{fnOnYes: this._buildUI.bind(this)}
			);
			return;
		}
		
		this._buildUI();
	},
	
	_buildUI : function(oAccount)
	{
		if (!oAccount)
		{
			Flex.Account.getForId(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		
		// Create control fields
		var oStartDateControl =	Control_Field.factory(
									'date-picker', 
									{
										sLabel				: 'Start Date',
										mMandatory			: true,
										mEditable			: true,
										sDateFormat			: Popup_Account_TIO_Complaint.CONTROL_DATE_FORMAT,
										fnValidate			: this._validateStartDate.bind(this),
										sValidationReason	: 'Must be at or after ' + new Date(this._iStartTime).$format('d/m/y') + '.'
									}
								);
		oStartDateControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oStartDateControl.setValue(new Date(this._iStartTime).$format(Popup_Account_TIO_Complaint.CONTROL_DATE_FORMAT));
		this._oStartDateControl = oStartDateControl;
		this._aControls.push(oStartDateControl);
		
		var oReferenceNumberControl =	Control_Field.factory(
											'text', 
											{
												sLabel		: 'TIO Reference Number',
												mMandatory	: true,
												mEditable	: true,
												fnValidate	: Reflex_Validation.Exception.tioReferenceNumber
											}
										);
		oReferenceNumberControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReferenceNumberControl = oReferenceNumberControl;
		this._aControls.push(oReferenceNumberControl);
		
		// Create ui content
		var oContentDiv = $T.div({class: 'popup-account-tio-complaint'},
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
											$T.td(this._oStartDateControl.getElement())
										),
										$T.tr(
											$T.th({class: 'popup-account-tio-complaint-reference-number-label'},
												'TIO Reference Number'
											),
											$T.td(this._oReferenceNumberControl.getElement())
										)
									)
								),
								$T.div({class: 'popup-account-tio-complaint-buttons'},
									$T.button('Create Complaint').observe('click', this._doSave.bind(this)),
									$T.button('Cancel').observe('click', this._cancel.bind(this))
								)
							);
		
		this._oTBody = oContentDiv.select('tbody').first();
		
		this.setTitle(this._iAccountId + ': Create TIO Complaint');
		this.setContent(oContentDiv);
		this.addCloseButton();
		this.display();
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
			
			if (aErrors.length)
			{
				Popup_Account_TIO_Complaint._validationError(aErrors);
				return;
			}
			
			var oDetails = 	{
								account_id				: this._iAccountId,
								start_datetime			: this._oStartDateControl.getValue(),
								tio_reference_number	: this._oReferenceNumberControl.getValue()
							};
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account_TIO_Complaint', 'createComplaint');
			fnReq(oDetails, Math.floor(this._iStartTime / 1000));
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Account_TIO_Complaint._ajaxError(oResponse, 'Could not create the complaint');
			return;
		}
		
		Reflex_Popup.alert('Complaint has been created');
		
		// Callback
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iComplaintId);
		}
		
		// Update collections components, if defined
		if (typeof Component_Account_Collections != 'undefined')
		{
			Component_Account_Collections.refreshInstances();
		}
		
		this.hide();
	},
	
	_cancel : function()
	{
		this.hide();
	},
	
	_validateStartDate : function(sDate)
	{
		var oDate = Date.$parseDate(sDate, Popup_Account_TIO_Complaint.CONTROL_DATE_FORMAT);
		return (oDate.getTime() >= this._iStartTime);
	}
});

// Static

Object.extend(Popup_Account_TIO_Complaint, 
{
	CONTROL_DATE_FORMAT : 'Y-m-d',
	MS_IN_DAY			: 1000 * 60 * 60 * 24,
	
	_ajaxError : function(oResponse, sMessage)
   	{
   		if (oResponse.aErrors)
   		{
   			// Validation errors
   			Popup_Account_TIO_Complaint._validationError(oResponse.aErrors);
   		}
   		else
   		{
   			// Exception
   			Reflex_Popup.alert(
   				(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
   				{sTitle: 'Error'}
   			);
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
   	}
});
