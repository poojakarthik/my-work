
var Page_Delinquent_CDR_List = Class.create(
{

	initialize	: function(oContainerDiv)
	{
		
		this._oContentDiv 	= 	$T.div({class: 'page-cdr-list'},
									$T.div({class: 'page-cdr-list'}
										// All - placeholder
									)
								);

		this._oFollowUpListAll	= 	new Component_Delinquent_CDR_List(this._oContentDiv.select('div.page-cdr-list').first()	);		
		oContainerDiv.appendChild(this._oContentDiv);
	}
});