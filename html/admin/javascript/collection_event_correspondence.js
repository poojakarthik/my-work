
var Collection_Event_Correspondence = Class.create(Collection_Event_Type,
{
	initialize : function($super, aEventInstanceIds, fnComplete)
	{
		$super(aEventInstanceIds, fnComplete);
	},
	
	_startInvoke : function()
	{
		this._createCorrespondenceRun();
	},
	
	_createCorrespondenceRun : function(oResponse)
	{
		if (!oResponse)
		{
			this._loading('Sending Correspondence...');
			
			var fnResp 	= this._createCorrespondenceRun.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'invokeCorrespondenceEvent');
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