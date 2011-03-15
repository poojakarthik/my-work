
var	Popup_Account_Promise_Edit_Schedule	= Class.create(/* extends */ Reflex_Popup, {

	initialize	: function ($super, oConfig) {
		$super(30);
		this.setTitle('Configure Recurring Instalments');
		this.addCloseButton();

		this.CONFIG	= oConfig;
		this._buildUI();
	},

	_submit	: function () {
		// Validate
		var	aValidationErrors	= [];
		for (var sName in this.CONTROLS) {
			try {
				this.CONTROLS[sName].validate(false);
			} catch (sException) {
				aValidationErrors.push(sException);
			}
		}
		
		if (aValidationErrors.length) {
			// Errors found
			var	oValidationErrorsList	= $T.ol();

			for (var i = 0, j = aValidationErrors.length; i < j; i++) {
				oValidationErrorsList.appendChild($T.li(aValidationErrors[i]));
			}

			Reflex_Popup.alert($T.div(
				$T.p('There following '+aValidationErrors.length+' error(s) were found with your data:'),
				oValidationErrorsList,
				$T.p('Please correct these errors and try again.')
			), {
				sTitle	: 'Validation Error',
				iWidth	: 30
			});
			
			return false;
		}

		// Callback with list of Instalment dates
		this.CONFIG.fnCallback(this._calculateInstalmentDates());

		// Hide me
		this.hide();

		return true;
	},

	_calculateInstalmentDates	: function () {
		var	aInstalmentDates		= [],
			oLastOccurrence			= new Date(this.CONTROLS.oStartingDate.getElementValue()),
			iFrequencyMultiplier	= parseInt(this.CONTROLS.oFrequencyMultiplier.getElementValue()),
			sFrequencyType			= this.CONTROLS.oFrequencyType.getElementValue(),
			iOccurrences			= parseInt(this.CONTROLS.oOccurrences.getElementValue());

		// Validate the Form
		for (var sName in this.CONTROLS) {
			if (!this.CONTROLS[sName].validate(true)) {
				throw "The Form is invalid";
			}
		}

		if (iOccurrences > 0) {
			var	iOccurrence	= 0;
			do {
				iOccurrence++;
				aInstalmentDates.push(oLastOccurrence.$format('Y-m-d'));
				oLastOccurrence.shift(iFrequencyMultiplier, sFrequencyType);
			} while (iOccurrence < iOccurrences);
		}

		return aInstalmentDates;
	},

	_getSelectedEndingType	: function () {
		var	oSelectedRadio	= this.contentPane.select('input[type="radio"][name="popup-account-promise-edit-schedule-endtype"]:checked').first();
		return (!oSelectedRadio) ? null : oSelectedRadio.parentNode.oControlField;
	},

	_updateEndingValues	: function () {
		var	oSelectedRadio			= this._getSelectedEndingType(),
			oStartingDate			= new Date(this.CONTROLS.oStartingDate.getElementValue()),
			oEndingDate				= new Date(this.CONTROLS.oEndingDate.getElementValue()),
			iOccurrences			= parseInt(this.CONTROLS.oOccurrences.getElementValue()),
			iFrequencyMultiplier	= parseInt(this.CONTROLS.oFrequencyMultiplier.getElementValue()),
			sFrequencyType			= this.CONTROLS.oFrequencyType.getElementValue(),
			bFormValid				= (this.CONTROLS.oStartingDate.isValid() && this.CONTROLS.oFrequencyType.isValid() && this.CONTROLS.oFrequencyMultiplier.isValid());

		if (!oSelectedRadio) {
			// Nothing selected: leave everything as it is
		} else if (oSelectedRadio === this.CONTROLS.oEndAfterOccurrences) {
			// Occurrences selected; Update the Ending Date
			//debugger;
			if (bFormValid && this.CONTROLS.oOccurrences.isValid()) {
				var	sEndingDate	= oStartingDate.shift(Math.max(0, iOccurrences-1) * iFrequencyMultiplier, sFrequencyType).$format('Y-m-d');
				this.CONTROLS.oEndingDate.setValue(sEndingDate);
			}
		} else if (oSelectedRadio === this.CONTROLS.oEndOnDate) {
			// Ending Date selected; Update the Occurrences
			//debugger;
			if (bFormValid && this.CONTROLS.oEndingDate.isValid()) {
				var	oLastOccurrence	= new Date(oStartingDate);

				iOccurrences	= 0;
				if (oStartingDate.getTime() <= oEndingDate.getTime()) {
					do {
						iOccurrences++;
						oLastOccurrence.shift(iFrequencyMultiplier, sFrequencyType);
					} while (oLastOccurrence.getTime() <= oEndingDate.getTime());
				}

				this.CONTROLS.oOccurrences.setValue(iOccurrences);
			}
		}
	},

	_onEndingTypeSelect	: function () {
		//debugger;
		var	oSelectedRadio	= this._getSelectedEndingType();

		// Disable everything
		this.CONTROLS.oOccurrences.setRenderMode(Control_Field.RENDER_MODE_VIEW);
		this.CONTROLS.oEndingDate.setRenderMode(Control_Field.RENDER_MODE_VIEW);

		if (!oSelectedRadio) {
			// Nothing selected: leave everything disabled
		} else if (oSelectedRadio === this.CONTROLS.oEndAfterOccurrences) {
			this.CONTROLS.oOccurrences.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		} else if (oSelectedRadio === this.CONTROLS.oEndOnDate) {
			this.CONTROLS.oEndingDate.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		}

		this._updateEndingValues();
	},

	_validateFrequency	: function () {
		var	iMultiplier		= parseInt(this.CONTROLS.oFrequencyMultiplier.getElementValue(), 10),
			sType			= this.CONTROLS.oFrequencyType.getElementValue();

		if (isNaN(iMultiplier) || iMultiplier <= 0) {
			// This may seem strange, but the Frequency Multiplier validation function will fail before this point, anyway
			return true;
		} else if (!sType) {
			// This may seem strange, but the Frequency Type mandatory check will fail before this point, anyway
			return true;
		}

		var	iIntervalDays	= iMultiplier * Popup_Account_Promise_Edit_Schedule.FREQUENCY_DAYS[sType];
		if (iIntervalDays > this.CONFIG.permissions.promise_instalment_maximum_interval_days) {
			throw "Recurring Frequency of "+iMultiplier+' '+sType+' ('+iIntervalDays+' days) is greater than the maximum allowed between instalments ('+this.CONFIG.permissions.promise_instalment_maximum_interval_days+' days)';
		}

		return true;
	},

	_buildUI	: function () {

		var	oCurrentDate	= new Date();

		// Controls
		this.CONTROLS	= {
			oStartingDate	: Control_Field.factory('date-picker', {
				sLabel		: 'Starting Date',
				sExtraClass	: 'popup-account-promise-edit-schedule-startingdate',
				mEditable	: true,
				mMandatory	: true,
				sDateFormat	: 'Y-m-d',
				bTimePicker	: false,
				iYearStart	: oCurrentDate.getFullYear(),
				iYearEnd	: oCurrentDate.getFullYear() + 1,
				fnValidate	: (function (mValue) {
					// Check against promise_start_delay_maximum_days permission
					var	oStartingDate	= (new Date(mValue)).truncate(Date.DATE_INTERVAL_DAY),
						oCurrentDate	= (new Date()).truncate(Date.DATE_INTERVAL_DAY);
					if ((new Date(oCurrentDate)).shift(this.CONFIG.permissions.promise_start_delay_maximum_days, Date.DATE_INTERVAL_DAY).getTime() < oStartingDate.getTime()) {
						throw "The Starting Date/First Instalment is later than "+this.CONFIG.permissions.promise_start_delay_maximum_days+" days(s) after today";
					} else if (oCurrentDate.getTime() >= oStartingDate.getTime()) {
						throw "The Starting Date/First Instalment is earlier than tomorrow";
					}
					return true;
				}).bind(this)
			}),
			oFrequencyMultiplier	: Control_Field.factory('number', {
				sLabel			: 'Repeating Frequency',
				sExtraClass		: 'popup-account-promise-edit-schedule-frequency-multiplier',
				mEditable		: true,
				mMandatory		: true,
				fMinimumValue	: 1,
				iDecimalPlaces	: 0,
				mValue			: 1,
				fnValidate		: (function (mValue) {
					var	iMultiplier	= parseInt(mValue, 10);

					if (isNaN(iMultiplier) || iMultiplier <= 0) {
						throw "Must be a whole number greater than 0";
					}

					return this._validateFrequency();
				}).bind(this)
			}),
			oFrequencyType	: Control_Field.factory('select', {
				sLabel		: 'Frequency Type',
				sExtraClass	: 'popup-account-promise-edit-schedule-frequency-type',
				fnPopulate	: function (fnCallback) {
					fnCallback([
						$T.option({value:Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_DAY}, 'day(s)'),
						$T.option({value:Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_WEEK}, 'weeks(s)'),
						$T.option({value:Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_MONTH}, 'months(s)')
					]);
				},
				mVisible	: true,
				mEditable	: true,
				mMandatory	: true,
				fnValidate	: this._validateFrequency.bind(this)
			}),
			oEndAfterOccurrences	: Control_Field.factory('radio', {
				sLabel		: 'End after occurrences',
				sExtraClass	: 'popup-account-promise-edit-schedule-endtype-occurrences',
				sFieldName	: 'popup-account-promise-edit-schedule-endtype',
				mEditable	: true,
				mMandatory	: false/*,
				mValue		: false*/
			}),
			oEndOnDate				: Control_Field.factory('radio', {
				sLabel		: 'End on date',
				sExtraClass	: 'popup-account-promise-edit-schedule-endtype-date',
				sFieldName	: 'popup-account-promise-edit-schedule-endtype',
				mEditable	: true,
				mMandatory	: false/*,
				mValue		: false*/
			}),
			oOccurrences	: Control_Field.factory('number', {
				sLabel			: 'Occurrences',
				sExtraClass		: 'popup-account-promise-edit-schedule-occurrences',
				mEditable		: true,
				mMandatory		: (function () {
					// Check for the radio setting
					return (this.CONTROLS.oEndAfterOccurrences === this._getSelectedEndingType());
				}).bind(this),
				fMinimumValue	: 1,
				iDecimalPlaces	: 0,
				mValue			: 1,
				fnValidate		: function (mValue) {
					var	iOccurrences	= parseInt(mValue, 10);

					if (typeof iOccurrences !== 'number') {
						// non-Number (empty/null/something similar)
						return true;
					} else if (iOccurrences !== iOccurrences) {
						// NaN
						return false;
					} else {
						// TODO: Check permissions vs. occurrences * multiplier * frequency type
						return true;
					}
				}
			}),
			oEndingDate	: Control_Field.factory('date-picker', {
				sLabel		: 'Ending Date',
				sExtraClass	: 'popup-account-promise-edit-schedule-endingdate',
				mEditable	: true,
				mMandatory	: (function () {
					// Check for the radio setting
					return (this.CONTROLS.oEndOnDate === this._getSelectedEndingType());
				}).bind(this),
				sDateFormat	: 'Y-m-d',
				bTimePicker	: false,
				iYearStart	: oCurrentDate.getFullYear(),
				iYearEnd	: oCurrentDate.getFullYear() + 1,
				fnValidate	: (function (mValue) {
					var	oEndingDate	= (new Date(mValue)).truncate(Date.DATE_INTERVAL_DAY);
					if (oEndingDate.getTime() > (new Date(this.CONFIG.oEarliestDueDate)).shift(this.CONFIG.permissions.promise_maximum_days_between_due_and_end, Date.DATE_INTERVAL_DAY).getTime()) {
						throw "Ending Date/Last Instalment must be at most "+this.CONFIG.permissions.promise_maximum_days_between_due_and_end+" days from the oldest Invoice being promised ("+this.CONFIG.oEarliestDueDate.$format('j M Y')+")";
					}
					return true;
				}).bind(this)
			})
		};

		for (var sName in this.CONTROLS) {
			this.CONTROLS[sName].setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this.CONTROLS[sName].addOnChangeCallback(this._updateEndingValues.bind(this));
		}

		// onChange Events
		this.CONTROLS.oEndAfterOccurrences.addOnChangeCallback(this._onEndingTypeSelect.bind(this));
		this.CONTROLS.oEndOnDate.addOnChangeCallback(this._onEndingTypeSelect.bind(this));

		// Content
		var	oDOM	= $T.table({'class':'reflex popup-account-promise-edit-schedule'},
				$T.tbody(
					$T.tr(
						$T.th({'class':'label'},
							'Starting Date'
						),
						$T.td({'class':'input'},
							this.CONTROLS.oStartingDate.getElement()
						)
					),
					$T.tr(
						$T.th({'class':'label'},
							'Repeating...'
						),
						$T.td({'class':'input'},
							$T.span('Every'),
							this.CONTROLS.oFrequencyMultiplier.getElement(),
							this.CONTROLS.oFrequencyType.getElement()
						)
					),
					$T.tr(
						$T.th({'class':'label'},
							'Ending...'
						),
						$T.td({'class':'input'},
							$T.div({'class':'popup-account-promise-edit-schedule-end-afteroccurrences'},
								$T.label(
									this.CONTROLS.oEndAfterOccurrences.getElement(),
									$T.span('after ')
								),
								$T.label(
									this.CONTROLS.oOccurrences.getElement(),
									$T.span(' occurrences')
								)
							),
							$T.div({'class':'popup-account-promise-edit-schedule-end-ondate'},
								$T.label(
									this.CONTROLS.oEndOnDate.getElement(),
									$T.span('on')
								),
								this.CONTROLS.oEndingDate.getElement()
							)
						)
					)
				)
			),
			oSubmitButton	= $T.button({type:'button'},
				$T.img({src:'../admin/img/template/tick.png','class':'icon'}),
				$T.span('Apply')
			),
			oCancelButton	= $T.button({type:'button'},
				$T.img({src:'../admin/img/template/delete.png','class':'icon'}),
				$T.span('Cancel')
			);

		oSubmitButton.observe('click', this._submit.bind(this));
		oCancelButton.observe('click', this.hide.bind(this));

		// Set Content
		this.setContent(oDOM);
		this.setFooterButtons([oSubmitButton, oCancelButton], true);

		// Set initial state
		this._onEndingTypeSelect();

		// Renderification!
		this.display();
	}

});

Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_DAY		= Date.DATE_INTERVAL_DAY;
Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_WEEK		= Date.DATE_INTERVAL_WEEK;
Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_MONTH	= Date.DATE_INTERVAL_MONTH;

Popup_Account_Promise_Edit_Schedule.FREQUENCY_DAYS	= {};
Popup_Account_Promise_Edit_Schedule.FREQUENCY_DAYS[Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_DAY]		= 1;	// 1 Day per Day
Popup_Account_Promise_Edit_Schedule.FREQUENCY_DAYS[Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_WEEK]		= 7;	// 7 Days per Week
Popup_Account_Promise_Edit_Schedule.FREQUENCY_DAYS[Popup_Account_Promise_Edit_Schedule.FREQUENCY_TYPE_MONTH]	= 31;	// 31 Days per Month (for frequency validation)
