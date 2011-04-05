
// Reflex Templating engine (similar to JAML)
Reflex_Template	= function(sTag) {
	//debugger;
	var	oElement		= document.createElement(sTag),
		oArgumentData	= Reflex_Template.parseArguments($A(arguments).slice(1));

	// Configure from Argument Data
	// Attributes
	for (sAttributeName in oArgumentData.oConfig) {
		oElement.setAttribute(sAttributeName, oArgumentData.oConfig[sAttributeName]);
	}

	// Children
	for (var i = 0, j = oArgumentData.aChildren.length; i < j; i++) {
		oElement.appendChild(Reflex_Template.extractNode(oArgumentData.aChildren[i]));
	}

	// Return the Element
	return oElement;
};

Reflex_Template.parseArguments	= function (aArguments) {
	var	oArgumentData	= {
		oConfig		: {},
		aChildren	: []
	};
	//debugger;
	for (var i=0, l=aArguments.length; i < l; i++) {
		if (typeof aArguments[i] === 'undefined' || aArguments[i] === null) {
			// No data -- do nothing
		} else if (i == 0 && typeof aArguments[i] === 'object' && Object.getPrototypeOf(aArguments[i]) === Object.getPrototypeOf({})) {
			// Attribute/Config Definition
			//debugger;
			oArgumentData.oConfig	= aArguments[i];
		} else {
			// Child
			oArgumentData.aChildren.push(typeof aArguments[i] === 'function' ? aArguments[i]() : aArguments[i]);
		}
	}
	return oArgumentData;
};

// extractNode(): Takes any input and attempts to extract a DOM node from it
Reflex_Template.extractNode	= function (mNodeContainer) {
	var	oNode;
	//debugger;
	// Use our Node Parsers
	for (var i=0, j=Reflex_Template.aChildNodeParsers.length; i < j; i++) {
		oNode	= Reflex_Template.aChildNodeParsers[i](mNodeContainer);
		if (oNode) {
			return oNode;
		}
	}

	// If all else fails, put it in a Text node
	return document.createTextNode(String(mNodeContainer));
};

// _parseChildElement(): Node parser to check if the Node Container is a DOM Element
Reflex_Template._parseChildElement	= function (mNodeContainer) {
	return mNodeContainer.nodeType ? mNodeContainer : null;
};

// List of registered Child Parsers.  Processed from 0..n.  First to return a DOM Node wins.
Reflex_Template.aChildNodeParsers	= [
	Reflex_Template._parseChildElement
];

// Backwards Compatibility
Reflex_Template._createElement	= Reflex_Template;

Reflex_Template._oTemplates	= {};

//Create a function for each tag
Reflex_Template._aTags	= ['a','abbr','address','area','article','aside','audio','b','base','bdo','blockquote','body','br','button','canvas','caption','cite','code','col','colgroup','command','datalist','dd','del','details','dfn','div','dl','dt','em','embed','fieldset','figcaption','figure','footer','form','h1','h2','h3','h4','h5','h6','head','header','hgroup','hr','html','i','iframe','img','input','ins','keygen','kbd','label','legend','li','link','map','mark','menu','meta','meter','nav','noscript','object','ol','optgroup','option','output','p','param','pre','progress','q','rp','rt','ruby','s','samp','script','section','select','small','source','span','strong','style','sub','summary','sup','table','tbody','td','textarea','tfoot','th','thead','time','title','tr','ul','var','video','wbr'];
for (var i = 0, l = Reflex_Template._aTags.length; i < l; i++) {
	Reflex_Template[Reflex_Template._aTags[i]]	=	Reflex_Template._createElement.curry(Reflex_Template._aTags[i]);
}

// Try to create a shortcut with $T
if (typeof $T === 'undefined') {
	$T	= Reflex_Template;
}
