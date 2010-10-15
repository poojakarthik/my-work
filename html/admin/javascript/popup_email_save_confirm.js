
var Popup_Email_Save_Confirm	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oData, fnCallback)
	{
		$super(50);
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._bFutureVersionsComboHasValues = false;
		this._aErrors = [];		
		this._fnCallback = fnCallback;
		this._oData = oData;	
		this._buildUI();		
	},
	
	
	_buildUI	: function()
	{
		this._oTemplateSelect		= 	$T.div(	{class: 'popup-email-html-save'},
											
											$T.div({class: 'report'}),
											$T.div({class: 'date'}),
											$T.div({class: 'footer'},
													$T.span({class: 'preview-button'},
															$T.button
																	({class: 'icon-button'},											
																	$T.img({src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE, alt: '', title: 'Preview'}),
																	$T.span('Preview')																	
																	).observe('click', this._preview.bind(this))
															
															),
													$T.span({class: 'save-button'},
															this._saveButton = $T.button({class: 'icon-button'},
																$T.img({src: Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
																$T.span('Save')
																	).observe('click', this._save.bind(this)),
															$T.button({class: 'icon-button'},
																$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
																$T.span('Cancel')
																	).observe('click', this._close.bind(this))
															)
													)
											);
				
		
			
		var oReportDiv = this._oTemplateSelect.select('div.report').last();
		oReportDiv.appendChild(this._createReport());
		
		var oDateSection = this._oTemplateSelect.select('div.date').first();
		oDateSection.appendChild(this._buildDateContent());
						
		this.setTitle('Save Email Template');
		
		this.addCloseButton(this._close.bind(this));
		this.setContent(this._oTemplateSelect);
		this.display();
		
		this._oLoadingPopup.display();
		this._comboFuture.populate();
	},
	
	
	_createReport : function()
	{
		
		this.oChangeReportDiv = document.createElement('div');		
	
		var ul = document.createElement('ul');
		var aKeys = Object.keys(this._oData.Report);
		
		
		var header = document.createElement('div');
		header.className = 'email-confirm-report-header';
		header.innerHTML = 'To ensure consistent rendering in different mail clients, your HTML will be modified:';
		
		
		var bChanges = false;
		for (var i = 0; i < aKeys.length; i++)
		{
			oChange	= this._oData.Report[aKeys[i]];
			if (oChange.length>0)
			{
				bChanges = true;
				var li = document.createElement('li');
				li.innerHTML = Popup_Email_Save_Confirm.HTML_CHANGES[aKeys[i]];
				ul.appendChild(li);
			}
		}
				
		if (bChanges)
		{
			this.oChangeReportDiv.appendChild(header);
			this.oChangeReportDiv.appendChild(ul);
		}
		return this.oChangeReportDiv;
	
	},
	
	_buildDateContent	: function()
	{
		// Fields
		this._oChangeDateTime			= this._createField('changeDate');
		this._oChangeDateTime.addOnChangeCallback(this._dateChanged.bind(this));
		this._oChangeDateTime.disableInput();
		this._oChangeDateTimeHidden	= $T.input({type: 'hidden', name: 'change_datetime'});
		this._oChangeNow				= this._createField('change_now');
		this._oChangeNow.addOnChangeCallback(this._changeNowChanged.bind(this));
		
		
		this._radioSelect = document.createElement('input');
		this._radioSelect.type = 'radio';
		this._radioSelect.value = 'selected_date';
		this._radioSelect.checked = false;
		
		this._radioSelect.label = 'select date';
		
		this._radioSelect.name = 'date_select';
		
		this._radioSelect.className = 'date-select';
		this._radioSelect.observe('click',this._changeNowChanged.bind(this));
		
		this._radioNow = document.createElement('input');
		this._radioNow.type = 'radio';
		this._radioNow.value = 'date_now';
		this._radioNow.checked = true;
		this._oData.oTemplateDetails.effective_datetime = new Date().$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat);
		
		this._radioNow.label = 'Now';
		
		this._radioNow.name = 'date_select';
		
		this._radioNow.className = 'date-select';
		this._radioNow.observe('click',this._changeNowChanged.bind(this));
		
		this._comboFuture = Control_Field.factory('select',{sLabel: 'Select End Date',fnPopulate: this.getFutureVersions.bind(this),mEditable	: true,
											mMandatory	: true,});
		this._comboFuture.setVisible(false);
		this._comboFuture.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._comboFuture.disableValidationStyling();
		this._comboFuture.addOnChangeCallback(this._endDateChanged.bind(this));
		
		this._comboFutureLabel = document.createElement('span');
		this._comboFutureLabel.innerHTML = 'End Date';
		this._comboFutureLabel.style.display = 'none';
		// File upload form
		var oForm	= 	$T.div(	{class: 'email-template-effective-date-select'}	,					
							$T.table({class: 'reflex input'},
								$T.tbody(
									
									$T.tr(
										$T.th('Effective Date'),
										$T.td(										
										this._radioNow,
											$T.span('Immediately')											
										)),
										$T.tr(
										$T.th(''),
										$T.td(
											this._radioSelect,
											this._oChangeDateTime.getElement()
										)
									),
									$T.tr(
										$T.th(this._comboFutureLabel),
										$T.td(
											this._comboFuture.getElement(),
										this._endDateMessage = $T.div(	{class: 'email-template-enddate-message'})	
										)
									)
								)
							)
							
							
						);
		
		return $T.div(oForm);
	},
	
	_formatDate: function(sDate)
	{
		return new Date(Date.$parseDate(sDate,'Y-m-d').getTime()).$format('j F Y');	
	},
	
	_endDateChanged : function()
	{
		
		this._oData.oTemplateDetails.end_datetime = this._comboFuture.getElementValue();
		var sEndDateDescription = '';
		var iNumberOfAffectedVersions = 0;
		
		if (typeof (this._futureVersions) != 'undefined' && this._futureVersions!=null)
		{
			for (var i=0;i< this._futureVersions.length;i++)
			{						
				if (this._futureVersions[i].effective_datetime>this._oData.oTemplateDetails.effective_datetime &&  this._futureVersions[i].effective_datetime<this._comboFuture.getElementValue())
				{
					sEndDateDescription+= "<li>" + this._futureVersions[i].description + "(From: " + this._formatDate(this._futureVersions[i].effective_datetime) + ")</li>";
					iNumberOfAffectedVersions++;
				}
				else if (this._futureVersions[i].effective_datetime==this._oData.oTemplateDetails.effective_datetime)
				{
					sEndDateDescription+= "<li>" + this._futureVersions[i].description + "(From: " + this._formatDate(this._futureVersions[i].effective_datetime) + ")</li>";
					iNumberOfAffectedVersions++;				
				}			
			}
		}
		
		var sEndDateMessage = '';
		iNumberOfAffectedVersions>1?sEndDateMessage = "<span>The following versions will be cancelled:</span><ul>" + sEndDateDescription:iNumberOfAffectedVersions==1?sEndDateMessage = "<span>The following version will be cancelled:</span><ul>" + sEndDateDescription:null;
		sEndDateMessage!=''?sEndDateMessage+='</ul>':null;
		this._endDateMessage.innerHTML = sEndDateMessage;	
	},
	
	
	_dateChanged: function()
	{		
		this._oData.oTemplateDetails.effective_datetime = this._oChangeDateTime.getElementValue();
		this._oChangeDateTime.validate()?this._comboFuture.populate():null;
	},
	
	_changeNowChanged	: function()
	{		
		if (this._radioNow.checked)
		{
			var sNow	= new Date().$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat);
			this._oChangeDateTime.clearValue();
			this._oChangeDateTime.disableInput();			
			this._oData.oTemplateDetails.effective_datetime = sNow;			
			this._comboFuture.populate();
		}
		else
		{
			this._oData.oTemplateDetails.effective_datetime = null;
			this._oChangeDateTime.clearValue();
			this._oChangeDateTime.enableInput();
			this._comboFuture.populate();
		}
	},
	
	_createField	: function(sName)
	{
		var oConfig	= Popup_Email_Save_Confirm.FIELD_CONFIG[sName];
		var oField	= Control_Field.factory(oConfig.sType, oConfig.oConfig);
		oField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		return oField;
	},
	
	_validate: function()
	{
		this._oChangeDateTime.setMandatory(true);
		var aErrors = [];
		
		for (var i = 0; i < this._oData.Errors.length; i++)
		{
			aErrors.push(this._oData.Errors[i]);		
		}
		
		if (typeof (this._oData.oTemplateDetails.effective_datetime) == 'undefined')
		{
			aErrors.push('you must specify the effective date');		
		}
		else if (!this._oChangeDateTime.validate() && !this._radioNow.checked)
		{
			aErrors.push('You must specify a valid effective date.');
		}
		else if (this._bFutureVersionsComboHasValues && (typeof(this._oData.oTemplateDetails.end_datetime)=='undefined' ||  this._oData.oTemplateDetails.end_datetime == null))
		{			
			aErrors.push('you must either specify an end date, or select an effective date further into the future.');
		}
		
		this._oChangeDateTime.setMandatory(false);
		return aErrors;
	},
	
	
	
	_save: function()
	{		
		this._bFutureVersionsComboHasValues?null:this._oData.oTemplateDetails.end_datetime=Popup_Email_Save_Confirm.END_OF_TIME;				
		
		if (this._radioNow.checked)
		{
			this._oData.oTemplateDetails.effective_datetime = new Date().$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat);		
		}
		
		var aErrors = this._validate();	
		if (aErrors.length>0)
		{
			var oErrors = document.createElement("ul");
			for (var i=0;i<aErrors.length;i++)
			{
				var li = document.createElement("li");
				var span = document.createElement("span");
				span.innerHTML = aErrors[i];
				li.appendChild(span)
				oErrors.appendChild(li);					
			}			
			Reflex_Popup.alert(oErrors, {sTitle: 'Email Template Save Errors'});		
		}
		else
		{
			if (this._futureVersions!=null)
			{
				for (var i=0;i<this._futureVersions.length;i++)
				{
					if (this._futureVersions[i].effective_datetime<this._oData.oTemplateDetails.effective_datetime)
					{
						this._futureVersions[i].end_datetime >this._oData.oTemplateDetails.effective_datetime? this._futureVersions[i].end_datetime = this._oData.oTemplateDetails.effective_datetime:null;				
					}
					else
					{
						sEndDate = new Date(Date.$parseDate(this._futureVersions[i].effective_datetime,'Y-m-d').getTime()-1000).$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat);
						this._oData.oTemplateDetails.end_datetime >this._futureVersions[i].effective_datetime?this._futureVersions[i].end_datetime = sEndDate:null;					
					}				
				}
			}
				
			this._oData.aFutureVersions = this._futureVersions;	
			this._oData.Confirm = true;						
			this.hide();			
			this._fnCallback(this._oData);
		}
	},
	
	_preview: function()
	{
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successPreviewCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(this._oData.oTemplateDetails.email_html);	
	},
	
	errorCallback: function()
	{
		alert('error');
	},	
	
	successPreviewCallback: function (oResponse)
	{
	    if (oResponse.Success)
		{
			this._oLoadingPopup.hide();	
			var html = oResponse.html;
			new Popup_email_HTML_Preview(html, this._unhide.bind(this));
			this.hide();
		}
		else
		{
			Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template HTML Preview Error')();				
		}
			
	},
	
	_close : function ()
	{
		this._oLoadingPopup.hide();	
		this.hide();	
	},
	
	_unhide: function()
	{
		this.display();	
	},
	
	getFutureVersions	: function(fnCallback, oResponse)
	{		
		var aOptions	= [];
		
		if (typeof this._futureVersions != 'undefined')
		{
			this._comboFuture.emptyList();
			fnCallback(this._createSelectList());
			this._bFutureVersionsComboHasValues?this._comboFuture.setVisible(true):this._comboFuture.setVisible(false);
			this._bFutureVersionsComboHasValues?this._comboFutureLabel.style.display = '':this._comboFutureLabel.style.display = 'none';
			this._bStartDateEqualsFutureStartDate?this._endDateChanged():null;	
			this._oLoadingPopup.hide();
		}
		else if (typeof oResponse == 'undefined')
		{
			
			// Make Request
			var fnGetTemplates	=	jQuery.json.jsonFunction(
										this.getFutureVersions.bind(this,fnCallback),
										Popup_Email_Save_Confirm._ajaxError,
										'Email_Text_Editor', 'getFutureVersions'
									);
			fnGetTemplates(this._oData.oTemplateDetails.email_template_id);
		}
		else
		{
			// Build array of option elements
			this._comboFuture.emptyList();
			if (!oResponse.Success)
			{				
				Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template HTML Save Error')();			
			}
			else
			{
				// Success
				this._futureVersions = oResponse.aTemplateDetails;
				fnCallback(this._createSelectList());
				this._bFutureVersionsComboHasValues?this._comboFuture.setVisible(true):this._comboFuture.setVisible(false);
				this._bFutureVersionsComboHasValues?this._comboFutureLabel.style.display = '':this._comboFutureLabel.style.display = 'none';
				this._bStartDateEqualsFutureStartDate?this._endDateChanged():null;	
				this._oLoadingPopup.hide();				
			}		
		}		
	},
		
	_createSelectList: function()
	{
		this._bFutureVersionsComboHasValues = false;
		this._bStartDateEqualsFutureStartDate = false;
		
		var aOptions	= [$T.option({value: Popup_Email_Save_Confirm.END_OF_TIME},
						"No end date."
				)];
		var oTemplate	= null;
				
		if (this._futureVersions!=null)
		{
			for (var i=0;i< this._futureVersions.length;i++)
			{				
				if (isNaN(i))
				{
					continue;
				}			
				
				oTemplate	= this._futureVersions[i];
				if (oTemplate.effective_datetime>this._oData.oTemplateDetails.effective_datetime) 
				{
					this._bFutureVersionsComboHasValues = true;
					aOptions.push(
						$T.option({value: oTemplate.effective_datetime},
								this._formatDate(oTemplate.effective_datetime) + " ("+ oTemplate.description + ")"
						)
					);	
				}
				else if (oTemplate.effective_datetime==this._oData.oTemplateDetails.effective_datetime)
				{
					this._bStartDateEqualsFutureStartDate = true;
				
				}
			}
		}
		return this._bFutureVersionsComboHasValues?aOptions:[];
	}
	
});	


