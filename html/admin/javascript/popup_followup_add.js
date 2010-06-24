
var Popup_FollowUp_Add	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iType, iTypeDetail, fnOnSave)
	{
		$super(40);
		
		this._iType						= parseInt(iType);
		this._iTypeDetail				= iTypeDetail;
		this._iSelectedRecurringEndDate	= null;
		this._iSelectedRecurrenceType	= null;
		this._fnOnSave					= fnOnSave;
		
		// Date object to use as 'now'
		this._oInstanceCreatedDate		= new Date();
		this._oInstanceCreatedDate.setSeconds(0);
		this._oInstanceCreatedDate.setMilliseconds(0);
		
		this._buildUI();
	},

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get details about the ticket/note/action
			var fnGetContextDetails	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this), 
											this._ajaxError.bind(this, true), 
											'FollowUp', 
											'getFollowUpContextDetails'
										);
			fnGetContextDetails(this._iType, this._iTypeDetail);
		}
		else if (oResponse.Success)
		{
			// Cache response
			this._oDetails	= oResponse.aDetails;
			
			// Create category control
			var oCategorySelect	= new Control_Field_Select('Category');
			oCategorySelect.setVisible(true);
			oCategorySelect.setEditable(true);
			oCategorySelect.setMandatory(true);
			oCategorySelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oCategorySelect.setPopulateFunction(FollowUp_Category.getAllAsSelectOptions.bind(FollowUp_Category));
			this._oCategorySelect	= oCategorySelect;
			
			// Create period select control
			var oPeriodSelect	= new Control_Field_Select('Recurrence Period');
			oPeriodSelect.setVisible(true);
			oPeriodSelect.setEditable(true);
			oPeriodSelect.setMandatory(true);
			oPeriodSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oPeriodSelect.setPopulateFunction(Popup_FollowUp_Add.getRecurrencePeriodsAsOptions);
			oPeriodSelect.addOnChangeCallback(this._calculateRecurringEndDate.bind(this));
			oPeriodSelect.setValue($CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_WEEK);
			this._oPeriodSelect	= oPeriodSelect;
			
			// Create occurences text control
			var oOccurrencesText	= new Control_Field_Text('Occurences');
			oOccurrencesText.setVisible(true);
			oOccurrencesText.setEditable(true);
			oOccurrencesText.setMandatory(true);
			oOccurrencesText.setValidateFunction(Popup_FollowUp_Add.validateOccurrences);
			oOccurrencesText.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oOccurrencesText.setValue('2');
			oOccurrencesText.addOnChangeCallback(this._calculateRecurringEndDate.bind(this));
			this._oOccurrencesText	= oOccurrencesText;
			
			// Create recurrence multiplier control
			var oMultiplierText	= new Control_Field_Text('Recurrence Multiplier');
			oMultiplierText.setVisible(true);
			oMultiplierText.setEditable(true);
			oMultiplierText.setMandatory(true);
			oMultiplierText.setValidateFunction(Popup_FollowUp_Add.validateRecurrenceMultiplier);
			oMultiplierText.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oMultiplierText.setValue('1');
			oMultiplierText.addOnChangeCallback(this._calculateRecurringEndDate.bind(this));
			this._oMultiplierText	= oMultiplierText;
			
			// Create default date value (now, with no seconds)
			var sDefaultDate	= this._oInstanceCreatedDate.$format(Popup_FollowUp_Add.DATE_FORMAT);
			
			// Create date time picker controls
			var oStartDatePicker	= new Control_Field_Date_Picker('Date', null, Popup_FollowUp_Add.DATE_FORMAT, true);
			oStartDatePicker.setVisible(true);
			oStartDatePicker.setEditable(true);
			oStartDatePicker.setMandatory(true);
			oStartDatePicker.setValidateFunction(this._validateStartDate.bind(this));
			oStartDatePicker.setValidationReason(Popup_FollowUp_Add.VALIDATION_REASON_START_DATE);
			oStartDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oStartDatePicker.setValue(sDefaultDate);
			oStartDatePicker.addOnChangeCallback(this._calculateRecurringEndDate.bind(this));
			this._oStartDatePicker	= oStartDatePicker;
			
			var oDueDatePicker	= new Control_Field_Date_Picker('Date', null, Popup_FollowUp_Add.DATE_FORMAT, true);
			oDueDatePicker.setVisible(true);
			oDueDatePicker.setEditable(true);
			oDueDatePicker.setMandatory(true);
			oDueDatePicker.setValidateFunction(this._validateStartDate.bind(this));
			oDueDatePicker.setValidationReason(Popup_FollowUp_Add.VALIDATION_REASON_START_DATE);
			oDueDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oDueDatePicker.setValue(sDefaultDate);
			this._oDueDatePicker	= oDueDatePicker;
			
			var oEndByDatePicker	= new Control_Field_Date_Picker('Date', null, Popup_FollowUp_Add.DATE_FORMAT, true);
			oEndByDatePicker.setVisible(true);
			oEndByDatePicker.setEditable(true);
			oEndByDatePicker.setMandatory(true);
			oEndByDatePicker.setValidateFunction(this._validateEndDate.bind(this));
			oEndByDatePicker.setValidationReason(Popup_FollowUp_Add.VALIDATION_REASON_END_DATE);
			oEndByDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oEndByDatePicker.setValue(sDefaultDate);
			oEndByDatePicker.addOnChangeCallback(this._calculateRecurringEndDate.bind(this));
			this._oEndByDatePicker	= oEndByDatePicker;
			
			// Build popup content
			this._oContent	= 	$T.div({class: 'popup-followup-add'},
									$T.div({class: 'section'},
											$T.div({class: 'section-header'},
												$T.div({class: 'section-header-title'},
													Flex.Constant.arrConstantGroups.followup_type[this._iType].Name + ' Details'
												)
											),
											$T.div({class: 'section-content'},
												this._createDetailsElement()
											)
										),
										$T.div({class: 'section'},
											$T.div({class: 'section-header'},
												$T.div({class: 'section-header-title'},
													'Follow-Up Details'
												)
											),
											$T.div({class: 'section-content section-content-fitted'},
												$T.table({class: 'input popup-followup-details'},
													$T.colgroup(
														$T.col({style: 'width: 23%'}),
														$T.col({style: 'width: 77%'})
													),
													$T.tbody(
														$T.tr(
															$T.th({class: 'label'},
																'Type :'
															),
															$T.td(
																Popup_FollowUp_Add._getTypeElement(this._iType)
															)															
														),
														$T.tr(
															$T.th({class: 'label'},
																'Category :'
															),
															$T.td(
																this._oCategorySelect.getElement()
															)
														),
														$T.tr(
															$T.th({class: 'label'}),
															$T.td({class: 'popup-followup-details-recurrence-type-select'},
																this._createRadio(
																	Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_ONCE_OFF, 
																	Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE,
																	this._recurrenceTypeRadioSelected,
																	$T.span('Once Off'),
																	true
																),
																this._createRadio(
																	Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_RECURRING, 
																	Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE,
																	this._recurrenceTypeRadioSelected,
																	$T.span('Recurring'),
																	true
																),
																/*)
														),
														$T.tr(
															$T.th({class: 'label'}),
															$T.td({class: 'popup-followup-details'},*/
																// Once-off follow-up specific information
																$T.table({class: 'popup-followup-details-onceoff', style: 'display: none;'},
																	$T.tbody(
																		$T.tr(
																			$T.td('Due On'),
																			$T.td(this._oDueDatePicker.getElement())
																		)
																	)
																),
																// Recurring follow-up specific information
																$T.table({class: 'popup-followup-details-recurring', style: 'display: none;'},
																	$T.tbody(
																		$T.tr(
																			$T.td('Start On'),
																			$T.td(this._oStartDatePicker.getElement())
																		),
																		$T.tr(
																			$T.td('How Often?'),
																			$T.td({class: 'popup-followup-add-details-recurring-howoften'},
																				$T.span('Every '),
																				this._oMultiplierText.getElement(),
																				this._oPeriodSelect.getElement()
																			)
																		),
																		$T.tr(
																			$T.td('End'),
																			$T.td({class: 'popup-followup-add-details-recurring-end'},
																				this._createRadio(
																					Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_NO_END, 
																					Popup_FollowUp_Add.RADIO_RECURRING_END_DATE,
																					this._recurringEndDateRadioSelected,
																					$T.span('No end date')
																				),
																				this._createRadio(
																					Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_AFTER, 
																					Popup_FollowUp_Add.RADIO_RECURRING_END_DATE,
																					this._recurringEndDateRadioSelected,
																					$T.div({class: 'popup-followup-details-occurences'},
																						$T.span('...After '),
																						this._oOccurrencesText.getElement(),
																						$T.span(' occurrences')
																					)
																				),
																				this._createRadio(
																					Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_BY, 
																					Popup_FollowUp_Add.RADIO_RECURRING_END_DATE,
																					this._recurringEndDateRadioSelected,
																					$T.div({class: 'popup-followup-details-endby'},
																						$T.span('...By '),
																						this._oEndByDatePicker.getElement()
																					)
																				)
																			)
																		)
																	)
																)
															)
														)
													)
												)
											)
										),
									$T.div({class: 'popup-followup-add-buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_FollowUp_Add.CREATE_IMAGE_SOURCE, alt: 'Create Follow-Up', title: 'Create Follow-Up'}),
											'Create'
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_FollowUp_Add.CANCEL_IMAGE_SOURCE, alt: 'Cancel', title: 'Cancel'}),
											'Cancel'
										)
									)
								);
			
			// Pre-select radio buttons
			var oOnceOffRadio	= this._oContent.select('td.popup-followup-details-recurrence-type-select > div > input[value="' + Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_ONCE_OFF + '"]').first();
			this._recurrenceTypeRadioSelected(oOnceOffRadio);
			
			var oNoEndDateRadio	= this._oContent.select('td.popup-followup-add-details-recurring-end > div > input[value="' + Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_NO_END + '"]').first();
			this._recurringEndDateRadioSelected(oNoEndDateRadio);
			
			// Footer button events
			var oCreateButton	= this._oContent.select('button.icon-button').first();
			oCreateButton.observe('click', this._save.bind(this));
			var oCancelButton	= this._oContent.select('button.icon-button').last();
			oCancelButton.observe('click', this.hide.bind(this));
			
			this.setTitle('New Follow-Up');
			this.addCloseButton();
			this.setIcon(Popup_FollowUp_Add.FOLLOWUP_IMAGE_SOURCE);
			this.setContent(this._oContent);
			this.display();
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_ajaxError	: function(bHideOnClose, oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.Success == false)
		{
			var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
			
			if (oResponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message, oConfig);
			}
			else if (oResponse.ERROR)
			{
				Reflex_Popup.alert(oResponse.ERROR, oConfig);
			}
			else if (oResponse.aValidationErrors)
			{
				Popup_FollowUp_Add._showValidationErrorPopup(oResponse.aValidationErrors);
			}
		}
	},
	
	_save	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Set up controls to validate depending on the recurrence type that has been selected
			var aControlsToValidate	= [];
			var hDetails			= {};
			
			// Validate category
			aControlsToValidate.push(this._oCategorySelect);
			hDetails['iCategory']	= parseInt(this._oCategorySelect.getElementValue());
			
			switch (this._iSelectedRecurrenceType)
			{
				case Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_ONCE_OFF:
					// Validate due date
					aControlsToValidate.push(this._oDueDatePicker);
					hDetails['sDueDateTime']	= this._oDueDatePicker.getElementValue();
					break;
				case Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_RECURRING:
					// Validate start date, how often *, occurrences & end date
					aControlsToValidate.push(this._oStartDatePicker);
					aControlsToValidate.push(this._oMultiplierText);
					aControlsToValidate.push(this._oPeriodSelect);
					aControlsToValidate.push(this._oOccurrencesText);
					aControlsToValidate.push(this._oEndByDatePicker);
					hDetails['sStartDateTime']			= this._oStartDatePicker.getElementValue();
					hDetails['iRecurrenceMultiplier']	= parseInt(this._oMultiplierText.getElementValue());
					hDetails['iRecurrencePeriod']		= parseInt(this._oPeriodSelect.getElementValue());
					hDetails['iOccurrences']			= parseInt(this._oOccurrencesText.getElementValue());
					
					if (this._iSelectedRecurringEndDate == Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_NO_END)
					{
						hDetails['sEndDateTime']	= null;
					}
					else
					{
						hDetails['sEndDateTime']	= this._oEndByDatePicker.getElementValue();
					}
					break;
			}
	
			// Validate the controls
			var aErrors	= [];
			for (var i = 0; i < aControlsToValidate.length; i++)
			{
				try
				{
					aControlsToValidate[i].validate(false);
				}
				catch (ex)
				{
					aErrors.push(ex);
				}
			}
			
			if (aErrors.length)
			{
				// Errors, show them
				Popup_FollowUp_Add._showValidationErrorPopup(aErrors);
				return;
			}
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Input valid, do the save
			var fnSave	=	jQuery.json.jsonFunction(
								this._save.bind(this, null),
								this._ajaxError.bind(this, false), 
								'FollowUp', 
								'createNew'
							);
			fnSave(this._iType, this._iTypeDetail, hDetails);
		}
		else if (oResponse.Success)
		{
			// Hide loading
			this.oLoading.hide();
			delete this.oLoading;
			
			// Completion callback
			if (this._fnOnSave)
			{
				this._fnOnSave();
			}
			
			// Close popup
			this.hide();
		}
		else
		{
			// Error
			this._ajaxError(false, oResponse);
		}
	},
	
	_createDetailsElement	: function()
	{
		var oDiv	= $T.div({class: 'popup-followup-add-details'});
		if (this._oDetails)
		{
			switch (this._iType)
			{
				case $CONSTANT.FOLLOWUP_TYPE_ACTION:
				case $CONSTANT.FOLLOWUP_TYPE_NOTE:
					// Account, service or contact info
					if (this._oDetails.customer_group)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getCustomerGroupLink(this._oDetails.account_id, this._oDetails.customer_group));
					}
					
					if (this._oDetails.account_id && this._oDetails.account_name)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getAccountLink(this._oDetails.account_id, this._oDetails.account_name));
					}
					
					if (this._oDetails.service_id && this._oDetails.service_fnn)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getServiceLink(this._oDetails.service_id, this._oDetails.service_fnn));
					}
					
					if (this._oDetails.contact_id && this._oDetails.contact_name)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getAccountContactLink(this._oDetails.contact_id, this._oDetails.contact_name));
					}
					break;
				case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
					// Account or ticket contact info
					if (this._oDetails.customer_group)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getCustomerGroupLink(this._oDetails.account_id, this._oDetails.customer_group));
					}
					
					if (this._oDetails.account_id && this._oDetails.account_name)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getAccountLink(this._oDetails.account_id, this._oDetails.account_name));
					}
					
					if (this._oDetails.account_id && this._oDetails.ticket_id && this._oDetails.ticket_contact_name)
					{
						oDiv.appendChild(Popup_FollowUp_Add.getTicketLink(this._oDetails.ticket_id, this._oDetails.account_id, this._oDetails.ticket_contact_name));
					}
					break;
			}
		}
		
		return oDiv;
	},
	
	_createRadio	: function(mValue, sName, fnOnClick, oLabelElement, bInline)
	{
		var oRadio	= $T.input({type: 'radio', name: sName, value: mValue});
		
		if (fnOnClick)
		{
			oRadio.observe('change', fnOnClick.bind(this, oRadio));
		}
		
		if (oLabelElement.observe)
		{
			oLabelElement.observe('click', fnOnClick.bind(this, oRadio));
		}
		
		return 	$T.div({class: 'popup-followup-add-radio' + (bInline ? ' popup-followup-add-radio-inline' : '')},
					oRadio,
					oLabelElement
				);
	},
	
	_recurrenceTypeRadioSelected	: function(oRadio)
	{
		oRadio.checked		= true;
		var iValue			= parseInt(oRadio.value);
		var oOnceOffTable	= this._oContent.select('table.popup-followup-details-onceoff').first();
		var oRecurringTable	= this._oContent.select('table.popup-followup-details-recurring').first();
		switch (iValue)
		{
			case Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_ONCE_OFF:
				oOnceOffTable.show();
				oRecurringTable.hide();
				break;
			case Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_RECURRING:
				oOnceOffTable.hide();
				oRecurringTable.show();
				break;
		}
		
		this._iSelectedRecurrenceType	= iValue;
	},
	
	_recurringEndDateRadioSelected	: function(oRadio)
	{
		this._iSelectedRecurringEndDate	= parseInt(oRadio.value);
		oRadio.checked					= true;
		
		switch (this._iSelectedRecurringEndDate)
		{
			case Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_NO_END:
				this._oOccurrencesText.disableInput();
				this._oEndByDatePicker.disableInput();
				break;
			case Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_AFTER:
				this._oOccurrencesText.enableInput();
				this._oEndByDatePicker.disableInput();
				break;
			case  Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_BY:
				this._oOccurrencesText.disableInput();
				this._oEndByDatePicker.enableInput();
				break;
		}
		
		this._calculateRecurringEndDate();
		this._oOccurrencesText.validate();
		this._oEndByDatePicker.validate();
	},
	
	_calculateRecurringEndDate	: function()
	{
		switch (this._iSelectedRecurringEndDate)
		{
			case Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_NO_END:
				// Calculate for the number of occurences when no end date is selected
			case Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_AFTER:
				// Calculate the end date
				var sStartDate				= this._oStartDatePicker.getElementValue();
				var iRecurrenceMultiplier	= parseInt(this._oMultiplierText.getElementValue());
				var iRecurrencePeriod		= parseInt(this._oPeriodSelect.getElementValue());
				var iOccurrences			= parseInt(this._oOccurrencesText.getElementValue());
				
				if (!isNaN(iRecurrenceMultiplier) && !isNaN(iOccurrences) && !isNaN(iRecurrencePeriod))
				{
					this._oEndByDatePicker.setValue(
						Popup_FollowUp_Add.calculateEndDate(sStartDate, iRecurrenceMultiplier, iRecurrencePeriod, iOccurrences)
					);
				}
				break;
			case  Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_BY:
				// Calculate the number of occurences
				var sStartDate				= this._oStartDatePicker.getElementValue();
				var iRecurrenceMultiplier	= parseInt(this._oMultiplierText.getElementValue());
				var iRecurrencePeriod		= parseInt(this._oPeriodSelect.getElementValue());
				var sEndDate				= this._oEndByDatePicker.getElementValue();
				
				if (!isNaN(iRecurrenceMultiplier) && !isNaN(iRecurrencePeriod))
				{
					this._oOccurrencesText.setValue(
						Popup_FollowUp_Add.calculateOccurrences(sStartDate, sEndDate, iRecurrenceMultiplier, iRecurrencePeriod)
					);
				}
				break;
		}
	},
	
	_validateStartDate	: function(sValue)
	{
		var iMilliseconds	= Date.parse(sValue.replace(/-/g, '/'));
		if (isNaN(iMilliseconds) || (iMilliseconds < this._oInstanceCreatedDate.getTime()))
		{
			return false;
		}
		else
		{
			return true;
		}
	},
	
	_validateEndDate	: function(sValue)
	{
		/*if (this._iSelectedRecurringEndDate != Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_BY)
		{
			// end date radio not selected, validation not necessary
			return true
		}
		else
		{*/
			var iStartDate		= Date.parse(this._oStartDatePicker.getElementValue().replace(/-/g, '/'));
			var iMilliseconds	= Date.parse(sValue.replace(/-/g, '/'));
			if (isNaN(iMilliseconds) || (iMilliseconds <= iStartDate))
			{
				return false;
			}
			else
			{
				return true;
			}
		//}
	}
});

