var Reflex_FX_Transition	= Class.create
({
	/**
	 * initialize()
	 * 
	 * Constructor
	 * 
	 * @param {Object}			$super						Reference to the Parent Object (populated by PrototypeJS)
	 * @param {Object}			oBindElement				Element to animate
	 * @param {Object}			oTargetStyle				Style to transition to (styles that cannot be animated will snap at the end)
	 * @param {Float}			fDuration					Duration of the transition (in seconds)
	 * @param {String|Function}	mTimingFunction	[optional]	string		: Name of the built-in transition function to use
	 * 														function	: Custom transition function to use
	 * 														default		: 'ease'
	 * @param {Function}		fnCallback		[optional]	Callback function invoked when the transition ends
	 * @param {Integer}			iFPS			[optional]	Frames per Second (overrides the default)
	 */
	initialize	: function(oElement, oTargetStyle, fDuration, mTimingFunction, fnOnCompleteCallback, iFPS)
	{
		this.oElement	= oElement;
		
		// Config
		this.oAnimatedStyles	= {};
		for (sCSSProperty in oTargetStyle)
		{
			this.oAnimatedStyles[sCSSProperty]	=	{
														sStartValue	: oElement.getStyle(sCSSProperty),
														sEndValue	: oTargetStyle[sCSSProperty]
													};
		}
		this.fPercentComplete		= 0.0;
		this.iDuration				= Math.floor(fDuration * 1000);
		this.fnOnCompleteCallback	= fnOnCompleteCallback;
		
		switch (typeof mTimingFunction)
		{
			case 'function':
				//alert("Timing Function is a custom function");
				this.fnTimingFunction	= mTimingFunction;
				break;
			
			case 'string':
				mTimingFunction	= mTimingFunction.camelize();
			default:
				if (Reflex_FX_Transition.oTimingFunctions[mTimingFunction])
				{
					//alert("Timing Function is built-in function '" + mTimingFunction + "'");
					this.fnTimingFunction	= Reflex_FX_Transition.oTimingFunctions[mTimingFunction];
				}
				else
				{
					//alert("Timing Function is reverting to the default function");
					this.fnTimingFunction	= Reflex_FX_Transition.DEFAULT_TIMING_FUNCTION;
				}
				break;
		}
		
		iFPS				= parseInt(iFPS);
		this.iFPSOverride	= (iFPS !== NaN && iFPS > 0) ? iFPS : null;
	},
	
	getTargetStyle	: function()
	{
		return Object.clone(oTargetStyle);
	},
	
	start	: function()
	{
		this.iStartTime	= (new Date()).getTime();
		if (this.iDuration == 0)
		{
			// Zero-length duration - skip straight to the end
			this.end();
		}
		else
		{
			// Start the transition
			this.oPeriodicalExecuter	= new PeriodicalExecuter(this._refresh.bind(this), 1 / (this.iFPSOverride ? this.iFPSOverride : Reflex_FX_Transition.DEFAULT_FRAMES_PER_SECOND));
			//alert('Animation Started!');
		}
	},
	
	// Stop the transition in its current state
	cancel	: function()
	{
		this._destruct();
		//alert('Animation Cancelled!');
	},
	
	// Skip to the end of the transition
	end		: function()
	{
		this._destruct();
		this._paint(1.0);
		//alert('Animation Ended!');
	},
	
	isRunning	: function()
	{
		return (this.oPeriodicalExecuter !== undefined);
	},
	
	isComplete	: function()
	{
		return (!this.isRunning() && this.iStartTime);
	},
	
	_destruct	: function()
	{
		// Cleanup
		//alert('Cleaning up!');
		if (this.oPeriodicalExecuter)
		{
			this.oPeriodicalExecuter.stop();
			delete this.oPeriodicalExecuter;
		}
	},
	
	_refresh	: function()
	{
		if (this.iStartTime)
		{
			//alert('Animation Refreshing!');
			
			// Determine progress
			var iTranspired			= (new Date()).getTime() - this.iStartTime;
			//this.oElement.innerHTML	= this.oElement.innerHTML + "\n"+iTranspired+" = " + ((new Date()).getTime()) + " - " + this.iStartTime + "<br />";
			//this.oElement.innerHTML	= this.oElement.innerHTML + "\n_paint(" + Math.min(1, (iTranspired / this.iDuration)) + " = Math.min(1, ("+iTranspired+" / "+this.iDuration+")))<br />";
			this._paint(Math.min(1, (iTranspired / this.iDuration)));
		}
		else
		{
			throw "_refresh() called before start()!";
		}
	},
	
	_paint	: function(fPercentComplete)
	{
		//alert('Animation Painting!');
		
		// Get transformation factor
		var fTransformationFactor	= this.fnTimingFunction(fPercentComplete);
		//alert("Transformation Factor: " + fTransformationFactor + " @ " + (fPercentComplete * 100) + "% complete");
		
		// Update the Element's style
		var oTransitionStyle	= {};
		for (sCSSProperty in this.oAnimatedStyles)
		{
			oTransitionStyle[sCSSProperty]	= Reflex_FX_Transition.transformCSSValue(this.oAnimatedStyles[sCSSProperty].sStartValue, this.oAnimatedStyles[sCSSProperty].sEndValue, fTransformationFactor);
		}
		this.oElement.setStyle(oTransitionStyle);
		
		alert("Element style updated with: " + oTransitionStyle.toSource());
		
		// Has the transition finished?
		if (Math.floor(fPercentComplete) >= 1.0)
		{
			this._destruct();
			//alert("Transition Complete!");
			if (typeof this.fnOnCompleteCallback == 'function')
			{
				// Invoice the callback
				this.fnOnCompleteCallback();
			}
		}
		//Reflex_Debug.asHTMLPopup("Transformation Factor: " + fTransformationFactor + " @ " + (fPercentComplete * 100) + "% complete (" + Math.floor(fPercentComplete) + ")");
	}
});

