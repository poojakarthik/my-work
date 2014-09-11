
/**
 * Reflex.Control
 * 
 * Controls are essentially the same as "widgets" -- Rich just hates the word "widget" [see: http://en.wikipedia.org/wiki/Widget_(TV_series)].
 * 
 */
Reflex.Control	= Class.create
({
	initialize	: function()
	{
		this.oElement	= document.createElement('div');
	},
	
	/**
	 * getElement()
	 * 
	 * Returns the Container Element for this Control
	 */
	getElement	: function()
	{
		return this.oElement;
	}
});
