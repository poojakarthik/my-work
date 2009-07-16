var Operation_Profile	= Class.create
({
	initialize	: function()
	{
		
	}
});

/* Static Methods */
Operation_Profile.prepareForTreeGrid	= function(oOperationProfiles)
{
	oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfiles);
	
	//Reflex_Debug.asHTMLPopup(oOperationProfiles);
	
	for (iOperationProfileId in oOperationProfiles)
	{
		oOperationProfiles[iOperationProfileId].aInstances	= [];
	}
	
	//Reflex_Debug.asHTMLPopup(oOperationProfiles);
	
	return oOperationProfiles;
};