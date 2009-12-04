var Reflex_Slider_Range	= Class.create(/* extends */Reflex_Slider,
{
	initialize	: function($super)
	{
		$super();
	},
	
	setSelectMode	: function(sSelectMode, iRangeMinimumDifference)
	{
		iRangeMinimumDifference	= iRangeMinimumDifference ? iRangeMinimumDifference : 0;
		
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
		this._paint();
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
		
		// Handle any limits
		this._limitValues();
		
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
	
	setStepping	: function(iStepping, bMinAlwaysSelectable, bMaxAlwaysSelectable)
	{
		this.bMinAlwaysSelectable	= (bMinAlwaysSelectable || bMinAlwaysSelectable === null || bMinAlwaysSelectable === undefined) ? true : false;
		this.bMaxAlwaysSelectable	= (bMaxAlwaysSelectable || bMaxAlwaysSelectable === null || bMaxAlwaysSelectable === undefined) ? true : false;
		
		this.iStepping	= Math.max(1, parseInt(iStepping));
		
		this._limitValues();
		this._paint();
	},
	
	_correctValues	: function()
	{
		for (sHandleName in this.oHandles)
		{
			this.oHandles[sHandleName].iValue	= this._snapValue(this.oHandles[sHandleName].iValue);
		}
		this._limitValues();
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
	}
});
