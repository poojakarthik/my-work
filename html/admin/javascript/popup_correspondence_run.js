
var Popup_Correspondence_Run = Class.create(Reflex_Popup, 
{
	initialize	: function($super, iId)
	{
		$super(75);
		
		this._iId	= iId;
		
		Flex.Constant.loadConstantGroup('correspondence_delivery_method', this._buildUI.bind(this));
	},
	
	_buildUI	: function()
	{
		var oDetailsDiv	= $T.div();
		var oDetails	= new Component_Correspondence_Run_Details(oDetailsDiv, this._iId);
		var oLedgerDiv	= $T.div();
		var oLedger		= new Component_Correspondence_Ledger_For_Run(oLedgerDiv, this._iId);
		
		this._oContentDiv 	= 	$T.div({class: 'popup-correspondence-run'},
									oDetailsDiv,
									oLedgerDiv
								);
		
		this.setContent(this._oContentDiv);
		this.setTitle('Correspondence Run Details');
		this.addCloseButton();
		this.display();
	}
});