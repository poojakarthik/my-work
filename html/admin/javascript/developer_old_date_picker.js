
var Developer_Old_Date_Picker	= Class.create(Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(40);
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		var oLinkElement	= $T.a('Position via Element');
		oLinkElement.observe('click', this._movePicker.bind(this, oLinkElement));
		
		var oLinkEvent	= $T.a('Position via Event');
		oLinkEvent.observe('click', this._movePicker.bind(this, false));
		
		this._oInput		= $T.input({type: 'text'});
		this._oDatePicker	= 	new Component_Date_Picker(
									new Date(), 
									true,
									2010, 
									2020, 
									null, 
									this._dateChange.bind(this)
								);
		this._oInput.value	= this._oDatePicker.getFormattedDateString();
		this._oContent		= 	$T.div(
									this._oDatePicker.getElement(),
									$T.div(
										oLinkElement
									),
									$T.div(
										oLinkEvent
									),
									$T.div(this._oInput)
								);
		
		this.setContent(this._oContent);
		this.setTitle('Old Date Time Picker');
		this.display();
	},
	
	_movePicker	: function(oLink, oEvent)
	{
		if (oLink)
		{
			this._oDatePicker.show(oLink);
		}
		else
		{
			this._oDatePicker.show(oEvent);
		}
	},
	
	_dateChange	: function()
	{
		this._oInput.value	= this._oDatePicker.getFormattedDateString();
	}
});