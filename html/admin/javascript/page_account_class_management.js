
var Page_Account_Class_Management = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
	
		var oAccountClassList		= 	new Component_Account_Class_List(
											null, 
											null, 
											this._classAddedOrChanged.bind(this), 
											this._classesReplaced.bind(this)
										);
		this._oCustomerGroupList	= 	new Component_Customer_Group_Account_Class_Configuration(null, oLoadingPopup);
		var oContentDiv 			= 	$T.div({class: 'page-account-class-management'},
											oAccountClassList.getElement(),
											this._oCustomerGroupList.getElement()
										);
		oContainerDiv.appendChild(oContentDiv);
	},
	
	_classAddedOrChanged : function()
	{
		this._oCustomerGroupList.refreshSelectControls();
	},
	
	_classesReplaced : function()
	{
		this._oCustomerGroupList.reloadCustomerGroups();
	}
});