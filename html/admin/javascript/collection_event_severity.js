
var Collection_Event_Severity = Class.create(Collection_Event_Type,
{
	initialize : function($super, aEventInstanceIds, fnComplete)
	{
		$super(aEventInstanceIds, fnComplete);
	},
	
	_startInvoke : function()
	{
		this._completeInvoke();
	},
	
	_completeInvoke : function(oResponse)
	{
		if (!oResponse)
		{
			this._loading('Setting Account Severity...');
			
			var fnResp 	= this._completeInvoke.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'invokeSeverityEvent');
			fnReq(this._aEventInstanceIds);
			return;
		}
		
		this._hideLoading();
		
		if (!oResponse.bSuccess)
		{
			Collection_Event_Type.ajaxError(oResponse);
		}
		
		Collection_Event_Type._displayInvokeInformation(oResponse, this._fnComplete);
	}
});