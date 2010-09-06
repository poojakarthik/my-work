
var Popup_Correspondence_Create	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, iCorrespondenceTemplateId)
	{
		$super(50);
		
		this._iTemplateId	= parseInt(iCorrespondenceTemplateId);
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oSourceType)
	{
		if (typeof oSourceType == 'undefined')
		{
			// Get the correspondence_source_type for the template
			this._oLoading	= new Reflex_Popup.Loading();
			this._oLoading.display();
			
			Correspondence_Template.getCorrespondenceSourceType(this._iTemplateId, this._buildUI.bind(this));
		}
		else
		{
			this._oLoading.hide();
			delete this._oLoading;
			
			var sTitleDetail	= '';
			var oInputContent	= null;
			switch (oSourceType.system_name)
			{
				case 'CSV':
					sTitleDetail	= ' - CSV File Upload';
					oInputContent	= this._buildCSVContent();
					break;
				case 'SQL':
					sTitleDetail	= ' - SQL Query';
					oInputContent	= this._buildSQLContent();
					break;
			}
			this._oSourceType	= oSourceType;
			
			this._oContent	=	$T.div({class: 'popup-correspondence-create'},
									oInputContent,
									$T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											'Create'
										).observe('click', this._doCreate.bind(this)),
										$T.button({class: 'icon-button'},
											'Cancel'
										).observe('click', this.hide.bind(this))
									)
								);
			
			this.setTitle('Create Correspondence' + sTitleDetail);
			this.addCloseButton();
			this.setContent(this._oContent);
			this.display();
		}
	},
	
	_buildCSVContent	: function()
	{
		// Delivery date time picker
		this._oDeliveryDateTime			= Popup_Correspondence_Create._createField('delivery');
		this._oDeliveryDateTimeHidden	= $T.input({type: 'hidden', name: 'delivery_datetime'});
		
		// File upload form
		var oForm	= 	$T.form({method: 'POST', action: '../admin/reflex.php/Correspondence/CreateFromCSV/', enctype: 'multipart/form-data'},
							$T.input({type: 'hidden', name: 'correspondence_template_id', value: this._iTemplateId}),
							$T.input({type: 'file', name: 'csv_file'}),
							this._oDeliveryDateTime.getElement(),
							this._oDeliveryDateTimeHidden
						);
		
		return $T.div(oForm);
	},
	
	_buildSQLContent	: function()
	{
		// Delivery date time picker
		this._oDeliveryDateTime	= Popup_Correspondence_Create._createField('delivery');
		
		// Run Query: now/on delivery (radio button group)
		this._oProcessSQLWhen	= Popup_Correspondence_Create._createField('run_query');
		
		return 	$T.div(
					this._oDeliveryDateTime.getElement(),
					this._oProcessSQLWhen.getElement()
				);
	},
	
	_doCreate	: function(oEvent)
	{
		switch (this._oSourceType.system_name)
		{
			case 'CSV':
				// CSV
				var oForm	= this._oContent.select('form').first();
				if (jQuery.json.jsonIframeFormSubmit(oForm, this._csvSubmitted.bind(this)))
				{
					this._oDeliveryDateTimeHidden.value	= this._oDeliveryDateTime.getElementValue();
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
				
				// Send request
				this._showLoading();
				fnSQL(this._iTemplateId, this._oDeliveryDateTime.getElementValue(), bProcessNow);
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
		debugger;
		// Success
		Reflex_Popup.alert('Correspondence Run created from SQL template', {sTitle: 'Success'});
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
		debugger;
		// Success
		Reflex_Popup.alert('Correspondence Run created from CSV file', {sTitle: 'Success'});
	},
	
	_ajaxError	: function(oResponse)
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		var oConfig	= {sTitle: 'Error'};
		if (oResponse.aErrors)
		{
			// Validation errors
			var oUL	= $T.ul();
			for (var i = 0; i < oResponse.aErrors.length; i++)
			{
				oUL.appendChild($T.li(oResponse.aErrors[i]));
			}
			
			Reflex_Popup.alert(
				$T.div({class: 'popup-correspondence-create-error-content'},
					$T.div(oResponse.sMessage),
					$T.div(oUL)
				), 
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
		oField.disableValidationStyling();
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
	}
});

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
				mVisible	: true
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
				fnPopulate	: Popup_Correspondence_Create._getRunQueryOptions
			}
		}
	}
});