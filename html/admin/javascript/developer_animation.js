var Developer_Animation	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super();
		
		this.fx	=	{
						display	: null,
						hide	: null
					};
		this.container.style.opacity	= 0;
	},
	
	display	: function($super)
	{
		if (this.fx.hide)
		{
			this.fx.hide.cancel();
		}
		
		// Animate
		this.fx.display	= new Reflex_FX_Transition(this.container, {opacity: 1}, 0.5);
		this.fx.display.start();
	},
	
	hide	: function($super)
	{
		if (this.fx.display)
		{
			this.fx.display.cancel();
		}
		
		// Animate
		this.fx.hide	= new Reflex_FX_Transition(this.container, {opacity: 0}, 0.5);
		this.fx.hide.start();
	}
});