Object.extend(Popup_Email_Save_Confirm,
{
	END_OF_TIME		: "9999-12-31",
	
	FIELD_CONFIG	:
	{
		changeDate	:
		{
			sType	: 'date-picker',
			oConfig	:
			{
				sLabel		: 'Change On', 
				sDateFormat	: 'Y-m-d', 
				bTimePicker	: false,
				iYearStart	: 2010,
				iYearEnd	: new Date().getFullYear() + 1,
				mMandatory	: false,
				mEditable	: true,
				mVisible	: true,
				bDisableValidationStyling	: false,
				fnValidate	: function(sDate){return sDate<new Date((new Date()).getTime()-1000).$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat)?false:true;}	
			}
		},

		change_now	:
		{
			sType	: 'checkbox',
			oConfig	:
			{
				sLabel		: 'Change Now',
				mMandatory	: true,
				mEditable	: true,
				mVisible	: true,
				bDisableValidationStyling	: true
			}
		}
	},	
	
	HTML_CHANGES	:
	{
		javascript	: 'Javascript tags and code removed',
		events		: 'Event triggers (eg \'onClick\' removed',
		form		: 'HTML Forms changed to DIVs',
		input		: 'form input elements removed',
		link		: 'link tags removed (mainly used for external stylesheets)',
		style		: 'internal css removed',
		head		: 'header section removed'
		
	},
	
	startDateValidation : function(sDate)
	{			
		return sDate<new Date().$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat)?false:true;		
	}	
});