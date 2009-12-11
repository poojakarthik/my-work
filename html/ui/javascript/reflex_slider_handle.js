
var Reflex_Slider_Handle	= Class.create
({
	initialize	: function(oReflexSlider, sName, iValue, fnOnSetValueCallback)
	{
		this.oReflexSlider	= oReflexSlider;
		
		this.sName	= sName;
		
		// Event Handlers
		this.onMouseDown	= this._onMouseDown.bindAsEventListener(this);
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
		
		if (this.oMorphFX)
		{
			this.oMorphFX.cancel();
		}
		
		// Update the Element styling
		var oValueLimits	= this.oReflexSlider.getValueLimits();
		var sLeftOffset		= String((this.iValue / (oValueLimits.iMaxValue - oValueLimits.iMinValue)) * 100) + '%';
		//alert("sLeftOffset: '"+sLeftOffset+"'");
		if (bAnimate)
		{
			this.oMorphFX	= new Reflex_FX_Morph(this.oElement, {left: sLeftOffset}, 0.25, 'ease');
		}
		else
		{
			this.oMorphFX	= new Reflex_FX_Morph(this.oElement, {left: sLeftOffset}, 0, 'linear');
		}
		this.paint();
		
		if (this.onSetValue)
		{
			var oValues	= {};
			oValues[this.sName]	= this.getValue();
			this.onSetValue(oValues);
		}
	},
	
	setValueForCoordinates	: function(iX, iY)
	{
		//alert("Setting Value for Coordinates ["+iX+","+iY+"]");
		this.setValue(this.oReflexSlider.calculateValueFromCoordinates(iX, iY));
	},
	
	paint	: function()
	{
		if (this.oMorphFX)
		{
			//alert("Painting with iValue "+this.iValue);
			if (!this.oMorphFX.isRunning() && !this.oMorphFX.isComplete())
			{
				//alert("Starting Animation! (iValue: "+this.iValue+")");
				this.oMorphFX.start();
			}
		}
		else
		{
			throw "paint() called before setValue() on Reflex_Slider_Handle '"+this.sName+"'";
		}
	},
	
	_onMouseDown	: function(oEvent)
	{
		//alert("Handle Mouse Down");
		// Enable Dragging
		document.observe('mouseup', this.onMouseUp);
		document.observe('mousemove', this.onDrag);
	},
	
	_onMouseUp		: function(oEvent)
	{
		//alert("Handle Mouse Up");
		// Disable Dragging
		document.stopObserving('mouseup', this.onMouseUp);
		document.stopObserving('mousemove', this.onDrag);
	},
	
	_onDrag	: function(oEvent)
	{
		//alert("Handle Drag");
		// Update the Handle's Value based on the Mouse Position
		var oMouseCoordinates	= oEvent.pointer();
		this.setValueForCoordinates(oMouseCoordinates.x, oMouseCoordinates.y);
	}
});
