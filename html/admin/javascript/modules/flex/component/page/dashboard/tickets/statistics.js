
var H			= require('fw/dom/factory'), // HTML
	S			= H.S, // SVG
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	XHRRequest	= require('fw/xhrrequest');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-dashboard-tickets-statistics');
	},


	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		var iViewboxWidth	= self.GRAPH_WIDTH+self.GRAPH_MARGIN_LEFT+self.GRAPH_MARGIN_RIGHT,
			iViewboxHeight	= self.GRAPH_HEIGHT+self.GRAPH_MARGIN_TOP+self.GRAPH_MARGIN_BOTTOM;
		this.NODE = H.section(
			H.header('Ticket Statistics'),
			H.h2('Tickets per Status'),
			this._oSvg = S.svg({'viewBox':'0 0 '+iViewboxWidth+' '+iViewboxHeight},
				S.rect({'class':'flex-page-dashboard-tickets-statistics-graph','x':0,'y':0,'width':iViewboxWidth,'height':iViewboxHeight}),
				S.rect({'class':'flex-page-dashboard-tickets-statistics-grid','x':self.GRAPH_MARGIN_LEFT,'y':self.GRAPH_MARGIN_TOP,'width':self.GRAPH_WIDTH,'height':self.GRAPH_HEIGHT}),
				// Graph Lines
				this._getGraphGrid()
			)
		);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {
			if (!this._bInitialised) {
				// First call.
				this._getData(this._getDataDates(), function(oData){
						this._renderGraph(oData);
					}.bind(this)
				);
			} else {
				// Every other call
			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
		}
	},
	_renderGraph : function(oData) {
		try {
			// Get Max Values
			var iHighestDataValue = this._getHighestDataValue(oData);
			var iMaximumYAxesValue = self._numberRoundUp(iHighestDataValue);

			// Get Label configuration for each Axes.
			var oYAxesLabelConfig = this._getYAxesLabelConfig(iHighestDataValue, iMaximumYAxesValue);
			var oXAxesLabelConfig = this._getXAxesLabelConfig(oData);
			
			// Create labels.
			this._createXAxesLabels(oXAxesLabelConfig);
			this._createYAxesLabels(oYAxesLabelConfig);

			// Graph is setup and ready for use.


			// Draw Lines, Dots and whatever other marks you want here:
			// Create a Polygon.
			var sClosedTicketAreaPoints = this._getGraphPointsForClosedTicketsArea(oData, iMaximumYAxesValue);
			var sClosedTicketPoints = this._getGraphPointsForClosedTickets(oData, iMaximumYAxesValue);
			this._oSvg.appendChild(this._getPolygonForPoints(sClosedTicketAreaPoints, 'flex-page-dashboard-tickets-statistics-tickets-closed'));
			this._oSvg.appendChild(this._getPolylineForPoints(sClosedTicketPoints, 'flex-page-dashboard-tickets-statistics-tickets-closed'));

			// Create some Polylines
			var aStatusData	= this._getPreparedStatusData(oData);
			var aPoints		= this._getGraphPointsForTicketStatuses(aStatusData, iMaximumYAxesValue);
			this._oSvg.appendChild(this._getPolylineForPoints(aPoints));
			// Create a Legend
			this._setLegendItemForClosedTickets(oData);
			this._setLegendItemsForStatuses(oData);
			this._createLegend();


			// Create the polylines on the X/Y Axes
			this._createAxesLines();
		} catch (oException) {
			// Fail
			this._handleException(oException);
		}
	},

	_handleException : function(oException) {
		if (oException && oException.message) {
			console.log('An exception has occurred with the message: "' + oException.message + '"');
			console.log('Exception: "' + oException + '"');
		} else {
			console.log('An unknown error has occurred.');
		}
	},

	// ----------------------------------------------------------------------------------- //
	// Legend
	// ----------------------------------------------------------------------------------- //
	_createLegend : function() {
		// Get items
		var aItems = this._getLegendItems();
		// Create them.
		var oLegend = H.$fragment();
		//var iLegendTitleSpacing = Math.round(self.GRAPH_HEIGHT/aItems.length);
		var iLegendTitleSpacing = self.GRAPH_LEGEND_ITEM_SPACING;
		//var iLegendPositionTop = self.GRAPH_MARGIN_TOP+(Math.round(iLegendTitleSpacing/2));
		var iLegendPositionTop = self.GRAPH_MARGIN_TOP+self.GRAPH_LEGEND_MARGIN_TOP;

		for (var i=0; i<aItems.length; i++) {
			var oItem = aItems[i];
			switch(oItem.sIdentifierType) {
				case "line":
					var oLegendItemIdentifier = S.line({
						'x1'			: (self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH)+5, // HACK HACK HACK FIXME
						'x2'			: (self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH)+10, // HACK HACK HACK FIXME
						'y1'			: iLegendPositionTop,
						'y2'			: iLegendPositionTop
					});
				break;
				case "rect":
					var oLegendItemIdentifier = S.rect({
						'x'			: (self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH)+5, // HACK HACK HACK FIXME
						'y'			: iLegendPositionTop-2.5, // HACK HACK HACK FIXME
						'width'		: 5,
						'height'	: 5
					});
				break;
				default:
					var oLegendItemIdentifier = H.$fragment();
				break;
			}
			oLegend.appendChild(S.g({'class':oItem.sClassName},
				S.text({
					'x'				: (self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH)+15, // HACK HACK HACK FIXME
					'y'				: iLegendPositionTop+2 // HACK HACK HACK FIXME
				}, oItem.sName),
				oLegendItemIdentifier
			));
			iLegendPositionTop = iLegendPositionTop+iLegendTitleSpacing;
		}
		this._oSvg.appendChild(oLegend);
	},
	_getLegendItems : function() {
		if(this._aLegendItems) {
			return this._aLegendItems;
		} else {
			this._aLegendItems = [];
			return this._aLegendItems;
		}
	},
	_setLegendItemForClosedTickets : function(oData) {
		var aLegendItems = this._getLegendItems();
		var oLegendItem = {
			'sIdentifierType'	: 'rect',
			'sName'				: 'Closed on Day',
			'sClassName'		: 'flex-page-dashboard-tickets-statistics-legend-tickets-closed'
		};
		// Return
		aLegendItems.push(oLegendItem);
		return aLegendItems;
	},
	_setLegendItemsForStatuses : function(oData) {
		var aLegendItems = this._getLegendItems();
		var oTicketData	= oData[0];			
		for (var x=0; x<oTicketData.oStatusesAtRangeEnd.length; x++) {
			var oTicketStatusData	= oTicketData.oStatusesAtRangeEnd[x];
			var oLegendItem = {
				'sIdentifierType'	: 'line',
				'sName'				: oTicketStatusData.status_name,
				'sClassName'		: this._getLegendClassNameForTicketStatusConstant(oTicketStatusData.status_constant)
			};
			aLegendItems.push(oLegendItem);
		}
		return aLegendItems;
	},


	// ----------------------------------------------------------------------------------- //
	// ....
	// ----------------------------------------------------------------------------------- //
	_createAxesLines : function() {
		var sXAxesPoints = self.GRAPH_MARGIN_LEFT + ',' + self.GRAPH_MARGIN_TOP + ' ' + self.GRAPH_MARGIN_LEFT + ',' + (self.GRAPH_MARGIN_TOP+self.GRAPH_HEIGHT);
		var sYAxesPoints = self.GRAPH_MARGIN_LEFT + ',' + (self.GRAPH_MARGIN_TOP+self.GRAPH_HEIGHT) + ' ' + (self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH) + ',' + (self.GRAPH_MARGIN_TOP+self.GRAPH_HEIGHT);
		this._oSvg.appendChild(this._getPolylineForPoints(sXAxesPoints, 'flex-page-dashboard-tickets-statistics-graph-x-axes'));
		this._oSvg.appendChild(this._getPolylineForPoints(sYAxesPoints, 'flex-page-dashboard-tickets-statistics-graph-y-axes'));
	},

	_getGraphPointsForClosedTickets : function(oData, iMaximumYAxesValue) {
		var iPositionX		= 0;
		var sPoints			= '';
		for (var iId = 0; iId<oData.length; iId++) {
			iPositionX		= iPositionX + ((Math.round(self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH))/(self.GRAPH_COUNT_X));
			var oTicketData	= oData[iId];
			var iPercent	= 100-(Math.round(oTicketData.iTicketsClosedInRange/iMaximumYAxesValue * 100));
			var iPositionY	= iPercent+self.GRAPH_MARGIN_TOP;
			sPoints			= sPoints+' '+iPositionX+','+iPositionY;
		}
		return sPoints;
	},
	_getGraphPointsForClosedTicketsArea : function(oData, iMaximumYAxesValue) {
		var iPositionX		= 0;
		var sPoints			= '';
		for (var iId = 0; iId<oData.length; iId++) {
			iPositionX		= iPositionX + ((Math.round(self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH))/(self.GRAPH_COUNT_X));
			var oTicketData	= oData[iId];
			var iPercent	= 100-(Math.round(oTicketData.iTicketsClosedInRange/iMaximumYAxesValue * 100));
			var iPositionY	= iPercent+self.GRAPH_MARGIN_TOP;
			sPoints			= sPoints+' '+iPositionX+','+iPositionY;
		}
		sPoints			= sPoints+' '+iPositionX+','+(100+self.GRAPH_MARGIN_TOP);
		sPoints			= sPoints+' '+self.GRAPH_MARGIN_LEFT+','+(100+self.GRAPH_MARGIN_TOP);
		return sPoints;
	},
	_getGraphPointsForTicketStatuses : function(oData, iMaximumYAxesValue) {
		// Each loop represents a possible Polyline.
		var aPoints = [];
		for (var i=0; i<oData.length; i++) {

			var sPoints		= '';
			var oTicketData	= oData[i];
			var iPositionX	= 0;
			
			for (var x=0; x<oTicketData.aStatus.length; x++) {
				var oTicketStatusData	= oTicketData.aStatus[x];
				iPositionX				= iPositionX + ((Math.round(self.GRAPH_MARGIN_LEFT+self.GRAPH_WIDTH))/(self.GRAPH_COUNT_X));
				var iPercent			= 100-(Math.round(oTicketStatusData.iCount/iMaximumYAxesValue * 100));
				var iPositionY			= iPercent+self.GRAPH_MARGIN_TOP;
				sPoints					= sPoints+' '+iPositionX+','+iPositionY;
			}
			var sClassName = this._getPolylineClassNameForTicketStatusConstant(oTicketData.sName);
			aPoints.push({
				'sPoints'		: sPoints,
				'sClassName'	: sClassName
			});
		}
		return aPoints;
	},

	_getPolylineClassNameForTicketStatusConstant : function(sConstant) {
		var sClassName = self.oStatusConstantClassNames[sConstant].polyline;
		return sClassName;
	},

	_getLegendClassNameForTicketStatusConstant : function(sConstant) {
		var sClassName = self.oStatusConstantClassNames[sConstant].legend;
		return sClassName;
	},


	// ----------------------------------------------------------------------------------- //
	// Prepare status data.
	// ----------------------------------------------------------------------------------- //
	_getPreparedStatusData : function(oData) {
		var aStatusData = [];
		// Dates, 16, 17, 18
		for (var iDataId = 0; iDataId<oData.length; iDataId++) {
			var sRangeStart			= oData[iDataId].sRangeStart;
			// Statuses on this date
			for (var iId = 0; iId<oData[iDataId].oStatusesAtRangeEnd.length; iId++) {
				var oStatusesAtRangeEnd		= oData[iDataId].oStatusesAtRangeEnd[iId];
				var iStatusId				= oStatusesAtRangeEnd.status_id;
				var iStatusCount			= oStatusesAtRangeEnd.status_count;
				var sStatusConstant			= oStatusesAtRangeEnd.status_constant;
				// Add data to existing status array...
				var bStatusFound = false;
				for (var i=0; i<aStatusData.length; i++) {
					if (aStatusData[i].sName === sStatusConstant) {
						// Modify
						aStatusData[i].aStatus.push({
							'iCount'	: iStatusCount,
							'sDate'		: sRangeStart
						});
						var bStatusFound = true;
						break
					}
				}
				// Else create a new one.
				if (bStatusFound === false) {
					// Push new
					aStatusData.push({
						'sName' : sStatusConstant,
						'aStatus' : [{
							'iCount'	: iStatusCount,
							'sDate'		: sRangeStart
						}]
					});
				}
			}
		}
		return aStatusData;
	},



	// ----------------------------------------------------------------------------------- //
	// SVG
	// ----------------------------------------------------------------------------------- //
	_getPolygonForPoints : function(sPoints, sClassName) {
		return S.polygon({'class':sClassName, 'points':sPoints});
	},
	_getPolylineForPoints : function(mPoints, sClassName) {
		if (typeof mPoints === 'object') {
			var oPolyline = H.$fragment();
			for (var i=0; i<mPoints.length; i++) {
				oPolyline.appendChild(S.polyline({'class':mPoints[i].sClassName, 'points':mPoints[i].sPoints}));
			}
		} else {
			var oPolyline = S.polyline({'class':sClassName,points:mPoints});
		}
		return oPolyline;
	},
	

	// ----------------------------------------------------------------------------------- //
	// Labels
	// ----------------------------------------------------------------------------------- //
	_getYAxesLabelConfig : function(iHighestDataValue, iMaximumYAxesValue) {
		// Y Axes
		var oLabelConfig = {
			aLabels			: [],
			iRotate			: 0
		};
		var iLabelIncrement = iMaximumYAxesValue/self.GRAPH_COUNT_Y;
		var iCount = 0;
		for (var iId = 0; iId<self.GRAPH_COUNT_Y+1; iId++) {
			oLabelConfig.aLabels.push({
				'sLabel'	: Math.round(iCount)
			});
			iCount = iCount+iLabelIncrement;
		}
		oLabelConfig.aLabels = oLabelConfig.aLabels.reverse();
		return oLabelConfig;
	},
	_getXAxesLabelConfig : function(oData) {
		// X Axes
		var oLabelConfig = {
			aLabels			: [],
			iRotate			: -90
		};
		for (var iId = 0; iId<self.GRAPH_COUNT_X; iId++) {
			var oTicketData = oData[iId];
			var oDate = Date.$parseDate(oTicketData.sRangeStart, 'Y-m-d h:i:s');
			oLabelConfig.aLabels.push({
				'sLabel'	: oDate.$format('D')
			});
		}
		return oLabelConfig;
	},
	_createYAxesLabels : function(oLabelConfig) {
		var iPositionX		= 2,
			iPositionY		= self.GRAPH_MARGIN_TOP+self.GRAPH_LABEL_INDENT,
			iIncrementY		= (self.GRAPH_HEIGHT/self.GRAPH_COUNT_Y);

		for (var iLabelId=0; iLabelId<self.GRAPH_COUNT_Y; iLabelId++) {
			var oLabel = oLabelConfig.aLabels[iLabelId];
			var oText = S.text({'class':'flex-page-dashboard-tickets-statistics-label', 'y':iPositionY, 'x':iPositionX, 'transform':'rotate('+oLabelConfig.iRotate+')'}, oLabel.sLabel);
			this._oSvg.appendChild(oText);
			// Set new position for next item.
			iPositionY = iPositionY+iIncrementY;
		}
	},
	_createXAxesLabels : function(oLabelConfig) {
		var iPositionX		= 2,
			iPositionY		= 0,
			iIncrementY		= self.GRAPH_WIDTH/(self.GRAPH_COUNT_X - 1);
		for (var iLabelId=0; iLabelId<self.GRAPH_COUNT_X; iLabelId++) {

			var oLabel = oLabelConfig.aLabels[iLabelId];
			var oText = S.text({'class':'flex-page-dashboard-tickets-statistics-label', 'y':(iPositionY+self.GRAPH_LABEL_INDENT), 'x':iPositionX, 'transform':'rotate('+oLabelConfig.iRotate+')'}, oLabel.sLabel);
			this._oGroup = S.g({'transform':'translate('+self.GRAPH_MARGIN_LEFT+','+(self.GRAPH_HEIGHT+self.GRAPH_MARGIN_TOP+self.GRAPH_MARGIN_BOTTOM)+')'});

			this._oSvg.appendChild(this._oGroup);
			this._oGroup.appendChild(oText);
			// Set new position for next item.
			iPositionY = iPositionY+iIncrementY;
		}
	},


	
	// ----------------------------------------------------------------------------------- //
	// Grid
	// ----------------------------------------------------------------------------------- //
	_getGraphGrid : function() {
		var oFragment = H.$fragment();
		// Lines Y
		var iLineSpacing		= self.GRAPH_HEIGHT / self.GRAPH_COUNT_Y, // The space between each line.
			iPositionX			= self.GRAPH_MARGIN_LEFT,
			iCurrentPositionY	= self.GRAPH_MARGIN_TOP;
		for (var iLine=0; iLine<self.GRAPH_COUNT_Y+1; iLine++) {
			oFragment.appendChild(S.polyline({'class':'flex-page-dashboard-tickets-statistics-grid-line-y', points:iPositionX+','+iCurrentPositionY+' '+(self.GRAPH_WIDTH+iPositionX)+','+iCurrentPositionY}));
			iCurrentPositionY	= iCurrentPositionY+iLineSpacing;
		}
		// Lines X
		var iLineSpacing		= self.GRAPH_WIDTH / (self.GRAPH_COUNT_X -1), // The space between each line.
			iPositionY			= self.GRAPH_MARGIN_TOP,
			iCurrentPositionX	= self.GRAPH_MARGIN_LEFT;
		for (var iLine=0; iLine<self.GRAPH_COUNT_X; iLine++) {
			oFragment.appendChild(S.polyline({'class':'flex-page-dashboard-tickets-statistics-grid-line-x', points:iCurrentPositionX+','+iPositionY+' '+iCurrentPositionX+','+(self.GRAPH_HEIGHT+iPositionY)}));
			iCurrentPositionX	= iCurrentPositionX+iLineSpacing;
		}
		return oFragment;
	},


	
	// ----------------------------------------------------------------------------------- //
	// Data
	// ----------------------------------------------------------------------------------- //
	_getHighestDataValue : function(oData) {
		// Current value
		var iHighestDataValue = 0;
		for (var iId = 0; iId<oData.length; iId++) {
			var oTicketData = oData[iId];
			// Test: Closed Tickets
			iHighestDataValue = (oTicketData.iTicketsClosedInRange > iHighestDataValue) ? oTicketData.iTicketsClosedInRange : iHighestDataValue;
			// Test: StatusRange values
			for (var iStatusId = 0; iStatusId<oTicketData.oStatusesAtRangeEnd.length; iStatusId++) {
				var oStatusesAtRangeEnd = oTicketData.oStatusesAtRangeEnd[iStatusId];
				iHighestDataValue = (oStatusesAtRangeEnd.status_count > iHighestDataValue) ? oStatusesAtRangeEnd.status_count : iHighestDataValue;
			}
		}
		return iHighestDataValue;
	},

	_getDataDates : function() {
		var aDates = [],
		oDate = new Date(),
		sCurrentDay, sPreviousDay, oDates,
		i, l;
		/*oDate.setDate(oDate.getDate()-183); // DEBUG */
		for (i = 0, l = self.GRAPH_COUNT_X; i < l; i++) {
			//sCurrentDay = oDate.$format('Y-m-d H:i:s');
			sCurrentDay = oDate.$format('Y-m-d');
			oDate.setDate(oDate.getDate() - 1);
			//sPreviousDay = oDate.$format('Y-m-d H:i:s');
			sPreviousDay = oDate.$format('Y-m-d');
			aDates.push({
				sDateFrom: sPreviousDay + ' 00:00:00',
				sDateTo: sCurrentDay + ' 00:00:00'
			});
		}
		return aDates.reverse();
	},

	_getData : function(aDates, fnCallback, oXHREvent) {
		new Ajax.Request('/admin/reflex_json.php/Ticketing/getTicketStatsForDates', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([aDates])),
			onSuccess: function (oResponse){
				var oData = JSON.parse(oResponse.responseText);
				if (fnCallback) {
					// Once the data is retrieved from the server, run the callback function to render the data.
					fnCallback(oData);
				} else {
					return (oData) ? oData : null;
				}
			}.bind(this)
		});
	},

	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {

		GRAPH_WIDTH : 140,
		GRAPH_HEIGHT : 100,

		GRAPH_MARGIN_TOP : 10,
		GRAPH_MARGIN_RIGHT : 65,
		GRAPH_MARGIN_BOTTOM : 20,
		GRAPH_MARGIN_LEFT : 20,
		
		GRAPH_COUNT_X : 8, // Days
		GRAPH_COUNT_Y : 4, // Values

		GRAPH_LABEL_INDENT : 2,
		GRAPH_LEGEND_ITEM_SPACING : 8,
		GRAPH_LEGEND_MARGIN_TOP : 5,

		oStatusConstantClassNames : {
			'TICKETING_STATUS_ASSIGNED'			: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-assigned',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-assigned'
			},
			'TICKETING_STATUS_COMPLETED'		: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-completed',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-completed'
			},
			'TICKETING_STATUS_IN_PROGRESS'		: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-inprogress',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-inprogress'
			},
			'TICKETING_STATUS_UNASSIGNED'		: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-unassigned',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-unassigned'
			},
			'TICKETING_STATUS_WITH_CARRIER'		: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-withcarrier',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-withcarrier'
			},
			'TICKETING_STATUS_WITH_CUSTOMER'	: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-withcustomer',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-withcustomer'
			},
			'TICKETING_STATUS_WITH_INTERNAL'	: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-withinternal',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-withinternal'
			},
			'TICKETING_STATUS_DELETED'			: {
				'polyline'	: 'flex-page-dashboard-tickets-statistics-ticket-status-deleted',
				'legend'	: 'flex-page-dashboard-tickets-statistics-legend-ticket-status-deleted'
			}
		},

		_numberRoundUp : function(iNumber) {
			return Math.ceil((iNumber + 1) / Math.max(10, Math.pow(10, ('' + iNumber).length - 2))) * Math.max(10, Math.pow(10, ('' + iNumber).length - 2));
		}

	}

});

return self;