Popup_FollowUp_Add.FOLLOWUP_IMAGE_SOURCE					= '../admin/img/template/followup.png',

Popup_FollowUp_Add.CREATE_IMAGE_SOURCE						= '../admin/img/template/approve.png';
Popup_FollowUp_Add.CANCEL_IMAGE_SOURCE						= '../admin/img/template/decline.png';

Popup_FollowUp_Add.TYPE_NOTE_IMAGE_SOURCE					= '../admin/img/template/followup_note.png';
Popup_FollowUp_Add.TYPE_ACTION_IMAGE_SOURCE					= '../admin/img/template/followup_action.png';
Popup_FollowUp_Add.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE	= '../admin/img/template/tickets.png';

Popup_FollowUp_Add.DETAILS_ACCOUNT_IMAGE_SOURCE				= '../admin/img/template/account.png';
Popup_FollowUp_Add.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE		= '../admin/img/template/contact_small.png';
Popup_FollowUp_Add.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE		= '../admin/img/template/service.png';
Popup_FollowUp_Add.DETAILS_TICKET_IMAGE_SOURCE				= Popup_FollowUp_Add.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;

Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE					= 'followup_add_recurrence_type';
Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_ONCE_OFF			= 1;
Popup_FollowUp_Add.RADIO_RECURRENCE_TYPE_RECURRING			= 2;

