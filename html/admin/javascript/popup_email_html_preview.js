
var Popup_email_HTML_Preview	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, sHTML, fnCallback)
	{
		$super(70,70);
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._fnCallback = fnCallback;
		this._sHTML = sHTML;
		this._buildUI();
	},
	
		
	_buildUI	: function()
	{
		
		var oContent		= 	$T.div({class: 'popup-email-html-preview'},
									$T.iframe({class: 'preview-pane' }
										
									).observe('load', this._setFrameContent.bind(this)),
									
									
									$T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Close'}),
											$T.span('Close')
										).observe('click', this._close.bind(this))
									)
									

									
								);
			
		
		this._ifrm = oContent.select('iframe.preview-pane').first();		
		
		this.setTitle('HTML Preview');		
		this.addCloseButton(this._close.bind(this));
		this.setContent(oContent);
		this.display();
	},
	
	_setFrameContent: function()
	{
	
		ifrm = (this._ifrm.contentWindow) ? this._ifrm.contentWindow : (this._ifrm.contentDocument.document) ? this._ifrm.contentDocument.document : this._ifrm.contentDocument;
		ifrm.document.open();
		ifrm.document.write(this._sHTML);
		ifrm.document.close();
		//this._ifrm.style.height = ifrm.document.body.scrollHeight + (ifrm.document.body.offsetHeight - ifrm.document.body.clientHeight);	
	},
	
	_close : function ()
	{
		this.hide();
		this._fnCallback();
	
	}
});	
	