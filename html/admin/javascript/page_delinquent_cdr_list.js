
var Page_Delinquent_CDR_List = Class.create(
{
	/*
	 * iEmployeeId & bEditMode are used to determine which actions can be performed on a follow-up.
	 * 
	 * If bEditMode is true, then all can be edited, reassigned and closed.
	 * 
	 * If bEditMode is false, then only those who belong to iEmployeeId can be closed or edited (not reassigned).
	 */
	initialize	: function(oContainerDiv, iEmployeeId, bEditMode, sStartDate, sEndDate)
	{
		this._iEmployeeId	= iEmployeeId;
		this._bEditMode		= bEditMode;
		this._oContentDiv 	= 	$T.div({class: 'page-followup-list'},
									$T.div({class: 'page-followup-list-all-container'}
										// All - placeholder
									)
								);

		this._oFollowUpListAll	= 	new Component_Delinquent_CDR_List(
										this._oContentDiv.select('div.page-followup-list-all-container').first(), 
										this._iEmployeeId, 
										this._bEditMode,
										true,
										sStartDate, sEndDate
									);		
		oContainerDiv.appendChild(this._oContentDiv);
	}
});