
var Popup_FollowUp_Due_Date	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iFollowUpId, initialDateTime, fnOnFinish)
	{
		$super(30);
		
		this._iFollowUpId		= iFollowUpId;
		this._initialDateTime	= initialDateTime;
		this._fnOnFinish		= fnOnFinish;
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		// Create date time picker
		var oDatePicker	= new Control_Field_Date_Picker('Date', null, 'Y-m-d H:i:s', true);
		oDatePicker.setVisible(true);
		oDatePicker.setEditable(true);
		oDatePicker.setMandatory(false);
		oDatePicker.setValidateFunction(this._validateDate.bind(this));
		oDatePicker.setValidationReason(Popup_FollowUp_Due_Date.VALIDATION_REASON_DATE);
		oDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDatePicker.setValue(this._initialDateTime);
		this._oDatePicker	= oDatePicker;
		
		var oReasonSelect	= new Control_Field_Select('Reason');
		oReasonSelect.setPopulateFunction(FollowUp_Modify_Reason.getAllAsSelectOptions.bind(FollowUp_Modify_Reason));
		oReasonSelect.setVisible(true);
		oReasonSelect.setEditable(true);
		oReasonSelect.setMandatory(true);
		oReasonSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonSelect	= oReasonSelect;
		
		// Build content
		this._oContent	= 	$T.div({class: 'popup-followup-due-date'},
								$T.div({class: 'popup-followup-due-date-select'},
									$T.ul({class: 'reset horizontal'},
										$T.li('Choose a date: '),
										$T.li({class: 'popup-followup-due-date-picker'},
											this._oDatePicker.getElement()
										),
										$T.li({class: 'popup-followup-due-date-reason'},
											'Please specify a reason why you are modifying the Due Date:'
										),
										$T.li({class: 'popup-followup-due-date-reason-select'},
											this._oReasonSelect.getElement()
										)
									)
								),
								$T.div({class: 'popup-followup-due-date-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Button events
		var oSaveButton	= this._oContent.select('div.popup-followup-due-date-buttons > button.icon-button').first();
		oSaveButton.observe('click', this._doUpdate.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-due-date-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
					
		// Popup setup
		this.setTitle('Change Follow-Up Due Date');
		this.setIcon(Popup_FollowUp_Due_Date.ICON_IMAGE_SOURCE);
		this.setContent(this._oContent);
		this.display();
	},
	
	_ajaxError	: function(bHideOnClose, oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	},
	
	_doUpdate	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Validate the date
			try
			{
				this._oDatePicker.validate(false);
				this._oReasonSelect.validate(false);
			}
			catch (ex)
			{
				// Validation error, show in popup
				Reflex_Popup.alert(ex);
				return;
			}
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Updating due date...');
			this.oLoading.display();
			
			// Make request
			var fnJSON	= 	jQuery.json.jsonFunction(
								this._doUpdate.bind(this, null), 
								this._ajaxError.bind(this, false), 
								'FollowUp', 
								'updateFollowUpDueDate'
							);
			fnJSON(this._iFollowUpId, this._oDatePicker.getElementValue(), this._oReasonSelect.getElementValue());
		}
		else if (oResponse.Success)
		{
			// Success, handle response
			if (this.oLoading)
			{
				this.oLoading.hide();
				delete this.oLoading;
			}
			
			if (this._fnOnFinish)
			{
				this._fnOnFinish();
			}
			
			this.hide();
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_validateDate	: function(sValue)
	{
		var sDate			= sValue;
		var iMilliseconds	= Date.parse(sDate.replace(/-/g, '/'));
		
		if (isNaN(iMilliseconds))
		{
			return false;
		}
		else if (iMilliseconds > new Date().getTime())
		{
			return true;
		}
		else
		{
			return false;
		}
	}
});

// Image paths
Popup_FollowUp_Due_Date.ICON_IMAGE_SOURCE 	= '../admin/img/template/edit_date.png';
Popup_FollowUp_Due_Date.YEAR_MINIMUM		= new Date().getFullYear();
Popup_FollowUp_Due_Date.YEAR_MAXIMUM		= Popup_FollowUp_Due_Date.YEAR_MINIMUM + 10;

Popup_FollowUp_Due_Date.VALIDATION_REASON	= 'The Date & Time must be in the future';
