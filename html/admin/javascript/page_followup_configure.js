
var Page_FollowUp_Configure = Class.create(
{
	initialize	: function(oContainerDiv)
	{
		this._oContentDiv 	= 	$T.div({class: 'page-followup-configure'},
									$T.div({class: 'page-followup-category-container'}),
									$T.div({class: 'page-followup-closure-container'})
								);

		this._oFollowUpCategoryList	= 	new Component_FollowUp_Category_List(
											this._oContentDiv.select('div.page-followup-category-container').first()
										);
		this._oFollowUpClosureList	= 	new Component_FollowUp_Closure_List(
											this._oContentDiv.select('div.page-followup-closure-container').first()
										);
		oContainerDiv.appendChild(this._oContentDiv);
	}
});