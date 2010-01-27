
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
		
		this.oLastUpdated	= new Date();
		
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
		oMessage.className	= 'ticker-message';
		oMessage.innerHTML	= sMessage;
		this.oFrame.appendChild(oMessage);
	},
	
	_refresh	: function()
	{
		var oCurrentTime	= new Date();
		var iDifference		= (oCurrentTime.getTime() - this.oLastUpdated.getTime()) / 1000;
		var fShiftPixels	= this.iPixelsPerSecond * iDifference;
		//alert("Painting with offet "+iShiftPixels+'px');
		this._paint(fShiftPixels);
	},
	
	_paint	: function(fPixelOffset)
	{
		//alert(this.toSource());
		console.log(this.toSource());
		
		var fLeft	= parseFloat(this.oFrame.style.left) + fPixelOffset;
		//alert(fLeft);
		console.log("Ticker.leftBefore: "+fLeft);
		
		var fDifference	= parseFloat(this.oFrame.offsetWidth) - fLeft;
		if (fDifference < 0)
		{
			fLeft	= parseFloat(this.oElement.clientWidth) + fDifference;
		}
		else if (fDifference > parseFloat(this.oFrame.offsetWidth))
		{
			fLeft	= fDifference;
		}
		
		console.log("Ticker.leftAfter: "+fLeft);
		//alert(fLeft);
		this.oFrame.style.left	= fLeft+'px';
	}
});

Reflex.Control.Ticker.ANIMATION_PIXELS_PER_SECOND	= 30;
Reflex.Control.Ticker.FRAMES_PER_SECOND				= 60;

Reflex.Control.Ticker.DIRECTION_LEFT	= 'left';
Reflex.Control.Ticker.DIRECTION_RIGHT	= 'right';
