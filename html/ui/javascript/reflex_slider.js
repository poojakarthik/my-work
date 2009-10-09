Reflex_Slider	= Class.create
({
	initialize	: function(iMinValue, iMaxValue, sSelectMode)
	{
		// DOM Elements	
		this.oContainer	= {domElement: document.createElement('div')};
		this.oContainer.domElement.addClassName('reflex-slider');
		
		this.oContainer.oRail	= {domElement: document.createElement('div')};
		this.oContainer.oRail.domElement.addClassName('reflex-slider-rail');
		this.oContainer.domElement.appendChild(this.oContainer.oRail.domElement);
		
		this.oContainer.oRail.oHandleStart						= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleStart.sName				= 'handle-start';
		this.oContainer.oRail.oHandleStart.domElement.innerHTML	= '&nbsp;';
		this.oContainer.oRail.oHandleStart.domElement.addClassName('reflex-slider-rail-handle');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleStart.domElement);
		this.oContainer.oRail.oHandleStart.onMouseDown			= this._onMouseDown.bindAsEventListener(this, this.oContainer.oRail.oHandleStart);
		this.oContainer.oRail.oHandleStart.onMouseUp			= this._onMouseUp.bindAsEventListener(this, this.oContainer.oRail.oHandleStart);
		this.oContainer.oRail.oHandleStart.onDrag				= this._onDrag.bindAsEventListener(this, this.oContainer.oRail.oHandleStart);
		
		this.oContainer.oRail.oHandleRange						= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleRange.sName				= 'handle-range';
		this.oContainer.oRail.oHandleRange.domElement.innerHTML	= '&nbsp;';
		this.oContainer.oRail.oHandleRange.domElement.addClassName('reflex-slider-rail-range');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleRange.domElement);
		
		this.oContainer.oRail.oHandleEnd						= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleEnd.sName					= 'handle-end';
		this.oContainer.oRail.oHandleEnd.domElement.innerHTML	= '&nbsp;';
		this.oContainer.oRail.oHandleEnd.domElement.addClassName('reflex-slider-rail-handle');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleEnd.domElement);
		this.oContainer.oRail.oHandleEnd.onMouseDown			= this._onMouseDown.bindAsEventListener(this, this.oContainer.oRail.oHandleEnd);
		this.oContainer.oRail.oHandleEnd.onMouseUp				= this._onMouseUp.bindAsEventListener(this, this.oContainer.oRail.oHandleEnd);
		this.oContainer.oRail.oHandleEnd.onDrag					= this._onDrag.bindAsEventListener(this, this.oContainer.oRail.oHandleEnd);
		
		this.oContainer.oRail.oHandleStart.domElement.observe('mousedown', this.oContainer.oRail.oHandleStart.onMouseDown);
		this.oContainer.oRail.oHandleEnd.domElement.observe('mousedown', this.oContainer.oRail.oHandleEnd.onMouseDown);
		
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
		document.body.appendChild(this.domDebugConsole);
		
		// Defaults
		this.oValues	=	{
								iStartValue	: 0,
								iEndValue	: 0
							};
		this.aLabels	= [];

		this.setMinValue(iMinValue);
		this.setMaxValue(iMaxValue);
		this.setValues(iMinValue);
		this.setSelectMode(sSelectMode);
		this.setStepping(1);
		
		// Render!
		this._render();
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
	
	setSelectMode	: function(sSelectMode, iRangeMinimumDifference)
	{
		sSelectMode	= String(sSelectMode).toLowerCase();
		switch (sSelectMode)
		{
			case Reflex_Slider.SELECT_MODE_RANGE_MIN:
				this.oContainer.oRail.oHandleStart.domElement.hide();
				this.oContainer.oRail.oHandleEnd.domElement.show();
				this.oContainer.oRail.oHandleRange.domElement.show();
				break;
				
			case Reflex_Slider.SELECT_MODE_RANGE_MAX:
				this.oContainer.oRail.oHandleStart.domElement.show();
				this.oContainer.oRail.oHandleEnd.domElement.hide();
				this.oContainer.oRail.oHandleRange.domElement.show();
				break;
				
			case Reflex_Slider.SELECT_MODE_RANGE:
				this.oContainer.oRail.oHandleStart.domElement.show();
				this.oContainer.oRail.oHandleEnd.domElement.show();
				this.oContainer.oRail.oHandleRange.domElement.show();
				this.iRangeMinimumDifference	= Math.max(0, parseInt(iRangeMinimumDifference));
				break;
				
			case Reflex_Slider.SELECT_MODE_VALUE:
			default:
				this.oContainer.oRail.oHandleStart.domElement.show();
				this.oContainer.oRail.oHandleEnd.domElement.hide();
				this.oContainer.oRail.oHandleRange.domElement.hide();
				
				sSelectMode	= Reflex_Slider.SELECT_MODE_VALUE;
				break;
		}
		this.sSelectMode	= sSelectMode;
		
		// Update the Slider
		this._render();
	},
	
	getValues	: function()
	{
		return this.oValues;
	},
	
	setValues	: function(iStartValue, iEndValue)
	{
		iStartValue					= parseInt(iStartValue);
		iStartValue					= Math.round(iStartValue);
		this.oValues.iStartValue	= Math.min(this.iMaxValue, Math.max(this.iMinValue, iStartValue));
		
		iEndValue				= parseInt(iEndValue);
		iEndValue				= Math.round(iEndValue);
		this.oValues.iEndValue	= Math.min(this.iMaxValue, Math.max(this.iMinValue, iEndValue));
		
		// Handle any limits
		this._limitValues();
		
		// Render
		this._render();
		
		// User Callback
		if (this.fnSetValueCallback)
		{
			this.fnSetValueCallback(this.getValues());
		}
		
		//$Alert("Values set to: [iStartValue: " + this.oValues.iStartValue + ", iEndValue: " + this.oValues.iEndValue + "]");
		this.domDebugConsole.innerHTML	+= "Values set to: [iStartValue: " + this.oValues.iStartValue + ", iEndValue: " + this.oValues.iEndValue + "]<br />\n";
	},
	
	_limitValues	: function()
	{
		switch (this.sSelectMode)
		{
			case Reflex_Slider.SELECT_MODE_RANGE_MIN:
				this.oValues.iStartValue	= this.iMinValue;
				break;
				
			case Reflex_Slider.SELECT_MODE_RANGE_MAX:
				this.oValues.iEndValue		= this.iMaxValue;
				break;
				
			case Reflex_Slider.SELECT_MODE_RANGE:
				this.oValues.iEndValue		= Math.max(this.oValues.iStartValue, this.oValues.iEndValue);
				break;
				
			case Reflex_Slider.SELECT_MODE_VALUE:
				this.oValues.iEndValue		= this.oValues.iStartValue;
				break;
		}
	},
	
	setStepping	: function(iStepping)
	{
		this.iStepping	= Math.max(1, parseInt(iStepping));
		
		this._limitValues();
		this._render();
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
	
	setValueCallback	: function(fnCallback)
	{
		if (typeof fnCallback === 'function')
		{
			this.fnSetValueCallback	= fnCallback;
		}
		else
		{
			throw "fnCallback is not a Function!";
		}
	},
	
	_calculatePercentageFromValue	: function(iValue)
	{
		return (this.iMaxValue - this.iMinValue / 100) * (this.iMinValue - iValue);
	},
	
	_calculateValueFromMousePosition	: function(iX, iY)
	{
		var oCumulativeOffset	= this.oContainer.oRail.domElement.cumulativeOffset();
		var iDifference			= iX - oCumulativeOffset.left;
		
		var iValueRange	= this.iMaxValue - this.iMinValue;
		var iMultiplier	= iValueRange / this.oContainer.oRail.domElement.getWidth();
		var iValue		= (iDifference * iMultiplier) + this.iMinValue;
		
		this.domDebugConsole.innerHTML	+= String(iValue) + " (x: " + iX + ", y: " + iY + ", ElementWidth: " + this.oContainer.oRail.domElement.getWidth() + ", CumulativeOffset: " + oCumulativeOffset.left + ", ValueRange: " + iValueRange + ", Multiplier: " + iMultiplier + ")<br />\n";
		
		return iValue;
	},
	
	_onMouseDown	: function(oEvent, oHandle)
	{
		// Enable Dragging
		document.observe('mouseup', oHandle.onMouseUp);
		document.observe('mousemove', oHandle.onDrag);
		
		this.domDebugConsole.innerHTML	+= oHandle.sName + ".mouseDown()<br />\n";
	},
	
	_onMouseUp		: function(oEvent, oHandle)
	{
		// Disable Dragging
		document.stopObserving('mouseup', oHandle.onMouseUp);
		document.stopObserving('mousemove', oHandle.onDrag);
		
		this.domDebugConsole.innerHTML	+= oHandle.sName + ".mouseUp()<br />\n";
	},
	
	_onDrag	: function(oEvent, oHandle)
	{
		this.domDebugConsole.innerHTML	+= oHandle.sName + ".drag():";
		// Which Slider?
		if (oHandle === this.oContainer.oRail.oHandleStart)
		{
			// Update Slider position
			this.setValues(this._calculateValueFromMousePosition(oEvent.pointerX(), oEvent.pointerY()), this.oValues.iEndValue);
		}
		else if (oHandle === this.oContainer.oRail.oHandleEnd)
		{
			// Update Slider position
			this.setValues(this.oValues.iStartValue, this._calculateValueFromMousePosition(oEvent.pointerX(), oEvent.pointerY()));
		}
		else
		{
			// Neither?  WTF?
			throw "_onDrag() has been passed an Event whose Element is neither the Start nor End Handle!";
		}
	},
	
	_render	: function()
	{
		// Find Percentage Positions
		var iValueRange	= this.iMaxValue - this.iMinValue;
		
		var fStartPercentage	= this._calculatePercentageFromValue(this.oValues.iStartValue);
		var fEndPercentage		= this._calculatePercentageFromValue(this.oValues.iEndValue);
		
		// Update Handles
		this.oContainer.oRail.oHandleStart.domElement.style.left	= String(fStartPercentage) + "%";
		this.oContainer.oRail.oHandleEnd.domElement.style.left		= String(fEndPercentage) + "%";
		
		// Update Range
		this.oContainer.oRail.oHandleRange.domElement.style.left	= String(fStartPercentage) + "%";
		this.oContainer.oRail.oHandleRange.domElement.style.width	= String(fEndPercentage - fStartPercentage) + "%";
	}
});

Reflex_Slider.SELECT_MODE_VALUE		= 'value';
Reflex_Slider.SELECT_MODE_RANGE		= 'range';
Reflex_Slider.SELECT_MODE_RANGE_MIN	= 'min';
Reflex_Slider.SELECT_MODE_RANGE_MAX	= 'max';