
var Popup_Collections_Scenario = Class.create(Reflex_Popup, 
{
	initialize : function($super, bRenderMode, iScenarioId, fnOnComplete)
	{
		$super(70);
		
		this._fnOnComplete = fnOnComplete;
		
		var oContentDiv = $T.div();
		this.setTitle((bRenderMode ? 'Create' : 'View') + ' Collections Scenario');
		this.setContent(oContentDiv);
		this.addCloseButton();
		this.display();
		
		var oLoading = new Reflex_Popup.Loading('');
		oLoading.display();
		new Component_Collections_Scenario(oContentDiv, bRenderMode, iScenarioId, this._createComplete.bind(this), this._createCancelled.bind(this), oLoading, true);
	},
	
	_createComplete : function()
	{
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
		this.hide();
	},
	
	_createCancelled : function()
	{
		this.hide();
	},
});