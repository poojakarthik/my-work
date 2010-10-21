
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{	
	
	initialize	: function($super, iTemplateDetailsId, sTemplateName, customerGroupName, fnCallback, iTemplateId)
	{			
		
		$super(80);			
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		
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
						
					
		//if we are working from an existing template version, retrieve its details, if not, just get the template variables.
		//this.__buildTemplateDetailsObject or this._getTemplateDetails will take control from here on
		iTemplateDetailsId == null && iTemplateId!=null?this._buildTemplateDetailsObject(iTemplateId):this._getTemplateDetails();		

	},
	
	_buildTemplateDetailsObject: function(iTemplateId, oResponse)
	{
		if (typeof(oResponse)=='undefined')
		{
			var fnRequest     = jQuery.json.jsonFunction(this._buildTemplateDetailsObject.bind(this,iTemplateId ), this.errorCallback.bind(this), 'Email_Text_Editor', 'getTemplateVariables');
			fnRequest(iTemplateId);
		}
		else
		{					
			if (oResponse.Success)
			{
			this._oTemplateDetails = {};
			this._oTemplateDetails.email_template_id = iTemplateId;
			this._oTemplateDetails.email_text = ' ';
			this._oTemplateDetails.email_html = ' ';
			this._oTemplateDetails.created_timestamp = null;
			this._oTemplateDetails.created_employee_id = null;
			this._oTemplateDetails.effective_datetime = null;
			this._oTemplateDetails.email_subject = ' ';
			this._oTemplateDetails.end_datetime = null;
			this._oTemplateDetails.description = ' ';			
			this._oVariables  = oResponse.variables;
			this._buildGUI();
			}
			else
			{
				Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template Retrieval Error')();			
			
			}
		}	
	},
	
	
	_getTemplateDetails: function(oResponse)
	{		
		if (typeof(oResponse)=='undefined')
		{
			var fnRequest     = jQuery.json.jsonFunction(this._getTemplateDetails.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'getTemplateVersionDetails');
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
				Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template Retrieval Error')();				
			}
		
		}
	},
	
	_buildGUI: function()
	{
	
		// Button events
		var oButton							= this._oContent.select('div.buttons > button.icon-button').first();
		oButton.observe('click', this._saveButtonClick.bind(this));
		
		oButton							= this._oContent.select('div.buttons > button.icon-button').last();
		oButton.observe('click', this._close.bind(this));			
		
		//add the subject and description text fields	
		var oSpan 								= document.createElement('span');
		oSpan.innerHTML 						= 'Email Subject';
		oSpan.className 						= 'email-subject-label';
		

		
		
		this._oSubjectTextField = Control_Field.factory('text', {mAutoTrim: false, sLabel: 'Email Subject', fnValidate: this._validText, mEditable	: true, bDisableValidationStyling	: false, mMandatory	: true});		
		this._oSubjectTextField.addOnChangeCallback(this._oSubjectTextField.validate.bind(this._oSubjectTextField));
		this._oSubjectTextField.setRenderMode(true);
		this._oSubjectTextField.setElementValue(this._oTemplateDetails.email_subject);
		this._oSubjectTextField.validate();
		
		
			

		var oDescriptionLabel  					= document.createElement('span');
		oDescriptionLabel.innerHTML 			= 'Description';
		oDescriptionLabel.className 			= 'email-subject-label';
		

		
		
		this._oDescriptionTextField = Control_Field.factory('text', {mAutoTrim: false, sLabel: 'Description', fnValidate: this._validText, mEditable	: true, bDisableValidationStyling	: false, mMandatory	: true});		
		this._oDescriptionTextField.addOnChangeCallback(this._oDescriptionTextField.validate.bind(this._oDescriptionTextField));
		this._oDescriptionTextField.setRenderMode(true);
		this._oDescriptionTextField.setElementValue(this._sTemplateName + " - " + new Date().$format('d/m/Y'));
		this._oDescriptionTextField.validate();
		
		
		
		
		
		var oTable 								= new Email_Template_Table({}, {}, {class: 'reflex input'});
		oTable.appendRow($T.tr(
								$T.th({class: 'description'},oDescriptionLabel),
								$T.td(this._oDescriptionTextField.getElement())
								)
						);
		oTable.appendRow($T.tr(
								$T.th(oSpan),
								$T.td(this._oSubjectTextField.getElement())
								)
						);
		
		var oSubjectDiv							= 	$T.div(	{class: 'email-subject-container'},	oTable.getElement());
					
		 var oContainer							= this._oContent.select('.subject-description').first();
		oContainer.appendChild(oSubjectDiv);
		

		//define the content for the tab group
		 var oTabContainer		= this._oContent.select('div.tabgroup').first();			 
		 this._oTabGroup		= new Control_Tab_Group(oTabContainer, true);
		 
						
		//generate the Text tab	
		var oTabContent			=	$T.table({class: 'reflex input'},
												oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
											 );
		
		var iNumRows				= document.viewport.getHeight()>768?25:18;
		var oControl			= Control_Field.factory('textarea', {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:iNumRows, cols:25});
		this.oTextArea 			= oControl.oControlOutput.oEdit;
		this.oTextArea.value 	= this._oTemplateDetails.email_text;			
		var oTableRow 			= oControl.generateInputTableRow().oElement;
		var th 					= oTableRow.select('th').first();
		th.appendChild($T.div({class: 'buttons'},

				$T.button({class: 'icon-button', title: 'Text Only Test Email'},
					$T.img({src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE , alt: ''}),
					$T.span('Send Text Test')
				).observe('click', this._sendTestMail.bind(this, true))
				)					
			);
		
		
		th.appendChild(this.defineVariableList());			
		oTBody.appendChild(oTableRow);
		
		this._oTextTab 			= new Control_Tab("Text", oTabContent)
		this._oTabGroup.addTab("Text", this._oTextTab);
		
		//generate the HTML tab
		oTabContent				=	$T.table({class: 'reflex input'},
											 oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
											 );
			 
		oControl				= Control_Field.factory('textarea', {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:iNumRows, cols:25});
		this.oHTMLTextArea 		= oControl.oControlOutput.oEdit;
		this.oHTMLTextArea.value= this._oTemplateDetails.email_html;
		oTableRow 				= oControl.generateInputTableRow().oElement;		
		th 						= oTableRow.select('th').first();
		th.appendChild($T.div({class: 'buttons'},
						$T.button({class: 'icon-button', title: 'Generate Text Version Based on HTML'},
									$T.img({src: Popup_Email_Text_Editor.ICON_IMAGE_SOURCE, alt: '', title: 'Generate Text'}),
									$T.span({class: 'padded'},'Generate Text')
									).observe('click', this._generateTextButtonClick.bind(this)),
						$T.button({class: 'icon-button', title: 'HTML Preview'},
							$T.img({src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE, alt: '', title: 'HTML Preview'}),
							$T.span({class: 'padded'},'HTML Preview')
						).observe('click', this._htmlPreviewSelected.bind(this)),
						$T.button({class: 'icon-button', title: 'Send HTML Test'},
							$T.img({src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: ''}),
							//Popup_Email_Text_Editor.EMAIL_HTML_IMAGE_SOURCE_RED
							//Popup_Email_Text_Editor.EMAIL_HTML_IMAGE_SOURCE
							$T.span('Send HTML Test')
						).observe('click', this._sendTestMail.bind(this, false))
						)					
					);
		th.appendChild(this.defineVariableList());	
		oTBody.appendChild(oTableRow);
		this._oTabGroup.addTab("HTML", new Control_Tab("HTML", oTabContent));		
		
		this.setTitle('Email Text Editor - Template \'' + this._sTemplateName + '\' for ' + this._sCustomerGroupName);
		this.addCloseButton(this._close.bind(this));
		this.setContent(this._oContent);
		this._oLoadingPopup.hide();
		this.display();	
	},
	
	_sendTestMail: function(bTextOnly)
	{
		sHTML = bTextOnly?null:this.oHTMLTextArea.value;		
		oData = {text: this.oTextArea.value, html:sHTML, subject:this._oSubjectTextField.getElementValue() }
		new Popup_Email_Test_Email(oData);	
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
	
	defineVariableList: function()
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
			$T.span
			oLabel = $T.span({class:'varobject'});
			oLabel.innerHTML = key;
			div.appendChild(oLabel);
			var ul = $T.ul({class:'list'});
			div.appendChild(ul);
			for (var i=0;i<this._oVariables[key].length;i++)
			{
				var li = document.createElement('li');
				 li.innerHTML = this._oVariables[key][i];
				ul.appendChild(li);				
			}		
		}
		return oVariableList;
		
	},
	
	display	: function($super)
	{
		$super();
		this.container.style.top = '150px';
	},
	
	
	_htmlPaneChange: function()
	{
		this.oHTMLPreviewDiv.innerHTML = this.oHTMLTextArea.value;		
	},
	
	_htmlPreviewSelected: function(html)
	{
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successPreviewCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(this.oHTMLTextArea.value);	
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
		var fnRequest     = jQuery.json.jsonFunction(this.successToTextCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'toText');
		fnRequest(this.oHTMLTextArea.value);	
	},	
	
	successToTextCallback: function (oResponse)
	{
	    if (oResponse.Success)
		{
			this._oLoadingPopup.hide();		
			var text = oResponse.text;
			this.oTextArea.value = text;
			this._oTabGroup.switchToTab(this._oTextTab);
		}
		else
		{
			Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template Text Generation Error')();				
		}
	},	
	
	_saveButtonClick: function()
	{			
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this._saveSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		this._oTemplateDetails.email_text = this.oTextArea.value=='undefined'?'':this.oTextArea.value;
		this._oTemplateDetails.email_html = this.oHTMLTextArea.value == 'undefined'?'':this.oHTMLTextArea.value;
		this._oTemplateDetails.email_subject = this._oSubjectTextField.getElementValue()== 'undefined'?'':this._oSubjectTextField.getElementValue();
		this._oTemplateDetails.description = this._oDescriptionTextField.getElementValue()== 'undefined'?'':this._oDescriptionTextField.getElementValue();
		this._oTemplateDetails.id = this._iTemplateDetailsId;
		fnRequest(this._oTemplateDetails, false);		
	},
	
	_save: function (oResponse)
	{		
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this._saveSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'save');
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
				Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template Save Error')();
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
	
	errorCallback: function()
	{		  
		Popup_Email_Text_Editor.serverErrorMessage.bind(this,"Ajax error. No details available", 'Email Template System Error')();
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
		Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template HTML Preview Error')();
	}
}



