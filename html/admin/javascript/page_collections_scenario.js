
var Page_Collections_Scenario = Class.create(
{
	initialize : function(oContainerDiv, bRenderMode, iCopyScenarioId, iEditSenarioId)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
		
		var oContentDiv = $T.div({class: 'page-collections-scenario'});
		new Component_Collections_Scenario(oContentDiv, bRenderMode, iCopyScenarioId, iEditSenarioId, null, null, oLoadingPopup);
		oContainerDiv.appendChild(oContentDiv);
	}
});