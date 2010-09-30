
var Popup_Email_Save_Confirm	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oResponse, fnCallback)
	{
		$super(70);
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._fnCallback = fnCallback;
		this._oResponse = oResponse;
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(sHTML)
	{
		var oTemplateSelect		= 	$T.div({class: 'popup-email-html-preview'},
									$T.div({class: 'preview-pane'}
										
									),
									
									
									$T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Save'}),
											$T.span('Save')
										).observe('click', this._save.bind(this)),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										).observe('click', this._close.bind(this)),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Preview'}),
											$T.span('Preview')
										).observe('click', this._preview.bind(this))
									)
									

									
								);
		this._oTemplateSelect	= oTemplateSelect;		
		this.oHTMLPreviewDiv = document.createElement('div');
		var header = document.createElement('h2');
		header.innerHTML = 'In order to make it render consistently across different mail clients, the HTML you supplied will be modified as follows:';
			this.oHTMLPreviewDiv.appendChild(header);
			// for (var i=0;i<oResponse.Report.length;i++)
			// {
				// var div = document.createElement('div');
				// div.innerHTML = oResponse.Report[i].length
				// for (var z = 0;z<oResponse.Report[i];z++)
				// {
					// var div = document.createElement('div');
					// div.innerHTML = 
				
				// }
				
				// var div = document.createElement('div');
				// div.innerHTML = 
			
			// }
			
			
		var oPreviewDiv = oTemplateSelect.select('div.preview-pane').first();
		oPreviewDiv.appendChild(this.oHTMLPreviewDiv);
		this.setTitle('Email Save Confirm');
		
		this.addCloseButton(this._close.bind(this));
		this.setContent(oTemplateSelect);
		this.display();
	},
	
	_save: function()
	{
	
	
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
		//this.oHTMLPreviewDiv.innerHTML = html;
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
	