Popup_FollowUp_Add.RADIO_RECURRING_END_DATE					= 'followup_add_recurring_end_date';
Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_NO_END 			= 1;
Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_AFTER 		= 2;
Popup_FollowUp_Add.RADIO_RECURRING_END_DATE_END_BY 			= 3; 

Popup_FollowUp_Add.DATE_FORMAT								= 'Y-m-d H:i:s';

Popup_FollowUp_Add.VALIDATION_REASON_START_DATE				= 'The Date must be in the future.';
Popup_FollowUp_Add.VALIDATION_REASON_END_DATE				= 'The End Date must be after the start date.';

Popup_FollowUp_Add.getCustomerGroupLink	= function(iAccountId, sName)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.span(sName)
			);
};

Popup_FollowUp_Add.getAccountLink	= function(iId, sName)
{
	var sUrl	= 'flex.php/Account/Overview/?Account.Id=' + iId;
	return 	$T.div({class: 'popup-followup-detail-subdetail account'},
				$T.div({class: 'account-id'},
					$T.img({src: Popup_FollowUp_Add.DETAILS_ACCOUNT_IMAGE_SOURCE}),
					$T.a(
						iId + ': '
					)
				),
				$T.a({class: 'account-name'},
					sName
				)
			);
};

Popup_FollowUp_Add.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Add.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.span(sName)
			);
};

