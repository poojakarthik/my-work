
var Page_Barring_Action_Ledger = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oContentDiv = $T.div({class: 'page-barring-action-ledger'});
		new Component_Barring_Action_Ledger(oContentDiv, oLoadingPopup);
		oContainerDiv.appendChild(oContentDiv);
	}
});