Popup_Email_Text_Editor.serverErrorMessage = function (sMessage,sTitle){

	var detailsDiv = document.createElement('div');
	detailsDiv.innerHTML = sMessage;
	detailsDiv.style.display = 'none';
	detailsDiv.className = 'error-details';

	var div = document.createElement('div');

	div.observe('click', Popup_Email_Text_Editor._toggleErrorDetails.bind(this,detailsDiv));
	div.innerHTML = "Error Details";
	div.className = 'details-link';
	var containerDiv = document.createElement('div');
	containerDiv.innerHTML = "There was a server processing error. Please contact YBS for assistance.";
	containerDiv.appendChild(div);
	containerDiv.appendChild(detailsDiv);
	containerDiv.className = "email-template-error";
	
	
	Reflex_Popup.alert(containerDiv, {sTitle: sTitle});
};

Popup_Email_Text_Editor.userErrorMessage = function (sSummary, sMessage,sTitle, iLineNumber){

	
	//var detailsDiv = document.createElement('div');
	//detailsDiv.innerHTML = sMessage;
	//detailsDiv.style.display = 'none';
	//detailsDiv.className = 'error-details';

	//var div = document.createElement('div');

	//div.observe('click', Popup_Email_Text_Editor._toggleErrorDetails.bind(this,detailsDiv));
	//div.innerHTML = "Error Details";
	//div.className = 'details-link';
	var containerDiv = document.createElement('div');
	containerDiv.innerHTML = sSummary; 
	typeof iLineNumber!= 'undefined'?containerDiv.innerHTML =containerDiv.innerHTML + ' Line Number: ' + iLineNumber:null;
	//containerDiv.appendChild(div);
	//containerDiv.appendChild(detailsDiv);
	containerDiv.className = "email-template-error";
	
	this._oLoadingPopup.hide();
	Reflex_Popup.alert(containerDiv, {sTitle: sTitle});
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