Reflex_FX_Transition.DEFAULT_FRAMES_PER_SECOND	= 24;

Reflex_FX_Transition.apply	= function(oElement)
{
	var oTransition	= new Reflex_FX_Transition(oElement);
};

Reflex_FX_Transition.getCSSTransformDefinition	= function(sCSSValue)
{
	sCSSValue	= String(sCSSValue);
	var oTransformDefinition	= {};
	var aMatches;
	//alert(aMatches = Reflex_FX_Transition.oCSSValueRegexes.measurements.exec(sCSSValue));
	if ((aMatches = Reflex_FX_Transition.oCSSValueRegexes.measurements.exec(sCSSValue)))
	{
		// Measurement (1.5em, 22px, 15%, etc)
		oTransformDefinition.sSourceType	= 'measurement';
		oTransformDefinition.sOutputType	= 'measurement';
		oTransformDefinition.sUnits			= aMatches[9] ? aMatches[9].toLowerCase() : null;
		oTransformDefinition.sValue			= aMatches[1];
	}
	else if ((aMatches = Reflex_FX_Transition.oCSSValueRegexes.rgb.exec(sCSSValue)))
	{
		// rgb(r,g,b)
		oTransformDefinition.sSourceType	= 'rgb';
		oTransformDefinition.sOutputType	= 'hexadecimal';
		oTransformDefinition.sValue			= '#' + Reflex_FX_Transition.decimalToHex(Math.min(255, Math.max(0, aMatches[1]))) + Reflex_FX_Transition.decimalToHex(Math.min(255, Math.max(0, aMatches[9]))) + Reflex_FX_Transition.decimalToHex(Math.min(255, Math.max(0, aMatches[17])));
	}
	else if ((aMatches = Reflex_FX_Transition.oCSSValueRegexes.rgbPercent.exec(sCSSValue)))
	{
		// rgb(r%,g%,b%)
		oTransformDefinition.sSourceType	= 'rgbPercent';
		oTransformDefinition.sOutputType	= 'hexadecimal';
		oTransformDefinition.sValue			= '#' + Reflex_FX_Transition.decimalToHex(Math.min(255, Math.max(0, Math.round(aMatches[1] * 255)))) + Reflex_FX_Transition.decimalToHex(Math.min(255, Math.max(0, Math.round(aMatches[9] * 255)))) + Reflex_FX_Transition.decimalToHex(Math.min(255, Math.max(0, Math.round(aMatches[17] * 255))));
	}
	else if ((aMatches = Reflex_FX_Transition.oCSSValueRegexes.hexadecimal.exec(sCSSValue)))
	{
		// Hexadecimal (#0099FF, #09F)
		oTransformDefinition.sSourceType	= 'hexadecimal';
		oTransformDefinition.sOutputType	= 'hexadecimal';
		
		if (aMatches[0].length === 4)
		{
			// #RGB
			oTransformDefinition.sValue	= '#' + aMatches[5]+aMatches[5] + aMatches[6]+aMatches[6] + aMatches[7]+aMatches[7];
		}
		else if (aMatches[0].length === 7)
		{
			// #RRGGBB
			oTransformDefinition.sValue	= aMatches[0];
		}
	}
	else
	{
		// Last Resort: Colour Name
		var sColourName	= sCSSValue.replace(/^\s*|\s*$/, '').toLowerCase();
		if (Reflex_FX_Transition.oColorNameToHex[sColourName] !== undefined)
		{
			// Looks like a Colour Name
			oTransformDefinition.sSourceType	= 'colourName';
			oTransformDefinition.sOutputType	= 'hexadecimal';
			oTransformDefinition.sValue			= '#' + Reflex_FX_Transition.oColorNameToHex[sColourName];
		}
		else
		{
			// We are unable to transform this -- unhandled value
			return null;
		}
	}
	
	return oTransformDefinition;
};

