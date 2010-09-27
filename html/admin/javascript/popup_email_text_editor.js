
var Popup_Email_Text_Editor	= Class.create(Reflex_Popup, 
{
	

	
	
	
	initialize	: function($super)
	{
			// Image paths
			Popup_Email_Text_Editor.ICON_IMAGE_SOURCE 	= '../admin/img/template/rebill.png';
			Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
			Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
		$super(70);
		
				this._oLoadingPopup	= new Reflex_Popup.Loading();
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
								
			this._getVariables();					
			

	},
	
	_getVariables: function()
	{
		var fnRequest     = jQuery.json.jsonFunction(this._getVariablesSuccess.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'getVariables');
		fnRequest();
	},
	
	_getVariablesSuccess: function(oResponse)
	{
		this._buildGUI(oResponse.variables);
	
	},
	
	_buildGUI: function()
	{
	
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
			oTableRow = oControl.generateInputTableRow().oElement;
			var th = oTableRow.select('th').first();
			
			
			th.appendChild($T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.ICON_IMAGE_SOURCE, alt: '', title: 'Generate Text'}),
											$T.span('Generate Text')
										)
									));
									
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
	
	
	_htmlPaneChange: function()
	{
		this.oHTMLPreviewDiv.innerHTML = this.oHTMLTextArea.value;
		
		
	},
	
	_htmlPreviewSelected: function(html)
	{
		//this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successPreviewCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'processHTML');
		fnRequest(this._preprocessHTML());
	
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
		this.oHTMLPreviewDiv.innerHTML = html;		 	  
	},
	
	successToTextCallback: function (oResponse)
	{
	    this._oLoadingPopup.hide();	
		
		var text = oResponse.text;
		this.oTextArea.value = text;		 	  
	},
	
	_preprocessHTML: function ()
	{
		var oDiv = document.createElement('div');
		oDiv.innerHTML = this.oHTMLTextArea.value;
		var s = new XMLSerializer(); 
		xml =  XML(s.serializeToString(oDiv)).toXMLString();
		return s.serializeToString(oDiv);	
	
	},
	
	_saveButtonClick: function()
	{		
		//this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'save');
		fnRequest(this._preprocessHTML());		
	},
	
	_generateTextButtonClick: function()
	{
		//this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		var fnRequest     = jQuery.json.jsonFunction(this.successToTextCallback.bind(this), this.errorCallback.bind(this), 'Email_Text_Editor', 'toText');
		fnRequest(this._preprocessHTML());	
	
	
	}

	
	
	
	
	
});