Popup_FollowUp_Add.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Add.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.span('FNN : ' + sFNN)
			);
};

Popup_FollowUp_Add.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Add.DETAILS_TICKET_IMAGE_SOURCE}),
				$T.span('Ticket ' + iTicketId + ' (' + sContact + ')')
			);
};

Popup_FollowUp_Add._getTypeElement	= function(iType)
{
	var sText	= Flex.Constant.arrConstantGroups.followup_type[iType].Name;
	var sImgSrc	= null;
	switch (iType)
	{
		case $CONSTANT.FOLLOWUP_TYPE_ACTION:
			sImgSrc	= Popup_FollowUp_Add.TYPE_ACTION_IMAGE_SOURCE;
			break;
		case $CONSTANT.FOLLOWUP_TYPE_NOTE:
			sImgSrc	= Popup_FollowUp_Add.TYPE_NOTE_IMAGE_SOURCE;
			break;
		case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
			sImgSrc	= Popup_FollowUp_Add.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;
			break;
	}
	
	return 	$T.div({class: 'popup-followup-details-type'},
				$T.img({src: sImgSrc, alt: sText, title: sText}),
				$T.span(
					sText
				)
			);	
};

Popup_FollowUp_Add.getRecurrencePeriodsAsOptions	= function(fnCallback)
{
	var aOptions	= [];
	var oPeriods	= Flex.Constant.arrConstantGroups.followup_recurrence_period;
	for (var iId in oPeriods)
	{
		aOptions.push(
			$T.option({value: iId},
				oPeriods[iId].Name + '(s)'
			)
		);
	}
	
	fnCallback(aOptions);
};

