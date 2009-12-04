
var Reflex_Slider_Handle	= Class.create
({
	initialize	: function(oReflexSlider, sName, iValue, fnOnSetValueCallback)
	{
		this.oReflexSlider	= oReflexSlider;
		
		this.sName	= sName;
		
		// Event Handlers
		this.onMouseDown	= this._onMouseUp.bindAsEventListener(this);
		this.onMouseUp		= this._onMouseUp.bindAsEventListener(this);
		this.onDrag			= this._onDrag.bindAsEventListener(this);
		
		this.oElement	= document.createElement('div');
		this.oElement.addClassName('reflex-slider-rail-handle');
		this.oElement.setStyle({left: '0%'});
		this.oElement.observe('mousedown', this.onMouseDown);
		
		this.setValue(iValue);
		
		this.onSetValue		= (typeof fnOnSetValueCallback == 'function') ? fnOnSetValueCallback : null;
	},
	
	getElement	: function()
	{
		return this.oElement;
	},
	
	getValue	: function()
	{
		return this.iValue;
	},
	
	setValue	: function(iValue, bAnimate)
	{
		this.iValue	= iValue;
		
		if (this.oTransitionFX)
		{
			this.oTransitionFX.cancel();
		}
		
		// Update the Element styling
		var oValueLimits	= this.oReflexSlider.getValueLimits();
		var sLeftOffset		= String(this.iValue / (oValueLimits.iMaxValue - oValueLimits.iMinValue)) + '%';
		if (bAnimate)
		{
			this.oTransitionFX	= new Reflex_FX_Transition(this.oElement, {left: sLeftOffset}, 0.25, 'ease');
		}
		else
		{
			this.oTransitionFX	= new Reflex_FX_Transition(this.oElement, {left: sLeftOffset}, 0, 'linear');
		}
		this.paint();
		
		if (this.onSetValue)
		{
			this.onSetValue(this);
		}
	},
	
	setValueForCoordinates	: function(iX, iY)
	{
		alert("Setting Value for Coordinates ["+iX+","+iY+"]");
		this.setValue(this.oReflexSlider.calculateValueFromCoordinates(oMouseCoordinates.x, oMouseCoordinates.y));
	},
	
	paint	: function()
	{
		if (this.oTransitionFX)
		{
			alert("Painting with iValue "+this.iValue);
			if (!this.oTransitionFX.isRunning() && !this.oTransitionFX.isComplete())
			{
				//alert("Starting Animation! (iValue: "+this.iValue+")");
				this.oTransitionFX.start();
			}
		}
		else
		{
			throw "paint() called before setValue() on Reflex_Slider_Handle '"+this.sName+"'";
		}
	},
	
	_onMouseDown	: function(oEvent)
	{
		// Enable Dragging
		document.observe('mouseup', this.onMouseUp);
		document.observe('mousemove', this.onDrag);
	},
	
	_onMouseUp		: function(oEvent)
	{
		// Disable Dragging
		document.stopObserving('mouseup', this.onMouseUp);
		document.stopObserving('mousemove', this.onDrag);
	},
	
	_onDrag	: function(oEvent)
	{
		// Update the Handle's Value based on the Mouse Position
		var oMouseCoordinates	= oEvent.pointer();
		this.setValueForCoordinates(oMouseCoordinates.x, oMouseCoordinates.y);
	}
});
