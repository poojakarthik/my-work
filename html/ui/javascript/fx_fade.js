var FX_Fade	= Class.create
({
	initialize	: function(fncCallback, bolVisible, intSpeed, intUpdateRate)
	{
		this.fncCallback	= fncCallback;
		
		this.intSpeed		= intSpeed;
 		this.intUpdateRate	= intUpdateRate;
		//alert("Opacity per Cycle: " + this.intSpeed);
		//alert("Update Frequency: " + this.intUpdateRate);
		
		this.fltMaxOpacity	= 1.0;
		this.fltMinOpacity	= 0.0;
		
		this.fltOpacity		= (bolVisible) ? this.fltMaxOpacity : this.fltMinOpacity;
		
		//alert("Initial Opacity: " + this.fltOpacity);
		
		this.objPeriodicalExecuter	= null;
	},
	
	hide	: function()
	{
		if (this.objPeriodicalExecuter)
		{
			this.objPeriodicalExecuter.stop();
			delete this.objPeriodicalExecuter;
		}
		this.objPeriodicalExecuter	= new PeriodicalExecuter(this.transition.bind(this, FX_Fade.FADE_OUT), this.intUpdateRate / 100);
	},
	
	show	: function()
	{
		if (this.objPeriodicalExecuter)
		{
			this.objPeriodicalExecuter.stop();
			delete this.objPeriodicalExecuter;
		}
		this.objPeriodicalExecuter	= new PeriodicalExecuter(this.transition.bind(this, FX_Fade.FADE_IN), this.intUpdateRate / 100);
	},
	
	transition	: function(intFadeDirection)
	{
		var intRate	= this.intSpeed / 100;
		switch (intFadeDirection)
		{
			case FX_Fade.FADE_OUT:
				this.fltOpacity	-= intRate;
				if (this.fltOpacity < this.fltMinOpacity)
				{
					this.fltOpacity	= this.fltMinOpacity;
					
					if (this.objPeriodicalExecuter)
					{
						this.objPeriodicalExecuter.stop();
						delete this.objPeriodicalExecuter;
					}
				}
				break;
				
			case FX_Fade.FADE_IN:
				this.fltOpacity	+= intRate;
				if (this.fltOpacity > this.fltMaxOpacity)
				{
					this.fltOpacity	= this.fltMaxOpacity;
					
					if (this.objPeriodicalExecuter)
					{
						this.objPeriodicalExecuter.stop();
						delete this.objPeriodicalExecuter;
					}
				}
				break;
		}
		this.fncCallback(this.fltOpacity);
	}
});

FX_Fade.FADE_OUT	= 0;
FX_Fade.FADE_IN		= 1;