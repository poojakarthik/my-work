
var Popup_Collections_Event = Class.create(Reflex_Popup, 
{
	initialize : function($super, fnOnComplete)
	{
		$super(60);
		
		this._fnOnComplete = fnOnComplete;
		
		var oContentDiv = $T.div();
		this.setIcon('../admin/img/template/new.png');
		this.setTitle('Create Event');
		this.setContent(oContentDiv);
		this.addCloseButton();
		this.display();
		
		var oLoading = new Reflex_Popup.Loading('');
		oLoading.display();
		new Component_Collections_Event(oContentDiv, this._createComplete.bind(this), this._createCancelled.bind(this), oLoading);
	},
	
	_createComplete : function(iEventId)
	{
		if (this._fnOnComplete)
		{
			this._fnOnComplete(iEventId);
		}
		this.hide();
	},
	
	_createCancelled : function()
	{
		this.hide();
	},
});