Reflex_FX_Transition.transformCSSValue	= function(sCSSStartValue, sCSSEndValue, fTransformationFactor)
{
	var oStartTransformDefinition	= Reflex_FX_Transition.getCSSTransformDefinition(sCSSStartValue);
	var oEndTransformDefinition		= Reflex_FX_Transition.getCSSTransformDefinition(sCSSEndValue);
	
	if (oStartTransformDefinition === null || oEndTransformDefinition === null)
	{
		// Unable to transform either the Start or End, so immediately snap it to the end
		return sCSSEndValue;
	}
	
	if (oStartTransformDefinition.sOutputType !== oEndTransformDefinition.sOutputType || oStartTransformDefinition.sUnits !== oEndTransformDefinition.sUnits)
	{
		// Start and End values are not in the same Units, so immediately snap it to the end
		return sCSSEndValue;
	}
	
	switch (oEndTransformDefinition.sOutputType)
	{
		case 'measurement':
			return parseFloat(oStartTransformDefinition.sValue) + ((parseFloat(oEndTransformDefinition.sValue) - parseFloat(oStartTransformDefinition.sValue)) * fTransformationFactor);
			break;
			
		case 'colour':
			var oRGB	=	{
								r	:	{
											fStart	: Reflex_FX_Transition.hexToDecimal(oStartTransformDefinition.sValue.substr(1, 2)),
											fEnd	: Reflex_FX_Transition.hexToDecimal(oEndTransformDefinition.sValue.substr(1, 2))
										},
								g	:	{
											fStart	: Reflex_FX_Transition.hexToDecimal(oStartTransformDefinition.sValue.substr(3, 2)),
											fEnd	: Reflex_FX_Transition.hexToDecimal(oEndTransformDefinition.sValue.substr(3, 2))
										},
								b	:	{
											fStart	: Reflex_FX_Transition.hexToDecimal(oStartTransformDefinition.sValue.substr(5, 2)),
											fEnd	: Reflex_FX_Transition.hexToDecimal(oEndTransformDefinition.sValue.substr(5, 2))
										}
							};
			return	'#'	+ Reflex_FX_Transition.decimalToHex(oRGB.r.fStart + ((oRGB.r.fEnd - oRGB.r.fStart) * fTransformationFactor))
						+ Reflex_FX_Transition.decimalToHex(oRGB.g.fStart + ((oRGB.g.fEnd - oRGB.g.fStart) * fTransformationFactor))
						+ Reflex_FX_Transition.decimalToHex(oRGB.b.fStart + ((oRGB.b.fEnd - oRGB.b.fStart) * fTransformationFactor));
			break;
	}
};

Reflex_FX_Transition.decimalToHex	= function(iDecimal)
{
	return iDecimal.toString(16);
};

Reflex_FX_Transition.hexToDecimal	= function(sHexadecimal)
{
	return parseInt(sHexadecimal);
};

