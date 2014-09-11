
var Page_Collections_Configure = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
	
		var oScenarioList 	= new Component_Collections_Scenario_List(null, oLoadingPopup);
		var oEventList 		= new Component_Collections_Event_List();
		var oEventTypeList 	= new Component_Collections_Event_Type_List();
		var oSeverityList 	= new Component_Collections_Severity_List();
		var oContentDiv 	= 	$T.div({class: 'page-collections-configure'},
									oScenarioList.getElement(),
									oEventList.getElement(),
									oEventTypeList.getElement(),
									oSeverityList.getElement()
								);
		oContainerDiv.appendChild(oContentDiv);
	}
});