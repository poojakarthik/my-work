
Reflex_FX_Reveal	= Class.create(/* extends */Reflex_FX,
{
	/**
	 * initialize()
	 * 
	 * Constructor
	 * 
	 * @param {Object} $super
	 * @param {Object} oElement
	 * @param {Object} sDirection
	 * @param {Object} sDockMode
	 * @param {Object} bFade
	 * @param {Object} bUseParentAsContainer	TRUE: Use the Element's parent as the container; FALSE: Create a Container Element
	 * @param {Object} fDuration
	 * @param {Object} mTimingFunction
	 * @param {Object} fnOnCompleteCallback
	 * @param {Object} iFPS
	 */
	initialize	: function($super, oElement, sDirection, sDockMode, bFade, bUseParentAsContainer, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS)
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
										width		:	{
															from	: '0px',
															to		: oDimensions.width+'px'
														},
										height		:	{
															from	: '0px',
															to		: oDimensions.height+'px'
														}
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
		// TODO
		
		// Dock Modes
		// TODO
		
		//Reflex_Debug.asHTMLPopup(oStyleDefinition);
		$super(oContainerElement, oStyleDefinition, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS);
	},
	
	_paint	: function($super, fPercentComplete)
	{
		// Ensure we are using the latest width/height (in case some child elements have been added)
		// TODO: Do we want to interpolate the "from" values as well, to ensure a nice, smooth animation?
		var oDimensions					= Reflex_FX_Reveal.calculateElementDimensions(this.oContainedElement);
		this.oStyleDefinition.width.to	= oDimensions.width;
		this.oStyleDefinition.height.to	= oDimensions.height;
		
		$super(fPercentComplete);
		//alert("Transformation Factor: " + this.fnTimingFunction(this.bReverseDirection ? 1.0 - fPercentComplete : fPercentComplete) + " @ " + (fPercentComplete * 100) + "% complete");
	}
});

Reflex_FX_Reveal.calculateElementDimensions	= function(oElement)
{
	// HACKY HACKY HACKY HACKY
	// Note: This is probably really slow
	
	// Clone the element
	var oElementClone	= oElement.clone(true);
	
	// Set opacity to 0
	oElementClone.setOpacity(0);
	
	// Position absolutely
	oElementClone.style.position	= 'absolute';
	
	// Attach to the Body
	document.body.appendChild(oElementClone);
	
	// Get calculated dimensions
	var oDimensions	= oElementClone.getDimensions();
	
	// Remove clone from DOM
	oElementClone.remove();
	
	return oDimensions;
};

Reflex_FX_Reveal.REVEAL_DIRECTION_UP		= 'up';
Reflex_FX_Reveal.REVEAL_DIRECTION_DOWN		= 'down';
Reflex_FX_Reveal.REVEAL_DIRECTION_LEFT		= 'left';
Reflex_FX_Reveal.REVEAL_DIRECTION_RIGHT		= 'right';
Reflex_FX_Reveal.REVEAL_DIRECTION_UPLEFT	= 'up-left';
Reflex_FX_Reveal.REVEAL_DIRECTION_UPRIGHT	= 'up-right';
Reflex_FX_Reveal.REVEAL_DIRECTION_DOWNLEFT	= 'down-left';
Reflex_FX_Reveal.REVEAL_DIRECTION_DOWNRIGHT	= 'down-right';

Reflex_FX_Reveal.REVEAL_MODE_REVEAL	= 'reveal';
Reflex_FX_Reveal.REVEAL_MODE_REVEAL	= 'slide';
