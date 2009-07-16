var Operation_Profile	= Class.create
({
	initialize	: function()
	{
		
	}
});

/* Static Methods */
Operation_Profile.buildTree	= function(oOperationProfiles)
{
	oProfileTree	= {};
	
	oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfiles);
	
	//Reflex_Debug.asHTMLPopup(oOperationProfiles);
	
	for (iOperationProfileId in oOperationProfiles)
	{
		oOperationProfiles[iOperationProfileId].aInstances	= [];
	}
	
	Reflex_Debug.asHTMLPopup(oProfileTree);
	Reflex_Debug.asHTMLPopup(oOperations);
	
	return oProfileTree;
};