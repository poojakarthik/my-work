
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{
	

	
	
	
	initialize	: function($super, sHTML)
	{
			
			this._oTemplateDetails = oTemplateDetails;
			// Image paths
			Popup_Email_Text_Editor.ICON_IMAGE_SOURCE 	= '../admin/img/template/rebill.png';
			Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
			Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
		$super(70);
		
				this._oLoadingPopup	= new Reflex_Popup.Loading();
		// Build content
			this._oContent	= 	$T.div({class: 'popup-account-edit-rebill'},
									$T.div({class: 'preview-pane'}
										
									).innerHTML = sHTML,
									
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Close')
										)
									
								);
								
							
			// Attach content and get data
		this.setTitle('Email HTML Preview');
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();

	},
	
	
	
	_buildGUI: function(oVariables)
	{
	
			// Button events
			var oAddButton		= this._oContent.select('div.buttons > button.icon-button').first();
			oAddButton.observe('click', this._saveButtonClick.bind(this));

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
							 oTBody = $T.tbody({class: 'popup-account-edit-rebill-fields'})
							 );
			oControl	= Control_Field.factory('textarea', oDefinition);
			this.oTextArea = oControl.oControlOutput.oEdit;
			this.oTextArea.value = this._oTemplateDetails.email_text;
			
			
			var oTableRow = oControl.generateInputTableRow().oElement;
			var th = oTableRow.select('th').first();
			th.appendChild(oTextVariableList);
			
			
			oTBody.appendChild(oTableRow);
			this._oTabGroup.addTab("Text", new Control_Tab("Text", oTabContent));
			
			//generate the HTML tab
			oTabContent	=	$T.table({class: 'reflex input'},
							 oTBody = $T.tbody({class: 'popup-account-edit-rebill-fields'})
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
							)
						)
						
						);
			th.appendChild(oHTMLVariableList);
			

									
			var oGenerateTextButton	= th.select('div.buttons > button.icon-button').first();
			oGenerateTextButton.observe('click', this._generateTextButtonClick.bind(this));
		
			oTBody.appendChild(oTableRow);
			this._oTabGroup.addTab("HTML", new Control_Tab("HTML", oTabContent));
			
			//generate the Preview tab	
			oTabContent	=	$T.table({class: 'reflex input'},
							 oTBody = $T.tbody({class: 'popup-account-edit-rebill-fields'})
							 );

			
			var oPreviewTab = new Control_Tab("Preview", oTabContent)
			oPreviewTab.oTabButton.observe('click', this._htmlPreviewSelected.bind(this));
			this._oTabGroup.addTab("Preview", oPreviewTab);
			this.oHTMLPreviewDiv = document.createElement('div');
			this.oHTMLPreviewDiv.innerHTML = this.oHTMLTextArea.value;
			oTBody.appendChild(this.oHTMLPreviewDiv);
				//oHTMLPreviewDiv.innerHTML = "<h1>Hello</h1>";
		
			//add listener method to the preview tab
			//this.oHTMLTextArea.observe('blur', this._htmlPreviewSelected.bind(this));
		
		// Attach content and get data
		this.setTitle('Email Text Editor');
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
	
	
	
	},
	

	
	display	: function($super)
	{
		$super();
		this.container.style.top = '150px';
	},
	




	

	


	
	
	
	
	
});



