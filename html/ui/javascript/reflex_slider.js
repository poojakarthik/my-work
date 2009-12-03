var Reflex_Slider	= Class.create
({
	initialize	: function(iMinValue, iMaxValue, sSelectMode)
	{
		// DOM Elements
		this.oContainer	= {domElement: document.createElement('div')};
		this.oContainer.domElement.addClassName('reflex-slider');
		this.oContainer.domElement.oReflexSlider	= this;
		
		this.oContainer.oRail	= {domElement: document.createElement('div')};
		this.oContainer.oRail.domElement.addClassName('reflex-slider-rail');
		this.oContainer.domElement.appendChild(this.oContainer.oRail.domElement);
		this.oContainer.oRail.onClick	= function(){};
		
		this.oHandles	= {};
		/*
		this.oContainer.oRail.oHandleRange						= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleRange.sName				= 'handle-range';
		this.oContainer.oRail.oHandleRange.domElement.addClassName('reflex-slider-rail-range');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleRange.domElement);
		this.oContainer.oRail.oHandleRange.domElement.observe('mousedown');
		
		this.oContainer.oRail.oHandleStart						= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleStart.sName				= 'handle-start';
		this.oContainer.oRail.oHandleStart.domElement.addClassName('reflex-slider-rail-handle');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleStart.domElement);
		this.oContainer.oRail.oHandleStart.onMouseDown			= this._onMouseDown.bindAsEventListener(this, this.oContainer.oRail.oHandleStart);
		this.oContainer.oRail.oHandleStart.onMouseUp			= this._onMouseUp.bindAsEventListener(this, this.oContainer.oRail.oHandleStart);
		this.oContainer.oRail.oHandleStart.onDrag				= this._onDrag.bindAsEventListener(this, this.oContainer.oRail.oHandleStart);
		this.oContainer.oRail.oHandleStart.oSlideFX				= null;
		
		this.oContainer.oRail.oHandleEnd						= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleEnd.sName					= 'handle-end';
		this.oContainer.oRail.oHandleEnd.domElement.addClassName('reflex-slider-rail-handle');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleEnd.domElement);
		this.oContainer.oRail.oHandleEnd.onMouseDown			= this._onMouseDown.bindAsEventListener(this, this.oContainer.oRail.oHandleEnd);
		this.oContainer.oRail.oHandleEnd.onMouseUp				= this._onMouseUp.bindAsEventListener(this, this.oContainer.oRail.oHandleEnd);
		this.oContainer.oRail.oHandleEnd.onDrag					= this._onDrag.bindAsEventListener(this, this.oContainer.oRail.oHandleEnd);
		this.oContainer.oRail.oHandleEnd.oSlideFX				= null;
		
		this.oContainer.oRail.oHandleStart.domElement.observe('mousedown', this.oContainer.oRail.oHandleStart.onMouseDown);
		this.oContainer.oRail.oHandleEnd.domElement.observe('mousedown', this.oContainer.oRail.oHandleEnd.onMouseDown);
		*/
		
		// DEBUG
		this.domDebugConsole						= document.createElement('div');
		this.domDebugConsole.style.position			= 'fixed';
		this.domDebugConsole.style.bottom			= '0';
		this.domDebugConsole.style.height			= '10em';
		this.domDebugConsole.style.width			= '100%';
		this.domDebugConsole.style.minHeight		= '10em';
		this.domDebugConsole.style.maxHeight		= '10em';
		this.domDebugConsole.style.overflowX		= 'scroll';
		this.domDebugConsole.style.overflowY		= 'scroll';
		this.domDebugConsole.style.border			= '0.1em solid #000';
		this.domDebugConsole.style.backgroundColor	= '#fff';
		//document.body.appendChild(this.domDebugConsole);
		
		// Defaults
		this.oValues	=	{
								iStartValue	: 0,
								iEndValue	: 0
							};
		this.aLabels	= [];
		
		this.oMouseCoordinates	=	{
										oLast	:	{
														iX	: null,
														iY	: null
													},
										oNext	:	{
														iX	: null,
														iY	: null
													}
									};
		
		this.iStepping	= 1;
		
		// Config
		this.iMinValue	= parseInt(iMinValue);
		this.iMaxValue	= parseInt(iMaxValue);
		
		this.setValues(this.iMinValue, this.iMaxValue);
		this.setSelectMode(sSelectMode);
		
		this.iRefreshFramesPerSecond	= Reflex_Slider.DEFAULT_REFRESH_FRAMES_PER_SECOND;
		
		// Paint!
		this._paint();
	},
	
	getElement	: function()
	{
		return this.oContainer.domElement;
	},
	
	setMinValue	: function(iMinValue)
	{
		this.iMinValue	= parseInt(iMinValue);
		this.setValues(this.oValues.iStartValue, this.oValues.iEndValue);
		
		// Ensure that the labels are still valid
		this.setLabels(this.aLabels);
	},
	
	setMaxValue	: function(iMaxValue)
	{
		this.iMaxValue	= parseInt(iMaxValue);
		this.setValues(this.oValues.iStartValue, this.oValues.iEndValue);
		
		// Ensure that the labels are still valid
		this.setLabels(this.aLabels);
	},
	
	// Adds a new Handle
	addHandle	: function(sHandleName, iValue, fnCallback)
	{
		if (this.oHandles[sHandleName] === undefined)
		{
			// Add a new Handle
			var oHandle	=	{
								sName		: sHandleName,
								oElement	: document.createElement('div'),
								iValue		: iValue,
								oSlideFX	: null
							};
			oHandle.oElement.addClassName('reflex-slider-rail-handle');
			
			// Attach DOM Element
			this.oContainer.oRail.domElement.appendChild(oHandle.oElement);
			
			// Event Handlers
			oHandle.oEventHandlers	=	{
											onMouseDown	: this._onMouseDown.bindAsEventListener(this, oHandle),
											onMouseUp	: this._onMouseUp.bindAsEventListener(this, oHandle),
											onDrag		: this._onDrag.bindAsEventListener(this, oHandle),
										};
		}
		else
		{
			throw "A Handle with the name '"+sHandleName+"' already exists with the value: '"+this.oHandles[sHandleName].iValue+"'";
		}
	},
	
	// Removes a Handle by it's name/alias
	removeHandle	: function(sHandleName)
	{
		if (this.oHandles[sHandleName] !== undefined)
		{
			// Remove the Handle
			this.oContainer.oRail.domElement.removeChild(oHandle.oElement);
			delete this.oHandles[sHandleName];
			return true;
		}
		return false;
	},
	
	// Returns the values of all Handles by their
	getValues	: function()
	{
		var oValues	= {};
		for (sHandle in this.oHandles)
		{
			oValues[sHandle]	= this.oHandles[sHandle].iValue;
		}
		return oValues;
	},
	
	setValues	: function(oValues)
	{
		for (sHandleName in oValues)
		{
			if (this.oHandles[sHandleName])
			{
				this.oHandles[sHandleName].iValue	= this._snapValue(parseInt(oValues[sHandleName]));
			}
		}
		
		// Paint
		this._paint();
		
		// User Callback
		if (this.fnSetValueCallback)
		{
			this.fnSetValueCallback(this.getValues());
		}
		
		//$Alert("Values set to: [iStartValue: " + this.oValues.iStartValue + ", iEndValue: " + this.oValues.iEndValue + "]");
		//this.domDebugConsole.innerHTML	+= "Values set to: [iStartValue: " + this.oValues.iStartValue + ", iEndValue: " + this.oValues.iEndValue + "]<br />\n";
	},
	
	_correctValues	: function()
	{
		for (sHandleName in this.oHandles)
		{
			this.oHandles[sHandleName].iValue	= this._snapValue(this.oHandles[sHandleName].iValue);
		}
	},
	
	_snapValue	: function(iValue)
	{
		//alert('iValue: '+iValue);
		// Ensure that it's within our boundaries
		var iMinValue	= this.bMinAlwaysSelectable ? this.iMinValue : Math.round(Math.ceil((this.iMinValue / this.iStepping)) * this.iStepping);
		var iMaxValue	= this.bMaxAlwaysSelectable ? this.iMaxValue : Math.round(Math.floor((this.iMaxValue / this.iStepping)) * this.iStepping);
		
		if (iValue == iMinValue)
		{
			// Snap to Min Value
			iValue	= iMinValue;
		}
		else if (iValue == iMaxValue)
		{
			// Snap to Max Value
			iValue	= iMaxValue;
		}
		else
		{
			// Snap to nearest stepping value
			iValue	= Math.round(Math.round((iValue / this.iStepping)) * this.iStepping);
		}
		
		//alert('Snapped iValue: '+String(Math.min(iMaxValue, Math.max(iMinValue, iValue))));
		
		return Math.min(iMaxValue, Math.max(iMinValue, iValue));
	},
	
	setStepping	: function(iStepping, bMinAlwaysSelectable, bMaxAlwaysSelectable)
	{
		this.bMinAlwaysSelectable	= (bMinAlwaysSelectable || bMinAlwaysSelectable === null || bMinAlwaysSelectable === undefined) ? true : false;
		this.bMaxAlwaysSelectable	= (bMaxAlwaysSelectable || bMaxAlwaysSelectable === null || bMaxAlwaysSelectable === undefined) ? true : false;
		
		this.iStepping	= Math.max(1, parseInt(iStepping));
		
		this._correctValues();
		this._paint();
	},
	
	setLabels	: function(aLabels)
	{
		// Remove existing Labels
		for (var i = 0, j = this.aLabels.length; i < j; i++)
		{
			this.oContainer.oRail.domElement.removeChild(this.aLabels[i].domElement);
		}
		
		// Add new Labels
		this.aLabels	= [];
		for (var i = 0, j = aLabels.length; i < j; i++)
		{
			// Add Element
			aLabels[i].domElement	= document.createElement('div');
			aLabels[i].domElement.addClassName('reflex-slider-rail-label');
			
			aLabels[i].domElement.style.left	= this._calculatePercentageFromValue(aLabels[i].iValue);
		}
		this.aLabels	= aLabels;
	},
	
	_calculatePercentageFromValue	: function(iValue)
	{
		return (iValue / (this.iMaxValue - this.iMinValue)) * 100;
	},
	
	_calculateValueFromMousePosition	: function(iX, iY)
	{
		var oCumulativeOffset	= this.oContainer.oRail.domElement.cumulativeOffset();
		var iDifference			= iX - oCumulativeOffset.left;
		
		var iValueRange	= this.iMaxValue - this.iMinValue;
		var iMultiplier	= iValueRange / this.oContainer.oRail.domElement.getWidth();
		var iValue		= (iDifference * iMultiplier) + this.iMinValue;
		
		//this.domDebugConsole.innerHTML	+= String(iValue) + " (x: " + iX + ", y: " + iY + ", ElementWidth: " + this.oContainer.oRail.domElement.getWidth() + ", CumulativeOffset: " + oCumulativeOffset.left + ", ValueRange: " + iValueRange + ", Multiplier: " + iMultiplier + ")<br />\n";
		
		return iValue;
	},
	
	_onRailClick	: function()	
	{
		// Snap the closest handle to this point
		// TODO
	},
	
	_onMouseDown	: function(oEvent, oHandle)
	{
		// Enable Dragging
		document.observe('mouseup', oHandle.onMouseUp);
		document.observe('mousemove', oHandle.onDrag);
		
		this.oDragRefreshPeriodicalExecuter	= new PeriodicalExecuter(this._dragRefresh.bind(this, oHandle), 1 / this.iRefreshFramesPerSecond);
		
		//this.domDebugConsole.innerHTML	+= oHandle.sName + ".mouseDown()<br />\n";
	},
	
	_onMouseUp		: function(oEvent, oHandle)
	{
		// Disable Dragging
		document.stopObserving('mouseup', oHandle.onMouseUp);
		document.stopObserving('mousemove', oHandle.onDrag);
		
		if (this.oDragRefreshPeriodicalExecuter)
		{
			this.oDragRefreshPeriodicalExecuter.stop();
			delete this.oDragRefreshPeriodicalExecuter;
		}
		
		//this.domDebugConsole.innerHTML	+= oHandle.sName + ".mouseUp()<br />\n";
	},
	
	_onDrag	: function(oEvent, oHandle)
	{
		// Update 'next' mouse coordinates
		var oMouseCoordinates			= oEvent.pointer();
		this.oMouseCoordinates.oNext.iX	= oMouseCoordinates.x;
		this.oMouseCoordinates.oNext.iY	= oMouseCoordinates.y;
	},
	
	// Refeshes the Slider value(s) at a configured FPS
	_dragRefresh	: function(oHandle)
	{
		//this.domDebugConsole.innerHTML	+= oHandle.sName + ".drag():";
		
		// Only update if the coordinates have changed
		if (this.oMouseCoordinates.oNext.iX != this.oMouseCoordinates.oLast.iX || this.oMouseCoordinates.oNext.iY != this.oMouseCoordinates.oLast.iY)
		{
			this.oMouseCoordinates.oLast.iX	= this.oMouseCoordinates.oNext.iX;
			this.oMouseCoordinates.oLast.iY	= this.oMouseCoordinates.oNext.iY;
			
			// Which Slider?
			var oValues	= {};
			oValues[oHandle.sName]	= this._calculateValueFromMousePosition(this.oMouseCoordinates.oNext.iX, this.oMouseCoordinates.oNext.iY);
			this.setValues(oValues);
		}
	},
	
	_paint	: function()
	{
		for (sHandleName in this.oHandles)
		{
			// Find Percentage Position
			var fPercentagePosition	= this._calculatePercentageFromValue(this.oHandles[sHandleName].iValue);
			
			// Update Element Style
			if (this.oHandles[sHandleName].oElement.style.left != String(fPercentagePosition)+'%')
			{
				this.oHandles[sHandleName].oElement.style.left = String(fPercentagePosition)+'%';
			}
		}
		
		//this.domDebugConsole.innerHTML	+= "Painting... [StartHandle: "+fStartPercentage+"%, EndHandle: "+fEndPercentage+"%, RangePosition: "+fStartPercentage+"%, RangeLength: "+(fEndPercentage - fStartPercentage)+"%]<br />\n";
	}
});

Reflex_Slider.SELECT_MODE_VALUE		= 'value';
Reflex_Slider.SELECT_MODE_RANGE		= 'range';
Reflex_Slider.SELECT_MODE_RANGE_MIN	= 'min';
Reflex_Slider.SELECT_MODE_RANGE_MAX	= 'max';

Reflex_Slider.DEFAULT_REFRESH_FRAMES_PER_SECOND	= 30;
