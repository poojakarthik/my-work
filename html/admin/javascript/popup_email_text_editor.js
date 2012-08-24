
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{	
	
	initialize	: function($super, iTemplateDetailsId, sTemplateName, customerGroupName, fnCallback, iTemplateId, bReadOnly)
	{			
		
		$super(80);			
	
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		
		this._bReadOnly 			= (typeof bReadOnly != 'undefined') ? bReadOnly : false;
		this._fnCallback 			= fnCallback;
		this._sTemplateName 		= sTemplateName;
		this._sCustomerGroupName 	= customerGroupName;
		this._iTemplateDetailsId 	= iTemplateDetailsId;
		this._iTemplateId			= iTemplateId;
		
		// The framework for the GUI
		this._oContent	= 	$T.div({class: 'popup-email-text-edit'},
								$T.div({class: 'subject-description'}
									// Content to come
								),	
								$T.div({class: 'tabgroup'}
									// Content to come
								),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
						
					
		// If we are working from an existing template version, retrieve its details, if not, just get the template variables.
		if ((iTemplateDetailsId == null) && (iTemplateId != null))
		{
			this._buildTemplateDetailsObject(iTemplateId);
		}
		else
		{
			this._getTemplateDetails();
		}
	},
	
	_buildTemplateDetailsObject: function(iTemplateId, oResponse)
	{
		if (typeof(oResponse)=='undefined')
		{
			var fnRequest     = jQuery.json.jsonFunction(this._buildTemplateDetailsObject.bind(this,iTemplateId ), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'getTemplateVariables');
			fnRequest(iTemplateId);
		}
		else
		{					
			if (oResponse.Success)
			{
				this._oTemplateDetails 									= {};
				this._oTemplateDetails.email_template_customer_group_id = iTemplateId;
				this._oTemplateDetails.email_text 						= '';
				this._oTemplateDetails.email_html 						= '';
				this._oTemplateDetails.created_timestamp 				= null;
				this._oTemplateDetails.created_employee_id 				= null;
				this._oTemplateDetails.effective_datetime 				= null;
				this._oTemplateDetails.email_subject 					= '';
				this._oTemplateDetails.email_from 						= '@' + (oResponse.sEmailDomain ? oResponse.sEmailDomain : '');
				this._oTemplateDetails.end_datetime 					= null;
				this._oTemplateDetails.description 						= '';			
				this._oVariables  										= oResponse.variables;
				this._buildGUI();
			}
			else				
			{
				Popup_Email_Text_Editor.serverErrorMessage.bind(this, oResponse, 'Email Template Retrieval Error')();
			}
		}	
	},
		
	_getTemplateDetails: function(oResponse)
	{		
		if (typeof(oResponse)=='undefined')
		{
			var fnRequest     = jQuery.json.jsonFunction(this._getTemplateDetails.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'getTemplateVersionDetails');
			fnRequest(this._iTemplateDetailsId);
		}
		else
		{
			
			if (oResponse.Success)
			{
				this._oTemplateDetails = oResponse.oTemplateDetails;
				this._oVariables  = oResponse.variables;
				this._buildGUI();
			}
			else
			{
				Popup_Email_Text_Editor.serverErrorMessage.bind(this, oResponse, 'Email Template Retrieval Error')();				
			}
		
		}
	},
	
	_buildGUI: function()
	{
		// Button events
		this._saveButton	= this._oContent.select('div.buttons > button.icon-button').first();
		this._saveButton.observe('click', this._saveButtonClick.bind(this));
		
		var oButton	= this._oContent.select('div.buttons > button.icon-button').last();
		oButton.observe('click', this._close.bind(this));			
		
		var oSubject	= 	Control_Field.factory(
								'text',
								{
									mAutoTrim					: false, 
									sLabel						: 'Email Subject', 
									fnValidate					: this._validText,
									mEditable					: true, 
									bDisableValidationStyling	: false,
									mMandatory					: true
								}
							);
		oSubject.setValue(this._oTemplateDetails.email_subject);
		oSubject.validate();
		oSubject.oControlOutput.oEdit.isFocused			= false;
		oSubject.oControlOutput.oEdit.onfocus			= function(){this.isFocused=true};
		oSubject.oControlOutput.oEdit.onblur 			= function(){this.isFocused=false};
		oSubject.oControlOutput.oEdit.variableFormat 	= 'text';
		
		var oDescription	= 	Control_Field.factory(
									'text',
									{
										mAutoTrim					: false, 
										sLabel						: 'Description', 
										fnValidate					: this._validText, 
										mEditable					: true, 
										bDisableValidationStyling	: false, 
										mMandatory					: true
									}
								);
		oDescription.setValue(this._sTemplateName + " - " + new Date().$format('d/m/Y'));
		oDescription.validate();
		
		var oSender	= 	Control_Field.factory(
							'text',
							{
								sLabel		: 'Sender',
								mMandatory	: true,
								mEditable	: true,
								fnValidate	: Reflex_Validation.email
							}
						);
		oSender.setValue(this._oTemplateDetails.email_from);
		oSender.validate();
		
		this._oSenderTextField		= oSender;
		this._oSubjectTextField		= oSubject;
		this._oDescriptionTextField	= oDescription;
		
		var oTable	= new Email_Template_Table({}, {}, {class: 'reflex input'});
		oTable.appendRow(
			$T.tr(
				$T.th({class: 'description'},
					$T.span({class: 'email-subject-label'},
						'Description'
					)
				),	
				$T.td(this._oDescriptionTextField.getElement())
			)
		);
		oTable.appendRow(
			$T.tr(
				$T.th(
					$T.span({class: 'email-subject-label'},
						'Email Subject'
					)
				),
				$T.td(this._oSubjectTextField.getElement())
			)
		);
		oTable.appendRow(
			$T.tr(
				$T.th(
					$T.span({class: 'email-subject-label'},
						'Sender'
					)
				),
				$T.td(this._oSenderTextField.getElement())
			)
		);
		
		var oSubjectDiv	= 	$T.div({class: 'email-subject-container'}, 
								oTable.getElement()
							);
		var oContainer	= this._oContent.select('.subject-description').first();
		oContainer.appendChild(oSubjectDiv);
		
		// Define the content for the tab group
		 var oTabContainer	= this._oContent.select('div.tabgroup').first();			 
		 this._oTabGroup	= new Control_Tab_Group(oTabContainer, true);
		 						
		// Generate the Text tab	
		var oTabContent	=	$T.table({class: 'reflex input'},
								oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
							);
		
		var iNumRows	= document.viewport.getHeight()>768?25:18;
		this.oTextArea	= Control_Field.factory('textarea', {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:iNumRows, cols:25});

		this.oTextArea.setValue(this._oTemplateDetails.email_text);
		//this.oTextArea.setElementValue(this._oTemplateDetails.email_text);
		this.oTextArea.disableValidationStyling();
		this.oTextArea.oControlOutput.oEdit.isFocused		= false;
		this.oTextArea.oControlOutput.oEdit.onfocus			= function(){this.isFocused=true};
		this.oTextArea.oControlOutput.oEdit.onblur			= function(){this.isFocused=false};
		this.oTextArea.oControlOutput.oEdit.variableFormat	= 'text';
		
		var oTableRow 	= this.oTextArea.generateInputTableRow().oElement;
		var th 			= oTableRow.select('th').first();
		th.appendChild(
			$T.div({class: 'buttons'},
				$T.button({class: 'icon-button', title: 'Text Only Test Email'},
					$T.img({class: 'padded-image', src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: ''}),
					$T.span('Send Text Test')
				).observe('click', this._sendTestMail.bind(this, true))
			)					
		);
		
		th.appendChild(this._defineVariableList(this.oTextArea));			
		oTBody.appendChild(oTableRow);
		
		this._oTextTab 			= new Control_Tab("Text", oTabContent)
		this._oTabGroup.addTab("Text", this._oTextTab);
		
		// Generate the HTML tab
		oTabContent	=	$T.table({class: 'reflex input'},
							oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
						);
			 
		this.oHTMLTextArea	= Control_Field.factory('textarea', {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:iNumRows, cols:25});
		this.oHTMLTextArea.setValue(this._oTemplateDetails.email_html);
		this.oHTMLTextArea.setElementValue(this._oTemplateDetails.email_html);
		this.oHTMLTextArea.oControlOutput.oEdit.isFocused=false;
		this.oHTMLTextArea.oControlOutput.oEdit.variableFormat='tag';
		this.oHTMLTextArea.oControlOutput.oEdit.onfocus=function(){this.isFocused=true};
		this.oHTMLTextArea.oControlOutput.oEdit.onblur=function(){this.isFocused=false};
		this.oHTMLTextArea.disableValidationStyling(); 
		oTableRow	= this.oHTMLTextArea.generateInputTableRow().oElement;		
		th 			= oTableRow.select('th').first();
		th.appendChild(
			$T.div({class: 'buttons'},
				this._generateTextButton = $T.button({class: 'icon-button', title: 'Generate Text Version Based on HTML'},
					$T.img({class: 'padded-image', src: Popup_Email_Text_Editor.ICON_IMAGE_SOURCE, alt: '', title: 'Generate Text'}),
					$T.span({class: 'padded'},'Generate Text')
				).observe('click', this._generateTextButtonClick.bind(this)),
				$T.button({class: 'icon-button', title: 'HTML Preview'},
					$T.img({class: 'padded-image', src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE, alt: '', title: 'HTML Preview'}),
					$T.span({class: 'padded'},'HTML Preview')
				).observe('click', this._htmlPreviewSelected.bind(this)),
				$T.button({class: 'icon-button', title: 'Send HTML Test'},
					$T.img({class: 'padded-image', src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: ''}),
					$T.span('Send HTML Test')
				).observe('click', this._sendTestMail.bind(this, false))
			)					
		);
		th.appendChild(this._defineVariableList(this.oHTMLTextArea));	
		oTBody.appendChild(oTableRow);
		this._oTabGroup.addTab("HTML", new Control_Tab("HTML", oTabContent));		
		
		this.setTitle('Email Text Editor - Template \'' + this._sTemplateName + '\' for ' + this._sCustomerGroupName);
		this.addCloseButton(this._close.bind(this));
		this.setContent(this._oContent);
		this._oLoadingPopup.hide();
		this._setEditable();		
		this.display();	
	},
	
	 _setEditable: function()
	 {
		var bRenderMode	= this._bReadOnly ? Control_Field.RENDER_MODE_VIEW : Control_Field.RENDER_MODE_EDIT;
		
		this._oSubjectTextField.setEditable(bRenderMode);		  
		this._oSubjectTextField.setRenderMode(bRenderMode);
		
		this._oSenderTextField.setEditable(bRenderMode);
		this._oSenderTextField.setRenderMode(bRenderMode);
		
		this._oDescriptionTextField.setEditable(bRenderMode);		
		this._oDescriptionTextField.setRenderMode(bRenderMode);

		this.oTextArea.setEditable(bRenderMode);
		this.oTextArea.setRenderMode(bRenderMode);

		this.oHTMLTextArea.setEditable(bRenderMode);
		this.oHTMLTextArea.setRenderMode(bRenderMode);
		
		if (bRenderMode == Control_Field.RENDER_MODE_VIEW)
		{
			this._oSubjectTextField.save(true);
			this._oSenderTextField.save(true);
			this._oDescriptionTextField.save(true);
		}
		
		
		this._generateTextButton.style.display	= bRenderMode ? '' : 'none';
		this._saveButton.style.display 			= bRenderMode ? '' : 'none';
	 },
	
	_sendTestMail: function(bTextOnly)
	{
		sHTML 	= bTextOnly ? null : this.oHTMLTextArea.getElementValue();		
		new Popup_Email_Test_Email(
			{
				text	: this.oTextArea.getElementValue(), 
				html	: sHTML, 
				subject	: this._oSubjectTextField.getElementValue(),
				from	: this._oSenderTextField.getElementValue()
			}, 
			this._iTemplateId
		);	
	},
	
	_close : function ()
	{
		this.hide();
	},
	
	_validText: function(sText)
	{
		return typeof sText == 'undefined' || sText == null || sText==''?false:true;	
	},
	
	
	_unhide: function()
	{
		this.display();	
	},
	
	_defineVariableList: function(oTextArea)
	{
		var oVariableList	= 	$T.div({class: 'variables'},
									$T.label({class: 'varLabel'}
									
										// Content to come
									),
									$T.div({class: 'vars'}
									
										// Content to come
									)
								);		
		
		oVariableList.select('label.varLabel').first().innerHTML = "Variables";
		var div = oVariableList.select('div.vars').first();			
		//aKeys = oVariables.keys();
		
		for(var key in this._oVariables)
		{
			
			oLabel = $T.span({class:'varobject'});
			oLabel.innerHTML = key;
			
			div.appendChild(oLabel);
			var ul = $T.ul({class:'list'});
			div.appendChild(ul);
			var fields =  Object.keys(this._oVariables[key]);
			for (var i=0;i<fields.length;i++)
			{
				var li = document.createElement('li');
				 li.innerHTML = fields[i];
				 oVariable = {
								tag:  "<variable object = \""+key+"\" field = \"" + fields[i] + "\"/>",
								text: "{" + key +"."+ fields[i]+"}"
							}
				 if (!this._bReadOnly)
				 {
					li.observe('mousedown', this._insertVariable.bindAsEventListener(this, oTextArea.oControlOutput.oEdit , this._oSubjectTextField.oControlOutput.oEdit, oVariable));
				 }
				 else
				 {
					li.style.cursor = 'default';
					li.style.hover = '';
				 }
				
				ul.appendChild(li);				
			}		
		}
		return oVariableList;
		
	},
	
	_insertVariable: function(oEvent, oTextarea, oSubjectTextField, oVariable)
	{
		
		
		var activeElement = oTextarea.isFocused?oTextarea:oSubjectTextField.isFocused?oSubjectTextField:null;
		if (activeElement!=null)
		{
			sVariable = activeElement.variableFormat=='tag'?oVariable.tag:oVariable.text;
			
			var pos = activeElement.selectionStart;
			var iScrollTop 	= activeElement.scrollTop;
			var front = (activeElement.value).substring(0,pos);  
			var back = (activeElement.value).substring(pos,activeElement.value.length); 
			activeElement.value=front+sVariable+back;
			
			activeElement.selectionStart	= pos + sVariable.length;
			activeElement.selectionEnd 		= pos + sVariable.length;
			activeElement.scrollTop 		= iScrollTop;
			activeElement.focus();
			activeElement.isFocused = true;
			oEvent.stop();
		}
		else
		{
			Reflex_Popup.alert('Please indicate where the variable must be inserted by placing the cursor at that point', {sTitle:'Insert Variable'});
		
		}
		return false;
		
	},
	
	display	: function($super)
	{
		$super();
		this.container.style.top = '150px';
	},
	
	
	_htmlPaneChange: function()
	{
		this.oHTMLPreviewDiv.innerHTML = this.oHTMLTextArea.getElementValue();		
	},
	
	_htmlPreviewSelected: function(html)
	{
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successPreviewCallback.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(this.oHTMLTextArea.getElementValue(), this._iTemplateId);	
	},

	successPreviewCallback: function (oResponse)
	{
		
	   this._oLoadingPopup.hide();	
		if (oResponse.Success)
		{
			var html = oResponse.html;
			new Popup_email_HTML_Preview(html, this._unhide.bind(this));
		}
		else
		{
			Popup_Email_Text_Editor.processError.bind(this,oResponse)();
			
		}	
	},
	
	_generateTextButtonClick: function()
	{
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successToTextCallback.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'toText');
		fnRequest(this.oHTMLTextArea.getElementValue());	
	},	
	
	successToTextCallback: function (oResponse)
	{
	    if (oResponse.Success)
		{
			this._oLoadingPopup.hide();		
			var text = oResponse.text;
			this.oTextArea.setElementValue(text);
			this._oTabGroup.switchToTab(this._oTextTab);
		}
		else
		{
			Popup_Email_Text_Editor.serverErrorMessage.bind(this, oResponse, 'Email Template Text Generation Error')();				
		}
	},	
	
	_saveButtonClick: function()
	{			
		this._oLoadingPopup.display();
		
		this._oTemplateDetails.email_text 		= this.oTextArea.getElementValue();
		this._oTemplateDetails.email_html 		= this.oHTMLTextArea.getElementValue();
		this._oTemplateDetails.email_subject 	= this._oSubjectTextField.getElementValue();
		this._oTemplateDetails.email_from		= this._oSenderTextField.getElementValue();
		this._oTemplateDetails.description 		= this._oDescriptionTextField.getElementValue();
		this._oTemplateDetails.id 				= this._iTemplateDetailsId;
		
		var fnRequest	= jQuery.json.jsonFunction(this._saveSuccess.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		fnRequest(this._oTemplateDetails, false);		
	},
	
	_save: function (oResponse)
	{		
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this._saveSuccess.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		fnRequest(oResponse, true);		
	},
	
	_saveSuccess: function (oResponse)
	{		
		if (!oResponse.Success)
		{
					
			if (typeof oResponse.LineNo != 'undefined')
			{
				Popup_Email_Text_Editor.userErrorMessage.bind(this,oResponse.Summary, oResponse.LineNo, this,oResponse.message,'Email Template Save Error')();
			}
			else
			{
				Popup_Email_Text_Editor.serverErrorMessage.bind(this, oResponse, 'Email Template Save Error')();
			}
			this._oLoadingPopup.hide();	
		}
		else
		{		
			if (oResponse.Confirm)
			{
				this._fnCallback();
				this._oLoadingPopup.hide();	
				this.hide();			
				Reflex_Popup.alert("The template was saved successfully", {sTitle: 'Email Template Save Success'});
			}
			else
			{				
				this._oLoadingPopup.hide();
				new Popup_Email_Save_Confirm(oResponse, this._save.bind(this), this._iTemplateId);							
			}		
		}	
	},
	
	
	
});

