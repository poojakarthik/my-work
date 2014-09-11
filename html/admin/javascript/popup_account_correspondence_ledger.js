
var Popup_Account_Correspondence_Ledger = Class.create(Reflex_Popup, 
{
	initialize	: function($super, iId)
	{
		$super(55);
		this._iId	= iId;
		Flex.Constant.loadConstantGroup('correspondence_delivery_method', this._buildUI.bind(this));
	},
	
	display	: function($super)
	{
		$super();
		this.container.style.top = '150px';
	},
	
	_buildUI	: function()
	{
		var oLedgerDiv		= $T.div();
		this._oContentDiv 	= 	$T.div({class: 'popup-account-correspondence-ledger'},
									oLedgerDiv
								);
		this.setContent(this._oContentDiv);
		this.setTitle('Account Correspondence Ledger');
		this.addCloseButton();
		this.display();
		
		this._oLedger	= new Component_Correspondence_Ledger_For_Account(oLedgerDiv, this._iId);
	}
});