
// Reflex Templating engine (similar to JAML)
Reflex_Template	= {};

Reflex_Template._oTemplates	= {};

Reflex_Template._createElement	= function(sTag)
{
	var	oElement	= document.createElement(sTag);
	
	for (var i = 1, l = arguments.length; i < l; i++)
	{
		if (i == 1 && typeof arguments[i] === 'object')
		{
			// Attribute Definition
			for (sAttributeName in arguments[i])
			{
				oElement.setAttribute(sAttributeName, arguments[i][sAttributeName]);
			}
		}
		else if (typeof arguments[i] === 'function')
		{
			// Child Element
			oElement.appendChild(arguments[i]());
		}
		else
		{
			// Text Node
			oElement.appendChild(document.createTextNode(String(arguments[i]).escapeHTML()));
		}
	}
	
	// Return the Element
	return oElement;
};

//Create a function for each tag
Reflex_Template._aTags	= ['a','abbr','acronym','address','area','b','base','bdo','big','blockquote','body','br','button','caption','cite','code','col','colgroup','dd','del','dfn','div','dl','dt','em','fieldset','form','frame','frameset','h1','head','hr','html','i','iframe','img','input','ins','kbd','label','legend','li','link','map','meta','noframes','noscript','object','ol','optgroup','option','p','param','pre','q','samp','script','select','small','span','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var'];
for (var i = 0, l = Reflex_Template._aTags.length; i < l; i++)
{
	Reflex_Template.[Reflex_Template._aTags[i]]	=	Reflex_Template._createElement.curry(Reflex_Template._aTags[i]);
}

// Try to create a shortcut with $T
if (typeof $T === 'undefined')
{
	$T	= Reflex_Template;
}
