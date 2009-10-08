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
		
		this.oContainer.oRail.oHandleStart	= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleStart.domElement.addClassName('reflex-slider-rail-handle');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleStart.domElement);
		
		this.oContainer.oRail.oHandleRange	= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleRange.domElement.addClassName('reflex-slider-rail-range');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleRange.domElement);
		
		this.oContainer.oRail.oHandleEnd	= {domElement: document.createElement('div')};
		this.oContainer.oRail.oHandleEnd.domElement.addClassName('reflex-slider-rail-handle');
		this.oContainer.oRail.domElement.appendChild(this.oContainer.oRail.oHandleEnd.domElement);

		this.oEventListeners	=	{
										onMouseDown: this._onMouseDown.bindAsEventListener(this),
										onMouseUp: this._onMouseUp.bindAsEventListener(this),
										onDrag: this._onDrag.bindAsEventListener(this)
									};
		
		this.oContainer.oRail.oHandleStart.domElement.observe('mousedown', this.oEventListeners.onMouseDown);
		this.oContainer.oRail.oHandleEnd.domElement.observe('mousedown', this.oEventListeners.onMouseDown);
		
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
		var iMultiplier	= this.oContainer.oRail.domElement.getWidth() / iValueRange;
		return (iDifference * iMultiplier) + this.iMinValue;
	},
	
	_onMouseDown	: function(oEvent)
	{
		// Enable Dragging
		document.observe('mouseup', this.oEventListeners.onMouseUp);
		document.observe('mousemove', this.oEventListeners.onDrag);
	},
	
	_onMouseUp		: function(oEvent)
	{
		// Disable Dragging
		document.stopObserving('mouseup', this.oEventListeners.onMouseUp);
		document.stopObserving('mousemove', this.oEventListeners.onDrag);
	},
	
	_onDrag	: function(oEvent)
	{
		// Which Slider?
		if (oEvent.element === this.oContainer.oRail.oHandleStart)
		{
			// Update Slider position
			this.setValues(this._calculateValueFromMousePosition(oEvent.pointerX(), oEvent.pointerY()), this.oValues.iEndValue);
		}
		else if (oEvent.element === this.oContainer.oRail.oHandleEnd)
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
