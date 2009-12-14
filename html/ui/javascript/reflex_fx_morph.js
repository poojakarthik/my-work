
var Reflex_FX_Morph	= Class.create(/* extends */Reflex_FX,
{
	/**
	 * initialize()
	 * 
	 * Constructor
	 * 
	 * @param {Object}			$super						Reference to the Parent Reflex_FX Object (populated by PrototypeJS)
	 * @param {Object}			oBindElement				Element to animate
	 * @param {Object}			oToStyle					Style to transition to (styles that cannot be animated will snap at the end)
	 * @param {Number[Float]}	fDuration					Duration of the transition (in seconds)
	 * @param {String|Function}	mTimingFunction	[optional]	string		: Name of the built-in transition function to use
	 * 														function	: Custom transition function to use
	 * 														default		: 'ease'
	 * @param {Function}		fnCallback		[optional]	Callback function invoked when the transition ends
	 * @param {Number[Integer]}	iFPS			[optional]	Frames per Second (overrides the default)
	 */
	initialize	: function($super, oElement, oToStyle, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS)
	{
		// Calculate the "from" style
		var oStyleDefinition	= {};
		for (sCSSProperty in oToStyle)
		{
			oStyleDefinition[sCSSProperty]	=	{
													from	: oElement.getStyle(sCSSProperty),
													to		: oToStyle[sCSSProperty]
												};
		}
		
		$super(oElement, oStyleDefinition, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS);
	},
	
	start	: function($super)
	{
		// Update the start value for the CSS properties with their current values
		for (sCSSProperty in this.oStyleDefinition)
		{
			this.oStyleDefinition[sCSSProperty].from	= this.oElement.getStyle(sCSSProperty);
		}
		
		$super();
	}
});
