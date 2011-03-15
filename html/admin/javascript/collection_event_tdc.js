
var Collection_Event_TDC = Class.create(Collection_Event_Type,
{
	initialize : function($super, aEventInstanceIds, fnComplete)
	{
		$super(aEventInstanceIds, fnComplete);
	},
	
	_startInvoke : function()
	{
		// Show a popup to allow scheduling of barring requests
		new Popup_Collection_Event_TDC(this._hEventInstances, this._complete.bind(this), this._cancelled.bind(this));
	}, 
	
	_cancelled : function()
	{
		if (this._fnComplete)
		{
			this._fnComplete();
		}
	},
	
	_complete : function()
	{
		if (this._fnComplete)
		{
			this._fnComplete();
		}
	}
});