
Reflex_FX_Reveal	= Class.create(/* extends */Reflex_FX,
{
	/**
	 * initialize()
	 * 
	 * Constructor
	 * 
	 * @param {Object} $super
	 * @param {Object} oElement
	 * @param {Object} sOrigin
	 * @param {Object} sDisplayMode
	 * @param {Object} bFade
	 * @param {Object} bUseParentAsContainer	TRUE: Use the Element's parent as the container; FALSE: Create a Container Element
	 * @param {Object} fDuration
	 * @param {Object} mTimingFunction
	 * @param {Object} fnOnCompleteCallback
	 * @param {Object} iFPS
	 */
	initialize	: function($super, oElement, sOrigin, sDisplayMode, bFade, bUseParentAsContainer, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS)
	{
		var oContainerElement;
		var oParent	= oElement.up();
		if (bUseParentAsContainer && oParent)
		{
			// Use Parent Element as Container
			oContainerElement		= oParent;
		}
		else
		{
			// Create a Container
			oContainerElement		= document.createElement('div');
			oParent.appendChild(oContainerElement);
			oContainerElement.appendChild(oElement);
		}
		oContainerElement.style.position	= 'relative';
		
		this.oContainedElement					= oElement;
		this.oContainedElement.style.position	= 'absolute';
		
		var oDimensions			= Reflex_FX_Reveal.calculateElementDimensions(this.oContainedElement);
		var oStyleDefinition	= {};
		
		// Fading
		if (bFade)
		{
			oStyleDefinition.opacity	=	{
												from	: 0.0,
												to		: 1.0
											};
		}
		
		// Direction
		var aDirectionTokens	= sOrigin ? String(sOrigin).split('-') : [];
		var oDirection			= {};
		for (var i = 0, j = aDirectionTokens.length; i < j; i++)
		{
			var sCleanedToken	= aDirectionTokens[i].toLowerCase().strip();
			switch (sCleanedToken)
			{
				case 'top':
					oDirection.up		= false;
					oDirection.down		= true;
					break;
				case 'bottom':
					oDirection.up		= true;
					oDirection.down		= false;
					break;
				case 'left':
					oDirection.left		= false;
					oDirection.right	= true;
					break;
				case 'right':
					oDirection.left		= true;
					oDirection.right	= false;
					break;
			}
		}
		
		if (oDirection.up || oDirection.down)
		{
			oStyleDefinition.height	= {from: '0px', to: oDimensions.height+'px'};
		}
		if (oDirection.left || oDirection.right)
		{
			oStyleDefinition.width	= {from: '0px', to: oDimensions.width+'px'};
		}
		
		// Display Modes
		switch (sDisplayMode.toLowerCase().strip())
		{
			case Reflex_FX_Reveal.DISPLAY_MODE_ROLLOUT:
				this.oContainedElement.style.top	= oDirection.down	? '0px' : null;
				this.oContainedElement.style.bottom	= oDirection.up		? '0px' : null;
				this.oContainedElement.style.left	= oDirection.right	? '0px' : null;
				this.oContainedElement.style.right	= oDirection.left	? '0px' : null;
				break;
			
			default:
			case Reflex_FX_Reveal.DISPLAY_MODE_SLIDE:
				this.oContainedElement.style.top	= oDirection.up		? '0px' : null;
				this.oContainedElement.style.bottom	= oDirection.down	? '0px' : null;
				this.oContainedElement.style.left	= oDirection.left	? '0px' : null;
				this.oContainedElement.style.right	= oDirection.right	? '0px' : null;
				break;
		}
		
		//alert(oStyleDefinition.toSource());
		
		//Reflex_Debug.asHTMLPopup(oStyleDefinition);
		$super(oContainerElement, oStyleDefinition, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS);
	},
	
	start	: function($super, bReverse, fStartOffset)
	{
		//this.oContainedElement.style.position	= fStartOffset > 0.0 ? 'absolute' : null;
		$super(bReverse, fStartOffset);
	},
	
	_destruct	: function($super)
	{
		var fPercentComplete	= this.getPercentComplete(false);
		fPercentComplete		= this.bReverse ? 1.0 - fPercentComplete : fPercentComplete;
		
		this.oElement.style.height				= (fPercentComplete < 1.0) ? this.oElement.style.height		: null;
		this.oElement.style.width				= (fPercentComplete < 1.0) ? this.oElement.style.width		: null;
		this.oContainedElement.style.position	= (fPercentComplete < 1.0) ? 'absolute'						: null;
		
		$super();
	},
	
	_paint	: function($super, fPercentComplete)
	{
		// Ensure we are using the latest width/height (in case some child elements have been added)
		// TODO: Do we want to interpolate the "from" values as well, to ensure a nice, smooth animation?
		var oDimensions					= Reflex_FX_Reveal.calculateElementDimensions(this.oContainedElement);
		if (this.oStyleDefinition.width)
		{
			this.oStyleDefinition.width.to		= oDimensions.width+'px';
		}
		if (this.oStyleDefinition.height)
		{
			this.oStyleDefinition.height.to		= oDimensions.height+'px';
		}
		this.oContainedElement.style.position	= 'absolute';
		
		$super(fPercentComplete);
		//alert("Transformation Factor: " + this.fnTimingFunction(this.bReverseDirection ? 1.0 - fPercentComplete : fPercentComplete) + " @ " + (fPercentComplete * 100) + "% complete");
	}
});

Reflex_FX_Reveal.calculateElementDimensions	= function(oElement)
{
	return oElement.getDimensions();
	
	// HACKY HACKY HACKY HACKY
	// Note: This is probably really slow
	
	//alert("Calculating "+oElement.tagName+" Element Dimensions");
	
	// Clone the element
	var oElementClone	= oElement.clone(true);
	
	//alert('Cloned');
	
	// Set opacity to 0
	oElementClone.setOpacity(0);
	
	//alert('Opacity set to 0');
	
	// Position absolutely
	oElementClone.style.position	= 'absolute';
	oElementClone.style.top			= 0;
	oElementClone.style.left		= 0;
	oElementClone.style.bottom		= null;
	oElementClone.style.right		= null;
	
	//alert('Positioned Absolutely @ [0, 0]');
	
	// Attach to the Body
	// FIXME: This won't work, because the font size may have changed! -- Need to either copy calculated font size, or attach to real parent (could be messy)
	document.body.appendChild(oElementClone);
	
	//alert('Attached to the Body');
	
	// Get calculated dimensions
	var oDimensions	= oElementClone.getDimensions();
	
	//alert(oElement.tagName+' dimensions calculated as '+oDimensions.width+'x'+oDimensions.height+' (vs '+oElement.getWidth()+'x'+oElement.getHeight()+')');
	
	//throw "BREAK";
	
	// Remove clone from DOM
	oElementClone.remove();
	
	//alert('Clone removed from the DOM');
	
	return oDimensions;
};

Reflex_FX_Reveal.DISPLAY_MODE_ROLLOUT	= 'reveal';
Reflex_FX_Reveal.DISPLAY_MODE_SLIDE		= 'slide';
