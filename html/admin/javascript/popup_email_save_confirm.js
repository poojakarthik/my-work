
var Popup_Email_Save_Confirm	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oResponse, fnCallback)
	{
		$super(30);
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._fnCallback = fnCallback;
		this._oResponse = oResponse;
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(sHTML)
	{
		var oTemplateSelect		= 	$T.div({class: 'popup-email-html-preview'},
									$T.div({class: 'report'}
										
									),
									$T.div({class: 'date'}
										
									),
									
									$T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
											$T.span('Save')
										).observe('click', this._save.bind(this)),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										).observe('click', this._close.bind(this)),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE, alt: '', title: 'Preview'}),
											$T.span('Preview')
										).observe('click', this._preview.bind(this))
									)
									

									
								);
		this._oTemplateSelect	= oTemplateSelect;		
		this.oChangeReportDiv = document.createElement('div');
		var header = document.createElement('div');
		header.innerHTML = 'In order to make it render consistently across different mail clients, the HTML you supplied will be modified as follows:';
			this.oChangeReportDiv.appendChild(header);
		var ul = this._createReport();
			this.oChangeReportDiv.appendChild(ul);
			
		var oReportDiv = oTemplateSelect.select('div.report').last();
		oReportDiv.appendChild(this.oChangeReportDiv);
		
		var oDateSection = oTemplateSelect.select('div.date').first();
		oDateSection.appendChild(this._buildDateContent());
						
		this.setTitle('Email Save Confirm');
		
		this.addCloseButton(this._close.bind(this));
		this.setContent(oTemplateSelect);
		this.display();
	},
	
	_createReport : function()
	{
		
		var ul = document.createElement('ul');
		var aKeys = Object.keys(this._oResponse.Report);
		
		for (var i = 0; i < aKeys.length; i++)
				{
					oChange	= this._oResponse.Report[aKeys[i]];
					if (oChange.length>0)
					{
						var li = document.createElement('li');
						li.innerHTML = Popup_Email_Save_Confirm.HTML_CHANGES[aKeys[i]];
						ul.appendChild(li);
					}
				}
		return ul;
	
	},
	
	_buildDateContent	: function()
	{
		// Fields
		this._oChangeDateTime			= this._createField('changeDate');
		this._oChangeDateTimeHidden	= $T.input({type: 'hidden', name: 'change_datetime'});
		this._oChangeNow				= this._createField('change_now');
		this._oChangeNow.addOnChangeCallback(this._changeNowChanged.bind(this));
		
		
		this._radioSelect = document.createElement('input');
		this._radioSelect.type = 'radio';
		this._radioSelect.value = 'selected_date';
		this._radioSelect.checked = true;
		//radio.setAttribute('label', $labels[i]);
		this._radioSelect.label = 'select date';
		//radio.setAttirbute('name', uniqueName);
		this._radioSelect.name = 'date_select';
		//radio.id = 'selected_date';
		this._radioSelect.className = 'date-select';
		this._radioSelect.observe('click',this._changeNowChanged.bind(this));
		
		this._radioNow = document.createElement('input');
		this._radioNow.type = 'radio';
		this._radioNow.value = 'date_now';
		this._radioNow.checked = false;
		//radio.setAttribute('label', $labels[i]);
		this._radioNow.label = 'Now';
		//radio.setAttirbute('name', uniqueName);
		this._radioNow.name = 'date_select';
		//radio.id = 'selected_date';
		this._radioNow.className = 'date-select';
		this._radioNow.observe('click',this._changeNowChanged.bind(this));
		
		
		
		
		
		// File upload form
		var oForm	= 	$T.div(	{class: 'email-template-effective-date-select'}	,					
							$T.table({class: 'reflex input'},
								$T.tbody(
									
									$T.tr(
										$T.th('Effective Date'),
										$T.td(
											this._radioSelect,
											$T.span('Select Date'),
											this._oChangeDateTime.getElement()
											
										)),
										$T.tr(
										$T.th(''),
										$T.td(
											this._radioNow,
											$T.span('Now')
										)
									)
								)
							)
						);
		
		return $T.div(oForm);
	},
	
	_changeNowChanged	: function()
	{
		
		if (this._radioNow.checked)
		{
			var sNow	= new Date().$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat);
			this._oChangeDateTime.setValue(sNow);
			this._oChangeDateTime.disableInput();
			this._oResponse.effectiveDate = sNow;
		}
		else
		{
			this._oChangeDateTime.enableInput();
		}
	},
	
		_toggleChangeNow	: function()
	{
		this._oChangeNow.setValue(!this._oChangeNow.getElementValue());
		this._changeNowChanged();
	},
	
	_createField	: function(sName)
	{
		var oConfig	= Popup_Email_Save_Confirm.FIELD_CONFIG[sName];
		var oField	= Control_Field.factory(oConfig.sType, oConfig.oConfig);
		oField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		return oField;
	},
	
	
	
	
	
	_save: function()
	{
		if (typeof (this._oResponse.effectiveDate) == 'undefined')
		{
			alert('you must specify the effective date');
		}
		else
		{
			this.hide();
			this._oResponse.Confirm = true;
			this._fnCallback(this._oResponse);
		}
	},
	
	_preview: function()
	{
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successPreviewCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(this._oResponse.oTemplateDetails.email_html);
	
	},
	
	errorCallback: function()
	{
		  // This gets called when it fails, happens rarely
		  alert('error');
	},
	
	
	successPreviewCallback: function (oResponse)
	{
	    this._oLoadingPopup.hide();	
		 var html = oResponse.html;
		//this.oChangeReportDiv.innerHTML = html;
		new Popup_email_HTML_Preview(html, this._unhide.bind(this));
		this.hide();		
	},
	
	_close : function ()
	{
		this.hide();
		
	
	},
	_unhide: function()
	{
		this.display();
	
	}
});	


// These static properties require references to other static properties (at time of definition) so they are separate
Object.extend(Popup_Email_Save_Confirm,
{
	FIELD_CONFIG	:
	{
		changeDate	:
		{
			sType	: 'date-picker',
			oConfig	:
			{
				sLabel		: 'Change On', 
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
		form		: '\'action\' and \'method\' attributes removed from form element',
		input		: 'form input elements removed',
		
	},
	
	CSV_ERROR_NAME_UNKNOWN	: 'Unkown Error',
	
	CORRESPONDENCE_RUN_ERROR_DUPLICATE_FILE	: 4
});
	