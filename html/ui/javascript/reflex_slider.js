var Reflex_Slider	= Class.create
({
	initialize	: function(iMinValue, iMaxValue)
	{
		// DOM Elements
		this.oContainer	= {domElement: document.createElement('div')};
		this.oContainer.domElement.addClassName('reflex-slider');
		this.oContainer.domElement.oReflexSlider	= this;
		
		this.oContainer.oRail	= {domElement: document.createElement('div')};
		this.oContainer.oRail.domElement.addClassName('reflex-slider-rail');
		this.oContainer.domElement.appendChild(this.oContainer.oRail.domElement);
		this.oContainer.oRail.onMouseDown	= this._onRailMouseDown.bindAsEventListener(this);
		this.oContainer.oRail.onMouseUp		= this._onRailMouseUp.bindAsEventListener(this);
		this.oContainer.oRail.onDrag		= this._onRailMouseMove.bindAsEventListener(this);
		this.oContainer.oRail.domElement.observe('mousedown', this.oContainer.oRail.onMouseDown);
		/*	Move these to a "Reflex_Slider_Scrollbar" subclass
		this.oContainer.oArrowLeft	= {domElement: document.createElement('div')};
		this.oContainer.oRail.domElement.addClassName('reflex-slider-arrow-left');
		this.oContainer.domElement.insertBefore(this.oContainer.oArrowLeft.domElement, this.oContainer.oRail.domElement);
		
		this.oContainer.oArrowRight	= {domElement: document.createElement('div')};
		this.oContainer.oRail.domElement.addClassName('reflex-slider-arrow-right');
		this.oContainer.domElement.appendChild(this.oContainer.oArrowRight.domElement);
		*/
		this.oHandles	= {};
		
		this.aMarkers	= [];
		
		// Defaults
		this.iStepping	= 1;
		
		// Config
		this.setValueLimits(iMinValue, iMaxValue);
	},
	
	getElement	: function()
	{
		return this.oContainer.domElement;
	},
	
	setValueLimits	: function(iMinValue, iMaxValue)
	{
		this.oValueLimits	=	{iMinValue: parseInt(iMinValue), iMaxValue: parseInt(iMaxValue)};
		this._correctValues();
	},
	
	getValueLimits	: function()
	{
		return this.oValueLimits;
	},
	
	// Adds a new Handle
	addHandle	: function(sHandleName, iValue, fnCallback)
	{
		if (this.oHandles[sHandleName] === undefined)
		{
			// Add a new Handle
			var oHandle	= new Reflex_Slider_Handle(this, sHandleName, iValue, fnCallback);
			this.oContainer.oRail.domElement.appendChild(oHandle.getElement());
			
			this.oHandles[sHandleName]	= oHandle;
		}
		else
		{
			throw "A Handle with the name '"+sHandleName+"' already exists with the value: '"+this.oHandles[sHandleName].getValue()+"'";
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
			oValues[sHandle]	= this.oHandles[sHandle].getValue();
		}
		return oValues;
	},
	
	setValues	: function(oValues, bAnimate)
	{
		//alert(oValues.toSource());
		for (sHandleName in oValues)
		{
			//alert(sHandleName);
			if (this.oHandles[sHandleName])
			{
				//alert("Setting '"+sHandleName+"' to "+oValues[sHandleName]);
				this.oHandles[sHandleName].setValue(this._snapValue(parseInt(oValues[sHandleName])), bAnimate);
			}
			else
			{
				//alert("No Handle with the name '"+sHandleName+"'");
			}
		}
	},
	
	_correctValues	: function()
	{
		for (sHandleName in this.oHandles)
		{
			this.oHandles[sHandleName].setValue(this._snapValue(this.oHandles[sHandleName].iValue));
		}
	},
	
	_snapValue	: function(iValue)
	{
		//alert('iValue: '+iValue);
		// Ensure that it's within our boundaries
		var iMinValue	= this.bMinAlwaysSelectable ? this.oValueLimits.iMinValue : Math.round(Math.ceil((this.oValueLimits.iMinValue / this.iStepping)) * this.iStepping);
		var iMaxValue	= this.bMaxAlwaysSelectable ? this.oValueLimits.iMaxValue : Math.round(Math.floor((this.oValueLimits.iMaxValue / this.iStepping)) * this.iStepping);
		
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
	},
	
	calculateValueFromCoordinates	: function(iX, iY)
	{
		var oCumulativeOffset	= this.oContainer.oRail.domElement.cumulativeOffset();
		var iDifference			= iX - oCumulativeOffset.left;
		
		var iValueRange	= this.oValueLimits.iMaxValue - this.oValueLimits.iMinValue;
		var iMultiplier	= iValueRange / this.oContainer.oRail.domElement.getWidth();
		var iValue		= (iDifference * iMultiplier) + this.oValueLimits.iMinValue;
		
		return this._snapValue(iValue);
	},
	
	_onRailMouseDown	: function(oEvent)	
	{
		document.observe('mousemove', this.oContainer.oRail.onDrag);
		document.observe('mouseup', this.oContainer.oRail.onMouseUp);
		
		var oPointer	= oEvent.pointer();
		this._actionRailMouseEvent(oPointer.x, oPointer.y, true);
	},
	
	_onRailMouseMove	: function(oEvent)	
	{
		var oPointer	= oEvent.pointer();
		this._actionRailMouseEvent(oPointer.x, oPointer.y, false);
	},
	
	_onRailMouseUp	: function(oEvent)	
	{
		document.stopObserving('mousemove', this.oContainer.oRail.onDrag);
		document.stopObserving('mouseup', this.oContainer.oRail.onMouseUp);
	},
	
	_actionRailMouseEvent	: function(iMouseX, iMouseY, bAnimate)
	{
		//alert("Rail event @ ["+iMouseX+","+iMouseY+"] " + (bAnimate ? "(animated)" : ""));
		// Snap the closest handle to this point
		var iMinDistance;
		var oClosestHandle;
		var iCalculatedValue	= this.calculateValueFromCoordinates(iMouseX, iMouseY);
		for (sHandle in this.oHandles)
		{
			var iDifference	= Math.abs(this.oHandles[sHandle].getValue() - iCalculatedValue);
			//alert("Difference for Handle '"+sHandle+"': " + iDifference);
			if (iMinDistance === undefined || iMinDistance > iDifference)
			{
				// This is now the closest Handle
				oClosestHandle	= this.oHandles[sHandle];
			}
		}
		
		if (oClosestHandle !== undefined)
		{
			// Update the closest Handle
			oClosestHandle.setValue(iCalculatedValue, bAnimate);
		}
	},
	
	setHandleLabel	: function()
	{
		
	},
	
	/*
	setMarkers	: function(aMarkers)
	{
		
	},
	
	addMarker	: function(sLabel, iValue, sCustomCSSClass)
	{
		// Does this marker already exist?
		for (var i = 0, j = this.aMarkers.length; i < j; i++)
		{
			if (this.aMarkers[0].sLabel == sLabel)
			{
				return false;
			}
		}
		
		// Add the Marker to the "Marker" array
		this.aMarkers.push({sLabel: sLabel, iValue: this._snapValue(parseInt(iValue))});
		
		// Render as DIVs
		var oContainer			= document.createElement('div');
		oContainer.style.left	= String((iValue / (this.oValueLimits.iMaxValue - this.oValueLimits.iMinValue)) * 100) + '%';
		
		oContainer.addClassName('reflex-slider-rail-marker');
		if (sCustomCSSClass)
		{
			oContainer.addClassName(sCustomCSSClass);
		}
		
		var oLabel	= document.createElement('div');
		oContainer.innerHTML	= sLabel.escapeHTML();
		
		// Add to DOM
		this.oContainer.oRail.appendChild(oContainer);
		
		return true;
	},
	
	removeMarker	: function (sLabel)
	{
		for (var i = 0, j = this.aMarkers.length; i < j; i++)
		{
			if (this.aMarkers[0].sLabel == sLabel)
			{
				return false;
			}
		}
	}*/
});
