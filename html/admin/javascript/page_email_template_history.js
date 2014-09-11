
var Page_Email_Template_History = Class.create(
{
	/*
	 * iEmployeeId & bEditMode are used to determine which actions can be performed on a follow-up.
	 * 
	 * If bEditMode is true, then all can be edited, reassigned and closed.
	 * 
	 * If bEditMode is false, then only those who belong to iEmployeeId can be closed or edited (not reassigned).
	 */
	initialize	: function(oContainerDiv, iTemplateId, sTemplateName, sCustomerGroup)
	{
		//'page-followup-list'
		//'page-followup-list-all-container'
		this._oContentDiv 	= 	$T.div({class: 'page-email-template-history'},
									$T.div({class: 'page-email-template-history-container'}
										// All - placeholder
									)
								);

		this._oFollowUpListAll	= 	new Component_Email_Template_History(
										this._oContentDiv.select('div.page-email-template-history-container').first(), 
										iTemplateId, sTemplateName, sCustomerGroup
									);		
		oContainerDiv.appendChild(this._oContentDiv);
	}
});