
/**
 * Reflex.Control.Field.ComboBox
 * 
 * Base Class for field/input objects
 * 
 */
Reflex.Control.Field.ComboBox	= Class.create(/* extends */Reflex.Control.Field,
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
	}
});
