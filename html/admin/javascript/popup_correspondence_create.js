
var Popup_Correspondence_Create	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(39);
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oSourceType)
	{
		var oTemplateSelect		= 	Control_Field.factory(
				'select', 
				{
					sLabel		: 'Template',
					mEditable	: true,
					mMandatory	: true,
					fnPopulate	: Correspondence_Template.getAllWithNonSystemSourcesAsSelectOptions
				}
			);
		oTemplateSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oTemplateSelect.disableValidationStyling();
		oTemplateSelect.addOnChangeCallback(this._templateChanged.bind(this));
		this._oTemplateSelect	= oTemplateSelect;
		
		var oTemplateSection	= new Section(false, 'template-section');
		oTemplateSection.setTitleText('Choose Template');
		oTemplateSection.setContent(
			this._oTemplateSelect.getElement()
		);
		
		this._oSection	= new Section(true);
		this._oSection.setTitleText('Schedule Information');
		this._oSection.setContent(
			$T.div({class: 'no-template'}, 
				'No Template has been selected'
			)
		);
		
		this._oContent	=	$T.div({class: 'popup-correspondence-create'},
								oTemplateSection.getElement(),
								this._oSection.getElement(),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										'Create'
									).observe('click', this._doCreate.bind(this)),
									$T.button({class: 'icon-button'},
										'Cancel'
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle('Schedule Correspondence');
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
	},
	
	_templateChanged	: function()
	{
		var iTemplateId	= parseInt(this._oTemplateSelect.getElementValue());
		if (!isNaN(iTemplateId))
		{
			this._iTemplateId	= iTemplateId;
			Correspondence_Template.getCorrespondenceSourceType(iTemplateId, this._templateSourceLoaded.bind(this));
		}
	},
	
	_templateSourceLoaded	: function(oSourceType)
	{
		var oInputContent	= null;
		switch (oSourceType.system_name)
		{
			case 'CSV':
				oInputContent	= this._buildCSVContent();
				break;
			case 'SQL':
				oInputContent	= this._buildSQLContent();
				break;
		}
		this._oSection.setContent(oInputContent);
		this._oSourceType	= oSourceType;
	},
	
	_buildCSVContent	: function()
	{
		// Fields
		this._oDeliveryDateTime			= Popup_Correspondence_Create._createField('delivery');
		this._oDeliveryDateTimeHidden	= $T.input({type: 'hidden', name: 'delivery_datetime'});
		this._oDeliverNow				= Popup_Correspondence_Create._createField('deliver_now');
		this._oDeliverNow.addOnChangeCallback(this._deliverNowChanged.bind(this));
		this._oForceIfNoData			= $T.input({type: 'hidden', name: 'force_if_no_data', value: 0});
		
		// File upload form
		var oForm	= 	$T.form({method: 'POST', action: '../admin/reflex.php/Correspondence/CreateFromCSV/', enctype: 'multipart/form-data'},
							$T.input({type: 'hidden', name: 'correspondence_template_id', value: this._iTemplateId}),
							this._oForceIfNoData,
							$T.table({class: 'reflex input'},
								$T.tbody(
									$T.tr(
										$T.th('CSV File'),
										$T.td(
											$T.input({class: 'csv-file-input', type: 'file', name: 'csv_file', size: 38})
										)
									),
									$T.tr(
										$T.th('When to Deliver'),
										$T.td(
											this._oDeliveryDateTime.getElement(),
											this._oDeliveryDateTimeHidden
										)
									),
									$T.tr(
										$T.th(),
										$T.td({class: 'deliver-now'},
											this._oDeliverNow.getElement(),
											$T.span(' Deliver Immediately')
										)
									)
								)
							)
						);
		
		return $T.div(oForm);
	},
	
	_buildSQLContent	: function()
	{
		// Fields
		this._oDeliveryDateTime	= Popup_Correspondence_Create._createField('delivery');
		this._oProcessSQLWhen	= Popup_Correspondence_Create._createField('run_query');
		this._oDeliverNow		= Popup_Correspondence_Create._createField('deliver_now');
		this._oDeliverNow.addOnChangeCallback(this._deliverNowChanged.bind(this));
		
		return 	$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th('When to Process'),
							$T.td(this._oProcessSQLWhen.getElement())
						),
						$T.tr(
							$T.th('When to Deliver'),
							$T.td(this._oDeliveryDateTime.getElement())
						),
						$T.tr(
							$T.th(),
							$T.td({class: 'deliver-now'},
								this._oDeliverNow.getElement(),
								$T.span(' Deliver Immediately')
							)
						)	
					)
				);
	},
	
	_deliverNowChanged	: function(oField)
	{
		if (oField.getElementValue())
		{
			var sNow	= new Date().$format(Popup_Correspondence_Create.FIELD_CONFIG.delivery.oConfig.sDateFormat);
			this._oDeliveryDateTime.setValue(sNow);
		}
	},
	
	_doCreate	: function()
	{
		this._create();
	},
	
	_create	: function(bForceIfNoData)
	{
		switch (this._oSourceType.system_name)
		{
			case 'CSV':
				// CSV
				var oForm	= this._oContent.select('form').first();
				if (jQuery.json.jsonIframeFormSubmit(oForm, this._csvSubmitted.bind(this)))
				{
					this._oForceIfNoData.value	= (bForceIfNoData ? 1 : 0);
					var sDeliveryDateTime	= this._oDeliveryDateTime.getElementValue();
					if (this._oDeliverNow.getElementValue())
					{
						sDeliveryDateTime	= new Date().$format('Y-m-d H:i:s');
					}
					this._oDeliveryDateTimeHidden.value	= sDeliveryDateTime;
					this._showLoading();
					oForm.submit();
				}
				break;
			case 'SQL':
				// SQL
				var fnSQL	=	jQuery.json.jsonFunction(
									this._sqlSubmitted.bind(this), 
									this._sqlSubmitted.bind(this), 
									'Correspondence_Run', 
									'scheduleRunFromSQLTemplate'
								);
				
				// Process now?
				var iProcessNow	= parseInt(this._oProcessSQLWhen.getElementValue());
				var bProcessNow	= null;
				switch (iProcessNow)
				{
					case Popup_Correspondence_Create.RUN_QUERY_NOW:
						bProcessNow	= true;
						break;
					case Popup_Correspondence_Create.RUN_QUERY_ON_DELIVERY:
						bProcessNow	= false;
						break;
					default:
						bProcessNow	= null;
				}
				
				var sDeliveryDateTime	= this._oDeliveryDateTime.getElementValue();
				if (this._oDeliverNow.getElementValue())
				{
					sDeliveryDateTime	= new Date().$format('Y-m-d H:i:s');
				}
				
				// Send request
				this._showLoading();
				fnSQL(this._iTemplateId, sDeliveryDateTime, bProcessNow, !!bForceIfNoData);
				break;
		}
	},
	
	_sqlSubmitted	: function(oResponse)
	{
		if (!oResponse.bSuccess)
		{
			// Error
			this._ajaxError(oResponse);
			return;
		}
		
		this._hideLoading();
		
		// Success
		Reflex_Popup.alert('Correspondence Run created from SQL template', {sTitle: 'Success'});
		this.hide();
	},
	
	_csvSubmitted	: function(oResponse)
	{
		if (!oResponse.bSuccess)
		{
			// Error
			this._ajaxError(oResponse);
			return;
		}
		
		this._hideLoading();
		
		// Success
		Reflex_Popup.alert('Correspondence Run created from CSV file', {sTitle: 'Success'});
		this.hide();
	},
	
	_ajaxError	: function(oResponse, sDescription)
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		var oConfig	= {sTitle: 'Error'};
		if (oResponse.oException)
		{
			var oEx	= oResponse.oException;
			switch (oEx.iError)
			{
				case $CONSTANT.CORRESPONDENCE_RUN_ERROR_NO_DATA:
					// No data to process, confirm the processing
					var sNoDataDescription	= '';
					switch (this._oSourceType.system_name)
					{
						case 'CSV':
							sNoDataDescription	= 'CSV File';
							break;
						case 'SQL':
							sNoDataDescription	= 'SQL Query Result';
							break;
					}
					Reflex_Popup.alert('There was no data in the ' + sNoDataDescription, oConfig);
					break;
				case $CONSTANT.CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX:
					// SQL error
					Reflex_Popup.alert('There is an error in the configuration of the Template', oConfig);
					break;
				case $CONSTANT.CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT:
					// Show alert outlining a summary of each error
					Popup_Correspondence_Create._showExceptionPopup(oEx);
					break;
			}
		}
		else if (oResponse.aErrors)
		{
			// Multiple input errors
			Reflex_Popup.alert(
				Popup_Correspondence_Create._getMultipleErrorHTML(oResponse.aErrors, oResponse.sMessage), 
				oConfig
			);
		}
		else if (oResponse.sMessage)
		{
			// Exception/Error message
			Reflex_Popup.alert(oResponse.sMessage, oConfig);
		}
		else if (oResponse.ERROR)
		{
			// System error, not thrown by handler code
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
		else if (oResponse.Message)
		{
			// TODO: DEV ONLY -- Disable for production
			// IFrame ajax output, most likely php error
			oConfig.iWidth	= 25;
			Reflex_Popup.alert(
				$T.div({class: 'popup-correspondence-create-error-content'},
					$T.div('A server error has occured, please contact YBS for assistance.'),
					$T.textarea(
						oResponse.Message
					)
				),
				oConfig
			);
		}
	},
	
	_showLoading	: function()
	{
		if (!this._oLoading)
		{
			this._oLoading	= new Reflex_Popup.Loading('');
		}
		this._oLoading.display();
	},
	
	_hideLoading	: function()
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
	}
});

