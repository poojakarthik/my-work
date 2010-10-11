
var Popup_Correspondence_Run_Ledger = Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		var iEmWidth	= 75;
		var sClass		= 'popup-correspondence-run-ledger-normal';
		if (document.viewport.getWidth() > 1024)
		{
			iEmWidth	= 95;
			sClass		= 'popup-correspondence-run-ledger-large';
		}
	
		$super(iEmWidth);
		
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		
		this._oContentDiv 	= $T.div({class: sClass});
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

