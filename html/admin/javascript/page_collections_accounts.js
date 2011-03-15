
var Page_Collections_Accounts = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oContentDiv = $T.div({class: 'page-collections-accounts'});
		new Component_Collections_Account_Management(oContentDiv, oLoadingPopup);
		oContainerDiv.appendChild(oContentDiv);
	}
});