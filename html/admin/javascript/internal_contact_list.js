// Class: InternalContactList
// Handles the Internal Contact List in Flex
var InternalContactList	= Class.create
({	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupPopup	= new Reflex_Popup(40);
		this.pupPopup.setTitle('Internal Contact List');
		this.pupPopup.addCloseButton();		
		this.pupPopup.setFooterButtons(new Array("<input id='Flex_InternalContactList_Popup_Close' type='button' onclick='Flex.InternalContactList.pupPopup.hide();' />"));
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
		
		// Render the popup
		pupPopup.setContent(objResponse.strHTML);
		pupPopup.display();
		
		return;
	}
});

Flex.InternalContactList = (Flex.InternalContactList == undefined) ? new InternalContactList() : Flex.InternalContactList;