Popup_Email_Text_Editor.processError = function(oResponse)
{

	this._oLoadingPopup.hide();
		
	if (typeof oResponse.Summary != 'undefined')
	{
		Popup_Email_Text_Editor.userErrorMessage.bind(this,oResponse.Summary, oResponse.message,'Email Template Save Error', oResponse.LineNo)();
	}
	else
	{
		Popup_Email_Text_Editor.serverErrorMessage.bind(this, oResponse, 'Email Template HTML Preview Error')();
	}
}

Popup_Email_Text_Editor.serverErrorMessage = function (oResponse, sMessage) {
	this._oLoadingPopup.hide();	
	jQuery.json.errorPopup(oResponse, sMessage);
};

Popup_Email_Text_Editor.userErrorMessage = function (sSummary, sMessage,sTitle, iLineNumber)
{
	var containerDiv = document.createElement('div');
	containerDiv.innerHTML = sSummary; 
	typeof iLineNumber!= 'undefined'?containerDiv.innerHTML =containerDiv.innerHTML + ' Line Number: ' + iLineNumber:null;
	containerDiv.className = "email-template-error";
	this._oLoadingPopup.hide();
	Reflex_Popup.alert(containerDiv, {sTitle: sTitle});
};

Popup_Email_Text_Editor.errorCallback = function(oResponse) {
	Popup_Email_Text_Editor.serverErrorMessage.bind(this, oResponse)();
};

Popup_Email_Text_Editor._toggleErrorDetails = function (oDiv)
{
	oDiv.style.display == 'none'?oDiv.style.display = '':oDiv.style.display = 'none';
};

Popup_Email_Text_Editor.ICON_IMAGE_SOURCE 		= '../admin/img/template/rebill.png';
Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE 		= '../admin/img/template/tick.png';
Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE 	='../admin/img/template/magnifier.png';
Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE 		='../admin/img/template/email.png';
Popup_Email_Text_Editor.ADD_IMAGE_SOURCE  	= '../admin/img/template/new.png';
Popup_Email_Text_Editor.EMAIL_HTML_IMAGE_SOURCE  	= '../admin/img/template/email_html3.png';
Popup_Email_Text_Editor.EMAIL_HTML_IMAGE_SOURCE_RED  	= '../admin/img/template/email_html_red2.png';
Popup_Email_Text_Editor.EMAIL_TEXT_IMAGE_SOURCE  	= '../admin/img/template/email_text.png';
Popup_Email_Text_Editor.BACKGROUND_BLUR_IMAGE_SOURCE  	= '../admin/img/template/background-blur.png';