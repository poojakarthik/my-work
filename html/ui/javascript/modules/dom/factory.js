
//debugger;

var	_undefined,
	_aElements	= ['a','abbr','address','area','article','aside','audio','b','base','bdo','blockquote','body','br','button','canvas','caption','cite','code','col','colgroup','command','datalist','dd','del','details','dfn','div','dl','dt','em','embed','fieldset','figcaption','figure','footer','form','h1','h2','h3','h4','h5','h6','head','header','hgroup','hr','html','i','iframe','img','input','ins','keygen','kbd','label','legend','li','link','map','mark','menu','meta','meter','nav','noscript','object','ol','optgroup','option','output','p','param','pre','progress','q','rp','rt','ruby','s','samp','script','section','select','small','source','span','strong','style','sub','summary','sup','table','tbody','td','textarea','tfoot','th','thead','time','title','tr','ul','var','video','wbr'],
	_toArray	= function (mArray) {
		return Array.prototype.slice.call(mArray, 0);
	},
	_isPlainObject	= function (mObject) {
		try {
			if (mObject.__proto__ !== Object.prototype) {
				return false;
			}
		} catch (oException) {
			return false;
		}
		return true;
	},
	_isString	= function (mString) {
		return typeof mString == 'string';
	},
	_isArray	= function (mArray) {
		return mArray.toString() == '[object Array]';
	},
	_isFunction	= function (mFunction) {
		return typeof mFunction == 'function';
	},
	_isDOMNode	= function (mDOMNode) {
		return !!mDOMNode.nodeType;
	},
	_isSet	= function (mObject) {
		return (mObject === _undefined || mObject === null);
	};

//debugger;

var	_domFactory	= function (sElement) {
	var	aArgs	= _toArray(arguments),
		i;

	// Param 1: Element
	var	oElement	= document.createElement(sElement);
	aArgs.shift();

	// Optional Arguments
	if (aArgs.length) {
		// Param 2: Attributes (optional)
		if (_isPlainObject(aArgs[0])) {
			var	oAttributes	= aArgs.shift();

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
						oElement.setAttribute(sAttribute, oAttributes[sAttribute]);
					}
				}
			}
		}
	}

	var	oChildNode;
	for (i=0; i < aArgs.length; i++) {
		if (_isDOMNode(aArgs[i])) {
			oChildNode	= aArgs[i];
		} else if (_isSet(aArgs[i])) {
			// Undefined or Null: Skip
			continue;
		} else {
			// Other Data
			oChildNode	= this.createTextNode(aArgs[i]);
		}
		oElement.appendChild(oChildNode);
	}

	return oElement;
};

var	_extendWithShortcuts	= function (oNamespace, oDocument) {
	for (var i=0; i < _aElements.length; i++) {
		oNamespace[_aElements[i]]	= _domFactory.bind(oDocument, _aElements[i]);
	}
	return oNamespace;
};

exports.$for	= function (oDocument) {
	return _extendWithShortcuts({}, oDocument);
}

if (document) {
	_extendWithShortcuts(exports, document);
};

