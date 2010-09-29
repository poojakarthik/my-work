
var Popup_email_HTML_Preview	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, sHTML, fnCallback)
	{
		$super(70);
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._fnCallback = fnCallback;
		
		this._buildUI(sHTML);
	},
	
	// Private
	
	_buildUI	: function(sHTML)
	{
		var oTemplateSelect		= 	$T.div({class: 'popup-account-edit-rebill'},
									$T.div({class: 'preview-pane'}
										
									),
									
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Close'}),
											$T.span('Close')
										).observe('click', this._close.bind(this))
									
								);
		this._oTemplateSelect	= oTemplateSelect;		
		this.oHTMLPreviewDiv = document.createElement('div');
			this.oHTMLPreviewDiv.innerHTML = sHTML;
			
			
		var oPreviewDiv = oTemplateSelect.select('div.preview-pane').first();
		oPreviewDiv.appendChild(this.oHTMLPreviewDiv);
		this.setTitle('HTML Preview');
		
		this.addCloseButton(this._close.bind(this));
		this.setContent(oTemplateSelect);
		this.display();
	},
	
	_close : function ()
	{
		this.hide();
		this._fnCallback();
	
	}
});	
	