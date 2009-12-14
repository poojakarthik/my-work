
Reflex_FX_Shift	= Class.create(/* extends */Reflex_FX,
{
	/**
	 * initialize()
	 * 
	 * Constructor
	 * 
	 * @param {Object}			$super
	 * @param {Object}			oElement
	 * @param {String}			sX
	 * @param {String}			sY
	 * @param {Object}			oBoxSize					Either .width & .height (for boxes with dimensions) or .fontSize (for font-relative boxes)
	 * @param {Number[Float]}	fOpacity
	 * @param {Number[Float]}	fDuration
	 * @param {String|Function}	mTimingFunction
	 * @param {Function}		fnOnCompleteCallback
	 * @param {Number[Integer]}	iFPS
	 */
	initialize	: function($super, oElement, fX, fY, oBoxSize, fOpacity, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS)
	{
		var oToStyle	=	{
								opacity	: (fOpacity !== undefined && fOpacity !== null)	? fOpacity	: oElement.getStyle('opacity'),
							};
		
		var oPositionedOffset	= oElement.positionedOffset();
		oToStyle.top	= (Number(fY) !== 'NaN') ? fY : oPositionedOffset.top;
		oToStyle.left	= (Number(fX) !== 'NaN') ? fX : oPositionedOffset.left;
		
		oToStyle.fontSize	= (oBoxSize.fontSize !== undefined && oBoxSize.fontSize !== null) ? oBoxSize.fontSize : oElement.getStyle('font-size');
		
		oToStyle.width	= oBoxSize.width	? oBoxSize.width	: oElement.getWidth();
		oToStyle.height	= oBoxSize.height	? oBoxSize.height	: oElement.getHeight();
		
		var oStyleDefinition	=	{
										opacity		:	{
															from	: oElement.getStyle('opacity'),
															to		: oToStyle.opacity
														},
										top			:	{
															from	: oElement.getStyle('top'),
															to		: oToStyle.top
														},
										left		:	{
															from	: oElement.getStyle('left'),
															to		: oToStyle.left
														},
										width		:	{
															from	: oElement.getStyle('width'),
															to		: oToStyle.width
														},
										height		:	{
															from	: oElement.getStyle('height'),
															to		: oToStyle.height
														},
										fontSize	:	{
															from	: oElement.getStyle('font-size'),
															to		: oToStyle.fontSize
														}
									};
		//Reflex_Debug.asHTMLPopup(oStyleDefinition);
		$super(oElement, oStyleDefinition, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS);
	},
	
	start	: function($super, bReverseDirection, bResume)
	{
		$super(bReverseDirection, bResume);
		//alert('Animation Started!' + (this.bReverseDirection ? ' IN REVERSE!!!!~~' : ''));
	},
	
	_paint	: function($super, fPercentComplete)
	{
		$super(fPercentComplete);
		//alert("Transformation Factor: " + this.fnTimingFunction(this.bReverseDirection ? 1.0 - fPercentComplete : fPercentComplete) + " @ " + (fPercentComplete * 100) + "% complete");
	}
});
