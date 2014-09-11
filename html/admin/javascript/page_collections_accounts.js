
var Page_Collections_Accounts = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		this._oContainerDiv	= oContainerDiv;
		this._oContentDiv 	= $T.div({class: 'page-collections-accounts'});
		this._oList 		= new Component_Collections_Account_Management(this._oContentDiv, oLoadingPopup, this._readyToAttach.bind(this));
	},
	
	_readyToAttach : function()
	{
		this._oContainerDiv.appendChild(this._oContentDiv);
		this._oList.refresh();
	}
});