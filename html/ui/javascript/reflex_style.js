
Reflex_Style	= Class.create
({
	initialize	: function(sTitle)
	{
		// Create Style Element
		this.oElement		= document.createElement('style');
		this.oElement.type	= 'text/css';
		this.oElement.title	= sTitle;
		
		// Append to DOM
		var oHeadElement	= $$('head').first();
		if (oHeadElement)
		{
			oHeadElement.appendChild(this.oElement);
		}
		else
		{
			throw "Unable to find <head> element";
		}
		
		// Get reference to the Stylesheet
		this.oStyleSheet	= Reflex_Style.getStylesheetForStyleElement(this.oElement);
	},
	
	addRule	: function(sSelector, sRule, iIndex)
	{
		iIndex	= (iIndex === undefined || iIndex === null || iIndex < 0) ? null : iIndex;
		if (this.oStyleSheet.addRule)
		{
			// IE-style
			this.oStyleSheet.addRule(sSelector, sRule, iIndex);
		}
		else if (this.oStyleSheet.insertRule)
		{
			// FF-style
			this.oStyleSheet.insertRule(sSelector + '{' + sRule + '}', iIndex);
		}
		else
		{
			throw "Adding CSS Rules is not supported by the browser";
		}
	},
	
	removeRule	: function(iIndex)
	{
		iIndex	= (iIndex <= 0 || !iIndex) ? 0 : iIndex;
		if (this.oStyleSheet.removeRule)
		{
			// IE-style
			this.oStyleSheet.removeRule(iIndex);
		}
		else if (this.oStyleSheet.deleteRule)
		{
			// FF-style
			this.oStyleSheet.deleteRule(iIndex);
		}
		else
		{
			throw "Removing CSS Rules is not supported by the browser";
		}
	},
	
	getRulesForSelector	: function(sSelector)
	{
		var oRules		= {};
		var aSelectors	= sSelector.split(/\s*,\s*/);
		
		// IE atomises comma-deliminated selectors from addRule()
		for (var i = 0, j = aSelectors.length; i < j; i++)
		{
			var oAtomicRules	= this.getRulesForDefinition(aSelectors[i]);
			for (iIndex in oAtomicRules)
			{
				oRules[iIndex]	= oAtomicRules[iIndex];
			}
		} 
		
		var aRules	= [];
		if (this.oStyleSheet.rules !== undefined)
		{
			// IE-style
			aRules	= this.oStyleSheet.rules;
		}
		else if (this.oStyleSheet.cssRules !== undefined)
		{
			// FF-style
			aRules	= this.oStyleSheet.cssRules;
		}
		else
		{
			throw "Unable to find CSS rule definitions";
		}
		
		for (var i = 0, j = aRules.length; i < j; i++)
		{
			// Match against supplied selector
			if (sSelector === aRules[i])
			{
				oRules[i]	= aRules[i];
			}
		}
		
		return oRules;
	}
});

Reflex_Style.getInstance	= function()
{
	if (!Reflex_Style._oInstance)
	{
		Reflex_Style._oInstance	= new Reflex_Style('reflex-dynamic-style');
	}
	
	return Reflex_Style._oInstance;
};

Reflex_Style.getStylesheetForStyleElement	= function(oStyleElement)
{
	for (var i = 0, j = document.styleSheets.length; i < j; i++)
	{
		if (document.styleSheets[i].ownerNode === oStyleElement || document.styleSheets[i].owningElement === oStyleElement)
		{
			return document.styleSheets[i];
		}
	}
	return null;
};
