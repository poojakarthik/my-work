
var Page_Carrier_Module_List = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oComponent		= 	new Component_Carrier_Module_List(oLoadingPopup);
		var oContentDiv 	= 	$T.div({class: 'page-carrier-module-list'},
									oComponent.getElement()
								);
		oContainerDiv.appendChild(oContentDiv);
	}
});