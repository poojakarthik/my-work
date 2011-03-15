
var Component_Collections_Scenario_Event_Timeline = Class.create(
{
	initialize : function(bRenderMode, iScenarioId, oLoadingPopup)
	{
		this._iMaxNumberOfDays 	= 100;
		this._oScenario			= null;
		this._bRenderMode	 	= bRenderMode;
		this._aNodes			= [];
		this._oElement 			= $T.div({class: 'component-collections-scenario-event-timeline'});
		this._oLoadingPopup		= oLoadingPopup;
		
		this._oComponentPositionAtDragStart	= null;
		this._oDragNodeElement 				= null;
		this._oDragNode						= null;
		this._iDragCell						= null;
		this._iDragPosition					= null;
		this._oDragTargetCell				= null;
	
		this._sClassRenderModeSuffix = (bRenderMode == Control_Field.RENDER_MODE_VIEW ? '-view' : '');
		
		if (iScenarioId)
		{
			this._loadConstants(this._getScenarioDetails.bind(this, iScenarioId));
		}
		else
		{
			this._loadConstants(this._buildUI.bind(this));
		}
	},
	
	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	getData : function()
	{
		var aData = [];
		for (var i = 0; i < this._aNodes.length; i++)
		{
			var oNode 	= this._aNodes[i];
			if (oNode.iNodeType == Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT)
			{
				var oRecord	=
				{
					collection_event_id				: oNode.iEventId,
					collection_event_invocation_id	: oNode.iInvocationId,
					day_offset						: oNode.iDayOffset
				};
				aData.push(oRecord);
			}
		}
		return aData;
	},
	
	setStartDayOffset : function(iDayOffset)
	{
		if (!isNaN(iDayOffset) && this._iStartDay != iDayOffset)
		{
			if (this._oScenario)
			{
				this._oScenario.day_offset = iDayOffset;
			}
			
			if (iDayOffset < this._iStartDay)
			{
				// New day offset is before the current start day
				// Add preceding days, to the header row
				for (var iDay = this._iStartDay - 1; iDay >= iDayOffset; iDay--)
				{
					this._oDayTR.insertBefore(this._createDayCell(iDay), this._oDayTR.firstChild);
				}
				
				// Add preceding days, create a cell in each row
				for (var i = 0; i < this._oTBody.childNodes.length; i++)
				{
					var oTR = this._oTBody.childNodes[i];
					for (var iDay = this._iStartDay - 1; iDay >= iDayOffset; iDay--)
					{
						oTR.insertBefore(this._createNodeCell(), oTR.firstChild);
					}
				}
			}
			else
			{
				// New day offset is after the current start day, remove unneeded columns
				// Delete unused day cells preceeding the start day
				var iNumberUnused = (iDayOffset - this._iStartDay);
				for (var iDay = 0; iDay < iNumberUnused; iDay++)
				{
					this._oDayTR.firstChild.remove();
				}
				
				// Add preceding days, create a cell in each row
				for (var i = 0; i < this._oTBody.childNodes.length; i++)
				{
					var oTR = this._oTBody.childNodes[i];
					for (var iDay = 0; iDay < iNumberUnused; iDay++)
					{
						oTR.firstChild.remove();
					}
				}
			}
			
			this._iStartDay	= iDayOffset;
			this._refresh();
		}
	},
	
	setRenderMode : function(bRenderMode)
	{
		this._bRenderMode = bRenderMode;
	},
	
	refresh : function(iScenarioId)
	{
		this._getScenarioDetails((iScenarioId ? iScenarioId : this._oScenario.id), this._refresh.bind(this));
	},
	
	// Protected
	
	_loadConstants : function(fnCallback)
	{
		Flex.Constant.loadConstantGroup(Component_Collections_Scenario_Event_Timeline.REQUIRED_CONSTANT_GROUPS, fnCallback);
	},
	
	_buildUI : function()
	{
		this._oElement.appendChild(
			$T.table(
				$T.thead(
					$T.tr({class: 'component-collections-scenario-event-timeline-day-row'})	
				),
				$T.tbody({class: 'component-collections-scenario-event-timeline-body'}),
				$T.tfoot()
			)
		);
		
		this._oDayTR 	= this._oElement.select('.component-collections-scenario-event-timeline-day-row').first();
		this._oTBody 	= this._oElement.select('.component-collections-scenario-event-timeline-body').first();
		this._iStartDay	= (this._oScenario ? (this._oScenario.day_offset ? this._oScenario.day_offset : 0) : 0);
		
		// Add day row (thead) cells
		for (var iDay = this._iStartDay; iDay <= this._iMaxNumberOfDays; iDay++)
		{
			// Add day, create a day row cell
			this._oDayTR.appendChild(this._createDayCell(iDay));
		}
		
		if (this._oScenario === null)
		{
			this._createNode(Component_Collections_Scenario_Event_Timeline.NODE_TYPE_SCENARIO_START);
		}
		
		this._refresh();
	},
	
	_getScenarioDetails : function(iScenarioId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp 	= this._getScenarioDetails.bind(this, iScenarioId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Scenario', 'getForId');
			fnReq(iScenarioId, true);
			return;
		}

		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Scenario_Event_Timeline._ajaxError(oResponse);
			return;
		}
		
		// Callback
		this._oScenario = oResponse.oScenario;
		
		// Reset nodes array
		this._aNodes = [];
		
		// Create the scenario start (first) node
		this._createNode(Component_Collections_Scenario_Event_Timeline.NODE_TYPE_SCENARIO_START);
		
		// Import from the scenario
		this._importEventNodesFromScenario();
		
		if (fnCallback)
		{
			fnCallback();
		}
	},
	
	_importEventNodesFromScenario : function()
	{
		if (Object.isArray(this._oScenario.events) && this._oScenario.events.length == 0)
		{
			// No Events
			return;
		}
		
		// Find the first event (the one with no prerequisite)
		var oFirst	= this._findScenarioEventFromPrerequisite(null);
		if (!oFirst)
		{
			// Can't find first, theres is a problem with the scenario
			Reflex_Popup.alert('The scenario has not been setup correctly, a starting event cannot be found.', {sTitle: 'Error'});
			return;
		}
		
		// Build list of event data in order
		var aEventDataInOrder	= [oFirst];
		var oCurrent 			= oFirst;
		while (oCurrent = this._findScenarioEventFromPrerequisite(oCurrent.id))
		{
			aEventDataInOrder.push(oCurrent);
		}
		
		// Create nodes from ordered event data
		var oPrevious 	= this._aNodes[this._aNodes.length - 1];
		var oEventData	= null;
		for (var i = 0; i < aEventDataInOrder.length; i++)
		{
			oEventData	= 	aEventDataInOrder[i];
			oPrevious	=	this._createNode(
								Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT,
								oPrevious,
								oEventData.collection_event_id,
								oEventData.collection_event.name,
								oEventData.day_offset,
								this._getEventInvocation(oEventData)
							);
		}
	},
	
	_getEventInvocation : function(oEventData)
	{
		var oEvent 			= oEventData.collection_event;
		var oType 			= oEvent.collection_event_type;
		var oImplementation	= oEvent.collection_event_type.collection_event_type_implementation;
		if (oImplementation.enforced_collection_event_invocation_id)
		{
			// Enforced by implementation
			return oImplementation.enforced_collection_event_invocation_id;
		}
		else if (oType.collection_event_invocation_id)
		{
			// Enforced by type
			return oType.collection_event_invocation_id;
		}
		else if (oEventData.collection_event_invocation_id)
		{
			// Default to the scenario events invocation
			return oEventData.collection_event_invocation_id;
		}
		else if (oEvent.collection_event_invocation_id)
		{
			// Default to the events invocation
			return oEvent.collection_event_invocation_id;
		}
		else
		{
			// Configuration error
			Reflex_Popup.alert('The Event ' + oEventData.name + ' has not got an Invocation (manual/automatic).', {sTitle: 'Configuration Error'});
		}
	},
	
	_findScenarioEventFromPrerequisite : function(iPreqrequisiteScenarioEventId)
	{
		var oEvent = null;
		for (var iId in this._oScenario.events)
		{
			oEvent = this._oScenario.events[iId];
			if (oEvent.prerequisite_collection_scenario_collection_event_id == iPreqrequisiteScenarioEventId)
			{
				return oEvent;
			}
		}
		return null;
	},
	
	_createNode : function(iNodeType, oPrerequisiteNode, iEventId, sEventName, iDayOffset, iInvocationId)
	{
		iDayOffset	= (iDayOffset ? iDayOffset : 0);
		iEventId	= (iEventId ? iEventId : null);
		
		var oNode =	{
						iNodeType			: iNodeType,
						iEventId			: iEventId,
						sEventName			: sEventName,
						iDayOffset			: iDayOffset,
						iInvocationId		: iInvocationId,
						oPrerequisiteNode	: (oPrerequisiteNode ? oPrerequisiteNode : null),
						oContentDiv			: null
					};
		
		this._createNodeContent(oNode);
		this._updateNodeContent(oNode);
		
		var iNumberOfColumnsNeeded = iDayOffset - this._iStartDay;
		if (oPrerequisiteNode)
		{
			// Insert the new node one position after it's prerequisite
			var iIndex = this._aNodes.indexOf(oPrerequisiteNode) + 1;
			this._aNodes.splice(iIndex, 0, oNode);
			
			// Get the cumulative number of columns needed to represent the node
			iNumberOfColumnsNeeded = 0 - this._iStartDay;
			for (var i = 0; i <= iIndex; i++)
			{
				iNumberOfColumnsNeeded += this._aNodes[i].iDayOffset;
			}
		}
		else
		{
			// No prerequisite, add to the end
			this._aNodes.push(oNode);
		}
		
		// Extend the table if the number of columns needed is more than we currently have
		if (iNumberOfColumnsNeeded > this._iMaxNumberOfDays)
		{
			var iNewColumnStartDay	= this._iMaxNumberOfDays + 1;
			this._iMaxNumberOfDays 	= iNumberOfColumnsNeeded;
			
			// Add extra days, to the header row
			for (var iDay = iNewColumnStartDay; iDay <= this._iMaxNumberOfDays; iDay++)
			{
				this._oDayTR.appendChild(this._createDayCell(iDay));
			}
			
			// Add extra days, create a cell in each row
			for (var i = 0; i < this._oTBody.childNodes.length; i++)
			{
				var oTR = this._oTBody.childNodes[i];
				for (var iDay = iNewColumnStartDay; iDay <= this._iMaxNumberOfDays; iDay++)
				{
					oTR.appendChild(this._createNodeCell());
				}
			}
		}
		
		// Ensure there are enough rows in the table (there should be alteast for each node)
		while (this._aNodes.length > this._oTBody.childNodes.length)
		{
			var oNewTR = $T.tr();
			for (var iDay = this._iStartDay; iDay <= this._iMaxNumberOfDays; iDay++)
			{
				oNewTR.appendChild(this._createNodeCell());
			}
			this._oTBody.appendChild(oNewTR);
		}
		
		return oNode;
	},
	
	_refresh : function()
	{
		// Clear all cell content
		for (var iRow = 0; iRow < this._oTBody.childNodes.length; iRow++)
		{
			for (var iCell = 0; iCell < this._oTBody.childNodes[iRow].childNodes.length; iCell++)
			{
				this._clearCell(this._oTBody.childNodes[iRow].childNodes[iCell]);
			}
		}
		
		// Update all node cells
		var oNode 		= null;
		var oCell		= null;
		var iDay		= this._iStartDay;
		for (var i = 0; i < this._aNodes.length; i++)
		{
			oNode 	= this._aNodes[i];
			iDay	+= oNode.iDayOffset;
			oCell	= this._oTBody.childNodes[i].childNodes[iDay - this._iStartDay];
			this._setupCellAsNode(oCell, oNode, i);
		}
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_clearCell : function(oCell)
	{
		// Clear the cells content div and make 'empty'
		var oCellDiv 		= oCell.select('div').first();
		oCellDiv.innerHTML 	= '';
		oCellDiv.removeClassName('component-collections-scenario-event-timeline-node' + this._sClassRenderModeSuffix);
		oCellDiv.addClassName('component-collections-scenario-event-timeline-emptynode' + this._sClassRenderModeSuffix);
	},
	
	_setupCellAsNode : function(oCell, oNode, iPosition)
	{
		var oCellDiv = oCell.select('div').first();
		oCellDiv.addClassName('component-collections-scenario-event-timeline-node' + this._sClassRenderModeSuffix);
		oCellDiv.removeClassName('component-collections-scenario-event-timeline-emptynode' + this._sClassRenderModeSuffix);
		oCellDiv.appendChild(oNode.oContentDiv);
		
		if (this._bRenderMode)
		{
			// In edit mode, add the action icons (new, view/edit & remove)
			var oIconDiv = $T.div({class: 'component-collections-scenario-event-timeline-node-icons'});
			if (oNode.iNodeType == Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT)
			{
				// Only allowed to view & remove event nodes
				var oViewIcon = $T.img({src: '../admin/img/template/magnifier.png', alt: 'View/Edit Event', title: 'View/Edit Event'});
				oViewIcon.observe('click', this._viewNode.bind(this, iPosition));
				
				var oRemoveIcon = $T.img({src: '../admin/img/template/remove.png', alt: 'Remove Event', title: 'Remove Event'});
				oRemoveIcon.observe('click', this._removeNode.bind(this, iPosition));
				
				oIconDiv.appendChild(oViewIcon);
				oIconDiv.appendChild(oRemoveIcon);
			}
			
			var oNewIcon = $T.img({src: '../admin/img/template/new.png', alt: 'Insert New Event After', title: 'Insert New Event After'});
			oNewIcon.observe('click', this._newNode.bind(this, iPosition, null, null, null));
			oIconDiv.appendChild(oNewIcon);
			oCellDiv.appendChild(oIconDiv);
		}
	},
	
	_viewNode : function(iNodePosition)
	{
		var oNode = this._aNodes[iNodePosition];
		if (oNode)
		{
			if (oNode.iNodeType == Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT)
			{
				// Show the event edit popup
				new Popup_Collections_Scenario_Event_Timeline_Event(oNode.iEventId, oNode.iDayOffset, oNode.iInvocationId, this._updateNode.bind(this, iNodePosition));
			}
		}
	},
	
	_removeNode : function(iNodePosition)
	{
		if (this._aNodes[iNodePosition])
		{
			this._aNodes[iNodePosition].oContentDiv = null;
			this._aNodes.splice(iNodePosition, 1);
			this._refresh();
		}
	},
	
	_newNode : function(iNodePosition, oEvent, iDayOffset, iInvocationId)
	{
		var oNode = this._aNodes[iNodePosition];
		if (!oNode)
		{
			// Invalid 'before' position given
			return;
		}
		
		if ((oEvent === null) && (iDayOffset === null))
		{
			// Get day offset, event id & invocation id from popup
			new Popup_Collections_Scenario_Event_Timeline_Event(null, null, null, this._newNode.bind(this, iNodePosition));
			return;
		}
		
		this._createNode(Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT, oNode, oEvent.id, oEvent.name, iDayOffset, iInvocationId);
		this._refresh();
	},
	
	_updateNode : function(iNodePosition, oEvent, iDayOffset, iInvocationId)
	{
		var oNode = this._aNodes[iNodePosition];
		if (!oNode)
		{
			return;
		}
		
		oNode.iEventId 		= oEvent.id;
		oNode.sEventName	= oEvent.name;
		oNode.iDayOffset 	= iDayOffset;
		oNode.iInvocationId	= iInvocationId;
		
		this._updateNodeContent(oNode);
		this._refresh();
	},
	
	_createNodeContent : function(oNode)
	{
		switch (oNode.iNodeType)
		{
			case Component_Collections_Scenario_Event_Timeline.NODE_TYPE_DUE_DATE:
				oNode.oContentDiv = $T.div('Due Date');
				break;
				
			case Component_Collections_Scenario_Event_Timeline.NODE_TYPE_SCENARIO_START:
				oNode.oContentDiv =	$T.div({class: 'component-collections-scenario-event-timeline-node-scenario-start' + this._sClassRenderModeSuffix},
										'Start'
									);
				break;
				
			case Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT:
				oNode.oContentDiv = $T.div({class: 'component-collections-scenario-event-timeline-node-event' + this._sClassRenderModeSuffix},
										$T.img(), // src comes later (depends on invocation)
										$T.span({class: 'component-collections-scenario-event-timeline-event-name'})
									);
				
				if (this._bRenderMode)
				{
					oNode.oContentDiv.observe('mousedown', this._nodeDragStart.bind(this, oNode));
					document.body.observe('mouseup', this._nodeDragEnd.bind(this));
					document.body.observe('mousemove', this._nodeDrag.bind(this));
				}
				break;
		}
	},
	
	_nodeDragStart : function(oNode, oEvent)
	{
		if (!this._oDragNode)
		{
			this._oComponentPositionAtDragStart	= this._getComponentPosition();
			this._oDragNodeElement				= Element.clone(oNode.oContentDiv.up(), true);
			this._oDragNode 					= oNode;
			
			this._oDragNode.oContentDiv.up().addClassName('component-collections-scenario-event-timeline-node-event-indrag');
			this._oDragNodeElement.addClassName('drag-node');
			document.body.appendChild(this._oDragNodeElement);
			this._nodeDrag(oEvent);
		}
	},
	
	_nodeDrag : function(oEvent)
	{
		if (this._oDragNode)
		{
			// Position the drag element
			this._oDragNodeElement.style.left	= oEvent.clientX + 'px';
			this._oDragNodeElement.style.top 	= oEvent.clientY + 'px';
			
			// Determin the table cell that the drag element is over top of
			var iXDiff 			= (oEvent.clientX - this._oComponentPositionAtDragStart.iX) + this._oElement.scrollLeft;
			var iYDiff 			= (oEvent.clientY - this._oComponentPositionAtDragStart.iY) + this._oElement.scrollTop;
			var iCellWidth 		= this._oTBody.firstChild.firstChild.offsetWidth;
			var iCellHeight		= this._oTBody.firstChild.firstChild.offsetHeight;
			this._iDragCell		= Math.floor(iXDiff / iCellWidth);
			this._iDragPosition	= Math.floor(iYDiff / iCellHeight);
			
			// Can't position more than 1 after the last node or before the first node
			if (this._iDragPosition > this._aNodes.length)
			{
				this._iDragPosition = this._aNodes.length;
			}
			else if (this._iDragPosition == 0)
			{
				this._iDragPosition = 1;
			}
			
			// Determine the cumulative day offset up until the node BEFORE the targeted one
			this._iDragCumulativeDayOffset = 0;
			for (var i = 0; i < this._iDragPosition; i++)
			{
				this._iDragCumulativeDayOffset += this._aNodes[i].iDayOffset;
			}
			
			// Cannot drag to a day before the previous node
			if (this._iDragCell < this._iDragCumulativeDayOffset)
			{
				this._iDragCell = this._iDragCumulativeDayOffset;
			}
			
			// De-style the current target cell
			if (this._oDragTargetCell)
			{
				this._oDragTargetCell.removeClassName('component-collectons-scenario-event-timeline-drag-target');
			}
			
			if ((this._iDragPosition >= 0) && (this._iDragPosition < this._oTBody.childNodes.length))
			{
				// We have a new drag position, find the cell
				var oRow = this._oTBody.childNodes[this._iDragPosition]
				if ((this._iDragCell >= 0) && (this._iDragCell < oRow.childNodes.length))
				{
					// Found the cell
					var oTargetCell = oRow.childNodes[this._iDragCell];
					if (oTargetCell)
					{
						// Style it
						oTargetCell.addClassName('component-collectons-scenario-event-timeline-drag-target');
						this._oDragTargetCell = oTargetCell;
					}
				}
			}
		}
	},
	
	_nodeDragEnd : function(oEvent)
	{
		if (this._oDragNode)
		{
			var iCurrentPosition = this._aNodes.indexOf(this._oDragNode);
			
			// Determine the new day offset of the drag node 
			this._oDragNode.iDayOffset 	= this._iDragCell - this._iDragCumulativeDayOffset;
			if (this._iDragPosition != iCurrentPosition)
			{
				if (this._iDragPosition == 0)
				{
					// Insert after (can't go before the first node)
					this._aNodes.splice(iCurrentPosition, 1);
					this._aNodes.splice(this._iDragPosition + 1, 0, this._oDragNode);
				}
				else
				{
					// Insert before (all other nodes)
					this._aNodes.splice(iCurrentPosition, 1);
					this._aNodes.splice(this._iDragPosition, 0, this._oDragNode);
				}
			}
			
			// Tidy up the drag state
			this._oDragNodeElement.remove();
			this._oDragNodeElement.removeClassName('drag-node');
			this._oDragNode.oContentDiv.up().removeClassName('component-collections-scenario-event-timeline-node-event-indrag');
			if (this._oDragTargetCell)
			{
				this._oDragTargetCell.removeClassName('component-collectons-scenario-event-timeline-drag-target');
			}
			this._oDragTargetCell	= null;
			this._oDragNodeElement 	= null;
			this._oDragNode			= null;
			
			// Refresh the timeline
			this._refresh();
		}
	},
			
	_updateNodeContent : function(oNode)
	{
		switch (oNode.iNodeType)
		{
			case Component_Collections_Scenario_Event_Timeline.NODE_TYPE_EVENT:
				var sIconSrc = null;
				switch (oNode.iInvocationId)
				{
					case $CONSTANT.COLLECTION_EVENT_INVOCATION_MANUAL:
						sIconSrc = Component_Collections_Scenario_Event_Timeline.INVOCATION_MANUAL_IMAGE_SOURCE;
						break;
						
					case $CONSTANT.COLLECTION_EVENT_INVOCATION_AUTOMATIC:
						sIconSrc = Component_Collections_Scenario_Event_Timeline.INVOCATION_AUTOMATIC_IMAGE_SOURCE;
						break;
				}
				
				var sAlt 	= Flex.Constant.arrConstantGroups.collection_event_invocation[oNode.iInvocationId].Name;
				var oIcon	= oNode.oContentDiv.select('img').first();
				oIcon.src	= sIconSrc; 
				oIcon.alt	= sAlt;
				oIcon.title	= sAlt;
				
				var oNameSpan 		= oNode.oContentDiv.select('.component-collections-scenario-event-timeline-event-name').first(); 
				oNameSpan.innerHTML	= oNode.sEventName;
				break;
		}
	},
	
	_createDayCell : function(iDay)
	{
		var oTD = $T.td(iDay);
		if (iDay === 0)
		{
			oTD.appendChild($T.span(' (Due Date)'));
		}
		return oTD;
	},

	_createNodeCell : function()
	{
		return $T.td($T.div());
	},
	
	_getComponentPosition : function()
	{
		var oElement 	= this._oElement;
		var iPositionY	= 0;
		var iPositionX	= 0;
		do 
		{
			iPositionY	+= oElement.offsetTop || 0;
			iPositionX 	+= oElement.offsetLeft || 0;
			oElement	= oElement.offsetParent;
		} 
		while (oElement);
		return {iX: iPositionX, iY: iPositionY};
	}
});

// Static

Object.extend(Component_Collections_Scenario_Event_Timeline, 
{
	REQUIRED_CONSTANT_GROUPS : ['collection_event_invocation'],
	
	NODE_TYPE_DUE_DATE			: 1,
	NODE_TYPE_SCENARIO_START	: 2,
	NODE_TYPE_EVENT				: 3,
	
	INVOCATION_MANUAL_IMAGE_SOURCE		: '../admin/img/template/collection_event_invocation_manual.png',
	INVOCATION_AUTOMATIC_IMAGE_SOURCE	: '../admin/img/template/collection_event_invocation_automatic.png',
	
	_ajaxError : function(oResponse, sMessage)
	{
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error'}
		);
	}
});