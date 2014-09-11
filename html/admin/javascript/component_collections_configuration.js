
var Component_Collections_Configuration = Class.create( 
{
	initialize : function(oContainerDiv, oLoadingPopup)
	{
		this._oContainerDiv = oContainerDiv;
		this._oLoadingPopup	= oLoadingPopup;
		
		this._buildUI();
	},
	
	_buildUI : function()
	{
		this._oContentDiv = $T.div({class: 'component-collections-configuration'},
								$T.div({class: 'component-collections-configuration-tabcontainer'})
							);
		
		this._oScenarioList 	= new Component_Collections_Scenario_List(null, this._oLoadingPopup);
		this._oEventList 		= new Component_Collections_Event_List();
		this._oEventTypeList 	= new Component_Collections_Event_Type_List();
		this._oSeverityList 	= new Component_Collections_Severity_List();
		
		this._oTabGroup =	new Control_Tab_Group(
								this._oContentDiv.select('.component-collections-configuration-tabcontainer').first(), 
								false
							);
		
		// TODO: Images for tabs
		this._oTabGroup.addTab('scenarios', 	new Control_Tab('Scenarios', 	this._oScenarioList.getElement(), 	null));
		this._oTabGroup.addTab('events',  		new Control_Tab('Events', 		this._oEventList.getElement(), 		null));
		this._oTabGroup.addTab('event_types',  	new Control_Tab('Event Types', 	this._oEventTypeList.getElement(), 	null));
		this._oTabGroup.addTab('severities',	new Control_Tab('Severities', 	this._oSeverityList.getElement(), 	null));
		
		// Attach content and get data
		this._oContainerDiv.appendChild(this._oContentDiv);
	}
});
