
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
		var oStyleDefinition	=	{
										width		:	{},
										height		:	{}
									};
		
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
				this.oContainedElement.style.top	= oDirection.top	? '0px' : null;
				this.oContainedElement.style.bottom	= oDirection.bottom	? '0px' : null;
				this.oContainedElement.style.left	= oDirection.left	? '0px' : null;
				this.oContainedElement.style.right	= oDirection.right	? '0px' : null;
				break;
			
			default:
			case Reflex_FX_Reveal.DISPLAY_MODE_SLIDE:
				this.oContainedElement.style.top	= oDirection.top	? null : '0px';
				this.oContainedElement.style.bottom	= oDirection.bottom	? null : '0px';
				this.oContainedElement.style.left	= oDirection.left	? null : '0px';
				this.oContainedElement.style.right	= oDirection.right	? null : '0px';
				break;
		}
		
		//Reflex_Debug.asHTMLPopup(oStyleDefinition);
		$super(oContainerElement, oStyleDefinition, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS);
	},
	
	_destruct	: function($super)
	{
		var fPercentComplete					= this.getPercentComplete(false);
		this.oElement.style.height				= fPercentComplete < 1.0 ? this.oElement.style.height				: null;
		this.oElement.style.width				= fPercentComplete < 1.0 ? this.oElement.style.width				: null;
		this.oContainedElement.style.position	= fPercentComplete < 1.0 ? this.oContainedElement.style.position	: null;
		$super();
	},
	
	_paint	: function($super, fPercentComplete)
	{
		// Ensure we are using the latest width/height (in case some child elements have been added)
		// TODO: Do we want to interpolate the "from" values as well, to ensure a nice, smooth animation?
		var oDimensions					= Reflex_FX_Reveal.calculateElementDimensions(this.oContainedElement);
		this.oStyleDefinition.width.to	= oDimensions.width;
		this.oStyleDefinition.height.to	= oDimensions.height;
		
		// DEBUG
		// Get transformation factor
		var fTransformationFactor	= this.fnTimingFunction(this.bReverse ? 1.0 - fPercentComplete : fPercentComplete);
		//alert("Transformation Factor: " + fTransformationFactor + " @ " + (fPercentComplete * 100) + "% complete");
		
		// Update the Element's style
		var oTransitionStyle	= {};
		for (sCSSProperty in this.oStyleDefinition)
		{
			oTransitionStyle[sCSSProperty]	= Reflex_FX.transformCSSValue(this.oStyleDefinition[sCSSProperty].from, this.oStyleDefinition[sCSSProperty].to, fTransformationFactor);
		}
		alert("Element style updated with: " + oTransitionStyle.toSource());
		// /DEBUG
		
		$super(fPercentComplete);
		//alert("Transformation Factor: " + this.fnTimingFunction(this.bReverseDirection ? 1.0 - fPercentComplete : fPercentComplete) + " @ " + (fPercentComplete * 100) + "% complete");
	}
});

Reflex_FX_Reveal.calculateElementDimensions	= function(oElement)
{
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
	document.body.appendChild(oElementClone);
	
	//alert('Attached to the Body');
	
	// Get calculated dimensions
	var oDimensions	= oElementClone.getDimensions();
	
	//alert(oElement.tagName+' dimensions calculated as '+oDimensions.width+'x'+oDimensions.height);
	
	//throw "BREAK";
	
	// Remove clone from DOM
	oElementClone.remove();
	
	//alert('Clone removed from the DOM');
	
	return oDimensions;
};

Reflex_FX_Reveal.DISPLAY_MODE_ROLLOUT	= 'reveal';
Reflex_FX_Reveal.DISPLAY_MODE_SLIDE		= 'slide';
