// Class: InternalContactList
// Handles the Internal Contact List in Flex
var InternalContactList	= Class.create
({	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupPopup	= new Reflex_Popup(90);
		this.pupPopup.setTitle('Internal Contact List');
		this.pupPopup.addCloseButton();
		
		var elmCloseButton		= document.createElement('input');
		elmCloseButton.type		= 'button';
		elmCloseButton.value	= 'Close';
		elmCloseButton.onclick	= this.pupPopup.hide.bind(this.pupPopup);
		this.pupPopup.setFooterButtons(new Array(elmCloseButton), true);
	},
	
	renderViewPopup	: function()
	{
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash(null, null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.InternalContactList._renderViewPopupHTML.bind(this), null, 'InternalContactList', 'getContactListHTML');
		//var fncJsonFunc		= jQuery.json.jsonFunction(Flex.InternalContactList._renderViewPopup.bind(this), null, 'InternalContactList', 'getContactList');
		fncJsonFunc();
	},
	
	_renderViewPopupHTML	: function(objResponse)
	{
		// Close the Splash and display the Summary
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Did we succeed?
		if (objResponse.Success === false)
		{
			$Alert(objResponse.ErrorMessage);
			return;
		}
		
		var strHTML	= "<div style='height: 500px; overflow: auto;'><div align='center'>\n" + objResponse.strHTML + "\n</div></div>\n";
		
		// Render the popup
		this.pupPopup.setContent(strHTML);
		this.pupPopup.display();
		
		return;
	}
});

Flex.InternalContactList = (Flex.InternalContactList == undefined) ? new InternalContactList() : Flex.InternalContactList;