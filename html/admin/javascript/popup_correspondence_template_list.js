
var Popup_Correspondence_Template_List = Class.create(Reflex_Popup,
{
	initialize : function($super, iAccountId)
	{
		$super(70);
	
		this._oComponent = new Component_Correspondence_Template_List();
		
		this.setTitle('Correspondence Template List');
		this.addCloseButton();
		this.setContent(
			$T.div({class: 'popup-correspondence-template-list'},
				this._oComponent.getElement()
			)
		);
		this.display();
	}
});