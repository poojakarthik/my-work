
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{
	

	
	
	
	initialize	: function($super)
	{
			// Image paths
			Popup_Email_Text_Editor.ICON_IMAGE_SOURCE 	= '../admin/img/template/rebill.png';
			Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
			Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
		$super(70);
		
				
		// Build content
			this._oContent	= 	$T.div({class: 'popup-account-edit-rebill'},
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
								
								
			// Button events
			var oAddButton		= this._oContent.select('div.buttons > button.icon-button').first();
			oAddButton.observe('click', this._saveButtonClick.bind(this));

			//var oCancelButton	= this._oContent.select('div.buttons > button.icon-button').last();
			//oCancelButton.observe('click', this._cancelEdit.bind(this));

			
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
			oTBody.appendChild(oControl.generateInputTableRow().oElement);
			this._oTabGroup.addTab("Text", new Control_Tab("Text", oTabContent));
			
			//generate the HTML tab
			oTabContent	=	$T.table({class: 'reflex input'},
							 oTBody = $T.tbody({class: 'popup-account-edit-rebill-fields'})
							 );
				 
			oControl	= Control_Field.factory('textarea', oDefinition);
			
			oControl.oControlOutput.oEdit
			this.oHTMLTextArea = oControl.oControlOutput.oEdit;
			// this.oHTMLTextArea.value   = " <div> \
												// <cssclass name = 'yellow' style = 'background: yellow; color: #00ff00; margin-left: 2cm'></cssclass> \
												// <cssclass name = 'blue' style = 'background: blue; color: #00ff00; margin-left: 2cm'></cssclass> \
												// <div> \
												  // <h1 class = 'yellow'>text</h1> \
												  // <h2>stuff</h2> \
												 // </div> \
												  // <p class = 'blue'>code</p> \
												 // <script> \
												 // alert('hello'); \
												 // </script> \
												// </div>";
			oTBody.appendChild(oControl.generateInputTableRow().oElement);
			this._oTabGroup.addTab("HTML", new Control_Tab("HTML", oTabContent));
			
			//generate the Preview tab	
			oTabContent	=	$T.table({class: 'reflex input'},
							 oTBody = $T.tbody({class: 'popup-account-edit-rebill-fields'})
							 );

			this._oTabGroup.addTab("Preview", new Control_Tab("Preview", oTabContent));
			this.oHTMLPreviewDiv = document.createElement('div');
			this.oHTMLPreviewDiv.innerHTML = this.oHTMLTextArea.value;
			oTBody.appendChild(this.oHTMLPreviewDiv);
				//oHTMLPreviewDiv.innerHTML = "<h1>Hello</h1>";
		
			//add listener method to the html tabl
			this.oHTMLTextArea.observe('blur', this._htmlPaneChange.bind(this));
		
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
	
	
	_htmlPaneChange: function()
	{
		this.oHTMLPreviewDiv.innerHTML = this.oHTMLTextArea.value;
		
		
	},
	
	_htmlPreviewSelected: function(html)
	{
		var fnRequest     = jQuery.json.jsonFunction(this.successCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(html);
	
	},

	 errorCallback: function()
	{
		  // This gets called when it fails, happens rarely
		  alert('error');
	},

	 successCallback: function (oResponse)
	{
		 
		 
		  var html = oResponse.html;
		  this.oTextArea.innerHTML = html;
		  this.oHTMLPreviewDiv.innerHTML = html;		  
	},
	
	_saveButtonClick: function()
	{
		var parser=new DOMParser();
		var oDiv = document.createElement('div');
		oDiv.innerHTML = this.oHTMLTextArea.value;
		var s = new XMLSerializer(); 
		this._htmlPreviewSelected(s.serializeToString(oDiv));
	}

	
	
	
	
	
});