// Bezier points source: http://webkit.org/blog/138/css-animation/
Reflex_FX_Transition.oTimingFunctions	=	{
												ease	: function(fProgress)
												{
													return Reflex_FX_Transition.oTimingFunctions.cubicBezier(0.25, 0.1, 0.25, 1.0, fProgress);
												},
												
												linear	: function(fProgress)
												{
													return fProgress;
												},
												
												easeIn	: function(fProgress)
												{
													return Reflex_FX_Transition.oTimingFunctions.cubicBezier(0.25, 0.1, 1.0, 1.0, fProgress);
												},
												
												easeOut	: function(fProgress)
												{
													return Reflex_FX_Transition.oTimingFunctions.cubicBezier(0, 0, 0.58, 1.0, fProgress);
												},
												
												easeInOut	: function(fProgress)
												{
													return Reflex_FX_Transition.oTimingFunctions.cubicBezier(0.42, 0, 0.58, 1.0, fProgress);
												},
												
												cubicBezier	: function(fPoint0, fPoint1, fPoint2, fPoint3, fProgress)
												{
													var fCurvedProgress;
													var t	= fProgress;
													
													// Source: http://en.wikipedia.org/wiki/Bezier_curve
													// B(t) = (1 - t)^3 * P0 + 3(1 - t)^2 * t * P1 + 3(1 - t) * t^2 * P2 + t^3 * P3
													fCurvedProgress	=	(Math.pow(1 - t, 3) * fPoint0)
																		+ (3 * (Math.pow(1 - t, 2) * t * fPoint1))
																		+ (3 * (1 - t) * Math.pow(t, 2) * fPoint2)
																		+ (Math.pow(t, 3) * fPoint3);
													return fCurvedProgress;
												},
											};

Reflex_FX_Transition.DEFAULT_TIMING_FUNCTION	= Reflex_FX_Transition.oTimingFunctions.ease;

Reflex_FX_Transition.oCSSValueRegexes	=	{
												measurements	: /((\d+)((\.)(\d+))?|((\.)(\d+)))(\%|in|cm|mm|em|ex|pt|pc|px)?/i,
												rgb				: /rgb\(\s*((\d+)((\.)(\d+))?|((\.)(\d+)))\s*\,\s*((\d+)((\.)(\d+))?|((\.)(\d+)))\s*\,\s*((\d+)((\.)(\d+))?|((\.)(\d+)))\s*\)/i,
												rgbPercent		: /rgb\(\s*((\d+)((\.)(\d+))?|((\.)(\d+)))\s*\%\s*\,\s*((\d+)((\.)(\d+))?|((\.)(\d+)))\s*\%\s*\,\s*((\d+)((\.)(\d+))?|((\.)(\d+)))\s*\%\s*\)/i,
												hexadecimal		: /\#(([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})|([0-9a-f]{1})([0-9a-f]{1})([0-9a-f]{1}))/i
											};