Popup_FollowUp_Add.calculateEndDate	= function(sStartDate, iRecurrenceMultiplier, iRecurrencePeriod, iOccurences)
{
	// Perform a date shift for the correct period as many times as there are occurrences
	var oStartDate	= new Date(Date.parse(sStartDate.replace(/-/g, '/')));
	for (var i = 1; i < iOccurences; i++)
	{
		Popup_FollowUp_Add.shiftDate(oStartDate, iRecurrenceMultiplier, iRecurrencePeriod);
	}
	
	return oStartDate.$format(Popup_FollowUp_Add.DATE_FORMAT);
};

Popup_FollowUp_Add.calculateOccurrences	= function(sStartDate, sEndDate, iRecurrenceMultiplier, iRecurrencePeriod)
{
	// Perform a date shift for the correct period until the end date is reached and record the iterations
	var oStartDate	= new Date(Date.parse(sStartDate.replace(/-/g, '/')));
	var oEndDate	= new Date(Date.parse(sEndDate.replace(/-/g, '/')));
	var iIteration	= 0;
	
	while (oStartDate.getTime() <= oEndDate.getTime())
	{
		Popup_FollowUp_Add.shiftDate(oStartDate, iRecurrenceMultiplier, iRecurrencePeriod);
		iIteration++;
	}
	
	return iIteration;
};

