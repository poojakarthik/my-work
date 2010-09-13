
var Popup_Correspondence_Run_Ledger = Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		var iEmWidth	= 75;
		if (document.viewport.getWidth() > 1024)
		{
			iEmWidth	= 95;
		}
	
		$super(iEmWidth);
		
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		
		this._oContentDiv 	= $T.div();
		this._oLedger		= new Component_Correspondence_Run_Ledger(this._oContentDiv, this._oLoadingPopup);
		this.setContent(this._oContentDiv);
		this.setTitle('Correspondence Run Ledger');
		this.addCloseButton();
		this.display();
		
		this._oLoadingPopup.display();
	},
	
	display	: function($super)
	{
		$super();
		this.container.style.top = '150px';
	},
});

