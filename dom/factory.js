
//debugger;

var _undefined,
	// Set of HTML5 + SVG elements to create shortcuts for
	_oElementDefinition = {
		'' : /* HTML5 */ ['a','abbr','address','area','article','aside','audio','b','base','bdo','blockquote','body','br','button','canvas','caption','cite','code','col','colgroup','command','datalist','dd','del','details','dfn','div','dl','dt','em','embed','fieldset','figcaption','figure','footer','form','h1','h2','h3','h4','h5','h6','head','header','hgroup','hr','html','i','iframe','img','input','ins','keygen','kbd','label','legend','li','link','map','mark','menu','meta','meter','nav','noscript','object','ol','optgroup','option','output','p','param','pre','progress','q','rp','rt','ruby','s','samp','script','section','select','small','source','span','strong','style','sub','summary','sup','table','tbody','td','textarea','tfoot','th','thead','time','title','tr','ul','var','video','wbr'],
		'S' : {
			'http://www.w3.org/2000/svg' : /* SVG */ ['svg', 'g', 'defs', 'desc', 'title', 'symbol', 'use', 'image', 'switch', 'style', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'tspan', 'tref', 'textPath', 'altGlyph', 'glyphRef', 'altGlyphItem', 'altGlyphDef', 'marker', 'color-profile', 'filter', 'cursor', 'a', 'view', 'script', 'animate', 'set', 'animateMotion', 'animateColor', 'animateTransform', 'font', 'glyph', 'missing-glyph', 'hkern', 'vkern', 'font-face', 'font-face-src', 'font-face-uri', 'font-face-format', 'font-face-name', 'metadata', 'foreignObject']
		}
	},
	// Converts and array-like object to a native Array
	_toArray = function (mArray) {
		return Array.prototype.slice.call(mArray, 0);
	},
	// Determines whether the Object is a plain object (no prototype chain)
	_isPlainObject = function (mObject) {
		try {
			if (mObject.constructor.prototype !== Object.prototype) {
				return false;
			}
		} catch (oException) {
			return false;
		}
		return true;
	},
	// Determines whether the Object is a String
	_isString = function (mString) {
		return typeof mString == 'string';
	},
	// Determines whether the Object is an Array
	_isArray = function (mArray) {
		return mArray.toString() == '[object Array]';
	},
	// Determines whether the 
	_isFunction = function (mFunction) {
		return typeof mFunction == 'function';
	},
	_isDOMNode = function (mDOMNode) {
		return !!(mDOMNode && mDOMNode.nodeType);
	},
	_isSet = function (mObject) {
		return !(mObject === _undefined || mObject === null);
	};

//debugger;

//debugger;

// ELEMENT FACTORY
var _elementFactory = function (mElement) {
	var aArgs = _toArray(arguments),
		i, j;

	// Param 1: Element
	var	oElement = typeof mElement === 'string' ? this.$document.createElement(mElement) : this.$document.createElementNS(Object.keys(mElement)[0], mElement[Object.keys(mElement)[0]]);
	aArgs.shift();

	// Optional Arguments
	if (aArgs.length) {
		// Param 2: Attributes (optional)
		if (_isPlainObject(aArgs[0])) {
			var oAttributes = aArgs.shift();

			for (var sAttribute in oAttributes) {
				if (oAttributes.hasOwnProperty(sAttribute)) {
					if (sAttribute.length > 2 && sAttribute.substring(0, 2).toLowerCase() == 'on') {
						// Event handler
						if (_isString(oAttributes[sAttribute])) {
							// Attribute on*
							oElement.setAttribute(sAttribute, oAttributes[sAttribute]);
						} else if (_isFunction(oAttributes[sAttribute])) {
							// Single Callback
							oElement.addEventListener(sAttribute.substring(2), oAttributes[sAttribute], false);
						} else if (_isArray(oAttributes[sAttribute])) {
							// Array of Callbacks
							for (i=0; i < oAttributes[sAttribute].length; i++) {
								if (_isFunction(oAttributes[sAttribute][i])) {
									oElement.addEventListener(sAttribute.substring(2), oAttributes[sAttribute][i], false);
								}
							}
						}
					} else {
						// Other attribute
						if (oAttributes[sAttribute] === true) {
							// Conform to XML `selected="selected"`-style boolean attributes
							oElement.setAttribute(sAttribute, sAttribute);
						} else if (oAttributes[sAttribute] !== false) {
							// Set Attribute to provided value (if it's not boolean false)
							oElement.setAttribute(sAttribute, oAttributes[sAttribute]);
						}
					}
				}
			}
		}

		// Param 3+: Children (optional)
		var aChildren = this.$parseChildren(aArgs);
		for (i=0; i < aChildren.length; i++) {
			oElement.appendChild(aChildren[i]);
		}
	}

	return oElement;
};