// Sourced from: http://www.w3schools.com/css/css_colorsfull.asp
Reflex_FX_Transition.oColorNameToHex	=	{
												black					: '000000',
												navy					: '000080',
												darkblue				: '00008b',
												mediumblue				: '0000cd',
												blue					: '0000ff',
												darkgreen				: '006400',
												green					: '008000',
												teal					: '008080',
												darkcyan				: '008b8b',
												deepskyblue				: '00bfff',
												darkturquoise			: '00ced1',
												mediumspringgreen		: '00fa9a',
												lime					: '00ff00',
												springgreen				: '00ff7f',
												aqua					: '00ffff',
												cyan					: '00ffff',
												midnightblue			: '191970',
												dodgerblue				: '1e90ff',
												lightseagreen			: '20b2aa',
												forestgreen				: '228b22',
												seagreen				: '2e8b57',
												darkslategray			: '2f4f4f',
												limegreen				: '32cd32',
												mediumseagreen			: '3cb371',
												turquoise				: '40e0d0',
												royalblue				: '4169e1',
												steelblue				: '4682b4',
												darkslateblue			: '483d8b',
												mediumturquoise			: '48d1cc',
												indigo 					: '4b0082',
												darkolivegreen			: '556b2f',
												cadetblue				: '5f9ea0',
												cornflowerblue			: '6495ed',
												mediumaquamarine		: '66cdaa',
												dimgray					: '696969',
												slateblue				: '6a5acd',
												olivedrab				: '6b8e23',
												slategray				: '708090',
												lightslategray			: '778899',
												mediumslateblue			: '7b68ee',
												lawngreen				: '7cfc00',
												chartreuse				: '7fff00',
												aquamarine				: '7fffd4',
												maroon					: '800000',
												purple					: '800080',
												olive					: '808000',
												gray					: '808080',
												skyblue					: '87ceeb',
												lightskyblue			: '87cefa',
												blueviolet				: '8a2be2',
												darkred					: '8b0000',
												darkmagenta				: '8b008b',
												saddlebrown				: '8b4513',
												darkseagreen			: '8fbc8f',
												lightgreen				: '90ee90',
												mediumpurple			: '9370d8',
												darkviolet				: '9400d3',
												palegreen				: '98fb98',
												darkorchid				: '9932cc',
												yellowgreen				: '9acd32',
												sienna					: 'a0522d',
												brown					: 'a52a2a',
												darkgray				: 'a9a9a9',
												lightblue				: 'add8e6',
												greenyellow				: 'adff2f',
												paleturquoise			: 'afeeee',
												lightsteelblue			: 'b0c4de',
												powderblue				: 'b0e0e6',
												firebrick				: 'b22222',
												darkgoldenrod			: 'b8860b',
												mediumorchid			: 'ba55d3',
												rosybrown				: 'bc8f8f',
												darkkhaki				: 'bdb76b',
												silver					: 'c0c0c0',
												mediumvioletred			: 'c71585',
												indianred 				: 'cd5c5c',
												peru					: 'cd853f',
												chocolate				: 'd2691e',
												tan						: 'd2b48c',
												lightgrey				: 'd3d3d3',
												palevioletred			: 'd87093',
												thistle					: 'd8bfd8',
												orchid					: 'da70d6',
												goldenrod				: 'daa520',
												crimson					: 'dc143c',
												gainsboro				: 'dcdcdc',
												plum					: 'dda0dd',
												burlywood				: 'deb887',
												lightcyan				: 'e0ffff',
												lavender				: 'e6e6fa',
												darksalmon				: 'e9967a',
												violet					: 'ee82ee',
												palegoldenrod			: 'eee8aa',
												lightcoral				: 'f08080',
												khaki					: 'f0e68c',
												aliceblue				: 'f0f8ff',
												honeydew				: 'f0fff0',
												azure					: 'f0ffff',
												sandybrown				: 'f4a460',
												wheat					: 'f5deb3',
												beige					: 'f5f5dc',
												whitesmoke				: 'f5f5f5',
												mintcream				: 'f5fffa',
												ghostwhite				: 'f8f8ff',
												salmon					: 'fa8072',
												antiquewhite			: 'faebd7',
												linen					: 'faf0e6',
												lightgoldenrodyellow	: 'fafad2',
												oldlace					: 'fdf5e6',
												red						: 'ff0000',
												fuchsia					: 'ff00ff',
												magenta					: 'ff00ff',
												deeppink				: 'ff1493',
												orangered				: 'ff4500',
												tomato					: 'ff6347',
												hotpink					: 'ff69b4',
												coral					: 'ff7f50',
												darkorange				: 'ff8c00',
												lightsalmon				: 'ffa07a',
												orange					: 'ffa500',
												lightpink				: 'ffb6c1',
												pink					: 'ffc0cb',
												gold					: 'ffd700',
												peachpuff				: 'ffdab9',
												navajowhite				: 'ffdead',
												moccasin				: 'ffe4b5',
												bisque					: 'ffe4c4',
												mistyrose				: 'ffe4e1',
												blanchedalmond			: 'ffebcd',
												papayawhip				: 'ffefd5',
												lavenderblush			: 'fff0f5',
												seashell				: 'fff5ee',
												cornsilk				: 'fff8dc',
												lemonchiffon			: 'fffacd',
												floralwhite				: 'fffaf0',
												snow					: 'fffafa',
												yellow					: 'ffff00',
												lightyellow				: 'ffffe0',
												ivory					: 'fffff0',
												white					: 'ffffff'
											};
