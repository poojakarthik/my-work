
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{	
	
	initialize	: function($super, iTemplateDetailsId, sTemplateName, customerGroupName, fnCallback, iTemplateId)
	{			
		
		$super(70);			
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
			this._oTemplateDetails = oResponse.oTemplateDetails;
			this._oVariables  = oResponse.variables;
			this._buildGUI();	
		
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
		
		this._oSubjectTextField 				= document.createElement('input');
		this._oSubjectTextField.type 			= 'text';
		this._oSubjectTextField.className 		= 'email-subject-input';
		this._oSubjectTextField.size 			= 50;				
		this._oSubjectTextField.value 			= this._oTemplateDetails.email_subject;
			

		var oDescriptionLabel  					= document.createElement('span');
		oDescriptionLabel.innerHTML 			= 'Description';
		oDescriptionLabel.className 			= 'email-subject-label';
		
		this._oDescriptionTextField 			= document.createElement('input');
		this._oDescriptionTextField.type 		= 'text';
		this._oDescriptionTextField.size 		= 30;
		this._oDescriptionTextField.className 	= 'email-subject-input';
		this._oDescriptionTextField.value 		= this._sTemplateName + " - " + new Date().$format('d/m/Y');
		
		var oTable 								= new Email_Template_Table({}, {}, {class: 'reflex input'});
		oTable.appendRow($T.tr(
								$T.th({class: 'description'},oDescriptionLabel),
								$T.td(this._oDescriptionTextField)
								)
						);
		oTable.appendRow($T.tr(
								$T.th(oSpan),
								$T.td(this._oSubjectTextField)
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
		var oControl			= Control_Field.factory('textarea', {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:25, cols:25});
		this.oTextArea 			= oControl.oControlOutput.oEdit;
		this.oTextArea.value 	= this._oTemplateDetails.email_text;			
		var oTableRow 			= oControl.generateInputTableRow().oElement;
		var th 					= oTableRow.select('th').first();
		th.appendChild(this.defineVariableList());			
		oTBody.appendChild(oTableRow);
		
		this._oTextTab 			= new Control_Tab("Text", oTabContent)
		this._oTabGroup.addTab("Text", this._oTextTab);
		
		//generate the HTML tab
		oTabContent				=	$T.table({class: 'reflex input'},
											 oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
											 );
			 
		oControl				= Control_Field.factory('textarea', {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:25, cols:25});
		this.oHTMLTextArea 		= oControl.oControlOutput.oEdit;
		this.oHTMLTextArea.value= this._oTemplateDetails.email_html;
		oTableRow 				= oControl.generateInputTableRow().oElement;		
		th 						= oTableRow.select('th').first();
		th.appendChild($T.div({class: 'buttons'},
						$T.button({class: 'icon-button'},
									$T.img({src: Popup_Email_Text_Editor.ICON_IMAGE_SOURCE, alt: '', title: 'Generate Text'}),
									$T.span('Generate Text')
									).observe('click', this._generateTextButtonClick.bind(this)),
						$T.button({class: 'icon-button'},
							$T.img({src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE, alt: '', title: 'HTML Preview'}),
							$T.span('HTML Preview')
						).observe('click', this._htmlPreviewSelected.bind(this)),
						$T.button({class: 'icon-button'},
							$T.img({src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: '', title: 'Send Test Mail'}),
							$T.span('Send Test Mail')
						).observe('click', this._sendTestMail.bind(this))
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
	
	_sendTestMail: function()
	{
		new Popup_Email_Test_Email({text: this.oTextArea.value, html:this.oHTMLTextArea.value, subject:this._oSubjectTextField.value });	
	},
	
	_close : function ()
	{
		this.hide();
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
		var html = oResponse.html;
		new Popup_email_HTML_Preview(html, this._unhide.bind(this));
		//this.hide();		
	},
	
	_generateTextButtonClick: function()
	{
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successToTextCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'toText');
		fnRequest(this.oHTMLTextArea.value);	
	},	
	
	successToTextCallback: function (oResponse)
	{
	    this._oLoadingPopup.hide();		
		var text = oResponse.text;
		this.oTextArea.value = text;
		this._oTabGroup.switchToTab(this._oTextTab);
	},	
	
	_saveButtonClick: function()
	{			
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this._saveSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		this._oTemplateDetails.email_text = this.oTextArea.value=='undefined'?'':this.oTextArea.value;
		this._oTemplateDetails.email_html = this.oHTMLTextArea.value == 'undefined'?'':this.oHTMLTextArea.value;
		this._oTemplateDetails.email_subject = this._oSubjectTextField.value== 'undefined'?'':this._oSubjectTextField.value;
		this._oTemplateDetails.description = this._oDescriptionTextField.value== 'undefined'?'':this._oDescriptionTextField.value;
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
			Reflex_Popup.alert("There was an error, your template could not be saved: " + oResponse.message, {sTitle: 'Email Template Save Error'});		
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
		alert('error');
	},
	
});

Popup_Email_Text_Editor.ICON_IMAGE_SOURCE 	= '../admin/img/template/rebill.png';
Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE 	='../admin/img/template/magnifier.png';
Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE 	='../admin/img/template/email.png';
