
var Popup_Collections_Scenario_Event_Timeline_Event = Class.create(Reflex_Popup,
{
	initialize : function($super, iEventId, iDayOffset, iInvocationId, fnOnSave)
	{
		$super(40);
		
		this._iEventId 		= iEventId;
		this._iDayOffset 	= (iDayOffset ? iDayOffset : 0);
		this._iInvocationId	= iInvocationId;
		this._fnOnSave		= fnOnSave;
		
		// Clear the required inactive (current) event
		Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent = null;
		
		// Load invocation constants
		Flex.Constant.loadConstantGroup('collection_event_invocation', this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
		// Create control fields
		var oEventControl = Control_Field.factory(
								'select', 
								{
									sLabel		: 'Event',
									mMandatory	: true,
									mEditable	: true,
									fnPopulate	: Popup_Collections_Scenario_Event_Timeline_Event._getEventOptions.curry(this._iEventId)
								}
							);
		oEventControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oEventControl.addOnChangeCallback(this._eventChange.bind(this));
		this._oEventControl = oEventControl;
		
		if (this._iEventId)
		{
			this._oEventControl.setValue(this._iEventId);
		}
		
		var oDayOffsetControl =	Control_Field.factory(
									'text', 
									{
										sLabel		: 'Day Offset',
										mMandatory	: true,
										mEditable	: true,
										fnValidate	: Reflex_Validation.digits
									}
								);
		oDayOffsetControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oDayOffsetControl = oDayOffsetControl;
		
		if (this._iDayOffset !== null)
		{
			oDayOffsetControl.setValue(this._iDayOffset);
		}
		
		var oInvocationControl = 	Control_Field.factory(
										'select', 
										{
											sLabel		: 'Manual',
											mMandatory	: true,
											mEditable	: true,
											fnPopulate	: this._getInvocationOptions.bind(this)
										}
									);
		oInvocationControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oInvocationControl = oInvocationControl;
		
		if (this._iInvocationId === null)
		{
			this._oInvocationControl.setValue(Popup_Collections_Scenario_Event_Timeline_Event.DEFAULT_INVOCATION);
		}
		else if (this._iInvocationId)
		{
			this._oInvocationControl.setValue(this._iInvocationId);
		}
		
		// Create UI content
		var oContentDiv =	$T.div({class: 'popup-collections-scenario-event-timeline-event'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Event'),
											$T.td(
												$T.ul({class: 'popup-collections-scenario-event-timeline-event-eventlist reset horizontal'},
													$T.li(this._oEventControl.getElement()),
													$T.li(
														$T.button({class: 'icon-button'},
															$T.img({src: '../admin/img/template/new.png'}),
															$T.span('Add Event')
														).observe('click', this._addEvent.bind(this, null))
													)
												)
											)
										),
										$T.tr(
											$T.th('Day Offset'),
											$T.td(this._oDayOffsetControl.getElement())
										),
										$T.tr(
											$T.th('Manual'),
											$T.td(this._oInvocationControl.getElement())		
										)
									)
								),
								$T.div({class: 'popup-collections-scenario-event-timeline-event-buttons'},
									$T.button('OK').observe('click', this._save.bind(this)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		this.setContent(oContentDiv);
		this.setTitle(((this._iEventId !== null) ? 'New' : 'Edit') + ' Event');
		this.addCloseButton();
		this.display();
	},
	
	_eventChange : function()
	{
		this._oInvocationControl.populate();
	},
	
	_getSelectedEvent : function()
	{
		if (!Popup_Collections_Scenario_Event_Timeline_Event._hEvents)
		{
			return false;
		}
		
		var iEventId = parseInt(this._oEventControl.getElementValue());
		if (Popup_Collections_Scenario_Event_Timeline_Event._hEvents[iEventId])
		{
			// Is on of the active events
			return Popup_Collections_Scenario_Event_Timeline_Event._hEvents[iEventId];
		}
		else if (Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent && (iEventId == Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent.id))
		{
			// Is the required (current) inactive event
			return Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent;
		}
	},
	
	_getInvocationOptions : function(fnCallback, oResponse)
	{
		var aOptions 	= [];
		var oEvent 		= this._getSelectedEvent();
		if (oEvent)
		{
			var oType 			= oEvent.collection_event_type;
			var oImplementation	= oEvent.collection_event_type.collection_event_type_implementation;
			
			// Check if an invocation is enforced by types implementation or the type itself
			var iEnforcedInvocationId = null;
			if (oImplementation.enforced_collection_event_invocation_id)
			{
				// Enforced by implementation
				iEnforcedInvocationId = oImplementation.enforced_collection_event_invocation_id;
			}
			else if (oType.collection_event_invocation_id)
			{
				// Enforced by type
				iEnforcedInvocationId = oType.collection_event_invocation_id;
			}
			
			if (iEnforcedInvocationId !== null)
			{
				// Only one allowed option
				aOptions.push(
					$T.option({value: Popup_Collections_Scenario_Event_Timeline_Event.DEFAULT_INVOCATION},
						'Default (' + Flex.Constant.arrConstantGroups.collection_event_invocation[iEnforcedInvocationId].Name + ')'
					)
				);
			}
		}
		
		if (!aOptions.length)
		{
			if (oEvent && oEvent.collection_event_invocation_id)
			{
				// Default to the events invocation
				aOptions.push(
					$T.option({value: Popup_Collections_Scenario_Event_Timeline_Event.DEFAULT_INVOCATION},
						'Default (' + Flex.Constant.arrConstantGroups.collection_event_invocation[oEvent.collection_event_invocation_id].Name + ')'
					)
				);
			}
			
			// Add all invocation options
			var aData = Flex.Constant.arrConstantGroups.collection_event_invocation;
			for (var i in aData)
			{
				aOptions.push(
					$T.option({value : i},
						aData[i].Name	
					)
				);
			}
		}
		
		fnCallback(aOptions);
	},
	
	_save : function()
	{
		// Validation
		var aControls	= [this._oEventControl, this._oDayOffsetControl, this._oInvocationControl];
		var aErrors 	= [];
		for (var i = 0; i < aControls.length; i++)
		{
			try
			{
				aControls[i].validate(false);
			}
			catch (oException)
			{
				aErrors.push(oException);
			}
		}
		
		if (aErrors.length)
		{
			// There were validation errors, show all in a popup
			var oErrorElement = $T.ul();
			for (var i = 0; i < aErrors.length; i++)
			{
				oErrorElement.appendChild($T.li(aErrors[i]));
			}
			
			Reflex_Popup.alert(
				$T.div(
					$T.div('There were errors in the form:'),
					oErrorElement
				),
				{sTitle: 'Validation Error'}
			);
			return;
		}
		
		if (this._fnOnSave)
		{
			var oEvent 			= this._getSelectedEvent();
			var iDayOffset		= parseInt(this._oDayOffsetControl.getElementValue());
			var iInvocationId	= parseInt(this._oInvocationControl.getElementValue());
			if (iInvocationId == Popup_Collections_Scenario_Event_Timeline_Event.DEFAULT_INVOCATION)
			{
				// Default, determine from the event
				var oType 			= oEvent.collection_event_type;
				var oImplementation	= oEvent.collection_event_type.collection_event_type_implementation;
				if (oImplementation.enforced_collection_event_invocation_id)
				{
					// Enforced by implementation
					iInvocationId = oImplementation.enforced_collection_event_invocation_id;
				}
				else if (oType.collection_event_invocation_id)
				{
					// Enforced by type
					iInvocationId = oType.collection_event_invocation_id;
				}
				else if (oEvent.collection_event_invocation_id)
				{
					// Default to the events invocation
					iInvocationId = oEvent.collection_event_invocation_id;
				}
			}
			
			// Callback with new details
			this._fnOnSave(oEvent, iDayOffset, iInvocationId);
		}
		
		this.hide();
	},
	
	_addEvent : function(iEventId, oEvent)
	{
		if (iEventId === null)
		{
			// Add event popup
			new Popup_Collections_Event(this._addEvent.bind(this));
			return;
		}
		
		if (iEventId)
		{
			Popup_Collections_Scenario_Event_Timeline_Event._hEvents 				= null;
			Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent	= null;
			this._oEventControl.populate();
			this._oEventControl.setValue(iEventId);
		}
	}
});

// Static

Object.extend(Popup_Collections_Scenario_Event_Timeline_Event, 
{
	DEFAULT_INVOCATION : 0,
	
	_hEvents 				: null,
	_oRequiredInactiveEvent	: null,
		
	_getEventOptions : function(iRequiredEventId, fnCallback, oResponse, oRequiredEventResponse)
	{
		// No events cached
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Popup_Collections_Scenario_Event_Timeline_Event._getEventOptions.curry(iRequiredEventId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'getAll');
			fnReq(true, true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		Popup_Collections_Scenario_Event_Timeline_Event._hEvents = oResponse.aEvents;
		
		var oExtraEvent = null;
		if ((iRequiredEventId !== null) && !Popup_Collections_Scenario_Event_Timeline_Event._hEvents[iRequiredEventId])
		{
			// The required event is missing, get it separately (must be current and inactive)
			if (!oRequiredEventResponse)
			{
				// Make request
				var fnResp 	= Popup_Collections_Scenario_Event_Timeline_Event._getEventOptions.curry(iRequiredEventId, fnCallback, oResponse);
				var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'getForId');
				fnReq(iRequiredEventId);
				return;
			}
			
			if (!oRequiredEventResponse.bSuccess)
			{
				// Error
				jQuery.json.errorPopup(oResponse);
				return;
			}
			
			Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent = oRequiredEventResponse.oEvent;
		}
		
		// Create options & callback
		var aData 		= Popup_Collections_Scenario_Event_Timeline_Event._hEvents;
		var aOptions 	= [];
		
		if (Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent)
		{
			// Add extra (inactive) event because it is required (currently saved to the scenario event)
			var oExtraEvent = Popup_Collections_Scenario_Event_Timeline_Event._oRequiredInactiveEvent;
			aOptions.push(
				$T.option({value : oExtraEvent.id},
					oExtraEvent.name
				)
			);
		}
		
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
});