Popup_FollowUp_Add.shiftDate	= function(oDate, iRecurrenceMultiplier, iRecurrencePeriod)
{
	switch (iRecurrencePeriod)
	{
		case $CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_WEEK:
			oDate.shift(iRecurrenceMultiplier * 7, 'days');
			break;
		case $CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_MONTH:
			oDate.shift(iRecurrenceMultiplier, 'months');
			break;
	}
};

Popup_FollowUp_Add.validateRecurrenceMultiplier	= function(sValue)
{
	var bIsDigits	= Reflex_Validation.digits(sValue);
	if (!bIsDigits || (parseInt(sValue) <= 0))
	{
		return false;
	}
	else
	{
		return true;
	}
};

Popup_FollowUp_Add.validateOccurrences	= function(sValue)
{
	var bIsDigits	= Reflex_Validation.digits(sValue);
	if (!bIsDigits || (parseInt(sValue) <= 1))
	{
		return false;
	}
	else
	{
		return true;
	}
};

Popup_FollowUp_Add._showValidationErrorPopup	= function(aErrors)
{
	// Build UL of error messages
	var oValidationErrors = $T.ul();
	
	for (var i = 0; i < aErrors.length; i++)
	{
		oValidationErrors.appendChild(
			$T.li(aErrors[i])
		);
	}
	
	// Show a popup containing the list
	Reflex_Popup.alert(
		$T.div({style: 'margin: 0.5em'},
			'The following errors have occured: ',
			oValidationErrors
		),
		{
			iWidth	: 30,
			sTitle	: 'Validation Errors'
		}
	);
};

