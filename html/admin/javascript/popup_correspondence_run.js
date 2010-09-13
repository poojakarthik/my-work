
var Popup_Correspondence_Run = Class.create(Reflex_Popup, 
{
	initialize	: function($super, iId)
	{
		$super(75);
		
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
		var oDetailsDiv	= $T.div();
		var oLedgerDiv	= $T.div();
		this._oContentDiv 	= 	$T.div({class: 'popup-correspondence-run'},
									oDetailsDiv,
									oLedgerDiv
								);
		this.setContent(this._oContentDiv);
		this.setTitle('Correspondence Run Details');
		this.addCloseButton();
		this.display();
		
		var oDetails	= new Component_Correspondence_Run_Details(oDetailsDiv, this._iId);
		var oLedger		= new Component_Correspondence_Ledger_For_Run(oLedgerDiv, this._iId);
	}
});