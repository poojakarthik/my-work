
var Collection_Event_Action = Class.create(Collection_Event_Type,
{
	initialize : function($super, aEventInstanceIds, fnComplete)
	{
		$super(aEventInstanceIds, fnComplete);
		
		this._hActionTypes			 		= null;
		this._aInstancesRequiringInput 		= null;
		this._aInstanceIdsNotRequiringInput = null;
	},
	
	_startInvoke : function()
	{
		// Ensure that the action detail requirement constants are loaded
		if (!Flex.Constant.arrConstantGroups.action_type_detail_requirement)
		{
			Flex.Constant.loadConstantGroup('action_type_detail_requirement', this._startInvoke.bind(this));
			return;
		}
		
		// Build list of action types and separate instances into input required and not (required).
		this._getActionTypes(this._processActions.bind(this));
	},
	
	_processActions : function()
	{
		if (this._aInstanceIdsNotRequiringInput.length > 0)
		{
			// Invoke the instances which don't require input
			Collection_Event_Action.invokeEventInstances(this._aInstanceIdsNotRequiringInput);
		}
		
		if (this._aInstancesRequiringInput.length > 0)
		{
			new Popup_Collection_Event_Action(
				this._aInstancesRequiringInput, 
				this._hActionTypes, 
				this._complete.bind(this),
				this._inputCancelled.bind(this)
			);
		}
		else if (this._aInstanceIdsNotRequiringInput.length > 0)
		{
			this._complete();
		}
	},
	
	_complete : function()
	{
		if (this._fnComplete)
		{
			this._fnComplete();
		}
	},
	
	_inputCancelled : function()
	{
		if (this._fnComplete)
		{
			this._fnComplete();
		}
	},
	
	_getActionTypes : function(fnCallback, hActionTypes)
	{
		if (!hActionTypes)
		{
			// Build array of unique action type ids, taken from each event instances type detail
			var aActionTypeIds = [];
			for (var iEventInstanceId in this._hEventInstances)
			{
				var iActionTypeId = this._hEventInstances[iEventInstanceId].collection_event.detail.action_type_id;
				if (aActionTypeIds.indexOf(iActionTypeId) == -1)
				{
					aActionTypeIds.push(iActionTypeId);
				}
			}
			
			// Get the action types
			Collection_Event_Action._getActionTypes(aActionTypeIds, this._getActionTypes.bind(this, fnCallback));
			return;
		}
		
		this._hActionTypes = hActionTypes;
		
		// We have the action types, split them into auto (no input required) and manual (input required/optional)
		var aInstanceIdsNotRequiringInput	= [];
		var aInstancesRequiringInput		= [];
		for (var iEventInstanceId in this._hEventInstances)
		{
			var oInstance	= this._hEventInstances[iEventInstanceId];
			var oActionType	= this._getEventInstanceActionType(oInstance);
			if (oActionType.action_type_detail_requirement_id == $CONSTANT.ACTION_TYPE_DETAIL_REQUIREMENT_NONE)
			{
				// No input required
				aInstanceIdsNotRequiringInput.push(oInstance.id);
			}
			else
			{
				// Input required/optional
				aInstancesRequiringInput.push(oInstance);
			}
		}
		
		this._aInstancesRequiringInput 		= aInstancesRequiringInput;
		this._aInstanceIdsNotRequiringInput = aInstanceIdsNotRequiringInput;
		
		if (fnCallback)
		{
			fnCallback();
		}
	},
	
	_getEventInstanceActionType : function(oEventInstance)
	{
		var iActionTypeId = oEventInstance.collection_event.detail.action_type_id;
		return this._hActionTypes[iActionTypeId];
	}
});

Object.extend(Collection_Event_Action, 
{
	invokeEventInstances : function(hEventInstanceDetails, fnCallback, oResponse) {
		if (!oResponse) {
			var fnResp	= Collection_Event_Action.invokeEventInstances.bind(this, hEventInstanceDetails, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'invokeActionEvents');
			fnReq(hEventInstanceDetails);
			return;
		}
		
		if (!oResponse.bSuccess) {
			jQuery.json.errorPopup(oResponse);
		}
		
		Collection_Event_Type._displayInvokeInformation(oResponse, fnCallback);
	},
	
	_getActionTypes : function(aActionTypeIds, fnCallback, oResponse) {
		if (!oResponse) {
			var fnResp = Collection_Event_Action._getActionTypes.bind(this, aActionTypeIds, fnCallback);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'ActionType', 'getForIds');
			fnReq(aActionTypeIds);
			return;
		}
		
		if (!oResponse.bSuccess) {
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		if (fnCallback) {
			fnCallback(oResponse.aActionTypes);
		}
	}
});