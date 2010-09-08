
var Popup_Correspondence_Run_Ledger = Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(75);
		
		this._oContentDiv 	= $T.div();
		this._oLedger		= new Component_Correspondence_Run_Ledger(this._oContentDiv);
		this.setContent(this._oContentDiv);
		this.setTitle('Correspondence Run Ledger');
		this.addCloseButton();
		this.display();
	}
});

