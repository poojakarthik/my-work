
var Reflex_Anchor	=	Class.create(
{
	initialize	: function()
	{
		this.aRegisteredCallbacks	= {};
		window.addEventListener('hashchange', this._hashChanged.bind(this), false);
	},
	
	registerCallback	: function(sHash, fnCallback, bExecuteIfPresent)
	{
		sHash	= '#' + sHash;
		
		if (!this.aRegisteredCallbacks[sHash])
		{
			this.aRegisteredCallbacks[sHash]	= [];
		}
		
		this.aRegisteredCallbacks[sHash].push(fnCallback);
		
		// Execute the given callback if the current window hash matches
		if (bExecuteIfPresent && (window.location.hash == sHash))
		{
			fnCallback();
		}
	},
	
	setHash	: function(sHash)
	{
		window.location.hash	= sHash.toString();
	},
	
	_hashChanged	: function()
	{
		this.executeCallbacks(window.location.hash);
	},
	
	executeCallbacks	: function(sHash)
	{
		var aCallbacks	= this.aRegisteredCallbacks[sHash];
		
		if (aCallbacks)
		{
			for (var i = 0; i < aCallbacks.length; i++)
			{
				aCallbacks[i]();
			}
		}
	},
});

Reflex_Anchor.getInstance	=	function()
{
	if (typeof Reflex_Anchor.oInstance === 'undefined')
	{
		Reflex_Anchor.oInstance	= new Reflex_Anchor();
	}
	
	return Reflex_Anchor.oInstance; 
};