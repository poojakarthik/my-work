
var Collection_Event_Milestone = Class.create(Collection_Event_Type,
{
	initialize : function($super, aEventInstanceIds, fnComplete)
	{
		$super(aEventInstanceIds, fnComplete);
	},
	
	_startInvoke : function()
	{
		this._completeInvoke();
	},
	
	_completeInvoke : function(oResponse) {
		if (!oResponse) {
			this._loading();
			
			var fnResp = this._completeInvoke.bind(this);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'invokeMilestoneEvent');
			fnReq(this._aEventInstanceIds);
			return;
		}
		
		this._hideLoading();
		
		if (!oResponse.bSuccess) {
			jQuery.json.errorPopup(oResponse);
		}
		
		Collection_Event_Type._displayInvokeInformation(oResponse, this._fnComplete);
	}
});