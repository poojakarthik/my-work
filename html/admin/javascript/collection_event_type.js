
var Collection_Event_Type = Class.create(
{
	initialize : function(aEventInstanceIds, fnComplete)
	{
		this._fnComplete 		= fnComplete;
		this._aEventInstanceIds	= aEventInstanceIds;
		this._hEventInstances 	= {};
		this._oLoading			= null;
		Collection_Event_Type._getEventInstancesForIds(aEventInstanceIds, this._instancesLoaded.bind(this));
	},
	
	_instancesLoaded : function(hInstances)
	{
		this._hEventInstances = hInstances;
		this._startInvoke();
	},
	
	_startInvoke : function()
	{
		// Needs to be overridden
	},
	
	_loading : function(sMessage)
	{
		this._hideLoading();
		this._oLoading = new Reflex_Popup.Loading(sMessage);
		this._oLoading.display();
	},
	
	_hideLoading : function()
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
		}
		delete this._oLoading;
	}
});

Object.extend(Collection_Event_Type, 
{
	getInstance : function(iEventTypeImplementationId, aInstanceIds, fnCallback)
	{
		switch (iEventTypeImplementationId)
		{
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
				new Collection_Event_Correspondence(aInstanceIds, fnCallback);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
				new Collection_Event_Report(aInstanceIds, fnCallback);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
				new Collection_Event_Action(aInstanceIds, fnCallback);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_BARRING:
				new Collection_Event_Barring(aInstanceIds, fnCallback);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_TDC:
				new Collection_Event_TDC(aInstanceIds, fnCallback);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
				new Collection_Event_OCA(aInstanceIds, fnCallback);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
				new Collection_Event_Charge(aInstanceIds, fnCallback);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
				new Collection_Event_Severity(aInstanceIds, fnCallback);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS:
				// Do nothing, this shouldn't happen
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_MILESTONE:
				new Collection_Event_Milestone(aInstanceIds, fnCallback);
				break;
		}
	},
	
	getAll : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnGetAll =	jQuery.json.jsonFunction(
								Collection_Event_Type.getAll.curry(fnCallback),
								Collection_Event_Type.getAll.curry(fnCallback),
								'Collection_Event_Type',
								'getAll'
							);
			fnGetAll();
		}
		else
		{
			if (!oResponse.bSuccess)
			{
				Collection_Event_Type.ajaxError(oResponse);
				return;
			}
			
			fnCallback(oResponse.aEventTypes);
		}
	},
	
	_getEventInstancesForIds : function(aEventInstanceIds, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Collection_Event_Type._getEventInstancesForIds.curry(aEventInstanceIds, fnCallback);
			var fnReq 	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Instance', 'getForIds');
			fnReq(aEventInstanceIds);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.aResults);
		}
	},
	
	ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error completing the Event(s). Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Event Completion Error'});
	},

	_displayInvokeInformation : function(oResponse, fnComplete)
	{
		var oCompletedDiv = $T.div({class: 'collection-event-type-summary-container'});
		if (oResponse.aCompletedEventInstances.length)
		{
			for (var i = 0; i < oResponse.aCompletedEventInstances.length; i++)
			{
				var oInstance = oResponse.aCompletedEventInstances[i];
				oCompletedDiv.appendChild(
					$T.div({class: 'collection-event-type-summary-item'},
						Collection_Event_Type._createEventSummaryItem(oInstance)
					)
				);
			}
		}
		else
		{
			oCompletedDiv.appendChild($T.span('None'));
		}
		
		var oFailedDiv = $T.div({class: 'collection-event-type-summary-container'});
		if (oResponse.aFailedEventInstances.length)
		{
			for (var i = 0; i < oResponse.aFailedEventInstances.length; i++)
			{
				var oInstance = oResponse.aFailedEventInstances[i];
				oFailedDiv.appendChild(
					$T.div({class: 'collection-event-type-summary-item collection-event-type-summary-item-exception'},
						Collection_Event_Type._createEventSummaryItem(oInstance),
						Collection_Event_Type._createExceptionSummaryItem(oInstance.exception)	
					)
				);
			}
		}
		else
		{
			oFailedDiv.appendChild($T.span('None'));
		}
		
		var oExceptionDiv = $T.div({class: 'collection-event-type-summary-container'});
		if (oResponse.aExceptions.length)
		{
			for (var i = 0; i < oResponse.aExceptions.length; i++)
			{
				var oException = oResponse.aExceptions[i];
				oExceptionDiv.appendChild(
					$T.div({class: 'collection-event-type-summary-item collection-event-type-summary-item-exception'},
						Collection_Event_Type._createExceptionSummaryItem(oException, true)
					)
				);
			}
		}
		else
		{
			oExceptionDiv.appendChild($T.span('None'));
		}
		
		var oTable =	$T.table({class: 'reflex input'},
							$T.tbody(
								$T.tr(
									$T.th('Completed'),
									$T.td(oCompletedDiv)
								),
								$T.tr(
									$T.th('Failed'),
									$T.td(oFailedDiv)
								),
								$T.tr(
									$T.th('Exceptions'),
									$T.td(oExceptionDiv)
								)
							)
						);
		
		Reflex_Popup.alert(oTable, {sTitle: 'Event Completion Summary', fnClose: fnComplete});
	},
	
	_createEventSummaryItem : function(oInstance)
	{
		return	$T.div(
					$T.div(
						$T.span({class: 'collection-event-type-summary-item-label'},
							'Account:'
						),
						$T.span(oInstance.account_id)
					),
					$T.div(
						$T.span({class: 'collection-event-type-summary-item-label'},
							'Event:'
						),
						$T.span(oInstance.event_name)
					)
				);
	},
	
	_createExceptionSummaryItem : function(oException, bStandAlone)
	{
		var oLabel = 	$T.span({class: 'collection-event-type-summary-item-label'},
							'Reason:'
						);
		
		return 	$T.div(
					bStandAlone ? null : oLabel,
					$T.span(
						oException.message,
						$T.a({class: 'collection-event-type-summary-item-exception-more-detail'},
							'More Detail'
						).observe('click', Collection_Event_Type._showExceptionDetail.curry(oException))	
					)
				);
	},
	
	_showExceptionDetail : function(oException)
	{
		Reflex_Popup.debug(oException.detail);
	}
});