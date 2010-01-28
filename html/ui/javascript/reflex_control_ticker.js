
Reflex.Control.Ticker	= Class.create(/* extends */Reflex.Control,
{
	initialize	: function($super, sDirection, bSeamlessLooping)
	{
		$super();
		
		this.aMessages	= [];
		
		// Bind to Element
		this.oFrame		= document.createElement('ul');
		this.oElement.appendChild(this.oFrame);
		
		this.oElement.addClassName('ticker');
		this.oFrame.addClassName('ticker-frame');
		
		this.iLastUpdateTime	= (new Date()).getTime();
		
		this.bSeamlessLooping	= bSeamlessLooping ? true : false;
		
		switch (sDirection.charAt(0).toLowerCase())
		{
			case 'r':
			this.sDirection	= Reflex.Control.Ticker.DIRECTION_RIGHT;
				break;
				
			case 'l':
			default:
			this.sDirection	= Reflex.Control.Ticker.DIRECTION_LEFT;
				break;
		}
		
		this.setSpeed(Reflex.Control.Ticker.ANIMATION_PIXELS_PER_SECOND);
		
		this.oPeriodicalExecuter	= new PeriodicalExecuter(this._refresh.bind(this), 1 / Reflex.Control.Ticker.FRAMES_PER_SECOND);
		
		// DEBUG: Actual FPS output
		this.addMessage("Target FPS: "+Reflex.Control.Ticker.FRAMES_PER_SECOND+" fps");
		this._oActualFPSMessage	= this.addMessage("Actual FPS: N/A");
		this._aFPSFrames		= [this.iLastUpdateTime];
	},
	
	setSpeed	: function(iPixelsPerSecond)
	{
		iPixelsPerSecond		= parseInt(iPixelsPerSecond);
		this.iPixelsPerSecond	= (!iPixelsPerSecond || iPixelsPerSecond === 'NaN') ? Reflex.Control.Ticker.ANIMATION_PIXELS_PER_SECOND : Math.abs(iPixelsPerSecond);
	},
	
	addMessage	: function (sMessage)
	{
		this.aMessages.push(sMessage);
		
		var oMessage		= document.createElement('li');
		oMessage.addClassName('ticker-message');
		oMessage.innerHTML	= sMessage;
		this.oFrame.appendChild(oMessage);
		
		return oMessage;
	},
	
	_refresh	: function()
	{
		var iCurrentTime	= (new Date()).getTime();
		var iDifference		= (iCurrentTime - this.iLastUpdateTime) / 1000;
		var fShiftPixels	= this.iPixelsPerSecond * iDifference;
		//alert("Painting with offet "+iShiftPixels+'px');
		this._paint(fShiftPixels);
		
		/* DEBUG: FPS Output */
		this._aFPSFrames.unshift(iCurrentTime);
		
		// Remove frames older than a second 
		while (this._aFPSFrames.length && (iCurrentTime - this._aFPSFrames[this._aFPSFrames.length-1] > 1000))
		{
			this._aFPSFrames.pop();
		}
		
		this._oActualFPSMessage.innerHTML	= "Actual FPS: "+this._aFPSFrames.length+" fps";
		/* /DEBUG */
		
		this.iLastUpdateTime	= iCurrentTime;
	},
	
	_paint	: function(fPixelOffset)
	{
		// Get Direction
		fPixelOffset	= Math.abs(fPixelOffset);
		
		//alert(this.toSource());
		var fCurrentOffset	= parseFloat(this.oFrame.getStyle(this.sDirection));
		//console.log("Ticker.offsetBefore: "+fCurrentOffset);
		
		var fPositionOffset	= fCurrentOffset - fPixelOffset;
		//alert(fLeft);
		//console.log("Ticker.offsetShifted: "+fPositionOffset);
		
		// Is it off the screen?
		if (fPositionOffset < 0 - parseFloat(this.oFrame.clientWidth))
		{
			fPositionOffset	= parseFloat(this.oElement.clientWidth) + fPositionOffset;
		}
		
		//console.log("Ticker.offsetCorrected: "+fPositionOffset);
		//alert(fLeft);
		
		var oUpdateStyle				= {};
		oUpdateStyle[this.sDirection]	= fPositionOffset+'px';
		this.oFrame.setStyle(oUpdateStyle);
	}
});

Reflex.Control.Ticker.ANIMATION_PIXELS_PER_SECOND	= 50;
Reflex.Control.Ticker.FRAMES_PER_SECOND				= 30;

Reflex.Control.Ticker.DIRECTION_LEFT	= 'left';
Reflex.Control.Ticker.DIRECTION_RIGHT	= 'right';
