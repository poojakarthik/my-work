
var Page_Collections_OCA_Ledger = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oContentDiv = $T.div({class: 'page-collections-oca-ledger'});
		new Component_Collections_OCA_Referral(oContentDiv, oLoadingPopup);
		oContainerDiv.appendChild(oContentDiv);
	}
});
