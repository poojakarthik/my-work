
var Page_Barring_Authorisation_Ledger = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oContentDiv = $T.div({class: 'page-barring-authorisation-ledger'});
		new Component_Barring_Authorisation_Ledger(oContentDiv, oLoadingPopup);
		oContainerDiv.appendChild(oContentDiv);
	}
});