
var Page_Collections_Events = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oContentDiv = $T.div({class: 'page-collections-events'});
		new Component_Collections_Event_Management(oContentDiv, oLoadingPopup);
		oContainerDiv.appendChild(oContentDiv);
	}
});