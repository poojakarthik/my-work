
/**
 * Reflex.Control.Field
 * 
 * Base Class for field/input objects
 * 
 */
Reflex.Control.Field	= Class.create(/* extends */Reflex.Control,
{
	initialize	: function()
	{
		
	},
	
	getValue	: function()
	{
		throw "Unimplemented Abstract Function";
	},
	
	setValue	: function(mValue)
	{
		throw "Unimplemented Abstract Function";
	},
	
	addLabel	: function(sLabel)
	{
		// Create a <label> element for this Control Field
		// TODO
	},
	
	removeLabel	: function()
	{
		// Remove any existing <label> element for this Control Field
		// TODO
	}
});
