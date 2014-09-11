
var Page_Adjustment_Configure = Class.create(
{
	initialize : function(oContainerDiv)
	{
		var oLoadingPopup = new Reflex_Popup.Loading();
		oLoadingPopup.display();
	
		var oAdjustmentTypeList	= 	new Component_Adjustment_Type_List(null);
		var oReviewOutcomeList	= 	new Component_Adjustment_Review_Outcome_List(null, oLoadingPopup);
		var oContentDiv 		= 	$T.div({class: 'page-collections-configure'},
										oAdjustmentTypeList.getElement(),
										oReviewOutcomeList.getElement()
									);
		oContainerDiv.appendChild(oContentDiv);
	}
});