// Static functions

Object.extend(Popup_Correspondence_Create,
{
	RUN_QUERY_NOW			: 1,
	RUN_QUERY_ON_DELIVERY	: 2,
	
	_createField	: function(sName)
	{
		var oConfig	= Popup_Correspondence_Create.FIELD_CONFIG[sName];
		var oField	= Control_Field.factory(oConfig.sType, oConfig.oConfig);
		oField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		return oField;
	},
	
	_getRunQueryOptions	: function(fnCallback)
	{
		fnCallback(
			[
	      	 	$T.option({value: Popup_Correspondence_Create.RUN_QUERY_NOW}, 
	      	 		'Now'
	      	 	), 
	      	 	$T.option({value: Popup_Correspondence_Create.RUN_QUERY_ON_DELIVERY}, 
	      	 		'At the Time of Delivery'
	      	 	)
	      	]
	    );
	},
	
	_getMultipleErrorHTML	: function(aErrors, sMessage)
	{
		// Validation errors
		var oUL	= $T.ul();
		for (var i = 0; i < aErrors.length; i++)
		{
			oUL.appendChild($T.li(aErrors[i]));
		}
		
		return 	$T.div({class: 'popup-correspondence-create-error-content'},
					$T.div(sMessage ? sMessage : ''),
					$T.div(oUL)
				);
	},
	
	_showExceptionPopup	: function(oException)
	{
		var oTBody		= $T.tbody();
		var oErrorTable	=	$T.table({class: 'reflex input correspondence-csv-exception-summary'},
								oTBody
							);
		for (var sErrorType in oException.aReport)
		{
			var mErrorType	= oException.aReport[sErrorType];
			var mErrorName	= null;
			var mErrorText	= null;
			if (sErrorType != 'success')
			{
				// Count the number of lines that errored
				var iCount	= 0;
				for (var i in mErrorType)
				{
					if (!isNaN(i))
					{
						iCount++;
					}
				}
				
				// Generic error, contains array of line numbers where the error occurred
				if (iCount)
				{
					if (Popup_Correspondence_Create.CSV_ERROR_NAMES[sErrorType])
					{
						mErrorName	= Popup_Correspondence_Create.CSV_ERROR_NAMES[sErrorType]
					}
					else
					{
						mErrorName	= Popup_Correspondence_Create.CSV_ERROR_NAME_UNKNOWN + ' (' + sErrorType + ')';
					}
					mErrorText	= iCount + ' lines';
				}
			}
			
			if (mErrorName && mErrorText)
			{
				// Add a row to the error output table
				oTBody.appendChild(
					$T.tr(
						$T.th(mErrorName),
						$T.td(mErrorText)
					)
				);
			}
		}
		
		// Show modified yesnocancel popup
		Reflex_Popup.yesNoCancel(
			oErrorTable,
			{
				sNoLabel		: 'Download Full Error Information', 
				sYesLabel		: 'OK',
				fnOnNo			: Popup_Correspondence_Create._downloadErrorCSV.bind(this, oException.sFileName),
				bOverrideStyle	: true,
				iWidth			: 45,
				sTitle			: 'Error Summary'
			}
		);
	},
	
	_downloadErrorCSV	: function(sFilename)
	{
		sFilename	= sFilename.replace(/\//g, "\\");
		window.location	= 'reflex.php/Correspondence/DownloadCSVErrorFile/' + encodeURIComponent(sFilename);
	}
});

// These static properties require references to other static properties (at time of definition) so they are separate
Object.extend(Popup_Correspondence_Create,
{
	FIELD_CONFIG	:
	{
		delivery	:
		{
			sType	: 'date-picker',
			oConfig	:
			{
				sLabel		: 'Delivery On', 
				sDateFormat	: 'Y-m-d H:i:s', 
				bTimePicker	: true,
				iYearStart	: 2010,
				iYearEnd	: new Date().getFullYear() + 1,
				mMandatory	: true,
				mEditable	: true,
				mVisible	: true,
				bDisableValidationStyling	: true
			}
		},
		run_query	:
		{
			sType	: 'select',
			oConfig	:
			{
				sLabel		: 'Run Query',
				mMandatory	: true,
				mEditable	: true,
				mVisible	: true,
				fnPopulate	: Popup_Correspondence_Create._getRunQueryOptions,
				bDisableValidationStyling	: true
			}
		},
		deliver_now	:
		{
			sType	: 'checkbox',
			oConfig	:
			{
				sLabel		: 'Deliver Now',
				mMandatory	: true,
				mEditable	: true,
				mVisible	: true,
				bDisableValidationStyling	: true
			}
		}
	},
	
	CSV_ERROR_NAMES	:
	{
		//invalid_account_id			: 'Invalid Account Id',
		customer_group_account_id	: 'Neither Customer Group nor Account Id provided',
		account_name				: 'Account Name is missing',
		first_name					: 'Contact First Name is missing',
		last_name					: 'Contact Last Name is missing',
		address_line_1				: 'Address Line 1 is missing',
		suburb						: 'Suburb is missing',
		postcode					: 'Postcode is missing',
		state						: 'State missing',
		customer_group_conflict		: "Customer Group doesn't match the Account's Customer Group",
		email						: 'Delivery Method is POST and no email is provided',
		delivery_method_account_id	: 'Account Id and Delivery Method is missing',
		column_count				: 'Column Count Mismatch'
	},
	
	CSV_ERROR_NAME_UNKNOWN	: 'Unkown Error'
});

// Special cases
Popup_Correspondence_Create.CSV_ERROR_NAMES['invalid account id']	= 'Invalid Account Id';