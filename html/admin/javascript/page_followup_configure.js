
var Page_FollowUp_Configure = Class.create(
{
	initialize	: function(oContainerDiv)
	{
		this._oContentDiv 	= 	$T.div({class: 'page-followup-configure'},
									$T.div({class: 'page-followup-configure-category-container'}),
									$T.div({class: 'page-followup-configure-closure-container'}),
									$T.div({class: 'page-followup-configure-modify-reason-container'}),
									$T.div({class: 'page-followup-configure-recurring-modify-reason-container'}),
									$T.div({class: 'page-followup-configure-reassign-reason-container'})
								);

		this._oFollowUpCategoryList					= 	new Component_FollowUp_Category_List(
															this._oContentDiv.select('div.page-followup-configure-category-container').first()
														);
		this._oFollowUpClosureList					= 	new Component_FollowUp_Closure_List(
															this._oContentDiv.select('div.page-followup-configure-closure-container').first()
														);
		this._oFollowUpModifyReasonList				= 	new Component_FollowUp_Modify_Reason_List(
															this._oContentDiv.select('div.page-followup-configure-modify-reason-container').first()
														);
		this._oFollowUpRecurringModifyReasonList	= 	new Component_FollowUp_Recurring_Modify_Reason_List(
															this._oContentDiv.select('div.page-followup-configure-recurring-modify-reason-container').first()
														);
		this._oFollowUpReassignReasonList			= 	new Component_FollowUp_Reassign_Reason_List(
															this._oContentDiv.select('div.page-followup-configure-reassign-reason-container').first()
														);
		oContainerDiv.appendChild(this._oContentDiv);
	}
});