// FRAGMENT FACTORY
var _fragmentFactory = function () {
	var oFragment = this.$document.createDocumentFragment();

	// 1: Children
	//debugger;
	var aChildren = this.$parseChildren($A(arguments));
	for (i=0; i < aChildren.length; i++) {
		oFragment.appendChild(aChildren[i]);
	}

	return oFragment;
};

// SUPPORT FUNCTIONS
var _parseChild = function (mChild) {
		var oChildNode;
		//debugger;

		// Check Child Parsers
		for (var j=0; j < _aChildNodeParsers.length; j++) {
			// Execute the parser
			oChildNode = _aChildNodeParsers[j](mChild);
			if (oChildNode !== null) {
				// If the Parser was able to match, then stop looking for matches
				break;
			}
		}

		// Default support
		if (oChildNode == null) {
			if (_isDOMNode(mChild)) {
				// DOM Node
				oChildNode = mChild;
			} else if (!_isSet(mChild)) {
				// undefined || null
				oChildNode = this.$document.createTextNode('');
			} else {
				// Anything else, rely on type coercion
				oChildNode = this.$document.createTextNode(mChild);
			}
		}

		return oChildNode;
	},
	_parseChildren = function (aChildren) {
	var aParsedChildren = [];
	
	for (var i=0; i < aChildren.length; i++) {
		aParsedChildren.push(this.$parseChild(aChildren[i]));
	}

	return aParsedChildren;
};

// Extends a namespace (either a new $D or the module's `exports`) with our helper functions
var _extendWithShortcuts = function (oTarget, oDocument) {
	oTarget.$document = oDocument;

	var sNamespaceAlias,
		i, l,
		sNamespace,
		oElementDefinition;
	for (sNamespaceAlias in _oElementDefinition) {
		if (_oElementDefinition.hasOwnProperty(sNamespaceAlias)) {
			if (sNamespaceAlias === '') {
				// No namespace
				for (i=0, l = _oElementDefinition[sNamespaceAlias].length; i < l; i++) {
					oTarget[_oElementDefinition[sNamespaceAlias][i]] = _elementFactory.bind(oTarget, _oElementDefinition[sNamespaceAlias][i]);
				}
			} else {
				// Namespace
				sNamespace = Object.keys(_oElementDefinition[sNamespaceAlias])[0];
				oTarget[sNamespaceAlias] = {};
				for (i=0, l = _oElementDefinition[sNamespaceAlias][sNamespace].length; i < l; i++) {
					oElementDefinition = {};
					oElementDefinition[sNamespace] = _oElementDefinition[sNamespaceAlias][sNamespace][i];
					oTarget[sNamespaceAlias][_oElementDefinition[sNamespaceAlias][sNamespace][i]] = _elementFactory.bind(oTarget, oElementDefinition);
				}
			}
		}
	}

	// Also add support for DOM Fragments
	oTarget.$fragment = _fragmentFactory;

	// Child Parsing
	oTarget.$parseChild = _parseChild;
	oTarget.$parseChildren = _parseChildren;

	return oTarget;
};

// Creates a DOM Factory for a given Document context
exports.$for = function (oDocument) {
	return _extendWithShortcuts({}, oDocument);
};

// Allows other modules to register "Child Parsers", which allow addition Child Types (e.g. a Class that contains an internal DOM Element)
// Parser functions should return `null` if the child isn't of its type
var _aChildNodeParsers = [];
exports.$addChildParser = function (fnHandler) {
	if (_aChildNodeParsers.indexOf(fnHandler) == -1) {
		_aChildNodeParsers.unshift(fnHandler);
	}
};

// Extend our `exports` if there is a global document (e.g. if we're in a browser)
if (document) {
	_extendWithShortcuts(exports, document);
}