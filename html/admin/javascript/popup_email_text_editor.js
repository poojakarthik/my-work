
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{	
	
	initialize	: function($super, iTemplateDetailsId, sTemplateName, customerGroupName, iMode)
	{
			
			
			//this._fnCallback = fnCallback;
			this._iTemplateDetailsId = iTemplateDetailsId;
			this._iMode = iMode;
			this._sTemplateName = sTemplateName;
			this._sCustomerGroupName = customerGroupName;
			// Image paths
			Popup_Email_Text_Editor.ICON_IMAGE_SOURCE 	= '../admin/img/template/rebill.png';
			Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
			Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
			Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE 	='../admin/img/template/magnifier.png';
		$super(70);
		
				this._oLoadingPopup	= new Reflex_Popup.Loading();
				this._oLoadingPopup.display();
		// Build content
			this._oContent	= 	$T.div({class: 'popup-email-text-edit'},
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
								
			this._getTemplateDetails();					
			

	},
	
	_getTemplateDetails: function()
	{
		
		
		var fnRequest     = jQuery.json.jsonFunction(this._getVariablesSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'getVariables');
		fnRequest(this._iTemplateDetailsId);
	},
	
	_getVariablesSuccess: function(oResponse)
	{
		
		this._oTemplateDetails = oResponse.oTemplateDetails;
		this._oVariables  = oResponse.variables;
		this._buildGUI();
		
	
	},
	
	_buildGUI: function(oVariables)
	{
	
			// Button events
			var oAddButton		= this._oContent.select('div.buttons > button.icon-button').first();
			oAddButton.observe('click', this._saveButtonClick.bind(this));
			
						var oAddButton		= this._oContent.select('div.buttons > button.icon-button').last();
			oAddButton.observe('click', this._close.bind(this));

			//var oCancelButton	= this._oContent.select('div.buttons > button.icon-button').last();
			//oCancelButton.observe('click', this._cancelEdit.bind(this));

			//the list of possible variables, for both the text and the html panes	
			var oHTMLVariableList	= 	$T.div({class: 'variables'},
									$T.label({class: 'varLabel'}
									
										// Content to come
									),
									$T.div({class: 'vars'}
									
										// Content to come
									)
								);
			var oTextVariableList	= 	$T.div({class: 'variables'},
									$T.label({class: 'varLabel'}
									
										// Content to come
									),
									$T.div({class: 'vars'}
									
										// Content to come
									)
								);
			
			
			this.defineVariableList(oHTMLVariableList, oVariables);
			this.defineVariableList(oTextVariableList, oVariables);
			
			
			
			

			//define the content for the tab group
			 var oTabContainer		= this._oContent.select('div.tabgroup').first();			 
			 this._oTabGroup		= new Control_Tab_Group(oTabContainer, true);
			 
			 
			
			
			//text area definition
			oDefinition	= {sLabel:"", sLabelSeparator:null, mVisible:true, mEditable:true, rows:25, cols:25};
			
			//generate the Text tab	
			oTabContent	=	$T.table({class: 'reflex input'},
							 oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
							 );
			oControl	= Control_Field.factory('textarea', oDefinition);
			this.oTextArea = oControl.oControlOutput.oEdit;
			this.oTextArea.value = this._oTemplateDetails.email_text;
			
			
			var oTableRow = oControl.generateInputTableRow().oElement;
			var th = oTableRow.select('th').first();
			th.appendChild(oTextVariableList);
			
			
			oTBody.appendChild(oTableRow);
			this._oTextTab = new Control_Tab("Text", oTabContent)
			this._oTabGroup.addTab("Text", this._oTextTab);
			
			//generate the HTML tab
			oTabContent	=	$T.table({class: 'reflex input'},
							 oTBody = $T.tbody({class: 'popup-email-text-edit-fields'})
							 );
				 
			oControl	= Control_Field.factory('textarea', oDefinition);
			
			
			this.oHTMLTextArea = oControl.oControlOutput.oEdit;
			this.oHTMLTextArea.value  = this._oTemplateDetails.email_html;
			 oTableRow = oControl.generateInputTableRow().oElement;
			
			
			
			
			
			//the side bar
			th = oTableRow.select('th').first();
		
			 th.appendChild($T.div({class: 'buttons'},
							$T.button({class: 'icon-button'},
								$T.img({src: Popup_Email_Text_Editor.ICON_IMAGE_SOURCE, alt: '', title: 'Generate Text'}),
								$T.span('Generate Text')
							),
							$T.button({class: 'icon-button'},
								$T.img({src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE, alt: '', title: 'HTML Preview'}),
								$T.span('HTML Preview')
							).observe('click', this._htmlPreviewSelected.bind(this))
							)					
						);
			th.appendChild(oHTMLVariableList);
			

									
			var oGenerateTextButton	= th.select('div.buttons > button.icon-button').first();
			oGenerateTextButton.observe('click', this._generateTextButtonClick.bind(this));
			
			//var oPreviewButton = th.select('div.buttons > button.icon-button').second();
			//oPreviewButton.observe(clic
		
			oTBody.appendChild(oTableRow);
			this._oTabGroup.addTab("HTML", new Control_Tab("HTML", oTabContent));
			
			//add the subject text field next to the tabs
			 var subjectDiv = oTabContainer.select('div.tab-row').first();
			
			var subjectElementDiv = document.createElement('div');
			subjectElementDiv.className = 'email-subject-container';
			
			var oSpan = document.createElement('span');
			oSpan.innerHTML = 'Email Subject';
			oSpan.className = 'email-subject-label';
			
			this._oSubjectTextField = document.createElement('input');
			this._oSubjectTextField.type = 'text';
				this._oSubjectTextField.className = 'email-subject-input';
				this._oSubjectTextField.size = 50;
				
			this._oSubjectTextField.value = this._oTemplateDetails.email_subject;
				
				subjectElementDiv.appendChild(oSpan );
			subjectElementDiv.appendChild(this._oSubjectTextField );
			
			var oDescriptionLabel  = document.createElement('span');
			oDescriptionLabel.innerHTML = 'Description';
			oDescriptionLabel.className = 'email-subject-label';
			
			this._oDescriptionTextField = document.createElement('input');
			this._oDescriptionTextField.type = 'text';
			this._oDescriptionTextField.size = 30;
			this._oDescriptionTextField.className = 'email-subject-input';
			this._oDescriptionTextField.value = this._sTemplateName + " - " + new Date().$format('d/m/Y');
			subjectElementDiv.appendChild(oDescriptionLabel );
			subjectElementDiv.appendChild(this._oDescriptionTextField );
			
			
			subjectDiv.appendChild(subjectElementDiv);	
		
		// Attach content and get data
		var sPopupMode = this._iMode == Popup_Email_Text_Editor.CREATE?'Create New Version':(this._iMode == Popup_Email_Text_Editor.READ?'Read Only':'Edit');
		
		this.setTitle('Email Text Editor (' + sPopupMode +' Mode) - Template \'' + this._sTemplateName +'\'(version ' +this._iTemplateDetailsId + ')  for ' + this._sCustomerGroupName);
		this.addCloseButton(this._close.bind(this));
		this.setContent(this._oContent);
		this._oLoadingPopup.hide();
		this.display();
	
	
	
	},
	
	_close : function ()
	{
		this.hide();
		
	
	},
	
	_previewButtonClicked: function()
	{
		new Popup_email_HTML_Preview(this.oHTMLTextArea.value, this._unhide.bind(this));
		this.hide();
	},
	
	_unhide: function()
	{
		this.display();
	
	},
	
	defineVariableList: function(oVariableList)
	{
		oVariableList.select('label.varLabel').first().innerHTML = "Variables";
		var div = oVariableList.select('div.vars').first();			
		//aKeys = oVariables.keys();
		
		for(var key in this._oVariables)
		{
			$T.span
			oLabel = $T.span({class:'varobject'});
			oLabel.innerHTML = key;
			div.appendChild(oLabel);
			ul = $T.ul({class:'list'});
			div.appendChild(ul);
			for (var i=0;i<this._oVariables[key].length;i++)
			{
				li = document.createElement('li');
				li.innerHTML = this._oVariables[key][i];
				ul.appendChild(li);				
			}		
		}			
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
		//this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successPreviewCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(this.oHTMLTextArea.value);
		//fnRequest(this._preprocessHTML());
	
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
		//this.oHTMLPreviewDiv.innerHTML = html;
		new Popup_email_HTML_Preview(html, this._unhide.bind(this));
		this.hide();		
	},
	
	successToTextCallback: function (oResponse)
	{
	    this._oLoadingPopup.hide();	
		
		var text = oResponse.text;
		this.oTextArea.value = text;
		//this._oTextTab.click();
		this._oTabGroup.switchToTab(this._oTextTab);
	},
	
	
	
	_preprocessHTML: function ()
	{		
		return this.oHTMLTextArea.value;	
	},
	
	_saveButtonClick: function(bUserConfirmed)
	{		
		
		//this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this._saveSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		this._oTemplateDetails.email_text = this.oTextArea.value;
		this._oTemplateDetails.email_html = this.oHTMLTextArea.value;
		this._oTemplateDetails.email_subject = this._oSubjectTextField.value;
		this._oTemplateDetails.description = this._oDescriptionTextField.value;
		this._oTemplateDetails.id = this._iTemplateDetailsId;
		fnRequest(this._oTemplateDetails, false, this._iMode);		
	},
	
	_save: function (oResponse)
	{
		
		debugger;
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this._saveSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		this._oTemplateDetails.email_text = this.oTextArea.value;
		this._oTemplateDetails.email_html = this.oHTMLTextArea.value;
		this._oTemplateDetails.effective_datetime = oResponse.effectiveDate;		
		this._oTemplateDetails.email_subject = this._oSubjectTextField.value;
		this._oTemplateDetails.description = this._oDescriptionTextField.value;
		this._iMode == Popup_Email_Text_Editor.EDIT?this._oTemplateDetails.id = this._iTemplateDetailsId:null;
		fnRequest(oResponse, true, this._iMode);
		//fnRequest(this._oTemplateDetails, true, this._iMode);	
	},
	
	_saveSuccess: function (oResponse)
	{
		
	
		if (oResponse.Confirm)
		{
			this._oTemplateDetails = oResponse.oTemplateDetails;
			this.oHTMLTextArea.value  = this._oTemplateDetails.email_html;
			this.oTextArea.value = this._oTemplateDetails.email_text;
			this._oLoadingPopup.hide();				
			alert('your template was saved successfully');
		}
		else
		{
			new Popup_Email_Save_Confirm(oResponse, this._save.bind(this));
			this._oLoadingPopup.hide();			
		}
	
	},
	
	_generateTextButtonClick: function()
	{
		//this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successToTextCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'toText');
		fnRequest(this.oHTMLTextArea.value);	
	
	
	}

	
	
	
	
	
});



Object.extend(Popup_Email_Text_Editor,
{
	 READ : 1,
	 EDIT : 2,
	 CREATE : 3,
	 
	 READ_LABEL: 'Read Only Mode',
	 EDIT_LABEL: 'Edit Mode',
	 CREATE_LABEL: 'Create Mode',
	 
 

});
	



