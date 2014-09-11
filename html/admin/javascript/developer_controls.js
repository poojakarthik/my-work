
Developer_Controls	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(80);
		
		// Add one of each of the Controls
		var oContent	= document.createElement('div');
		
		this.oControls	= {};
		
		this.oControls.oText	= new Reflex.Control.
		
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle("Form Controls");
	}
});
