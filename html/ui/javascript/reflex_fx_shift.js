
var Reflex_FX_Shift	= Class.create(/* extends */Reflex_FX,
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
	 * @param {String}			sWidth
	 * @param {String}			sHeight
	 * @param {Number[Float]}	fOpacity
	 * @param {Number[Float]}	fDuration
	 * @param {String|Function}	mTimingFunction
	 * @param {Function}		fnOnCompleteCallback
	 * @param {Number[Integer]}	iFPS
	 */
	initialize	: function($super, oElement, fX, fY, sWidth, sHeight, fOpacity, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS)
	{
		var oToStyle	=	{
								opacity	: fOpacity	? fOpacity	: oElement.getStyle('opacity'),
							};
		
		var oPositionedOffset	= oElement.positionedOffset();
		oToStyle.top	= (Number(fY) !== 'NaN') ? fY : oPositionedOffset.top;
		oToStyle.left	= (Number(fX) !== 'NaN') ? fX : oPositionedOffset.left;
		
		oToStyle.width	= sWidth	? sWidth	: oElement.getWidth();
		oToStyle.height	= sHeight	? sHeight	: oElement.getHeight();
		
		var oStyleDefinition	=	{
										opacity	:	{
														from	: oElement.getStyle('opacity'),
														to		: oToStyle.opacity
													},
										top	:	{
														from	: oElement.getStyle('top'),
														to		: oToStyle.top
													},
										left	:	{
														from	: oElement.getStyle('left'),
														to		: oToStyle.left
													},
										width	:	{
														from	: oElement.getStyle('width'),
														to		: oToStyle.width
													},
										height	:	{
														from	: oElement.getStyle('height'),
														to		: oToStyle.height
													}
									};
		
		$super(oElement, oStyleDefinition, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS);
	}
});
