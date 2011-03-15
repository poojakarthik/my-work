
var Popup_Collections_Event_Instance_View = Class.create(Reflex_Popup, 
{
	initialize : function($super, iEventInstanceId)
	{
		$super(40);
		
		this._iEventInstanceId = iEventInstanceId;
		
		Flex.Constant.loadConstantGroup(Popup_Collections_Event_Instance_View.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._buildUI.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Instance', 'getForIds');
			fnReq([this._iEventInstanceId]);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Collections_Event_Instance_View._ajaxError(oResponse);
			return;
		}
		
		// Success, have got the details
		var oEventInstance = oResponse.aResults[this._iEventInstanceId];
		
		var oEventType	= oEventInstance.collection_event.collection_event_type;
		var sEventType	= oEventType.description == oEventType.name ? oEventType.name : oEventType.name + ' (' + oEventType.description + ')';
		
		var oContentDiv = 	$T.div({class: 'popup-collections-event-instance-view'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Account'),
											$T.td(
												$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oEventInstance.account.Id}, 
													oEventInstance.account.Id
												),
												$T.span(': ' + oEventInstance.account.BusinessName)
											)
										),
										$T.tr(
											$T.th('Event'),
											$T.td(oEventInstance.collection_event.name)
										),
										$T.tr(
											$T.th('Event Type'),
											$T.td(sEventType)
										),
										$T.tr(
											$T.th('Is Manual'),
											$T.td(Flex.Constant.arrConstantGroups.collection_event_invocation[oEventInstance.collection_event_invocation_id].Name)
										),
										$T.tr(
											$T.th('Scheduled On'),
											$T.td(Popup_Collections_Event_Instance_View._formatDateTime(oEventInstance.scheduled_datetime))
										),
										$T.tr(
											$T.th('Completed On'),
											$T.td(Popup_Collections_Event_Instance_View._formatDateTime(oEventInstance.completed_datetime))
										),
										$T.tr(
											$T.th('Completed By'),
											$T.td(
												oEventInstance.completed_employee ? oEventInstance.completed_employee.FirstName + ' ' + oEventInstance.completed_employee.LastName : null
											)
										),
										$T.tr(
											$T.th('Status'),
											$T.td(Flex.Constant.arrConstantGroups.account_collection_event_status[oEventInstance.account_collection_event_status_id].Name)
										)
									)
								),
								$T.div({class: 'popup-collections-event-instance-view-buttons'},
									$T.button('OK').observe('click', this.hide.bind(this))	
								)
							);
		
		this.setTitle('Collections Event Details');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	}
});

Object.extend(Popup_Collections_Event_Instance_View, 
{
	REQUIRED_CONSTANT_GROUPS : ['collection_event_invocation',
	                            'account_collection_event_status'],
	
	_formatDateTime : function(sDatetime)
	{
		if (sDatetime !== null)
		{
			var oDate = Date.$parseDate(sDatetime, 'Y-m-d H:i:s');
			if (oDate)
			{
				return oDate.$format('d/m/y g:i A');
			}
		}
		return '';
	},
	
	_formatDate : function(sDatetime)
	{
		if (sDatetime !== null)
		{
			var oDate = Date.$parseDate(sDatetime, 'Y-m-d');
			if (oDate)
			{
				return oDate.$format('d/m/y');
			}
		}
		return '';
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	}
});

