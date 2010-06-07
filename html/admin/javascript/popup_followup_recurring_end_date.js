
var Popup_FollowUp_Recurring_End_Date	= Class.create(Reflex_Popup,
{
	initialize	: function($super, oFollowUpRecurring, fnOnFinish)
	{
		$super(28);
		
		this._hRadios				= {};
		this._fnOnFinish			= fnOnFinish;
		this._oFollowUpRecurring	= oFollowUpRecurring;
		this._minimumOccurrences	= Popup_FollowUp_Recurring_End_Date.calculateMinimumOccurrences(oFollowUpRecurring);
		
		if (!this._oFollowUpRecurring.end_datetime || (this._oFollowUpRecurring.end_datetime == Popup_FollowUp_Recurring_End_Date.NO_END_DATE))
		{
			// Use now
			this._mSelectedRadio	= Popup_FollowUp_Recurring_End_Date.NO_END_DATE;
			this._sEndDateTime		= Popup_FollowUp_Recurring_End_Date.getNowDateTime();
		}
		else
		{
			this._mSelectedRadio	= Popup_FollowUp_Recurring_End_Date.END_BY;
			this._sEndDateTime		= this._oFollowUpRecurring.end_datetime;
		}
		
		this._buildUI();
		this._radioSelected(this._mSelectedRadio);
	},
	
	_buildUI	: function()
	{
		// Create occurences text control field
		this._oOccurrencesText	= new Control_Field_Text('Occurences');
		this._oOccurrencesText.setVisible(true);
		this._oOccurrencesText.setEditable(true);
		this._oOccurrencesText.setMandatory(false);
		this._oOccurrencesText.setValidateFunction(this._validateOccurrence.bind(this));
		this._oOccurrencesText.setValidationReason(Popup_FollowUp_Recurring_End_Date.VALIDATION_REASON_OCCURRENCES + this._minimumOccurrences);
		this._oOccurrencesText.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oOccurrencesText.setValue('');
		
		// Create date time picker
		var oDatePicker	= new Control_Field_Date_Picker('Date', null, 'Y-m-d H:i:s', true);
		oDatePicker.setVisible(true);
		oDatePicker.setEditable(true);
		oDatePicker.setMandatory(false);
		oDatePicker.setValidateFunction(this._validateDate.bind(this));
		oDatePicker.setValidationReason(Popup_FollowUp_Recurring_End_Date.VALIDATION_REASON_DATE);
		oDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDatePicker.setValue(this._sEndDateTime);
		this._oDatePicker	= oDatePicker;
		
		// Build content
		this._oContent	= 	$T.div({class: 'popup-followup-recurring-dates'},
								$T.div(
									$T.ul({class: 'reset popup-followup-recurring-dates-radio-list'},
										$T.li(
											$T.ul({class: 'reset horizontal'},
												$T.li(
													this._createRadio(Popup_FollowUp_Recurring_End_Date.NO_END_DATE)
												),
												$T.li({class: 'popup-followup-recurring-dates-label'},
													$T.span('No end date')
												)
											)
										),
										$T.li(
											$T.ul({class: 'reset horizontal'},
												$T.li(
													this._createRadio(Popup_FollowUp_Recurring_End_Date.END_NOW)
												),
												$T.li({class: 'popup-followup-recurring-dates-label'},
													$T.span('End now')
												)
											)
										),
										$T.li(
											$T.ul({class: 'reset horizontal'},
												$T.li(
													this._createRadio(Popup_FollowUp_Recurring_End_Date.END_AFTER)
												),
												$T.li({class: 'popup-followup-recurring-dates-end-after'},
													$T.span('End after'),
													this._oOccurrencesText.getElement(),
													$T.span('occurences (' + this._minimumOccurrences + ' has already occurred)')
												)
											)
										),
										$T.li(
											$T.ul({class: 'reset horizontal'},
												$T.li(
													this._createRadio(Popup_FollowUp_Recurring_End_Date.END_BY)
												),
												$T.li({class: 'popup-followup-recurring-dates-end-by'},
													$T.span('End by: '),
													oDatePicker.getElement()
												)
											)
										)
									)
								),
								$T.div({class: 'popup-followup-recurring-dates-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Attach extra events for radio selection
		var oRadioContentLis	= this._oContent.select('ul.popup-followup-recurring-dates-radio-list > li > ul.reset > li:nth-child(2)');
		var oLi					= null;
		var mRadioValue			= null;
		for (var i = 0; i < oRadioContentLis.length; i++)
		{
			oLi	= oRadioContentLis[i];
			mRadioValue	= oLi.previousSibling.select('input[type="radio"]').first().value;
			oLi.observe('click', this._radioSelected.bind(this, mRadioValue));
		}
		
		// Button events
		var oSaveButton	= this._oContent.select('div.popup-followup-recurring-dates-buttons > button.icon-button').first();
		oSaveButton.observe('click', this._save.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-recurring-dates-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
					
		// Popup setup
		this.setTitle('Change Recurring Follow-Up End Date');
		this.setIcon(Popup_FollowUp_Recurring_End_Date.ICON_IMAGE_SOURCE);
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
	},

	_validateOccurrence	: function(sValue)
	{
		var iValue	= parseInt(sValue);
		
		if (isNaN(iValue))
		{
			return false;
		}
		else if (iValue <= this._minimumOccurrences)
		{
			return false
		}
		else
		{
			return true;
		}
	},
	
	_createRadio	: function(mValue)
	{
		var oRadio	= $T.input({type: 'radio', value: mValue, name: Popup_FollowUp_Recurring_End_Date.RADIO_NAME});
		oRadio.observe('click', this._radioSelected.bind(this, mValue));
		this._hRadios[mValue]	= oRadio;
		return oRadio;
	},
	
	_radioSelected	: function(mValue)
	{
		var oRadio	= this._hRadios[mValue];
		
		if (!oRadio.checked)
		{
			oRadio.checked	= 'checked';
		}
		
		this._mSelectedRadio	= mValue;
	},
	
	_save	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get the value
			var sDate	= '';
			switch (this._mSelectedRadio)
			{
				case Popup_FollowUp_Recurring_End_Date.NO_END_DATE:
					sDate	= Popup_FollowUp_Recurring_End_Date.NO_END_DATE;
					break;
				case Popup_FollowUp_Recurring_End_Date.END_NOW.toString():
					sDate	= Popup_FollowUp_Recurring_End_Date.getNowDateTime();
					break;
				case Popup_FollowUp_Recurring_End_Date.END_AFTER.toString():
					// Validate the number before saving
					try
					{
						this._oOccurrencesText.validate(false);
					}
					catch (ex)
					{
						// Validation error, show in popup
						Reflex_Popup.alert(ex);
						return;
					}
					
					var iIteration	= parseInt(this._oOccurrencesText.getElementValue());
					var oDate		= new Date(Date.parse(this._oFollowUpRecurring.start_datetime.replace(/-/g, '/')));
					
					Popup_FollowUp_Recurring_End_Date.shiftDate(
						oDate,
						iIteration * this._oFollowUpRecurring.recurrence_multiplier, 
						this._oFollowUpRecurring.followup_recurrence_period_id
					);
					
					sDate	= Reflex_Date_Format.format('Y-m-d H:i:s', oDate);
					break;
				case Popup_FollowUp_Recurring_End_Date.END_BY.toString():
					// Validate the date before saving
					try
					{
						this._oDatePicker.validate(false);
					}
					catch (ex)
					{
						// Validation error, show in popup
						Reflex_Popup.alert(ex);
						return;
					}
					
					sDate	= this._oDatePicker.getElementValue();
					break;
			}
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Updating end date...');
			this.oLoading.display();
			
			// Make request
			var fnJSON	= 	jQuery.json.jsonFunction(
								this._save.bind(this, null), 
								this._ajaxError.bind(this, false), 
								'FollowUp_Recurring', 
								'updateEndDate'
							);
			fnJSON(this._oFollowUpRecurring.id, sDate);
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
	}
});

// Image paths
Popup_FollowUp_Recurring_End_Date.ICON_IMAGE_SOURCE 	= '../admin/img/template/edit_date.png';
Popup_FollowUp_Recurring_End_Date.YEAR_MINIMUM			= new Date().getFullYear();
Popup_FollowUp_Recurring_End_Date.YEAR_MAXIMUM			= Popup_FollowUp_Recurring_End_Date.YEAR_MINIMUM + 10;

Popup_FollowUp_Recurring_End_Date.VALIDATION_REASON_DATE		= 'The Date & Time must be in the future';
Popup_FollowUp_Recurring_End_Date.VALIDATION_REASON_OCCURRENCES	= 'The number of occurrences must be greater than ';

Popup_FollowUp_Recurring_End_Date.RADIO_NAME			= 'followup_recurring_end_datetime';

Popup_FollowUp_Recurring_End_Date.NO_END_DATE			= '9999-12-31 23:59:00';
Popup_FollowUp_Recurring_End_Date.END_NOW				= 1;
Popup_FollowUp_Recurring_End_Date.END_AFTER				= 2;
Popup_FollowUp_Recurring_End_Date.END_BY				= 3;

Popup_FollowUp_Recurring_End_Date.getNowDateTime	= function()
{
	return Reflex_Date_Format.format('Y-m-d H:i:s', new Date());
};

Popup_FollowUp_Recurring_End_Date.calculateMinimumOccurrences	= function(oFollowUpRecurring)
{
	var iStart		= Date.parse(oFollowUpRecurring.start_datetime.replace(/-/g, '/'));
	var oStartDate	= new Date(iStart);
	var iNow		= new Date().getTime();
	var iIteration	= 0;
	
	while (oStartDate.getTime() < iNow)
	{
		Popup_FollowUp_Recurring_End_Date.shiftDate(
			oStartDate, 
			((iIteration + 1) * oFollowUpRecurring.recurrence_multiplier), 
			oFollowUpRecurring.followup_recurrence_period_id
		);
		
		iIteration++;
	}
	
	return iIteration;
}

Popup_FollowUp_Recurring_End_Date.shiftDate	= function(oDate, iMultiplier, iRecurrencePeriod)
{
	switch (iRecurrencePeriod)
	{
		case $CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_WEEK:
			oDate.shift(iMultiplier * 7, 'days');
			break;
		case $CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_MONTH:
			oDate.shift(iMultiplier, 'months');
			break;
	}
}

